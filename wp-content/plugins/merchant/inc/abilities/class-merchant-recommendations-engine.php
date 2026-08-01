<?php
/**
 * Merchant Recommendations Engine.
 *
 * Rules-based heuristic engine that scans analytics data and returns
 * structured optimization flags. No external AI or API dependencies.
 *
 * @package Merchant
 * @since   2.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Recommendations_Engine
 *
 * Evaluates heuristic rules against analytics data per active
 * campaign-based module. Produces recommendation objects with
 * flags, severity, metrics snapshots, and factual context strings.
 *
 * @since 2.3.0
 */
class Merchant_Recommendations_Engine {

	/**
	 * The abilities registry.
	 *
	 * @var Merchant_Abilities_Registry
	 */
	private $registry;

	/**
	 * Rules mapped to focus categories.
	 *
	 * @var array<string, string[]>
	 */
	private static $focus_rules = array(
		'conversion' => array( 'low_conversion', 'high_impressions_low_clicks', 'no_data' ),
		'revenue'    => array( 'underperforming', 'top_performer', 'inactive_with_history' ),
		'engagement' => array( 'high_impressions_low_clicks', 'no_data' ),
	);

	/**
	 * Constructor.
	 *
	 * @param Merchant_Abilities_Registry $registry The abilities registry.
	 */
	public function __construct( $registry ) {
		$this->registry = $registry;
	}

	/**
	 * Generate optimization recommendations.
	 *
	 * @param string|null          $module_id  Optional. Limit to one module.
	 * @param string               $focus      'conversion', 'revenue', 'engagement', or 'general'.
	 * @param array<string, mixed> $date_range Optional. { start: string, end: string }.
	 *
	 * @return array<int, array<string, mixed>> Array of recommendation objects.
	 */
	public function generate( $module_id = null, $focus = 'general', $date_range = array() ) {
		$recommendations = array();
		$data_provider   = new Merchant_Analytics_Data_Provider();
		$dates           = $this->resolve_dates( $date_range );

		$modules = $this->get_target_modules( $module_id );

		foreach ( $modules as $mod_id => $mod_data ) {
			$adapter           = $this->registry->get_adapter( $mod_id );
			$campaign_field_id = $adapter->get_campaign_field_id();

			if ( null === $campaign_field_id ) {
				continue; // Settings-only module.
			}

			// Module-level flag: inactive module with historical data.
			if ( ! $this->is_module_active( $mod_id ) ) {
				if ( $this->is_rule_in_focus( 'module_not_active', $focus ) ) {
					$data_provider->set_start_date( $dates['start'] );
					$data_provider->set_end_date( $dates['end'] );
					$historical = $data_provider->get_module_revenue( $mod_id );

					if ( $historical > 0 ) {
						$recommendations[] = $this->build_module_not_active( $mod_id );
					}
				}

				continue;
			}

			// Get campaigns.
			$settings  = $this->get_module_settings( $mod_id );
			$campaigns = isset( $settings[ $campaign_field_id ] ) ? $settings[ $campaign_field_id ] : array();

			if ( empty( $campaigns ) || ! is_array( $campaigns ) ) {
				continue;
			}

			$module_revenues = array();

			// First pass: collect metrics.
			foreach ( $campaigns as $index => $campaign ) {
				$campaign_id = isset( $campaign['flexible_id'] ) ? $campaign['flexible_id'] : $index;

				$data_provider->set_start_date( $dates['start'] );
				$data_provider->set_end_date( $dates['end'] );

				$metrics           = $this->get_campaign_metrics( $data_provider, $mod_id, $campaign_id );
				$module_revenues[] = $metrics['revenue'];

				// Per-campaign rules (except underperforming/top_performer).
				$per_campaign_rules = array(
					'low_conversion',
					'high_impressions_low_clicks',
					'no_data',
					'inactive_with_history',
				);

				foreach ( $per_campaign_rules as $rule ) {
					if ( ! $this->is_rule_in_focus( $rule, $focus ) ) {
						continue;
					}

					$result = $this->evaluate_rule( $rule, $campaign, $metrics, $mod_id, $index );

					if ( null !== $result ) {
						$recommendations[] = $result;
					}
				}
			}

			// Second pass: underperforming/top_performer (need average).
			if ( count( $module_revenues ) > 1 ) {
				$avg_revenue = array_sum( $module_revenues ) / count( $module_revenues );

				foreach ( $campaigns as $index => $campaign ) {
					$campaign_id = isset( $campaign['flexible_id'] ) ? $campaign['flexible_id'] : $index;

					$data_provider->set_start_date( $dates['start'] );
					$data_provider->set_end_date( $dates['end'] );

					$metrics = $this->get_campaign_metrics( $data_provider, $mod_id, $campaign_id );

					if ( $this->is_rule_in_focus( 'underperforming', $focus ) && $metrics['revenue'] < $avg_revenue * 0.5 ) {
						$recommendations[] = $this->build_recommendation(
							'underperforming',
							'warning',
							$mod_id,
							$index,
							$campaign_id,
							$metrics,
							sprintf(
								'Campaign revenue ($%s) is less than half the module average ($%s). Consider adjusting targeting or discount value.',
								number_format( $metrics['revenue'], 2 ),
								number_format( $avg_revenue, 2 )
							)
						);
					}

					if ( $this->is_rule_in_focus( 'top_performer', $focus ) && $metrics['revenue'] > $avg_revenue * 2 ) {
						$recommendations[] = $this->build_recommendation(
							'top_performer',
							'info',
							$mod_id,
							$index,
							$campaign_id,
							$metrics,
							sprintf(
								'Campaign revenue ($%s) is more than double the module average. This campaign is a standout performer.',
								number_format( $metrics['revenue'], 2 )
							)
						);
					}
				}
			}
		}

		return $recommendations;
	}

