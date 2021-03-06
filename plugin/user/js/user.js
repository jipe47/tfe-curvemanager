// From http://blog.akoo.be/2008/06/in_array-en-javascript/
Array.prototype.inArray = function(p_val) {
    var l = this.length;
    for(var i = 0; i < l; i++) {
        if(this[i] == p_val) {
            return true;
        }
    }
    return false;
}

// From http://stackoverflow.com/questions/3079385/str-shuffle-equivalent-in-javascript
function shuffle(string) {
    var parts = string.split('');
    for (var i = parts.length; i > 0;) {
        var random = parseInt(Math.random() * i);
        var temp = parts[--i];
        parts[i] = parts[random];
        parts[random] = temp;
    }
    return parts.join('');
}

// http://www.devkb.org/javascript/33-Fonction-trim-en-JavaScript-pour-enlever-les-espaces-de-debut-et-de-fin-de-chaine
function trim(sString) {
    while (sString.substring(0,1) == ' ' || sString.substring(0,1) == '\t' || sString.substring(0,1) == '\r' || sString.substring(0,1) == '\n')
		sString = sString.substring(1, sString.length);
    while (sString.substring(sString.length-1, sString.length) == ' ' || sString.substring(sString.length-1, sString.length) == '\t' || sString.substring(sString.length-1, sString.length) == '\r' || sString.substring(sString.length-1, sString.length) == '\n')
		sString = sString.substring(0,sString.length-1);
    return sString;
}


/*
 * Displays an admin panel. It is redundant with other JavaScript functions in
 * order to avoid dependances
 */
function userShowSubPanel(name)
{
	$("#admin_subpanels div").hide();
	$("#"+name).show();
}

/************************/
/** User add/edit form **/
/************************/

/*
 * Generates a random string.
 */
function userGeneratePassword()
{
	var pass = shuffle("abcdefghijklmnopqrstvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789");
	pass = pass.substr(0, 6);
	$("#pass1").val(pass);
	$("#pass2").val(pass);
	$("#pass_generated").val(pass);
}

/*
 * Checks if a login is not empty or too short and unique.
 */
function userVerifyLogin(ignore, fieldname)
{
	
	if(fieldname == undefined)
		fieldname = 'login';
	
	var login = $('#'+fieldname).val();
	if(ignore != false && login == ignore)
	{
		$("#"+fieldname).css('backgroundColor', "#B0F2B6");
		return;
	}	
	
	if(login == "")
	{
		return;
	}
	else if(login.length < 3)
	{
		$("#login_status").html('<span style="color: red;font-weight: bold">Too small</span>');
		$("#"+fieldname).css('backgroundColor', "#E9383F");
		return;
	}
	$("#login_status").html('<em>Checking...</em>');

	$.post('?Ajax/User/verifylogin', {login: login}, 
	function(data)
	{
		var color;
		//$("#div_debug").html(data);
		if(data == 1)
		{
			//$("#login_status").html("Ok");
			color = "#B0F2B6";
			$("#login_status").html('<span style="color: green;font-weight: bold">Ok</span>');
		}
		else
		{
			//$("#login_status").html("Already used");
			color = "#E9383F";
			$("#login_status").html('<span style="color: red;font-weight: bold">Already in use</span>');

		}
		$("#"+fieldname).css('backgroundColor', color);
	});
}

/*
 * Checks if two passwords are different.
 */
function userVerifyPass()
{
	var p1 = $("#pass1").val();
	var p2 = $("#pass2").val();
	
	var green = "#B0F2B6";
	var red = "#E9383F";
	
	if(p1 == p2 && p1 != "")
	{
		$('#pass1').css('backgroundColor', green);
		$('#pass2').css('backgroundColor', green);
		$("#pass_status").html('<span style="color: green;font-weight: bold">Ok</span>');
	}
	else
	{
		$('#pass1').css('backgroundColor', red);
		$('#pass2').css('backgroundColor', red);
		$("#pass_status").html('<span style="color: red;font-weight: bold">Different/empty</span>');
	}
}

/*
 * Adds an organisation.
 */
function userAddOrganisation()
{
	var name = $('#org_name').val();

	$.post('?Ajax/UserAjax/addorg', {name: name}, 
	function(data)
	{
		data = trim(data);
		var status = "";
		var ok = false;
		if(data == -1)
			status = 'Organisation already exists.';
		else if(data == 0)
			status = 'Error while inserting organisation.';
		else
		{
			ok = true;
			status = 'Organisation added.';
			
			// Insert in organisation array
			var html = '<tr><td><input type="checkbox" id="org_'+data+'" name="organisations[]" value="'+data+'"/></td>';
			html += '<td><label for="org_'+data+'">'+name+'</label></td>';
			html += '<td><input type="text" name="job_'+data+'" /></td>';
			html += '<td><input type="checkbox" value="'+data+'" name="contact[]"/></td>';
			html += '<td><input type="checkbox" value="'+data+'" name="substitute[]"/></td></tr>';
			$('#tab_org').append(html);
			
			$('#org_name').val('');
		}
		
		if(ok)
			status = '<span style="font-weight: bold; color: green;">'+status+'</span>';
		else
			status = '<span style="font-weight: bold; color: red;">'+status+'</span>';
		
		$('#org_status').html(status);
	});
}


/*****************/
/** User Search **/
/*****************/

/*
 * Performs a user search, by avoiding one or more ids.
 */
function userSearch()
{
	var search = $('#user_search_search').val();
	var id_avoid = $('#user_search_avoid').val();

	$.post('?Ajax/UserAjax/search', {search: search, id_avoid: id_avoid},
			function(data)
			{
				$('#user_search_result').html(data);
				var ids = $('#array_result_id').val().split(';');
				var ids_selected = $('#array_selected_user').val().split(';');
				for(var i = 0 ; i < ids.length ; i++)
				{
					if(ids[i] == "")
						continue;
					
					if(ids_selected.inArray(ids[i]))
						$('#checkbox_user_'+ids[i]).attr('checked', true);
				}
				});
}

/*
 * Selects a user who has been found by a search.
 */
function userSearchSelect(id)
{
	var checked = $('#checkbox_user_'+id).attr("checked");
	var ids = $('#array_selected_user').val().split(";");
	
	if(checked)
		ids.push(id);
	else
		ids.splice(ids.indexOf(id), 1);
	$('#array_selected_user').val(ids.join(';'));
	refreshSelectedId();
}

/*
 * Refreshes the list of selected ids.
 */
function refreshSelectedId()
{
	var ids = $("#array_selected_user").val();
	
	$.post('?Ajax/UserAjax/showmember', {users: ids},
			function(data)
			{
				$('#div_selected_user').html(data);
			}
			);
}