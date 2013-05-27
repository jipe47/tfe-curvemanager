function debug_toggle(id)
{
	$("#debug_"+id).toggle();
	$("#button_"+id+"_white").toggle();
	$("#button_"+id+"_orange").toggle();
}
function debug_init()
{
	//$("#debugger .panel").draggable();
	//$("#debugger .panel").resizable();
}