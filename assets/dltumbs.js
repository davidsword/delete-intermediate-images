jQuery(document).ready(function() {
	
	// we want to make sure the user knows whats happening, no Friday-afternoon mistakes
	// only enable submit button if all 3x warnings have been aknowldged
	jQuery('#dlthumbs input.nag').click(function(){
    	show = true
    	jQuery('input.nag').each(function(){
        	if ( !jQuery(this).prop('checked') )
        		show = false;
        })
        jQuery('#dlthumbs .button-primary').prop('disabled', !show);
	})
	
	// count number of inputs, relay this number in the header as if it happened before hand
	jQuery('#dlthumbs .total_thumbnail_count').html(jQuery('#dlthumbs td input[type=checkbox]').length)
	
	// everytime a checkbox is checked, 
	const inputs = jQuery('#dlthumbs td input[type=checkbox]');
	inputs.change(pushSelectedToJSON)
	
	// turn selected inputs into JSON string for form posting
	function pushSelectedToJSON(){
		const selected = [];
		var selectedCount = 0;
		
		jQuery('#dlthumbs td input[type=checkbox]').each(function(){
    		if (jQuery(this).is(':checked')) {
        		selected.push(jQuery(this).val());	
        		selectedCount++;
        		jQuery(this).parent().parent().addClass('deleteme');
    		} else {
        		jQuery(this).parent().parent().removeClass('deleteme');
    		}
		})
		jQuery('#dlthumbs textarea[name=dlthumbs_list]').val(JSON.stringify( selected ));
		
		jQuery('#dlthumbs .selected_thumbnail_count').html(selectedCount)
	}
	
	// select all
	jQuery('#dlthumbs input[name=selectall]').click(function() {
		if (jQuery(this).prop('checked')) {
    		inputs.prop('checked', true);
		} else {
    		inputs.prop('checked', false);
		}
		pushSelectedToJSON();
	})
});