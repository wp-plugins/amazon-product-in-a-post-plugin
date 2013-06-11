<?php
add_action( 'init', create_function('$a',"add_shortcode( 'amazon-element', 'amazon_product_shortcode_mini_function');add_shortcode( 'amazon-elements', 'amazon_product_shortcode_mini_function');add_shortcode( 'amazonproducts', 'amazon_product_shortcode_function');add_shortcode( 'amazonproduct', 'amazon_product_shortcode_function');add_shortcode( 'AMAZONPRODUCTS', 'amazon_product_shortcode_function');add_shortcode( 'AMAZONPRODUCT', 'amazon_product_shortcode_function');"));
add_action( 'init', 'amazon_appip_editor_button');
add_filter( 'widget_text', 'do_shortcode');
add_filter( 'the_content', 'do_shortcode');
add_filter( 'the_excerpt', 'do_shortcode');

function amazon_product_shortcode_mini_function($atts, $content = ''){
	global $appip_text_lgimage,$showformat,$public_key,$private_key,$aws_partner_id,$aws_partner_locale,$amazonhiddenmsg,$amazonerrormsg,$apippopennewwindow,$apippnewwindowhtml,$buyamzonbutton,$addestrabuybutton,$post,$validEncModes,$appip_text_lgimage;
	$defaults = array(
		'asin'=> '',
		'locale' => $aws_partner_locale,
		'partner_id' => $aws_partner_id,
		'private_key' => $private_key,
		'public_key' => $public_key, 
		'fields'=> '',
		'field'=> '',
		'showformat' => 1,
		'listprice' => 1, 
		'replace_title' => '', 
		'template' => 'default',
		'msg_instock' => 'In Stock',
		'msg_outofstock' => 'Out of Stock',
		'target' => '_blank',
		'button_url' => '',
		'labels' => '',
	);
	
	extract(shortcode_atts($defaults, $atts));
	if($labels != ''){
		$labelstemp = explode(',',$labels);
		unset($labels);
		foreach($labelstemp as $lab){
			$keytemp = explode('::',$lab);
			if(isset($keytemp[0]) && isset($keytemp[1])){
				$labels[$keytemp[0]][] = $keytemp[1];
			}
		}
	}else{
		$labels = array();
	}
	if($button_url != ''){
		$buttonstemp = explode(',',$button_url);
		unset($button_url);
		foreach($buttonstemp as $buttona){
			if(!empty($buttona)){
				$button_url[] = $buttona;
			}
		}
	}else{
		$button_url = array();
	}

	if($field == '' && $fields !=''){$field = $fields;}
	if($aws_partner_locale==''){$aws_partner_locale='com';}
	if($target!=''){$target = ' target="'.$target.'" ';}
	if($appip_text_lgimage == ''){$appip_text_lgimage = 'see larger image';}
	if ($asin!=''){
		$ASIN 					= (is_array($asin))? implode(',',$asin) : $asin; //valid ASIN or ASINs 
		$errors 				= '';
		$pxml 					= aws_signed_request($locale, array("Operation" => "ItemLookup","ItemId" => $ASIN,"ResponseGroup" => "Large","IdType" => "ASIN","AssociateTag" => $partner_id ), $public_key, $private_key);
		if(!is_array($pxml)){
			$pxmlErr["ItemLookupErrorResponse"]["Errors"]["Code"] = 'ERROR!';
			$pxmlErr["ItemLookupErrorResponse"]["Errors"]["Message"] = $pxml;
			echo '<'.'!-- APPIP ERROR:pxml['.$pxml.']-->';
			return false;
		}else{
			$resultarr = FormatASINResult($pxml);
			$arr_position = 0;
			if(is_array($resultarr)):
				$retarr = array();
				foreach($resultarr as $result):
					$currasin = $result['ASIN'];
					if($result['NoData'] == '1'):
						echo '<'.'!-- APPIP ERROR:nodata['.$result['Error'].']-->';
					else:
						if(is_array($field)){
							$fielda = $field;
						}else{
							$fielda = explode(',',str_replace(' ','',$field));
						}
						foreach($fielda as $fieldarr){
							switch(strtolower($fieldarr)){
								case 'title':
									if(!isset($labels['title-wrap'][$arr_position]) && !isset($labels['title'][$arr_position])){
										$labels['title'][$arr_position] = '<h2 class="appip-title"><a href="'.$result['URL'].'"'.$target.'>'. maybe_convert_encoding($result["Title"]).'</a></h2>';
									}elseif(!isset($labels['title-wrap'][$arr_position]) && isset($labels['title'][$arr_position])){
										$labels['title'][$arr_position] = '<h2 class="appip-title"><a href="'.$result['URL'].'"'.$target.'>'.$labels['title'][$arr_position].'</a></h2>';
									}elseif(isset($labels['title-wrap'][$arr_position]) && isset($labels['title'][$arr_position])){
										$labels['title'][$arr_position] = "<{$labels['title-wrap'][$arr_position]} class='appip-title'>{$labels['title'][$arr_position]}</a></{$labels['title-wrap'][$arr_position]}>";
									}else{
										$labels['title'][$arr_position] = '<h2 class="appip-title"><a href="'.$result['URL'].'"'.$target.'>'. maybe_convert_encoding($result["Title"]).'</a></h2>';
									}
									$retarr[$currasin][$fieldarr] = $labels['title'][$arr_position];
									break;
								case 'desc':
								case 'description':
									if(isset($labels['desc'])){
										$labels['desc'][$arr_position] = '<span class="appip-label label-'.$fieldarr.'">'.$labels['desc'][$arr_position].' </span>';
									}elseif(isset($labels['description'][$arr_position])){
										$labels['desc'][$arr_position] = '<span class="appip-label label-'.$fieldarr.'">'.$labels['description'][$arr_position].' </span>';
									}else{
										$labels['desc'][$arr_position] = '';
									}
									if(is_array($result["ItemDesc"])){
										$desc = $result["ItemDesc"][0];
										$retarr[$currasin][$fieldarr] = maybe_convert_encoding($labels['desc'][$arr_position].$desc['Content']);
									}
									break;
								case 'gallery':
									if(!isset($labels['gallery'][$arr_position])){$labels['gallery'][$arr_position] = "Additional Images:";}else{$labels['gallery'][$arr_position] = '<span class="appip-label label-'.$fieldarr.'">'.$labels[$fieldarr][$arr_position].' </span>';}
									if($result['AddlImages']!=''){
										$retarr[$currasin][$fieldarr] = '<div class="amazon-image-wrapper"><span class="amazon-additional-images-text">'.$labels['gallery'][$arr_position].'</span><br/>'.$result['AddlImages'].'</div>';
									}	
									break;
								case 'imagesets':
									if(!isset($labels['imagesets'][$arr_position])){$labels['imagesets'][$arr_position] = "Additional Images: ";}else{$labels['imagesets'][$arr_position] = '<span class="appip-label label-'.$fieldarr.'">'.$labels[$fieldarr][$arr_position].' </span>';}
									if($result['AddlImages']!=''){
										$retarr[$currasin][$fieldarr] = '<div class="amazon-image-wrapper"><span class="amazon-additional-images-text">'.$labels['imagesets'][$arr_position].'</span><br/>'.$result['AddlImages'].'</div>';
									}	
									break;
								case 'price':
								case 'new-price':
								case 'new price':
									if(isset($labels['price'][$arr_position])){
										$labels['price-new'][$arr_position] = '<span class="appip-label label-'.$fieldarr.'">'.$labels['price'][$arr_position].' </span>';
									}elseif(isset($labels['new-price'][$arr_position])){
										$labels['price-new'][$arr_position] = '<span class="appip-label label-'.$fieldarr.'">'.$labels['new-price'][$arr_position].' </span>';
									}elseif(isset($labels['new price'][$arr_position])){
										$labels['price-new'][$arr_position] = '<span class="appip-label label-'.$fieldarr.'">'.$labels['new price'][$arr_position].' </span>';
									}else{
										$labels['price-new'][$arr_position] = '<span class="appip-label label-'.$fieldarr.'">'.'New From:'.' </span>';
									}
									if($result["LowestNewPrice"]=='Too low to display'){
										$newPrice = 'Check Amazon For Pricing';
									}else{
										$newPrice = $result["LowestNewPrice"];
									}
									if($result["TotalNew"]>0){
										$retarr[$currasin][$fieldarr] = $labels['price-new'][$arr_position].maybe_convert_encoding($newPrice).' <span class="instock">'.$msg_instock.'</span>';
									}else{
										$retarr[$currasin][$fieldarr] = $labels['price-new'][$arr_position].maybe_convert_encoding($newPrice).' <span class="outofstock">'.$msg_instock.'</span>';
									}
									break;
								case 'image':
								case 'med-image':
									$retarr[$currasin][$fieldarr] = '<div class="amazon-image-wrapper"><a href="'.$result['URL'].'"'.$target.'>'.awsImageGrabber(awsImageGrabberURL($currasin,"M"),'amazon-image').'</a></div>';
									break;
								case 'sm-image':
									$retarr[$currasin][$fieldarr] = '<div class="amazon-image-wrapper"><a href="'.$result['URL'].'"'.$target.'>'.awsImageGrabber($result['SmallImage'],'amazon-image').'</a></div>';
									break;
								case 'lg-image':
								case 'full-image':
									$retarr[$currasin][$fieldarr] = '<div class="amazon-image-wrapper"><a href="'.$result['URL'].'"'.$target.'>'.awsImageGrabber($result['LargeImage'],'amazon-image').'</a></div>';
									break;
								case 'large-image-link':
									if(!isset($labels['large-image-link'][$arr_position])){
										$labels['large-image-link'][$arr_position] = $appip_text_lgimage;
									}else{
										$labels['large-image-link'][$arr_position] = $labels[$fieldarr][$arr_position].' ';
									}
									if(awsImageGrabberURL($currasin,"P")!=''){
										$retarr[$currasin][$fieldarr] = '<div class="amazon-image-link-wrapper"><a rel="appiplightbox-'.$result['ASIN'].'" href="'.awsImageURLModify($result['LargeImage'],"P").'"><span class="amazon-element-large-img-link">'.$labels['large-image-link'][$arr_position].'</span></a></div>';
									}
									break;
								case 'features':
									if(!isset($labels['features'][$arr_position])){
										$labels['features'][$arr_position] = '';
									}else{
										$labels['features'][$arr_position] = '<span class="appip-label label-'.$fieldarr.'">'.$labels[$fieldarr][$arr_position].' </span>';
									}
									$retarr[$currasin][$fieldarr] = $labels['features'][$arr_position].maybe_convert_encoding($result["Feature"]);
									break;
								case 'link':
									$retarr[$currasin][$fieldarr] = '<a href="'.$result['URL'].'"'.$target.'>'.$result['URL'].'</a>';
									break;
								case 'button':
									if(isset($button_url[$arr_position])){
										$retarr[$currasin][$fieldarr] = '<a '.$target.' href="'.$result["URL"].'"><img src="'.$button_url[$arr_position].'" border="0" /></a>';
									}else{
										$retarr[$currasin][$fieldarr] = '<a '.$target.' href="'.$result["URL"].'"><img src="'.plugins_url('/images/'.$buyamzonbutton,dirname(__FILE__)).'" border="0" /></a>';
									}
									break;
								case 'customerreviews':
									$retarr[$currasin][$fieldarr] = '<iframe src="'.$result['CustomerReviews'].'" class="amazon-customer-reviews" width="100%" seamless="seamless"></iframe>';
									break;
								default:
									if(isset($result[$fieldarr]) && $result[$fieldarr]!=''){
										if(!isset($labels[$fieldarr][$arr_position])){
											$labels[$fieldarr][$arr_position] = '';
										}else{
											$labels[$fieldarr][$arr_position] = '<span class="appip-label label-'.str_replace(' ','-',$fieldarr).'">'.$labels[$fieldarr][$arr_position].' </span>';
										}
										$retarr[$currasin][$fieldarr] = $labels[$fieldarr][$arr_position].$result[$fieldarr];
									}else{
										$retarr[$currasin][$fieldarr] = '';
									}
									break;
							}
						}
					endif;
					
					$retarr = apply_filters('amazon_product_in_a_post_plugin_elements_filter',$retarr);
					foreach($retarr[$currasin] as $key=>$val){
						$thenewret[] =  '<div class="amazon-element-'.$key.'">'.$val.'</div>';
					}
					$arr_position++;
				endforeach;
				if(is_array($thenewret)){
					return implode("\n",$thenewret);
				}
				return false;
			endif;
		}
	}else{
		return false;
	}

}
function amazon_product_shortcode_function($atts, $content = '') {
	global $aws_partner_locale;
	global $public_key;
	global $private_key; 
	global $aws_partner_id;

	$defaults = array(
		'asin'=> '',
		'locale' => $aws_partner_locale,
		'gallery' => 0, //set to 1 to show ectra photos
		'partner_id' => $aws_partner_id,
		'private_key' => $private_key,
		'public_key' => $public_key, 
		'showformat' => 1,
		'desc' => 0, //set to 1 to show or 0 to hide description if avail
		'features' => 0, //set to 1 to show or 0 to hide features if avail
		'listprice' => 1, //set to 0 to hide list price
		'replace_title' => '', //replace with your own title
		'template' => 'default' //future feature
	);
	if(array_key_exists('0',$atts)){
		extract(shortcode_atts($defaults, $atts));
		$asin = str_replace('=','',$atts[0]);
	}else{
		extract(shortcode_atts($defaults, $atts));
	}
	if(strpos($asin,',')!== false){
		$asin = explode(',', str_replace(' ','',$asin));
	}
	$product_array = $asin;	 /*$product_array can be array, comma separated string or single ASIN*/
	$amazon_array = array(
		'locale' => $locale,
		'partner_id' => $partner_id,
		'private_key' => $private_key,
		'public_key' => $public_key, 
		'gallery'	=> $gallery,
		'features' => $features,
		'listprice' => $listprice,
		'showformat' => $showformat,
		'desc' => $desc,
		'replace_title' => $replace_title,
		'template' => $template
	);
	$amazon_array = apply_filters('appip_shortcode_atts_array',$amazon_array);
	return getSingleAmazonProduct($product_array,$content,0,$amazon_array,$desc);
}
function amazon_appip_register_button( $buttons ) {
	array_push( $buttons, "|", "amazon_products" );
	return $buttons;
}
function amazon_appip_add_plugin( $plugin_array ) {
	$plugin_array['amazon_products'] = plugins_url('/js/wysiwyg/amazon_editor.js',dirname(__FILE__));
	return $plugin_array;
}
function amazon_appip_editor_button() {
	if(!current_user_can('edit_posts') && !current_user_can('edit_pages') ) {
		return;
	}
	if(get_user_option('rich_editing') == 'true' ) {
		add_filter( 'mce_external_plugins', 'amazon_appip_add_plugin' );
		add_filter( 'mce_buttons', 'amazon_appip_register_button' );
	}
}
