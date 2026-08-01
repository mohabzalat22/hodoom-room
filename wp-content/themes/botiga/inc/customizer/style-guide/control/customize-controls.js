( function( api ) {
	'use strict';

	if ( ! api || ! api.Section ) {
		return;
	}

	api.sectionConstructor[ 'botiga-style-guide-toggle' ] = api.Section.extend( {
		attachEvents: function() {},
		isContextuallyActive: function() {
			return true;
		},
	} );
} )( wp.customize );
