<?php

require_once "GFW_init.php";

/* import the controller, which fetches all the components needed */
require_once CONTROLLER_PATH . "/controller.php";

/* the others modules needed */
require_once MODULE_PATH . "/customer.class.php";
require_once MODULE_PATH . "/user.class.php";
require_once MODULE_PATH . "/order.class.php";

/* draw page frame, including header, meta, javascipt ans css */
$frame->print_frame('home', true);

/* draw page */
require_once VIEW_PATH . '/home.php';

printf("</html>");
?>
