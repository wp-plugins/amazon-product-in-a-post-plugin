<?php
add_action( 'init', create_function('$a',"add_shortcode('AMAZONPRODUCTS', 'amazon_product_shortcode_function');"));
add_action( 'init', 'amazon_appip_editor_button');
add_filter( 'widget_text', 'do_shortcode');
add_filter( 'the_content', 'do_shortcode');
add_filter( 'the_excerpt', 'do_shortcode');

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
		'desc' => 0, //set to 1 to show or 0 to hide description if avail
		'features' => 0, //set to 1 to show or 0 to hide features if avail
		'listprice' => 1 //set to 0 to hide list price
	);
	extract(shortcode_atts($defaults, $atts));
	$product_array = $asin;	 /*$product_array can be array, comma separated string or single ASIN*/
	$amazon_array = array(
		'locale' => $locale,
		'partner_id' => $partner_id,
		'private_key' => $private_key,
		'public_key' => $public_key, 
		'gallery'	=> $gallery,
		'features' => $features,
		'listprice' => $listprice,
		'desc' => $desc,
	);
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
?>