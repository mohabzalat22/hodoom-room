<?php
/**
 * Botiga Style Guide toggle section.
 *
 * @package Botiga
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Botiga Style Guide toggle section.
 */
class Botiga_Customize_Style_Guide_Toggle extends WP_Customize_Section {

	/**
	 * Section type.
	 *
	 * @var string
	 */
	public $type = 'botiga-style-guide-toggle';

	/**
	 * Render section template.
	 *
	 * @return void
	 */
	protected function render_template() {
		?>
		<li id="accordion-section-{{ data.id }}" class="botiga-style-guide-toggle control-section-{{ data.type }} cannot-expand">
			<button type="button" class="botiga-style-guide-toggle-button">
				<span class="dashicons dashicons-admin-appearance"></span>{{ data.title }}
			</button>
		</li>
		<?php
	}
}
