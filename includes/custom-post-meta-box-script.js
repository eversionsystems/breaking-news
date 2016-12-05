/**
 * Scripts to be loaded on the add/edit post page for setting Breaking News properties
 *
 * @requires https://github.com/trentrichardson/jQuery-Timepicker-Addon
 */

(function($) {

	/**
	 * Set the Breaking News control states on document load and create a field to set the expiry date.
	 */	
	$(document).ready(function(){
		
		$('.date-picker').datetimepicker({controlType: 'select',dateFormat: 'dd-mm-yy', timeFormat: 'HH:mm', minDate: new Date()});
		
		if($("#bn_enable").is(':checked')) {
			$('#bn_expire').attr('disabled', false);
			$('#bn_post_title').attr('disabled', false);
		}
		else {
			$('#bn_expire').attr('disabled', true);
			$('#bn_post_title').attr('disabled', true);
		}
	
		if($("#bn_expire").is(':checked')) {
			$('#bn_expire_dtm').attr('disabled', false);
		}
		else {
			$('#bn_expire_dtm').attr('disabled', true);
		}
	});
	
	/**
	 * Toggle the state of controls when the enable checkbox is clicked.
	 */
	$('#bn_enable').on( "click", function() {	
		if($("#bn_enable").is(':checked')) {
			$('#bn_expire').attr('disabled', false);
			$('#bn_post_title').attr('disabled', false);
		}
		else {
			$('#bn_expire').attr('disabled', true);
			$('#bn_post_title').attr('disabled', true);
		}
	});
	
	/**
	 * Toggle the state of controls when the expire checkbox is clicked.
	 */
	$('#bn_expire').on( "click", function() {	
		if($("#bn_expire").is(':checked')) {
			$('#bn_expire_dtm').attr('disabled', false);
		}
		else {
			$('#bn_expire_dtm').attr('disabled', true);
		}
	});
	
	/**
	 * Display a pop up datetimepicker on click.
	 */
	$('#bn_expire_dtm').on( "click", function() {
		$('#bn_expire_dtm').removeClass('missing-data');
	});
	
	/**
	 * On saving of the post ensure we have a expiry date entered if the expiry checkbox is checked.
	 * Display an alert message if the datetime is missing.
	 */
	$("input#publish").on("click", function(e) {
        var a = $(this).val();
        var c = true;
 
        var expireDtm = $('#bn_expire_dtm').val();
 
        if (expireDtm.length == 0 && $("#bn_expire").is(':checked')) {
			e.preventDefault();
			alert(bn_data_obj.stop_save_message);
			$('#bn_expire_dtm').addClass('missing-data');
			$('html, body').animate({scrollTop: $("#bn-meta-box-post").offset().top}, 400);
        }
    });
	
})(jQuery);