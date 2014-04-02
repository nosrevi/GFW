<?php

class STORE {

    private $store_inf;

    function __construct($store_id) {
        $this->store_inf = self::get_store_by_store_id($_store_id);
    }

    static function get_store_by_store_id($_store_id) {
        $query = "SELECT * FROM STORE WHERE STORE_ID = " . GFW::quote($_store_id);
        $result = GFW::query($query);
        if (GFW::is_empty($result, $query)) {
            return false;
        }
        if (GFW::count($result) == 1) {
            return new STORE(GFW::fetch($result));
        } else {
            return false;
        }
    }

    // TODO CODE NEED CHECKING
    static function get_stores_by_city($_city_name) {
        $stores_index = self::get_stores_index_by_city($_city_name);
        // return a array of STORE objects
        return $stores_arr;
    }

    static function get_stores_index_by_city($_city_name) {
        $query = "SELECT STORE_ID FROM STORE WHERE CITY = '" . $_city_name . "'";
        $result = GFW::query($query);
        if (GFW::is_empty($result, $query)) {
            return false;
        }
        while ($store = GFW::fetch($result)) {
            $stores_index[] = $store['STORE_ID'];
        }
        // return a list of STORE id
        return $stores_index;
    }
	
	static function get_stores_by_orders(&$orders){
		if(!$orders){
			return;
		}
		$stores_index = array();		
		foreach($orders as $order){
			$stores_index[] = $order->get_store_id();
		}
		$stores_names = self::get_stores_names_by_stores_index($stores_index);
		foreach(array_keys($orders) as $order_id){
			$orders[$order_id]->set_store_name($stores_names[$orders[$order_id]->get_store_id()]);
		}
	}
	
    static function get_stores_names_by_stores_index($_stores_index) {
        $query = 'SELECT STORE_ID, STORE_DESCRIPTION FROM STORE WHERE STORE_ID IN (' . implode(',', $_stores_index) . ')';
        $result = GFW::query($query);
        if (GFW::is_empty($result, $query)) {
            return false;
        }
        while ($store = GFW::fetch($result)) {
            $stores[$store['STORE_ID']] = $store['STORE_DESCRIPTION'];
        }
        return $stores;
    }	

    static function get_store_by_store_abbreviation($_store_abbreviation) {
        $query = "SELECT * FROM STORE WHERE STORE_ABBREVIATION ='" . $_store_abbreviation . "'";
        $result = GFW::query($query);
        if (GFW::is_empty($result, $query)) {
            return false;
        }
        if (GFW::count($result) == 1) {
            return new STORE(GFW::fetch($result));
        } else {
            return false;
        }
    }

    // TODO END


    static function get_store_abbreviation_by_store_id() {
        return $result['STORE_ABBREVIATION'];
    }

    static function get_store_description_by_store_id() {
        return $result['STORE_DESCRIPTION'];
    }

    static function get_city_by_store_id() {
        return $result['CITY'];
    }

    public function get_store_id() {
        return $this->store_inf['STORE_ID'];
    }

    public function get_store_abbreviation() {
        return $this->store_inf['STORE_ABBREVIATION'];
    }

    public function get_store_description() {
        return $this->store_inf['STORE_DESCRIPTION'];
    }

    public function get_city() {
        return $this->store_inf['CITY'];
    }

}
