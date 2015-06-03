<?php 
// Functions
function maybe_convert_encoding($text){
	$encmode_temp 	= mb_detect_encoding( "aeioué",mb_detect_order());
	$encodemode 	= get_bloginfo( 'charset' );
	if($encmode_temp!=$encodemode){
		return mb_convert_encoding($text, $encodemode, $encmode_temp)	;
	}
	return $text;
}

function appip_product_array_processed_add_variants($resultarr,$newWin=''){
	$resultArrNew = array();
	foreach($resultarr as $key => $val){
		if(isset($val['Offers_TotalOffers']) && $val['Offers_TotalOffers'] == '0'){
			$varLowPrice 	= isset($val['VariationSummary_LowestSalePrice_FormattedPrice']) ? $val['VariationSummary_LowestSalePrice_FormattedPrice'] : (isset($val['VariationSummary_LowestPrice_FormattedPrice']) ? $val['VariationSummary_LowestPrice_FormattedPrice'] : '');
			$varHiPrice		= isset($val['VariationSummary_HighestPrice_FormattedPrice']) ? $val['VariationSummary_HighestPrice_FormattedPrice'] : '';
			$varTotalNum 	= isset($val['Variations_TotalVariations']) ? (int)$val['Variations_TotalVariations'] : 0;
			$hasMainList 	= isset($val['ItemAttributes_ListPrice_FormattedPrice']) ? 1 : 0;
			if($hasMainList == 1){
				$val['ListPrice'] = isset($val['ItemAttributes_ListPrice_FormattedPrice']) ? $val['ItemAttributes_ListPrice_FormattedPrice'] : '';
			}
			if( $varTotalNum > 0 ){
				if($varTotalNum == 1){
					//Set Main Image as first variant Image if product does not have Image
					$val['MediumImage'] = isset($val['LargeImage_URL']) && $val['LargeImage_URL'] != '' ? $val['LargeImage_URL'] : (isset($val['Variations_Item_LargeImage_URL']) ? $val['Variations_Item_LargeImage_URL'] : '');
					$val['LargeImage']	= isset($val['LargeImage_URL']) && $val['LargeImage_URL'] != '' ? $val['LargeImage_URL'] : (isset($val['Variations_Item_LargeImage_URL']) ? $val['Variations_Item_LargeImage_URL'] : ''); ;
				}else{
					//Set Main Image as first variant Image if product does not have Image
					$val['MediumImage'] = isset($val['LargeImage_URL']) && $val['LargeImage_URL'] != '' ? $val['LargeImage_URL'] : (isset($val['Variations_Item_0_LargeImage_URL']) ? $val['Variations_Item_0_LargeImage_URL'] : '');
					$val['LargeImage']	= isset($val['LargeImage_URL']) && $val['LargeImage_URL'] != '' ? $val['LargeImage_URL'] : (isset($val['Variations_Item_0_LargeImage_URL']) ? $val['Variations_Item_0_LargeImage_URL'] : ''); ;
				}
				//Set New price for "from X to Y"
				if($varLowPrice != '' && $varHiPrice != ''){
					$val['LowestNewPrice'] = $varLowPrice.' &ndash; '. $varHiPrice;
				}

				//Set Total New
				$val["TotalNew"] = 1; //needs to be at least one to not show "Out of Stock".
				$val["PriceHidden"] = 0;
				$val["HideStockMsg"] = 1;
				
				//List Varients
				$vartype = isset($val['Variations_VariationDimensions_VariationDimension']) ? $val['Variations_VariationDimensions_VariationDimension'] : '';
				if($vartype != '') {
					$val['VariantHTML'] = '<div class="amazon_variations_wrapper">'.__('Variations:','amazon-product-in-a-post-plugin').' ('.$vartype.'):';
				}else{
					$val['VariantHTML'] = '<div class="amazon_variations_wrapper">'.__('Variations:','amazon-product-in-a-post-plugin').':';
				}
				$target = $newWin == '' ? '' : $newWin ; 
				$ImageSetsArray = array();
				if($varTotalNum == 1){
						$varASIN	= isset($val['Variations_Item_ASIN']) ? $val['Variations_Item_ASIN'] : '';
						if($hasMainList == 0 && isset($val['Variations_Item_ItemAttributes_ListPrice_FormattedPrice'])){
							$val['ListPrice'] = $val['Variations_Item_ItemAttributes_ListPrice_FormattedPrice'];
						}
						//for image sets
						for ($y = 0; $y < 10; $y++){
							if( isset($val['Variations_Item_ImageSets_ImageSet_'.$y.'_LargeImage_URL']) && isset($val['Variations_Item_ImageSets_ImageSet_'.$y.'_SmallImage_URL'])){
								$lgImg 	= $val['Variations_Item_ImageSets_ImageSet_'.$y.'_LargeImage_URL'];
								$swImg	= $val['Variations_Item_ImageSets_ImageSet_'.$y.'_SmallImage_URL'];
								if($lgImg != '' && $swImg !=''){
									$ImageSetsArray[] = '<a rel="appiplightbox-'.$val['ASIN'].'" href="'.$lgImg .'" target="amazonwin"><img src="'.$swImg.'" class="apipp-additional-image" target="amazonwin"/></a>'."\n";
								}
							}else{
								if($y > 9){
									break 1;
								}
							}
						}
						$varT 		= isset($val['Variations_Item_VariationAttributes_VariationAttribute_Value']) ? $val['Variations_Item_VariationAttributes_VariationAttribute_Value'] : '';
						$varC 		= isset($val['Variations_Item_Offers_Offer_OfferAttributes_Condition']) ? $val['Variations_Item_Offers_Offer_OfferAttributes_Condition'] : '' ;
						$varD 		= isset($val['Variations_Item_Offers_Offer_OfferListing_SalePrice_CurrencyCode']) ? get_appipCurrCode($val['Variations_Item_Offers_Offer_OfferListing_SalePrice_CurrencyCode']) : (isset($val['Variations_Item_Offers_Offer_OfferListing_Price_CurrencyCode']) ? get_appipCurrCode($val['Variations_Item_Offers_Offer_OfferListing_Price_CurrencyCode']) : '') ;
						$varP 		= isset($val['Variations_Item_Offers_Offer_OfferListing_SalePrice_FormattedPrice']) ? $val['Variations_Item_Offers_Offer_OfferListing_SalePrice_FormattedPrice'] : (isset($val['Variations_Item_Offers_Offer_OfferListing_Price_FormattedPrice']) ? $val['Variations_Item_Offers_Offer_OfferListing_Price_FormattedPrice'] : '');
						$linkStart 	= $varASIN != '' ? '<a href="'.str_replace($val['ASIN'],$varASIN,$val['URL']).'"'.$target.'>' : '';
						$linkEnd 	= $linkStart != '' ? '</a>' : '';
						$varL 		= $linkStart != '' ? ($linkStart.$varT.$linkEnd) : $varT;
						$photo		= isset($val['Variations_Item_SmallImage_URL']) ? $linkStart.'<img class="amazon-varient-image" src="'.$val['Variations_Item_SmallImage_URL'].'" />'.$linkEnd : '';
						if($varT !='' && $varC !='' && $varP!=''){
							$val['VariantHTML'] .= '<div class="amazon_varients">'.$photo.'<span class="amazon-varient-type-link">'.$varL.'</span> &mdash; <span class="amazon-varient-type-price"><span class="amazon-variant-price-text">'.$varC.' '.__('from','amazon-product-in-a-post-plugin').'</span> '.$varP.$varD.'</span></div>'."\n";
						}
					$val['VariantHTML'] .= '</div>';

					//Make Image Set from the first image for each varient
					if(!empty($ImageSetsArray)){
						$val['AddlImages'] = implode("\n",$ImageSetsArray);
					}
					
				}else{
					for ($x = 0; $x <= ($varTotalNum-1); $x++) {
						$varASIN	= isset($val['Variations_Item_'.$x.'_ASIN']) ? $val['Variations_Item_'.$x.'_ASIN'] : '';
						if($x == 0 && $hasMainList == 0 && isset($val['Variations_Item_'.$x.'_ItemAttributes_ListPrice_FormattedPrice'])){
							$val['ListPrice'] = $val['Variations_Item_'.$x.'_ItemAttributes_ListPrice_FormattedPrice'];
						}
						//for image sets
						for ($y = 0; $y < 10; $y++){
							if( isset($val['Variations_Item_'.$x.'_ImageSets_ImageSet_'.$y.'_LargeImage_URL']) && isset($val['Variations_Item_'.$x.'_ImageSets_ImageSet_'.$y.'_SmallImage_URL'])){
								$lgImg 	= $val['Variations_Item_'.$x.'_ImageSets_ImageSet_'.$y.'_LargeImage_URL'];
								$swImg	= $val['Variations_Item_'.$x.'_ImageSets_ImageSet_'.$y.'_SmallImage_URL'];
								if($lgImg != '' && $swImg !=''){
									$ImageSetsArray[] = '<a rel="appiplightbox-'.$val['ASIN'].'" href="'.$lgImg .'" target="amazonwin"><img src="'.$swImg.'" class="apipp-additional-image"/></a>'."\n";
								}
							}else{
								if($y > 9){
									break 1;
								}
							}
						}
						$varT 		= isset($val['Variations_Item_'.$x.'_VariationAttributes_VariationAttribute_Value']) ? $val['Variations_Item_'.$x.'_VariationAttributes_VariationAttribute_Value'] : '';
						$varC 		= isset($val['Variations_Item_'.$x.'_Offers_Offer_OfferAttributes_Condition']) ? $val['Variations_Item_'.$x.'_Offers_Offer_OfferAttributes_Condition'] : '' ;
						$varD 		= isset($val['Variations_Item_'.$x.'_Offers_Offer_OfferListing_SalePrice_CurrencyCode']) ? get_appipCurrCode($val['Variations_Item_'.$x.'_Offers_Offer_OfferListing_SalePrice_CurrencyCode']) : (isset($val['Variations_Item_'.$x.'_Offers_Offer_OfferListing_Price_CurrencyCode']) ? get_appipCurrCode($val['Variations_Item_'.$x.'_Offers_Offer_OfferListing_Price_CurrencyCode']) : '') ;
						$varP 		= isset($val['Variations_Item_'.$x.'_Offers_Offer_OfferListing_SalePrice_FormattedPrice']) ? $val['Variations_Item_'.$x.'_Offers_Offer_OfferListing_SalePrice_FormattedPrice'] : (isset($val['Variations_Item_'.$x.'_Offers_Offer_OfferListing_Price_FormattedPrice']) ? $val['Variations_Item_'.$x.'_Offers_Offer_OfferListing_Price_FormattedPrice'] : '');
						$linkStart 	= $varASIN != '' ? '<a href="'.str_replace($val['ASIN'],$varASIN,$val['URL']).'"'.$target.'>' : '';
						$linkEnd 	= $linkStart != '' ? '</a>' : '';
						$varL 		= $linkStart != '' ? ($linkStart.$varT.$linkEnd) : $varT;
						$photo		= isset($val['Variations_Item_'.$x.'_SmallImage_URL']) ? $linkStart.'<img class="amazon-varient-image" src="'.$val['Variations_Item_'.$x.'_SmallImage_URL'].'" />'.$linkEnd : '';
						if($varT !='' && $varC !='' && $varP!=''){
							$val['VariantHTML'] .= '<div class="amazon_varients">'.$photo.'<span class="amazon-varient-type-link">'.$varL.'</span> &mdash; <span class="amazon-varient-type-price"><span class="amazon-variant-price-text">'.$varC.' '.__('from','amazon-product-in-a-post-plugin').'</span> '.$varP.$varD.'</span></div>'."\n";
						}
					} 
					$val['VariantHTML'] .= '</div>';
	
					//Make Image Set from the first image for each varient
					if(!empty($ImageSetsArray)){
						$val['AddlImages'] = implode("\n",$ImageSetsArray);
					}
				}
	
			}
			
		}
		$resultArrNew[] = $val;
	}
	return $resultArrNew;
}
add_filter('appip_product_array_processed','appip_product_array_processed_add_variants',10,2);

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
		global $show_format;
		global $appip_templates; 
		global $appipTimestampMsgPrinted;
		$extratext 			= apply_filters('getSingleAmazonProduct_extratext',$extratext);
		$extrabutton		= apply_filters('getSingleAmazonProduct_extrabutton',$extrabutton);
		$manual_array		= apply_filters('getSingleAmazonProduct_manual_array',$manual_array);
		$manual_public_key 	= isset($manual_array['public_key'])	&& $manual_array['public_key'] !='' 	? $manual_array['public_key'] 	: $public_key ;
		$manual_private_key	= isset($manual_array['private_key'])	&& $manual_array['private_key'] !='' 	? $manual_array['private_key'] 	: $private_key ;
		$manual_locale 		= isset($manual_array['locale']) 		&& $manual_array['locale']!='' 			? $manual_array['locale'] 		: $aws_partner_locale ;
		$manual_partner_id	= isset($manual_array['partner_id']) 	&& $manual_array['partner_id'] !='' 	? $manual_array['partner_id'] 	: $aws_partner_id ;
		$manual_new_window	= isset($manual_array['newwindow'])		&& (int) $manual_array['newwindow']!= 0 ? 1 : $apippopennewwindow;
		$apippopennewwindow = $manual_new_window;
		$apippnewwindowhtml	= (int) $manual_new_window == 1 ? ' target="amazonwin" ' : $apippnewwindowhtml;
		if($manual_partner_id == ''){$manual_partner_id = 'wolvid-20';} //have to give it some user id or it will fail.
		if ($asin!='' && $manual_public_key!='' && $manual_private_key!=''){
			// Main Amazon API Call
			$ASIN 					= apply_filters('getSingleAmazonProduct_asin',(is_array($asin) ? implode(',',$asin) : $asin)); //valid ASIN or ASINs 
			$errors 				= '';
			$appip_responsegroup 	= apply_filters('getSingleAmazonProduct_response_group',"Large,Reviews,Offers,Variations");
			$appip_operation 		= apply_filters('getSingleAmazonProduct_operation',"ItemLookup");
			$appip_idtype	 		= apply_filters('getSingleAmazonProduct_type',"ASIN");
			$description			= isset($manual_array['desc'])? $manual_array['desc'] : 0 ; //set to no by default - too many complaints!
			$show_list				= isset($manual_array['listprice'])? $manual_array['listprice'] : 0 ;
			$show_used				= isset($manual_array['used_price'])? $manual_array['used_price'] : 0 ;
			$show_used_price		= $show_used;
			$show_saved_amt			= isset($manual_array['saved_amt'])? $manual_array['saved_amt'] : 0 ;
			$show_format			= isset($manual_array['showformat'])? $manual_array['showformat'] : 1 ;
			$show_features			= isset($manual_array['features'])? $manual_array['features'] : 0 ;
			$show_gallery			= isset($manual_array['gallery'])? $manual_array['gallery'] : 0 ;
			$replace_title			= isset($manual_array['replace_title']) && $manual_array['replace_title']!='' ? $manual_array['replace_title'] : '' ;
			$template				= isset($manual_array['template']) && $manual_array['template']!='' ? $manual_array['template'] : 'default' ;
			$show_timestamp			= isset($manual_array['timestamp'])? $manual_array['timestamp'] : 0 ;
			$title_wrap				= isset($manual_array['title_wrap'])? $manual_array['title_wrap'] : 0 ;
			$array_for_templates	= array(  //these are shortcode variables to pass to template functions
				'apippnewwindowhtml'		=> $apippnewwindowhtml,
				'amazonhiddenmsg'			=> $amazonhiddenmsg,
				'amazonerrormsg'			=> $amazonerrormsg,
				'apippopennewwindow'		=> $apippopennewwindow,
				'appip_text_lgimage'		=> $appip_text_lgimage,
				'appip_text_listprice'		=> $appip_text_listprice,
				'appip_text_newfrom'		=> $appip_text_newfrom,
				'appip_text_usedfrom'		=> $appip_text_usedfrom,
				'appip_text_instock'		=> $appip_text_instock,
				'appip_text_outofstock'		=> $appip_text_outofstock,
				'appip_text_author'			=> $appip_text_author,
				'appip_text_starring'		=> $appip_text_starring,
				'appip_text_director'		=> $appip_text_director,
				'appip_text_reldate'		=> $appip_text_reldate,
				'appip_text_preorder'		=> $appip_text_preorder,
				'appip_text_releasedon'		=> $appip_text_releasedon,
				'appip_text_notavalarea'	=> $appip_text_notavalarea,
				'appip_text_manufacturer'	=> $appip_text_manufacturer,
				'appip_text_ESRBAgeRating'	=> $appip_text_ESRBAgeRating,
				'appip_text_feature'		=> $appip_text_feature,
				'appip_text_platform'		=> $appip_text_platform,
				'appip_text_genre'			=> $appip_text_genre,
				'buyamzonbutton'			=> $buyamzonbutton,
				'addestrabuybutton'			=> $addestrabuybutton,
				'description'				=> $description,
				'encodemode'				=> $encodemode,
				'replace_title'				=> $replace_title,
				'show_list'					=> $show_list,
				'show_format'				=> $show_format,
				'show_features'				=> $show_features,
				'show_used_price'			=> $show_used_price,
				'show_saved_amt'			=> $show_saved_amt,
				'show_timestamp'			=> $show_timestamp,
				'show_gallery'				=> $show_gallery,
				'template'					=> $template,
				'title_wrap'				=> $title_wrap,
				'validEncModes'				=> $validEncModes,
			);
			$set_array				= array("Operation" => $appip_operation,"ItemId" => $ASIN,"ResponseGroup" => $appip_responsegroup,"IdType" => $appip_idtype,"AssociateTag" => $manual_partner_id );
			$api_request_array		= array('locale'=>$manual_locale,'public_key'=>$manual_public_key,'private_key'=>$manual_private_key,'api_request_array'=>$set_array);
			$request_array			= apply_filters('appip_pre_request_array',$api_request_array);
			$pxml 					= amazon_plugin_aws_signed_request($request_array['locale'],$request_array['api_request_array'],$request_array['public_key'],$request_array['private_key']);
			if(!is_array($pxml)){
				$pxml2	= $pxml;
				$pxml 	= array();
				$pxml["ItemLookupErrorResponse"]["Errors"]["Code"] = 'ERROR!';
				$pxml["ItemLookupErrorResponse"]["Errors"]["Message"] = $pxml2;
			}else{
				$resultarr1	= appip_plugin_FormatASINResult($pxml);
				$resultarr2 = appip_plugin_FormatASINResult($pxml,1);
				foreach($resultarr1 as $key1 => $result1):
					$mainAArr 			= (array)$result1;
					$otherArr 			= (array)$resultarr2[$key1];
					$resultarr[$key1] 	= (array)$mainAArr + $otherArr;
				endforeach;
				$returnval 	= '';
				$resultarr 	= has_filter('appip_product_array_processed') ? apply_filters('appip_product_array_processed',$resultarr,$apippnewwindowhtml,$resultarr1,$resultarr2,$template) : $resultarr;
				//$resultarr 	= has_filter('appip_which_product_array_to_use') ? apply_filters('appip_which_product_array_to_use',$resultarr1,$resultarr2,$template) : $resultarr1;
				//$compArrs  	= array_diff((array)$resultarr2,$resultarr);
				//$otherArr 	= empty($compArrs) ? $resultarr1 : $resultarr2; // this calculates what array is being used after the filter is applied and will pass the other array into the template function in case it is needed. 
				if(!is_array($resultarr)){$resultarr = (array) $resultarr;}
				if(is_array($resultarr)):
					$array_for_templates['timestamp_printed'] = $appipTimestampMsgPrinted; 
					if($show_timestamp!=0 && $appipTimestampMsgPrinted != 1){
						//echo '<span style="display:none;" class="appip-tos-price-cache-notice">'. sprintf(__('Product prices and availability are accurate as of the date/time indicated and are subject to change. Any price and availability information displayed on amazon.%1$s at the time of purchase will apply to the purchase of this product.','amazon-product-in-a-post-plugin'),$aws_partner_locale).'</span>';
						$appipTimestampMsgPrinted = 1;
						$array_for_templates['timestamp_printed'] = $appipTimestampMsgPrinted; 
					}
					if(count($resultarr) >=1){
						$thedivider = '<div class="appip-multi-divider"><!--appip divider--></div>';
					}
					foreach($resultarr as $key => $result):
						if(isset($result['NoData']) && $result['NoData'] == '1'):
							$returnval .=  $result['Error'];
							if($extratext != ''):
								$returnval .= $extratext;
							endif;
						else:
							unset($temppart);
							$temppart[] = '<div>';
							$temppart[] = '	<div class="amazon-image-wrapper"><a href="[!URL!]" [!TARGET!]>[!IMAGE!]</a></div>';
							$temppart[] = '	<a rel="appiplightbox-[!ASIN!]" href="[!LARGEIMAGE!]" target="amazonwin"><span class="amazon-tiny">[!LARGEIMAGETXT!]</span></a>';
							if($result['AddlImages']!='' && $show_gallery == 1){
								$temppart[] = '	<div class="amazon-additional-images-wrapper"><span class="amazon-additional-images-text">[!LBL-ADDL-IMAGES!]:</span>[!ADDL-IMAGES!]</div>';
							}	
							$temppart[] = '	<h2 class="amazon-asin-title"><a href="[!URL!]" [!TARGET!]><span class="asin-title">[!TITLE!]</span></a></h2>';
							$temppart[] = '	<div class="amazon-description">[!CONTENT!]</div>';
							
							if($result["Department"]=='Video Games' || $result["ProductGroup"]=='Video Games'){
								$temppart[] = '	<div>';
								$temppart[] = '		<span class="amazon-manufacturer"><span class="appip-label">[!LBL-MANUFACTURER!]:</span> [!MANUFACTURER!]</span><br />';
								$temppart[] = '		<span class="amazon-ESRB"><span class="appip-label">[!LBL-ESRBA!]:</span> [!ESRBA!]</span><br />';
								$temppart[] = '		<span class="amazon-platform"><span class="appip-label">[!LBL-PLATFORM!]:</span> [!PLATFORM!]</span><br />';
								$temppart[] = '		<span class="amazon-system"><span class="appip-label">[!LBL-GENRE!]:</span> [!GENRE!]</span><br />';
								
								if($show_features != 0){
									$temppart[] = '		<span class="amazon-feature"><span class="appip-label">[!LBL-FEATURE!]:</span> [!FEATURE!]</span><br />';
								}		
								$temppart[] = '	</div>';					
							}elseif($show_features != 0 && $result["Feature"] != ''){
								$temppart[] = '		<span class="amazon-feature"><span class="appip-label">[!LBL-FEATURE!]:</span> [!FEATURE!]</span><br />';
							}
							if($result["ReleaseDate"] != ''){	
								$nowdatestt = strtotime(date("Y-m-d",time()));
								$nowminustt = strtotime("-180 days");
								$reldatestt = strtotime($result["ReleaseDate"]);
								if($reldatestt > $nowdatestt){
									$temppart[] = '<span class="amazon-preorder"><br />[!LBL-RELEASED-ON-DATE!] [!RELEASE-DATE!]</span>';
								}elseif($reldatestt >= $nowminustt){
									$temppart[] = '<span class="amazon-release-date">[!LBL-RELEASE-DATE!] [!RELEASE-DATE!]</span>';
								}
							}
							$temppart[] = '<div><a style="display:block;margin-top:8px;margin-bottom:5px;width:165px;" [!TARGET!] href="[!URL!]"><img src="[!AMZ-BUTTON!]" border="0" style="border:0 none !important;margin:0px !important;background:transparent !important;" /></a></div>';
							$temppart[] = '</div>';
							$temppart[] = '<div><hr noshade="noshade" size="1" /></div>';
							$appip_templates['fluffy'] = implode("\n",$temppart);
							$appip_templates = apply_filters('appip-template-filter',$appip_templates, $result);
							if($template !='default' && isset($appip_templates[$template])){
								if($replace_title!=''){$title = $replace_title;}else{$title = maybe_convert_encoding($result["Title"]);}
								$newdesc 	= '';
								if(is_array($result["ItemDesc"]) && $description == 1){
									$desc 	= $result["ItemDesc"][0];
									$newdesc= maybe_convert_encoding($desc['Content']);
								}

								$findarr 	= array(
									'[!URL!]',
									'[!TARGET!]',
									'[!IMAGE!]',
									'[!TITLE!]',
									'[!LARGEIMAGE!]',
									'[!LARGEIMAGETXT!]',
									'[!ASIN!]',
									'[!CONTENT!]',
									'[!LBL-MANUFACTURER!]',
									'[!MANUFACTURER!]',
									'[!LBL-ESRBA!]',
									'[!ESRBA!]',
									'[!LBL-PLATFORM!]',
									'[!PLATFORM!]',
									'[!LBL-GENRE!]',
									'[!GENRE!]',
									'[!LBL-FEATURE!]',
									'[!FEATURE!]',
									'[!AMZ-BUTTON!]',
									'[!LBL-RELEASED-ON-DATE!]',
									'[!LBL-RELEASE-DATE!]',	
									'[!RELEASE-DATE!]',	
									'[!LBL-ADDL-IMAGES!]',	
									'[!ADDL-IMAGES!]',	
								);
								$replacearr = array(
									$result['URL'],
									$apippnewwindowhtml,
									awsImageGrabber($result['LargeImage'],'amazon-image'),
									$title,
									$result['LargeImage'],
									$appip_text_lgimage,
									$result['ASIN'],
									$newdesc,
									$appip_text_manufacturer,
									maybe_convert_encoding($result["Manufacturer"]),
									$appip_text_ESRBAgeRating,
									maybe_convert_encoding($result["ESRBAgeRating"]),
									$appip_text_platform,
									maybe_convert_encoding($result["Platform"]),
									$appip_text_genre,
									maybe_convert_encoding($result["Genre"]),
									$appip_text_feature,
									maybe_convert_encoding($result["Feature"]),
									plugins_url('/images/'.$buyamzonbutton,dirname(__FILE__)),
									$appip_text_releasedon,
									date("F j, Y", strtotime($result["ReleaseDate"])),
									$appip_text_reldate,
									'Additional Images',
									$result['AddlImages'],
									
								);
								$findarr = apply_filters('appip_template_find_array',$findarr,$template,$result);
								$replacearr = apply_filters('appip_template_replace_array',$replacearr,$template,$result,$title,$desc);
								$returnval	.= str_replace($findarr,$replacearr,$appip_templates[$template]);
							
							}else{
							
								$returnval .= '	<br /><table cellpadding="0" class="amazon-product-table">'."\n";
								$returnval .= '		<tr>'."\n";
								$returnval .= '			<td valign="top">'."\n";
								$returnval .= '				<div class="amazon-image-wrapper">'."\n";
								$returnval .= '					<a href="' . $result['URL'] . '" '. $apippnewwindowhtml .'>' . awsImageGrabber($result['MediumImage'],'amazon-image') . '</a><br />'."\n";
								if($result['LargeImage']!=''){
								$returnval .= '					<a rel="appiplightbox-'.$result['ASIN'].'" href="'.$result['LargeImage'] .'" target="amazonwin"><span class="amazon-tiny">'.$appip_text_lgimage.'</span></a>'."\n";
								}
								if($result['AddlImages']!='' && $show_gallery == 1){
								$returnval .= ' 					<div class="amazon-additional-images-wrapper"><span class="amazon-additional-images-text">Additional Images:</span>'.$result['AddlImages'].'</div>';
								}	
								$returnval .= '				</div>'."\n";
								$returnval .= '				<div class="amazon-buying">'."\n";
									if($replace_title!=''){$title = $replace_title;}else{$title = maybe_convert_encoding($result["Title"]);}
									if(strtolower($title) != 'null'){ 
										$returnval .= '					<h2 class="amazon-asin-title"><a href="' . $result['URL'] . '" '. $apippnewwindowhtml .'><span class="asin-title">'.$title.'</span></a></h2>'."\n";
									}
									$returnval .= '				<hr noshade="noshade" size="1" />'."\n";
									if($result["Department"]=='Video Games' || $result["ProductGroup"]=='Video Games'){
										$returnval .= '					<span class="amazon-manufacturer"><span class="appip-label">'.($appip_text_manufacturer != '' ? $appip_text_manufacturer .':' : '').'</span> '.maybe_convert_encoding($result["Manufacturer"]).'</span><br />'."\n";
										$returnval .= '					<span class="amazon-ESRB"><span class="appip-label">'.($appip_text_ESRBAgeRating != '' ? $appip_text_ESRBAgeRating .':' : '').'</span> '.maybe_convert_encoding($result["ESRBAgeRating"]).'</span><br />'."\n";
										$returnval .= '					<span class="amazon-platform"><span class="appip-label">'.($appip_text_platform != '' ? $appip_text_platform .':' : '').'</span> '.maybe_convert_encoding($result["Platform"]).'</span><br />'."\n";
										$returnval .= '					<span class="amazon-system"><span class="appip-label">'.($appip_text_genre != '' ? $appip_text_genre .':' : '').'</span> '.maybe_convert_encoding($result["Genre"]).'</span><br />'."\n";
										if($show_features != 0){
											$returnval .= '					<span class="amazon-feature"><span class="appip-label">'.($appip_text_feature != '' ? $appip_text_feature .':' : '').'</span> '.maybe_convert_encoding($result["Feature"]).'</span>'."\n";
										}							
									}elseif($show_features != 0 && $result["Feature"] != ''){
										$returnval .= '					<span class="amazon-feature"><span class="appip-label">'.($appip_text_feature != '' ? $appip_text_feature .':' : '').'</span> '.maybe_convert_encoding($result["Feature"]).'</span>'."\n";
									}
									if($show_features != 0){
										if(trim($result["Author"])!=''){
											$returnval .= '					<span class="amazon-author">'.($appip_text_author != '' ? $appip_text_author .': ': '').'</span> '.maybe_convert_encoding($result["Author"]).'</span><br />'."\n";
										}
										if(trim($result["Director"])!=''){
											$returnval .= '					<span class="amazon-director-label">'.($appip_text_director != '' ? $appip_text_director .': ' : '').' </span><span class="amazon-director">'.maybe_convert_encoding($result["Director"]).'</span><br />'."\n";
										}
										if(trim($result["Actor"])!=''){
											$returnval .= '					<span class="amazon-starring-label">'.($appip_text_starring != '' ? $appip_text_starring.': ' : '').'</span><span class="amazon-starring">'.maybe_convert_encoding($result["Actor"]).'</span><br />'."\n";
										}
										if(trim($result["AudienceRating"])!=''){
											$returnval .= '					<span class="amazon-rating-label">Rating: </span><span class="amazon-rating">'.$result["AudienceRating"].'</span><br />'."\n";
										}
									}
									if(!empty($result["ItemDesc"]) && $description == 1){
										//$returnval .= '				<hr noshade="noshade" size="1" />'."\n";
										if(is_array($result["ItemDesc"])){
											$desc = $result["ItemDesc"][0];
											$returnval .= '				<div class="amazon-description">'.maybe_convert_encoding($desc['Content']).'</div>'."\n";
										}
									}
									$returnval .= '				<div align="left" class="amazon-product-pricing-wrap">'."\n";
									$returnval .= '					<table class="amazon-product-price" cellpadding="0">'."\n";
									if($extratext!=''){
									$returnval .= '						<tr>'."\n";
									$returnval .= '							<td class="amazon-post-text" colspan="2">'.$extratext.'</td>'."\n";
									$returnval .= '						</tr>'."\n";
									}
									if($show_list == 1){
										if($result["PriceHidden"]== '1' ){
											$returnval .= '						<tr>'."\n";
											$returnval .= '							<td class="amazon-list-price-label">'.($appip_text_listprice != '' ? $appip_text_listprice .':' : '').'</td>'."\n";
											$returnval .= '							<td class="amazon-list-price-label">'.$amazonhiddenmsg.'</td>'."\n";
											$returnval .= '						</tr>'."\n"; 
										}elseif($result["ListPrice"]!= '0'){
											$returnval .= '						<tr>'."\n";
											$returnval .= '							<td class="amazon-list-price-label">'.($appip_text_listprice != '' ? $appip_text_listprice .':' : '').'</td>'."\n";
											$returnval .= '							<td class="amazon-list-price">'.  maybe_convert_encoding($result["ListPrice"]) .'</td>'."\n";
											$returnval .= '						</tr>'."\n";
										}
									}
									if(isset($result["LowestNewPrice"])){
										if($result["Binding"] == 'Kindle Edition'){
											$returnval .= '						<tr>'."\n";
											$returnval .= '							<td class="amazon-new-label">Kindle Edition:</td>'."\n";
											$returnval .= '							<td class="amazon-new">Check Amazon for Pricing <span class="instock">Digital Only</span></td>'."\n";
											$returnval .= '						</tr>'."\n";
										}else{
											if($result["LowestNewPrice"] == 'Too low to display'){
												$newPrice = 'Check Amazon For Pricing';
											}else{
												$newPrice = $result["LowestNewPrice"];
											}
											$returnval .= '						<tr>'."\n";
											$returnval .= '							<td class="amazon-new-label">'.($appip_text_newfrom != '' ? $appip_text_newfrom .':' : '').'</td>'."\n";
											if(!(isset($result["HideStockMsg"]) && isset($result["HideStockMsg"]) == '1')){
												$stockIn = $appip_text_instock;
												$stockOut = $appip_text_outofstock;
											}else{
												$stockIn = '';
												$stockOut = '';
											}
												if($result["TotalNew"]>0){
													$returnval .= '							<td class="amazon-new">'. maybe_convert_encoding($newPrice ).' <span class="instock">'.$stockIn.'</span></td>'."\n";
												}else{
													$returnval .= '							<td class="amazon-new">'. maybe_convert_encoding($newPrice ).' <span class="outofstock">'.$stockOut.'</span></td>'."\n";
												}
												$returnval .= '						</tr>'."\n";
											
										}
									}
									if($show_used == 1){
										if(isset($result["LowestUsedPrice"]) && $result["Binding"] != 'Kindle Edition'){
											$returnval .= '						<tr>'."\n";
											$returnval .= '							<td class="amazon-used-label">'.($appip_text_usedfrom != '' ? $appip_text_usedfrom .':' : '').'</td>'."\n";
											if($result["TotalUsed"] > 0){
												$returnval .= '						<td class="amazon-used">'. maybe_convert_encoding($result["LowestUsedPrice"]) .' <span class="instock">'.$appip_text_instock.'</span></td>'."\n";
											}else{
												if($result["LowestUsedPrice"] == '' || $result["LowestUsedPrice"] =="0"){
													$usedfix = '';
												}else{
													$usedfix = maybe_convert_encoding($result["LowestUsedPrice"]);
												}
												$returnval .= '						<td class="amazon-used">'. $usedfix . ' <span class="outofstock">'.$appip_text_outofstock.'</span></td>'."\n";
											}
											$returnval .= '						</tr>'."\n";
										}
									}
									if(isset($result["VariantHTML"]) && $result["VariantHTML"] != ''){
										$returnval .= '						<tr>'."\n";
										$returnval .= '							<td colspan="2" class="amazon-list-variants">'.$result["VariantHTML"].'</td>'."\n";
										$returnval .= '						</tr>'."\n"; 
									}
									$returnval .= '						<tr>'."\n";
									$returnval .= '							<td valign="top" colspan="2">'."\n";
									$returnval .= '								<div class="amazon-dates">'."\n";
									if($result["ReleaseDate"] != ''){	
										$nowdatestt = strtotime(date("Y-m-d",time()));
										$nowminustt = strtotime("-60 days");
										$reldatestt = strtotime($result["ReleaseDate"]);
										if($reldatestt > $nowdatestt){
									$returnval .= '									<span class="amazon-preorder"><br />'.$appip_text_releasedon.' '.date("F j, Y", strtotime($result["ReleaseDate"])).'.</span>'."\n";
										}elseif($reldatestt >= $nowminustt){
									$returnval .= '									<span class="amazon-release-date">'.$appip_text_reldate.' '.date("F j, Y", strtotime($result["ReleaseDate"])).'.</span>'."\n";
										}
									}
									$buttonURL  = apply_filters('appip_amazon_button_url',plugins_url('/images/'.$buyamzonbutton,dirname(__FILE__)),$buyamzonbutton,$manual_locale);
									$returnval .= '									<div class="amazon-price-button"><a '. $apippnewwindowhtml .' href="' . $result["URL"] .'"><img class="amazon-price-button-img" src="'.$buttonURL.'" /></a></div>'."\n";
									if($extrabutton==1 && $aws_partner_locale!='.com'){
									//$returnval .= '									<br /><div><a style="display:block;margin-top:8px;margin-bottom:5px;width:165px;" '. $apippnewwindowhtml .' href="' . $result["URL"] .'"><img src="'.plugins_url('/images/buyamzon-button.png',dirname(__FILE__)).'" border="0" style="border:0 none !important;margin:0px !important;background:transparent !important;"/></a></div>'."\n";
									}
									$returnval .= '								</div>'."\n";
									$returnval .= '							</td>'."\n";
									$returnval .= '						</tr>'."\n";
									if(!isset($result["LowestUsedPrice"]) && !isset($result["LowestNewPrice"]) && !isset($result["ListPrice"])){
										$returnval .= '						<tr>'."\n";
										$returnval .= '							<td class="amazon-price-save-label" colspan="2">'.$appip_text_notavalarea.'</td>'."\n";
										$returnval .= '						</tr>'."\n";
									}
									$returnval .= '					</table>'."\n";
								$returnval .= '					</div>'."\n";
								$returnval .= '				</div>'."\n";
								$returnval .= '			</td>'."\n";
								$returnval .= '		</tr>'."\n";
								$returnval .= '	</table>'."\n";
								if($result["CachedAPPIP"] !=''){
									$returnval .= '<'.'!-- APPIP Item Cached ['.$result["CachedAPPIP"].'] -->'."\n";
								}
								$returnval .= $thedivider;
							}//template
						endif;
					endforeach;
				endif;
				return apply_filters('appip_single_product_filter',$returnval,$resultarr);
			}
		}
	}
}
function appip_fix_button_url_for_locale($url='',$button='',$locale=''){
	if(($button == 'buyamzon-button-'.$locale.'.png') || ($button == 'buyamzon-button.png' && $locale == 'com') ){
		return $url;
	}else{
		$tempURL 	= str_replace($button,'',$url);
		$newURL 	= $tempURL .'new-buyamzon-button-'.$locale.'.png';
		return $newURL;
	}
	return $url;	
}
add_filter('appip_amazon_button_url','appip_fix_button_url_for_locale',10,3);

