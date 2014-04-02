<?php

require_once MODULE_PATH . "/order_header.class.php";
require_once MODULE_PATH . "/order_detail.class.php";
require_once MODULE_PATH . "/user.class.php";
require_once MODULE_PATH . "/customer.class.php";

class ORDER {

    private $order_detail_inf;
    private $order_header_inf;
    private $product_inf;
    private $order_id;
    private $customer_id;
    private $user_id;

    function __construct($order_header, $order_detail = NULL, $customer_id = NULL, $user_id = NULL) {
        $this->order_id = $order_header['ORDER_ID'];
        $this->customer_id = $customer_id;
        $this->user_id = $user_id;
        $this->order_header_inf = $order_header;
        $this->order_detail_inf = $order_detail;
        $this->set_total_amount();
    }

    public function get_order_id() {
        return $this->order_id;
    }

    public function get_customer_id() {
        return $this->customer_id;
    }

    public function get_user_id() {
        return $this->user_id;
    }

    public function get_order_date() {
        return $this->order_header_inf['ORDER_DATE'];
    }

    public function get_sales_amount() {
        return $this->order_header_inf['TOTAL_SALES_AMOUNT'];
    }

    public function get_store_id() {
        return $this->order_header_inf['STORE_ID'];
    }

    public function set_store_name($_store_name) {
        $this->order_header_inf['STORE_NAME'] = $_store_name;
    }

    public function get_store_name() {
        return $this->order_header_inf['STORE_NAME'];
    }

    public function get_tax_amount() {
        return $this->order_header_inf['TOTAL_TAX_AMOUNT'];
    }

    public function get_total_amount() {
        return $this->order_header_inf['TOTAL_AMOUNT'];
    }

    public function set_total_amount() {
        $this->order_header_inf['TOTAL_AMOUNT'] = number_format($this->order_header_inf['TOTAL_TAX_AMOUNT'] + $this->order_header_inf['TOTAL_SALES_AMOUNT'], 2, '.', '');
    }

    public function get_discount_amount() {
        return $this->order_header_inf['TOTAL_DISCOUNT_AMOUNT'];
    }

    public function set_order_detail($order_detail = -1) {
        $this->order_detail_inf = $order_detail;
    }

    public function get_order_detail() {
        return $this->order_detail_inf;
    }

    public function set_product_inf($_product_inf) {
        $this->product_inf = $_product_inf;
    }

    public function get_product_inf() {
        return $this->product_inf;
    }

    static function get_order_by_order_id($_order_id) {
        $order_header = ORDER_HEADER::get_order_header_by_order_id($_order_id);
        if ($order_header) {
            $order_detail = ORDER_DETAIL::get_order_detail_by_order_id($_order_id);
            if ($order_detail) {
                return new ORDER($order_header, $order_detail);
            }
            // for some reason, some orders dont have details, 
            // which may be caused by return of goods 				
            else {
                return new ORDER($order_header, -1);
            }
        } else {
            return false;
        }
    }

    // $detail indicates whether need the detail of order. For instance,
    // to calculate how much a client spent in last month we do not need the 
    // details 				
    static function &get_orders_by_user_id($_user_id, $_detail = true, $_condition = NULL) {
        $user = USER::get_user_by_user_id($_user_id);
        if ($user) {
            $customer_index = $user->get_customer_index();
            $order_arr = self::get_orders_by_customer_id($customer_index, $_detail, $_condition, $_user_id);
            return $order_arr;
        } else {
            // no such user, impossible
            return false;
        }
    }
    
    static function &get_orders_by_user_id_and_upc($_user_id, $_upc, $_detail = false, $_condition = NULL) {
        $user = USER::get_user_by_user_id($_user_id);
        if ($user) {
            $customer_index = $user->get_customer_index();
            $order_arr = self::get_orders_by_customer_id_and_upc($customer_index, $_upc, $_detail, $_condition, $_user_id);
            return $order_arr;
        } else {
            // no such user, impossible
            return false;
        }
    }    

    static function &get_orders_index_by_user_id($_user_id) {
        $user = USER::get_user_by_user_id($_user_id);
        if ($user) {
            $customer_index = $user->get_customer_index();
            $order_index = self::get_orders_index_by_customer_id($customer_index);
            return $order_index;
        } else {
            // no such user, impossible
            return 0;
        }
    }

    static function sort_products_by_sales(&$_order) {
        usort($_order, "self::compare_products_by_sale");
    }

