<?php

require_once MODULE_PATH . "/drawer.core.php";

class HISTRY_DRAWER extends DRAWER {

    static $request;
    // there is only ony customer for this user;
    public static $solo;

    static function set_request($_req) {
        parent::set_request($_req);
        self::$request = $_req;
    }

    static function get_page_nav($_num) {
        return parent::get_page_nav($_num);
    }

    static function set_solo($_solo) {
        self::$solo = $_solo;
    }

    // draw histry link based on request received
    // $_change indicates which elements gonna be replaced by what value		
    static function get_msg($_change = NULL) {
        $para = self::$request;
        if (isset($para['row'])) {
            unset($para['row']);
        }
        if (isset($para['detail'])) {
            unset($para['detail']);
        }
        if (isset($para['filter'])) {
            unset($para['filter']);
        }
        // replace values first		
        if ($_change) {
            foreach ($_change as $key => $value) {
                $para[$key] = $value;
            }
        }
        // combine the values from the request into a message with &			
        foreach ($para as $key => $value) {
            $para[$key] = $key . '=' . $value;
        }
        $msg = implode('&', $para);
        return $msg;
    }

    // draw histry title
    // $_order = the firsr order; $_num the sum all qualified orders
    static function draw_title($_order, $_num) {
        // the words displayed in title
        $title_msg = '';
        // specified style
        $title_style = '';
        // pagnation part in nav bar 	
        $page_nav = '';

        $filter_button = '';
        $filter_panel = '';
        $detail_button = '';

        // didn't find any orders
        if (!$_order) {
            $title_msg .= '<span class="red grey_shadow" style="font-size:20px;">';
            if (self::$request['day'] == 0) {
                $title_msg .= 'You don not have any orders yet';
            } else if (!self::$request['apply_filter']) {
                $title_msg .= 'You don not have any order during the past ' . self::$request['day'] . ' days';
            } else if (self::$request['apply_filter']) {
                $title_msg .= 'You don not have any order matches this search.<br>';
            }
            $title_msg .= '</span>';
        } else {
            // show latest order, only one order
            if (self::$request['day'] == 0) {
                if (isset(self::$request['limit']) && self::$request['limit'] == 1) {
                    $title_msg .= 'Your latest order was on <span class="blue bigger">'
                            . $_order->get_order_date() . '</span>';
                } else {
                    $title_msg .= 'Listed are your past <span class="blue bigger">' . self::$request['row'] . '</span> orders';
                }
            }
            // otherwise
            else {
                // get the first order to be shown
                $title_msg .= 'You have <span class="blue bigger">' . $_num . '</span> orders';
                if (self::$request['day'] > 0 && !self::$request['apply_filter']) {
                    $title_msg .= ' in the past <span class="blue bigger">' . self::$request['day'] . '</span> days<br>';
                } else if (self::$request['apply_filter']) {
                    $title_msg .= ' match this search<br>';
                } else if (isset(self::$request['upc'])) {
					require_once MODULE_PATH . "/product.class.php";
					$product = new PRODUCT(self::$request['upc']);
                    $title_msg .= ' including <span class="red">'.$product->get_product_description().'</span><br>';
                }				
                // for "show all orders", self::$request['page'] is set to -1 as a sign. now restore it to 1
                if (self::$request['page'] < 0) {
                    $start_order = 1;
                    $end_order = $_num;
                } else {
                    $start_order = (self::$request['page'] - 1) * self::$request['row'] + 1;
                    $end_order = min($_num, self::$request['page'] * self::$request['row']);
                }

                $title_msg .= '. Listed are the <span class="blue bigger">' . $start_order . '</span>
							 to <span class="blue bigger">' . $end_order . '</span> orders.';
                $page_nav = self::get_page_nav($_num);
            }

            $show_detail = (self::$request['detail'] || (self::$request['day'] == 0 && isset(self::$request['limit']) && self::$request['limit'] == 1)) ? true : false;

            if ($show_detail) {
                $detail_button = '<input type="button" value="Hide Detail" class="button" id="histry_show_detail">';
            } else {
                $detail_button = '<input type="button" value="Show Detail" class="button" id="histry_show_detail">';
            }
        }
        if (self::$request['filter']) {
            $filter_button = '<input type="button" value="Hide Filter" class="button" id="histry_show_filter">';
            $filter_panel = '<div id="histry_filter_panel_border" class="bg_white" ><div id="histry_filter_panel">' . self::get_filter() . '</div></div>';
        } else {
            $filter_button = '<input type="button" value="Show Filter" class="button" id="histry_show_filter">';
            $filter_panel = '<div id="histry_filter_panel_border" class="bg_white hide" ><div id="histry_filter_panel">' . self::get_filter() . '</div></div>';
        }
        $histry_toolbar = '<div id="histry_toolbar">' . $detail_button . $filter_button . '</div>';
        echo '<div id="histry_title">
						<table style="width: 100%;"><tr><td style="width: 50%; height: 40px; text-align: center"><div id="histry_title_msg">' . $title_msg . '</div></td><td>' . $histry_toolbar . '</div></td></tr></table>
					</div>'
        . $filter_panel . $page_nav;
    }

