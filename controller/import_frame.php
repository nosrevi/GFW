<?php

require_once "GFW_init.php";

$frame = new Frame();

class Frame {

    public function print_frame($page_name = "default", $print = false) {
        global $js;
        global $style;

        $str = "<!DOCTYPE html>\n";
        $str .= "<head>\n";
        $str .= "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\n";
        $str .= "<link rel=\"shortcut icon\" href=\"http://www.heb.com/static/images/favicon.ico\" type=\"image/x-icon\" />\n";
        $str .= $style->print_css($page_name);
        $str .= $js->print_js($page_name);
        $str .= "<title>H-E-B Explorer</title>\n";
        $str .= "</head>\n";

        if ($print) {
            printf($str);
        } else {
            return $str;
        }
    }

    function __construct() {
        
    }

}

