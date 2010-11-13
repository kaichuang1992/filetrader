                                $("input[type=checkbox]").click(function() {
                                        var item = $(this).attr('value');
                                        var id = $(this).attr('class');
                                        $.post("?action=updategroups", { id: id, groupid: item, checked: $(this).is(':checked') });
                                });

				$("input[type=button]").click(function(event) {
					event.preventDefault();
					var address = $(this).prevAll("label").children("input[type=text]").attr("value");
					var id = $(this).attr('class');
					$.post("?action=emailshare", { id: id, address: address });
				});

		                $("a.delete").click(function() {
		                        var id = $(this).attr('id');
		                        var cnfrm = confirm('Are you sure you want to delete this token? The user will no longer be able to download the file!');
		                        if(cnfrm) {
		                                $.post('?action=deletetoken&id='+id, function(data) {
		                                        $.get('?action=myfiles', function(data) {
                	                	                $("#content").html(data);
	                        	                });
	                	                });
	        	                }
		                });


