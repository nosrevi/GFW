<?php

require_once "GFW_init.php";

/* import the controller, which fetches all the components needed */
require_once CONTROLLER_PATH . "/controller.php";

/* draw page frame, including header, meta, javascipt ans css */
$frame->print_frame('index', true);

/* draw page */
require_once VIEW_PATH . '/index.php';

printf("</html>");
?>