	/**
	 * Get target modules filtered by module_id.
	 *
	 * @param string|null $module_id Optional module filter.
	 *
	 * @return array<string, array<string, mixed>> Module data keyed by module ID.
	 */
	private function get_target_modules( $module_id ) {
		$all_modules = Merchant_Admin_Modules::$modules_data;
		$filtered    = array();

		foreach ( $all_modules as $mod_id => $mod_data ) {
			if ( Merchant_Abilities_Registry::is_excluded( $mod_id ) ) {
				continue;
			}

			if ( null !== $module_id && $mod_id !== $module_id ) {
				continue;
			}

			$filtered[ $mod_id ] = $mod_data;
		}

		return $filtered;
	}

	/**
	 * Check if a module is active via the options DB.
	 *
	 * Uses direct option check instead of Merchant_Modules::is_module_active()
	 * to avoid UI-specific side effects (preview mode, filters, etc.).
	 *
	 * @param string $module_id Module identifier.
	 *
	 * @return bool True if active.
	 */
	private function is_module_active( $module_id ) {
		$modules = get_option( 'merchant-modules', array() );

		return ! empty( $modules[ $module_id ] );
	}

	/**
	 * Get module settings from the merchant option.
	 *
	 * @param string $module_id Module identifier.
	 *
	 * @return array<string, mixed> Module settings.
	 */
	private function get_module_settings( $module_id ) {
		$options = get_option( 'merchant', array() );

		return isset( $options[ $module_id ] ) ? $options[ $module_id ] : array();
	}

	/**
	 * Get campaign metrics from the data provider.
	 *
	 * @param Merchant_Analytics_Data_Provider $data_provider Data provider instance.
	 * @param string                           $module_id     Module identifier.
	 * @param string|int                       $campaign_id   Campaign identifier.
	 *
	 * @return array<string, mixed> Metrics array.
	 */
	private function get_campaign_metrics( $data_provider, $module_id, $campaign_id ) {
		$impressions = $data_provider->get_campaign_impressions( $campaign_id, $module_id );
		$clicks      = $data_provider->get_campaign_clicks( $campaign_id, $module_id );
		$orders      = $data_provider->get_campaign_orders_count( $campaign_id, $module_id );
		$revenue     = $data_provider->get_campaign_revenue( $campaign_id, $module_id );
		$ctr         = $data_provider->get_campaign_ctr_percentage( $campaign_id, $module_id );

		// Compute conversion rate inline (no provider method).
		$conversion_rate = $impressions > 0 ? ( $orders / $impressions ) * 100 : 0;

		return array(
			'impressions'     => $impressions,
			'clicks'          => $clicks,
			'orders'          => $orders,
			'revenue'         => (float) $revenue,
			'ctr'             => (float) $ctr,
			'conversion_rate' => round( $conversion_rate, 2 ),
		);
	}

