<?php

/* 	Almost every dynamic generated elements, including tables. graphics and links  
 * 	are created by Drawer.
 */

class DRAWER {

	// a copy of request in Drawer scope
    static $request;

    // the max # of paginations displayed in nav bar
    static $max_pages_in_nav = 14;

    static $action;

	// modify the requset in the Drawer scope
    static function set_request($_req) {
        self::$request = $_req;
        self::$action = $_req['action'];
    }

	// draw the welcome word at home page
    static function hello_word() {
        require_once MODULE_PATH . "/user.class.php";
        global $_user;
        echo "Hello ";
        $count = 0;
        foreach ($_user->get_customers() as $key => $customer) {
            ++$count;
            echo '<span class="red bold">' . $customer->get_firstname() . '</span>';
            if (( $user_num = $_user->get_customers_num() ) > 1) {
                if ($count == $user_num - 1) {
                    echo " and ";
                } else if ($count <= $user_num - 1) {
                    echo ", ";
                }
            }
        }
        echo ", My iPad2 relies on you!";
    }

    /* 
	 * TODO parent class call sub-class, I dont think the way using eval() is correct	
	 * get the msg. every ajax request is a msg, a combination of parameters, for example
	 * "action=histry&row=12&page=1"
	 * this function combine a requset into an array
	 */
    static function get_msg($_change = NULL) {
        eval('$str = ' . strtoupper(self::$action) . '_DRAWER::get_msg($_change);');
        return $str;
    }

	// get the sorting part of nav bar, such as sort by date, sort by payment
    static function get_sorting() {
        eval('$str = ' . strtoupper(self::$action) . '_DRAWER::get_sorting();');
        return $str;
    }

	/*	get paginate navigation bar
	 *	$_num = the num of items for pagination
	 */
    static function get_page_nav($_num) {
		// the num of pages
        $page_num = floor(($_num - 1) / self::$request['row'] + 1);
        $page_nav = "";
        $page_list = self::get_page_list(self::$request['page'], $page_num);
        for ($page = 1; $page <= $page_num; $page++) {
            if (self::$request['page'] == $page) {
                $page_nav .= '<span>' . $page . '</span>';
            } else if ($page == $page_num && self::$request['page'] != $page_num) {
                if ($page_list[$page] == 'Last' && self::$request['page'] > 0) {
                    $page_nav .= '<a link="#' . self::get_msg(array('page' => (self::$request['page'] + 1))) . '">Next</a><a link="#' . self::get_msg(array('page' => $page)) . '">' . $page_list[$page] . '</a>';
                } else if (self::$request['page'] > 0) {
                    $page_nav .= '<a link="#' . self::get_msg(array('page' => $page)) . '">' . $page_list[$page] . '</a><a link="#' . self::get_msg(array('page' => (self::$request['page'] + 1))) . '">Next</a>';
                } else {
                    $page_nav .= '<a link="#' . self::get_msg(array('page' => $page)) . '">' . $page_list[$page] . '</a>';
                }
            } else if ($page_list[$page]) {
                $page_nav .= '<a link="#' . self::get_msg(array('page' => $page)) . '">' . $page_list[$page] . '</a>';
            }
        }
        if (self::$request['page'] > 0) {
            $link = self::get_msg(array('page' => -1));
            $page_nav .= '<a link="#' . $link . '" >All</a>';
        } else {
            $page_nav .= '<span>All</span>';
        }
        return '<div class="' . self::$action . '_page_nav_border bg_white"><div class="' . self::$action . '_page_nav">' . self::get_sorting() . 'Pages: ' . $page_nav . '</div></div>';
    }

    /*	this funtion returns an array indicating what the nav bar should show
	 *	$_page = the current page;  $_pages = how mant pages
	 */
    static function &get_page_list($_page, $_pages) {
        $page_arr = array();
        for ($i = 1; $i <= $_pages; $i++) {
            $page_arr[$i] = $i;
        }
        if ($_pages <= self::$max_pages_in_nav) {
            return $page_arr;
        } else {
            for ($i = 1; $i <= $_pages; $i++) {
                $page_arr[$i] = false;
            }
            $page_arr[1] = 'First';
            $page_arr[$_pages] = 'Last';
            if ($_page - self::$max_pages_in_nav / 2 >= 1 && $_pages - self::$max_pages_in_nav / 2 >= $_page) {
                for ($i = $_page - self::$max_pages_in_nav / 2; $i <= $_page + self::$max_pages_in_nav / 2; $i++) {
                    $page_arr[$i] = $i;
                }
            } else if ($_page <= self::$max_pages_in_nav / 2) {
                for ($i = 1; $i <= self::$max_pages_in_nav; $i++) {
                    $page_arr[$i] = $i;
                }
            } else {
                for ($i = $_pages - self::$max_pages_in_nav; $i <= $_pages; $i++) {
                    $page_arr[$i] = $i;
                }
            }
            return $page_arr;
        }
    }

    static function get_color($_index) {
        switch ($_index % 5) {
            case 0:
                return 'pink';
            case 1:
                return 'orange';
            case 2:
                return 'yellow';
            case 3:
                return 'green';
            case 4:
                return 'blue';
            case 5:
                return 'violet';
        }
    }

}

?>
