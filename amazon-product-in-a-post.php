<?php
/*
Plugin Name: Amazon Product In a Post Plugin (3.0.0 beta1)
Plugin URI: http://fischercreativemedia.com/wordpress-plugins/amazon-affiliate-product-in-a-post/
Description: Quickly add a formatted Amazon Product (image, pricing and buy button, etc.) to a post by using just the Amazon product ASIN (ISBN-10). Great for writing product reviews or descriptions to help monetize your posts and add content that is relevant to your site. You can also customize the styles for the product data. Remember to add your Amazon Affiliate ID on the <a href="admin.php?page=apipp_plugin_admin">options</a> page or all sales credit will go to the plugin creator by default.
Author: Don Fischer
Author URI: http://www.fischercreativemedia.com/
Version: 3.0.0b1
    Copyright (C) 2009-2013 Donald J. Fischer
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
if(!defined('WP_DEBUG') || WP_DEBUG==0){
	//remove for final release!
	//ini_set('display_errors', 0);
	//ini_set('log_errors', 1);
	//ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
	//error_reporting( E_ALL  & ~E_NOTICE & ~E_WARNING);
}

// Variables
	global $public_key;
	global $private_key; 
	global $aws_partner_id;
	global $aws_eatra_pages;
	global $aws_plugin_version;
	global $aws_plugin_dbversion;
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
	global $appip_text_manufacturer;
	global $appip_text_ESRBAgeRating;
	global $appip_text_feature;
	global $appip_text_platform;
	global $appip_text_genre;
	global $buyamzonbutton;
	global $addestrabuybutton;
	global $awspagequery;
	global $apip_language;
	global $appuninstall;
	global $appuninstallall;
	global $validEncModes;
	
	register_activation_hook(__FILE__,'appip_install');
	register_deactivation_hook(__FILE__,'appip_deinstall');
	
// MISC Settings, etc.
	session_start();	
    //if(!isset($_SESSION['Amazon-PIPP-Cart-HMAC'])) $_SESSION['Amazon-PIPP-Cart-HMAC'] = '';
    //if(!isset($_SESSION['Amazon-PIPP-Cart-Encoded-HMAC'])) $_SESSION['Amazon-PIPP-Cart-Encoded-HMAC']='';
    //if(!isset($_SESSION['Amazon-PIPP-Cart-ID'])) $_SESSION['Amazon-PIPP-Cart-ID']='';

	//added in 1.7 to allow those that could not use file_get_contents() to use Curl instead.		
	if(get_option('appip_amazon_usefilegetcontents')==''){update_option('appip_amazon_usefilegetcontents','1');}
	if(get_option('appip_amazon_usecurl')==''){update_option('appip_amazon_usecurl','0');}
	if(get_option('apipp_API_call_method')=='' && get_option('appip_amazon_usecurl')=='0'){
		update_option('apipp_API_call_method','0');}
	elseif(get_option('apipp_API_call_method')=='' && get_option('appip_amazon_usecurl')!='1'){
		update_option('apipp_API_call_method','1');
	}
	
	$appipitemnumber	= 0;
	$awspagequery		= '';
	$public_key 		= get_option('apipp_amazon_publickey'); //Developer Public AWS Key
	$private_key 		= get_option('apipp_amazon_secretkey'); //Developer Secret AWS Key
	$appuninstall 		= get_option('apipp_uninstall'); //Uninstall database and options
	$appuninstallall	= get_option('apipp_uninstall_all'); //Uninstall shortcodes in pages an posts
	$aws_partner_id		= get_option('apipp_amazon_associateid'); //Amazon Partner ID 
	$awsPageRequest 	= 1;
	$aws_plugin_version = "3.0.0";
	$aws_plugin_dbversion = '3.0.0';
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
	$validEncModes 		= array('ISO-8859-1','ASCII','ISO-8859-2','ISO-8859-3','ISO-8859-4','ISO-8859-5','ISO-8859-6','ISO-8859-7','ISO-8859-8','ISO-8859-9','ISO-8859-10','ISO-8859-15','ISO-2022-JP','ISO-2022-JP-2','ISO-2022-KR','UTF-8','UTF-16');
	
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
		function mb_detect_encoding($etext='', $encodemode=array(),$encstrict = false){
			return $etext;
		}
	}	
	if(!function_exists('mb_detect_order')){
		function mb_detect_order(){
			return array('ASCII','ISO-8859-1','UTF-8');
		}
	}	
	
	// 1.7 - change encoding if needed via GET
	// use http://yoursite.com/?resetenc=UTF-8 or http://yoursite.com/?resetenc=ISO-8859-1
	// this will be the mode you want the text OUTPUT as.
	if(isset($_GET['resetenc'])){
		if(in_array(strtoupper($_GET['resetenc']),$validEncModes)){
			update_option('appip_encodemode',strtoupper($_GET['resetenc']));
			$encodemode = strtoupper($_GET['resetenc']);
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
		//update_option('apipp_amazon_locale','com'); //set default to US
		//$aws_partner_locale='com';
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
	if(isset($_GET['awsclearcache'])){ //future item for search results
		if(is_numeric($_GET['awsclearcache'])&& $_GET['awsclearcache'] == '1'){
			global $wpdb;
			$checksql= "DELETE FROM ".$wpdb->prefix."amazoncache;";
			$result = $wpdb->query($checksql);
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
	
// Warnings Quickfix
	if(get_option('apipp_hide_warnings_quickfix')==true){
		 ini_set("display_errors", 0); //turns off error display
	}

// Includes
	require_once("inc/amazon-product-in-a-post-activation.php"); 		//Install and Uninstall hooks
	require_once("inc/amazon-product-in-a-post-functions.php"); 		//Functions
	require_once("inc/sha256.inc.php"); 								//required for php4
	require_once("inc/aws_signed_request.php"); 						//major workhorse for plugin
	require_once("inc/amazon-product-in-a-post-tools.php"); 			//edit box for plugin
	require_once("inc/amazon-product-in-a-post-options.php"); 			//admin options for plugin
	require_once("inc/amazon-product-in-a-post-translations.php"); 		//translations for plugin
	require_once("inc/amazon-product-in-a-post-styles-product.php"); 	//styles for plugin
	require_once("inc/amazon-product-in-a-post-shortcodes.php"); 		//shortcodes for plugin


	$thisstyleversion	=	get_option('apipp_product_styles_default_version');
	//echo $thisstyleversion.'<br/>'.$thedefaultapippstyle;
	//upgrade check. Lets me add/change the default style etc to fix/add new items during updrages.
	if($thisstyleversion != "1.8" || get_option("apipp_product_styles_default")==''){
		update_option("apipp_product_styles_default",$thedefaultapippstyle);
		update_option("apipp_product_styles_default_version","1.8");
		//add the new element style to their custom ones - so at least it has the default functionality. They can change it after if they like
		$apipp_product_styles_cust_temp = get_option("apipp_product_styles");
 		if($apipp_product_styles_cust_temp!=''){
 			update_option("apipp_product_styles",$apipp_product_styles_cust_temp."\n".".amazon-manufacturer{color : #666;font-size : 12px;}"."\n".".amazon-ESRB{color : #666;font-size : 12px;}"."\n".".amazon-feature{color : #666;font-size : 12px;}"."\n".".amazon-platform{color : #666;font-size : 12px;}"."\n".".amazon-system{color : #666;font-size : 12px;}"."\n");
 		}
		update_option("apipp_amazon_notavailable_message","This item is may not be available in your area. Please click the image or title of product to check pricing & availability."); //default message
		update_option("apipp_amazon_hiddenprice_message","Price Not Listed"); //default message - done
		update_option("apipp_hook_content","1"); //default is yes - done
		update_option("apipp_hook_excerpt","0"); //default is no - done
		update_option('apipp_open_new_window',"0"); //default is no - newoption added at 1.6 - done
	}

add_action('admin_menu', 'llkl_js_libs'); 
function llkl_js_libs() {
	if ( $_GET['page'] == "appip-layout-styles" ) {
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('jquery-ui-sortable');
	}
}
