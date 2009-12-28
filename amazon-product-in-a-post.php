<?php
/*
Plugin Name: Amazon Product In a Post Plugin
Plugin URI: http://fischercreativemedia.com/wordpress-plugins/amazon-affiliate-product-in-a-post/
Description: Quickly add a formatted Amazon Product (image, pricing and buy button, etc.) to a post by using just the Amazon product ASIN (ISBN-10). Great for writing product reviews or descriptions to help monetize your posts and add content that is relevant to your site. You can also customize the styles for the product data. Remember to add your Amazon Affiliate ID on the <a href="admin.php?page=apipp_plugin_admin">options</a> page or all sales credit will go to the plugin creator by default.
Author: Don Fischer
Author URI: http://www.fischercreativemedia.com/
Version: 1.9
Version info:
1.9 - Fix to undefined function error - causing problems - sorry for the trouble everyone. (12/28/2009)
1.8 - Added Fix for users without encoding functions in PHP4 to be able to use. This may have caused some problems with users on 1.7. (12/21/2009)
	  Added Options for Language selection. (12/21/2009)
	  Added French Language and buttons (special thanks to Henri Sequeira for translations). (12/21/2009)
	  Added Lightbox type effect for "View Larger Image" function.(12/22/2009)
	  Modified Style Call to use a more WP friendly method to not rely on a "guess" as to where the core WP files are located. (12/22/2009)
	  Fixed Open in new window functionality - was not working 100% of the time. (12/22/2009)
1.7 - Add Curl option for users that cant use file_get_contents() for some reason or another. (12/1/2009)
	  Added Show on Single Page Only option to Options Page.(11/30/2009)
	  Added a way to change encoding display of prices from API if funny characters are showing.(12/1/2009)
1.6 - Added Options to let admin choose if they want to "Hook" into the_excerpt and the_content hooks in Main Options with override on each post/page.(10/3/2009)
 	  Added Open in a New Window Option (for Amazon button) in Main Options with override on each page/post.(10/3/2009)
	  Added "Show Only on Single Page" option to individual post/page options.(10/4/2009)
	  Added Shortcode functionality to allow addition of unlimited products in the post/page content.(10/4/2009)
	  Added "Quick Fix - Hide Warnings" option in Main Options. Adds ini_set("display_errors", 0) to code to help some admins hide any Warnings if their PHP settings are set to show them.(10/3/2009)
	  Fixed Array Merge Warning when item is not an array.(10/3/2009)
	  Fixed "This Items not available in your locale" message as to when it acatually displays and spelling.(10/3/2009)
	  Added Options to let admin add their own Error Messages for Item Not available and Amazon Hidden Price notificaton.(10/3/2009)
	  Updated Default CSS styles to include in Stock and Out of Stock classes and made adjustments to other improper styles. (10/3/2009)1.5 - Remove hook to the_excerpt because it could cause problems in themes that only want to show text. (9/17/2009)
1.4 - Added menthod to restore default CSS if needed - by deleting all CSS in options panel and saving - default css will re-appear in box. (9/16/2009) 
1.3	- Added new feature to be able to adjust or add your own styles. (9/16/2009)
1.2	- Fix to image call procedure to help with "no image available" issue. (9/15/2009)
1.1	- Minor Fixes/Spelling Error corrections & code cleanup to prep for WordPress hosting of Plugin. (9/14/2009)
1.0	- Plugin Release (9/12/2009)

    Copyright (C) 2009 Donald J. Fischer
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
//error_reporting(E_ALL);
//ini_set("display_errors", 0); 

// Warnings Quickfix
	if(get_option('apipp_hide_warnings_quickfix')==true){
		 ini_set("display_errors", 0); //turns off error display
	}

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

// Includes
	require_once("inc/sha256.inc.php"); //required for php4
	require_once("inc/aws_signed_request.php"); //major workhorse for plugin
	require_once("inc/amazon-product-in-a-post-tools.php"); //edit box for plugin
	require_once("inc/amazon-product-in-a-post-options.php"); //admin options for plugin
	require_once("inc/amazon-product-in-a-post-translations.php"); //translations for plugin
	require_once("inc/amazon-product-in-a-post-styles-product.php"); //styles for plugin

//upgrade check. Lets me add/change the default style etc to fix/add new items during updrages.
	$thisstyleversion=get_option('apipp_product_styles_default_version');
	if($thisstyleversion!="1.6"){
		update_option("apipp_product_styles_default",$thedefaultapippstyle);
		update_option("apipp_product_styles_default_version","1.6");
		update_option("apipp_amazon_notavailable_message","This item is may not be available in your area. Please click the image or title of product to check pricing & availability."); //default message
		update_option("apipp_amazon_hiddenprice_message","Price Not Listed"); //default message - done
		update_option("apipp_hook_content","1"); //default is yes - done
		update_option("apipp_hook_excerpt","0"); //default is no - done
		update_option('apipp_open_new_window',"0"); //newoption added at 1.6 - done
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
	$public_key 		= get_option('awsplugin_amazon_publickey'); //Developer Public AWS Key
	$private_key 		= get_option('awsplugin_amazon_secretkey'); //Developer Secret AWS Key
	$aws_partner_id		= get_option('apipp_amazon_associateid'); //Amazon Partner ID 
	//$aws_partner_locale	= get_option('apipp_amazon_locale'); //Amazon Locale - moved to translations file
	$awsPageRequest 	= 1;
	$aws_plugin_version = "1.8";
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
	// this will be the mode you wat the text OUTPUT as.
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
			$ASIN = $asin; //valid ASIN
			$errors='';
			//Main Amazon API Call
			$pxml = aws_signed_request($aws_partner_locale, array("Operation"=>"ItemLookup","ItemId"=>"$ASIN","ResponseGroup"=>"ItemAttributes,Images,Offers","IdType"=>"ASIN","AssociateTag"=>"$aws_partner_id"), $public_key, $private_key);
			
			//print_r($pxml);
			if(isset($pxml["ItemLookupResponse"]["Items"]["Request"]["Errors"]["Error"]["Message"])){
				$errors=$pxml["ItemLookupResponse"]["Items"]["Request"]["Errors"]["Error"]["Message"];
			}
			if($errors!=''){
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
				$returnval .= '									<br /><div><a style="display:block;margin-top:8px;margin-bottom:5px;width:165px;" '. $apippnewwindowhtml .' href="' . $result["URL"] .'"><img src="'.get_bloginfo('url').'/'.PLUGINDIR.'/amazon-product-in-a-post-plugin/images/'.$buyamzonbutton.'" border="0" style="border:0 none !important;margin:0px !important;background:transparent !important;"/></a></div>'."\n";
				if($extrabutton==1 && $aws_partner_locale!='.com'){
				//$returnval .= '									<br /><div><a style="display:block;margin-top:8px;margin-bottom:5px;width:165px;" '. $apippnewwindowhtml .' href="' . $result["URL"] .'"><img src="'.get_bloginfo('url').'/'.PLUGINDIR.'/amazon-product-in-a-post-plugin/images/buyamzon-button.png" border="0" style="border:0 none !important;margin:0px !important;background:transparent !important;"/></a></div>'."\n";
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
	
	// Search Product API Call - Returns Search Product Data for 10 items per page. 
	// For Future Use - Not Active Right now.
	function searchAmazonProduct($searchkey=''){
		global $public_key, $private_key, $aws_partner_id, $aws_eatra_pages,$aws_partner_locale;
		$pxml = aws_signed_request($aws_partner_locale, array($aws_eatra_pages."Operation"=>"ItemSearch","SearchIndex"=>Books,"Keywords"=>"harry+potter","ResponseGroup"=>"ItemAttributes","IdType"=>"ASIN","MerchantId"=>"Amazon","AssociateTag"=>"$aws_partner_id"), $public_key, $private_key);
		foreach($pxml['ItemSearchResponse']['Items']['Item'] as $items){
			$result 	 = FormatSearchResult($items["ItemAttributes"]);
			$returnval  .= awsImageGrabber($items["ASIN"], "M") . "<br />";
			$returnval  .= "Title: ".$result["Title"] . "<br />";
			$returnval  .= "ASIN: ".$items["ASIN"] . "<br />";
			$returnval  .= "Price: ".$result["Price"] . "<br />";
			$returnval  .= "<br /><br />";
			return $returnval;
		}
	}
	// Purchase URL Product API Call - For Specific Offer Purchase
	// For Future Use When Cart is Implemented (If I get around to it) - Not Active Right now.
	function AmazonPurchaseProductURL($offerlistingid=''){
		global $public_key, $private_key, $aws_partner_id, $aws_eatra_pages,$aws_partner_locale;
		if($offerlistingid!=''){
			if($_SESSION['Amazon-PIPP-Cart-ID']==''){
				//Main API Call if no cart ID is found
				$pxml = aws_signed_request($aws_partner_locale, array(
					"Operation" => "CartCreate",
					"MergeCart"=> "True",
					"Item.1.OfferListingId" =>$offerlistingid,
					"Item.1.Quantity" => 1,
					"AssociateTag"=>"$aws_partner_id"
					), $public_key, $private_key);
				$result = $pxml["CartCreateResponse"]["Cart"];
				$CartId = $result["CartId"]; 
	            $HMAC   = $result["HMAC"];
	            $URLEncodedHMAC = $result["URLEncodedHMAC"];
	            $PurchaseURL = $result["PurchaseURL"];
				return $PurchaseURL;
			}else{
				//Main API Call if a cart ID is found
				$pxml = aws_signed_request($aws_partner_locale, array(
					"Operation" => "CartCreate",
					"MergeCart"=> "True",
					"Item.1.OfferListingId" =>$offerlistingid,
					"Item.1.Quantity" => 1,
					"AssociateTag"=>"$aws_partner_id"
					), $public_key, $private_key);
				$result = $pxml["CartCreateResponse"]["Cart"];
				$CartId = $result["CartId"];
	            $HMAC   = $result["HMAC"];
	            $URLEncodedHMAC = $result["URLEncodedHMAC"];
	            $PurchaseURL = $result["PurchaseURL"];
				return $PurchaseURL;
			}
		}
		return;
	}
	
	//For Future Use - Not Active Right now. Sets up Cart ID from Amazon API
	function aws_prodinpost_cartsetup(){ 
		global $wp_query, $post, $public_key, $private_key, $aws_partner_id, $aws_eatra_pages,$aws_partner_locale;
		session_start();
		if(isset($_GET['offerid'])){
			$offerlistingid = $_GET['offerid'];
			if(	$_SESSION['Amazon-PIPP-Cart-ID'] !=''){
				//cartadd
				$pxml = aws_signed_request($aws_partner_locale, array(
					"CartId" => $_SESSION['Amazon-PIPP-Cart-ID'],
					"HMAC" => $_SESSION['Amazon-PIPP-Cart-HMAC'],
					"Operation" => "CartAdd",
					"MergeCart"=> "False",
					"Item.1.OfferListingId" =>$offerlistingid,
					"Item.1.Quantity" => 1,
					"AssociateTag"=>"$aws_partner_id"
					), $public_key, $private_key);
				$result = $pxml["CartAddResponse"]["Cart"];
				$CartId = $result["CartId"]; 
	            $HMAC   = $result["HMAC"];
	            $URLEncodedHMAC = $result["URLEncodedHMAC"];
	            $PurchaseURL = $result["PurchaseURL"];
			}else{
				//cartcreate
				$pxml = aws_signed_request($aws_partner_locale, array(
					"Operation" => "CartCreate",
					"MergeCart"=> "False",
					"Item.1.OfferListingId" =>$offerlistingid,
					"Item.1.Quantity" => 1,
					"AssociateTag"=>"$aws_partner_id"
					), $public_key, $private_key);
				$result = $pxml["CartCreateResponse"]["Cart"];
				$CartId = $result["CartId"]; 
	            $HMAC   = $result["HMAC"];
	            $URLEncodedHMAC = $result["URLEncodedHMAC"];
	            $_SESSION['Amazon-PIPP-Cart-HMAC'] = $HMAC;
	            $_SESSION['Amazon-PIPP-Cart-Encoded-HMAC'] = $URLEncodedHMAC;
	            $_SESSION['Amazon-PIPP-Cart-ID'] = $CartId;
				
				//cartadd
				$pxml = aws_signed_request($aws_partner_locale, array(
					"CartId" => $CartId,
					"HMAC" => $HMAC ,
					"Operation" => "CartAdd",
					"MergeCart"=> "False",
					"Item.1.OfferListingId" =>$offerlistingid,
					"Item.1.Quantity" => 1,
					"AssociateTag"=>"$aws_partner_id"
					), $public_key, $private_key);
				$result = $pxml["CartAddResponse"]["Cart"];
				$CartId = $result["CartId"]; 
	            $HMAC   = $result["HMAC"];
	            $URLEncodedHMAC = $result["URLEncodedHMAC"];
	            $PurchaseURL = $result["PurchaseURL"];
			}
			
		}
			// This Part Manipulates/Tricks the $post and $wp_query Objects to 
			// create a custom Added to Cart Page without needing to Physically create one.
			// It also makes it so you don't need to go to Amazon.com when you add an item to the cart.
			
			if(isset($_GET['offerid'])){
				unset($wp_query->posts);
				unset($wp_query->post);
				unset($wp_query->comments); 
		    	unset($wp_query->comment);
				$wp_query->post_count = 1;
				$wp_query->comment_count = 0;
				$wp_query->current_comment = -1;
				$wp_query->found_posts = 1;
				$wp_query->max_num_pages = 1;
				$wp_query->max_num_comment_pages = 0;
				$wp_query->is_single = 0 ;
				$wp_query->is_preview = 0 ;
				$wp_query->is_page = 1;
				$wp_query->is_archive = 0; 
				$wp_query->is_date = 0; 
				$wp_query->is_year = 0; 
				$wp_query->is_month = 0; 
				$wp_query->is_day = 0; 
				$wp_query->is_time = 0; 
				$wp_query->is_author = 0; 
				$wp_query->is_category = 0; 
				$wp_query->is_tag = 0; 
				$wp_query->is_tax = 0; 
				$wp_query->is_search = 0; 
				$wp_query->is_feed = 0; 
				$wp_query->is_comment_feed = 0; 
				$wp_query->is_trackback = 0; 
				$wp_query->is_home = 0;
				$wp_query->is_404 = 0; 
				$wp_query->is_comments_popup = 0; 
				$wp_query->is_admin = 0; 
				$wp_query->is_attachment = 0; 
				$wp_query->is_singular = 0; 
				$wp_query->is_robots = 0; 
				$wp_query->is_posts_page = 0; 
				$wp_query->is_paged = 0; 
				$postsarray = array(
					'ID' => 0,
					'post_author' =>  1,
					'post_date' =>  date("Y-m-d h:i:s", time()),
					'post_date_gmt' =>  date("Y-m-d h:i:s", time()),
					'post_content' => "Thank you. The item has been added to yout cart. To checkout, <a href=\"http://www.amazon.com/gp/cart/view.html/\">go here</a>.",
					'post_title' =>  "The Product has been added to your Cart.",
					'post_excerpt' =>  "test excerpt",
					'post_status' =>  "publish",
					'comment_status' =>  "closed",
					'ping_status' =>  "closed",
					'post_name' =>  "don-title-test",
					'post_modified' =>  date("Y-m-d h:i:s", time()),
					'post_modified_gmt' =>  date("Y-m-d h:i:s", time()),
					'post_content_filtered' => "test Don", 
					'post_parent' =>  0,
					'guid' =>  get_bloginfo('url'),
					'menu_order' =>  0,
					'post_type' =>  "page",
					'comment_count' =>  0
				);
			$wp_query->posts =	array((object)$postsarray);
			add_filter('edit_post_link', create_function( '$a', 'return "";' ), 10);
			add_filter('the_title', create_function( '$a', 'return "The Product has been added to your Cart.";' ), 10);
			add_filter('the_permalink', create_function( '$a', 'return "'.get_bloginfo('url').'";' ), 10);		
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
			$base_url = get_bloginfo('url').'/'.PLUGINDIR.'/amazon-product-in-a-post-plugin/images/noimage.jpg';
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
	  echo '<link rel="stylesheet" href="'.get_bloginfo('url').'/'.PLUGINDIR.'/amazon-product-in-a-post-plugin/css/amazon-product-in-a-post-styles-icons.css" type="text/css" media="screen" />'."\n";
	}
	function aws_prodinpost_addhead(){
		global $aws_plugin_version;
		$amazonStylesToUseMine = get_option("apipp_product_styles_mine"); //is box checked?
		echo '<'.'!-- Amazon Product In a Post Plugin Styles & Scripts - Version '.$aws_plugin_version.' -->'."\n";
		if($amazonStylesToUseMine=='true'){ //use there styles
			//echo '<link rel="stylesheet" href="'.get_bloginfo('url').'/'.PLUGINDIR.'/amazon-product-in-a-post-plugin/inc/amazon-product-in-a-post-styles-product.php?appip-style=custom" type="text/css" media="screen" />'."\n";
			echo '<link rel="stylesheet" href="'.get_bloginfo('url').'/index.php?appip_style=custom" type="text/css" media="screen" />'."\n";
		}else{ //use default styles
			//echo '<link rel="stylesheet" href="'.get_bloginfo('url').'/'.PLUGINDIR.'/amazon-product-in-a-post-plugin/inc/amazon-product-in-a-post-styles-product.php" type="text/css" media="screen" />'."\n";
			echo '<link rel="stylesheet" href="'.get_bloginfo('url').'/index.php?appip_style=default" type="text/css" media="screen" />'."\n";
		}
		echo '<link rel="stylesheet" href="'.get_bloginfo('url').'/'.PLUGINDIR.'/amazon-product-in-a-post-plugin/css/amazon-lightbox.css" type="text/css" media="screen" />'."\n";
		echo '<'.'!-- End Amazon Product In a Post Plugin Styles & Scripts-->'."\n";
	}
	function add_appip_jquery(){
		wp_register_script('appip-amazonlightbox', WP_PLUGIN_URL . '/amazon-product-in-a-post-plugin/js/amazon-lightbox.js');
		wp_enqueue_script('jquery'); 
		wp_enqueue_script('appip-amazonlightbox'); 
	}
?>