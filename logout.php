<?php

/* no cache */
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Cache-Control: no-cache");
header("Pragma: no-cache");

require_once "GFW_init.php";

/* destroy session and cookie, then redirect to index page */
GFW::delete_session();
GFW::delete_cookie();
GFW::redirect_to(HOME_PATH . '/index.php?action=logout');
?>
