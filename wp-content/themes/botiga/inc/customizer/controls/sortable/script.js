/**
 * Botiga Sortable Control.
 *
 * Behavior mirrors the previous Kirki Sortable control:
 * - drag/drop reordering via jQuery UI sortable
 * - per-item visibility toggle via the eye icon
 * - value saved as an ordered array of the enabled item keys
 *
 * @since 2.4.7
 */
wp.customize.controlConstructor['botiga-sortable-control'] = wp.customize.Control.extend({

	// When we're finished loading continue processing.
	ready: function() {

		var control = this;

		// Reuse the theme's existing sortable styling in assets/css/customizer.css
		// (.customize-control-kirki-sortable) and keep the legacy hook that external
		// customizer JS/CSS depends on. The item template also carries kirki-sortable-item.
		control.container.addClass( 'customize-control-kirki-sortable' );

		// Init sortable.
		jQuery( control.container.find( 'ul.sortable' ).first() ).sortable({

			// Update value when we stop sorting.
			update: function() {
				control.setting.set( control.getNewVal() );
			}
		}).disableSelection().find( 'li' ).each( function() {

			// Enable/disable an item when its eye icon is clicked.
			jQuery( this ).find( 'i.visibility' ).click( function() {
				jQuery( this ).toggleClass( 'dashicons-visibility-faint' ).parents( 'li:eq(0)' ).toggleClass( 'invisible' );
			});
		}).click( function() {

			// Persist the value on click.
			control.setting.set( control.getNewVal() );
		});
	},

	/**
	 * Gets the new value.
	 *
	 * @since 2.4.7
	 * @returns {Array} - The ordered list of enabled item keys.
	 */
	getNewVal: function() {
		var items  = jQuery( this.container.find( 'li' ) ),
			newVal = [];
		_.each( items, function( item ) {
			if ( ! jQuery( item ).hasClass( 'invisible' ) ) {
				newVal.push( jQuery( item ).data( 'value' ) );
			}
		});
		return newVal;
	}

});