    static function compare_products_by_sale($a, $b) {
        if ($a['TOTAL_RETAIL_AMOUNT'] == $b['TOTAL_RETAIL_AMOUNT']) {
            return 0;
        }
        return ($a['TOTAL_RETAIL_AMOUNT'] < $b['TOTAL_RETAIL_AMOUNT']) ? 1 : -1;
    }

    static function get_products_by_orders(&$_orders) {
        if (!$_orders) {
            return false;
        }
        $orders_index = array_keys($_orders);
        $query = 'SELECT DISTINCT P.*, OD.ORDER_ID FROM PRODUCT AS P JOIN ORDER_DETAIL AS OD ON P.UPC = OD.UPC WHERE OD.ORDER_ID IN (' . implode(',', $orders_index) . ')';
        $result = GFW::query($query);
        if (GFW::is_empty($result, $query)) {
            return false;
        }
        $products_arr = array();
        foreach ($orders_index as $order_id) {
            $orders_arr[$order_id] = array();
        }
        while ($product = GFW::fetch($result)) {
            $orders_arr[$product['ORDER_ID']][$product['UPC']] = $product;
        }
        foreach ($_orders as $key => $value) {
            $value->set_product_inf($orders_arr[$key]);
        }
    }

    static function get_orders_details_by_orders(&$_orders) {
        if (!$_orders) {
            return false;
        }
        $orders_index = array_keys($_orders);
        $query = "SELECT * FROM ORDER_DETAIL WHERE ORDER_ID IN (" . implode(', ', $orders_index) . ')';
        $result = GFW::query($query);
        if (GFW::is_empty($result, $query)) {
            return false;
        }

        foreach ($orders_index as $order_id) {
            $orders_arr[$order_id] = array();
        }
        while ($entity = GFW::fetch($result)) {
            $orders_arr[$entity['ORDER_ID']][] = $entity;
        }

        //var_export($_orders);
        foreach ($_orders as $order) {
            $order_id = $order->get_order_id();
            if ($orders_arr[$order_id]) {
                $_orders[$order_id]->set_order_detail($orders_arr[$order_id]);
            } else {
                $_orders[$order_id]->set_order_detail(-1);
            }
        }
    }

    // $detail indicates whether need the detail of order.
    static function get_orders_by_customer_id($_customer_id, $_detail = true, $_condition = NULL, $_user_id = NULL) {
        if (!$_user_id) {
            $_user_id = USER::get_user_id_by_customer_id($_customer_id);
            if (!$_user_id) {
                return false;
            }
        }
        $orders_index = ORDER::get_orders_index_by_customer_id($_customer_id);
        if ($orders_index) {
            $order_header_arr = array();
            $order_detail_arr = array();

            // get all order's headers
            $query = "SELECT * FROM ORDER_HEADER WHERE ORDER_ID IN ";
            $query .= '(' . implode(',', array_keys($orders_index)) . ')';
            if ($_condition) {
                $query .= ' AND ' . $_condition;
            }

            $order_header_result = GFW::query($query);

            if (GFW::is_empty($order_header_result, $query)) {
                return false;
            }

            while ($order_header = GFW::fetch($order_header_result)) {
                $order_header_arr[$order_header['ORDER_ID']] = $order_header;
            }


            if ($_detail) {
                // get all order's details
                $query = "SELECT * FROM ORDER_DETAIL WHERE ORDER_ID IN ";
                $query .= '(' . implode(',', array_keys($order_header_arr)) . ')';
                $order_detail_result = GFW::query($query);

                if (!$order_detail_result) {
                    // no order_detail history
                    return false;
                } else {
                    while ($order_detail = GFW::fetch($order_detail_result)) {
                        if (!isset($order_detail_arr[$order_detail['ORDER_ID']])) {
                            $order_detail_arr[$order_detail['ORDER_ID']] = array();
                        }
                        $order_detail_arr[$order_header['ORDER_ID']][] = $order_detail;
                    }
                }
            }

            $order_arr = array();
            // if $_detail is set, add details to ORDER
            if ($_detail) {
                foreach (array_keys($order_header_arr) as $order_id) {
                    if (isset($order_detail_arr[$order_id])) {
                        $order_arr[$order_id] = new ORDER($order_header_arr[$order_id], $order_detail_arr[$order_id], $orders_index[$order_id], $_user_id);
                    } else {
                        // for some reason, some orders dont have details
                        $order_arr[$order_id] = new ORDER($order_header_arr[$order_id], "-1", $orders_index[$order_id], $_user_id);
                    }
                }
            } else {
                foreach (array_keys($order_header_arr) as $order_id) {
                    $order_arr[$order_id] = new ORDER($order_header_arr[$order_id], "-1", $orders_index[$order_id], $_user_id);
                }
            }

            return $order_arr;
        } else {
            // no shopping history
            return false;
        }
    }

