$(document).ready(function() {
	$("select.change_view").change(function() {
		$("form.change_view").submit();
	});

        $("select.change_group").change(function() {
                $("form.change_group").submit();
        });

	/* Disable the groups if file is public on page load */
        if($('input.share_public').is(':checked')) {
   	     $('input.share_group').attr('disabled', 'disabled');
        }
	
	$("input.share_public").change(function() {
		if($('input.share_public').is(':checked')) {
			$('input.share_group').attr('disabled', 'disabled');
		}else {
			$('input.share_group').removeAttr('disabled');
		}
	});
});
