<?php
	require_once "GFW_init.php";

	require_once MODULE_PATH."/drawer.core.php";
	require_once MODULE_PATH."/product.class.php";
?>

<body onLoad="load();">
<div id="header">
  <div id="cover"><center><img src="images/bspinner.gif" class="hide"></center></div>
  <div id="logo"></div>
  <div id="welcome_word">
    <?php DRAWER::hello_word();?>
  </div>
  <div id="logout"> <input type="button" class="button" value="Log out" id="logout_button" onClick="javascript:window.location = 'logout.php'"></div>
</div>
<div id="nav_border">
  <div id="nav_bar">
    <table id="search_table">
      <tr>
        <td width="160px" height="30px"></td>
        <td width="140px"><span class="white bold" style="float:left">How better is it here?</span></td>
        <td style="padding-right: 10px"><input type="text" id="search_field" value=""></td>
        <td width="100px" style="padding-left: 5px"><input type="submit" id="search_submit" class="button" value="Search" /></td>
        <td width="200px"></td>
      </tr>
    </table>
  </div>
</div>
<div id="container">
  <table width="100%">
    <tr>
      <td width="170px" valign="top"><div id="left">
          <div id="sidebar_border">
            <div id="sidebar">
              <ul id="menu_ul">
                <!-- <div id="chtsht_div">
                  <div class="li_border"><li class="menu_li"> <a link="#">My Cheetsheet</a></li></div>
                </div> -->
                <div id="histry_div">
                  <div class="li_border"><li class="menu_li"> <a class="histry_a" link="#action=histry&type=user&day=0&page=1">Shopping History</a></li></div>
                  <ul id="histry_ul">
                    <div class="li_border"><li class="menu_li_li"> <a class="histry_a" link="#action=histry&type=user&day=0&page=1&limit=1">My Latest Order</a></li></div>
                    <div class="li_border"><li class="menu_li_li"> <a class="histry_a" link="#action=histry&type=user&day=7&page=1">In Past 7 Days</a></li></div>
                    <div class="li_border"><li class="menu_li_li"> <a class="histry_a" link="#action=histry&type=user&day=30&page=1">In Past 30 Days</a></li></div>
                    <div class="li_border"><li class="menu_li_li"> <a class="histry_a" link="#action=histry&type=user&day=90&page=1">In Past 90 Days</a></li></div>
                  </ul>
                </div>
                <div id="stats_div">
                  <div class="li_border"><li class="menu_li"><a class="stats_a" link="#action=stats&date_type=all">Shopping Statistics</a></li></div>
                  <ul id="stats_ul">
                    <div class="li_border"><li class="menu_li_li"><a class="stats_a" link="#action=stats&date_type=month&date=1">In Past Month</a></li></div>
					<div class="li_border"><li class="menu_li_li"><a class="stats_a" link="#action=stats&date_type=month&date=3">In Past 3 Months</a></li></div>
					<div class="li_border"><li class="menu_li_li"><a class="stats_a" link="#action=stats&date_type=month&date=6">In Past 6 Months</a></li></div>
					<div class="li_border"><li class="menu_li_li"><a class="stats_a" link="#action=stats&date_type=month&date=12">In Past 12 Months</a></li></div>
                  </ul>
                </div>
              </ul>
            </div>
          </div>
        </div></td>
      <td valign="top"><div id="right">
          <div id="right_container"> </div>
          <?php //ORDER::get_orders_by_user_id($_user->get_user_id()); ?>
        </div></td>
    </tr>
  </table>
</div>
</body>
