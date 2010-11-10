$(document).ready(function() {
	$(".login label").hide();
	$(".login input").hide();

	$(".login select").change(function() {
		$(".login select option:selected").each(function () {
			var v = $(this).val();
			/* if it contains @ID@ it is OpenID 1, otherwise 
			 * OpenID 2 and we don't show identity box */
			if(v == '---') {
                                $(".login input").hide();
				$(".login label").hide();
			}else {
				if(v.indexOf('@ID@') == -1) {
					/* OpenID 2 */
					$(".login input").show();
					$(".login label").hide();
				} else {
					/* OpenID 1 */
					$(".login input").show();
					$(".login label").css('display','block');
				}
			}
		});
	});
});
