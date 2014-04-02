<?php
require_once "GFW_init.php";
require_once MODULE_PATH . "/order.class.php";
require_once MODULE_PATH . "/customer.class.php";
require_once MODULE_PATH . "/user.class.php";
require_once MODULE_PATH . "/histry.drawer.php";
require_once MODULE_PATH . "/product.class.php";
require_once MODULE_PATH . "/stats.drawer.php";

$solo = $_user->get_customers_num() == 1 ? true : false;
$customers = $_user->get_customers();

if (!$this->request['start_date']) {
    $this->request['start_date'] = STATS_DRAWER::get_start_date($this->request);
}
if (!$this->request['end_date']) {
    $this->request['end_date'] = STATS_DRAWER::get_end_date($this->request);
}
if ($this->request['group'] == 'auto') {
    $this->request['group'] = STATS_DRAWER::auto_group($this->request['start_date'], $this->request['end_date']);
}

STATS_DRAWER::set_request($this->request);
STATS_DRAWER::set_solo($solo);

$condition = GFW::get_condition($this->request);
$orders_index = ORDER::get_orders_index_by_user_id($_SESSION['USER_ID'], false, $condition);

// line chart
$spending_line = array();
$spending_line['category'] = ORDER::get_line_category($this->request['group'], $condition);
if ($spending_line['category']) {
    foreach ($customers as $customer_id => $customer) {
        $customer_orders_index = ORDER::get_orders_index_by_customer_id($customer_id, false, $condition);
        if ($customer_orders_index) {
            if ($this->request['group'] == 'day') {
                $spending_line['customers'][$customer_id]['data'] = ORDER::group_orders_by_day(array_keys($customer_orders_index), $condition);
            } else if ($this->request['group'] == 'week') {
                $spending_line['customers'][$customer_id]['data'] = ORDER::group_orders_by_week(array_keys($customer_orders_index), $condition);
            } else if ($this->request['group'] == 'month') {
                $spending_line['customers'][$customer_id]['data'] = ORDER::group_orders_by_month(array_keys($customer_orders_index), $condition);
            }
            $spending_line['customers'][$customer_id]['name'] = $customer->get_firstname();
        }
    }

    if ($orders_index) {
        if ($this->request['group'] == 'day') {
            $spending_line['user']['data'] = ORDER::group_orders_by_day(array_keys($orders_index), $condition);
        } else if ($this->request['group'] == 'week') {
            $spending_line['user']['data'] = ORDER::group_orders_by_week(array_keys($orders_index), $condition);
        } else if ($this->request['group'] == 'month') {
            $spending_line['user']['data'] = ORDER::group_orders_by_month(array_keys($orders_index), $condition);
        }
        $spending_line['user']['name'] = $_user->get_firstname();
    }
}
if ($orders_index) {
    $condition = str_replace('DC.', '', $condition);
    if ($this->request['type'] == 'user') {
        $orders = ORDER::get_orders_by_user_id($_SESSION['USER_ID'], false, $condition);
    } else {
        $orders = ORDER::get_orders_by_customer_id($this->request['customer_id'], false, $condition);
    }
    ORDER::get_orders_details_by_orders($orders);
    ORDER::get_products_by_orders($orders);
    // bar chart
        $spending_bar = array();
        $spending_bar['category'] = ORDER::get_most_products($orders);

    // pie chart
    $spending_pie_left = array();
    $spending_pie_left['category'] = PRODUCT::get_dpts();
    $spending_pie_right = array();
    $spending_pie_right['category'] = PRODUCT::get_cates();

    $spending_pie_left['category'] = ORDER::group_products_by_department($orders, $spending_pie_left['category']);
    $spending_pie_right['category'] = ORDER::group_products_by_category($orders, $spending_pie_right['category']);
}
$find_result = $spending_line['category'] && $spending_line['user'] ? true : false;
echo '<table width="100%"><tr><td valign="top" style="padding: 15px; padding-right: 0px">
		<div id="stats_container" style="width: 100%;">';
echo STATS_DRAWER::draw_title($find_result, "spending_line_chart");
echo '
        <table width="100%">
            <tr>
                <td width="50%">
                    <div id="stats_spending_line"></div>
                </td>
                <td>
                    <div id="stats_spending_bar"></div>
                </td>
            </tr>
        </table>  
        <table width="100%">
            <tr>
                <td width="50%">
                    <div id="spending_pie_chart_left"></div>
                </td>
                <td>
                    <div id="spending_pie_chart_right"></div>
                </td>
            <tr>
        </table>
        <div id="spending_hidden"></div></div></td>
        <td width="10px"></td></tr></table>
        <script type="text/javascript">';
if ($find_result) {
    echo STATS_DRAWER::get_spending_bar($spending_bar);
    echo STATS_DRAWER::get_spending_line($spending_line);
    echo STATS_DRAWER::get_pie_chart($spending_pie_left, "Most Spending Departments<br>", "pie_left", "spending_pie_chart_left", 'pie_left#source=pie_left&start_date=' . $this->request['start_date'] . '&end_date=' . $this->request['end_date'], '$');
    echo STATS_DRAWER::get_pie_chart($spending_pie_right, "Most Spending Categories<br>", "pie_right", "spending_pie_chart_right", 'pie_right#source=pie_right&start_date=' . $this->request['start_date'] . '&end_date=' . $this->request['end_date'], '$');
}
echo '</script>';
?>
<script>
    Calendar.setup({
        inputField : "stats_start_date",
        trigger    : "set_stats_start_date",
        //onSelect   : function() { this.hide(); $("#histry_days").val(''); },
        dateFormat : "%Y-%m-%d",
        animation  : false
    });
    Calendar.setup({
        inputField : "stats_end_date",
        trigger    : "set_stats_end_date",
        //onSelect   : function() { this.hide(); $("#histry_days").val(''); },
        dateFormat : "%Y-%m-%d",
        animation  : false
    });  
    $("#stats_show_filter").click(function(){
        if(show_stats_filter==0){	
            show_stats_filter = 1;		
            $(this).val("Hide Filter");
            $('#stats_filter_panel_border').css('display', 'block');
        }
        else if(show_stats_filter==1){	
            show_stats_filter = 0;				
            $('#stats_filter_panel_border').css('display', 'none');
            $(this).val("Show Filter");
        }
    });
    $("#stats_container .button").mouseover(function () {
        $(this).css("box-shadow", "inset 0 0 10px rgba(255, 255, 255, 0.9)");
    });
    $("#stats_container .button").mouseleave(function () {
        $(this).css("box-shadow", "0 0 3px #000");
    });	
    $("#stats_container .button").mousedown(function () {
        $(this).removeClass("button");
        $(this).addClass("button_on");
    });
    $("#stats_container .button").mouseup(function () {
        $(this).removeClass("button_on");
        $(this).addClass("button");
    });
    $('#stats_apply_filter').click(function(){
        request='action=stats&apply_filter=1&start_date='+$('#stats_start_date').val()+'&end_date='+$('#stats_end_date').val()
            +'&group='+$('#stats_group option:selected').val();
        sendStatsMsg(request, $("#right_container"));
    });
</script>