        // $detail indicates whether need the detail of order.
    static function get_orders_by_customer_id_and_upc($_customer_id, $_upc, $_detail = true, $_condition = NULL, $_user_id = NULL) {
        if (!$_user_id) {
            $_user_id = USER::get_user_id_by_customer_id($_customer_id);
            if (!$_user_id) {
                return false;
            }
        }       
        $orders_index = array();
        $query = "SELECT DISTINCT CO.CUSTOMER_ID, OH.ORDER_ID FROM ORDER_HEADER AS OH 
				JOIN ORDER_DETAIL AS OD ON OH.ORDER_ID = OD.ORDER_ID 
				JOIN CUSTOMER_ORDER AS CO ON CO.ORDER_ID = OH.ORDER_ID 
                WHERE OD.UPC = ".GFW::quote($_upc);
        $result = GFW::query($query);
        if (GFW::is_empty($result, $query)) {
            return false;
        }
        while ($row = GFW::fetch($result)) {
            $orders_index[$row['ORDER_ID']] = $row['CUSTOMER_ID'];
        }
       
        if ($orders_index) {
            $order_header_arr = array();
            $order_detail_arr = array();

            // get all order's headers
            $query = "SELECT * FROM ORDER_HEADER WHERE ORDER_ID IN ";
            $query .= '(' . implode(',', array_keys($orders_index) ). ')';
            if ($_condition) {
                $query .= ' AND ' . $_condition;
            }
            $order_header_result = GFW::query($query);
            if (GFW::is_empty($order_header_result, $query)) {
                return false;
            }
            while ($order_header = GFW::fetch($order_header_result)) {
                $order_header_arr[$order_header['ORDER_ID']] = $order_header;
            }
            $order_arr = array();
            // if $_detail is set, add details to ORDER
            foreach (array_keys($order_header_arr) as $order_id) {
                $order_arr[$order_id] = new ORDER($order_header_arr[$order_id], "-1", $orders_index[$order_id], $_user_id);
            }
            return $order_arr;
        } else {
            // no shopping history
            return false;
        }
    }
    
    static function get_orders_index_by_customer_id($_customer_id) {
        $query = "SELECT ORDER_ID, CUSTOMER_ID FROM CUSTOMER_ORDER WHERE ";
        if (is_array($_customer_id)) {
            $query .= 'CUSTOMER_ID IN (' . implode(', ', $_customer_id) . ')';
        } else {
            $query .= "CUSTOMER_ID=" . GFW::quote($_customer_id);
        }
        $result = GFW::query($query);
        if (GFW::is_empty($result, $query)) {
            return false;
        }

        if (GFW::count($result) >= 1) {
            $orders_index = array();
            while ($entity = GFW::fetch($result)) {
                $orders_index[$entity['ORDER_ID']] = $entity['CUSTOMER_ID'];
            }
            return $orders_index;
        } else {
            return false;
        }
    }

    static function get_line_category($_group, $_condition = NULL) {
        if ($_group == 'day') {
            $query = "SELECT DC.ORDER_DATE FROM DATE_CALENDAR AS DC ";
            if ($_condition) {
                $query .= 'WHERE ' . $_condition;
            }
            $query .= ' GROUP BY DC.ORDER_DATE ORDER BY DC.ORDER_DATE';
        }
        if ($_group == 'week') {
            $query = "SELECT DC.ORDER_DATE, DC.DATE_WEEK FROM DATE_CALENDAR AS DC ";
            if ($_condition) {
                $query .= 'WHERE ' . $_condition;
            }
            $query .= ' GROUP BY DC.DATE_YEAR, DC.DATE_WEEK ORDER BY DC.ORDER_DATE';
        }
        if ($_group == 'month') {
            $query = "SELECT DC.ORDER_DATE, DC.DATE_YEAR, DC.DATE_MONTH FROM DATE_CALENDAR AS DC ";
            if ($_condition) {
                $query .= 'WHERE ' . $_condition;
            }
            $query .= ' GROUP BY DC.DATE_YEAR, DC.DATE_MONTH ORDER BY DC.ORDER_DATE';
        }
        $category_result = GFW::query($query);
        if (GFW::is_empty($category_result, $query)) {
            return false;
        }
        $categories = array();
        if ($_group == 'day') {
            while ($category = GFW::fetch($category_result)) {
                $categories[] = $category['ORDER_DATE'];
            }
        } else if ($_group == 'week') {
            while ($category = GFW::fetch($category_result)) {
                $categories[] = $category['DATE_WEEK'];
            }
        } else if ($_group == 'month') {
            while ($category = GFW::fetch($category_result)) {
                $categories[] = $category['DATE_MONTH'] . ' ' . $category['DATE_YEAR'];
            }
        }
        return $categories;
    }

