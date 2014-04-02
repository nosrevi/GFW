<?php
require_once "GFW_init.php";
require_once MODULE_PATH . "/product.class.php";
require_once MODULE_PATH . "/search.drawer.php";

SEARCH_DRAWER::set_request($this->request);

echo '<table width="100%"><tr><td valign="top" style="padding: 15px; padding-right: 0px"><div id="search_container">';

$searchQuery = 'SELECT * FROM PRODUCT WHERE ';

$searchQuery .= GFW::get_condition($this->request);
$result = GFW::query($searchQuery);

if (GFW::is_empty($result, $searchQuery)) {
    $resultCount = 0;
	SEARCH_DRAWER::draw_title($resultCount);
} else {
	$purchased_products_index = PRODUCT::get_products_index_by_user_id($_SESSION['USER_ID']);

    $searchResults = array();
    while ($row = GFW::fetch($result)) {
        $searchResults[$row['UPC']] = $row;
    }
    $resultCount = GFW::count($result);
    SEARCH_DRAWER::draw_title($resultCount);

    if ($this->request['page'] > 0 && $resultCount > $this->request['row']) {
        $searchResults = array_slice($searchResults, ($this->request['page'] - 1) * $this->request['row'], $this->request['row'], true);
    }

    echo '<div id="search_results">';
		 
    foreach ($searchResults as $resultUPC => $searchResult) {
        SEARCH_DRAWER::draw_entity($resultUPC, $searchResult, isset($purchased_products_index[$resultUPC]));
    }
    echo '</div>';
    echo SEARCH_DRAWER::get_page_nav($resultCount);
}

echo '</div></td>';
echo '<td width="10px" valign="top"><div id="histry_sidebar"></div></td></tr></table>';
?>
<script>
<?php 
	echo $this->request['filter']?"show_search_filter=1;":"show_histry_filter=0;"; 
?>
    $("#search_show_filter").click(function(){
		if(show_search_filter==0){	
            show_search_filter = 1;		
            $(this).val("Hide Filter");
            $('#search_filter_panel_border').css('display', 'block');
        }
        else if(show_search_filter==1){	
            show_search_filter = 0;				
            $('#search_filter_panel_border').css('display', 'none');
            $(this).val("Show Filter");
        }
    });
    $("#search_container .button").mouseover(function () {
        $(this).css("box-shadow", "inset 0 0 10px rgba(255, 255, 255, 0.9)");
    });
    $("#search_container .button").mouseleave(function () {
        $(this).css("box-shadow", "0 0 3px #000");
    });	
    $("#search_container .button").mousedown(function () {
        $(this).removeClass("button");
        $(this).addClass("button_on");
    });
    $("#search_container .button").mouseup(function () {
        $(this).removeClass("button_on");
        $(this).addClass("button");
    })
	;
    $("#search_container a").click(function () {
        sendSearchMsg($(this).attr('link').replace("#", ""), $('#right_container'));
    }); 
    $(".add_to_list").click(function () {
        alert('Under Working!');
    }); 	
	$('#search_apply_filter').click(function(){
		request = '';
		if($('#search_heb').is(':checked')) {
			request += 'heb=1&';
		}
		if($('#search_healthy').is(':checked')) {
			request += 'hlt=1&';
		}
		request += 'category='+$('#search_category option:selected').val() +'&priority='+$('#search_priority option:selected').val()
				+'&'+$('#search_priority option:selected').val()+'='+$('#search_order option:selected').val()		
		call_search(1, request);
    });
	
</script>
