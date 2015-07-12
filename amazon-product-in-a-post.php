<?php
/*
Plugin Name: Amazon Product In a Post Plugin
Plugin URI: http://fischercreativemedia.com/wordpress-plugins/amazon-affiliate-product-in-a-post/
Description: Quickly add stylized Amazon Products to your site. Requires signup for an Amazon Affiliate Account and Product Advertising API Keys which are currently FREE from Amazon.
Author: Don Fischer
Author URI: http://www.fischercreativemedia.com/
Version: 3.6.0
    Copyright (C) 2009-2015 Donald J. Fischer
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
	global $encodemode;
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
	global $degunningAPPIP;
	
	register_activation_hook(__FILE__,'appip_install');
	register_deactivation_hook(__FILE__,'appip_deinstall');
	
// MISC Settings, etc.
	//session_start();	
    //if(!isset($_SESSION['Amazon-PIPP-Cart-HMAC'])) $_SESSION['Amazon-PIPP-Cart-HMAC'] = '';
    //if(!isset($_SESSION['Amazon-PIPP-Cart-Encoded-HMAC'])) $_SESSION['Amazon-PIPP-Cart-Encoded-HMAC']='';
    //if(!isset($_SESSION['Amazon-PIPP-Cart-ID'])) $_SESSION['Amazon-PIPP-Cart-ID']='';

	//allow those that can not use file_get_contents() to use Curl instead.		
	if(get_option('appip_amazon_usefilegetcontents')==''){update_option('appip_amazon_usefilegetcontents','1');}
	if(get_option('appip_amazon_usecurl')==''){update_option('appip_amazon_usecurl','0');}
	if(get_option('apipp_API_call_method')=='' && get_option('appip_amazon_usecurl')=='0'){
		update_option('apipp_API_call_method','0');}
	elseif(get_option('apipp_API_call_method')=='' && get_option('appip_amazon_usecurl')!='1'){
		update_option('apipp_API_call_method','1');
	}
	
	$degunningAPPIP		= false;
	$appipitemnumber	= 0;
	$awspagequery		= '';
	$public_key 		= get_option('apipp_amazon_publickey'); //Developer Public AWS Key Removed
	$private_key 		= get_option('apipp_amazon_secretkey'); //Developer Secret AWS Key Removed
	$appuninstall 		= get_option('apipp_uninstall'); //Uninstall database and options
	$appuninstallall	= get_option('apipp_uninstall_all'); //Uninstall shortcodes in pages an posts
	$aws_partner_id		= get_option('apipp_amazon_associateid'); //Amazon Partner ID 
	$awsPageRequest 	= 1;
	$aws_plugin_version = "3.6.0";
	$aws_plugin_dbversion = '3.6.0';
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
	$encodemode 		= get_option('appip_encodemode'); //UTF-8 will be default
	$validEncModes 		= array('ISO-8859-1','ASCII','ISO-8859-2','ISO-8859-3','ISO-8859-4','ISO-8859-5','ISO-8859-6','ISO-8859-7','ISO-8859-8','ISO-8859-9','ISO-8859-10','ISO-8859-15','ISO-2022-JP','ISO-2022-JP-2','ISO-2022-KR','UTF-8','UTF-16');
	
	// api get method defaults/check
	if($apip_getmethod=='0'){
		$apip_usefileget = '1';
	}
	if($apip_getmethod=='1'){
		$apip_usecurlget = '1';
	}
	if($apip_getmethod==''){
		$apip_usefileget = '1'; //set default if not set
	}
	//Encode Mode
	if(get_option('appip_encodemode')==''){
		update_option('appip_encodemode','UTF-8'); //set default to UTF-8
		$encodemode="UTF-8";
	}
	//backward compat.
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
	
	// Change encoding if needed via GET -  use http://yoursite.com/?resetenc=UTF-8 or http://yoursite.com/?resetenc=ISO-8859-1 - this will be the mode you want the text OUTPUT as.
	if( isset( $_GET['resetenc'] ) && ( is_user_logged_in() && current_user_can( 'manage_options' ) ) || can_set_debug() ){
		if( in_array( strtoupper( $_GET['resetenc'] ), $validEncModes ) ){
			update_option( 'appip_encodemode', strtoupper( esc_attr( $_GET['resetenc'] ) ) );
			$encodemode = strtoupper(esc_attr($_GET['resetenc']));
		}
	}
	if($apippopennewwindow==true){
		$apippnewwindowhtml=' target="amazonwin" ';
	}
	if($amazonerrormsg == ''){
		$amazonerrormsg = 'Product Unavailable.';
	}
	if($amazonhiddenmsg == ''){
		$amazonhiddenmsg = 'Visit Amazon for Price.';
	}
	if($aws_partner_id == ''){
		$aws_partner_id = ""; 	//Amazon Partner ID - if one is not set up, plugin will not output product!!
	}
	if($public_key == ''){
		$public_key = "";  		//Developer Public AWS Key Removed 3.5.1
	}
	if($private_key == ''){
		$private_key = ""; 		//Developer Secret AWS Key Removed 3.5.1
	}
	if(trim(get_option("apipp_product_styles",'')) == ''){ //reset to default styles if user deletes styles in admin
		update_option("apipp_product_styles",$thedefaultapippstyle);
	}
	if(trim(get_option("apipp_amazon_debugkey",'')) == ''){ //generate debug key
		$randomkey = md5(uniqid(get_bloginfo('url').time(), true));
		update_option("apipp_amazon_debugkey",$randomkey);
	}

// Filters & Hooks
	//add_action('wp_head','aws_prodinpost_addhead',10); //add styles to head
	//add_action('wp','aws_prodinpost_cartsetup', 1, 2); //Future Item
	add_filter( 'the_content', 'aws_prodinpost_filter_content', 10); //hook content - we will filter the override after
	add_filter( 'the_excerpt', 'aws_prodinpost_filter_excerpt', 10); //hook excerpt - we will filter the override after 
	add_filter( 'plugin_row_meta',  'apipp_filter_plugin_links', 10, 2 );
	add_action( 'wp','add_appip_jquery'); //enqueue scripts
	add_action( 'plugin_action_links_' . plugin_basename(__FILE__),'apipp_filter_plugin_actions' );
	function apipp_filter_plugin_actions($links){$new_links = array();$new_links[] = '<a href="admin.php?page=apipp-main-menu">Getting Started</a>';return array_merge($links,$new_links );}
	function apipp_filter_plugin_links($links, $file){if ( $file == plugin_basename(__FILE__) ){$links[] = '<a href="admin.php?page=apipp-main-menu">Getting Started</a>';$links[] = '<a href="admin.php?page=apipp_plugin-shortcode">Shortcode Usage</a>';$links[] = '<a href="admin.php?page=apipp_plugin-faqs">FAQs</a>';$links[] = '<a target="_blank" href="http://fischercreativemedia.com/donations/">Donate</a>';}return $links;}
	
// Warnings Quickfix
	if(get_option('apipp_hide_warnings_quickfix') == true){
		 ini_set("display_errors", 0); //turns off error display
	}

// Includes
	require_once("inc/amazon-product-in-a-post-activation.php"); 		//Install and Uninstall hooks
	require_once("inc/amazon-product-in-a-post-functions.php"); 		//Functions
	require_once("inc/amazon-product-in-a-post-sha256.inc.php"); 		//required for php4
	require_once("inc/amazon-product-in-a-post-aws-signed-request.php");//major workhorse for plugin
	require_once("inc/amazon-product-in-a-post-tools.php"); 			//edit box for plugin
	require_once("inc/amazon-product-in-a-post-options.php"); 			//admin options for plugin
	require_once("inc/amazon-product-in-a-post-translations.php"); 		//translations for plugin
	//require_once("inc/amazon-product-in-a-post-styles-product.php"); 	//styles for plugin - REMOVED 3.6.0
	require_once("inc/amazon-product-in-a-post-shortcodes.php"); 		//shortcodes for plugin

	if ( is_admin() && !( defined('DOING_AJAX') && DOING_AJAX )){
		//upgrade check. Lets me add/change the default style etc to fix/add new items during updrages.
		if(get_option("apipp_product_styles_default",'') == ''){
			update_option("apipp_product_styles_default",$thedefaultapippstyle);
		}
		$thisstyleversion	=	get_option('apipp_product_styles_default_version','0');
		
		if($thisstyleversion != "2.0"){
			update_option("apipp_product_styles_default_version","2.0");
			//add the new element style to their custom ones - so at least it has the default functionality. They can change it after if they like
			$apipp_product_styles_cust_temp = get_option("apipp_product_styles",'');
			$apipp_product_styles_cust_temp = $apipp_product_styles_cust_temp;
			if($apipp_product_styles_cust_temp != ''){
				update_option("apipp_product_styles",'/*version 2.0 Modified*/'."\n".$apipp_product_styles_cust_temp."
