<?php

require_once MODULE_PATH . "/drawer.core.php";

class STATS_DRAWER extends DRAWER {

    static $request;
    // there is only ony customer for this user;
    public static $solo;

    static function set_request($_req) {
        parent::set_request($_req);
        self::$request = $_req;
    }

    static function set_solo($_solo) {
        self::$solo = $_solo;
    }

    static function num_month($_str) {
        $replace = array(
            'january' => '01', 'jan' => '01',
            'february' => '02', 'feb' => '02',
            'march' => '03', 'mar' => '03',
            'april' => '04', 'apr' => '04',
            'may' => '05', 'may' => '05',
            'june' => '06', 'jun' => '06',
            'july' => '07', 'jul' => '07',
            'august' => '08', 'aug' => '08',
            'september' => '09', 'sep' => '09',
            'october' => '10', 'oct' => '10',
            'november' => '11', 'nov' => '11',
            'december' => '12', 'dec' => '12'
        );
        return $replace[strtolower($_str)];
    }

    static function wrap_date($_date) {
        if (self::$request['date_type'] == 'all') {
            self::$request['date_type'] = self::$request['group'];
        }
        $start_date = '';
        $end_date = '';
        if (self::$request['date_type'] == 'day') {
            return "start_date=" . $_date . "&end_date=" . $_date;
        } else if (self::$request['date_type'] == 'week') {
            $date = str_replace(',', '', $_date);
            $date = explode(' - ', $date);
            $start_date = $date[0];
            $end_date = $date[1];
            $start_date = explode(' ', $start_date);
            $end_date = explode(' ', $end_date);
            if (strlen($start_date[1]) == 1) {
                $start_date[1] = '0' . $start_date[1];
            }
            if (strlen($end_date[1]) == 1) {
                $end_date[1] = '0' . $end_date[1];
            }
            $start_date = $start_date[2] . '-' . self::num_month($start_date[0]) . '-' . $start_date[1];
            $end_date = $end_date[2] . '-' . self::num_month($end_date[0]) . '-' . $end_date[1];
            //return "start_date=".$start_date."&end_date=".$end_date;
        } else if (self::$request['date_type'] == 'month') {
            $date = explode(' ', $_date);
            $start_date = $date[1] . '-' . self::num_month($date[0]) . '-01';
            $end_date = strtotime($start_date);
            $end_date = date('Y-m-d', mktime(0, 0, 0, date("m", $end_date) + 1, date("d", $end_date) - 1, date("Y", $end_date)));
            //return "start_date=".$start_date."&end_date=".$end_date;
        }
        if ($start_date && $end_date) {
            if (strtotime($start_date) < strtotime(self::$request['start_date'])) {
                $start_date = self::$request['start_date'];
            }
            if (strtotime($end_date) > strtotime(self::$request['end_date'])) {
                $end_date = self::$request['end_date'];
            }
            return "start_date=" . $start_date . "&end_date=" . $end_date;
        }
        return false;
    }

    static function wrap_category($_date) {
        $start_date = '';
        $end_date = '';
        if (self::$request['group'] == 'day') {
            return $_date;
        } else if (self::$request['group'] == 'week') {
            $date = str_replace(',', '', $_date);
            $date = explode(' - ', $date);
            $start_date = $date[0];
            $end_date = $date[1];
            $start_date = explode(' ', $start_date);
            $end_date = explode(' ', $end_date);
            if (strlen($start_date[1]) == 1) {
                $start_date[1] = '0' . $start_date[1];
            }
            if (strlen($end_date[1]) == 1) {
                $end_date[1] = '0' . $end_date[1];
            }
            $start_date = $start_date[2] . '-' . self::num_month($start_date[0]) . '-' . $start_date[1];
            $end_date = $end_date[2] . '-' . self::num_month($end_date[0]) . '-' . $end_date[1];
        } else if (self::$request['group'] == 'month') {
            $date = explode(' ', $_date);
            $start_date = $date[1] . '-' . self::num_month($date[0]) . '-01';
            $end_date = strtotime($start_date);
            $end_date = date('Y-m-d', mktime(0, 0, 0, date("m", $end_date) + 1, date("d", $end_date) - 1, date("Y", $end_date)));
        }
        if ($start_date && $end_date) {
            if (strtotime($start_date) < strtotime(self::$request['start_date'])) {
                $start_date = self::$request['start_date'];
            }
            if (strtotime($end_date) > strtotime(self::$request['end_date'])) {
                $end_date = self::$request['end_date'];
            }
            return $start_date . " to " . $end_date;
        }
        return false;
    }

