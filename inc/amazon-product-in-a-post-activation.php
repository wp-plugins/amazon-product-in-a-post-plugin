<?php
// Plugin Hooks

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
		global $wpdb, $aws_plugin_version, $aws_plugin_dbversion;
		$curappipver = get_option("apipp_version");
		$dbversion = get_option("apipp_dbversion");
		$appiptable = $wpdb->prefix . 'amazoncache';
		if($curappipver== ''){
			$createSQL = "CREATE TABLE IF NOT EXISTS $appiptable (`Cache_id` int(10) NOT NULL auto_increment, `URL` text NOT NULL, `updated` datetime default NULL, `body` longtext, PRIMARY KEY (`Cache_id`), UNIQUE KEY `URL` (`URL`(255)), KEY `Updated` (`updated`)) ENGINE=MyISAM;";
	      	$wpdb->query($createSQL);
			add_option("apipp_version", $aws_plugin_version);
			add_option("apipp_dbversion", $aws_plugin_version);
		}
		if($dbversion != $aws_plugin_dbversion){
			$alterSQL = "ALTER TABLE `{$appiptable}` CHANGE `body` `body` LONGTEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;";
	      	$testif = $wpdb->query($alterSQL);
			update_option("apipp_version", $aws_plugin_version);
			update_option("apipp_dbversion", $aws_plugin_dbversion);
		}
	}

?>