if(!function_exists('awsImageGrabber')){
	//Amazon Product Image from ASIN function - Returns HTML Image Code
	function awsImageGrabber($imgurl, $class=""){
		if($imgurl != ''){
	    	return '<img src="'.$imgurl.'" class="amazon-image '.$class.'" />';
		}else{
	    	return '<img src="'. plugins_url('/images/noimage.jpg',dirname(__FILE__)).'" class="amazon-image '.$class.'" />';
		}
	}
}
/*
To filter labels:
add_filter('appip_text_newfrom', '_clear_appip_text');
function _clear_appip_text($val=''){
	return 'Your Text Label Here';
}
*/

if(!function_exists('awsImageGrabberURL')){
	//Amazon Product Image from ASIN function - Returns URL only
	function awsImageGrabberURL($asin, $size="M"){
	    $base_url = 'http://images.amazon.com/images/P/'.$asin.'.01.';
	    if (strcasecmp($size, 'S') == 0){
	      $base_url .= '_AA200_SCLZZZZZZZ_';
	    }else if (strcasecmp($size, 'L') == 0){
	      $base_url .= '_AA450_SCSCRM_';
	    }else if (strcasecmp($size, 'H') == 0){ //huge
	      $base_url .= '_SCRM_';
	    }else if (strcasecmp($size, 'P') == 0){ //pop
	      $base_url .= '_AA800_SCRM_';
	    }else{
	      $base_url .= '_AA300_SCLZZZZZZZ_';
	    }
	    $base_url .= '.jpg';
	    return $base_url;
	}
}
	
