<?php
/*
Plugin Name: Amazon Product In a Post Plugin
Plugin URI: http://fischercreativemedia.com/wordpress-plugins/amazon-affiliate-product-in-a-post/
Description: Quickly add a formatted Amazon Product (image, pricing and buy button, etc.) to a post by using just the Amazon product ASIN (ISBN-10). Great for writing product reviews or descriptions to help monetize your posts and add content that is relevant to your site. You can also customize the styles for the product data. Remember to add your Amazon Affiliate ID on the <a href="admin.php?page=apipp_plugin_admin">options</a> page or all sales credit will go to the plugin creator by default.
Author: Don Fischer
Author URI: http://www.fischercreativemedia.com/
Version: 1.4

Version info:
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
// Includes
	require_once("sha256.inc.php"); //required for php4
	require_once("aws_signed_request.php"); //major workhorse for plugin
	require_once("amazon-product-in-a-post-tools.php"); //edit box for plugin
	require_once("amazon-product-in-a-post-options.php"); //admin options for plugin

// Variables
	global $public_key;
	global $private_key; 
	global $aws_partner_id;
	global $aws_eatra_pages;
	global $aws_plugin_version;
	global $aws_partner_locale;
	global $thedefaultapippstyle;
	
	session_start();	
    if(!isset($_SESSION['Amazon-PIPP-Cart-HMAC'])) $_SESSION['Amazon-PIPP-Cart-HMAC'] = '';
    if(!isset($_SESSION['Amazon-PIPP-Cart-Encoded-HMAC'])) $_SESSION['Amazon-PIPP-Cart-Encoded-HMAC']='';
    if(!isset($_SESSION['Amazon-PIPP-Cart-ID'])) $_SESSION['Amazon-PIPP-Cart-ID']='';
    //echo session_id();
	$public_key 	= get_option('awsplugin_amazon_publickey'); //Developer Public AWS Key
	$private_key 	= get_option('awsplugin_amazon_secretkey'); //Developer Secret AWS Key
	$aws_partner_id	= get_option('apipp_amazon_associateid'); //Amazon Partner ID 
	$aws_partner_locale	= get_option('apipp_amazon_locale'); //Amazon Locale
	$awsPageRequest = 1;
	$aws_plugin_version = "1.0";

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
	
	if(isset($_GET['awspage'])){
		if(is_numeric($_GET['awspage'])){
			$awspagequery = (int)$wpdb->escape($_GET['awspage']);
		}
	}

	if($awspagequery>1){
		$awsPageRequest = $awspagequery;
	}
	$aws_eatra_pages = '"ItemPage"=>"'.$awspagequery.'",';
	
	$thereapippstyles = get_option("apipp_product_styles_default"); 
	if(trim(get_option("apipp_product_styles")) == ''){
		update_option("apipp_product_styles",$thedefaultapippstyle);
	}


// Filters & Hooks
	add_filter('the_content', 'aws_prodinpost_filter', 10);
	add_filter('the_excerpt', 'aws_prodinpost_filter', 10);
	add_action('wp_head','aws_prodinpost_addhead',10);
	//add_action('wp','aws_prodinpost_cartsetup', 1, 2); //Future Item
	add_action('admin_head','aws_prodinpost_addadminhead',10);

// Functions
	//Single Product API Call - Returns One Product Data
	function getSingleAmazonProduct($asin='',$extratext=''){
		global $public_key, $private_key, $aws_partner_id,$aws_partner_locale;
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
				$returnval .= '					<a href="' . $result["URL"] . '">' . awsImageGrabber($result['MediumImage'],'amazon-image') . '</a><br />'."\n";
				if($result['LargeImage']!=''){
				$returnval .= '					<a target="amazon-image" href="javascript: void(0)" onclick="artwindow=window.open(\'' .$result['LargeImage'] .'\',\'art\',\'directories=no, location=no, menubar=no, resizable=no, scrollbars=no, status=no, toolbar=no, width=400,height=525\');artwindow.focus();return false;"><span class="amazon-tiny">See larger image</span></a>'."\n";
				}
				$returnval .= '				</div>'."\n";
				$returnval .= '				<div class="amazon-buying">'."\n";
				$returnval .= '					<h2 class="amazon-asin-title"><a href="' . $result["URL"] . '"><span class="asin-title">'.$result["Title"].'</span></a></h2>'."\n";
				if(isset($result["Author"])){
				$returnval .= '					<span class="amazon-author">By '.$result["Author"].'</span><br />'."\n";
				}
				if(isset($result["Director"])){
				$returnval .= '					<span class="amazon-director-label">Directed by: </span><span class="amazon-director">'.$result["Director"].'</span><br />'."\n";
				}
				if(isset($result["Actors"])){
				$returnval .= '					<span class="amazon-starring-label">Starring: </span><span class="amazon-starring">'.$result["Actors"].'</span><br />'."\n";
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
				if(isset($result["ListPrice"]) && $result["ListPrice"]!=' '){
					$returnval .= '						<tr>'."\n";
					$returnval .= '							<td class="amazon-list-price-label">List Price:</td>'."\n";
					$returnval .= '							<td class="amazon-list-price">'. $result["ListPrice"] .'</td>'."\n";
					$returnval .= '						</tr>'."\n";
					if(isset($result["LowestNewPrice"])){
						$returnval .= '						<tr>'."\n";
						$returnval .= '							<td class="amazon-new-label">New From:</td>'."\n";
						$returnval .= '							<td class="amazon-new">'. $result["LowestNewPrice"] .'</td>'."\n";
						$returnval .= '						</tr>'."\n";
					}
					if(isset($result["LowestUsedPrice"])){
						$returnval .= '						<tr>'."\n";
						$returnval .= '							<td class="amazon-used-label">Used From:</td>'."\n";
						$returnval .= '							<td class="amazon-used">'. $result["LowestUsedPrice"] .'</td>'."\n";
						$returnval .= '						</tr>'."\n";
					}
					$returnval .= '						<tr>'."\n";
					$returnval .= '							<td valign="top" width="100%" colspan="2">'."\n";
					$returnval .= '								<div class="amazon-dates">'."\n";
					if(isset($result["ReleaseDate"])){
						if(strtotime($result["ReleaseDate"]) > strtotime(date("Y-m-d",time()))){
					$returnval .= '									<span class="amazon-preorder"><br />This title will be released on '.date("F j, Y", strtotime($result["ReleaseDate"])).'.</span>'."\n";
						}else{
					$returnval .= '									<span class="amazon-release-date">Released '.date("F j, Y", strtotime($result["ReleaseDate"])).'.</span>'."\n";
						}
					}
					$returnval .= '									<br /><div><a style="display:block;margin-top:8px;width:158px;height:26px;" href="' . $result["URL"] .'"><img src="'.get_bloginfo('url').'/'.PLUGINDIR.'/amazon-product-in-a-post-plugin/images/buyamzon-button.png" border="0" style="border:0 none !important;margin:0px !important;background:transparent !important;"/></a></div>'."\n";
					$returnval .= '								</div>'."\n";
					$returnval .= '							</td>'."\n";
					$returnval .= '						</tr>'."\n";
				}else{
					$returnval .= '						<tr>'."\n";
					$returnval .= '							<td class="amazon-price-save-label" colspan="2">This items is not available in your locale.</td>'."\n";
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

	    $base_url0 = '<'.'img src="';
	    $base_url = $imgurl;
	    $base_url1 = '"';
	    $base_url1 = $base_url1.' class="amazon-image '.$class.'"';
	    $base_url1 = $base_url1.' rel="image-'.$asin.'" />';
		
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
	
	  function aws_prodinpost_filter($text){
	  	global $post;
	  	$ActiveProdPostAWS = get_post_meta($post->ID,'amazon-product-isactive',true);
	  	$singleProdPostAWS = get_post_meta($post->ID,'amazon-product-single-asin',true);
	  	$AWSPostLoc = get_post_meta($post->ID,'amazon-product-content-location',true);
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
	function aws_prodinpost_addadminhead(){
	  echo '<link rel="stylesheet" href="'.get_bloginfo('url').'/'.PLUGINDIR.'/amazon-product-in-a-post-plugin/amazon-product-in-a-post-styles-icons.css" type="text/css" media="screen" />'."\n";
	}
	function aws_prodinpost_addhead(){
		global $aws_plugin_version;
		$amazonStylesToUseMine = get_option("apipp_product_styles_mine"); //is box checked?
		echo '<'.'!-- Amazon Product In a Post Plugin Styles - Version '.$aws_plugin_version.' -->'."\n";
		if($amazonStylesToUseMine=='true'){ //use there styles
			echo '<link rel="stylesheet" href="'.get_bloginfo('url').'/'.PLUGINDIR.'/amazon-product-in-a-post-plugin/amazon-product-in-a-post-styles-product.php?style=custom" type="text/css" media="screen" />'."\n";
		}else{ //use default styles
			echo '<link rel="stylesheet" href="'.get_bloginfo('url').'/'.PLUGINDIR.'/amazon-product-in-a-post-plugin/amazon-product-in-a-post-styles-product.php" type="text/css" media="screen" />'."\n";
		}
		echo '<'.'!-- End Amazon Product In a Post Plugin Styles -->'."\n";
	}
?>