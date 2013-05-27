function refreshCaptcha()
{
	var d = new Date();
	var id = d.getSeconds()*1000+d.getMilliseconds()
	$('#captcha_img').attr('src', 'captcha.php?'+id);
}