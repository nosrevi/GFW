<?php

$_listener = new LISTENER($_REQUEST);

class LISTENER {

    private $request;
    private $valid_actions = array();

    function __construct($_req) {
        $this->request = $_req;
        $this->valid_actions = array('histry', 'stats', 'recmd', 'chtsht', 'search');
        $this->catch_req();
    }

    public function catch_req() {
        if ($this->valid_action($this->request['action'])) {
            switch ($this->request['action']) {
                case "histry":
                    $this->process_histry();
                    break;
                case "stats":
                    $this->process_stats();
                    break;
                case "recmd":
                    $this->process_recmd();
                    break;
                case "chtsht":
                    $this->process_chtsht();
                    break;
                case "search":
                    $this->process_search();
                    break;
            }
        } else {
            echo "process_" . $this->request['action'] . " is not valid";
        }
    }

    public function get_request($key) {
        return $this->request[$key];
    }

    public function process_histry() {

        // order date desc by default
        if (!isset($this->request['date'])) {
            $this->request['date'] = 'desc';
        }
        // order payment desc by default
        if (!isset($this->request['payment'])) {
            $this->request['payment'] = 'desc';
        }
        // set date as priority order by default
        if (!isset($this->request['priority'])) {
            $this->request['priority'] = 'date';
        }
        if (!isset($this->request['detail'])) {
            $this->request['detail'] = false;
        }
        if (!isset($this->request['row']) || $this->request['row'] < 1) {
            $this->request['row'] = '16';
        }
        if (!isset($this->request['page'])) {
            $this->request['page'] = 1;
        }
        if (!isset($this->request['filter'])) {
            $this->request['filter'] = 0;
        }
        if (!isset($this->request['day']) || $this->request['day'] == NULL) {
            $this->request['day'] = -1;
        }
        if (!isset($this->request['start_date'])) {
            $this->request['start_date'] = NULL;
        }
        if (!isset($this->request['end_date'])) {
            $this->request['end_date'] = NULL;
        }
        if (!isset($this->request['paymore'])) {
            $this->request['paymore'] = NULL;
        }
        if (!isset($this->request['payless'])) {
            $this->request['payless'] = NULL;
        }
        if (!isset($this->request['apply_filter'])) {
            $this->request['apply_filter'] = 0;
        }

        // while searcing should we fetch the detail of a order, or just an abstract	
        $detail = false;

        if ($this->request['day'] == 0) {
            if (isset($this->request['limit']) && $this->request['limit'] <= 0) {
                $this->request['limit'] = $this->request['row'];
            }
        }
        // TODO: notice that the request is completely processed, the listerner's task is over
        // now we start drawing the viewer based the request
        require_once VIEW_PATH . "/histry.php";
    }

    public function process_search() {
        if (!$this->request['keywords'] || trim($this->request['keywords']) == '') {
            echo "type something!";
            return;
        }
		if(strlen($this->request['keywords']) >= 5 && $this->request['keywords']{0} == "\"" && $this->request['keywords']{strlen($this->request['keywords'])-1} == "\""){
			$keyword = substr($this->request['keywords'], 1, strlen($this->request['keywords'])-2);
			$this->request['keywords'] = array( $keyword) ;
		}
		else{
        	$keywords = $this->request['keywords'];
        	$this->request['keywords'] = preg_split("/[\s,]*\\\"([^\\\"]+)\\\"[\s,]*|" . "[\s,]*'([^']+)'[\s,]*|" . "[\s,]+/", $this->request['keywords'], 0, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
		}

        if (!isset($this->request['priority'])) {
            $this->request['priority'] = 'name';
        }
        if (!isset($this->request['name'])) {
            $this->request['name'] = 'asc';
        }
        if (!isset($this->request['vendor'])) {
            $this->request['vendor'] = 'asc';
        }
        if (!isset($this->request['row'])) {
            $this->request['row'] = '8';
        }
        if (!isset($this->request['page'])) {
            $this->request['page'] = 1;
        }
        if (!isset($this->request['category'])) {
            $this->request['category'] = 'ALL';
        }
        if (!isset($this->request['filter'])) {
            $this->request['filter'] = 0;
        }
        if (!isset($this->request['heb'])) {
            $this->request['heb'] = 0;
        }
        if (!isset($this->request['hlt'])) {
            $this->request['hlt'] = 0;
        }
        if (!isset($this->request['apply_filter'])) {
            $this->request['apply_filter'] = 0;
        }
        require_once VIEW_PATH . "/search.php";
    }

    public function process_stats() {
        //print_r($this->request);
        if (!isset($this->request['date_type'])) {
            $this->request['date_type'] = 'all';
        }
        if (!isset($this->request['date'])) {
            $this->request['date'] = '0';
        }
        if (!isset($this->request['group'])) {
            $this->request['group'] = 'auto';
        }
        if (!isset($this->request['filter'])) {
            $this->request['filter'] = 0;
        }
        if (!isset($this->request['start_date'])) {
            $this->request['start_date'] = NULL;
        }
        if (!isset($this->request['end_date'])) {
            $this->request['end_date'] = NULL;
        }
        if (!isset($this->request['group'])) {
            $this->request['group'] = 'auto';
        }
        if (!isset($this->request['source'])) {
            $this->request['source'] = NULL;
        }
        if (!isset($this->request['type'])) {
            $this->request['type'] = 'user';
        }
		if(isset($this->request['subgroup'])){
			$this->request['subgroup'] = str_replace('aNd', '&', $this->request['subgroup']);
		}
        
        if( $this->request['source'] && $this->request['source'] != 'line'){
            require_once VIEW_PATH . "/substats.php";
        }
        else{
            require_once VIEW_PATH . "/stats.php";
        }
    }

    public function process_recmd() {
        print_r($this->request);
    }

    public function process_chtsht() {
        echo "asd";
    }

    public function valid_action($_action) {
        return in_array($_action, $this->valid_actions);
    }

}

?>
