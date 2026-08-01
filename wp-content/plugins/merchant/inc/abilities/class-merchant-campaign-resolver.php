<?php
/**
 * Merchant Campaign Resolver.
 *
 * Resolves campaign identifiers (name or index) to actual campaign data
 * from the module's flexible_content field.
 *
 * @package Merchant
 * @since   2.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Campaign_Resolver
 *
 * Locates a specific campaign within a module's flexible_content
 * settings by index (integer) or by title (string).
 *
 * @since 2.3.0
 */
class Merchant_Campaign_Resolver {

	/**
	 * Resolve a campaign by name (string) or index (integer).
	 *
	 * @param string     $module_id            The module identifier.
	 * @param string|int $campaign_identifier  Name or index.
	 * @param string     $campaign_field_id    The flexible_content field ID.
	 * @param string     $title_field          The field used as campaign title.
	 *
	 * @return array{index: int, campaign: array<string, mixed>}|array{error: array<string, mixed>}
	 */
	public function resolve( $module_id, $campaign_identifier, $campaign_field_id, $title_field = 'offer-title' ) {
		$module = Merchant_Modules::get_module( $module_id );

		if ( ! $module ) {
			return array(
				'error' => array(
					'code'    => 'module_not_found',
					'message' => sprintf( "Module '%s' not found.", $module_id ),
				),
			);
		}

		$settings  = $module->get_module_settings();
		$campaigns = isset( $settings[ $campaign_field_id ] ) ? $settings[ $campaign_field_id ] : array();

		if ( ! is_array( $campaigns ) || empty( $campaigns ) ) {
			return array(
				'error' => array(
					'code'    => 'campaign_not_found',
					'message' => sprintf( "No campaigns found in '%s'.", $module_id ),
				),
			);
		}

		// Resolve by flexible_id UUID (highest priority — guaranteed unique).
		$uuid_result = $this->resolve_by_uuid( $campaigns, (string) $campaign_identifier );
		if ( null !== $uuid_result ) {
			return $uuid_result;
		}

		// Resolve by index.
		if ( is_int( $campaign_identifier ) || ctype_digit( (string) $campaign_identifier ) ) {
			$index = (int) $campaign_identifier;

			if ( ! isset( $campaigns[ $index ] ) ) {
				return array(
					'error' => array(
						'code'      => 'campaign_not_found',
						'message'   => sprintf( "Campaign index %d not found. Total campaigns: %d.", $index, count( $campaigns ) ),
						'available' => $this->get_available_names( $campaigns, $title_field ),
					),
				);
			}

			return array(
				'index'    => $index,
				'campaign' => $campaigns[ $index ],
			);
		}

		// Resolve by name (case insensitive).
		$search = strtolower( (string) $campaign_identifier );
		foreach ( $campaigns as $index => $campaign ) {
			$name = isset( $campaign[ $title_field ] ) ? $campaign[ $title_field ] : '';
			if ( strtolower( $name ) === $search ) {
				return array(
					'index'    => $index,
					'campaign' => $campaign,
				);
			}
		}

		return array(
			'error' => array(
				'code'      => 'campaign_not_found',
				'message'   => sprintf( "Campaign '%s' not found in '%s'.", $campaign_identifier, $module_id ),
				'available' => $this->get_available_names( $campaigns, $title_field ),
			),
		);
	}

	/**
	 * Resolve a campaign by its flexible_id UUID.
	 *
	 * @param array<int, array<string, mixed>> $campaigns  The campaigns array.
	 * @param string                           $identifier The identifier to match against flexible_id.
	 *
	 * @return array{index: int, campaign: array<string, mixed>}|null
	 */
	private function resolve_by_uuid( $campaigns, $identifier ) {
		foreach ( $campaigns as $index => $campaign ) {
			if ( isset( $campaign['flexible_id'] ) && $campaign['flexible_id'] === $identifier ) {
				return array(
					'index'    => $index,
					'campaign' => $campaign,
				);
			}
		}

		return null;
	}

	/**
	 * Get available campaign names for error messages.
	 *
	 * @param array<int, array<string, mixed>> $campaigns   The campaigns array.
	 * @param string                           $title_field The title field key.
	 *
	 * @return array<int, string> Campaign names.
	 */
	private function get_available_names( $campaigns, $title_field ) {
		$names = array();
		foreach ( $campaigns as $campaign ) {
			if ( isset( $campaign[ $title_field ] ) && '' !== $campaign[ $title_field ] ) {
				$names[] = $campaign[ $title_field ];
			}
		}
		return $names;
	}
}
