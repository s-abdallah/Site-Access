<?php
// this script will init the ability to use the CMS on front-end pages
// let's check and set the root path of the site so that everything links up correctly
$root = "sitecontrol/";

$basePath = (strpos($_SERVER['REQUEST_URI'], $root)) ? "" : $root;
// $basePath = (strpos($_SERVER['REQUEST_URI'],$root)?"":$root);

// only call in the class that we need to use to gather up data from the CMS
require_once $basePath . "scripts/php/db.php";
