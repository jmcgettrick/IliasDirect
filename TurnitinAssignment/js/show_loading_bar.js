$(document).ready(function(){
	$('#loading_bar').show();

	switch ($('#redirect_bar_link').html()) {

		case "showSubmissions":
		case "showDetails":
			$('#loading_bar_text').show();
			break;

		case "editSettings":
			$('.ilSuccessMessage').hide();
			$('#saving_bar_text').show();
			break;
	}

	window.location.href = window.location.href+"&cmd="+$('#redirect_bar_link').html();
});