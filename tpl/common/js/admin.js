function adminShowSubPanel(name)
{
	$("#admin_subpanels .panel").hide();
	$("#"+name).show();
}

function adminInitScript()
{
	$(".script_name").click(
		function()
		{
			$(this).siblings(".script_content").toggle("slow");
			$("img", this).toggle();
		});
}