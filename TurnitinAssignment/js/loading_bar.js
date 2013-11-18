$(document).ready(function(){
	$('li.tabinactive, li.tabactive, div.refresh_link, th a').click(function() {//p#download_all_files a span, 
		$('#il_center_col').children().hide();
		$('.ilSuccessMessage').hide();
		$('.ilFailureMessage').hide();
		
		$('#loading_bar, #loading_bar_text').show();
	});
	
	$('#il_center_col form').submit(function() {
		$('#il_center_col').children().hide();
		$('.ilSuccessMessage').hide();
		$('.ilFailureMessage').hide();
		
		$('#loading_bar, #saving_bar_text').show();
	});
	
	$(".sync_link").click(function() {
		$('#il_center_col').children().hide();
		$('.ilSuccessMessage').hide();
		$('.ilFailureMessage').hide();
		
		$('#loading_bar, #synching_bar_text').show();
		
		var link = $(this).attr("title");
		$.ajax({
			type: "POST",
			url: "./"+link,
			data: "",
			success: function(msg) {
				$('#loading_bar').hide();
				$("#synching_bar_text").html(msg);
				$("#synching_bar_view_submissions").show();
			}
		});
	});
});