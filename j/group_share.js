                                $("input[type=checkbox]").click(function() {
                                        var item = $(this).attr('value');
                                        var id = $(this).attr('class');
                                        $.post("?action=updategroups", { id: id, groupid: item, checked: $(this).is(':checked') });
                                });
