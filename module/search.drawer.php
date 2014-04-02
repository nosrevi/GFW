<?php

require_once MODULE_PATH . "/drawer.core.php";

class SEARCH_DRAWER extends DRAWER {

    static $request;

    static function set_request($_req) {
        parent::set_request($_req);
        self::$request = $_req;
    }

    static function get_page_nav($_num) {
        return parent::get_page_nav($_num);
    }

    static function get_msg($_change = NULL) {
        $para = self::$request;

        $para['keywords'] = implode(' ', $para['keywords']);
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

    // draw search title
    // $_order = the firsr order; $_num the sum all qualified orders
    static function draw_title($_resultCount) {
        // the words displayed in title
        $title_msg = '';
        // specified style
        $title_style = '';
        // pagnation part in nav bar 	
        $page_nav = '';

        $filter_button = '';
        $filter_panel = '';

        if ($_resultCount > 0) {
            $title_msg = 'You have found <span class="red bold bigger">' . $_resultCount . '</span> results';
            if (self::$request['page'] < 0) {
                $start_product = 1;
                $end_product = $_resultCount;
            } else {
                $start_product = (self::$request['page'] - 1) * self::$request['row'] + 1;
                $end_product = min($_resultCount, self::$request['page'] * self::$request['row']);
            }
            $title_msg .= ' Listed are the <span class="red bigger bold">' . $start_product . '</span>
						to <span class="red bigger bold">' . $end_product . '</span> products.';
        } else {
            $title_msg .= '<span class="red grey_shadow" style="font-size:20px;">';
            $title_msg .= 'Found Nothing</span>';
        }
        if (self::$request['filter']) {
            $filter_button = '<input type="button" value="Hide Filter" class="button" id="search_show_filter">';
            $filter_panel = '<div id="search_filter_panel_border" class="bg_white" ><div id="search_filter_panel">' . self::get_filter() . '</div></div>';
        } else {
            $filter_button = '<input type="button" value="Show Filter" class="button" id="search_show_filter">';
            $filter_panel = '<div id="search_filter_panel_border" class="bg_white hide" ><div id="search_filter_panel">' . self::get_filter() . '</div></div>';
        }
        if ($_resultCount > 0) {
            $page_nav = parent::get_page_nav($_resultCount);
        }
        $search_toolbar = '<div id="search_toolbar">' . $filter_button . '</div>';
        echo '<div id="search_title">
						<table style="width: 100%;"><tr><td style="width: 50%; height: 40px; text-align: center"><div id="search_title_msg">' . $title_msg . '</div></td><td>' . $search_toolbar . '</div></td></tr></table>
					</div>'
        . $filter_panel . $page_nav;
    }

    //draw department select box
    static function get_department_select($_dpt = NULL, $_selected_dpt = NULL) {
        $str = '<select id="search_category"><option value="ALL" selected="selected">ALL</option>';

        foreach ($_dpt as $dpt) {
            $selected = '';
            if ($_selected_dpt) {
                if ($dpt == $_selected_dpt) {
                    $selected .= 'selected="selected"';
                }
            }
            //not sure if i should escape 'no dept'
            if ($dpt == 'DEPARTMENT 00' || $dpt == 'NO DEPT')
                continue;
            $str .= '<option value="' . $dpt . '" ' . $selected . '>' . $dpt . '</option>';
        }
        $str .= '</select>';
        return $str;
    }

    static function get_sorting() {
        $order_select = array('', '', '', '');
        $order_index = 0;
        $order_index += self::$request['priority'] == 'vendor' ? 2 : 0;
        $order_index += self::$request[self::$request['priority']] == 'asc' ? 1 : 0;
        $order_select[$order_index] = "_light";

        return '<div style="float:left">
						Product
						<a link="#' . self::get_msg(array('priority' => 'name', 'name' => 'asc')) . '">
						<img src="images/arrow_asc' . $order_select[1] . '.png" width="14px" class="page_nav_arrow"></a>						
						<a link="#' . self::get_msg(array('priority' => 'name', 'name' => 'desc')) . '">
						<img src="images/arrow_desc' . $order_select[0] . '.png" width="14px" class="page_nav_arrow"></a>						

												
						&nbsp;&nbspVendor
						<a link="#' . self::get_msg(array('priority' => 'vendor', 'vendor' => 'asc')) . '">
						<img src="images/arrow_asc' . $order_select[3] . '.png" width="14px" class="page_nav_arrow"></a>							
						<a link="#' . self::get_msg(array('priority' => 'vendor', 'vendor' => 'desc')) . '">
						<img src="images/arrow_desc' . $order_select[2] . '.png" width="14px" class="page_nav_arrow"></a>											
					</div>';
    }

    static function get_filter() {

        $priority_selected = array('name' => NULL, 'vendor' => NULL);
        $priority_selected[self::$request['priority']] = 'selected="selected"';

        $order_selected = array('desc' => NULL, 'asc' => NULL);
        $order_selected[self::$request[self::$request['priority']]] = 'selected="selected"';

        $checkboxes = array('heb' => NULL, 'hlt' => NULL);
        if (self::$request['heb']) {
            $checkboxes['heb'] = 'checked="checked"';
        }
        if (self::$request['hlt']) {
            $checkboxes['hlt'] = 'checked="checked"';
        }

        $category = self::get_department_select(PRODUCT::get_dpts(), self::$request['category']);
        $filter_table = '<table><tr>
							<th>Keywords <input id="search_keywords" name="search_keywords" type="text" size="40" value="' . implode(' ', self::$request['keywords']) . '"></th><th></th>
						</tr></table><table><tr>
						<th>In Category ' . $category . '</th>
						<th><input id="search_heb" name="search_heb" type="checkbox" value="yes" ' . $checkboxes['heb'] . '> HEB Brand</th>
				 		<th><input id="search_healthy" name="search_healthy" type="checkbox" value="yes" ' . $checkboxes['hlt'] . '> Healthy</th>
						</tr></table>
						<table><tr>
							<th>Order by <select name="search_prority" id="search_priority">
								<option ' . $priority_selected['name'] . ' value="name">Product Name</option>
								<option ' . $priority_selected['vendor'] . ' value="vendor">Product Vendor</option></select>
							</th>
							<th>In <select name="search_order" id="search_order">
								<option ' . $order_selected['asc'] . ' value="asc">Ascdent</option>
								<option ' . $order_selected['desc'] . ' value="desc">Descdent</option>
							</select></th>								
							<th></th>
						</tr></tbody></table>						
						<table width="100%"><tr>
							<th style="padding: 10px 0 5px"><center>
								<input type="button" id="search_apply_filter" class="button" value="Apply Filter">
							</center></th>
						</tr></table>';
        return $filter_table;
    }

    static function get_order_caption() {
        $caption = 'example <span class="red">">caption</span>';

        return $caption;
    }

    static function draw_entity($_product_id, $_product_inf, $_purchased = false) {
        $vendor = str_replace(' ', '_', $_product_inf['VENDOR']);
        echo '<div id="search_result_' . $_product_id . '" class="search_result" name="' . $_product_id . '">
				<div class="search_result_image">
					<img class="search_product_img" alt="Product Details" src="' . LOGO_PATH . $vendor . '.jpg">
				</div>
				<div class="search_result_data">';
        $detail = '';
        if ($_product_inf['HEB_BRAND_FLAG'] == 'Y') {
            $detail .= '<img title="H-E-B Brand" class="product_type_icon" style="margin: 5px 0" src="images/t_heb.png"">';
        }
        if ($_product_inf['HEALTHY_LIVING_FLAG'] == 'Y') {
            $detail .= '<img title="Healthy Living" class="product_type_icon" style="margin: 5px 0" src="images/t_hea.png"">';
        }
        echo '<div class="search_result_des"><span class="red bold">' . $_product_inf['PRODUCT_DESCRIPTION'] . ' </span></div>';
        echo '<div style="margin-bottom: 10px"><span class="blue">' . $_product_inf['CATEGORY'] . '</span><br>' . $detail . '<br>BY ' . $_product_inf['VENDOR'] . '</div>';
        if ($_purchased) {
            echo '<div style="text-align: center; margin-top: 5px">
					<input type="submit" id="addd_list_' . $_product_id . '" class="button view_histry" value="Shopping History" onclick="sendUPCMsg(' . $_product_id . ')"/>
				</div>';
        }
        //echo '<div style="text-align: center; margin-top: 5px"><input type="submit" id="add_list_' . $_product_id . '" class="button add_to_list" value="Add to List" /></div>';
        echo '</div></div>';
    }

}

?>
