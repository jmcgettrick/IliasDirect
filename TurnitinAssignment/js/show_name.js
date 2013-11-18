$(document).ready(function(){
	
	$(".launch_popup_anon_form").click(function() {
		$(".popup_anon_form").show();
		
		$("#paper_id").val($(this).attr("id"));
	});
	
	
	$("#anon_popup_close").click(function() {
		$(".popup_anon_form").hide();
	});
	
});