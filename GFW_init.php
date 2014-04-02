<?php

session_start();
require_once "global_config.php";
require_once MODULE_PATH . "/gfw.core.php";

// TODO this work should not be done here, but in .htaccess
$_SERVER['PHP_SELF'] = strtolower($_SERVER['PHP_SELF']);

if( ! ini_get('date.timezone') )
{
    date_default_timezone_set('GMT');
}

// checking session and cookie
if (GFW::check_session() || GFW::check_cookie()) {
    // if current page is index.php, redirect to home page
    if ($_SERVER['PHP_SELF'] == HOME_PATH . '/index.php') {
        GFW::redirect_to(HOME_PATH . '/home.php');
    }
	else{
	}
}
// not logged in yet... at index.php but do not redirect
else if ($_SERVER['PHP_SELF'] == HOME_PATH . '/index.php' &&
        ((!$_POST && !$_GET) ||
        isset($_REQUEST['error']) ||
        (isset($_REQUEST['action']) && $_REQUEST['action'] == 'logout'))) {
    
}
// verify identity
else {
	GFW::check_valid();
}
	
