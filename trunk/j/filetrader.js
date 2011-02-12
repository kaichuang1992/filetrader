$(document).ready(function() {
	$("select.change_view").change(function() {
		$("form.change_view").submit();
	});

        $("select.change_group").change(function() {
                $("form.change_group").submit();
        });
});
