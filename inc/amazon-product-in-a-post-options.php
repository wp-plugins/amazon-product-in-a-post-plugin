<?php
//VARIABLES
	global $fullname_apipp, $shortname_apipp, $options_apipp, $thedefaultapippstyle;
	$fullname_apipp = __('Amazon Product In a Post Plugin', 'amazon-product-in-a-post-plugin');
	$shortname_apipp = "apipp";
	
	$options_apipp= array (
		array(	"name" => __('General Plugin Settings','amazon-product-in-a-post-plugin'),
				"type" => "heading"),
		array(	"name" => __('Amazon Affiliate ID','amazon-product-in-a-post-plugin'),
				"desc" => __('Your Amazon Affiliate ID','amazon-product-in-a-post-plugin'),'<br /><br />',
	    		"id" => $shortname_apipp."_amazon_associateid",
	    		"type" => "text",
				"width" => '150'),
		array(	"name" => __('Amazon Access Key ID','amazon-product-in-a-post-plugin'),
				"desc" => __('Your Amazon Access Key ID (or Public Key). If you do not have one, you will need to sign up for one (click a link below):', 'amazon-product-in-a-post-plugin')."<br />
				<a target='_blank' style='outline: 0px; color: rgb(33, 117, 155);' href='http://associados.amazon.com.br/gp/associates/apply/main.html'><strong>".__('Brazil', 'amazon-product-in-a-post-plugin')." (br)</strong></a>, 
				<a target='_blank' style='outline: 0px; color: rgb(33, 117, 155);' href='https://associates.amazon.ca/gp/advertising/api/detail/main.html'><strong>".__('Canada', 'amazon-product-in-a-post-plugin')." (ca)</strong></a>, 
				<a target='_blank' style='outline: 0px; color: rgb(33, 117, 155);' href='https://associates.amazon.cn/gp/advertising/api/detail/main.html'><strong>".__('China', 'amazon-product-in-a-post-plugin')." (cn)</strong></a>, 
				<a target='_blank' style='outline: 0px; color: rgb(33, 117, 155);' href='https://partenaires.amazon.fr/gp/advertising/api/detail/main.html'><strong>".__('France', 'amazon-product-in-a-post-plugin')." (fr)</strong></a>, 
				<a target='_blank' style='outline: 0px; color: rgb(33, 117, 155);' href='https://affiliate-program.amazon.com/gp/advertising/api/detail/main.html'><strong>".__('Germany', 'amazon-product-in-a-post-plugin')." (de)</strong></a>, 
				<a target='_blank' style='outline: 0px; color: rgb(33, 117, 155);' href='https://affiliate-program.amazon.in/gp/advertising/api/detail/main.html'><strong>".__('India', 'amazon-product-in-a-post-plugin')." (in)</strong></a>, 
				<a target='_blank' style='outline: 0px; color: rgb(33, 117, 155);' href='https://programma-affiliazione.amazon.it/gp/advertising/api/detail/main.html'><strong>".__('Italy', 'amazon-product-in-a-post-plugin')." (it)</strong></a>, 
				<a target='_blank' style='outline: 0px; color: rgb(33, 117, 155);' href='https://affiliate.amazon.co.jp/gp/advertising/api/detail/agreement.html'><strong>".__('Japan', 'amazon-product-in-a-post-plugin')." (jp)</strong></a>, 
				<a target='_blank' style='outline: 0px; color: rgb(33, 117, 155);' href='https://afiliados.amazon.es/gp/advertising/api/detail/main.html'><strong>".__('Spain', 'amazon-product-in-a-post-plugin')." (es)</strong></a>, 
				<a target='_blank' style='outline: 0px; color: rgb(33, 117, 155);' href='https://affiliate-program.amazon.co.uk/gp/flex/advertising/api/sign-in.html'><strong>".__('United Kingdom', 'amazon-product-in-a-post-plugin')." (co.uk)</strong></a>, 
				<a target='_blank' style='outline: 0px; color: rgb(33, 117, 155);' href='https://affiliate-program.amazon.com/gp/flex/advertising/api/sign-in.html'><strong>".__('United States', 'amazon-product-in-a-post-plugin')." (com)</strong></a>",
	    		"id" => $shortname_apipp."_amazon_publickey",
	    		"type" => "text"),
		array(	"name" => __('Amazon Secret Access Key','amazon-product-in-a-post-plugin'),
				"desc" => sprintf(__('Your Amazon Secret Access Key (or Private Key). %1$sCheck out this page%1$s for more information on the Access Key IDs and Secret Access Keys.','amazon-product-in-a-post-plugin'),'<a href="admin.php?page=apipp-main-menu">','</a>').'<br/><br/>',
	    		"id" => $shortname_apipp."_amazon_secretkey",
	    		"type" => "password",
				"width" => '400'),
		array(	"name" => __('Debug Key','amazon-product-in-a-post-plugin'),
				"desc" => sprintf(__('Your Custom Debug Key. If you have problems with the plugin not working and you need help from the developer, email %1$s with this key and your website url. They will be able to use it to figure out issues. Without it, they can do nothing to help. This is unique to your site. If you received help and the problem is resolved, you can change it to make sure the developer cannot access the debugging features again, should you feel inclined to do so.','amazon-product-in-a-post-plugin'),'<a href="mailto:appip@fischercreativemedia.com">appip@fischercreativemedia.com</a>')."<br /><br />",
	    		"id" => $shortname_apipp."_amazon_debugkey",
	    		"type" => "text"),
		array(	"name" => __('Your Amazon Locale/Region','amazon-product-in-a-post-plugin'),
				"desc" => __('The Locale to use for Amazon API Calls (ca,cn,com,co.uk,de,fr,jp,es,in,it) Default is "com" for US.','amazon-product-in-a-post-plugin')."<br /><br />",
	    		"id" => $shortname_apipp."_amazon_locale",
	    		"type" => "select",
	    		"options" => array(
	    			"0" => array("value" => "com","text" => __('US (default)','amazon-product-in-a-post-plugin')),
	    			"1" => array("value" => "br","text" => __('Brazil','amazon-product-in-a-post-plugin')),
	    			"2" => array("value" => "ca","text" => __('Canada','amazon-product-in-a-post-plugin')),
	    			"3" => array("value" => "cn","text" => __('China','amazon-product-in-a-post-plugin')),
	    			"4" => array("value" => "fr","text" => __('France','amazon-product-in-a-post-plugin')),
	    			"5" => array("value" => "de","text" => __('Germany','amazon-product-in-a-post-plugin')),
	    			"6" => array("value" => "co.jp","text" => __('Japan','amazon-product-in-a-post-plugin')),
	    			"7" => array("value" => "in","text" => __('India','amazon-product-in-a-post-plugin')),
	    			"8" => array("value" => "it","text" => __('Italy','amazon-product-in-a-post-plugin')),
	    			"9" => array("value" => "es","text" => __('Spain','amazon-product-in-a-post-plugin')),
	    			"1-" => array("value" => "co.uk","text" => __('United Kingdom','amazon-product-in-a-post-plugin')),
	    		 )),
		array(	"name" => __('Language', 'amazon-product-in-a-post-plugin'),
				"desc" => __('Language to use for Text and Button (currently only English, French and Spanish - default is English).', 'amazon-product-in-a-post-plugin')."<br /><br />",
	    		"id" => $shortname_apipp."_amazon_language",
	    		"type" => "select",
	    		"options" => array(
	    			"0" => array("value" => "en","text" => __('English (default)', 'amazon-product-in-a-post-plugin')),
	    			"1" => array("value" => "fr","text" => __('French', 'amazon-product-in-a-post-plugin')),
	    			"2" => array("value" => "sp","text" => __('Spanish', 'amazon-product-in-a-post-plugin'))
	    		 )),
		array(	"name" => __('Not Available Error Message', 'amazon-product-in-a-post-plugin'),
				"desc" => __('The message to display if the item is not available for some reason, i.e., your locale or no longer available.', 'amazon-product-in-a-post-plugin')."<br /><br />",
	    		"id" => $shortname_apipp."_amazon_notavailable_message",
	    		"type" => "textlong"),
	    		
		array(	"name" => __('Amazon Hidden Price Message', 'amazon-product-in-a-post-plugin'),
				"desc" => __('For Some products, Amazon will hide the List price of a product. When hidden, this plugin cannot show a price for the product. This message will display in the List Price area when that occurs.', 'amazon-product-in-a-post-plugin')."<br /><br />",
	    		"id" => $shortname_apipp."_amazon_hiddenprice_message",
	    		"type" => "textlong"),
	    		
		array(	"name" => __('Use SSL Images?', 'amazon-product-in-a-post-plugin').' <span style="color: #FF0000;"><em>Coming Soon</em></span>',
				"desc" => __('<em>Not Available Yet.</em> If you use SSL on your site, and you are getting messages about insecure images (or they do not display), check this option.', 'amazon-product-in-a-post-plugin')."<br /><br />",
	    		"id" => $shortname_apipp."_ssl_images",
	    		"type" => "checkbox"),
		
		array(	"name" => __('Hook plugin into Excerpt?', 'amazon-product-in-a-post-plugin'),
				"desc" => __('If you want to have the product displayed when the <code>the_excerpt()</code> function is called, select this box. Disable this function if your theme uses short excerpts on pages, such as the home page. You can override this on each individual page/post.', 'amazon-product-in-a-post-plugin')."<br /><br />",
	    		"id" => $shortname_apipp."_hook_excerpt",
	    		"type" => "checkbox"),
		
		array(	"name" => __('Hook plugin into Content?', 'amazon-product-in-a-post-plugin'),
				"desc" => __('If you want to have the product displayed when the <code>the_content()</code> function is called, select this box. NOTE: This is the standard call - if you disable both Excerpt and Content, the only way you can add products to a page/post is to add the shortcode (<code>[AMAZONPRODUCT=XXXXXX]</code> where XXXXXX is the ASIN). You can override this on each individual page/post.', 'amazon-product-in-a-post-plugin')."<br /><br />",
	    		"id" => $shortname_apipp."_hook_content",
	    		"type" => "checkbox"),
		
		array(	"name" => __('Quick Fix - Hide Warnings?', 'amazon-product-in-a-post-plugin'),
				"desc" => '<span style="color:red;font-weight:bold;">'.__('IMPORTANT MESSAGE:', 'amazon-product-in-a-post-plugin').'</span> '.__('Checking this box will excecute the code, <code>ini_set("display_errors", 0); </code> to force stop WARNING messages. This can be helpful if your server php configuration has error reporting on and you are getting warning messages. This WILL override any setting you have in your php.ini or php config files. It is not recommended you turn this on unless you need it.', 'amazon-product-in-a-post-plugin') ."<br /><br />",
	    		"id" => $shortname_apipp."_hide_warnings_quickfix",
	    		"type" => "checkbox"),
	
		array(	"name" => __('Uninstall when deactivated?', 'amazon-product-in-a-post-plugin'),
				"desc" => "<span style='color:red;font-weight:bold;'>".__('BE VERY CAREFUL WITH THIS!!', 'amazon-product-in-a-post-plugin')."</span> ".__('Checking this box will delete ALL settings and database items when you deativate the plugin', 'amazon-product-in-a-post-plugin')."<br /><br />",
	    		"id" => $shortname_apipp."_uninstall",
	    		"type" => "checkbox"),

		array(	"name" => __('Remove ALL traces when uninstalled?', 'amazon-product-in-a-post-plugin'),
				"desc" => "<span style='color:red;font-weight:bold;'>".__('BE VERY CAREFUL WITH THIS!!', 'amazon-product-in-a-post-plugin')."</span> ".__('Checking this box AND the above box will delete <em>ALL</em> Amazon shortcodes from posts and pages, and all meta data associated with this plugin will be cleaned up and cleared out when you deativate this plugin. As a safety precaution, both boxes must be checked or data will not be removed.', 'amazon-product-in-a-post-plugin')."<br /><br />",
	    		"id" => $shortname_apipp."_uninstall_all",
	    		"type" => "checkbox"),

		array(	"name" => __('Open Product Link in New Window?', 'amazon-product-in-a-post-plugin'),
				"desc" => __('If you want to have the product displayed in a new window, check this box. Default is no.', 'amazon-product-in-a-post-plugin')."<br /><br />",
	    		"id" => $shortname_apipp."_open_new_window",
	    		"type" => "checkbox"),
		
		array(	"name" => __('Show on Single Page Only?', 'amazon-product-in-a-post-plugin'),
				"desc" => __('If you want to have the product displayed only when the page/post is singular, check this box. Default is no.', 'amazon-product-in-a-post-plugin')."<br /><br />",
	    		"id" => $shortname_apipp."_show_single_only",
	    		"type" => "checkbox"),
				
		array(	"name" => __('API get method', 'amazon-product-in-a-post-plugin'),
				"desc" => __('If you are seeing BLANK products it may be because your server does not support the php file_get_contents() function. If that is the case, try CURL option to see if it resolves the problem. Default is File Get Contents method.', 'amazon-product-in-a-post-plugin')."<br /><br />",
	    		"id" => $shortname_apipp."_API_call_method",
	    		"type" => "select",
	    		"options" => array(
	    			"0" => array("value" => "0","text" => "file_get_contents() (default)"),
	    			"1" => array("value" => "1","text" => "CURL"),
	    		 )),
				 
		array(	"name" => __('Use My Custom Styles?', 'amazon-product-in-a-post-plugin'),
				"desc" => __('If you want to use your own styles, check this box and enter them below. <br/>Additionally, you can put your own styles in a CSS file called <code>appip-styles.css</code> located at: ', 'amazon-product-in-a-post-plugin').'<br/><code>'.get_bloginfo('stylesheet_directory').'/appip-styles.css</code> or <code>'.get_bloginfo('stylesheet_directory')."/css/appip-styles.css</code><br /><br />",
	    		"id" => $shortname_apipp."_product_styles_mine",
	    		"type" => "checkbox"),
		array(	"name" => __('Product Styles', 'amazon-product-in-a-post-plugin'),
				"desc" => __('Your Custom styles can go here. To reset the styles, remove all styles from textarea and then save the options - the default styles will be loaded.', 'amazon-product-in-a-post-plugin')."<br /><br />",
	    		"id" => $shortname_apipp."_product_styles",
	    		"type" => "textareabig"),
	);

