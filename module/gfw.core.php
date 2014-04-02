<?php

require_once "GFW_init.php";

class GFW {

    //standard query function		
    static function query($query) {
        return mysql_query($query);
    }

    // standard query function		
    static function fetch($res) {
        return mysql_fetch_assoc($res);
    }

    // standard rows counter
    static function count($res) {
        return mysql_num_rows($res);
    }

    //standard quote function
    static function quote($string) {
        if (get_magic_quotes_gpc()) {
            $quoted_string = stripslashes($string);
        } else {
            $quoted_string = $string;
        }
        return "'" . mysql_real_escape_string($quoted_string) . "'";
    }

    static function error($_err, $_dest = ERROR_LOG) {
        error_log(date(DATE_RFC822) . "\n" . $_err . "\n\n", 3, $_dest);
    }

    static function mem() {
        printf(' memory usage: %01.2f MB', memory_get_usage() / 1024 / 1024);
    }

    static function is_empty($_result, $_query) {
        if (!$_result) {
            self::error("The query maybe illegal\n" . $_query);
            return true;
        } else if (self::count($_result) == 0) {
            //self::error("The result of the query is 0\n".$_query);
            return true;
        }
        return false;
    }

    static function array_sort(&$array, $order = SORT_ASC) {
        $new_array = array();
        $sortable_array = array();

        if (count($array) > 0) {
            foreach ($array as $k => $v) {
                $sortable_array[$k] = $v;
            }

            switch ($order) {
                case SORT_ASC:
                    asort($sortable_array);
                    break;
                case SORT_DESC:
                    arsort($sortable_array);
                    break;
            }

            foreach ($sortable_array as $k => $v) {
                $new_array[$k] = $array[$k];
            }
        }
        $array = $new_array;
    }

    // create condition based on parameters
    static function get_condition($_para) {
        $condition_line = array();
        $order_line = '';
        $limit_line = NULL;
        if ($_para['action'] == 'histry') {
            if (isset($_para['limit'])) {
                if ($_para['limit'] > 0) {
                    $limit_line = $_para['limit'];
                } else {
                    $limit_line = $_para['row'];
                }
            }

            if ($_para['day'] > 0) {
                $condition_line[] = "ORDER_DATE > ( NOW() - INTERVAL " . GFW::quote($_para['day']) . " DAY )";
            }

            if ($_para['priority'] == 'payment') {
                $order_line .= '(TOTAL_SALES_AMOUNT + TOTAL_TAX_AMOUNT) ' . $_para['payment'] . ', ORDER_DATE ' . $_para['date'];
            } else {
                $order_line .= 'ORDER_DATE ' . $_para['date'] . ', (TOTAL_SALES_AMOUNT + TOTAL_TAX_AMOUNT) ' . $_para['payment'];
            }

            if ($_para['paymore'] || $_para['paymore'] == '0') {
                $condition_line[] = '(TOTAL_SALES_AMOUNT + TOTAL_TAX_AMOUNT) >= ' . GFW::quote($_para['paymore']);
            }

            if ($_para['payless'] || $_para['payless'] == '0') {
                $condition_line[] = '(TOTAL_SALES_AMOUNT + TOTAL_TAX_AMOUNT) <= ' . GFW::quote($_para['payless']);
            }

            if ($_para['start_date']) {
                $condition_line[] = "ORDER_DATE >= " . GFW::quote($_para['start_date']);
            }

            if ($_para['end_date']) {
                $condition_line[] = "ORDER_DATE <= " . GFW::quote($_para['end_date']);
            }          
        }
        if ($_para['action'] == 'search') {
            foreach ($_para['keywords'] as $word) {
                $condition_line[] = "CONCAT_WS(' ',PRODUCT_DESCRIPTION,CATEGORY,VENDOR) LIKE UCASE('%" . $word . "%')";
            }
            if ($_para['category'] != 'ALL') {
                $condition_line[] = "DEPARTMENT = '" . $_para['category'] . "'";
            }
            if ($_para['priority'] == 'vendor') {
                $order_line = 'VENDOR ' . $_para['vendor'] . ', PRODUCT_DESCRIPTION ' . $_para['name'];
            } else {
                $order_line = 'PRODUCT_DESCRIPTION ' . $_para['name'] . ', VENDOR ' . $_para['vendor'];
            }
            if ($_para['hlt'] == 1) {
                $condition_line[] = "HEALTHY_LIVING_FLAG = 'Y'";
            }
            if ($_para['heb'] == 1) {
                $condition_line[] = "HEB_BRAND_FLAG = 'Y'";
            }
        }
        if ($_para['action'] == 'stats') {
            if ($_para['date_type'] == 'all') {
                
            }
            if ($_para['start_date']) {
                $condition_line[] = "DC.ORDER_DATE >= " . GFW::quote($_para['start_date']);
            }

            if ($_para['end_date']) {
                $condition_line[] = "DC.ORDER_DATE <= " . GFW::quote($_para['end_date']);
            }
        }
        return GFW::create_condition($condition_line, $order_line, $limit_line);
    }