    static function group_orders_by_week($_orders_index, $_condition = NULL) {
        $query = "SELECT DC.DATE_YEAR, DC.DATE_WEEK, SUM(OH.TOTAL_SALES_AMOUNT + OH.TOTAL_TAX_AMOUNT) AS SPENDING 
                    FROM ORDER_HEADER AS OH JOIN DATE_CALENDAR AS DC ON OH.ORDER_DATE = DC.ORDER_DATE WHERE ORDER_ID IN ";
        $query .= '(' . implode(',', $_orders_index) . ')';
        if ($_condition) {
            $query .= ' AND ' . $_condition . ' AND (OH.TOTAL_SALES_AMOUNT + OH.TOTAL_TAX_AMOUNT) > 0 ';
        }
        $query .= ' GROUP BY DC.DATE_YEAR, DATE_WEEK ORDER BY DC.ORDER_DATE';
        $group_orders_result = GFW::query($query);
        if (GFW::is_empty($group_orders_result, $query)) {
            return false;
        }
        while ($group_order = GFW::fetch($group_orders_result)) {
            $group_orders_by_week[$group_order['DATE_WEEK']] = $group_order['SPENDING'];
        }
        return $group_orders_by_week;
    }

    static function group_orders_by_day($_orders_index, $_condition = NULL) {
        $query = "SELECT DC.ORDER_DATE, SUM(OH.TOTAL_SALES_AMOUNT + OH.TOTAL_TAX_AMOUNT) AS SPENDING 
                    FROM DATE_CALENDAR AS DC RIGHT JOIN ORDER_HEADER AS OH ON DC.ORDER_DATE = OH.ORDER_DATE WHERE ORDER_ID IN ";
        $query .= '(' . implode(',', $_orders_index) . ')';
        if ($_condition) {
            $query .= ' AND ' . $_condition . ' AND (OH.TOTAL_SALES_AMOUNT + OH.TOTAL_TAX_AMOUNT) > 0 ';
        }
        $query .= ' GROUP BY DC.ORDER_DATE ORDER BY DC.ORDER_DATE';
        $group_orders_result = GFW::query($query);
        if (GFW::is_empty($group_orders_result, $query)) {
            return false;
        }
        while ($group_order = GFW::fetch($group_orders_result)) {
            $group_orders_by_day[$group_order['ORDER_DATE']] = $group_order['SPENDING'];
        }
        return $group_orders_by_day;
    }

    static function group_orders_by_month($_orders_index, $_condition = NULL) {
        $query = "SELECT DC.DATE_YEAR, DC.DATE_MONTH, SUM(OH.TOTAL_SALES_AMOUNT + OH.TOTAL_TAX_AMOUNT) AS SPENDING 
                    FROM ORDER_HEADER AS OH JOIN DATE_CALENDAR AS DC ON OH.ORDER_DATE = DC.ORDER_DATE WHERE ORDER_ID IN ";
        $query .= '(' . implode(',', $_orders_index) . ')';
        if ($_condition) {
            $query .= ' AND ' . $_condition . ' AND (OH.TOTAL_SALES_AMOUNT + OH.TOTAL_TAX_AMOUNT) > 0 ';
        }
        $query .= ' GROUP BY DC.DATE_YEAR, DATE_MONTH ORDER BY DC.ORDER_DATE';
        $group_orders_result = GFW::query($query);
        if (GFW::is_empty($group_orders_result, $query)) {
            return false;
        }
        while ($group_order = GFW::fetch($group_orders_result)) {
            $group_orders_by_month[$group_order['DATE_MONTH'] . ' ' . $group_order['DATE_YEAR']] = $group_order['SPENDING'];
        }
        return $group_orders_by_month;
    }

