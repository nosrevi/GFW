<?php

/* no cache */
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Cache-Control: no-cache");
header("Pragma: no-cache");

require_once "GFW_init.php";

/* redirect to listener */
require_once CONTROLLER_PATH . "/listener.php";
?>
