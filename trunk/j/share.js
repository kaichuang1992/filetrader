                                $("input[type=checkbox]").click(function() {
                                        var item = $(this).attr('value');
                                        var id = $(this).attr('class');
                                        $.post("?action=updategroups", { id: id, groupid: item, checked: $(this).is(':checked') });
                                });

				$("input[type=button]").click(function(event) {
					event.preventDefault();
					var address = $(this).prevAll("label").children("input[type=text]").attr("value");

					//var address = 'gaap@gaap.com';
					var id = $(this).attr('class');
					$.post("?action=emailshare", { id: id, address: address });
				});

