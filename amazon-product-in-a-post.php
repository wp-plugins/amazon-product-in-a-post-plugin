<?php
/*
Plugin Name: Amazon Product In a Post Plugin
Plugin URI: http://fischercreativemedia.com/wordpress-plugins/amazon-affiliate-product-in-a-post/
Description: Quickly add a formatted Amazon Product (image, pricing and buy button, etc.) to a post by using just the Amazon product ASIN (ISBN-10). Great for writing product reviews or descriptions to help monetize your posts and add content that is relevant to your site. You can also customize the styles for the product data. Remember to add your Amazon Affiliate ID on the <a href="admin.php?page=apipp_plugin_admin">options</a> page or all sales credit will go to the plugin creator by default.
Author: Don Fischer
Author URI: http://www.fischercreativemedia.com/
Version: 2.0.2
    Copyright (C) 2009-2012 Donald J. Fischer
    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

// Warnings Quickfix
	if(get_option('apipp_hide_warnings_quickfix')==true){
		 ini_set("display_errors", 0); //turns off error display
	}
	register_activation_hook(__FILE__,'appip_install');
	register_deactivation_hook(__FILE__,'appip_deinstall');

// Variables
	global $public_key;
	global $private_key; 
	global $aws_partner_id;
	global $aws_eatra_pages;
	global $aws_plugin_version;
	global $aws_partner_locale;
	global $thedefaultapippstyle;
	global $amazonhiddenmsg;
	global $amazonerrormsg;
	global $apipphookexcerpt;
	global $apipphookcontent;
	global $apippopennewwindow;
	global $apippnewwindowhtml;
	global $encodemode; //1.7 new
	global $appip_text_lgimage;
	global $appip_text_listprice; 
	global $appip_text_newfrom; 
	global $appip_text_usedfrom;
	global $appip_text_instock;
	global $appip_text_outofstock; 
	global $appip_text_author;
	global $appip_text_starring;
	global $appip_text_director;
	global $appip_text_reldate;
	global $appip_text_preorder;
	global $appip_text_releasedon;
	global $appip_text_notavalarea;
	global $buyamzonbutton;
	global $addestrabuybutton;
	global $awspagequery;
	global $apip_language;
	global $appuninstall;
	global $appuninstallall;
	global $thedefaultapippstyle;

// Includes
	require_once("inc/sha256.inc.php"); //required for php4
	require_once("inc/aws_signed_request.php"); //major workhorse for plugin
	require_once("inc/amazon-product-in-a-post-tools.php"); //edit box for plugin
	require_once("inc/amazon-product-in-a-post-options.php"); //admin options for plugin
	require_once("inc/amazon-product-in-a-post-translations.php"); //translations for plugin
	require_once("inc/amazon-product-in-a-post-styles-product.php"); //styles for plugin

//upgrade check. Lets me add/change the default style etc to fix/add new items during updrages.
	$thisstyleversion=get_option('apipp_product_styles_default_version');
	if($thisstyleversion!="1.7" || get_option("apipp_product_styles_default")==''){
		update_option("apipp_product_styles_default",$thedefaultapippstyle);
		update_option("apipp_product_styles_default_version","1.7");
		
		//add the new element style to their custom ones - so at least it has the default functionality. They can change it after if they like
		$apipp_product_styles_cust_temp = get_option("apipp_product_styles");
 		if($apipp_product_styles_cust_temp!=''){
 			update_option("apipp_product_styles",$apipp_product_styles_cust_temp.'div.appip-multi-divider{margin:10px 0;}');
 		}
 		
		update_option("apipp_amazon_notavailable_message","This item is may not be available in your area. Please click the image or title of product to check pricing & availability."); //default message
		update_option("apipp_amazon_hiddenprice_message","Price Not Listed"); //default message - done
		update_option("apipp_hook_content","1"); //default is yes - done
		update_option("apipp_hook_excerpt","0"); //default is no - done
		update_option('apipp_open_new_window',"0"); //default is no - newoption added at 1.6 - done
	}
//added in 1.7 to allow those that could not use file_get_contents() to use Curl instead.		
	if(get_option('awsplugin_amazon_usefilegetcontents')==''){update_option('awsplugin_amazon_usefilegetcontents','1');}
	if(get_option('awsplugin_amazon_usecurl')==''){update_option('awsplugin_amazon_usecurl','0');}
	//if(get_option('apipp_API_call_method')==''){update_option('apipp_API_call_method','0');}
	if(get_option('apipp_API_call_method')=='' && get_option('awsplugin_amazon_usecurl')=='0'){
		update_option('apipp_API_call_method','0');}
	elseif(get_option('apipp_API_call_method')=='' && get_option('awsplugin_amazon_usecurl')!='1'){
		update_option('apipp_API_call_method','1');
	}
	
	
	session_start();	
    if(!isset($_SESSION['Amazon-PIPP-Cart-HMAC'])) $_SESSION['Amazon-PIPP-Cart-HMAC'] = '';
    if(!isset($_SESSION['Amazon-PIPP-Cart-Encoded-HMAC'])) $_SESSION['Amazon-PIPP-Cart-Encoded-HMAC']='';
    if(!isset($_SESSION['Amazon-PIPP-Cart-ID'])) $_SESSION['Amazon-PIPP-Cart-ID']='';
    
	$awspagequery		= '';
	$public_key 		= get_option('apipp_amazon_publickey'); //Developer Public AWS Key
	$private_key 		= get_option('apipp_amazon_secretkey'); //Developer Secret AWS Key
	$appuninstall 		= get_option('apipp_uninstall'); //Uninstall database and options
	$appuninstallall	= get_option('apipp_uninstall_all'); //Uninstall shortcodes in pages an posts
	$aws_partner_id		= get_option('apipp_amazon_associateid'); //Amazon Partner ID 
	//$aws_partner_locale	= get_option('apipp_amazon_locale'); //Amazon Locale - moved to translations file
	$awsPageRequest 	= 1;
	$aws_plugin_version = "2.0";
	$amazonhiddenmsg 	= get_option('apipp_amazon_hiddenprice_message'); //Amazon Hidden Price Message
	$amazonerrormsg 	= get_option('apipp_amazon_notavailable_message'); //Amazon Error No Product Message
	$apipphookexcerpt 	= get_option('apipp_hook_excerpt'); //Hook the excerpt?
	$apipphookcontent 	= get_option('apipp_hook_content'); //Hook the content?
	$apippopennewwindow = get_option('apipp_open_new_window'); //open in new window?
	$aws_eatra_pages 	= '';
	$aws_eatra_pages 	= '"ItemPage"=>"'.$awspagequery.'",';
	$thereapippstyles 	= get_option("apipp_product_styles_default"); 
	$apippnewwindowhtml	= '';
	$apip_getmethod 	= get_option('apipp_API_call_method');
	$apip_usefileget 	= '0';
	$apip_usecurlget	= '0';
	$encodemode 		= get_option('appip_encodemode'); //1.7 added - UTF-8 will be default\
	
	// 1.7 api get method defaults/check
	if($apip_getmethod=='0'){
		$apip_usefileget = '1';
	}
	if($apip_getmethod=='1'){
		$apip_usecurlget = '1';
	}
	if($apip_getmethod==''){
		$apip_usefileget = '1'; //set default if not set
	}
	
	//1.7 Encode Mode
	if(get_option('appip_encodemode')==''){
		update_option('appip_encodemode','UTF-8'); //set default to UTF-8
		$encodemode="UTF-8";
	}
	
	//1.8 backward compat.
	if(!function_exists('mb_convert_encoding')){
		function mb_convert_encoding($etext='', $encodemode='', $encis=''){
			return $etext;
		}
	}	
	if(!function_exists('mb_detect_encoding')){
		function mb_detect_encoding($etext='', $encodemode=''){
			return $etext;
		}
	}	
	
	// 1.7 - change encoding if needed via GET
	// use http://yoursite.com/?resetenc=UTF-8 or http://yoursite.com/?resetenc=ISO-8859-1
	// this will be the mode you want the text OUTPUT as.
	if(isset($_GET['resetenc'])){
		if($_GET['resetenc']=='ISO-8859-1' || $_GET['resetenc']=='UTF-8'){
			update_option('appip_encodemode',$_GET['resetenc']);
			$encodemode = $_GET['resetenc'];
		}
	}
	if($apippopennewwindow==true){
		$apippnewwindowhtml=' target="amazonwin" ';
	}
	if($amazonerrormsg==''){
		$amazonerrormsg='Product Unavailable.';
	}
	if($amazonhiddenmsg==''){
		$amazonhiddenmsg='Visit Amazon for Price.';
	}
	if($aws_partner_locale==''){
		update_option('apipp_amazon_locale','com'); //set default to US
		$aws_partner_locale='com';
	}
	if($aws_partner_id==''){
		$aws_partner_id = "wolvid-20"; //Amazon Partner ID - if one is not set up, we will use Plugin Creator's ID - so be sure to set one up!!
	}
	if($public_key==''){
		$public_key = "AKIAIR3UXPU7Y7GQQPAQ";  //Developer Public AWS Key
	}
	if($private_key==''){
		$private_key = "oKUKoxCKgsmN1pmNbBYYi6DT9vMJfNMdt3Q1VUfJ"; //Developer Secret AWS Key
	}
	
	if(isset($_GET['awspage'])){ //future item for search results
		if(is_numeric($_GET['awspage'])){
			$awspagequery = (int)$wpdb->escape($_GET['awspage']);
		}
	}
	if($awspagequery>1){ //future item for search results
		$awsPageRequest = $awspagequery;
	}
	
	if(trim(get_option("apipp_product_styles")) == ''){ //reset to default styles if user deletes styles in admin
		update_option("apipp_product_styles",$thedefaultapippstyle);
	}
	
// Filters & Hooks
	
	add_filter('the_content', 'aws_prodinpost_filter_content', 10); //hook content - we will filter the override after
	add_filter('the_excerpt', 'aws_prodinpost_filter_excerpt', 10); //hook excerpt - we will filter the override after 
	add_action('wp_head','aws_prodinpost_addhead',10); //add styles to head
	add_action('wp','add_appip_jquery'); //enqueue scripts
	add_action('admin_head','aws_prodinpost_addadminhead',10); //add admin styles to admin head
	//add_action('wp','aws_prodinpost_cartsetup', 1, 2); //Future Item

// Functions
	function appip_deinstall() {
		global $wpdb;
		$appuninstall 		= get_option('apipp_uninstall'); 
		$appuninstallall	= get_option('apipp_uninstall_all');
		if($appuninstall == 'true'){
			$appiptable = $wpdb->prefix . 'amazoncache'; 
			$deleteSQL = "DROP TABLE $appiptable";
	      	$wpdb->query($deleteSQL);
			delete_option('apipp_amazon_publickey');
			delete_option('apipp_amazon_secretkey');
			delete_option('apipp_uninstall');
			delete_option('apipp_uninstall_all');
			delete_option('apipp_amazon_associateid'); 
			delete_option('apipp_amazon_locale');
			delete_option('apipp_amazon_hiddenprice_message');
			delete_option('apipp_amazon_notavailable_message');
			delete_option('apipp_hook_excerpt');
			delete_option('apipp_hook_content');
			delete_option('apipp_open_new_window');
			delete_option('apipp_product_styles_default'); 
			delete_option('apipp_API_call_method');
			delete_option('appip_encodemode');
			delete_option('apipp_amazon_language');
			delete_option('apipp_product_styles_mine');
			delete_option('apipp_version');
			delete_option('apipp_show_single_only');
			delete_option('apipp_product_styles_default_version');
			delete_option('apipp_product_styles');
		}

		if($appuninstall == 'true' && $appuninstallall == 'true'){
			//DELETE ALL POST META FOR ITEMS WITH APIPP USAGE
			$remSQL = "DELETE FROM $wpdb->postmeta WHERE `meta_key` LIKE '%amazon-product%';";
			$cleanit = $wpdb->query($remSQL);
			//Now get data for IDs with content or excerpt containing the shortcodes.
			$thesqla = "SELECT ID, post_content, post_excerpt FROM $wpdb->posts WHERE post_content like '%[AMAZONPRODUCT%' OR post_excerpt like '%[AMAZONPRODUCT%';";
			$postData = $wpdb->get_results($thesqla);
			if(count($postData)>0){
				foreach ($postData as $pdata){
					$pcontent = $pdata->post_content;
					$pexcerpt = $pdata->post_excerpt;
					$pupdate  = 0;
					$pid 	  = $pdata->ID;
					$search   = "@(?:<p>)*\s*\[AMAZONPRODUCT\s*=\s*(.+|^\+)\]\s*(?:</p>)*@i"; 
					if(preg_match_all($search, $pcontent, $matches1)) {
						if (is_array($matches1)) {
							foreach ($matches1[1] as $key =>$v0) {
								$search 	= $matches1[0][$key];
								$ASINis		= $matches1[1][$key];
								$pcontent 	= str_replace ($search, '', $pcontent);
							}
							$pupdate  = 1;
						}
					}
					if(preg_match_all($search, $pexcerpt, $matches2)) {
						if (is_array($matches2)) {
							foreach ($matches2[1] as $key =>$v0) {
								$search		= $matches2[0][$key];
								$ASINis		= $matches2[1][$key];
								$pexcerpt	= str_replace ($search, '', $pexcerpt);
							}
							$pupdate  = 1;
						}
					}
					if($pupdate == 1){
						$wpdb->query("UPDATE $wpdb->posts SET post_excerpt = '$pexcerpt', post_content = '$pcontent' WHERE ID = '$pid';");
					}
				}
			}
		}
	}
	// Install Function - called on activation
	function appip_install () {
		global $wpdb, $wp_roles, $wp_version, $aws_plugin_version;
		if(get_option("apipp_version")== ''){
			$appiptable = $wpdb->prefix . 'amazoncache'; 
			$createSQL = "CREATE TABLE IF NOT EXISTS $appiptable (`Cache_id` int(10) NOT NULL auto_increment, `URL` text NOT NULL, `updated` datetime default NULL, `body` text, PRIMARY KEY (`Cache_id`), UNIQUE KEY `URL` (`URL`(255)), KEY `Updated` (`updated`)) ENGINE=MyISAM;";
	      	//echo $createSQL;
	      	$wpdb->query($createSQL);
			add_option("apipp_version", $aws_plugin_version);
		}
	}

	//Single Product API Call - Returns One Product Data
	function getSingleAmazonProduct($asin='',$extratext='',$extrabutton=0){
		global $public_key, $private_key, $aws_partner_id,$aws_partner_locale,$amazonhiddenmsg,$amazonerrormsg,$apippopennewwindow,$apippnewwindowhtml;
		global $appip_text_lgimage;
		global $appip_text_listprice; 
		global $appip_text_newfrom; 
		global $appip_text_usedfrom;
		global $appip_text_instock;
		global $appip_text_outofstock; 
		global $appip_text_author;
		global $appip_text_starring;
		global $appip_text_director;
		global $appip_text_reldate;
		global $appip_text_preorder;
		global $appip_text_releasedon;
		global $appip_text_notavalarea;
		global $buyamzonbutton;
		global $addestrabuybutton;
		global $encodemode;
		global $post;
		//$apippOpenNewWindow = get_post_meta($post->ID,'amazon-product-newwindow',true);
		//if($apippOpenNewWindow!='3'){$apippnewwindowhtml=' target="amazonwin" ';}

		if ($asin!=''){
			$ASIN 					= $asin; //valid ASIN
			$errors 				= '';
			$appip_responsegroup 	= "ItemAttributes,Images,Offers,Reviews";
			$appip_operation 		= "ItemLookup";
			$appip_idtype	 		= "ASIN";
			$pxml 					= aws_signed_request($aws_partner_locale, array("Operation"=>$appip_operation,"ItemId"=>$ASIN,"ResponseGroup" => $appip_responsegroup,"IdType"=>$appip_idtype,"AssociateTag"=>$aws_partner_id ), $public_key, $private_key);
			
			if(!is_array($pxml)){
				$pxml2=$pxml;
				$pxml = array();
				$pxml["itemlookuperrorresponse"]["error"]["code"]["message"] = $pxml2;
			}
			if(isset($pxml["itemlookuperrorresponse"]["error"]["code"])){
				$errors = $pxml["itemlookuperrorresponse"]["error"]["code"]["message"];
			}
			
			if($errors=='exceeded'){
				$hiddenerrors = "<"."!-- HIDDEN AMAZON PRODUCT IN A POST ERROR: Requests Exceeded -->";
				$errors = 'Requests Exceeded';
				if($extratext!=''){return $hiddenerrors.$extratext;}
				return $hiddenerrors;
			}elseif($errors=='no signature match'){
				$hiddenerrors = "<"."!-- HIDDEN AMAZON PRODUCT IN A POST ERROR: Signature does not match AWS Signature. Check AWS Keys and Signature method. -->";
				$errors = 'Signature does not match';
				if($extratext!=''){return $hiddenerrors.$extratext;}
				return $hiddenerrors;
			}elseif($errors=='not valid'){
				$hiddenerrors = "<"."!-- HIDDEN AMAZON PRODUCT IN A POST ERROR: Item Not Valid. Possibly not available in your locale or you did not enter a correct ASIN. -->";
				$errors = 'Not a valid item';
				if($extratext!=''){return $hiddenerrors.$extratext;}
				return $hiddenerrors;
			}elseif($errors!=''){
				$hiddenerrors = "<"."!-- HIDDEN AMAZON PRODUCT IN A POST ERROR: ". $errors ."-->";
				if($extratext!=''){return $hiddenerrors.$extratext;}
				return $hiddenerrors;
			}else{

				$result = FormatASINResult($pxml);
				$returnval  = '	<br /><table cellpadding="0"class="amazon-product-table">'."\n";
				$returnval .= '		<tr>'."\n";
				$returnval .= '			<td valign="top">'."\n";
				$returnval .= '				<div class="amazon-image-wrapper">'."\n";
				$returnval .= '					<a href="' . $result["URL"] . '" '. $apippnewwindowhtml .'>' . awsImageGrabber($result['MediumImage'],'amazon-image') . '</a><br />'."\n";
				if($result['LargeImage']!=''){
				//$returnval .= '				<a target="amazon-image" href="javascript: void(0)" onclick="artwindow=window.open(\'' .$result['LargeImage'] .'\',\'art\',\'directories=no, location=no, menubar=no, resizable=no, scrollbars=no, status=no, toolbar=no, width=400,height=525\');artwindow.focus();return false;"><span class="amazon-tiny">'.$appip_text_lgimage.'</span></a>'."\n";
				$returnval .= '					<a rel="appiplightbox" href="'.$result['LargeImage'] .'"><span class="amazon-tiny">'.$appip_text_lgimage.'</span></a>'."\n";
				}
				$returnval .= '				</div>'."\n";
				$returnval .= '				<div class="amazon-buying">'."\n";
				$returnval .= '					<h2 class="amazon-asin-title"><a href="' . $result["URL"] . '" '. $apippnewwindowhtml .'><span class="asin-title">'.$result["Title"].'</span></a></h2>'."\n";
				if(isset($result["Author"])){
				$returnval .= '					<span class="amazon-author">'.$appip_text_author.' '.$result["Author"].'</span><br />'."\n";
				}
				if(isset($result["Director"])){
				$returnval .= '					<span class="amazon-director-label">'.$appip_text_director.': </span><span class="amazon-director">'.$result["Director"].'</span><br />'."\n";
				}
				if(isset($result["Actors"])){
				$returnval .= '					<span class="amazon-starring-label">'.$appip_text_starring.': </span><span class="amazon-starring">'.$result["Actors"].'</span><br />'."\n";
				}
				if(isset($result["Rating"])){
				$returnval .= '					<span class="amazon-rating-label">Rating: </span><span class="amazon-rating">'.$result["Rating"].'</span><br />'."\n";
				}
				$returnval .= '				</div>'."\n";
				$returnval .= '				<hr noshade="noshade" size="1" />'."\n";
				$returnval .= '				<div align="left">'."\n";
				$returnval .= '					<table class="amazon-product-price" cellpadding="0">'."\n";
				if($extratext!=''){
				$returnval .= '						<tr>'."\n";
				$returnval .= '							<td class="amazon-post-text" colspan="2">'.$extratext.'</td>'."\n";
				$returnval .= '						</tr>'."\n";
				}
				If($result["PriceHidden"]==1 ){
					$returnval .= '						<tr>'."\n";
					$returnval .= '							<td class="amazon-list-price-label">'.$appip_text_listprice.':</td>'."\n";
					$returnval .= '							<td class="amazon-list-price-label">'.$amazonhiddenmsg.'</td>'."\n";
					$returnval .= '						</tr>'."\n"; 
				}elseif($result["ListPrice"]!='0'){
					$returnval .= '						<tr>'."\n";
					$returnval .= '							<td class="amazon-list-price-label">'.$appip_text_listprice.':</td>'."\n";
					$returnval .= '							<td class="amazon-list-price">'.  mb_convert_encoding($result["ListPrice"], $encodemode, mb_detect_encoding( $result["ListPrice"], "auto" )) .'</td>'."\n";
					$returnval .= '						</tr>'."\n";
				}
				if(isset($result["LowestNewPrice"])){
					if($result["LowestNewPrice"]=='Too low to display'){
						$newPrice = 'Check Amazon For Pricing';
					}else{
						$newPrice = $result["LowestNewPrice"];
					}
					$returnval .= '						<tr>'."\n";
					$returnval .= '							<td class="amazon-new-label">'.$appip_text_newfrom.':</td>'."\n";
					if($result["TotalNew"]>0){
						$returnval .= '							<td class="amazon-new">'. mb_convert_encoding($newPrice , $encodemode, mb_detect_encoding( $newPrice, "auto" )).' <span class="instock">'.$appip_text_instock.'</span></td>'."\n";
					}else{
						$returnval .= '							<td class="amazon-new">'. mb_convert_encoding($newPrice , $encodemode, mb_detect_encoding( $newPrice, "auto" )).' <span class="outofstock">'.$appip_text_outofstock.'</span></td>'."\n";
					}
					$returnval .= '						</tr>'."\n";
				}
				if(isset($result["LowestUsedPrice"])){
					$returnval .= '						<tr>'."\n";
					$returnval .= '							<td class="amazon-used-label">'.$appip_text_usedfrom.':</td>'."\n";
					if($result["TotalUsed"]>0){
						
						$returnval .= '						<td class="amazon-used">'. mb_convert_encoding($result["LowestUsedPrice"], $encodemode, mb_detect_encoding( $result["LowestUsedPrice"], "auto" )) .' <span class="instock">'.$appip_text_instock.'</span></td>'."\n";
					}else{
						$returnval .= '						<td class="amazon-new">'. mb_convert_encoding($result["LowestNewPrice"], $encodemode, mb_detect_encoding( $result["LowestUsedPrice"], "auto" )) . ' <span class="outofstock">'.$appip_text_outofstock.'</span></td>'."\n";
					}
					$returnval .= '						</tr>'."\n";
				}
				$returnval .= '						<tr>'."\n";
				$returnval .= '							<td valign="top" colspan="2">'."\n";
				$returnval .= '								<div class="amazon-dates">'."\n";
				if(isset($result["ReleaseDate"])){
					if(strtotime($result["ReleaseDate"]) > strtotime(date("Y-m-d",time()))){
				$returnval .= '									<span class="amazon-preorder"><br />'.$appip_text_releasedon.' '.date("F j, Y", strtotime($result["ReleaseDate"])).'.</span>'."\n";
					}else{
				$returnval .= '									<span class="amazon-release-date">'.$appip_text_reldate.' '.date("F j, Y", strtotime($result["ReleaseDate"])).'.</span>'."\n";
					}
				}
				$returnval .= '									<br /><div><a style="display:block;margin-top:8px;margin-bottom:5px;width:165px;" '. $apippnewwindowhtml .' href="' . $result["URL"] .'"><img src="'.WP_PLUGIN_URL.'/amazon-product-in-a-post-plugin/images/'.$buyamzonbutton.'" border="0" style="border:0 none !important;margin:0px !important;background:transparent !important;" /></a></div>'."\n";
				if($extrabutton==1 && $aws_partner_locale!='.com'){
				//$returnval .= '									<br /><div><a style="display:block;margin-top:8px;margin-bottom:5px;width:165px;" '. $apippnewwindowhtml .' href="' . $result["URL"] .'"><img src="'.WP_PLUGIN_URL.'/amazon-product-in-a-post-plugin/images/buyamzon-button.png" border="0" style="border:0 none !important;margin:0px !important;background:transparent !important;"/></a></div>'."\n";
				}
				$returnval .= '								</div>'."\n";
				$returnval .= '							</td>'."\n";
				$returnval .= '						</tr>'."\n";
				If(!isset($result["LowestUsedPrice"]) && !isset($result["LowestNewPrice"]) && !isset($result["ListPrice"])){
					$returnval .= '						<tr>'."\n";
					$returnval .= '							<td class="amazon-price-save-label" colspan="2">'.$appip_text_notavalarea.'</td>'."\n";
					$returnval .= '						</tr>'."\n";
				}
				$returnval .= '					</table>'."\n";
				$returnval .= '				</div>'."\n";
				$returnval .= '			</td>'."\n";
				$returnval .= '		</tr>'."\n";
				$returnval .= '	</table>'."\n";
				return $returnval;
			}
		}
	}
	
	//Amazon Product Image from ASIN function - Returns HTML Image Code
	function awsImageGrabber($imgurl, $class=""){
		global $asin;
	    $base_url0 = '<'.'img src="';
	    $base_url = $imgurl;
	    $base_url1 = '"';
	    $base_url1 = $base_url1.' class="amazon-image '.$class.'"';
	    $base_url1 = $base_url1.' />';
		
		if($base_url!=''){
	    	return $base_url0.$base_url.$base_url1;
		}else{
			$base_url = WP_PLUGIN_URL .'/amazon-product-in-a-post-plugin/images/noimage.jpg';
	    	return $base_url0.$base_url.$base_url1;
		}
	}
	
	//Amazon Product Image from ASIN function - Returns URL only
	function awsImageGrabberURL($asin, $size="M"){
	    $base_url = 'http://images.amazon.com/images/P/'.$asin.'.03.';
	    if (strcasecmp($size, 'S') == 0){
	      $base_url .= 'THUMBZZZ';
	    }
	    else if (strcasecmp($size, 'L') == 0){
	      $base_url .= 'LZZZZZZZ';
	    }
	    else{
	      $base_url .= 'MZZZZZZZ';
	    }
	    $base_url .= '.jpg';
	    return $base_url;
	}
	
	  function aws_prodinpost_filter_excerpt($text){
	  	global $post,$apipphookexcerpt;
	  	$ActiveProdPostAWS = get_post_meta($post->ID,'amazon-product-isactive',true);
	  	$singleProdPostAWS = get_post_meta($post->ID,'amazon-product-single-asin',true);
	  	$AWSPostLoc = get_post_meta($post->ID,'amazon-product-content-location',true);
	  	$apippExcerptHookOverride = get_post_meta($post->ID,'amazon-product-excerpt-hook-override',true);
	  	$apippShowSingularonly = '0';
	  	if(get_option('appip_show_single_only')=='1'){$apippShowSingularonly = '1';}
	  	$apippShowSingularonly2 = get_post_meta($post->ID,'amazon-product-singular-only',true);
		if($apippShowSingularonly2=='1'){$apippShowSingularonly = '1';}
		
		if(($apipphookexcerpt==true && $apippExcerptHookOverride!='3')){ //if options say to show it, show it
			//replace short tag here. Handle a bit different than content so they get stripped if they don't want to hook excerpt 
			//we don't want to show the [AMAZON-PRODUCT=XXXXXXXX] tag in the excerpt text!
		 	if ( stristr( $text, '[AMAZONPRODUCT' )) {
				$search = "@(?:<p>)*\s*\[AMAZONPRODUCT\s*=\s*(\w+|^\+)\]\s*(?:</p>)*@i"; 
				if	(preg_match_all($search, $text, $matches)) {
					if (is_array($matches)) {
						foreach ($matches[1] as $key =>$v0) {
							$search = $matches[0][$key];
							$ASINis	= $matches[1][$key];
							if($apippShowSingularonly=='1' && !is_singular()){
								$text	= str_replace ($search, '', $text);
							}else{
								$text	= str_replace ($search, getSingleAmazonProduct($ASINis,''), $text);
							}
						}
					}
				}
		  	}		
			if($apippShowSingularonly=='1'){
			  	if(is_singular()&& ($singleProdPostAWS!='' && $ActiveProdPostAWS!='')){
			  		if($AWSPostLoc=='2'){
			  			//Post Content is the description
			  			$theproduct = getSingleAmazonProduct($singleProdPostAWS,$text);
			  		}elseif($AWSPostLoc=='3'){
			  			//Post Content before product
			  			$theproduct = $text.'<br />'.getSingleAmazonProduct($singleProdPostAWS,'');
			  		}else{
			  			//Post Content after product - default
			  			$theproduct = getSingleAmazonProduct($singleProdPostAWS,'').'<br />'.$text;
			  		}
			  		return $theproduct;
			  	} else {
			  		return $text;
			  	}
			}else{
			  	if($singleProdPostAWS!='' && $ActiveProdPostAWS!=''){
			  		if($AWSPostLoc=='2'){
			  			//Post Content is the description
			  			$theproduct = getSingleAmazonProduct($singleProdPostAWS,$text);
			  		}elseif($AWSPostLoc=='3'){
			  			//Post Content before product
			  			$theproduct = $text.'<br />'.getSingleAmazonProduct($singleProdPostAWS,'');
			  		}else{
			  			//Post Content after product - default
			  			$theproduct = getSingleAmazonProduct($singleProdPostAWS,'').'<br />'.$text;
			  		}
			  		return $theproduct;
			  	} else {
			  		return $text;
			  	}
			}
		}else{
		   if ( stristr( $text, '[AMAZONPRODUCT' )) {
				$search = "@(?:<p>)*\s*\[AMAZONPRODUCT\s*=\s*(\w+|^\+)\]\s*(?:</p>)*@i"; 
				if	(preg_match_all($search, $text, $matches)) {
					if (is_array($matches)) {
						foreach ($matches[1] as $key =>$v0) {
							$search = $matches[0][$key];
							$ASINis	= $matches[1][$key];
							$text	= str_replace ($search, '', $text); //take the darn thing out!
						}
					}
				}
		    }		
		
		}
		return $text;
	  }
	  function aws_prodinpost_filter_content($text){
	  	global $post,$apipphookcontent;
	  	$ActiveProdPostAWS = get_post_meta($post->ID,'amazon-product-isactive',true);
	  	$singleProdPostAWS = get_post_meta($post->ID,'amazon-product-single-asin',true);
	  	$AWSPostLoc = get_post_meta($post->ID,'amazon-product-content-location',true);
	  	$apippContentHookOverride = get_post_meta($post->ID,'amazon-product-content-hook-override',true);
	  	$apippShowSingularonly = get_post_meta($post->ID,'amazon-product-singular-only',true);
		//replace short tag here
		   if ( stristr( $text, '[AMAZONPRODUCT' )) {
				//$search = "@(?:<p>)*\s*\[AMAZONPRODUCT\s*=\s*(\w+|^\+|,)\]\s*(?:</p>)*@i"; //need to change to allow commas in regex
				$search = "@(?:<p>)*\s*\[AMAZONPRODUCT\s*=\s*(.+|^\+)\]\s*(?:</p>)*@i"; 
				if	(preg_match_all($search, $text, $matches)) {
					if (is_array($matches)) {
						foreach ($matches[1] as $key =>$v0) {
							$search = $matches[0][$key];
							$ASINis	= $matches[1][$key];
							if($apippShowSingularonly=='1' && !is_singular()){
								$text	= str_replace ($search, '', $text);
							}else{
								if(strpos($ASINis,',')){
									$product_text = '';
									//clean the spaces out if any
									$ASINis = str_replace(' ','',$ASINis);
									$ASINisArray = explode(',',$ASINis);
									//loop through them
									foreach($ASINisArray as $ASINmt){
										$product_text	.= getSingleAmazonProduct($ASINmt,'');
										$product_text	.= '<div class="appip-multi-divider"><!--appip divider--></div>';
									}
									//replace the original shortcode with new multi products
									$text	= str_replace ($search, $product_text, $text);
								}else{
									$text	= str_replace ($search, getSingleAmazonProduct($ASINis,''), $text);
								}
							}
						}
					}
				}
		    }
			if($apippShowSingularonly=='1'){
			    if(is_singular() && (($apipphookcontent==true && $apippContentHookOverride!='3') || $apippContentHookOverride=='' || $apipphookcontent=='')){ //if options say to show it, show it
				  	if($singleProdPostAWS!='' && $ActiveProdPostAWS!=''){
				  		if($AWSPostLoc=='2'){
				  			//Post Content is the description
				  			$theproduct = getSingleAmazonProduct($singleProdPostAWS,$text);
				  		}elseif($AWSPostLoc=='3'){
				  			//Post Content before product
				  			$theproduct = $text.'<br />'.getSingleAmazonProduct($singleProdPostAWS,'');
				  		}else{
				  			//Post Content after product - default
				  			$theproduct = getSingleAmazonProduct($singleProdPostAWS,'').'<br />'.$text;
				  		}
				  		return $theproduct;
				  	} else {
				  		return $text;
				  	}
				 }
			}else{
			    if(($apipphookcontent==true && $apippContentHookOverride!='3') || $apippContentHookOverride=='' || $apipphookcontent==''){ //if options say to show it, show it
				  	if($singleProdPostAWS!='' && $ActiveProdPostAWS!=''){
				  		if($AWSPostLoc=='2'){
				  			//Post Content is the description
				  			$theproduct = getSingleAmazonProduct($singleProdPostAWS,$text);
				  		}elseif($AWSPostLoc=='3'){
				  			//Post Content before product
				  			$theproduct = $text.'<br />'.getSingleAmazonProduct($singleProdPostAWS,'');
				  		}else{
				  			//Post Content after product - default
				  			$theproduct = getSingleAmazonProduct($singleProdPostAWS,'').'<br />'.$text;
				  		}
				  		return $theproduct;
				  	} else {
				  		return $text;
				  	}
				 }
			}
		 return $text;
	  }
	function aws_prodinpost_addadminhead(){
	  echo '<link rel="stylesheet" href="'.WP_PLUGIN_URL.'/amazon-product-in-a-post-plugin/css/amazon-product-in-a-post-styles-icons.css" type="text/css" media="screen" />'."\n";
	}
	function aws_prodinpost_addhead(){
		global $aws_plugin_version;
		$amazonStylesToUseMine = get_option("apipp_product_styles_mine"); //is box checked?
		echo '<'.'!-- Amazon Product In a Post Plugin Styles & Scripts - Version '.$aws_plugin_version.' -->'."\n";
		if($amazonStylesToUseMine=='true'){ //use there styles
			echo '<link rel="stylesheet" href="'.get_bloginfo('url').'/index.php?apipp_style=custom" type="text/css" media="screen" />'."\n";
		}else{ //use default styles
			echo '<link rel="stylesheet" href="'.get_bloginfo('url').'/index.php?apipp_style=default" type="text/css" media="screen" />'."\n";
		}
		echo '<link rel="stylesheet" href="'.WP_PLUGIN_URL.'/amazon-product-in-a-post-plugin/css/amazon-lightbox.css" type="text/css" media="screen" />'."\n";
		echo '<'.'!-- End Amazon Product In a Post Plugin Styles & Scripts-->'."\n";
	}
	function add_appip_jquery(){
		wp_register_script('appip-amazonlightbox', WP_PLUGIN_URL . '/amazon-product-in-a-post-plugin/js/amazon-lightbox.js');
		wp_enqueue_script('jquery'); 
		wp_enqueue_script('appip-amazonlightbox'); 
	}
?>