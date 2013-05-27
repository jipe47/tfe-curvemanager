<?php
function jphp_generateId($length = 10)
{
	$letters = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	
	$ids = (!empty($_SESSION['jphp_ids'])) ? unserialize($_SESSION['jphp_ids']) : array();
	$s = "";
	do
	{
		$s = str_shuffle($letters);
		$s = substr($s, 0, $length);
	}while(in_array($s, $ids));
	
	$ids[] = $s;
	$_SESSION['jphp_ids'] = serialize($ids);
	
	return $s;
}

function jphp_getSessionId()
{
	if(empty($_SESSION['cmp_sessionid']))
		$_SESSION['cmp_sessionid'] = jphp_generateId(10);
	return $_SESSION['cmp_sessionid'];
}

function jphp_getCurrentLanguage()
{
	return 1;
}

function jphp_startsWith($haystack, $needle)
{
	$length = strlen($needle);
	return (substr($haystack, 0, $length) === $needle);
}

function jphp_endsWith($haystack, $needle)
{
	$length = strlen($needle);
	$start  = $length * -1; //negative
	return (substr($haystack, $start) === $needle);
}

function array_copy($a)
{
	$b = array();
	
	foreach($a as $k => $v)
		$b[$k] = $v;
	return $b;
}

// From: http://php.net/manual/en/language.operators.comparison.php
function array_compare($op1, $op2)
{
    if (count($op1) < count($op2))
        return -1; // $op1 < $op2
    elseif (count($op1) > count($op2))
        return 1; // $op1 > $op2
    
    foreach ($op1 as $key => $val) {
        if (!array_key_exists($key, $op2))
            return null; // uncomparable
        elseif ($val < $op2[$key])
            return -1;
        elseif ($val > $op2[$key])
            return 1;
    }
    return 0; // $op1 == $op2
}

function jphp_isAdmin()
{
	return isset($_SESSION['isAdmin']) && $_SESSION['isAdmin'] > 0;
}
?>