    // get the histry filter
    static function get_filter() {
        $day = NULL;

        if (isset(self::$request['day']) && self::$request['day'] >= 0) {
            $day = self::$request['day'];
        }

        $priority_selected = array('payment' => NULL, 'date' => NULL);
        $priority_selected[self::$request['priority']] = 'selected="selected"';

        $order_selected = array('desc' => NULL, 'asc' => NULL);
        $order_selected[self::$request[self::$request['priority']]] = 'selected="selected"';

        $filter_table = '<table><tr>';
        $filter_table .= '<th>Date from <input type="text" value="' . self::$request['start_date'] . '" size="10" maxlength="10" name="histry_start_date" id="histry_start_date"/><img src="images/calendar.png" id="set_histry_start_date"></th>
							<th>Date to <input type="text" value="' . self::$request['end_date'] . '" size="10" maxlength="10" name="histry_end_date" id="histry_end_date"/><img src="images/calendar.png" id="set_histry_end_date"></th>
							<th><span class="red">OR</span>  in the past <input type="text" value="' . $day . '" size="3" maxlength="3"  name="histry_days" id="histry_days"/> days</th>
							<th></th></tr></table>
						<table><tr><th>Payment more than <input type="text" value="' . self::$request['paymore'] . '" size="6" maxlength="6" name="histry_payment_more" id="histry_payment_more"/></th>
							<th>less than <input type="text" value="' . self::$request['payless'] . '" size="6" maxlength="6" name="histry_payment_less" id="histry_payment_less"/></th>
							<th></th><th></th></tr></table>
						<table><tr>
							<th>Order by <select id="histry_priority" name="histry_prority"><option value="date" ' . $priority_selected['date'] . '>Date</option><option value="payment" ' . $priority_selected['payment'] . '>Payment</option></select></th>
							<th>In <select id="histry_order" name="histry_order"><option value="desc" ' . $order_selected['desc'] . '>Descdent</option><option value="asc" ' . $order_selected['asc'] . '>Ascdent</option></select></th>';
        if (self::$solo) {
            $filter_table .= '<th></th>';
        } else {
            $customers = USER::get_customers_by_user_id($_SESSION['USER_INF']['USER_ID']);
            $options = "<option value='0'>All Customers</option>";
            foreach ($customers as $key => $value) {
                $options .= '<option value="' . $key . '">' . $value->get_firstname() . '</option>';
            }
            $filter_table .= '<th style="width: 45%;">By <select id="histry_customer" name="histry_order">' . $options . '</select></th>';
        }
        $filter_table .= '</tr></table><table width="100%"><tr>';
        $filter_table .= '<th style="padding: 10px 0 5px"><center><input type="button" id="histry_apply_filter" class="button" value="Apply Filter"></center></th>';
        $filter_table .= '</tr></table>';
        return $filter_table;
    }

