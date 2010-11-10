                $("a.share").click(function() {
                        var id = $(this).attr('id');
			var data = $("#"+id).nextAll("ul.groups").html();
			if(data == '') {
	                        $.get('?action=share&id='+id, function(data) {
        	                        $("#"+id).nextAll("ul.groups").html(data);
	                        });
			}else {
				$("#"+id).nextAll("ul.groups").html('');
			}
                });

		$("a.delete").click(function() {
                        var id = $(this).attr('id');
			var cnfrm = confirm('Are you sure you want to delete this file?');
			if(cnfrm) {
				$.post('?action=delete&id='+id, function(data) {
					$.get('?action=myfiles', function(data) {
						$("#content").html(data);
					});	
                                });
			}
		});

