<?php 
// Functions
	//Single Product API Call - Returns One Product Data
if(!function_exists('getSingleAmazonProduct')){
	function getSingleAmazonProduct($asin='',$extratext='',$extrabutton=0,$manual_array = array(),$desc=0){
		global $public_key; 
		global $private_key; 
		global $aws_partner_id;
		global $aws_partner_locale;
		global $amazonhiddenmsg;
		global $amazonerrormsg;
		global $apippopennewwindow;
		global $apippnewwindowhtml;
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
		global $encodemode;
		global $post;
		global $validEncModes;
		
		if($aws_partner_locale==''){
			$aws_partner_locale='com';
		}
		
		if ($asin!=''){
			// Main Amazon API Call
			$ASIN 					= $asin; //valid ASIN or ASINs 
			if(is_array($ASIN)){
				$ASIN = implode(',',$ASIN);
			}
			$errors 				= '';
			//$appip_responsegroup 	= "ItemAttributes,Images,VariationImages,Offers,Reviews,Medium";
			$appip_responsegroup 	= "Large";
			$appip_operation 		= "ItemLookup";
			$appip_idtype	 		= "ASIN";
			$manual_locale 			= isset($manual_array['locale']) && $manual_array['locale']!='' ? $manual_array['locale'] : $aws_partner_locale ;
			$manual_public_key 		= isset($manual_array['public_key'])&& $manual_array['public_key'] !='' ? $manual_array['public_key'] : $public_key ;
			$manual_private_key		= isset($manual_array['private_key'])&& $manual_array['private_key'] !='' ? $manual_array['private_key'] : $private_key ;
			$manual_partner_id		= isset($manual_array['partner_id']) && $manual_array['partner_id'] !='' ? $manual_array['partner_id'] : $aws_partner_id ;
			$description			= isset($manual_array['desc'])? $manual_array['desc'] : 1 ;
			$show_list				= isset($manual_array['listprice'])? $manual_array['listprice'] : 1 ;
			$show_features			= isset($manual_array['features'])? $manual_array['features'] : 0 ;
			$show_gallery			= isset($manual_array['gallery'])? $manual_array['gallery'] : 0 ;
			$pxml 					= aws_signed_request($manual_locale, array("Operation" => $appip_operation,"ItemId" => $ASIN,"ResponseGroup" => $appip_responsegroup,"IdType" => $appip_idtype,"AssociateTag" => $manual_partner_id ), $manual_public_key, $manual_private_key);
			
			if(!is_array($pxml)){
				$pxml2	= $pxml;
				$pxml 	= array();
				$pxml["ItemLookupErrorResponse"]["Errors"]["Code"] = 'ERROR!';
				$pxml["ItemLookupErrorResponse"]["Errors"]["Message"] = $pxml2;
			}else{
				$resultarr = FormatASINResult($pxml);
				//print_r($resultarr);
				$returnval = '';
				$encmode_temp = mb_detect_encoding( "aeiou",mb_detect_order());
				
				if(is_array($resultarr)):
					if(count($resultarr) >=1){
						$thedivider = '<div class="appip-multi-divider"><!--appip divider--></div>';
					}
					foreach($resultarr as $result):
						if($result['NoData'] == '1'):
							$returnval .=  $result['Error'];
							if($extratext != ''):
								$returnval .= $extratext;
							endif;
						else:
							$returnval .= '	<br /><table cellpadding="0" class="amazon-product-table">'."\n";
							$returnval .= '		<tr>'."\n";
							$returnval .= '			<td valign="top">'."\n";
							$returnval .= '				<div class="amazon-image-wrapper">'."\n";
							$returnval .= '					<a href="' . $result['URL'] . '" '. $apippnewwindowhtml .'>' . awsImageGrabber($result['MediumImage'],'amazon-image') . '</a><br />'."\n";
							if($result['LargeImage']!=''){
							$returnval .= '					<a rel="appiplightbox-'.$result['ASIN'].'" href="'.$result['LargeImage'] .'"><span class="amazon-tiny">'.$appip_text_lgimage.'</span></a>'."\n";
							}
							if($result['AddlImages']!='' && $show_gallery == 1){
							$returnval .= ' 					<div class="amazon-additional-images-wrapper"><span class="amazon-additional-images-text">Additional Images:</span>'.$result['AddlImages'].'</div>';
							}	
							$returnval .= '				</div>'."\n";
							$returnval .= '				<div class="amazon-buying">'."\n";
							$returnval .= '					<h2 class="amazon-asin-title"><a href="' . $result['URL'] . '" '. $apippnewwindowhtml .'><span class="asin-title">'.mb_convert_encoding($result["Title"], $encodemode, $encmode_temp).'</span></a></h2>'."\n";
							if($result["Department"]=='Video Games' || $result["ProductGroup"]=='Video Games'){
								$returnval .= '					<span class="amazon-manufacturer"><span class="appip-label">'.$appip_text_manufacturer.':</span> '.mb_convert_encoding($result["Manufacturer"], $encodemode, $encmode_temp).'</span><br />'."\n";
								$returnval .= '					<span class="amazon-ESRB"><span class="appip-label">'.$appip_text_ESRBAgeRating.':</span> '.mb_convert_encoding($result["ESRBAgeRating"], $encodemode, $encmode_temp).'</span><br />'."\n";
								$returnval .= '					<span class="amazon-platform"><span class="appip-label">'.$appip_text_platform.':</span> '.mb_convert_encoding($result["Platform"], $encodemode, $encmode_temp).'</span><br />'."\n";
								$returnval .= '					<span class="amazon-system"><span class="appip-label">'.$appip_text_genre.':</span> '.mb_convert_encoding($result["Genre"], $encodemode, $encmode_temp).'</span><br />'."\n";
								if($show_features != 0){
									$returnval .= '					<span class="amazon-feature"><span class="appip-label">'.$appip_text_feature.':</span> '.mb_convert_encoding($result["Feature"], $encodemode, $encmode_temp).'</span><br />'."\n";
								}							
							}elseif($show_features != 0 && $result["Feature"] != ''){
								$returnval .= '					<span class="amazon-feature"><span class="appip-label">'.$appip_text_feature.':</span> '.mb_convert_encoding($result["Feature"], $encodemode, $encmode_temp).'</span><br />'."\n";
							}
							if(trim($result["Author"])!=''){
							$returnval .= '					<span class="amazon-author">'.$appip_text_author.':</span> '.mb_convert_encoding($result["Author"], $encodemode, $encmode_temp).'</span><br />'."\n";
							}
							if(trim($result["Director"])!=''){
							$returnval .= '					<span class="amazon-director-label">'.$appip_text_director.': </span><span class="amazon-director">'.mb_convert_encoding($result["Director"], $encodemode, $encmode_temp).'</span><br />'."\n";
							}
							if(trim($result["Actor"])!=''){
							$returnval .= '					<span class="amazon-starring-label">'.$appip_text_starring.': </span><span class="amazon-starring">'.mb_convert_encoding($result["Actor"], $encodemode, $encmode_temp).'</span><br />'."\n";
							}
							if(trim($result["AudienceRating"])!=''){
							$returnval .= '					<span class="amazon-rating-label">Rating: </span><span class="amazon-rating">'.$result["AudienceRating"].'</span><br />'."\n";
							}
							$returnval .= '				</div>'."\n";
							if(trim($result["ItemDesc"]) != '' && $description == 1){
							//$returnval .= '				<div><a style="" '. $apippnewwindowhtml .' href="' . $result['URL'] .'"><img src="'.plugins_url('/images/'.$buyamzonbutton,dirname(__FILE__)).'" border="0" style="border:0 none !important;margin:0px !important;background:transparent !important;" /></a></div>'."\n";
							$returnval .= '				<hr noshade="noshade" size="1" />'."\n";
							$returnval .= '				<div class="amazon-description"><span class="appip-label">Description:</span> '.mb_convert_encoding($result["ItemDesc"], $encodemode, $encmode_temp).'</div>'."\n";
							}else{
							$returnval .= '				<hr noshade="noshade" size="1" />'."\n";
							}
							$returnval .= '				<div align="left">'."\n";
							$returnval .= '					<table class="amazon-product-price" cellpadding="0">'."\n";
							if($extratext!=''){
							$returnval .= '						<tr>'."\n";
							$returnval .= '							<td class="amazon-post-text" colspan="2">'.$extratext.'</td>'."\n";
							$returnval .= '						</tr>'."\n";
							}
							if($show_list == 1){
								If($result["PriceHidden"]== '1' ){
									$returnval .= '						<tr>'."\n";
									$returnval .= '							<td class="amazon-list-price-label">'.$appip_text_listprice.':</td>'."\n";
									$returnval .= '							<td class="amazon-list-price-label">'.$amazonhiddenmsg.'</td>'."\n";
									$returnval .= '						</tr>'."\n"; 
								}elseif($result["ListPrice"]!= '0'){
									$returnval .= '						<tr>'."\n";
									$returnval .= '							<td class="amazon-list-price-label">'.$appip_text_listprice.':</td>'."\n";
									$returnval .= '							<td class="amazon-list-price">'.  mb_convert_encoding($result["ListPrice"], $encodemode, $encmode_temp) .'</td>'."\n";
									$returnval .= '						</tr>'."\n";
								}
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
									$returnval .= '							<td class="amazon-new">'. mb_convert_encoding($newPrice , $encodemode, $encmode_temp).' <span class="instock">'.$appip_text_instock.'</span></td>'."\n";
								}else{
									$returnval .= '							<td class="amazon-new">'. mb_convert_encoding($newPrice , $encodemode, $encmode_temp).' <span class="outofstock">'.$appip_text_outofstock.'</span></td>'."\n";
								}
								$returnval .= '						</tr>'."\n";
							}
							if(isset($result["LowestUsedPrice"])){
								$returnval .= '						<tr>'."\n";
								$returnval .= '							<td class="amazon-used-label">'.$appip_text_usedfrom.':</td>'."\n";
								if($result["TotalUsed"] > 0){
									$returnval .= '						<td class="amazon-used">'. mb_convert_encoding($result["LowestUsedPrice"], $encodemode, $encmode_temp) .' <span class="instock">'.$appip_text_instock.'</span></td>'."\n";
								}else{
									if($result["LowestNewPrice"] == '' || $result["LowestNewPrice"] =="0"){
										$usedfix = '';
									}else{
										$usedfix = mb_convert_encoding($result["LowestNewPrice"], $encodemode, $encmode_temp);
									}
									$returnval .= '						<td class="amazon-new">'. $usedfix . ' <span class="outofstock">'.$appip_text_outofstock.'</span></td>'."\n";
								}
								$returnval .= '						</tr>'."\n";
							}
							$returnval .= '						<tr>'."\n";
							$returnval .= '							<td valign="top" colspan="2">'."\n";
							$returnval .= '								<div class="amazon-dates">'."\n";
							if($result["ReleaseDate"] != ''){	
								$nowdatestt = strtotime(date("Y-m-d",time()));
								$nowminustt = strtotime("-180 days");
								$reldatestt = strtotime($result["ReleaseDate"]);
								if($reldatestt > $nowdatestt){
							$returnval .= '									<span class="amazon-preorder"><br />'.$appip_text_releasedon.' '.date("F j, Y", strtotime($result["ReleaseDate"])).'.</span>'."\n";
								}elseif($reldatestt >= $nowminustt){
							$returnval .= '									<span class="amazon-release-date">'.$appip_text_reldate.' '.date("F j, Y", strtotime($result["ReleaseDate"])).'.</span>'."\n";
								}
							}
							$returnval .= '									<br /><div><a style="display:block;margin-top:8px;margin-bottom:5px;width:165px;" '. $apippnewwindowhtml .' href="' . $result["URL"] .'"><img src="'.plugins_url('/images/'.$buyamzonbutton,dirname(__FILE__)).'" border="0" style="border:0 none !important;margin:0px !important;background:transparent !important;" /></a></div>'."\n";
							if($extrabutton==1 && $aws_partner_locale!='.com'){
							//$returnval .= '									<br /><div><a style="display:block;margin-top:8px;margin-bottom:5px;width:165px;" '. $apippnewwindowhtml .' href="' . $result["URL"] .'"><img src="'.plugins_url('/images/buyamzon-button.png',dirname(__FILE__)).'" border="0" style="border:0 none !important;margin:0px !important;background:transparent !important;"/></a></div>'."\n";
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
							$returnval .= $thedivider;
						endif;
					endforeach;
				endif;
				return $returnval;
			}
		}
	}
}
	
