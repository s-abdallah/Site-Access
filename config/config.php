<?php
/************************************************************
config.php

This file contains some basic configuration and checks that
will be automatically included into every file of the site
through the base .htaccess file

 ************************************************************/

/***** DO NOT EDIT  ****************************************/
if (session_status() != PHP_SESSION_ACTIVE) {
    session_start(); // if the session does not exist, start it now before we do anything else
}
/***********************************************************/

/***** GLOBAL VARIABLE DEFINITIONS *************************/
define("PROJECTNAME", ""); // the name of this project
define("DASHLOGMAX", 5); // how many log items to show on the dashboard page
define("ADMINEMAIL", ""); // email address of admin to receive security warnings etc.
define("TESTMODE", true); // whether or not we are in test mode which will remove login requirement, etc.
define("NOTIFYNEWUSERS", false); // send users an email when their account has been created
define("ALLOWWYSIWYG", true); // allow extra controls on certain text areas fields within forms
define("USESOCIAL", false); // will include the social class for access to certain features
define("SUPPORTEMAIL", ""); // the email address for all support and error emails
define("LOGMAXLENGTH", 100); // max length of log records
define("USEMAPS", true);
define("PAGIITEMS", 25); // define how many items is going to be in each page.
define("MAPSAPIKEY", "");

$useImportOn = ["availabilities"]; // needs to match the name of the tool
$useMapsOn = ["offices", "properties", "communities", "neighborhoods"]; // needs to match the name of the tool

// some other email configuration for lost passwords, etc.
define("EMAIL_DEBUG", 0); // 0 = off / 1 = on
define("EMAIL_FROMNAME", "");
define("EMAIL_FROMEMAIL", "");
define("EMAIL_USESMTP", true); // if true, fill in the next few items
define("EMAIL_SMTPHOST", "smtp.gmail.com");
define("EMAIL_SMTPPORT", 587);
define("EMAIL_SMTPAUTH", true);
define("EMAIL_SMTUSER", "");
define("EMAIL_SMTPPASS", "");
define("EMAIL_SMTPSECURE", "tls");
/***********************************************************/

/***** DO NOT EDIT BELOW THIS LINE *************************/

define("APPVER", "3.5.2"); // version of siteaccess core

// read into arrays and set up a global var for both: uploads.json and tools.json
//define("UPLOADS_CONFIG",null);
//$_UPLOADS_CONFIG = array("","","");

// let's make sure that we have a user logged in if we are NOT in testmode above
if (!TESTMODE) { // we are NOT in test mode
    if (!isset($_SESSION['userid']) || $_SESSION['userid'] === "") {
        // force the user to the login screen
        if (basename($_SERVER['REQUEST_URI']) !== "login.php" && basename($_SERVER['REQUEST_URI']) !== "forgot.php") { // make sure that we are not on the login/reset page
            header("location:login.php");
        }
    }
} else { // test mode, go anywhere do anything
    $_SESSION['userid'] = "4438bfa741090c5ae1bdf5e29c2712cd"; // default id of neoscape developer (should not be removed)
}

// let's check and set the root path of the site so that everything links up correctly
$root = "siteaccess/";
$basePath = (strpos($_SERVER['REQUEST_URI'], $root) ? "" : $root);

// call in the required DB class(es) so that we can actually do something when and where we need to
// I may want to look at splitting this up and ONLY including certain classes when they are needed and not all the time?
require_once $basePath . "scripts/php/db.php";
require_once $basePath . "scripts/php/email.php";
require_once $basePath . "scripts/php/logs.php"; // extends DB
require_once $basePath . "scripts/php/tools.php"; // extends DB
require_once $basePath . "scripts/php/media.php"; // extends DB
require_once $basePath . "scripts/php/forms.php"; // extends DB
require_once $basePath . "scripts/php/users.php"; // extends DB
//if(USESOCIAL){  // ony include social if active in settings
//require_once($basePath."scripts/php/social.php");  // extends DB
//}
