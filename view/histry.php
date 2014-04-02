<?php
require_once "GFW_init.php";
require_once MODULE_PATH . "/order.class.php";
require_once MODULE_PATH . "/customer.class.php";
require_once MODULE_PATH . "/user.class.php";
require_once MODULE_PATH . "/histry.drawer.php";
require_once MODULE_PATH . "/product.class.php";
require_once MODULE_PATH . "/store.class.php";

HISTRY_DRAWER::set_request($this->request);
HISTRY_DRAWER::set_solo($_user->get_customers_num() == 1);

// get query condition based on request
$condition = GFW::get_condition($this->request);
$orders_num = 0;
$orders = array();

// get all the user's shopping history
if ($this->request['type'] == 'user') {
    if(isset($this->request['upc']) && $this->request['upc']){
        $orders = ORDER::get_orders_by_user_id_and_upc($_SESSION['USER_ID'], $this->request['upc'], false, $condition);
    }
    else{
        $orders = ORDER::get_orders_by_user_id($_SESSION['USER_ID'], false, $condition);
    }
}
// get the customer's shooping history
else if($this->request['type'] == 'customer'){
    if(isset($this->request['upc']) && $this->request['upc']){
        $orders = ORDER::get_orders_by_customer_id_and_upc($this->request['customer'], $this->request['upc'], false, $condition, $_SESSION['USER_ID']);
    }
    else{    
        $orders = ORDER::get_orders_by_customer_id($this->request['customer'], false, $condition, $_SESSION['USER_ID']);
    }
}

if($orders){
    $orders_num = count($orders);
    if ($this->request['page'] > 0 && $orders_num > $this->request['row']) {
        $orders = array_slice($orders, ($this->request['page'] - 1) * $this->request['row'], $this->request['row'], true);
    }
}

echo '<table width="100%"><tr><td valign="top" style="padding: 15px; padding-right: 0px"><div id="histry_container">';

HISTRY_DRAWER::draw_title($orders ? reset($orders) : NULL, $orders_num);

ORDER::get_orders_details_by_orders($orders);
ORDER::get_products_by_orders($orders);
STORE::get_stores_by_orders($orders);

if ($orders) {
    $counter = 0;
    echo '<div id="histry_orders">';

    foreach ($orders as $order) {
        HISTRY_DRAWER::draw_entity($order, $counter++);
    }
    // end of histry_orders
    echo '</div>';
    if ($this->request['day'] != 0) {
        echo HISTRY_DRAWER::get_page_nav($orders_num);
    }
}
// end of histry_container
echo '</div></td>';
echo '<td width="10px"></td>';

// end of histry_toobar
echo '</tr></table>';
?>
<script>
<?php 
	echo $this->request['filter']?"show_histry_filter=1;":"show_histry_filter=0;"; 
?>	
    Calendar.setup({
        inputField : "histry_start_date",
        trigger    : "set_histry_start_date",
        onSelect   : function() { this.hide(); $("#histry_days").val(''); },
        dateFormat : "%Y-%m-%d",
		animation  : false
      });
    Calendar.setup({
        inputField : "histry_end_date",
        trigger    : "set_histry_end_date",
        onSelect   : function() { this.hide(); $("#histry_days").val(''); },
        dateFormat : "%Y-%m-%d",
		animation  : false
      });	  
    $("#histry_show_filter").click(function(){
        if(show_histry_filter==0){	
            show_histry_filter = 1;		
            $(this).val("Hide Filter");
            $('#histry_filter_panel_border').css('display', 'block');
        }
        else if(show_histry_filter==1){	
            show_histry_filter = 0;				
            $('#histry_filter_panel_border').css('display', 'none');
            $(this).val("Show Filter");
        }
    });
    $("#histry_show_detail").click(function(){
        if(show_histry_detail==0){
            show_histry_detail = 1;
            $(this).val("Hide Detail");
            $('.order_body').slideDown('fast');
            $('.show_detail_img').css('display','none');
            $('.hide_detail_img').css('display','inline');
        }
        else if(show_histry_detail==1){
            show_histry_detail = 0;
            $(this).val("Show Detail");
            $('.order_body').css('display', 'none');
            $('.show_detail_img').css('display','inline'); 
            $('.hide_detail_img').css('display','none');        
        }
    });
	$("#histry_days").focus(function(){
		$("#histry_start_date").val('');
		$("#histry_end_date").val('');
	});
    $("#histry_start_date").focus(function(){
		$("#histry_days").val('');
    });
    $("#histry_end_date").focus(function(){
		$("#histry_days").val('');
    });
    $("#histry_container .button").mouseover(function () {
        $(this).css("box-shadow", "inset 0 0 10px rgba(255, 255, 255, 0.9)");
    });
    $("#histry_container .button").mouseleave(function () {
        $(this).css("box-shadow", "0 0 3px #000");
    });	
    $("#histry_container .button").mousedown(function () {
        $(this).removeClass("button");
        $(this).addClass("button_on");
    });
    $("#histry_container .button").mouseup(function () {
        $(this).removeClass("button_on");
        $(this).addClass("button");
    });
    $("#histry_container a").click(function () {
        sendHistryMsg($(this).attr('link').replace("#", ""), $('#right_container'));
    }); 
	
    function show_order_detail(id){
        $("#show_detail_"+id).css('display', 'none');
        $("#hide_detail_"+id).css('display', 'inline');	
        $("#order_body_"+id).css('display', 'block');
    }
    function hide_order_detail(id){
        $("#hide_detail_"+id).css('display', 'none');
        $("#show_detail_"+id).css('display', 'inline');		
        $("#order_body_"+id).css('display', 'none');
    }

	$('#histry_apply_filter').click(function(){
		type = $('#histry_customer option:selected').val() > 0 ? "customer": "user";
		request='action=histry&apply_filter=1&type='+type+'&start_date='+$('#histry_start_date').val()+'&end_date='+$('#histry_end_date').val()
				+'&day='+$('#histry_days').val()+'&paymore='+$('#histry_payment_more').val()+'&payless='+$('#histry_payment_less').val()
				+'&priority='+$('#histry_priority option:selected').val()+'&'+$('#histry_priority option:selected').val()+'='+$('#histry_order option:selected').val()
				+'&customer='+$('#histry_customer option:selected').val();
		sendHistryMsg(request, $("#right_container"));
    });

	$('#histry_start_date').click(function(){
	});
	$('#histry_end_date').click(function(){
    });
</script>