    static function get_spending_line($_spending_line) {
        if (!$_spending_line['user']) {
            return false;
        }

        $title = "Spending History Overview<br>";
        if (self::$request['start_date'] == self::$request['end_date']) {
            $title .= "on " . self::$request['start_date'];
        } else {
            $title .= "from " . self::$request['start_date'] . " to " . self::$request['end_date'];
        }
        
        $line_action = "line_stable#date=" . self::$request['group'] . '&';
        if (self::$request['group'] != 'day') {
            $column_action = "line#date=" . self::$request['group'] . '&';
        } else {
            $column_action = "line_stable#date=" . self::$request['group'] . '&';
        }
        $categories = $_spending_line['category'];
        $series = '';

        $data = array();
        foreach ($categories as $key) {
            $warped_date = self::wrap_date($key);
            if (isset($_spending_line['user']['data'][$key])) {
                $data[] = "{name: 'type=user&" . $warped_date . "', y: " . $_spending_line['user']['data'][$key] . "}";
            } else {
                $data[] = "{name: 'type=user&" . $warped_date . "', y: 0 }";
            }
        }
        $series .= "{type: 'column', name: 'In Sum', data: [" . implode(',', $data) . "]}";


        foreach ($_spending_line['customers'] as $customer_id => $spending) {
            $data = array();
            foreach ($categories as $key) {
                $warped_date = self::wrap_date($key);
                if (isset($spending['data'][$key])) {
                    $data[] = "{name: 'type=customer&customer_id=" . $customer_id . "&" . $warped_date . "', y: " . $spending['data'][$key] . "}";
                } else {
                    $data[] = "{name: 'type=customer&customer_id=" . $customer_id . "&" . $warped_date . "', y: 0}";
                }
            }
            $series .= ", {name: '" . $spending['name'] . "', data: [" . implode(',', $data) . "]}";
        }

        $series = "series: [" . $series . "]";

        $i = 0;
        foreach ($categories as $value) {
            $categories[$i++] = self::wrap_category($value);
        }

        $str = "
            column_action = '" . $column_action . "';
            line_action = '" . $line_action . "';
            var spending_line_chart = new Highcharts.Chart({
                chart: {
                    renderTo: 'stats_spending_line', 
                    defaultSeriesType: 'line',
                    marginBottom: 40, marginLeft:40, marginRight: 10,
                    marginTop: 80,
                },
                title: {
                    text: '".$title."', y: 18, 
                    style:{ fontSize: '15px', color: '#004891', fontFamily:'calibri', fontWeight: 'bold'}
                },
                subtitle: { text: ' ', x: -20 },
                xAxis: {
                    categories: ['" . implode('\',\'', $categories) . "'],					
                    labels: {
                        y: 15, step: " . (((count($categories) * strlen($categories[0])) / 60 + 1) % 100) . ",
                        style:{ fontSize: '11px', fontFamily:'calibri', color: '#333'}
                    },  
                },
                yAxis: {
                    title: { text: null },						
                    plotLines: [{ value: 0, width: 1, color: '#808080'}],
                    labels: { y: 10,
                        formatter: function() {
                            return '\$'+this.value;
                        },
                        style:{ color: '#333', fontSize: '12px', fontFamily:'calibri', }
                    },
                    showFirstLabel: false,												
                },
                plotOptions: {
                    column: {
                        cursor: 'pointer',
                        point: { events: {
                            click: function() {
                                sendChartMsg(column_action+this.name);
                            }}},
                        marker: { lineWidth: 1 }},
                    line: {
                        cursor: 'pointer',
                        point: { events: {
                            click: function() {
                                sendChartMsg(line_action+this.name);
                            }}},
                        marker: {  lineWidth: 1 }}                                            
                },								
                tooltip: {
                    style:{ color: '#333', fontSize: '12px', fontFamily:'calibri', },
                    formatter: function() {
                        return '<b>'+ this.series.name +'</b>spent \$'+ this.y +'<br /> during '+ this.x.replace('<br>-<br>', ' to ');;
                    }},
                legend: {
                        align: 'left', verticalAlign: 'top', y: 38, floating: true, borderWidth: 0, 
                        itemStyle:{ fontSize: '12px', fontFamily:'calibri', color: '#333'}
                },
                " . $series . "});";
        return $str;
    }