    static function get_sorting() {
        $order_select = array('', '', '', '');
        $order_index = 0;
        $order_index += self::$request['priority'] == 'payment' ? 2 : 0;
        $order_index += self::$request[self::$request['priority']] == 'asc' ? 1 : 0;
        $order_select[$order_index] = "_light";

        return '<div style="float:left">
						Date
						<a link="#' . self::get_msg(array('priority' => 'date', 'date' => 'desc')) . '">
						<img src="images/arrow_desc' . $order_select[0] . '.png" width="14px" class=page_nav_arrow"></a>						
						<a link="#' . self::get_msg(array('priority' => 'date', 'date' => 'asc')) . '">
						<img src="images/arrow_asc' . $order_select[1] . '.png" width="14px" class="page_nav_arrow"></a>
												
						&nbsp;&nbspPayment
						<a link="#' . self::get_msg(array('priority' => 'payment', 'payment' => 'desc')) . '">
						<img src="images/arrow_desc' . $order_select[2] . '.png" width="14px" class="page_nav_arrow"></a>	
						<a link="#' . self::get_msg(array('priority' => 'payment', 'payment' => 'asc')) . '">
						<img src="images/arrow_asc' . $order_select[3] . '.png" width="14px" class="page_nav_arrow"></a>											
					</div>';
    }

    static function get_order_caption($_order, $_customer) {
        $caption = $_order->get_order_date() . ' -&nbsp;';
        if (!self::$solo) {
            $caption .= $_customer->get_firstname() . '&nbsp;';
        }
        $caption .= 'spent $' . number_format($_order->get_total_amount(), 2, '.', '') . '&nbsp;';
        $order_detail = $_order->get_order_detail();

        // this is a return (payment < 0 without purchasing any products)
        if ($order_detail == -1) {
            return $caption;
        }
        $detail_num = count($order_detail);
        if ($detail_num == 1) {
            $product = new PRODUCT($order_detail[0]['UPC']);
            $caption .= 'for <span class="red">' . $product->get_product_description() . '</span>&nbsp;';
        } else if ($detail_num == 2) {
            ORDER::sort_products_by_sales($order_detail);
            $product_1 = new PRODUCT($order_detail[0]['UPC']);
            $product_2 = new PRODUCT($order_detail[1]['UPC']);
            $caption .= 'for <span class="red">' . $product_1->get_product_description() . '</span> and <span class="red">' . $product_2->get_product_description() . '</span>&nbsp;';
        } else {
            ORDER::sort_products_by_sales($order_detail);
            $product = new PRODUCT($order_detail[0]['UPC']);
            $caption .= 'for <span class="red">' . $product->get_product_description() . '</span> and other <span class="red">' . ($detail_num - 1) . '</span> products&nbsp;';
        }
        return $caption;
    }

    static function draw_entity($_order, $_seq) {
        global $_listener;
        $customer = CUSTOMER::get_customer_by_customer_id($_order->get_customer_id());
        $order_id = $_order->get_order_id();

        $hide = (self::$request['detail'] || (self::$request['day'] == 0 && isset(self::$request['limit']) && self::$request['limit'] == 1)) ? '' : ' hide';
        echo '<div class="order_border hide">
					<div class="order_title bg_white">
						<table style="width:100%"><tr><td style="padding: 4px 0">					
						<div class="order_caption" id="order_caption_' . $order_id . '">' . self::get_order_caption($_order, $customer) . '</div>
						</td><td style="width: 25px" valign="middle">
						<div class="order_display_button" id="order_display_button_' . $order_id . '">' . self::get_order_display_button($order_id, $hide) . '</div>
						</td></tr></table>
					</div>
					<div class="order_body ' . $hide . '" id="order_body_' . $order_id . '">' . self::get_detail($_order) . '</div>';
        echo '</div>';
    }

    static function get_order_display_button($_order_id, $_hide) {
        if ($_hide) {
            return '<img id="show_detail_' . $_order_id . '" src="images/plus_blue.png" class="histry_display_button show_detail_img" onclick="show_order_detail(' . $_order_id . ')">
						<img id="hide_detail_' . $_order_id . '" src="images/minus_blue.png" class="histry_display_button hide_detail_img hide" onclick="hide_order_detail(' . $_order_id . ')">';
        } else {
            return '<img id="show_detail_' . $_order_id . '" src="images/plus_blue.png" class="histry_display_button hide show_detail_img" onclick="show_order_detail(' . $_order_id . ')">
						<img id="hide_detail_' . $_order_id . '" src="images/minus_blue.png" class="histry_display_button hide_detail_img" onclick="hide_order_detail(' . $_order_id . ')">';
        }
    }

