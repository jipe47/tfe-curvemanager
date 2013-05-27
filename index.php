<?php
/**
 * Access Page.
 * @author Jean-Philippe Collette
 * @package Core
 */

session_start();
require_once "config/init.php";

$query_string = $_SERVER['QUERY_STRING'];

// Remove potentiel $_GET var from JPHP url
if(count($_GET) > 0)
	$query_string = urldecode(array_shift(array_keys($_GET)));

if($query_string == "")
	$query_string = JPHP::get("default_page");
$array_query = explode(JPHP::ARG_SEPARATOR, $query_string);
JPHP::setArg($array_query);
/* If an identification cookies exist, redirect the user to the appropriate page. */
if(!jphp_isAdmin() && !empty($_COOKIE[STRUCTURE_NAME.'_login']) && !empty($_COOKIE[STRUCTURE_NAME.'_password']) 
	&& (JPHP::argc() == 0 || (JPHP::argc() > 0 && JPHP::arg(0) != "Request")))
	JPHP::setArg(array_merge(array('Request', 'jphp', 'login_cookie'), $array_query));

$urlAnalyze = JPHP::parseArg(true);
try
{
	$class = PageRegister::getPageInstance($urlAnalyze['page'], $urlAnalyze['type']);
	$class->setObjectName($urlAnalyze['page']);
}
catch(Exception $e)
{
	//$class = new HttpError(404);
	$class = new Error("32", "Not found: ".$urlAnalyze['page']." (".$urlAnalyze['type'].").");
}

if(Post::boolValue("fullrender", "false"))
	$class->setFullRender(false);

if(Post::boolValue("showheaders", "false"))
	$class->showHeaders(false);

$render = $class->render();

if($class->hasLocation())
{
	$external = strpos($class->getLocation(), "http") !== false;
	$location = $external ? $class->getLocation() : "?".$class->getLocation();
	header("Location: ".$location);
}		
else if(!empty($class))
{
	//echo $render;
	// Compression
	
	
	// 1. Removing useless characters
	
	// From http://www.php.net/manual/en/function.ob-start.php#71953
	function sanitize_output($buffer)
	{
		$search = array(
				'/\>[^\S ]+/s', //strip whitespaces after tags, except space
				'/[^\S ]+\</s', //strip whitespaces before tags, except space
				'/(\s)+/s'  // shorten multiple whitespace sequences
		);
		$replace = array(
				'>',
				'<',
				'\\1'
		);
		$buffer = preg_replace($search, $replace, $buffer);
		return $buffer;
	}
	
	//$render = sanitize_output($render);
	
	
	// 2. Gzip compression (if available), from http://www.php.net/manual/en/function.gzcompress.php#88044
	/*
    $HTTP_ACCEPT_ENCODING = $_SERVER["HTTP_ACCEPT_ENCODING"];
    if(headers_sent())
    	$encoding = false;
	if( strpos($HTTP_ACCEPT_ENCODING, 'x-gzip') !== false )
        $encoding = 'x-gzip';
    else if( strpos($HTTP_ACCEPT_ENCODING,'gzip') !== false )
        $encoding = 'gzip';
    else
        $encoding = false;
    if($encoding)
    {
        $_temp1 = strlen($render);
        if ($_temp1 < 2048)    // no need to waste resources in compressing very little data
            echo $render;
        else
        {
            header('Content-Encoding: '.$encoding);
           echo "\x1f\x8b\x08\x00\x00\x00\x00\x00";
            $contents = gzcompress($render, 9);
            $contents = substr($contents, 0, $_temp1);
            echo $contents;
        }
    }
    else*/
        echo $render;
       
}
?>
