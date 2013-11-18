$(document).ready(function(){
	if ($("#submission_format").val() == 1)
	{
		$("label[for=paper]").hide();
		$("#paper").hide();
		$("#paper").siblings().hide();
	}
	
	if ($("#submission_format").val() == 2)
	{
		$("label[for=paper_text]").hide();
		$("#paper_text").hide();
	}
	
	$("#submission_format").change(function() {
		if ($("#submission_format").val() == 1)
		{
			$("label[for=paper]").hide();
			$("#paper").hide();
			$("#paper").siblings().hide();
			
			$("label[for=paper_text]").show();
			$("#paper_text").show();
		}
		else
		{
			$("label[for=paper]").show();
			$("#paper").show();
			$("#paper").siblings().show();
			
			$("label[for=paper_text]").hide();
			$("#paper_text").hide();
		}
	});
});