    static function get_most_products(&$_orders, $department = NULL, $category = NULL) {
        if (!$_orders) {
            return false;
        }
        $products = array();
        $products_inf = array();
        foreach ($_orders as $order) {
            if ($order->get_total_amount() < 0) {
                continue;
            }
            $order_detail = $order->get_order_detail();
            if ($order_detail > 0) {
                foreach ($order_detail as $product) {                    
                    if (!isset($products[$product['UPC']])) {
                        $products[$product['UPC']] = 0;
                    }
                    $products[$product['UPC']] += $product['TOTAL_RETAIL_AMOUNT'];
                }
            }
            foreach ($order->get_product_inf() as $product_inf) {
                if (!isset($products_inf[$product_inf['UPC']])) {
                    $products_inf[$product_inf['UPC']] = $product_inf;
                }
            }
        }
        if (!$products) {
            return false;
        }
        
        if($department){
            foreach ($products_inf as $key => $value) {
                if($value['DEPARTMENT'] != $department){
                    unset($products[$key]);
                }
            }
        }
        if($category){
            foreach ($products_inf as $key => $value) {
                if($value['CATEGORY'] != $category){
                    unset($products[$key]);
                }
            }
        }
        
        GFW::array_sort($products, $order = SORT_DESC);
        $products_category = array();
        $count = 0;
        foreach ($products as $key => $value) {
            if (++$count > 9) {
                return $products_category;
            }
            $products_category[$key] = array('name' => $products_inf[$key]['PRODUCT_DESCRIPTION'], 'payment' => $value);
        }
        return $products_category;
    }

    static function group_products_by_department(&$_orders, $_department) {
        if (!$_orders) {
            return;
        }
        $department = array();
        $products = array();

        foreach ($_department as $key) {
            $department[$key] = 0;
        }

        $amount = 0;
        foreach ($_orders as $order) {
            if ($order->get_total_amount() < 0) {
                continue;
            }
            foreach ($order->get_product_inf() as $product_inf) {
                $products[$product_inf['UPC']] = $product_inf['DEPARTMENT'];
            }
            $order_detail = $order->get_order_detail();
            if ($order_detail > 0) {
                foreach ($order_detail as $product) {
                    $amount += $product['TOTAL_RETAIL_AMOUNT'];
                    $department[$products[$product['UPC']]] += $product['TOTAL_RETAIL_AMOUNT'];
                }
            }
        }

        $other = 0;
        foreach ($department as $key => $value) {
            if ($value < $amount / 40) {
                $other += $value;
                unset($department[$key]);
            }
        }
        GFW::array_sort($department, SORT_DESC);
        if ($other) {
            $department['OTHERS'] = $other;
        }
        $data = array();
        $data['amount'] = $amount;
        $data['data'] = $department;
        return $data;
    }

    static function group_products_by_category(&$_orders, $_category, $_department = NULL) {
        if (!$_orders) {
            return;
        }
        $category = array();
        $products = array();
        $cate_pro_index = array();
        foreach ($_category as $key) {
            $category[$key] = 0;
            $cate_pro_index[$key] = array();
        }
        $amount = 0;

        foreach ($_orders as $order) {
            if ($order->get_total_amount() < 0) {
                continue;
            }

            foreach ($order->get_product_inf() as $product_inf) {
                $products[$product_inf['UPC']] = $product_inf['CATEGORY'];
                if ($_department && !isset($departments[$product_inf['UPC']])) {
                    $departments[$product_inf['UPC']] = $product_inf['DEPARTMENT'];
                    $cate_pro_index[$product_inf['CATEGORY']][] = $product_inf['UPC'];
                }
            }
            $order_detail = $order->get_order_detail();

            if ($order_detail > 0) {
                if ($_department) {
                    foreach ($order_detail as $product) {
                        if (isset($category[$products[$product['UPC']]]) && $departments[$product['UPC']] == $_department) {
                            $category[$products[$product['UPC']]] += $product['TOTAL_RETAIL_AMOUNT'];
                            $amount += $product['TOTAL_RETAIL_AMOUNT'];
                        }
                    }
                } else {
                    foreach ($order_detail as $product) {
                        if (isset($category[$products[$product['UPC']]])) {
                            $category[$products[$product['UPC']]] += $product['TOTAL_RETAIL_AMOUNT'];
                            $amount += $product['TOTAL_RETAIL_AMOUNT'];
                        }
                    }
                }
            }
        }

        $other = 0;
        foreach ($category as $key => $value) {
            if ($value < $amount / 40) {
                $other += $value;
                unset($category[$key]);
            }
        }
        GFW::array_sort($category, SORT_DESC);
        if (count($category) > 9) {
            $category = array_slice($category, 0, 9);
            $other = $amount;
            foreach ($category as $key => $value) {
                $other -= $value;
            }
        }

        if ($other) {
            $category['OTHERS'] = $other;
        }
        $data = array();
        $data['amount'] = $amount;
        $data['data'] = $category;
        $data['products'] = $cate_pro_index;
        return $data;
    }

}
