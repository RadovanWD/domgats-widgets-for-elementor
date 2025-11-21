( function ( $ ) {
	'use strict';

	// Placeholder for future AJAX filtering hooks.
	const domgatsWidgets = {
		init() {
			// Developers can hook into this namespace for custom behaviors.
		},
	};

	$( window ).on( 'elementor/frontend/init', () => {
		domgatsWidgets.init();
	} );
}( jQuery ) );