a[target=\"amazonwin\"] {margin: 0 !important;}
a[rel^=\"appiplightbox\"] { display: inline-block; font-size: .75rem; text-align: center; max-width: 100%; }
table.amazon-product-table td { border: 0 none ; padding: 0; }
.amazon-image-wrapper { padding: 0 1%; text-align: center;float: left; margin: 0 2% 0 0;-webkit-box-sizing: border-box;-moz-box-sizing: border-box; box-sizing: border-box; max-width: 25%; width: 100%; }
.amazon-image-wrapper a { border-bottom: none; display: block; font-size: 12px; text-align: center; }
.amazon-image-wrapper br {display: none;}
.appip-label {font-size: inherit;font-weight: bold;text-transform: uppercase;}
.amazon-product-table .amazon-buying h2.amazon-asin-title { border-bottom: 0 none; font-size: 1rem; line-height: 1.25em; margin: 0; }
.amazon-product-table hr { height: 0px; margin: 6px 0; }
.amazon-list-price-label, .amazon-new, .amazon-new-label, .amazon-used-label, .amazon-list-price {}
.amazon-dates {height: auto;}
.amazon-dates br {display: none;}
.amazon-list-price-label, .amazon-new-label, .amazon-used-label { font-weight: bold; min-width: 7em;width: auto;}
.amazon-product-table:after {clear: both;}
.amazon-tiny {text-align: center;}
#content table.amazon-product-table { clear: both; margin-bottom: 10px; }
#content table.amazon-product-price { -moz-border-radius: 0; -webkit-border-radius: 0; border-collapse: collapse; border-radius: 0; border: 0 none; margin: 0; max-width: 100%; width: auto; }
#content table.amazon-product-price td { border: 0 none !important; padding: .25em 0; }
#content table.amazon-product-table > tbody > tr > td {padding: .5rem !important;}
.amazon-buying { box-sizing: border-box; float: left; max-width: 73%; width: 100%; }
table.amazon-product-table hr {display:inline-block;max-width:100%;  width: 100%;  border-top: 1px solid #e2e5e7;}
table.amazon-product-price { float: left; margin: 0; width: 100%; }
.amazon-product-table a { border-bottom: 0 none; text-decoration: none; }
table.amazon-product-price td { padding: 1%; width: auto; }
table.amazon-product-price tr:first-child td {width:7em;}
.amazon-additional-images-text { display: block; font-size: x-small; font-weight: bold; }
.amazon-dates br {display: none;}
.amazon-element-imagesets { border: 1px solid #ccc; display: inline-block; margin: 5px; overflow: hidden; padding: 10px; }
.amazon-element-imagesets br {display: none;}
.amazon-element-imagesets a { float: left; margin: 3px; }
.amazon-element-imagesets a img {border: 1px solid #fff;}
.amazon-additional-images-wrapper { border: 1px solid #ccc; box-sizing: border-box; display: inline-block; margin: 1%; overflow: hidden; padding: 2%; }
.amazon-additional-images-wrapper a { float: left; margin: 3px; }
.amazon-additional-images-wrapper a img {border: 1px solid #fff;width:25px;}
.amazon-additional-images-wrapper br {display: none;}
img.amazon-varient-image {max-width: 50px;margin: 1%;padding: 1px;background-color: #999;}
img.amazon-varient-image:hover {background-color: #3A9AD9;}
.amazon_variations_wrapper{}
.amazon_varients{}
.amazon-varient-type-link {display: inline-block;font-weight: bold;}
.amazon-varient-type-price {display: inline-block;color: #EA0202;font-weight: bold;}
.amazon-price-button{margin-top:2%;display:block;}
.amazon-price-button > a{display:block;margin-top:8px;margin-bottom:5px;width:165px;}
.amazon-price-button > a img.amazon-price-button-img{border:0 none;margin:0px;background:transparent;}
.amazon-product-table td.amazon-list-variants {border-top: 1px solid #CCC;border-bottom: 1px solid #ccc;padding: 2%;margin-top:2%;}
.amazon-variant-price-text{color:initial;}
span.amazon-variant-price-text {font-weight: normal;}
@media only screen and (max-width : 1200px) {}
@media only screen and (max-width : 992px) {}
@media only screen and (max-width : 768px) {}
@media only screen and (max-width : 550px) {
	.amazon-image-wrapper { padding: 0; text-align: center; float: none; margin: 0 auto 2%; -webkit-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box; max-width: 75%; width: 100%; }
	.amazon-buying { box-sizing: border-box; float: none; max-width: 100%; width: 100%; }
	.amazon-product-price,table.amazon-product-price { float: none; margin: 0; max-width: 100%; width: 100%; }
	.amazon-product-pricing-wrap { display: block; clear: both; }
	.amazon-dates { text-align: center; }
	.amazon-dates a { margin: 0 auto !important; width: 50% !important; }
	.amazon-dates a img { margin: 5% auto 0 !important; width: 95% !important; }
	span.amazon-tiny {margin-top: 2px;background: #ccc;padding:1%;display: block;font-size: 1.25em;color: #000;text-transform: uppercase;border: 1px solid #999;line-height: 1.25em;}
	span.amazon-tiny:active {background: #EDEDED;}
	.amazon-product-table .amazon-buying h2.amazon-asin-title {margin-top: 3%;display: block;line-height: 1.5em;}
	.amazon-additional-images-wrapper { max-width: 100%; width: 100%; margin: 1% 0; text-align: center; }
	.amazon-additional-images-wrapper a { float: none; display: inline-block; width: 18%; margin: 0; -webkit-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box; }
	.amazon-additional-images-wrapper a img { width: 75%; }
	td.amazon-list-price-label, td.amazon-new-label, td.amazon-used-label, td.amazon-used-price, td.amazon-new, td.amazon-list-price { display: inline-block; }
}
@media only screen and (max-width : 320px) {}");
			}
			if( get_option("apipp_amazon_notavailable_message",'') == ''){update_option("apipp_amazon_notavailable_message","This item is may not be available in your area. Please click the image or title of product to check pricing & availability.");} //default message
			if( get_option("apipp_amazon_hiddenprice_message",'') == ''){update_option("apipp_amazon_hiddenprice_message","Price Not Listed");} //default message - done
			if( get_option("apipp_hook_content",'') == ''){update_option("apipp_hook_content","1");} 		//default is yes - done
			if( get_option("apipp_hook_excerpt",'') == ''){update_option("apipp_hook_excerpt","0");}		//default is no - done
			if( get_option("apipp_open_new_window",'') == ''){update_option('apipp_open_new_window',"0");} 	//default is no - newoption added at 1.6 - done
		}
	}
function can_set_debug(){
	global $degunningAPPIP;
	if( $degunningAPPIP )
		return true;
	return false;
}
function appip_admin_scripts($hook) {
	wp_enqueue_style( 'amazon-plugin-admin-styles',plugins_url('/css/amazon-admin.css',__FILE__),null,'15-07-12');
	if ( $hook == "amazon-product_page_appip-layout-styles" ) {
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('jquery-ui-sortable');
	}elseif("amazon-product_page_apipp-cache-page" == $hook || "amazon-product_page_apipp-add-new" == $hook || $hook == "post.php" || $hook == "post-new.php" || $hook == "edit.php"){
		wp_enqueue_script('amazon-plugin-admin',plugins_url('/js/amazon-admin.js',__FILE__),array('jquery-ui-tooltip'),'15-07-12');
		wp_localize_script('amazon-plugin-admin','appipData',array( 'ajaxURL' => admin_url('admin-ajax.php'), 'appip_nonce' => wp_create_nonce( 'appip_cache_delete_nonce_ji9osdjfkjl' ), 'confirmDel' => __('Are you sure you want to delete this cache?', 'amazon-product-in-a-post-plugin'),'noCacheMsg' => __('no cached products at this time', 'amazon-product-in-a-post-plugin'), 'deleteMsgErr' => __('there was an error - the cache could not be deleted', 'amazon-product-in-a-post-plugin') ) );
	}
}
add_action( 'admin_enqueue_scripts', 'appip_admin_scripts' );