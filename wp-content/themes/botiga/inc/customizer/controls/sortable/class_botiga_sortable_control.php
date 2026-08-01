<?php
/**
 * Sortable Control
 *
 * Native replacement for the bundled Kirki `\Kirki\Control\Sortable` control.
 * Renders a list of items that can be reordered by drag/drop and toggled on/off.
 * The saved value is an ordered array of the enabled item keys, matching the
 * exact format the Kirki Sortable control produced, so existing settings carry
 * over without migration.
 *
 * @package Botiga
 * @since 2.4.7
 */

if ( ! class_exists( 'Botiga_Sortable_Control' ) ) {

	/**
	 * Botiga Sortable customizer control.
	 *
	 * @since 2.4.7
	 */
	class Botiga_Sortable_Control extends WP_Customize_Control {

		/**
		 * The control type.
		 *
		 * @since 2.4.7
		 * @var string
		 */
		public $type = 'botiga-sortable-control';

		/**
		 * Enqueue control related scripts/styles.
		 *
		 * @since 2.4.7
		 * @return void
		 */
		public function enqueue() {
			parent::enqueue();

			// jquery-ui-sortable is declared explicitly here. The old Kirki control
			// relied on it already being present in the Customizer; we do not.
			wp_enqueue_script(
				'botiga-sortable-control',
				get_template_directory_uri() . '/inc/customizer/controls/sortable/script.js',
				array( 'jquery', 'jquery-ui-sortable', 'customize-base' ),
				BOTIGA_VERSION,
				false
			);
		}

		/**
		 * Export data to the underscore template.
		 *
		 * @since 2.4.7
		 * @return void
		 */
		public function to_json() {
			parent::to_json();

			// Default value.
			$this->json['default'] = $this->setting->default;
			if ( isset( $this->default ) ) {
				$this->json['default'] = $this->default;
			}

			// Value.
			$this->json['value'] = $this->value();

			// Choices.
			$this->json['choices'] = $this->choices;

			// The link.
			$this->json['link'] = $this->get_link();

			// The ID.
			$this->json['id'] = $this->id;

			// The ajaxurl in case we need it.
			$this->json['ajaxurl'] = admin_url( 'admin-ajax.php' );

			// Input attributes.
			$this->json['inputAttrs'] = '';
			if ( is_array( $this->input_attrs ) ) {
				foreach ( $this->input_attrs as $attr => $value ) {
					$this->json['inputAttrs'] .= $attr . '="' . esc_attr( $value ) . '" ';
				}
			}
		}

		/**
		 * Render is handled by the JS template.
		 *
		 * @since 2.4.7
		 * @return void
		 */
		protected function render_content() {}

		/**
		 * An Underscore (JS) template for this control's content.
		 *
		 * @since 2.4.7
		 * @return void
		 */
		protected function content_template() { ?>
			<label class='botiga-sortable'>
				<span class="customize-control-title">
					{{{ data.label }}}
				</span>
				<# if ( data.description ) { #>
					<span class="description customize-control-description">{{{ data.description }}}</span>
				<# } #>

				<ul class="sortable">
					<# _.each( data.value, function( choiceID ) { #>
						<# if ( data.choices[ choiceID ] ) { #>
							<li {{{ data.inputAttrs }}} class='botiga-sortable-item kirki-sortable-item' data-value='{{ choiceID }}'>
								<i class='dashicons dashicons-menu'></i>
								<i class="dashicons dashicons-visibility visibility"></i>
								{{{ data.choices[ choiceID ] }}}
							</li>
						<# } #>
					<# }); #>
					<# _.each( data.choices, function( choiceLabel, choiceID ) { #>
						<# if ( -1 === data.value.indexOf( choiceID ) ) { #>
							<li {{{ data.inputAttrs }}} class='botiga-sortable-item kirki-sortable-item invisible' data-value='{{ choiceID }}'>
								<i class='dashicons dashicons-menu'></i>
								<i class="dashicons dashicons-visibility visibility"></i>
								{{{ data.choices[ choiceID ] }}}
							</li>
						<# } #>
					<# }); #>
				</ul>
			</label>
		<?php
		}
	}
}

/**
 * Backward-compatibility alias.
 *
 * The bundled Kirki framework (vendor-legacy/) has been removed. Older Botiga Pro
 * versions still instantiate `\Kirki\Control\Sortable` directly, so we alias that
 * legacy class name to the native control to avoid fatal errors. This is loaded
 * during customize_register at the theme's priority (before Pro adds its controls),
 * so the alias is always in place when older Pro code runs.
 *
 * New code should reference Botiga_Sortable_Control directly.
 *
 * @since 2.4.7
 */
if ( ! class_exists( 'Kirki\\Control\\Sortable', false ) ) {
	class_alias( 'Botiga_Sortable_Control', 'Kirki\\Control\\Sortable' );
}
