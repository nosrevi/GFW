<?php

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Cache-Control: no-cache");
header("Pragma: no-cache");

require_once "GFW_init.php";

GFW::redirect_to(HOME_PATH . '/home.php');
?>