    static function get_detail($_order) {
        $detail = '<table style="width:95%; margin: 5px auto; text-align: center">';
        $detail .= '<tr><th>Product</th><th style="width:80px">Quantity</th><th style="width:80px">Retail</th><th style="width:80px">Discount</th><th style="width:80px">Type</th><th style="width:100px"></th><th style="width:20px"></th></tr>';
        $detail .= '<tr><td colspan="7" style="padding: 0; padding-top: 2px"><hr></td></tr>';
        $product_inf = $_order->get_product_inf();
        $counter = 0;
        if ($_order->get_order_detail() != -1) {
            foreach ($_order->get_order_detail() as $order_detail) {
                if (++$counter % 2 == 1) {
                    $detail .= '<tr style="background-color: #FFEEEE">';
                } else {
                    $detail .= '<tr>';
                }
                if (isset(self::$request['upc']) && self::$request['upc'] == $order_detail['UPC']) {
                    $detail .= '<td align="left" style="padding-right: 20px"><span class="blue">' . $product_inf[$order_detail['UPC']]['PRODUCT_DESCRIPTION'] . '</span></td>';
                } else {
                    $detail .= '<td align="left" style="padding-right: 20px">' . $product_inf[$order_detail['UPC']]['PRODUCT_DESCRIPTION'] . '</td>';
                }
                $detail .= '<td>' . $order_detail['ITEM_QUANTITY'] . '</td>';
                $detail .= '<td>$' . $order_detail['TOTAL_RETAIL_AMOUNT'] . '</td>';
                $detail .= '<td>$' . $order_detail['TOTAL_DISCOUNT_AMOUNT'] . '</td>';
                $detail .= '<td align="right">';
                if ($product_inf[$order_detail['UPC']]['HEB_BRAND_FLAG'] == 'Y') {
                    $detail .= '<img title="H-E-B Brand" class="product_type_icon" src="images/t_heb.png"">';
                }
                if ($product_inf[$order_detail['UPC']]['HEALTHY_LIVING_FLAG'] == 'Y') {
                    $detail .= '<img title="Healthy Living" class="product_type_icon" src="images/t_hea.png"">';
                }
                if ($order_detail['TAX_FLAG'] == 'Y') {
                    $detail .= '<img title="Tax" class="product_type_icon" src="images/t_tax.png"">';
                }
                if ($order_detail['FOOD_STAMP_FLAG'] == 'Y') {
                    $detail .= '<img title="Food Stamp" class="product_type_icon" src="images/t_foo.png"">';
                }
                $detail .= '</td>';
                if (isset(self::$request['upc']) && self::$request['upc'] == $order_detail['UPC']) {
                	$detail .= '<td></td>';
                } else {
                	$detail .= '<td><a class="blue bold product_a" onclick="sendUPCMsg(' . $order_detail['UPC'] . ')" title="show all orders including '.$product_inf[$order_detail['UPC']]['PRODUCT_DESCRIPTION'].'">View Histroy</a></td>';
				}
                $detail .= '<td></td>';
                $detail .= '</tr>';
            }
        }
        $detail .= '<tr><td colspan="6" align="right" style="padding: 0;"><hr>
					<div style="padding: 4px 10px 0" class="bigger blue">Total: $' . $_order->get_total_amount() . '&nbsp;&nbsp;&nbsp;&nbsp;Sale: $' . $_order->get_sales_amount() . '&nbsp;&nbsp;&nbsp;&nbsp;Tax: $' . $_order->get_tax_amount() . '&nbsp;&nbsp;&nbsp;&nbsp;Discount: $' . $_order->get_discount_amount() . '</div><div style="padding: 2px 10px 8px" class="blue">' . $_order->get_store_name() . '</div>
				</td></tr></table>';
        return $detail;
    }

}

?>
