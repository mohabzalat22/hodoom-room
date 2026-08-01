<?php
$is_pro_installed = ! empty( $this->settings['has_pro_installed'] );

$action_url = $is_pro_installed
	? add_query_arg(
		array(
			'activate_module[module-page]'   => 'shop-filters',
			'activate_module[settings-page]' => 'filter-presets',
		),
		$this->settings['pro_activate_url']
	)
	: $this->settings['pf_upgrade_pro'];

$target = $is_pro_installed ? '_self' : '_blank';

$tooltip_message = $is_pro_installed ? __( 'Activate Botiga Pro to use this feature', 'botiga' ) : __( 'This is only available on Botiga Pro', 'botiga' );
?>

<div class="botiga-dashboard-card botiga-dashboard-card-no-box-shadow">
    <div class="botiga-dashboard-card-inner-header bt-mb-10px">
        <h2 class="bt-font-size-20px bt-mb-10px bt-mt-0"><?php echo esc_html__( 'Filter Presets', 'botiga' ); ?></h2>
        <p class="bt-text-color-grey bt-m-0">The list of filter presets which you can use in your shop.</p>
    </div>
    <div class="bt-presets-list">
        <div class="bt-presets-list__header">
            <strong>Preset Name</strong>
            <strong>Shortcode</strong>
            <strong>Actions</strong>
        </div>
        <div class="bt-presets-list__body">
            <div class="bt-presets-list__item">
                <p class="bt-presets-list__item-name">My preset</p>
                <p class="bt-presets-list__item-shortcode">[botiga_shop_filters-preset-0ugb9]</p>
                <div class="bt-presets-list__item-actions">
                    <a href="<?php echo esc_url( $action_url ); ?>" class="button button-primary botiga-dashboard-pro-tooltip has-icon" data-tooltip-message="<?php echo esc_attr( $tooltip_message ); ?>" target="<?php echo esc_attr( $target ); ?>">
                        Edit
                        <?php botiga_get_svg_icon( 'icon-lock-outline', true ); ?>
                    </a>
                    <a href="<?php echo esc_url( $action_url ); ?>" class="button button-secondary botiga-dashboard-pro-tooltip has-icon has-icon-blue" data-tooltip-message="<?php echo esc_attr( $tooltip_message ); ?>" target="<?php echo esc_attr( $target ); ?>">
                        Remove
                        <?php botiga_get_svg_icon( 'icon-lock-outline', true ); ?>
                    </a>
                </div>
            </div>
        </div>
        <a href="<?php echo esc_url( $action_url ); ?>" class="button button-secondary bt-presets-list__add-button botiga-dashboard-pro-tooltip has-icon has-icon-blue" data-tooltip-message="<?php echo esc_attr( $tooltip_message ); ?>" target="<?php echo esc_attr( $target ); ?>">
            + Add new preset
            <?php botiga_get_svg_icon( 'icon-lock-outline', true ); ?>
        </a>
    </div>
    <hr class="botiga-dashboard-divider">
    <a href="<?php echo esc_url( $action_url ); ?>" class="button button-primary btsf-save-settings botiga-dashboard-pro-tooltip has-icon" data-tooltip-message="<?php echo esc_attr( $tooltip_message ); ?>" target="<?php echo esc_attr( $target ); ?>" style="max-width: 150px;">
        Save settings
        <?php botiga_get_svg_icon( 'icon-lock-outline', true ); ?>
    </a>
</div>