if(!function_exists('awsImageURLModify')){
	//Amazon Product Image from ASIN function - Returns URL only
	function awsImageURLModify($imgurl, $size="P"){
		//http://ecx.images-amazon.com/images/I/
	    $base_url = str_replace('.jpg','.',$imgurl);
	    if (strcasecmp($size, 'S') == 0){
	      $base_url .= '_SY200_';
	    }else if (strcasecmp($size, 'L') == 0){
	      $base_url .= '_SY450_';
	    }else if (strcasecmp($size, 'H') == 0){ //huge
	      $base_url .= '_SY1200_';
	    }else if (strcasecmp($size, 'P') == 0){ //pop
	      $base_url .= '_SY800_';
	    }else{
	      $base_url .= '_SY300_';
	    }
	    $base_url .= '.jpg';
	    return $base_url;
	}
}

if(!function_exists('aws_prodinpost_filter_excerpt')){
	function aws_prodinpost_filter_excerpt($text){
		global $post,$apipphookexcerpt;
		$ActiveProdPostAWS 			= get_post_meta($post->ID,'amazon-product-isactive',true);
		$singleProdPostAWS 			= get_post_meta($post->ID,'amazon-product-single-asin',true);
		$AWSPostLoc 				= get_post_meta($post->ID,'amazon-product-content-location',true);
		$apippExcerptHookOverride 	= get_post_meta($post->ID,'amazon-product-excerpt-hook-override',true);
		$apippShowSingularonly 		= get_option('appip_show_single_only')=='1' ? '1' : '0';
		$apippShowSingularonly2 	= get_post_meta($post->ID,'amazon-product-singular-only',true);
		$showFormat					= get_post_meta($post->ID,'amazon-product-show-format',true);
		$showDesc 					= get_post_meta($post->ID,'amazon-product-amazon-desc',true);
		$showGallery 				= get_post_meta($post->ID,'amazon-product-show-gallery',true);
		$showFeatures 				= get_post_meta($post->ID,'amazon-product-show-features',true);
		$showList 					= get_post_meta($post->ID,'amazon-product-show-list-price',true);
		$showUsed 					= get_post_meta($post->ID,'amazon-product-show-used-price',true);
		$showSaved 					= get_post_meta($post->ID,'amazon-product-show-saved-amt',true);
		$showTimestamp 				= get_post_meta($post->ID,'amazon-product-timestamp',true);
		$newTitle 					= get_post_meta($post->ID,'amazon-product-new-title',true);
		$manualArray = array(
			'desc' 			=> $showDesc,
			'listprice' 	=> $showList,
			'showformat' 	=> $showFormat,
			'features' 		=> $showFeatures ,
			'used_price' 	=> $showUsed,
			'saved_amt'		=> $showSaved,
			'timestamp' 	=> $showTimestamp,
			'gallery' 		=> $showGallery,
			'replace_title' => $newTitle
		);
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
			  			$theproduct = getSingleAmazonProduct($singleProdPostAWS,$text,0,$manualArray);
			  		}elseif($AWSPostLoc=='3'){
			  			$theproduct = $text.'<br />'.getSingleAmazonProduct($singleProdPostAWS,'',0,$manualArray);
			  		}else{
			  			$theproduct = getSingleAmazonProduct($singleProdPostAWS,'',0,$manualArray).'<br />'.$text;
			  		}
			  		return $theproduct;
			  	} else {
			  		return $text;
			  	}
			}else{
			  	if($singleProdPostAWS!='' && $ActiveProdPostAWS!=''){
			  		if($AWSPostLoc=='2'){
			  			$theproduct = getSingleAmazonProduct($singleProdPostAWS,$text,0,$manualArray);
			  		}elseif($AWSPostLoc=='3'){
			  			$theproduct = $text.'<br />'.getSingleAmazonProduct($singleProdPostAWS,'',0,$manualArray);
			  		}else{
			  			$theproduct = getSingleAmazonProduct($singleProdPostAWS,'',0,$manualArray).'<br />'.$text;
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
		$ActiveProdPostAWS 			= get_post_meta($post->ID,'amazon-product-isactive',true);
		$singleProdPostAWS 			= get_post_meta($post->ID,'amazon-product-single-asin',true);
		$AWSPostLoc 				= get_post_meta($post->ID,'amazon-product-content-location',true);
		$apippContentHookOverride 	= get_post_meta($post->ID,'amazon-product-content-hook-override',true);
		$apippShowSingularonly 		= get_post_meta($post->ID,'amazon-product-singular-only',true);
		$showFormat					= get_post_meta($post->ID,'amazon-product-show-format',true);
		$showDesc 					= get_post_meta($post->ID,'amazon-product-amazon-desc',true);
		$showGallery 				= get_post_meta($post->ID,'amazon-product-show-gallery',true);
		$showFeatures 				= get_post_meta($post->ID,'amazon-product-show-features',true);
		$newWindow 					= get_post_meta($post->ID,'amazon-product-newwindow',true);
		$showList 					= get_post_meta($post->ID,'amazon-product-show-list-price',true);
		$showUsed 					= get_post_meta($post->ID,'amazon-product-show-used-price',true);
		$showSaved 					= get_post_meta($post->ID,'amazon-product-show-saved-amt',true);
		$showTimestamp 				= get_post_meta($post->ID,'amazon-product-timestamp',true);
		$newTitle 					= get_post_meta($post->ID,'amazon-product-new-title',true);
		$newWindow					= $newWindow == '2' ? 1 : 0;
		
		$manualArray = array(
			'desc' 			=> $showDesc,
			'listprice' 	=> $showList,
			'showformat' 	=> $showFormat,
			'features' 		=> $showFeatures ,
			'used_price' 	=> $showUsed,
			'saved_amt'		=> $showSaved,
			'timestamp' 	=> $showTimestamp,
			'gallery' 		=> $showGallery,
			'replace_title' => $newTitle,
			'newwindow' 	=> $newWindow
		);
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
			  			$theproduct = getSingleAmazonProduct($singleProdPostAWS,$text,0,$manualArray);
			  		}elseif($AWSPostLoc=='3'){
			  			//Post Content before product
			  			$theproduct = $text.'<br />'.getSingleAmazonProduct($singleProdPostAWS,'',0,$manualArray);
			  		}else{
			  			//Post Content after product - default
			  			$theproduct = getSingleAmazonProduct($singleProdPostAWS,'',0,$manualArray).'<br />'.$text;
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
			  			$theproduct = getSingleAmazonProduct($singleProdPostAWS,$text,0,$manualArray);
			  		}elseif($AWSPostLoc=='3'){
			  			//Post Content before product
			  			$theproduct = $text.'<br />'.getSingleAmazonProduct($singleProdPostAWS,'',0,$manualArray);
			  		}else{
			  			//Post Content after product - default
			  			$theproduct = getSingleAmazonProduct($singleProdPostAWS,'',0,$manualArray).'<br />'.$text;
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

/**
 * Add Styles to HTML Head.
 *
 * Echos the content to the head.
 *
 * @depricated 3.5.3 Replaced with Ajax Call for faster action
 * @since 1.8
 *
 * @echo stylesheet links.
 */
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
	
/**
 * Enqueue styles for plugin.
 * Replaces previous function aws_prodinpost_addhead().
 *
 * @since 3.5.3
 *
 * @return none.
 */
function appip_addhead_new_ajax(){
	if(file_exists(get_stylesheet_directory().'/appip-styles.css')){
		wp_enqueue_style('appip-theme-styles',get_stylesheet_directory_uri().'/appip-styles.css',array(),null);
	}elseif(file_exists(get_stylesheet_directory().'/css/appip-styles.css')){
		wp_enqueue_style('appip-theme-styles',get_stylesheet_directory_uri().'/css/appip-styles.css',array(),null);
	}else{
		$ajax_nonce = wp_create_nonce( 'appip_style_verify' );
		wp_enqueue_style('appip-dynamic-styles',admin_url('admin-ajax.php').'?action=appip_dynaminc_css_custom&nonce='.$ajax_nonce,array(),null);
	}
	wp_enqueue_style('appip-lightbox', plugins_url().'/amazon-product-in-a-post-plugin/css/amazon-lightbox.css', array(),null);
}
add_action('wp_enqueue_scripts', 'appip_addhead_new_ajax',10);

/**
 * Dynamic style creation. Replaces old styles layout which is very slow on large sites.
 *
 * Prints CSS styles out in the browser dynamically.
 *
 * @since 3.5.3
 *
 * @echo css values stored in DB.
 * @return none.
 */
function appip_dynaminc_css_custom() {
	check_ajax_referer( 'appip_style_verify', 'nonce', true );  
	$usemine    = get_option('apipp_product_styles_mine', false);
	$data       = $usemine ? get_option('apipp_product_styles', '') : get_option('apipp_product_styles_default', '') ;
  	header('Content-type: text/css');
  	header('Cache-control: must-revalidate');	
	echo $data;
	exit;
}
add_action('wp_ajax_appip_dynaminc_css_custom', 'appip_dynaminc_css_custom');
add_action('wp_ajax_nopriv_appip_dynaminc_css_custom', 'appip_dynaminc_css_custom');

function add_appip_jquery(){
	wp_register_script('appip-amazonlightbox', plugins_url('/js/amazon-lightbox.js',dirname(__FILE__)));
	wp_enqueue_script('jquery'); 
	wp_enqueue_script('appip-amazonlightbox'); 
	if(!is_admin()){
		wp_enqueue_style( 'amazon-plugin-frontend-styles',plugins_url('/css/amazon-frontend.css',dirname(__FILE__)),null,'13-08-24');
		wp_enqueue_script('amazon-plugin-frontend-script',plugins_url('/js/amazon-frontend.js',dirname(__FILE__)),array('jquery-ui-tooltip'),'13-08-24');
	}
}