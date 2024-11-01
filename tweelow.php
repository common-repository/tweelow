<?php
/*
Plugin Name: Tweelow
Plugin URI: http://creative-boy.com
Description: Simple plugin that displays count of your twitter followers
Version: 1.1
Author: Allahverdi Suleymanov
Author URI: http://creative-boy.com
*/

/*  Copyright 2009  Allahverdi Suleymanov  (email : allahverdi.suleymanov@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
?>
<?php
if(file_exists("/wp-content/plugins/tweelow/login.php")){
	unlink("/wp-content/plugins/tweelow/login.php");
}
else{
}

function generateRTUri($url){
	$lookingfor = "http://";
	$finding = strpos($url, $lookingfor);
	$selected_option = get_option('tweelow_show_retweet');
	if($selected_option == "links"){
		if($finding === false){
		}
		else{
			$ready_retweet = "http://twitter.com/?status=".str_replace(" ", "+", $url);
			$retweet = " <a href='$ready_retweet' style='font-size:10px'>ReTweet</a>";
			echo $retweet;
		}
	}
	elseif($selected_option == "always"){
		$ready_retweet = "http://twitter.com/?status=".str_replace(" ", "+", $url);
		$retweet = " <a href='$ready_retweet' style='font-size:10px'>ReTweet</a>";
		echo $retweet;
	}
	else{
	}
}

function process($url,$postargs=false){  
	$ch = curl_init($url);  
	if($postargs !== false){  
		curl_setopt ($ch, CURLOPT_POST, true);  
		curl_setopt ($ch, CURLOPT_POSTFIELDS, $postargs);  
}  
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.11) Gecko/2009060215 Firefox/3.0.11");  
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);  
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
	$response = curl_exec($ch);  
	curl_close($ch);  
	return $response;  
}

add_action('admin_menu', 'tweelow_options_page');

function tweelow_options_page(){
add_menu_page('Tweelow Settings', 'Tweelow', 8, __FILE__, 'tweelow_options');
add_submenu_page(__FILE__, 'Tweelow Settings', 'Settings', 8, __FILE__, 'tweelow_options');
}
if($_POST['savetwollow']){
	if(isset($_POST['tw_username']) && $_POST['tw_username'] != ""){
		$twitter_username = $_POST['tw_username'];
		$ct_before = $_POST['tw_beforecount'];
		$ct_after = $_POST['tw_aftercount'];
		$st_before = $_POST['tw_beforestatus'];
		$st_after = $_POST['tw_afterstatus'];
		$show_retweet = $_POST['show_retweet'];
		update_option('tweelow_show_retweet', $show_retweet);
		update_option('tweelow_username', $twitter_username);
		update_option('ct_before', $ct_before);
		update_option('ct_after', $ct_after);
		update_option('st_before', $st_before);
		update_option('st_after', $st_after);
	}
}
function tweelow_options() {
?>
<div class="wrap">
	<h2>Tweelow Settings</h2>
	<p style="font-style:italic;">Your username will be a secret between this plugin and you. This plugin doesn't send your username and password to someone. If you don't believe, you can check this out from plugin's php file (wp-content/plugins/tweelow/tweelow.php).</p>
	<form method="post">
	<p>
	<input value="<?php echo get_option("tweelow_username"); ?>" type="text" name="tw_username"/>
	<label> - Your Twitter Username</label>
	</p>
	<p>
	<input value="<?php echo get_option("ct_before"); ?>" type="text" name="tw_beforecount"/>
	<label> - Word(s) before count of your followers</label>
	</p>
	<p>
	<input value="<?php echo get_option("ct_after"); ?>" type="text" name="tw_aftercount"/>
	<label> - Word(s) after count of your followers</label>
	</p>
	<p>
	<input value="<?php echo get_option("st_before"); ?>" type="text" name="tw_beforestatus"/>
	<label> - Word(s) before your twitter status</label>
	</p>
	<p>
	<input value="<?php echo get_option("st_after"); ?>" type="text" name="tw_afterstatus"/>
	<label> - Word(s) after your twitter status</label>
	</p>
	<p>
	<select name="show_retweet">
		<option value="always">Always</option>
		<option value="links">Only when it has links</option>
		<option value="never">Never</option>
	</select>
	<label> - Show ReTweet Button</label>
	</p>
	<p>
	<input type="submit" value="Save" name="savetwollow"/>
	</p>
	</form>
</div>
<?php
}
?>
<?php
	function display_tweelow(){
		if(get_option("tweelow_username") == ""){
			echo "Please, enter Twitter username in Admin Panel :)";
		}
		else{
			$username = get_option("tweelow_username");
			$html = process("http://twitter.com/users/show.xml?screen_name=$username");  
 
			$pattern = '<followers_count>(.*)<\/followers_count>';  
 			ereg($pattern, $html, $matches);  
  			if($matches[1] != ""){
  				update_option("tweelow_count",$matches[1]);
  			}
  			else{
  				$matches[1] = get_option("tweelow_count");
  			}
  			$beforeCountText = get_option("ct_before");
  			$afterCountText = get_option("ct_after");
			echo $beforeCountText."<a href=\"http://twitter.com/$username\">".$matches[1]."</a>".$afterCountText;  
		}
	}
	function lateststatus_tweelow(){
		if(get_option("tweelow_username") == ""){
			echo "Please, enter Twitter username in Admin Panel :)";
		}
		else{
			$username = get_option("tweelow_username");
			$html = process("http://twitter.com/statuses/user_timeline/$username.xml?count=1");  

			$pattern = '<text>(.*)<\/text>';  
			ereg($pattern, $html, $matches);  
  			if($matches[1] != ""){
  				update_option("tweelow_ltstatus",$matches[1]);
  			}
  			else{
  				$matches[1] = get_option("tweelow_ltstatus");
  			}
  			$beforeStatusText = get_option("st_before");
  			$afterStatusText = get_option("st_after");
			echo $beforeStatusText;
			echo $matches[1];
			echo $afterStatusText;
			generateRTUri($matches[1]);
		}
	}
function quickEdit(){
  	$matches = get_option("tweelow_ltstatus");
	echo $matches;
}
?>
<?php
/*
Check Functions Here
*/
$checkOptionsLinkShow = get_option("tweelow_show_retweet");
$checkOptionsUsernameExists = get_option("tweelow_username");
if($checkOptionsLinkShow == false && $checkOptionsUsernameExists != false){
	update_option("tweelow_show_retweet", "links");
}
?>
