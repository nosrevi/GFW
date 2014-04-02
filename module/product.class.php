<?php

class PRODUCT {

    private $product_inf;

    // here $product_id is actually UPC, but we prefer to use the word "ID" 
    function __construct($_product_id) {
        $this->product_inf = self::get_product_by_product_id($_product_id);
    }

    static function get_dpts() {
        $query = "SELECT DISTINCT DEPARTMENT FROM PRODUCT WHERE 1 ORDER BY DEPARTMENT ASC ";
        $result = GFW::query($query);
        $dpts = array();
        while ($row = GFW::fetch($result)) {
            $dpts[] = $row['DEPARTMENT'];
        }
        return $dpts;
    }
    static function get_cates($_condition = NULL) {
        $condition = array();
        if($_condition){
            foreach($_condition as $key=>$value){
                $condition[] = $key."=".GFW::quote($value);
            }
            $condition = implode(' AND ', $condition);
            $query = "SELECT DISTINCT CATEGORY FROM PRODUCT WHERE $condition ORDER BY DEPARTMENT ASC ";
        }
        else{
            $query = "SELECT DISTINCT CATEGORY FROM PRODUCT WHERE 1 ORDER BY DEPARTMENT ASC ";
        }
        $result = GFW::query($query);
        $dpts = array();
        while ($row = GFW::fetch($result)) {
            $dpts[] = $row['CATEGORY'];
        }
        return $dpts;
    }    

    static function get_product_by_product_id($_product_id, $_condition = NULL) {
        $query = "SELECT * FROM PRODUCT WHERE UPC = " . GFW::quote($_product_id);
        if ($_condition) {
            $query .= ' AND ' . $_condition;
        }
        $result = GFW::query($query);
        if (GFW::count($result) == 1) {
            return GFW::fetch($result);
        } else {
            return false;
        }
    }

    // need to join ORDER_DETAIL
    static function get_products_by_order_id($_order_id, $_condition = NULL) {
        $products_index = self::get_products_index_by_order_id($_order_id, $_condition);
        $query = "SELECT * FROM PRODUCT WHERE UPC IN ";
        $query .= '(' . implode(',', $products_index) . ')';
        if ($_condition) {
            $query .= ' AND ' . $_condition;
        }
        $result = GFW::query($query);

        if (GFW::is_empty($resulit, $query)) {
            return false;
        }

        $products_arr = array();
        while ($row = GFW::fetch($result)) {
            $this_product = new PRODUCT($row);
            $products_arr[$row['UPC']] = $this_product;
        }

        // return a array of PRODUCT objects
        return $products_arr;
    }

    // need to join ORDER_DETAIL
    static function get_products_index_by_order_id($_order_id, $_condition = NULL) {
        $query = "SELECT UPC FROM ORDER_DETAIL WHERER ORDER_ID = " . GFW::quote($_order_id);
        if ($_condition) {
            $query .= ' AND ' . $_condition;
        }
        $result = GFW::query($query);
        if (GFW::is_empty($result, $query)) {
            return false;
        }

        $products_index = array();
        while ($row = GFW::fetch($result)) {
            $products_index[] = $row['UPC'];
        }

        // return a list of product_id
        return $products_index;
    }

	// get all products purchased by given user
	static function get_products_index_by_user_id($_user_id, $_condition = NULL){
		$query = "SELECT DISTINCT OD.UPC FROM USER_CUSTOMER AS UC ".
			"INNER JOIN CUSTOMER_ORDER AS CO ON UC.CUSTOMER_ID = CO.CUSTOMER_ID ".
			"INNER JOIN ORDER_DETAIL AS OD ON CO.ORDER_ID = OD.ORDER_ID ".
			"INNER JOIN ORDER_HEADER AS OH ON OD.ORDER_ID = OH.ORDER_ID ".
			"WHERE UC.USER_ID = ".GFW::quote($_user_id);
		$result = GFW::query($query);
        if (GFW::is_empty($result, $query)) {
            return false;
        }				
		$products_index = array();
        while ($row = GFW::fetch($result)) {
            $products_index[$row['UPC']] = true;
        }		
		return $products_index;
    }

    static function get_product_abbreviation_by_product_id() {
        return $result['STORE_ABBREVIATION'];
    }

    static function get_product_description_by_product_id() {
        return $result['STORE_DESCRIPTION'];
    }

    static function get_city_by_product_id() {
        return $result['CITY'];
    }

    public function get_product_id() {
        return $this->product_inf['UPC'];
    }

    public function get_product_description() {
        return $this->product_inf['PRODUCT_DESCRIPTION'];
    }

    public function get_department() {
        return $this->product_inf['DEPARTMENT'];
    }

    public function get_category() {
        return $this->product_inf['CATEGORY'];
    }

    public function get_vendor() {
        return $this->product_inf['VENDOR'];
    }

    public function is_heb() {
        return 'Y' == $this->product_inf['HEB_BRAND_FLAG'];
    }

    public function is_healthy() {
        return 'Y' == $this->product_inf['HEALTHY_LIVING_FLAG'];
    }

}