    static function create_condition($_condition = NULL, $_order = NULL, $_limit = NULL) {
        $condition_line = '';
        $order_line = '';
        $limit_line = '';

        if ($_condition) {
            $condition_line .= " (" . implode(') AND (', $_condition) . ')';
        } else {
            $condition_line = ' 1 ';
        }
        if ($_order) {
            $order_line = " ORDER BY " . $_order;
        }
        if ($_limit) {
            if (is_array($_limit)) {
                $limit_line = ' LIMIT ' . $_limit[0] . ', ' . $_limit[1];
            } else {
                $limit_line = ' LIMIT ' . $_limit;
            }
        }
        return $condition_line . $order_line . $limit_line;
    }

    // standard redirect function
    static function redirect_to($dest) {
        header('Location: ' . $dest . '');
        die();
    }

    // start checking user's identity, this function is forced to be called
    // when every is loaded
    static function check_valid() {
        // the function is called by other pages
        if (self::check_session() || self::check_cookie()) {
            return true;
        }
        // The function is called by login.php, which means the user is logging in
        else if ($_SERVER['PHP_SELF'] == HOME_PATH . '/login.php') {
            /// clean session and cookie up
            self::delete_session();
            self::delete_cookie();
            // then verify identity by checking database
            if (self::check_identity(NULL, $_REQUEST['username'], $_REQUEST['password'])) {
                // if the user requires session
                if (isset($_POST['remember'])) {
                    self::set_cookie($_SESSION['USER_ID'], $_SESSION['EMAIL_ADDRESS'], $_SESSION['PASSWORD'], time() + 60 * 60 * 24 * 7);
                } else {
                    self::delete_cookie();
                }
                self::redirect_to(HOME_PATH . '/home.php');
            } else {
                // login failed, redirect to login page
                self::redirect_to(HOME_PATH . '/index.php?error=invalid_password_or_username');
            }
        }
        self::redirect_to(HOME_PATH . '/index.php?error=illegal_visit');
    }

    // verify user's identify by checking dataqbase
    static function check_identity($user_id = NULL, $email, $password) {
        $query = "SELECT * FROM USER WHERE ";

        // Check user_id if user_id is passed 
        if ($user_id) {
            $query .= "USER_ID = " . self::quote($user_id) . " AND ";
        }
        $query .= "EMAIL_ADDRESS = " . self::quote($email) . " AND PASSWORD = " . self::quote($password);
        $result = self::query($query);

        if (self::is_empty($result, $query)) {
            return false;
        }

        // if there is only one result found, the user & passwod pair is true
        if (mysql_num_rows($result) == 1) {
            $res = mysql_fetch_array($result);
            self::set_session($res['USER_ID'], $res['EMAIL_ADDRESS'], $res['PASSWORD'], $res);
            self::update_login_date($res['USER_ID']);
            return true;
        } else {
            return false;
        }
    }

    static function update_login_date($user_id) {
        $query = "UPDATE USER SET LAST_LOGIN = NOW() WHERE USER_ID = " . self::quote($user_id);
        self::query($query);
    }

    static function check_session() {
        if (isset($_SESSION)) {
            // the session is set, which means the user has logged in 
            if (isset($_SESSION['USER_ID']) &&
                    isset($_SESSION['EMAIL_ADDRESS']) &&
                    isset($_SESSION['PASSWORD'])) {
                return true;
            }
        }
        return false;
    }

    // verify user's identify by checking cookie
    static function check_cookie() {
        $user_info = array();

        // if the cookie is set, copy it to $userinfo, otherwise return false
        if (isset($_COOKIE['USER_ID']) &&
                isset($_COOKIE['EMAIL_ADDRESS']) &&
                isset($_COOKIE['PASSWORD'])) {
            $user_info['USER_ID'] = $_COOKIE['USER_ID'];
            $user_info['EMAIL_ADDRESS'] = $_COOKIE['EMAIL_ADDRESS'];
            $user_info['PASSWORD'] = $_COOKIE['PASSWORD'];
        } else {
            return false;
        }

        // check database to verify the user's identity
        if (self::check_identity($user_info['USER_ID'], $user_info['EMAIL_ADDRESS'], $user_info['PASSWORD'])) {
            self::set_cookie($user_info['USER_ID'], $user_info['EMAIL_ADDRESS'], $user_info['PASSWORD'], time() + 60 * 60 * 24 * 7);
            return true;
        } else {
            // delete the invalid cookie 
            self::delete_cookie();
            return false;
        }
    }

    static function set_session($user_id, $email, $password, $user_inf) {
        // update session 
        $_SESSION['USER_ID'] = $user_id;
        $_SESSION['EMAIL_ADDRESS'] = $email;
        $_SESSION['PASSWORD'] = $password;
        $_SESSION['USER_INF'] = $user_inf;
    }

    static function set_cookie($user_id, $email, $password, $time = -1) {
        // write user information to cookie 
        setcookie('USER_ID', $user_id, $time);
        setcookie('EMAIL_ADDRESS', $email, $time);
        setcookie('PASSWORD', $password, $time);
    }

    static function delete_session() {
        session_unset();
    }

    static function delete_cookie() {
        self::set_cookie(NULL, NULL, NULL, time() - 3600);
    }

}
?>
