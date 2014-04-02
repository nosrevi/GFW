<?php

class ORDER_DETAIL {

    public $order_detail_inf;

    function __construct($order_detail) {
        $this->order_detail_inf = $order_detail;
    }

    static function get_order_detail_by_order_id($_order_id, $_condition = NULL) {
        $query = "SELECT * FROM ORDER_DETAIL WHERE ORDER_ID=" . GFW::quote($_order_id);
        if ($_condition) {
            $query .= ' AND ' . $_condition;
        }
        if (GFW::is_empty($result, $query)) {
            return false;
        }
        if (GFW::count($result) >= 1) {
            $order_details_arr = array();
            while ($entity = GFW::fetch($result)) {
                $order_details_arr[] = new ORDER_DETAIL($result);
            }
            return $order_details_arr;
        } else {
            return false;
        }
    }

}
