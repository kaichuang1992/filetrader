$(document).ready(function() {
        $("select.change_group").change(function() {
                $("form.change_group").submit();
        });

	$(".fileinfo img").click(function() {
		$(this).parent().next().children(".showView").toggle();
		$(this).parent().next().children(".editView").toggle();
	});

});
