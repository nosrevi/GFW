<?php

require_once MODULE_PATH . "/customer.class.php";

$_user = new USER($_SESSION['USER_INF']);

class USER {

    // user information is stored as an array
    private $user_inf = array();
    private $customers = array();
    private $customers_num;

    function __construct($inf) {
        $this->user_inf = $inf;
        $this->customers = self::get_customers_by_user_id($inf['USER_ID']);
        $this->customers_num = count($this->customers);
    }

    static function get_user_by_user_id($_user_id, $_condition = NULL) {
        $query = "SELECT * FROM USER WHERE USER_ID = " . GFW::quote($_user_id);
        if ($_condition) {
            $query .= ' AND ' . $_condition;
        }
        $result = GFW::query($query);

        if (GFW::is_empty($result, $query)) {
            return false;
        }

        if (GFW::count($result) == 1) {
            return new USER(GFW::fetch($result));
        } else {
            return false;
        }
    }

    static function get_user_id_by_customer_id($_customer_id) {
        $query = "SELECT USER_ID FROM USER_CUSTOMER WHERE CUSTOMER_ID = " . GFW::quote($_customer_id);
        $result = GFW::query($query);

        if (GFW::is_empty($result, $query)) {
            return false;
        }

        if (GFW::count($result) == 1) {
            $result = GFW::fetch($result);
            return $result['USER_ID'];
        } else {
            return false;
        }
    }

    static function get_customers_by_user_id($_user_id, $_index = false, $_condition = NULL) {
        if ($_index) {
            $query = "SELECT CUSTOMER_ID FROM USER_CUSTOMER WHERE USER_ID=" . GFW::quote($_user_id);
        } else {
            $query = "SELECT C.* FROM CUSTOMER AS C JOIN USER_CUSTOMER AS UC ON C.CUSTOMER_ID = UC.CUSTOMER_ID WHERE USER_ID =" . GFW::quote($_user_id);
        }

        if ($_condition) {
            $query .= ' AND ' . $_condition;
        }
        $result = GFW::query($query);
        if (GFW::is_empty($result, $query)) {
            return false;
        }

        if ($_index) {
            $user_customers_index = array();
            while ($entity = GFW::fetch($result)) {
                $user_customers_index[] = $entity['CUSTOMER_ID'];
            }
            return $user_customers_index;
        } else {
            $user_customers_arr = array();
            while ($entity = GFW::fetch($result)) {
                $customers_arr[$entity['CUSTOMER_ID']] = new CUSTOMER($entity);
            }
            return $customers_arr;
        }
    }

    public function get_firstname() {
        return $this->user_inf['FIRST_NAME'];
    }

    public function get_lastname() {
        return $this->user_inf['LAST_NAME'];
    }

    public function get_fullname() {
        return $this->user_inf['FIRST_NAME'] . ' ' . $this->user_inf['LAST_NAME'];
    }

    public function get_email() {
        return $this->user_inf['EMAIL_ADDRESS'];
    }

    public function get_user_id() {
        return $this->user_inf['USER_ID'];
    }

    public function set_firstname($str) {
        $this->user_inf['FIRST_NAME'] = $str;
    }

    public function set_lastname($str) {
        $this->user_inf['LAST_NAME'] = $str;
    }

    public function set_password($str) {
        $this->user_inf['PASSWORD'] = $str;
    }

    public function get_customer_index() {
        return array_keys($this->customers);
    }

    public function get_customers() {
        return $this->customers;
    }

    public function get_customers_num() {
        return $this->customers_num;
    }

}