    static function get_spending_bar($_spending_bar, $_title = '') {
        if(!$_spending_bar['category']){
            return;
        }
        $categories = array();
        $data = array();
        
        if (self::$request['start_date'] == self::$request['end_date']) {
            $_title .= " on " . self::$request['start_date'];
        } else {
            $_title .= " from " . self::$request['start_date'] . " to " . self::$request['end_date'];
        }
        
        foreach ($_spending_bar['category'] as $key => $_item) {
            $categories[] = "'" . $_item['name'] . "'";
            $data[] = "['$key'," . $_item['payment'] . "]";
        }
        $cate = implode(',', $categories);
        $series = "{name: 'In Sum', data: [" . implode(',', $data) . "]}";
        $series = "series: [" . $series . "]";
        $str = "
			start_date = '".self::$request['start_date']."';
			end_date = '".self::$request['end_date']."';
            categories = [" . $cate . "];
            var spending_bar_chart = new Highcharts.Chart({
                chart: {
                    renderTo: 'stats_spending_bar', 
                    defaultSeriesType: 'bar',
                    marginRight: 20, 
                },
                legend: {
                	enabled: false
                },
				colors: ['#AA4643'],				
                title: {
                    text: '".$_title."', y: 18, 
                    style:{ fontSize: '15px', color: '#004891', fontFamily:'calibri', fontWeight: 'bold'}
                },
                subtitle: { text: ' ', x: -20 },
                xAxis: {
                    categories: categories,			
                    labels: {
                        style: { fontFamily: 'calibri', fontSize: '11px', color: '#333', width: 120, lineHeight: 11},
                    },  
                },
                yAxis: {
                    title: { text: null },						
                    labels: { y: 10,
                        formatter: function() {
                            return '\$'+this.value;
                        },
                        style:{ color: '#333', fontSize: '12px', fontFamily:'calibri', }
                    },
                    showFirstLabel: false,												
                },
                plotOptions: {
                    series: {
                        stacking: 'normal'
                    },
                    bar: {
                        allowPointSelect: true, cursor: 'pointer',
                        events: {
                            click: function(event) {
                                sendUPCMsg(event.point.name, start_date, end_date);
                            }
                        }
                    }
                },											
                tooltip: {
                    style:{ color: '#333', fontSize: '12px', fontFamily:'calibri', },
                    formatter: function() {
                        return 'Spent <b>\$'+ this.y +'</b> on<br>'+ this.x.replace('<br>-<br>', ' to ');
                    }},
                " . $series . "});";
        return $str;
    }

