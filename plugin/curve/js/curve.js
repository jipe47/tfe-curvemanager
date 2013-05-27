function adminDisplayNewCurve()
{
	var d = new Date();
	var values = ["timestamp="+d.getTime()];
	
	$(".gen_value").each(function(){
		var t = $(this);
		var value = t.val();
		var name = t.attr('name');
		if(name == "gen_f")
			value = value.replace("+", "ppp");
		values.push(name+"="+value);
		
	});
	//$("#debug").html(values.join("&"));
	$("#preview").html('<img src="curveDisplay.php?mode=function&amp;'+values.join("&")+'" />');
}

function adminDisplayPartialCurve()
{
	var d = new Date();
	
	var ys = $("#missing_ys").val();
	var id_curve = $("#id_curve").val();
	var prediction = $("#prediction").val();
	
	var missing_choice = $("input[name='missing_choice']:checked").val();
	var x = "";
	if(missing_choice == "x")
		x = $("#missing_x").val();
	else
		x = Number($("#missing_nbr").val()) + Number($("#x_last").val());
	var values = ["timestamp="+d.getTime(), "id_curve="+id_curve, "ys="+ys, "x="+x, "prediction="+prediction];
	
	$("#preview").html('<img src="curveDisplay.php?mode=partial&amp;'+values.join("&")+'" />');
}

function adminCurveDisplayTab(id_zone, tab)
{
	$('.tab_zone_'+id_zone).removeClass('curve_tab_show');
	$('.tab_zone_'+id_zone).addClass('curve_tab_hidden');
	
	$('#zone_'+id_zone+'_'+tab).removeClass('curve_tab_hidden');
	$('#zone_'+id_zone+'_'+tab).addClass('curve_tab_show');
	
	$('.zone_'+id_zone+'_tabs li').removeClass('selected');
	$('#zone_'+id_zone+'_tab_'+tab).addClass('selected');
}