if(!function_exists('awsImageGrabber')){
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
			$base_url = plugins_url('/images/noimage.jpg',dirname(__FILE__));
	    	return $base_url0.$base_url.$base_url1;
		}
	}
}
	
if(!function_exists('awsImageGrabberURL')){
	//Amazon Product Image from ASIN function - Returns URL only
	function awsImageGrabberURL($asin, $size="M"){
	    $base_url = 'http://images.amazon.com/images/P/'.$asin.'.03.';
	    if (strcasecmp($size, 'S') == 0){
	      $base_url .= 'THUMBZZZ';
	    }else if (strcasecmp($size, 'L') == 0){
	      $base_url .= 'LZZZZZZZ';
	    }else{
	      $base_url .= 'MZZZZZZZ';
	    }
	    $base_url .= '.jpg';
	    return $base_url;
	}
}
	
if(!function_exists('aws_prodinpost_filter_excerpt')){
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
			//replace short tag here. Handle a bit different than content so they get stripped if they don't want to hook excerpt we don't want to show the [AMAZON-PRODUCT=XXXXXXXX] tag in the excerpt text!
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
			  			$theproduct = getSingleAmazonProduct($singleProdPostAWS,$text);
			  		}elseif($AWSPostLoc=='3'){
			  			$theproduct = $text.'<br />'.getSingleAmazonProduct($singleProdPostAWS,'');
			  		}else{
			  			$theproduct = getSingleAmazonProduct($singleProdPostAWS,'').'<br />'.$text;
			  		}
			  		return $theproduct;
			  	} else {
			  		return $text;
			  	}
			}else{
			  	if($singleProdPostAWS!='' && $ActiveProdPostAWS!=''){
			  		if($AWSPostLoc=='2'){
			  			$theproduct = getSingleAmazonProduct($singleProdPostAWS,$text);
			  		}elseif($AWSPostLoc=='3'){
			  			$theproduct = $text.'<br />'.getSingleAmazonProduct($singleProdPostAWS,'');
			  		}else{
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
}
		
if(!function_exists('aws_prodinpost_filter_content')){
	function aws_prodinpost_filter_content($text){
		global $post,$apipphookcontent;
		$ActiveProdPostAWS = get_post_meta($post->ID,'amazon-product-isactive',true);
		$singleProdPostAWS = get_post_meta($post->ID,'amazon-product-single-asin',true);
		$AWSPostLoc = get_post_meta($post->ID,'amazon-product-content-location',true);
		$apippContentHookOverride = get_post_meta($post->ID,'amazon-product-content-hook-override',true);
		$apippShowSingularonly = get_post_meta($post->ID,'amazon-product-singular-only',true);
		if ( stristr( $text, '[AMAZONPRODUCT' )) {
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
								$ASINis = str_replace(' ','',$ASINis);
								$ASINisArray = explode(',',$ASINis);
								$product_text	.= getSingleAmazonProduct($ASINis,'');
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
}

	function aws_prodinpost_addadminhead(){
	  echo '<link rel="stylesheet" href="'.plugins_url('/css/amazon-product-in-a-post-styles-icons.css',dirname(__FILE__)).'" type="text/css" media="screen" />'."\n";
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
		echo '<link rel="stylesheet" href="'.plugins_url('/css/amazon-lightbox.css',dirname(__FILE__)).'" type="text/css" media="screen" />'."\n";
		echo '<'.'!-- End Amazon Product In a Post Plugin Styles & Scripts-->'."\n";
	}
	
	function add_appip_jquery(){
		wp_register_script('appip-amazonlightbox', plugins_url('/js/amazon-lightbox.js',dirname(__FILE__)));
		wp_enqueue_script('jquery'); 
		wp_enqueue_script('appip-amazonlightbox'); 
	}
?>