    static function get_pie_chart($_data, $_title, $_name, $target, $_action, $b_unit='', $a_unit='') {
        if (!$_data['category']) {
            return false;
        }

        if (self::$request['start_date'] == self::$request['end_date']) {
            $_title .= " on " . self::$request['start_date'];
        } else {
            $_title .= " from " . self::$request['start_date'] . " to " . self::$request['end_date'];
        }
        $categories = array();

        $user_type = isset(self::$request['customer_id']) ? "type=customer&customer_id=" . self::$request['customer_id'] : "type=user";
        $restore = $_name . "#source=" . $_name . "&start_date=" . self::$request['start_date'] . "&end_date=" . self::$request['end_date'] . '&' . $user_type;
        $restore_action = "sendChartMsg('" . $restore . "')";

        if ($_action) {
            $action = "sendChartMsg('" . $_action . "'+'&" . $user_type . "&subgroup='+event.point.name.replace('&', 'aNd'));";
        }

        foreach ($_data['category']['data'] as $key => $value) {
			//$key = str_replace('&', "aNd", $key);
            //number_format($value*100/$_data['category']['amount'], 1)
            $categories[] = "['$key', " . $value . "]";
        }
        $series = implode(',', $categories);
        $str = "var $_name;";
        if ($_name == 'pie_left') {
            $str.="default_left_pie = 1;";
        }
        if ($_name == 'pie_right') {
            $str.="default_right_pie = 1;";
        }
        $str .= "
        $_name = new Highcharts.Chart({
            chart: { renderTo: '$target', plotBackgroundColor: null, plotBorderWidth: null, 
                plotShadow: false, 
            },
            title: {
                text: '$_title',
                style:{ fontSize: '14px', color: '#004891', fontFamily: 'calibri', fontWeight: 'bold'}
            },
            tooltip: {
                style:{ color: '#333', fontSize: '12px', fontFamily:'calibri', },
                formatter: function() {
                    return 'Spent <b>'+ '$b_unit' + this.y + '$a_unit</b><br>on <b>'+ this.point.name +'</b>';
                }
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true, cursor: 'pointer',
                    dataLabels: { enabled: false, },
                    innerSize: '35%', showInLegend: true,
                    events: {
                        click: function(event) {
        if(event.point.name.slice(-6) == 'OTHERS'){
            return;
        }
        ";

        if ($_name == 'pie_left') {
            $str.="if(default_left_pie != event.point.name){
                        default_left_pie = event.point.name;
                        " . $action . ";}
                    else{
                        default_left_pie = 0;
                        " . $restore_action . ";}
    		";
        } else if ($_name == 'pie_right') {
            $str.="if(default_right_pie != event.point.name){
                        default_right_pie = event.point.name;
                        " . $action . ";}
                    else{
                        default_right_pie = 0;
                        " . $restore_action . ";}
    		";
        } else {
            $str .= $action . ';';
        }
        $str .= "}}},},
		legend: { itemStyle: { fontFamily: 'calibri', fontSize: '12px', color: '#333', width: 140, lineHeight: 12 }, 
            layout: 'vertical', align: 'left', width: 140, verticalAlign: 'top', x: 0, y: 50, margin: 0, marginRight: 10,
        borderWidth: 0},
        series: [{
            type: 'pie', name: '', data: [ $series ]
            }]
        });
        ";
        return $str;
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

    // draw stats title
    static function draw_title($_found = false) {
        // the words displayed in title
        $title_msg = '';

        $filter_button = '';
        $filter_panel = '';

        if (!$_found) {
            $title_msg .= '<span class="red grey_shadow" style="font-size:20px;">';
            $title_msg .= 'You don not have any orders during given period';
            $title_msg .= '</span>';
        } else {
            $title_msg .= '<a onclick="sendHistryMsg(\'action=histry&type=user&start_date='. self::$request['start_date'] .'&end_date='. self::$request['end_date'] .'\', $(\'#right_container\'))"><span class="red grey_shadow" style="font-size:16px;">Your shopping statistics from ' . self::$request['start_date'] . ' to ' . self::$request['end_date'] . '</span></a>';
        }

        if (self::$request['filter']) {
            $filter_button = '<input type="button" value="Hide Filter" class="button" id="stats_show_filter">';
            $filter_panel = '<div id="stats_filter_panel_border" class="bg_white" ><div id="stats_filter_panel">' . self::get_filter() . '</div></div>';
        } else {
            $filter_button = '<input type="button" value="Show Filter" class="button" id="stats_show_filter">';
            $filter_panel = '<div id="stats_filter_panel_border" class="bg_white hide" ><div id="stats_filter_panel">' . self::get_filter() . '</div></div>';
        }
        $search_toolbar = '<div id="stats_toolbar">' . $filter_button . '</div>';
        return '<div id="stats_title">
						<table style="width: 100%;"><tr><td style="width: 80%; height: 40px; text-align: center"><div id="stats_title_msg">' . $title_msg . '</div></td><td style="text-align: right">' . $search_toolbar . '</div></td></tr></table>
					</div>' . $filter_panel;
    }

    static function auto_group($_start, $_end) {
        $start = new Datetime($_start);
        $end = new Datetime($_end);
        $interval = $end->diff($start);
        $diff = 0;

        $diff += $interval->format('%d');
        $diff += $interval->format('%m') * 30;
        $diff += $interval->format('%y') * 360;

        if ($diff < 16) {
            return 'day';
        } else if ($diff < 101) {
            return 'week';
        } else {
            return 'month';
        }
        return 'week';
    }

    static function get_start_date($_req) {
        if ($_req['date_type'] == 'month') {
            if ($_req['date'] > 0) {
                return date('Y-m-d', mktime(0, 0, 0, date("m") - $_req['date'], date("d"), date("Y")));
            }
        } else if ($_req['date_type'] == 'week') {
            if ($_para['date'] > 0) {
                return date('Y-m-d', mktime(0, 0, 0, date("m"), date("d") - 7 * $_req['date'], date("Y")));
            }
        } else if ($_req['date_type'] == 'day') {
            if ($_para['date'] > 0) {
                return date('Y-m-d', mktime(0, 0, 0, date("m"), date("d") - $_req['date'], date("Y")));
            }
        }
        return "2011-04-01";
    }

    static function get_end_date($_start_date) {
        return date('Y-m-d');
    }

    static function get_filter() {
        $group_selected = array('auto' => NULL, 'day' => NULL, 'week' => NULL, 'month' => NULL);
        $group_selected[self::$request['group']] = 'selected="selected"';
        $filter_table = '<table><tr>
                        <th>Date from <input type="text" value="' . self::$request['start_date'] . '" size="10" maxlength="10" name="stats_start_date" id="stats_start_date"/><img src="images/calendar.png" id="set_stats_start_date"></th>
                                <th>Date to <input type="text" value="' . self::$request['end_date'] . '" size="10" maxlength="10" name="stats_end_date" id="stats_end_date"/><img src="images/calendar.png" id="set_stats_end_date"></th>
                        <th>Group by <select id="stats_group" name="stats_group"><option value="auto" ' . $group_selected['auto'] . '>Auto</option><option value="day" ' . $group_selected['day'] . '>Day</option><option value="week" ' . $group_selected['week'] . '>Week</option><option value="month" ' . $group_selected['month'] . '>Month</option></select></th>
                        </tr></table>
                        <table><tr>
                                <th></th>
                        </tr></tbody></table>						
                        <table width="100%"><tr>
                                <th style="padding: 10px 0 5px"><center>
                                        <input type="button" id="stats_apply_filter" class="button" value="Apply Filter">
                                </center></th>
                        </tr></table>';
        return $filter_table;
    }

}

?>
