$(document).ready(function() {
	$.get('?action=myfiles', function(data) {
		$('#content').html(data);
		$('#content').show();
		$('#navigation li.myfiles a').addClass('selected');
	});

	/* handle clicks on the menu bar */
	$("#navigation li").click(function() {
                var pageName = $(this).attr('class');

            	$("#navigation a").removeClass('selected');
                $(this).children("a").addClass('selected');

		$.get('?action='+pageName, function(data) {
			$("#content").html(data);
		});
	});
});
