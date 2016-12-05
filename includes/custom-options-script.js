/**
 * Scripts to be loaded on our custom options page
 *
 */

(function( $ ) {
 
	 /**
	 * Add the WordPress color picker control to all classes named color-field
	 */
    $(function() {
        $('.color-field').wpColorPicker();
    });
     
})( jQuery );