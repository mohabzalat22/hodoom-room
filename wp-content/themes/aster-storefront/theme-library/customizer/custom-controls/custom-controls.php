<?php
/**
 * Customizer Custom Controls
 */

if ( class_exists( 'WP_Customize_Control' ) ) {

	/**
	 * Toggle Switch Custom Control
	 */
	class Aster_Storefront_Toggle_Switch_Custom_Control extends WP_Customize_Control {
		public $type = 'toggle_switch';
		public function render_content() {
			?>
			<div class="toggle-switch-control">
				<div class="toggle-switch">
					<input type="checkbox" id="<?php echo esc_attr( $this->id ); ?>" name="<?php echo esc_attr( $this->id ); ?>" class="toggle-switch-checkbox" value="<?php echo esc_attr( $this->value() ); ?>" 
					<?php
						$this->link();
						checked( $this->value() );
					?>
					>
					<label class="toggle-switch-label" for="<?php echo esc_attr( $this->id ); ?>">
						<span class="toggle-switch-inner"></span>
						<span class="toggle-switch-switch"></span>
					</label>
				</div>
				<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
				<?php if ( ! empty( $this->description ) ) { ?>
					<span class="customize-control-description"><?php echo esc_html( $this->description ); ?></span>
				<?php } ?>
			</div>
			<?php
		}
	}

	/**
	 * Separator/Heading Custom Control
	 */
	class Aster_Storefront_Separator_Custom_Control extends WP_Customize_Control {
		public $type = 'separator';
		public function render_content() {
			?>
			<div class="separator-control">
				<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
				<hr />
			</div>
			<?php
		}
	}

	class Aster_Storefront_Image_Radio_Control extends WP_Customize_Control {

		public function render_content() {
			if (empty($this->choices)) return;
	
			$aster_storefront_name = '_customize-radio-' . $this->id;
			?>
			
			<span class="customize-control-title"><?php echo esc_html($this->label); ?></span>
		   
			<ul class="controls" id='aster-storefront-custom-container'>
				<?php
				
				foreach ($this->choices as $aster_storefront_value => $aster_storefront_label) :
					
					$aster_storefront_class = ($this->value() == $aster_storefront_value) ? 'aster-storefront-selected-img aster-storefront-selector-img ' : 'aster-storefront-selector-img';
					?>
					
					<li style="display: inline;">
						<label>
							<input <?php $this->link(); ?> style='display:none' type="radio" value="<?php echo esc_attr($aster_storefront_value); ?>" name="<?php echo esc_attr($aster_storefront_name); ?>" <?php
								  $this->link();
								  checked($this->value(), $aster_storefront_value);
								  ?> />
	
							<img src='<?php echo esc_url($aster_storefront_label); ?>' class='<?php echo esc_attr($aster_storefront_class); ?>' />
						</label>
					</li>
					<?php
				endforeach;
				?>
			</ul>
	
			<script type="text/javascript">
				(function($) {
					$(document).ready(function() {
						$('#aster-storefront-custom-container img').on('click', function() {
							var $this = $(this);
							var input = $this.prev('input');
							var inputName = input.attr('name');
	
							// Remove the 'aster-storefront-selected-img' class from all images
							$('#aster-storefront-custom-container img').removeClass('aster-storefront-selected-img');
	
							// Add the 'aster-storefront-selected-img' class to the clicked image
							$this.addClass('aster-storefront-selected-img');
	
							// Set the input as checked
							input.prop('checked', true).trigger('change');
	
							// Optionally: Update the WordPress Customizer to reflect the change
							wp.customize.control(inputName).setting.set(input.val());
						});
					});
				})(jQuery);
			</script>
			<?php
		}
	}

	// Add Aster_Storefront_Customize_Range_Control
	class Aster_Storefront_Customize_Range_Control extends WP_Customize_Control {
		public $type = 'aster-storefront-range-slider';

		public function to_json() {
			if ( ! empty( $this->setting->default ) ) {
				$this->json['default'] = $this->setting->default;
			} else {
				$this->json['default'] = false;
			}
			parent::to_json();
		}

		public function enqueue() {
			wp_enqueue_script( 'aster-storefront-range-slider', get_template_directory_uri() . '/resource/js/range-control.js', array( 'jquery' ), '', true );
			wp_enqueue_style( 'aster-storefront-range-slider', get_template_directory_uri() . '/resource/css/range-control.css' );
		}

		public function render_content() {
			?>
			<label>
				<?php if ( ! empty( $this->label ) ) : ?>
					<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
				<?php endif;
				if ( ! empty( $this->description ) ) : ?>
					<span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span>
				<?php endif; ?>
				<div id="<?php echo esc_attr( $this->id ); ?>">
					<div class="aster-storefront-range-slider">
						<input class="aster-storefront-range-slider-range" type="range" value="<?php echo esc_attr( $this->value() ); ?>" <?php $this->input_attrs(); $this->link(); ?> />
						<input class="aster-storefront-range-slider-value" type="number" value="<?php echo esc_attr( $this->value() ); ?>" <?php $this->input_attrs(); $this->link(); ?> />
						<?php if ( ! empty( $this->setting->default ) ) : ?>
							<span class="aster-storefront-range-reset-slider" title="<?php esc_attr_e( 'Reset', 'aster-storefront' ); ?>"><span class="dashicons dashicons-image-rotate"></span></span>
						<?php endif;?>
					</div>
				</div>
			</label>
			<?php
		}
	}

}

if ( class_exists( 'WP_Customize_Section' ) ) {
	/**
	 * Upsell section
	 */
	class Aster_Storefront_Upsell_Section extends WP_Customize_Section {
		/**
		 * The type of control being rendered
		 */
		public $type = 'aster-storefront-upsell';

		/**
		 * The Upsell button text
		 */
		public $button_text = '';

		/**
		 * The Upsell URL
		 */
		public $url = '';

		/**
		 * The background color for the control
		 */
		public $aster_storefront_background_color = '';

		/**
		 * The text color for the control
		 */
		public $aster_storefront_text_color = '';

		/**
		 * Render the section, and the controls that have been added to it.
		 */
		protected function render() {
			$aster_storefront_background_color = ! empty( $this->background_color ) ? esc_attr( $this->background_color ) : '#fff';
			$aster_storefront_text_color       = ! empty( $this->text_color ) ? esc_attr( $this->text_color ) : '#50575e';
			?>
			<li id="accordion-section-<?php echo esc_attr( $this->id ); ?>" class="aster_storefront_upsell_section accordion-section control-section control-section-<?php echo esc_attr( $this->id ); ?> cannot-expand">
				<h3 class="accordion-section-title" style="color:#000; background:<?php echo esc_attr( $aster_storefront_background_color ); ?>;border-left-color:<?php echo esc_attr( $aster_storefront_background_color ); ?>;">
					<?php echo esc_html( $this->title ); ?>
					<a href="<?php echo esc_url( $this->url ); ?>" class="button button-secondary alignright" target="_blank" style="margin-top: -4px;"><?php echo esc_html( $this->button_text ); ?></a>
				</h3>
			</li>
			<?php
		}
	}
}