	/**
	 * Evaluate a single rule against campaign data and metrics.
	 *
	 * @param string                 $rule     Rule identifier.
	 * @param array<string, mixed>   $campaign Campaign data.
	 * @param array<string, mixed>   $metrics  Campaign metrics.
	 * @param string                 $mod_id   Module identifier.
	 * @param int|string             $index    Campaign index.
	 *
	 * @return array<string, mixed>|null Recommendation or null if rule not triggered.
	 */
	private function evaluate_rule( $rule, $campaign, $metrics, $mod_id, $index ) {
		$campaign_id = isset( $campaign['flexible_id'] ) ? $campaign['flexible_id'] : $index;

		switch ( $rule ) {
			case 'low_conversion':
				return $this->evaluate_low_conversion( $campaign, $metrics, $mod_id, $index, $campaign_id );

			case 'high_impressions_low_clicks':
				return $this->evaluate_high_impressions_low_clicks( $campaign, $metrics, $mod_id, $index, $campaign_id );

			case 'no_data':
				return $this->evaluate_no_data( $campaign, $metrics, $mod_id, $index, $campaign_id );

			case 'inactive_with_history':
				return $this->evaluate_inactive_with_history( $campaign, $metrics, $mod_id, $index, $campaign_id );

			default:
				return null;
		}
	}

	/**
	 * Evaluate low_conversion rule.
	 *
	 * @param array<string, mixed> $campaign    Campaign data.
	 * @param array<string, mixed> $metrics     Campaign metrics.
	 * @param string               $mod_id      Module identifier.
	 * @param int|string           $index       Campaign index.
	 * @param string|int           $campaign_id Campaign identifier.
	 *
	 * @return array<string, mixed>|null Recommendation or null.
	 */
	private function evaluate_low_conversion( $campaign, $metrics, $mod_id, $index, $campaign_id ) {
		if ( $metrics['impressions'] <= 100 || $metrics['conversion_rate'] >= 1 ) {
			return null;
		}

		return $this->build_recommendation(
			'low_conversion',
			'warning',
			$mod_id,
			$index,
			$campaign_id,
			$metrics,
			sprintf(
				'%d impressions but only %d orders (%s%% conversion). The offer reaches shoppers but isn\'t compelling enough to drive purchases.',
				$metrics['impressions'],
				$metrics['orders'],
				number_format( $metrics['conversion_rate'], 1 )
			)
		);
	}

	/**
	 * Evaluate high_impressions_low_clicks rule.
	 *
	 * @param array<string, mixed> $campaign    Campaign data.
	 * @param array<string, mixed> $metrics     Campaign metrics.
	 * @param string               $mod_id      Module identifier.
	 * @param int|string           $index       Campaign index.
	 * @param string|int           $campaign_id Campaign identifier.
	 *
	 * @return array<string, mixed>|null Recommendation or null.
	 */
	private function evaluate_high_impressions_low_clicks( $campaign, $metrics, $mod_id, $index, $campaign_id ) {
		if ( $metrics['impressions'] <= 100 || $metrics['ctr'] >= 2 ) {
			return null;
		}

		return $this->build_recommendation(
			'high_impressions_low_clicks',
			'warning',
			$mod_id,
			$index,
			$campaign_id,
			$metrics,
			sprintf(
				'%d impressions but only %d clicks (%s%% CTR). The offer is seen but not compelling enough to trigger interaction.',
				$metrics['impressions'],
				$metrics['clicks'],
				number_format( $metrics['ctr'], 1 )
			)
		);
	}

	/**
	 * Evaluate no_data rule.
	 *
	 * @param array<string, mixed> $campaign    Campaign data.
	 * @param array<string, mixed> $metrics     Campaign metrics.
	 * @param string               $mod_id      Module identifier.
	 * @param int|string           $index       Campaign index.
	 * @param string|int           $campaign_id Campaign identifier.
	 *
	 * @return array<string, mixed>|null Recommendation or null.
	 */
	private function evaluate_no_data( $campaign, $metrics, $mod_id, $index, $campaign_id ) {
		if ( $metrics['impressions'] > 0 ) {
			return null;
		}

		$days = $this->get_campaign_age_days( $campaign );

		if ( $days <= 7 ) {
			return null;
		}

		return $this->build_recommendation(
			'no_data',
			'info',
			$mod_id,
			$index,
			$campaign_id,
			$metrics,
			sprintf(
				'Campaign has been active for %d days but has zero impressions. Check if targeting rules are correct or if the campaign is visible on the storefront.',
				$days
			)
		);
	}

