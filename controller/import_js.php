<?php

require_once "GFW_init.php";

$js = new Js();

class Js {

    private $js_list = array();

    public function print_js($page_name, $print = false) {
        $str = "<script type='text/javascript' src='" . JS_PATH . "/jquery-latest.min.js'></script>\n";
        $str .= "<script type='text/javascript' src='" . JS_PATH . "/global_js.js'></script>\n";
        $str .= "<script type='text/javascript' src='" . JS_PATH . '/' . $page_name . "_js.js'></script>\n";
        if (isset($this->js_list[$page_name])) {
            foreach ($this->js_list[$page_name] as $js_file) {
                $str .= "<script type='text/javascript' src='$js_file'></script>\n";
            }
        }

        if ($print) {
            printf($str);
        } else {
            return $str;
        }
    }

    function __construct() {
                $this->js_list['home'][] = JS_PATH.'/JSCal2-1.9/js/jscal2.js';
		$this->js_list['home'][] = JS_PATH.'/JSCal2-1.9/js/lang/en.js';
		$this->js_list['home'][] = JS_PATH.'/Highcharts-2.1.6/js/highcharts.js';
		$this->js_list['home'][] = JS_PATH.'/Highcharts-2.1.6/js/modules/exporting.js';
	//	$this->js_list['home'][] = JS_PATH.'/Highcharts-2.1.6/js/themes/gray.js';
    }

}