// Functions
	function apipp_options_add_subpage(){
		global $fullname_apipp, $shortname_apipp, $options_apipp;
		apipp_options_admin_page($fullname_apipp, $shortname_apipp, $options_apipp);
	}
	
	function apipp_options_add_admin_page($themename,$shortname,$options) {
	$up_opt='';
	    if ( basename(__FILE__) == 'amazon-product-in-a-post-options.php' ) {
	    	if(isset($_REQUEST['action'])){$req_action=$_REQUEST['action'];}else{$req_action='';}
		    if(isset($_REQUEST[$shortname.'_option'])){$req_option=$_REQUEST[$shortname.'_option'];}else{$req_option='';}
	        if ( 'save' == $req_action && $req_option== $shortname ) {
	                foreach ($options as $value) {
						if($value['type'] == 'multicheck'){
							foreach($value['options'] as $mc_key => $mc_value){
								$up_opt = $value['id'].'_'.$mc_key;						
								if( isset( $_REQUEST[ $up_opt ] ) ) { 
									update_option( $up_opt, $_REQUEST[ $up_opt ]  ); 
									$update_optionapp = $_REQUEST[ $up_opt ];
								} else { 
									delete_option( $up_opt );
									$update_optionapp=''; 
								} 
							}
						}elseif($value['type'] == 'select'){
							foreach($value['options'] as $mc_key => $mc_value){
								$up_opt = $value['id'];	
								if( isset( $_REQUEST[ $up_opt ] ) && ($_REQUEST[ $up_opt ] == $mc_value['value']) ) { 
									update_option( $value['id'], $mc_value['value']); 
								} 
							}
						}else{
	                    	if( isset( $_REQUEST[ $value['id'] ] ) ) { 
								if( $value['id'] == 'apipp_API_call_method' ){
									if($_REQUEST[ $value['id'] ] == '0'){
										update_option('appip_amazon_usefilegetcontents',1);
										update_option('appip_amazon_usecurl',0);
									}else{
										update_option('appip_amazon_usefilegetcontents',0);
										update_option('appip_amazon_usecurl',1);
									}
								}else{
									update_option( $value['id'], $_REQUEST[ $value['id'] ]  ); 
								}
	                    	} else {
	                    		delete_option( $value['id'] );
	                    	} 
						}
					}
	                wp_redirect("admin.php?page=".$shortname."_plugin_admin&saved=true",302);
	                die;
	
	        } else if( isset($_REQUEST['action']) && isset($_REQUEST[$shortname.'_option']) && 'reset' == $_REQUEST['action'] && $_REQUEST[$shortname.'_option']== $shortname ) {
	
	            foreach ($options as $value) {
					if($value['type'] != 'multicheck'){
	                	delete_option( $value['id'] ); 
					}else{
						foreach($value['options'] as $mc_key => $mc_value){
							$del_opt = $value['id'].'_'.$mc_key;
							delete_option($del_opt);
						}
					}
				}
	            wp_redirect("admin.php?page=".$shortname."_plugin_admin&reset=true",302);
	            die;
	
	        }
	    }
	}
	
	function apipp_options_admin_page($themename, $shortname, $options) {
		global $public_key,$private_key;
		global $appuninstall;
		global $thedefaultapippstyle;
		global $appuninstallall;
		if ( get_option('apipp_product_styles') == ''){update_option('apipp_product_styles',$thedefaultapippstyle);}
		if (isset($_REQUEST['dismissmsg']) && $_REQUEST['dismissmsg'] == '1'){update_option('appip_dismiss_msg',1);echo '<div id="message" class="updated fade"><p><strong>'.$themename.' message dismissed.</strong></p></div>';}
	    if (isset($_REQUEST['saved']) && $_REQUEST['saved'] ) echo '<div id="message" class="updated fade"><p><strong>'.$themename.' settings saved.</strong></p></div>';
	    if (isset($_REQUEST['reset']) &&  $_REQUEST['reset'] ) echo '<div id="message" class="updated fade"><p><strong>'.$themename.' settings reset.</strong></p></div>';
	?>
	<div class="wrap"><div id="icon-amazon" class="icon32"><br /></div>
	<style type="text/css">
		table.optiontable { max-width: 100%;width: 100% !important;}
		.option-th{text-align:left;max-width: 240px;width: 25%;}
		.small,small{font-size:13px;color:#777;line-height: 19px;}
		.style-text-sm{font-size: 13px;max-width: 400px;height: 100px;line-height: 19px;width: 90%;}
		.style-text{font-size: 13px;max-width: 650px;height: 500px;line-height: 19px;width: 90%;}
		.width150{width:150px;}
		.width200{width:200px;}
		.width250{width:250px;}
		.width300{width:300px;}
		.width350{width:350px;}
		.width400{width:400px;}
		@media screen and (max-width:600px){
			small,.small{padding-left: 2px;display: block;}
			select,input[type="text"],input[type="textlong"],input[type="password"],text-area{width:100% !important;max-width:100%;}
			.width150,.width200,.width250,.width300,.width350,.width400{width:100% !important; max-width:100% !important;}
			table.optiontable th{text-align: left;width: 100%;display: block;padding: 6px 0 5px 4px;border-top: 1px solid #fff;margin-top: 10px;max-width: 100%;}
			table.optiontable td{display:block;}
			table.optiontable td.dnone{display:none;}
		}
    </style>
	<h2><?php echo $themename; ?> options</h2>

	<form method="post" action="">
	<input type="hidden" name="<?php echo $shortname; ?>_option" id="<?php echo $shortname; ?>_option" value="<?php echo $shortname; ?>" />
	<table class="optiontable">
	<?php foreach ($options as $key => $value) { 
		switch ( $value['type'] ) {
			case 'text':
			apipp_option_wrapper_header($value);
			$width = isset($value['width']) && $value['width']!= '' ? 'width'.$value['width'] : 'width300';
			?>
			        <input class="<?php echo $width;?>" name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>" type="<?php echo $value['type']; ?>" value="<?php if ( get_option( $value['id'] ) != "") { echo get_option( $value['id'] ); } else { echo $value['std']; } ?>" />
			<?php
			apipp_option_wrapper_footer($value);
			break;
			case 'password':
			apipp_option_wrapper_header($value);
			$width = isset($value['width']) && $value['width']!= '' ? 'width'.$value['width'] : 'width300';
			?>
			        <input class="<?php echo $width;?>" name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>" type="<?php echo $value['type']; ?>" value="<?php if ( get_option( $value['id'] ) != "") { echo get_option( $value['id'] ); } else { echo $value['std']; } ?>" /> <a href="#" onclick="var element = document.getElementById('<?php echo $value['id']; ?>');element.type='text';return false;">show</a>
			<?php
			apipp_option_wrapper_footer($value);
			break;
			case 'textlong':
			apipp_option_wrapper_header($value);
			?>
			        <input style="width:95%;" name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>" type="<?php echo $value['type']; ?>" value="<?php if ( get_option( $value['id'] ) != "") { echo get_option( $value['id'] ); } else { echo $value['std']; } ?>" />
			<?php
			apipp_option_wrapper_footer($value);
			break;
			
			case 'select':
			apipp_option_wrapper_header($value);
			?>
		            <select style="width:240px;" name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>">
		                <?php foreach ($value['options'] as $option) { ?>
		                <option<?php if ( get_option( $value['id'] ) == $option["value"]) { echo ' selected="selected"'; } elseif (isset($value['std']) && $option["value"] == $value['std']) { echo ' selected="selected"'; } ?> value="<?php echo $option["value"]; ?>"><?php echo $option["text"]; ?></option>
		                <?php } ?>
		            </select>
			<?php
			apipp_option_wrapper_footer($value);
			break;
			
			case 'cat_select':
				apipp_option_wrapper_header($value);
				$categories = get_categories('hide_empty=0');
				?>
			            <select style="width:240px;" name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>">
							<?php foreach ($categories as $cat) {
							if ( get_option( $value['id'] ) == $cat->cat_ID) { $selected = ' selected="selected"'; } else { $selected = ''; }
							$opt = '<option value="' . $cat->cat_ID . '"' . $selected . '>' . $cat->cat_name . '</option>';
							echo $opt; } ?>
			            </select>
				<?php
				apipp_option_wrapper_footer($value);
				break;
			
			case 'textarea':
				//$ta_options = $value['options'];
				apipp_option_wrapper_header($value);
				?>
						<textarea name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>" class="style-text-sm"><?php 
						if( get_option($value['id']) != "") {
								echo stripslashes(get_option($value['id']));
							}else{
								echo $value['std'];
						}?></textarea>
				<?php
					apipp_option_wrapper_footer($value);
				break;
			
			case 'textareabig':
				//$ta_options = $value['options'];
				apipp_option_wrapper_header($value);
				?>
						<textarea name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>" class="style-text"><?php 
						if( get_option($value['id']) != "") {
								echo stripslashes(get_option($value['id']));
							}else{
								echo $value['std'];
						}?></textarea>
				<?php
				apipp_option_wrapper_footer($value);
				break;
	
			case "radio":
				apipp_option_wrapper_header($value);
		 		foreach ($value['options'] as $key=>$option) { 
						$radio_setting = get_option($value['id']);
						if($radio_setting != ''){
				    		if ($key == get_option($value['id']) ) {
								$checked = "checked=\"checked\"";
								} else {
									$checked = "";
								}
						}else{
							if($key == $value['std']){
								$checked = "checked=\"checked\"";
							}else{
								$checked = "";
							}
						}?>
			            <input type="radio" name="<?php echo $value['id']; ?>" value="<?php echo $key; ?>" <?php echo $checked; ?> /><?php echo $option; ?><br />
				<?php 
				}
				 
				apipp_option_wrapper_footer($value);
				break;
			
			case "checkbox":
				apipp_option_wrapper_header($value);
								if(get_option($value['id'])){
									$checked = "checked=\"checked\"";
								}else{
									$checked = "";
								}
							?>
				            <input type="checkbox" name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>" value="true" <?php echo $checked; ?> />
				<?php
				apipp_option_wrapper_footer($value);
				break;
	
			case "multicheck":
				apipp_option_wrapper_header($value);
				
		 		foreach ($value['options'] as $key=>$option) {
			 			$pn_key = $value['id'] . '_' . $key;
						$checkbox_setting = get_option($pn_key);
						if($checkbox_setting != ''){
				    		if (get_option($pn_key) ) {
								$checked = "checked=\"checked\"";
								} else {
									$checked = "";
								}
						}else{
							if($key == $value['std']){
								$checked = "checked=\"checked\"";
							}else{
								$checked = "";
							}
						}?>
			            <input type="checkbox" name="<?php echo $pn_key; ?>" id="<?php echo $pn_key; ?>" value="true" <?php echo $checked; ?> /><label for="<?php echo $pn_key; ?>"><?php echo $option; ?></label><br />
				<?php 
				}
				 
				apipp_option_wrapper_footer($value);
				break;
			
			case "heading":
				?>
				<tr valign="top"> 
				    <td colspan="2" style="text-align: left;"><h2><?php echo $value['name']; ?></h2><br/></td>
				</tr>
				<?php
				break;
			
			default:
				break;
		}
	}
	?>
	
	</table>
	<p class="submit"><input name="save" type="submit" value="Save changes" class="button-primary" /><input type="hidden" name="action" value="save" /></p>
	</form>
	<?php
	}
	
	function apipp_option_wrapper_header($values){
		?>
		<tr valign="top"> 
		    <th scope="row" class="option-th"><?php echo $values['name']; ?>:</th>
		    <td>
		<?php
	}
	
	function apipp_option_wrapper_footer($values){
		?>
		    </td>
		</tr>
		<tr valign="top">
			<td class="dnone">&nbsp;</td><td valign="top"><small><?php echo $values['desc']; ?></small></td>
		</tr>
		<?php 
	}
$thedefaultapippstyle='/*version 2.0*/
a[target="amazonwin"] {margin: 0 !important;}
a[rel^="appiplightbox"] { display: inline-block; font-size: .75rem; text-align: center; max-width: 100%; }
.amazon-product-table { border-collapse: collapse; border: 0 none !important; width: 100%; }
.amazon-product-table td, table.amazon-product-table td { border: 0 none ; padding: 0; }
.amazon-image-wrapper { padding: 0 1%; text-align: center;float: left; margin: 0 2% 0 0;-webkit-box-sizing: border-box;-moz-box-sizing: border-box; box-sizing: border-box; max-width: 25%; width: 100%; }
.amazon-image-wrapper a { border-bottom: none; display: block; font-size: 12px; text-align: center; }
.amazon-image-wrapper br {display: none;}
.amazon-product-table hr {display: block;}
span.amazon-tiny {font-size: 10px;}
div.amazon-buying {text-align: left;}
h2.amazon-asin-title {margin: 0 0 5px 0; }
span.asin-title {text-align: left;  font-size: inherit;}
span.amazon-author { color: #666; }
span.amazon-starring-label { color: #999; }
span.amazon-director-label { color: #999; }
span.amazon-rating-label { color: #999; }
span.amazon-starring { color: #666; }
.amazon-manufacturer { color: #666; }
.amazon-ESRB { color: #666; font-size : 12px;}
.amazon-feature { color: #666; font-size : 12px;}
.amazon-platform { color: #666; font-size : 12px;}
.amazon-system { color: #666; font-size : 12px;}
span.amazon-starring { color: #666; }
span.amazon-director { color: #666; }
span.amazon-rating { color: #666; }
.amazon-product-price { border-collapse: collapse; border: 0 none; padding: 0 !important; }
.amazon-product-price a img.amazon-image { background-color: transparent; border: 0 none; }
.amazon-post-text { padding: 0 !important; text-align: left; }
.appip-label {color: #666; font-size: inherit;font-weight: bold;text-transform: uppercase;}
.amazon-list-price-label { color: #666; text-align: left; }
.amazon-list-price { text-align: left; text-decoration: line-through; }
.amazon-price-label { color: #666; text-align: left;  }
.amazon-price { color: #800000; font-weight: bold; text-align: left; }
.amazon-new-label { color: #666; text-align: left;}
.amazon-new { color: #800000; font-weight: bold; text-align: left; }
.amazon-used-label { color: #666; text-align: left; }
.amazon-used { color: #666; text-align: left; }
div.amazon-dates { padding: 0 !important; text-align: left; }
div.amazon-dates span.amazon-preorder { color: #d16601; font-weight: bold; text-align: left; }
div.amazon-dates span.amazon-release-date { color: #666; font-size: 10px; text-align: left; }
span.instock { color: #008000; font-size: .85em; }
span.outofstock { color: #800000; font-size: .85em; }
div.appip-multi-divider {margin: 10px 0;}
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
@media only screen and (max-width : 320px) {}
';