	/**
	 * Evaluate inactive_with_history rule.
	 *
	 * @param array<string, mixed> $campaign    Campaign data.
	 * @param array<string, mixed> $metrics     Campaign metrics.
	 * @param string               $mod_id      Module identifier.
	 * @param int|string           $index       Campaign index.
	 * @param string|int           $campaign_id Campaign identifier.
	 *
	 * @return array<string, mixed>|null Recommendation or null.
	 */
	private function evaluate_inactive_with_history( $campaign, $metrics, $mod_id, $index, $campaign_id ) {
		$status = isset( $campaign['status'] ) ? $campaign['status'] : 'active';

		if ( 'inactive' !== $status ) {
			return null;
		}

		if ( $metrics['revenue'] <= 0 ) {
			return null;
		}

		return $this->build_recommendation(
			'inactive_with_history',
			'opportunity',
			$mod_id,
			$index,
			$campaign_id,
			$metrics,
			sprintf(
				'Campaign is currently inactive but generated $%s in revenue historically. Consider reactivating or creating a similar campaign.',
				number_format( $metrics['revenue'], 2 )
			)
		);
	}

	/**
	 * Build a module_not_active recommendation.
	 *
	 * @param string $module_id Module identifier.
	 *
	 * @return array<string, mixed> Recommendation object.
	 */
	private function build_module_not_active( $module_id ) {
		return array(
			'flag'      => 'module_not_active',
			'severity'  => 'opportunity',
			'module_id' => $module_id,
			'context'   => sprintf(
				"Module '%s' is not active but has historical revenue.",
				$module_id
			),
			'metrics'   => array(),
		);
	}

	/**
	 * Build a recommendation object.
	 *
	 * @param string               $flag        Flag identifier.
	 * @param string               $severity    Severity level.
	 * @param string               $module_id   Module identifier.
	 * @param int|string           $index       Campaign index.
	 * @param string|int           $campaign_id Campaign identifier.
	 * @param array<string, mixed> $metrics     Metrics snapshot.
	 * @param string               $context     Context description.
	 *
	 * @return array<string, mixed> Recommendation object.
	 */
	private function build_recommendation( $flag, $severity, $module_id, $index, $campaign_id, $metrics, $context ) {
		return array(
			'flag'        => $flag,
			'severity'    => $severity,
			'module_id'   => $module_id,
			'campaign_id' => $campaign_id,
			'index'       => $index,
			'context'     => $context,
			'metrics'     => $metrics,
		);
	}

	/**
	 * Get the age of a campaign in days.
	 *
	 * @param array<string, mixed> $campaign Campaign data.
	 *
	 * @return int Age in days.
	 */
	private function get_campaign_age_days( $campaign ) {
		$created_at = isset( $campaign['created_at'] ) ? $campaign['created_at'] : '';

		if ( empty( $created_at ) ) {
			// No creation date — assume old enough to trigger.
			return 30;
		}

		$created = strtotime( $created_at );

		if ( false === $created ) {
			return 30;
		}

		return max( 0, (int) floor( ( time() - $created ) / 86400 ) );
	}

	/**
	 * Check if a rule is included in the requested focus.
	 *
	 * @param string $rule  Rule identifier.
	 * @param string $focus Focus category.
	 *
	 * @return bool True if rule is in focus.
	 */
	private function is_rule_in_focus( $rule, $focus ) {
		if ( 'general' === $focus ) {
			return true;
		}

		if ( ! isset( self::$focus_rules[ $focus ] ) ) {
			return true; // Unknown focus — include all.
		}

		return in_array( $rule, self::$focus_rules[ $focus ], true );
	}

	/**
	 * Resolve date range with defaults.
	 *
	 * @param array<string, mixed> $date_range Optional date range { start: string, end: string }.
	 *
	 * @return array{start: string, end: string} Resolved dates in m/d/y format.
	 */
	private function resolve_dates( $date_range ) {
		$start = isset( $date_range['start'] ) ? $date_range['start'] : '';
		$end   = isset( $date_range['end'] ) ? $date_range['end'] : '';

		if ( empty( $start ) || empty( $end ) ) {
			return array(
				'start' => gmdate( 'm/d/y', strtotime( '-30 days' ) ),
				'end'   => gmdate( 'm/d/y' ),
			);
		}

		return array(
			'start' => $this->normalize_date( $start ),
			'end'   => $this->normalize_date( $end ),
		);
	}

	/**
	 * Normalize a date string to m/d/y format.
	 *
	 * @param string $date Date string (Y-m-d or m/d/y).
	 *
	 * @return string Normalized date in m/d/y format.
	 */
	private function normalize_date( $date ) {
		// ISO 8601: Y-m-d.
		if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
			$d = DateTime::createFromFormat( 'Y-m-d', $date );
			return $d ? $d->format( 'm/d/y' ) : gmdate( 'm/d/y' );
		}

		// Legacy m/d/y — pass through.
		return $date;
	}
}
