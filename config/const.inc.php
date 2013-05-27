<?php
/**
 * This file defines basic constants for the proper functionning of the
 * framework.
 * 
 * @author Jean-Philippe Collette
 */

define("PATH_CONFIG", dirname(__FILE__)."/");
define("PATH", PATH_CONFIG."../");

define("PATH_TPL", "tpl/");
define("PATH_TPL_COMMON", "tpl/common/");
define("PATH_PLUGIN", "plugin/");
define("PATH_UPLOAD", "upload/");
define("PATH_CACHE", "cache/");

define("EOL", "\r\n");
define("TAB", "\t");

// Définit si le mode de debug est par défaut activé ou non
define("DEBUG", 0);
define("ALL_ACCESS", 0);

define("STRUCTURE_NAME", "tfe");

define("DEFAULT_LANGUAGE", 7);

define("TPL", PATH_TPL."default/");

define("URL_SITE", "http://localhost:8080/tfe/");
define("EMAIL", "noreply@wegift.com");

define("DATE_FORMAT", "%d/%m/%Y");
define("TIME_FORMAT", "%H:%M");

define("FORCE_CACHEREBUILD", 1);

/*******************/
/* MySQL CONSTANTS */
/*******************/

define("FORCE_LOCAL", 0);
define("FORCE_ONLINE", 0);

define("SQL_PREFIX", "");
define("CONSTANT_PREFIX", "TABLE_");


/***************************/
/* CURVE MANAGER CONSTANTS */
/***************************/

define("CM_ENABLE_REQUEST", 0);
define("CM_ENABLE_ACCOUNTRESET", 0);
/*
if(!CM_ENABLE_REQUEST)
{
	curvemanagerShowLockMessage();
	return;
}
*/
function curvemanagerShowLockMessage()
{
	out::message("Administration operations have been disabled.", Message::INFO);
}
?>
