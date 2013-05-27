function jphp_startScript(plugin_nbr, script_nbr)
{
	// Retreive fields
	var script_args = new Object();
	
	$('.plugin'+plugin_nbr+'_script'+script_nbr).each(function()
			{
		script_args[$(this).attr("name")] = $(this).attr("value");
			});
	$("#output_"+plugin_nbr+"_"+script_nbr).html("<img src=\"tpl/common/images/loading_bar.gif\" alt=\"Loading...\" title=\"Loading...\" />");

	var accessname = $('#plugin'+plugin_nbr+'_script'+script_nbr).val();
	$.post("?Script/"+accessname, script_args, function(data)
			{
				$("#output_"+plugin_nbr+"_"+script_nbr).html(data);
			})
}

function jphp_toggleScript(plugin_nbr, script_nbr)
{
}