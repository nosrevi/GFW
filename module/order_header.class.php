<?php

class ORDER_HEADER {

    private $order_header_inf;

    function __construct($order_header) {
        $this->order_header_inf = $order_header;
        $this->order_header_inf['TOTAL_SALES_AMOUNT'] = number_format($this->order_header_inf['TOTAL_SALES_AMOUNT'], 2, '.', '');
    }

    static function get_order_header_by_order_id($_order_id, $_condition = NULL) {
        $query = "SELECT * FROM ORDER_HEADER WHERE ORDER_ID=" . GFW::quote($_order_id);
        if ($_condition) {
            $query .= ' AND ' . $_condition;
        }
        $result = GFW::query($query);

        if (GFW::is_empty($result, $query)) {
            return false;
        }

        if (GFW::count($result) == 1) {
            return new ORDER_HEADER($result);
        } else {
            return false;
        }
    }

    public function get_order_id() {
        return $this->order_header_inf['ORDER_ID'];
    }

}
