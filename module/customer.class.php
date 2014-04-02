<?php

class CUSTOMER {

    // customer information is stored as an array
    public $customer_inf = array();

    function __construct($_inf) {
        $this->customer_inf = $_inf;
    }

    // return a CUSTOMER object	
    static function get_customer_by_customer_id($_customer_id) {
        $query = "SELECT * FROM CUSTOMER WHERE CUSTOMER_ID=" . GFW::quote($_customer_id);
        $result = GFW::query($query);
        if (GFW::is_empty($result, $query)) {
            return false;
        }

        if (GFW::count($result) == 1) {
            return new CUSTOMER(GFW::fetch($result));
        } else {
            return false;
        }
    }

    public function get_customer_id() {
        
    }

    public function get_firstname() {
        return $this->customer_inf['FIRST_NAME'];
    }

    public function get_lastname() {
        return $this->customer_inf['LAST_NAME'];
    }

    public function get_full_name() {
        return $this->get_firstname() . " " . $this->get_lastname();
    }

}
