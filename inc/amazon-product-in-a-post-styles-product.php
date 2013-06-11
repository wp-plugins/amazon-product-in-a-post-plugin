<?php
add_action('wp', 'apipp_parse_request');
add_filter('query_vars', 'apipp_query_vars');

function apipp_query_vars($vars) {
	$vars[] = 'apipp_style';
	$vars[] = 'appip-cache-id';
	$vars[] = 'appip-cache-del';

    return $vars;
}

function apipp_parse_request($wp) {
    if (array_key_exists('apipp_style', $wp->query_vars) && $wp->query_vars['apipp_style'] == 'default') {
    	//use default;
		$defaultapippstyles= get_option("apipp_product_styles_default");
		$amazonStylesToUse = $defaultapippstyles;
		if(get_option("apipp_product_styles")==''){update_option("apipp_product_styles",$amazonStylesToUse);}
		header("Status: 200");
		header("Pragma: public");
		header("Content-type: text/css");
		header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
		print($amazonStylesToUse);
		exit;
    }elseif (array_key_exists('apipp_style', $wp->query_vars) && $wp->query_vars['apipp_style'] == 'custom'){
		//their Styles
		$thereapippstyles = get_option("apipp_product_styles"); //the styles in the admin
		$amazonStylesToUse = $thereapippstyles;
		header("Status: 200");
		header("Pragma: public");
		header("Content-type: text/css");
		header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
		print($amazonStylesToUse);
		exit;
	}elseif(array_key_exists('appip-cache-id', $wp->query_vars) && $wp->query_vars['appip-cache-id'] != ''){
		if(array_key_exists('appip-cache-del', $wp->query_vars) && $wp->query_vars['appip-cache-del'] == 'dodel'){
			global $wpdb;
			$tempswe = $wpdb->query("DELETE FROM {$wpdb->prefix}amazoncache WHERE Cache_id ='{$wp->query_vars['appip-cache-id']}' LIMIT 1;");
			if($tempswe){
				echo 'deleted';
			}else{
				echo 'error';
			}
			exit;
		}	
	}else{
		return;
	}
}