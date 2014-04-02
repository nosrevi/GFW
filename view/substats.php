<?php

require_once "GFW_init.php";
require_once MODULE_PATH . "/order.class.php";
require_once MODULE_PATH . "/customer.class.php";
require_once MODULE_PATH . "/user.class.php";
require_once MODULE_PATH . "/histry.drawer.php";
require_once MODULE_PATH . "/product.class.php";
require_once MODULE_PATH . "/stats.drawer.php";

STATS_DRAWER::set_request($this->request);
//STATS_DRAWER::set_solo($solo);

$condition = GFW::get_condition($this->request);
if ($this->request['type'] == 'user') {
    $orders_index = ORDER::get_orders_index_by_user_id($_SESSION['USER_ID'], false, $condition);
} else {
    $orders_index = ORDER::get_orders_index_by_customer_id($this->request['customer_id'], false, $condition);
}
if(!$orders_index){
    return;
}

$orders = array();
$condition = str_replace('DC.', '', $condition);
if ($this->request['type'] == 'user') {
    $orders = ORDER::get_orders_by_user_id($_SESSION['USER_ID'], false, $condition);
} else {
    $orders = ORDER::get_orders_by_customer_id($this->request['customer_id'], false, $condition);
}

ORDER::get_orders_details_by_orders($orders);
ORDER::get_products_by_orders($orders);

$customer_name = '';
if ($this->request['type'] != 'user') {
    $customer = CUSTOMER::get_customer_by_customer_id($this->request['customer_id']);
    $customer_name = $customer->get_firstname() . "\'s ";
}

if ($this->request['source'] == 'line_stable') {      
    $spending_pie_left = array();
    $spending_pie_left['category'] = PRODUCT::get_dpts();
    $spending_pie_left['category'] = ORDER::group_products_by_department($orders, $spending_pie_left['category']);
    echo "<script>" 
            . STATS_DRAWER::get_pie_chart($spending_pie_left, $customer_name . "Most Spending Categories<br>", "pie_left", "spending_pie_chart_left", 'pie_left#source=pie_left&start_date=' . $this->request['start_date'] . '&end_date=' . $this->request['end_date'], '$') . 
        "</script>";
}

if ($this->request['source'] == 'pie_left' || $this->request['source'] == 'line_stable') {
    $spending_pie_right = array();
    if (!isset($this->request['subgroup'])) {
        $spending_pie_right['category'] = PRODUCT::get_cates();
    } else {
        $spending_pie_right['category'] = PRODUCT::get_cates(array('DEPARTMENT' => $this->request['subgroup']));
    }
    if (isset($this->request['subgroup'])) {     
        $spending_pie_right['category'] = ORDER::group_products_by_category($orders, $spending_pie_right['category'], $this->request['subgroup']);
        echo "<script>" . STATS_DRAWER::get_pie_chart($spending_pie_right, $customer_name . "Most Spending Categories <br>in " . $this->request['subgroup'] . "", "pie_right", "spending_pie_chart_right", 'pie_right#start_date=' . $this->request['start_date'] . '&end_date=' . $this->request['end_date'], '$') . "</script>";
    } else {
        $spending_pie_right['category'] = ORDER::group_products_by_category($orders, $spending_pie_right['category']);
        echo "<script>" . STATS_DRAWER::get_pie_chart($spending_pie_right, $customer_name . "Most Spending Categories<br>", "pie_right", "spending_pie_chart_right", 'pie_right#source=pie_right&start_date=' . $this->request['start_date'] . '&end_date=' . $this->request['end_date'], '$') . "</script>";
    }
}
if ($this->request['source'] == 'pie_left' || $this->request['source'] == 'pie_right' || $this->request['source'] == 'line_stable') {
    $spending_bar = array();
    if ($this->request['source'] == 'pie_left' && isset($this->request['subgroup'])) {
        $spending_bar['category'] = ORDER::get_most_products($orders, $this->request['subgroup'], NULL);            
        echo "<script>" . STATS_DRAWER::get_spending_bar($spending_bar, $customer_name. "Most Spending Products<br>in " . $this->request['subgroup']) ."</script>";
    } else if($this->request['source'] == 'pie_right' && isset($this->request['subgroup'])) {
        $spending_bar['category'] = ORDER::get_most_products($orders, NULL, $this->request['subgroup']);   
        echo "<script>" . STATS_DRAWER::get_spending_bar($spending_bar, $customer_name. "Most Spending Products<br>in " . $this->request['subgroup']) ."</script>";
    } else{
        $spending_bar['category'] = ORDER::get_most_products($orders);   
        echo "<script>" . STATS_DRAWER::get_spending_bar($spending_bar, $customer_name. "Most Spending Products<br>") ."</script>";
    }
}
?>

