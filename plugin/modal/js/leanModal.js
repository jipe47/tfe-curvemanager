function leanModal_close(id)
{
	$('#lean_overlay').fadeOut(200);
	$('#modal_'+id).fadeOut(200);
}

function initJQueryLeanModal()
{
	$(".leanModal_button").leanModal({ top : 150, overlay : 0.4 });
	
	//$(".leanModal_window").resizable();
	//$(".leanModal_window").draggable();
}

function leanModal_loadWindow(id)
{
	$("#leanModal_light_title").html($("#leanModal_buffer_title_"+id).html());
	$("#leanModal_light_content").html($("#leanModal_buffer_content_"+id).html());
}
