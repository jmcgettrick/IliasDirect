var dvWindowTimer;
var dvWindow;

function openDvWindow(url, attributes)
{
	dvWindow = window.open(url, 'dvWindow', attributes);
	dvWindow.focus();
	dvWindowTimer = setInterval("watchDvWindowForClose()",2000);
}

function watchDvWindowForClose()
{
	if (!dvWindow || dvWindow.closed)
	{
		clearInterval(dvWindowTimer); //stop the timer
		window.location.href = window.location.href+"&refresh_submissions=1&cmd=showLoadRedirectSubmissions";
	}
}