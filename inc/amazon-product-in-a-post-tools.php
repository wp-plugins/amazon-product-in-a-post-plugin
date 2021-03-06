<?php
// Tools
global $appipBulidBox;
//ACTIONS
	add_action( 'init', 'apipp_parse_new',100); 
	//add_action( 'admin_menu', create_function("$appipBulidBox","if( function_exists( 'add_meta_box' ))add_meta_box( 'amazonProductInAPostBox1', __( 'Amazon Product In a Post Settings', 'amazon-product-in-a-post-plugin' ), 'amazonProductInAPostBox1', 'post', 'normal', 'high' );"));
	//add_action( 'admin_menu', create_function("$appipBulidBox","if( function_exists( 'add_meta_box' ))add_meta_box( 'amazonProductInAPostBox1', __( 'Amazon Product In a Post Settings', 'amazon-product-in-a-post-plugin' ), 'amazonProductInAPostBox1', 'page', 'normal', 'high' );"));
	add_action( 'admin_menu', 'apipp_plugin_menu');
	add_action( 'network_admin_notices', 'appip_warning_notice');
	add_action( 'admin_notices', 'appip_warning_notice');
	add_filter( 'contextual_help', 'appip_plugin_help', 10, 3);

//FUNCTIONS
	function add_appip_meta_posttype_support($posttypes = array()){
		if( function_exists( 'add_meta_box' ) && function_exists( 'amazonProductInAPostBox1' )){
			if(is_array($posttypes) && !empty($posttypes)){
				foreach($posttypes as $key => $type){
					add_meta_box( 'amazonProductInAPostBox_'.$type, __( 'Amazon Product In a Post Settings', 'amazon-product-in-a-post-plugin' ), 'amazonProductInAPostBox1', $type , 'normal', 'high' );
				}
			}
		}
	}
	function appip_post_type_support(){
		add_appip_meta_posttype_support(array('page','post'));	
	}
	add_filter( 'admin_menu', 'appip_post_type_support');
	
	function appip_post_type_support_product(){
		add_appip_meta_posttype_support(array('product'));	
	}
	add_filter( 'admin_menu', 'appip_post_type_support_product');
	
	/*
	to add your own type add via filters:
		function appip_post_type_support_product(){
			add_appip_meta_posttype_support(array('product'));	
		}
		add_filter( 'admin_menu', 'appip_post_type_support_product');
	*/
	
	function appip_plugin_help($contextual_help, $screen_id, $screen) {
		$plugin_donate = 0;
		switch($screen_id){
			case 'toplevel_page_apipp-main-menu':
				$contextual_help = __('Amazon PIP Options Contextual Help Coming Soon.','amazon-product-in-a-post-plugin');
				$plugin_donate = 1;
				break;
			case 'amazon-product_page_apipp-add-new':
				$contextual_help = __('New Product Contextual Help Coming Soon.','amazon-product-in-a-post-plugin');
				$plugin_donate = 1;
				break;
			case 'amazon-product_page_apipp-main-menu':
				$contextual_help = __('Amazon PIP Options Contextual Help Coming Soon.','amazon-product-in-a-post-plugin');
				$plugin_donate = 1;
				break;
			case 'amazon-product_page_apipp_plugin_admin':
				$contextual_help = __('Options Contextual Help Coming Soon.','amazon-product-in-a-post-plugin');
				$plugin_donate = 1;
				break;
			case 'amazon-product_page_apipp_plugin-shortcode':
				$contextual_help = __('Shortcode Usage Contextual Help Coming Soon.','amazon-product-in-a-post-plugin');
				$plugin_donate = 1;
				break;
			case 'amazon-product_page_apipp_plugin-faqs':
				$contextual_help = __('FAQs/Help Contextual Help Coming Soon.','amazon-product-in-a-post-plugin');
				$plugin_donate = 1;
				break;
			case 'amazon-product_page_apipp-cache-page':
				$contextual_help = __('Product Cache Contextual Help Coming Soon.','amazon-product-in-a-post-plugin');
				$plugin_donate = 1;
				break;
			}
		if($plugin_donate == 1){
			$screen->add_help_tab( array('id'=> 'appip_aboutus','title'=> __('About Us', 'amazon-product-in-a-post-plugin'),'content'=> '<p>'.__('Fischer Creative Media, LLC develops custom WordPress Themes and Plugins for clients who need a more individualized look, but still want the simplicity of a WordPress website.', 'amazon-product-in-a-post-plugin')));
			$screen->set_help_sidebar(
				'<p><strong>' . __('For more information:', 'amazon-product-in-a-post-plugin') . '</strong></p>' .
				'<p><a href="http://www.fischercreativemedia.com/donations/" target="_blank">' . __('Donate to this Plugin', 'amazon-product-in-a-post-plugin') . '</a></p>' .
				'<p><a href="http://www.fischercreativemedia.com/wordpress-plugins/" target="_blank">' . __('See Our Other WordPress Plugins', 'amazon-product-in-a-post-plugin') . '</a></p>'
			);
		}
		return $contextual_help;
	}
	function appip_warning_notice(){
		if( isset($_REQUEST['dismissmsg']) && $_REQUEST['dismissmsg'] == '1'){update_option('appip_dismiss_msg',1);}
		$appip_publickey 	= get_option('apipp_amazon_publickey');
		$appip_privatekey 	= get_option('apipp_amazon_secretkey');
		$appip_partner_id 	= get_option('apipp_amazon_associateid');
		$appip_dismiss 		= get_option('appip_dismiss_msg',0);
		if($appip_publickey =='' || $appip_privatekey ==''){
			echo '<div class="error"><h2><strong>'.__('Amazon Product in a Post Important Message!', 'amazon-product-in-a-post-plugin').'</strong></h2><p>'.__('Please note: You need to add your Access Key ID and Secrect Access Key to the <a href="admin.php?page=apipp_plugin_admin">options page</a> before the plugin will display any Amazon Products!', 'amazon-product-in-a-post-plugin').'</p></div>';
		}elseif($appip_partner_id =='' && $appip_dismiss == 0){
			echo '<div class="error"><h2><strong>'.__('Amazon Product in a Post Important Message!', 'amazon-product-in-a-post-plugin').'</strong></h2><p>'.__('You need to enter your Amazon Partner ID in order to get credit for any products sold. <a href="admin.php?page=apipp_plugin_admin">enter your partner id here</a> or you can <a href="admin.php?page=apipp_plugin_admin&dismissmsg=1">dismiss this message</a>', 'amazon-product-in-a-post-plugin').'</p></div>';
		}
	}
	function apipp_parse_new(){ //Custom Save Post items for Quick Add
		if(isset($_POST['createpost'])){ //form saved
			global $post;
			global $aws_partner_id;
			$teampappcats = array();
			$totalcategories 	= isset($_POST['post_category_count']) ? absint($_POST['post_category_count']) : 0;
			$post_stat 			= (isset($_POST['post_status']) ? sanitize_text_field($_POST['post_status']) :'draft');
			$post_type 			= (isset($_POST['post_type']) ? sanitize_text_field($_POST['post_type']): 'post');
			$allowed_tags 		= wp_kses_allowed_html( $post_type );
			$ASIN 				= isset($_POST['amazon-product-single-asin']) ? sanitize_text_field($_POST['amazon-product-single-asin']) : '';
			$amzArr				= array();
			$amzreq				= '';
			$params	= array(
				"ItemId" 		=> $ASIN,
				"Operation" 	=> "ItemLookup",
				"ResponseGroup" => "Large,Reviews,VariationSummary",
				"IdType" 		=> "ASIN",
				"AssociateTag" 	=> $aws_partner_id
			);
			if($ASIN != '')
				$amzreq = amazon_plugin_aws_signed_request('', $params, '', '');
			
			if($amzreq !='')
				$amzArr = appip_plugin_FormatASINResult($amzreq,0);
			
			$tempContentS = isset($amzArr[0]['ItemDesc'][0]['Source']) ? $amzArr[0]['ItemDesc'][0]['Source'] : '';
			$tempContentT = (isset($amzArr[0]['ItemDesc'][0]['Content']) && $tempContentS == 'Product Description') ? $amzArr[0]['ItemDesc'][0]['Content'] : '';
			
			//uses Amazon Title & Content if Title & COntent is empty.
			$temptitle		= (isset($amzArr[0]['Title'])) ? $amzArr[0]['Title'] : 'title did not work';
			$postTitle 		= (isset($_POST['post_title']) && $_POST['post_title']!='') ? sanitize_text_field($_POST['post_title']) : $temptitle;
			$postContent 	= (isset($_POST['post_content']) && $_POST['post_content'] !='') ? wp_kses($_POST['post_content'],$allowed_tags) : (($tempContentT != '') ? wp_kses($tempContentT,$allowed_tags) : 'content did not work');
			if(isset($_POST['post_category'][$post_type])){
				foreach($_POST['post_category'][$post_type] as $key => $val){
					$post_array = array(
						'post_author' 	=> (isset($_POST['post_author']) ? absint($_POST['post_author']) : ''),
					    'post_title' 	=> $postTitle,
						'post_status' 	=> $post_stat,
						'post_type' 	=> $post_type,
					    'post_content' 	=> $postContent,
					);
					$createdpostid = wp_insert_post($post_array);
					$val = array_unique( array_map( 'intval', $val ) );
					$tesrr = wp_set_post_terms($createdpostid,$val,$key,false);
				}
			}else{
				$post_array = array(
					'post_author' 	=> sanitize_text_field($_POST['post_author']),
				    'post_title' 	=> $postTitle,
				    'post_status' 	=> $post_stat,
				    'post_type' 	=> $post_type,
				    'post_content' 	=> $postContent,
				    'post_parent' 	=> 0,
				    'post_category' => ''
				);
				$createdpostid = wp_insert_post($post_array,'false');
			}
			if($createdpostid != ''){
				$newpost = get_post($createdpostid);
				ini_set('display_errors', 0);
				amazonProductInAPostSavePostdata($createdpostid,$newpost);
				header("Location: admin.php?page=apipp-add-new&appmsg=1");
				exit();
			}else{
				header("Location: admin.php?page=apipp-add-new&appmsg=2");
				exit();
			}
		}else{
			add_action('save_post', 'amazonProductInAPostSavePostdata', 1, 2); // save the custom fields
		}
		if( isset( $_GET['appip_debug'] ) && ( $_GET['appip_debug'] == get_option('apipp_amazon_debugkey') && get_option('apipp_amazon_debugkey') !='')){
			global $degunningAPPIP;
			global $wpdb;
			global $aws_plugin_version;
			$debugkey = get_option('apipp_amazon_debugkey');
			$siteKey = get_bloginfo('url').'/?appip_debug='.$debugkey;
			$addlCheck = md5($siteKey);
			$checkok = false;
			if(isset($_GET['keycheck']) && $_GET['keycheck'] == $addlCheck){
				$checkok = true;
			}
			if(!$checkok){
				wp_die('No Permission','You do not have permission to access this page.');	
				exit;
			}
			$degunningAPPIP = true;
			echo '<h1>Amazon Plugin Debug</h1>';
			$checksql= "SELECT Body, ( NOW() - Updated ) as Age FROM ".$wpdb->prefix."amazoncache ORDER BY Updated DESC;";
			$result = $wpdb->get_results($checksql);
			if(!empty($result)){
				echo '<h2>Amazon Product CACHE</h2>';
				foreach($result as $psxml){
					echo '<pre>';
					echo '[Body]: '.htmlspecialchars($psxml->Body).'<br/>';
					echo '[Age]: '. $psxml->Age.'<br/>';
					echo '</pre>';
				}
			}
			echo '<div style="border:1px solid #cccccc; padding:10px; margin:10px 0;font-family:courier; font-size;12px;">';
			echo '<h2>Amazon Variables</h2>';
			echo '$aws_plugin_version: '.$aws_plugin_version.'<br/>';
			echo '$public_key: '. get_option('apipp_amazon_publickey').'<br/>'; 
			//echo '$private_key: '. get_option('apipp_amazon_secretkey').'<br/>'; 
			echo '$aws_partner_id: '. get_option('apipp_amazon_associateid').'<br/>';
			echo '$apipphookexcerpt: '. get_option('apipp_hook_excerpt').'<br/>'; 
			echo '$apipphookcontent: '.get_option('apipp_hook_content').'<br/>'; 
			echo '$apippopennewwindow: '.get_option('apipp_open_new_window').'<br/>'; 
			echo '$apip_getmethod: '. get_option('apipp_API_call_method').'<br/>';
			echo '$encodemode: '.get_option('appip_encodemode').'<br/>'; 
			echo '</div>';
			echo '<div style="border:1px solid #cccccc; padding:10px; margin:10px 0;">';
			echo '<h2>PHP Info</h2>';
			phpinfo();
			echo '</div>';
			exit;
		}
	}
	
	/* Prints the inner fields for the custom post/page section */
	function amazonProductInAPostBox1() {
		global $post;
		global $aws_partner_locale;
		// Use nonce for verification ... ONLY USE ONCE!
		$appASIN		= get_post_meta($post->ID, 'amazon-product-single-asin', true);
		$appnewwin 		= get_post_meta($post->ID, 'amazon-product-newwindow', true);
		$appsingle 		= get_post_meta($post->ID, 'amazon-product-singular-only', true);
		$appnewinO 		= get_option('apipp_open_new_window') == true ? 1 : 0;
		$apphookO		= get_option('apipp_hook_excerpt') == true ? 1 : 0;
		$apphook		= get_post_meta($post->ID, 'amazon-product-excerpt-hook-override', true);
		$appcont		= get_post_meta($post->ID, 'amazon-product-content-hook-override', true);
		$appcontO 		= get_option('apipp_hook_content') == true ? 1 : 0 ;
		$appactive		= get_post_meta($post->ID, 'amazon-product-isactive', true);
		$appaffidO		= get_option('apipp_amazon_associateid');
		$appnoonce 		= wp_create_nonce( plugin_basename(__FILE__) );
		$appconloc 		= get_post_meta($post->ID, 'amazon-product-content-location', true);
		$amazondesc 	= get_post_meta($post->ID, 'amazon-product-amazon-desc', true);
		$amazongallery 	= get_post_meta($post->ID, 'amazon-product-show-gallery', true);
		$amazonfeatures = get_post_meta($post->ID, 'amazon-product-show-features', true);
		$amazontstamp 	= get_post_meta($post->ID, 'amazon-product-timestamp', true);
		$appipnewtitle 	= get_post_meta($post->ID, 'amazon-product-new-title', true);
		$amazonused 	= get_post_meta($post->ID, 'amazon-product-show-used-price', true);
		$amazonlist 	= get_post_meta($post->ID, 'amazon-product-show-list-price', true);
		$amazonsaved 	= get_post_meta($post->ID, 'amazon-product-show-saved-amt', true);
		
		$menuhide		= ($appactive != '') ? ' checked="checked"' : '' ;
		$hookcontent	= ($appcont=='2' || ($appcont == '' && $appcontO)) ? ' checked="checked"' : "" ;
		$hookexcerpt	= ($apphook == '2' || ($apphook == '' && $apphookO)) ? ' checked="checked"' : "" ;
		$singleonly		= ($appsingle == '1') ? ' checked="checked"' : "";
		$newwin 		= ($appnewwin == '2' || ($appnewwin == '' && $appnewinO) ) ? ' checked="checked"' : "" ;
		$amazontstamp 	= $amazontstamp != '' ? ' checked="checked"' : '';
		$amazondesc 	= $amazondesc != '' ? ' checked="checked"' : '';
		$amazongallery 	= $amazongallery != '' ? ' checked="checked"' : '';
		$amazonfeatures = $amazonfeatures != '' ? ' checked="checked"' : '';
		$amazonused 	= $amazonused != '' ? ' checked="checked"' : '';
		$amazonlist 	= $amazonlist != '' ? ' checked="checked"' : '';
		$amazonsaved 	= $amazonsaved != '' ? ' checked="checked"' : '';
		if($appconloc==='3'){
			$appeampleimg = 'example-layout-3.png';
		}elseif($appconloc==='2'){
			$appeampleimg = 'example-layout-2.png';
		}else{
			$appeampleimg = 'example-layout-1.png';
		}
		
		$noaffidmsg = '<div style="background-color: rgb(255, 251, 204);" id="message" class="updated fade below-h2"><p><strong>'. __('WARNING:','amazon-product-in-a-post-plugin', 'amazon-product-in-a-post-plugin').'</strong> '.__('You will not get credit for Amazon purchases until you add your Amazon Affiliate ID on the <a href="admin.php?page=apipp_plugin_admin">options</a> page.','amazon-product-in-a-post-plugin').'</p></div>';
		if($appaffidO == ''){ echo $noaffidmsg;}
		echo '<p><input type="checkbox" name="amazon-product-isactive" value="1" '.$menuhide.' /> <label for="amazon-product-isactive"><strong>' . __("Product is Active?", 'amazon-product-in-a-post-plugin' ) . '</strong></label> <em>'.__('if checked the product will be live','amazon-product-in-a-post-plugin').'</em></p>';
		echo '<p><label for="amazon-product-single-asin"><strong>'.__("Amazon Product ASIN", 'amazon-product-in-a-post-plugin' ).'</strong></label><br /><input type="text" name="amazon-product-single-asin" id="amazon-product-single-asin" size="25" value="'. $appASIN . '" /><em>'. __('You will need to get this from <a href="http://amazon.com/">Amazon.com</a>','amazon-product-in-a-post-plugin').'</em></p>';
		echo '<p><label for="amazon-product-new-title"><strong>'.__("Replace Amazon Title With Below Title:", 'amazon-product-in-a-post-plugin' ).'</strong></label> <em>'. __('Optional. To hide title all together, type "null". No HTML, plain text only. Use this if you want your own title to show instead of Amazon\'s title.','amazon-product-in-a-post-plugin').'</em><input type="text" class="amazon-product-new-title" name="amazon-product-new-title" id="amazon-product-new-title" size="35" value="'. $appipnewtitle. '" /></p>';
		echo '<input type="hidden" name="amazonpipp_noncename" id="amazonpipp_noncename" value="' . $appnoonce . '" /><input type="hidden" name="post_save_type_apipp" id="post_save_type_apipp" value="1" />';
		echo '<p><input type="checkbox" name="amazon-product-content-hook-override" value="2" '.$hookcontent.' /> <label for="amazon-product-content-hook-override"><strong>' . __("Hook into Content?", 'amazon-product-in-a-post-plugin' ) . '</strong></label> <em>'.__('Product will show when full content is used (when <code>the_content()</code> template tag). On by default.','amazon-product-in-a-post-plugin').'</em></p>';
		echo '<p><input type="checkbox" name="amazon-product-excerpt-hook-override" value="2" '.$hookexcerpt.' /> <label for="amazon-product-excerpt-hook-override"><strong>' . __("Hook into Excerpt?", 'amazon-product-in-a-post-plugin' ) . '</strong></label> <em>'.__('Product will show when partial excerpt content is used(when <code>the_excerpt()</code> is used. Off by default.','amazon-product-in-a-post-plugin').'</em></p>';
		echo '<p><input type="checkbox" name="amazon-product-singular-only" value="1" '.$singleonly.' /> <label for="amazon-product-singular-only"><strong>' . __("Show Only on Single Page?", 'amazon-product-in-a-post-plugin' ) . '</strong></label> <em>'.__('if checked the product will only show when on single page. Off by default.','amazon-product-in-a-post-plugin').'</em></p>';
		echo '<p><input type="checkbox" name="amazon-product-newwindow" value="2" '.$newwin.' /> <label for="amazon-product-newwindow"><strong>' . __("Open Product Link in New Window?", 'amazon-product-in-a-post-plugin' ) . '</strong></label> <em>'.__('if checked the product will open a new browser window. Off by default unless set in options.','amazon-product-in-a-post-plugin').'</em></p>';
		echo '<div class="appip-example-image"><img data="'.plugins_url( '/images/', dirname(__FILE__)).'" src="'.plugins_url( '/images/'.$appeampleimg , dirname(__FILE__)).'" class="appipexampleimg" alt=""/></div>';
		echo '<p><label for="amazon-product-content-location"><strong>' . __("Where would you like your product to show within the post?", 'amazon-product-in-a-post-plugin' ) . '</strong></label></p>';
		echo '<p>&nbsp;&nbsp;&nbsp;&nbsp;<input class="appip-content-type" type="checkbox" name="amazon-product-content-location[1][]" value="1" '. (($appconloc==='1') || ($appconloc == '') ? ' checked="checked"' : '') .' /> '.__('<strong>Above Post Content</strong> - <em>Default - Product will be first then post text</em>','amazon-product-in-a-post-plugin').'<br/>';
		echo '&nbsp;&nbsp;&nbsp;&nbsp;<input class="appip-content-type" type="checkbox" name="amazon-product-content-location[1][]" value="3" '. (($appconloc==='3') ? ' checked="checked"' : '') .' /> '.__('<strong>Below Post Content</strong> - <em>Post text will be first then the Product</em>','amazon-product-in-a-post-plugin').'<br/>';
		echo '&nbsp;&nbsp;&nbsp;&nbsp;<input class="appip-content-type" type="checkbox" name="amazon-product-content-location[1][]" value="2" '. (($appconloc==='2') ? ' checked="checked"' : '') .' /> '.__('<strong>Post Text becomes Description</strong> - <em>Post text will become part of the Product layout</em>','amazon-product-in-a-post-plugin').'</p>';
		echo '<h2>Additional Features:</h2>';
		echo '<p><input type="checkbox" name="amazon-product-amazon-desc" value="1" '.$amazondesc.' /> <label for="amazon-product-amazon-desc"><strong>' . __("Show Amazon Description?", 'amazon-product-in-a-post-plugin' ) . '</strong></label> <em>'.__('if available. This will be IN ADDITION TO your own content.','amazon-product-in-a-post-plugin').'</em></p>';
		echo '<p><input type="checkbox" name="amazon-product-show-gallery" value="1" '.$amazongallery.' /> <label for="amazon-product-show-gallery"><strong>' . __("Show Image Gallery?", 'amazon-product-in-a-post-plugin' ) . '</strong></label> <em>'.__('if available (Consists of Amazon Approved images only). Not all products have an Amazon Image Gallery.','amazon-product-in-a-post-plugin').'</em></p>';
		echo '<p><input type="checkbox" name="amazon-product-show-features" value="1" '.$amazonfeatures.' /> <label for="amazon-product-show-features"><strong>' . __("Show Amazon Features?", 'amazon-product-in-a-post-plugin' ) . '</strong></label> <em>'.__('if available. Not all items have this feature.','amazon-product-in-a-post-plugin').'</em></p>';
		echo '<p><input type="checkbox" name="amazon-product-show-used-price" value="1" '.$amazonused.' /> <label for="amazon-product-show-used-price"><strong>' . __("Show Amazon Used Price?", 'amazon-product-in-a-post-plugin' ) . '</strong></label> <em>'.__('if available. Not all items have this feature.','amazon-product-in-a-post-plugin').'</em></p>';
		echo '<p><input type="checkbox" name="amazon-product-show-list-price" value="1" '.$amazonlist.' /> <label for="amazon-product-show-list-price"><strong>' . __("Show Amazon List Price?", 'amazon-product-in-a-post-plugin' ) . '</strong></label> <em>'.__('if available. Not all items have this feature.','amazon-product-in-a-post-plugin').'</em></p>';
		//echo '<p><input type="checkbox" name="amazon-product-show-saved-amt" value="1" '.$amazonsaved.' /> <label for="amazon-product-show-saved-amt"><strong>' . __("Show Saved Amount?", 'amazon-product-in-a-post-plugin' ) . '</strong></label> <em>'.__('if available. Not all items have this feature.','amazon-product-in-a-post-plugin').'</em></p>';
		//echo '<p><input type="checkbox" name="amazon-product-timestamp" value="1" '.$amazontstamp.' /> <label for="amazon-product-show-timestamp"><strong>' . __("Show Price Timestamp?", 'amazon-product-in-a-post-plugin' ) . '</strong></label> <em>'.__('for example:','amazon-product-in-a-post-plugin').'</em>'.__('<div class="appip-em-sample">&nbsp;&nbsp;Amazon.com Price: $32.77 (as of 01/07/2008 14:11 PST - <span class="appip-tos-price-cache-notice-tooltip" title="">Details</span>)<br/>&nbsp;&nbsp;Amazon.com Price: $32.77 (as of 14:11 PST - <span class="appip-tos-price-cache-notice-tooltip" title="">More info</span>)</div>','amazon-product-in-a-post-plugin').'</p>';
		echo '<span style="display:none;" class="appip-tos-price-cache-notice">'. __('Product prices and availability are accurate as of the date/time indicated and are subject to change. Any price and availability information displayed on amazon.'.$aws_partner_locale.' at the time of purchase will apply to the purchase of this product.','amazon-product-in-a-post-plugin').'</span>';
		echo '<div style="clear:both;"></div>';
	}
	
	/* When the post is saved, saves our custom data */
	function amazonProductInAPostSavePostdata($post_id, $post) {
		if($post_id==''){$post_id=$post->ID;}
		if(!isset($_POST['post_save_type_apipp'])){return;}
		$mydata = array();
		$mydata['amazon-product-isactive'] 					= isset($_POST['amazon-product-isactive']) ? sanitize_text_field($_POST['amazon-product-isactive']) : '0';
		$mydata['amazon-product-content-location'] 			= isset($_POST['amazon-product-content-location'][1][0]) ? sanitize_text_field($_POST['amazon-product-content-location'][1][0]) : '1';
		$mydata['amazon-product-single-asin'] 				= isset($_POST['amazon-product-single-asin']) ? sanitize_text_field($_POST['amazon-product-single-asin']) : '';
		$mydata['amazon-product-excerpt-hook-override'] 	= isset($_POST['amazon-product-excerpt-hook-override']) ? sanitize_text_field($_POST['amazon-product-excerpt-hook-override']) : '3';
		$mydata['amazon-product-content-hook-override'] 	= isset($_POST['amazon-product-content-hook-override']) ? sanitize_text_field($_POST['amazon-product-content-hook-override']) : '3';
		$mydata['amazon-product-newwindow'] 				= isset($_POST['amazon-product-newwindow']) ? sanitize_text_field($_POST['amazon-product-newwindow']) : '3';
		$mydata['amazon-product-singular-only'] 			= isset($_POST['amazon-product-singular-only']) ? sanitize_text_field($_POST['amazon-product-singular-only']) : '0';
		$mydata['amazon-product-amazon-desc'] 				= isset($_POST['amazon-product-amazon-desc']) ? sanitize_text_field($_POST['amazon-product-amazon-desc']) : '0';
		$mydata['amazon-product-show-gallery'] 				= isset($_POST['amazon-product-show-gallery']) ? sanitize_text_field($_POST['amazon-product-show-gallery']) : '0';
		$mydata['amazon-product-show-features'] 			= isset($_POST['amazon-product-show-features']) ? sanitize_text_field($_POST['amazon-product-show-features']) : '0';
		$mydata['amazon-product-show-list-price'] 			= isset($_POST['amazon-product-show-list-price']) ? sanitize_text_field($_POST['amazon-product-show-list-price']) : '0';
		$mydata['amazon-product-show-used-price'] 			= isset($_POST['amazon-product-show-used-price']) ? sanitize_text_field($_POST['amazon-product-show-used-price']) : '0';
		//$mydata['amazon-product-show-saved-amt'] 			= isset($_POST['amazon-product-show-saved-amt']) ? sanitize_text_field($_POST['amazon-product-show-saved-amt']) : '0';
		//$mydata['amazon-product-timestamp'] 				= isset($_POST['amazon-product-timestamp']) ? sanitize_text_field($_POST['amazon-product-timestamp']) : '0';
		$mydata['amazon-product-new-title'] 				= isset($_POST['amazon-product-new-title']) ? sanitize_text_field($_POST['amazon-product-new-title']): '';
		
		if($mydata['amazon-product-isactive']=='' && $mydata['amazon-product-single-asin']==""){$mydata['amazon-product-content-location']='';}
		if($mydata['amazon-product-excerpt-hook-override']==''){$mydata['amazon-product-excerpt-hook-override']='3';}
		if($mydata['amazon-product-content-hook-override']==''){$mydata['amazon-product-content-hook-override']='3';}
		if($mydata['amazon-product-newwindow']==''){$mydata['amazon-product-newwindow']='3';}
		$mydata = apply_filters('amazon_product_in_a_post_plugin_meta_presave',$mydata);
		foreach ($mydata as $key => $value) { 
			if( isset($post->post_type) && $post->post_type == 'revision'){return;}
			$value = implode(',', (array)$value);
			if(get_post_meta($post_id, $key, FALSE)) {
				update_post_meta($post_id, $key, $value);
			} else {
				add_post_meta($post_id, $key, $value);
			}
			if(!$value) delete_post_meta($post_id, $key); //delete if blank
		}
	}
	
	/* When the post is saved, saves our custom data */
	function amazonProductInAPostSavePostdataForm($post_id, $post) {
		if($post_id==''){$post_id=$post->ID;}
		if(!isset($post['post_save_type_apipp'])){return;}
		$mydata 										= array();
		$mydata['amazon-product-isactive'] 				= sanitize_text_field($post['amazon-product-isactive']);
		$mydata['amazon-product-content-location'] 		= sanitize_text_field($post['amazon-product-content-location']);
		$mydata['amazon-product-single-asin']			= sanitize_text_field($post['amazon-product-single-asin']);
		$mydata['amazon-product-excerpt-hook-override'] = sanitize_text_field($post['amazon-product-excerpt-hook-override']);
		$mydata['amazon-product-content-hook-override'] = sanitize_text_field($post['amazon-product-content-hook-override']);
		$mydata['amazon-product-newwindow'] 			= sanitize_text_field($post['amazon-product-newwindow']);
		$mydata['amazon-product-singular-only']			= sanitize_text_field($post['amazon-product-singular-only']);
		$mydata['amazon-product-amazon-desc'] 			= sanitize_text_field($post['amazon-product-amazon-desc']);
		$mydata['amazon-product-show-gallery'] 			= sanitize_text_field($post['amazon-product-show-gallery']);
		$mydata['amazon-product-show-features']			= sanitize_text_field($post['amazon-product-show-features']);
		$mydata['amazon-product-show-list-price']		= sanitize_text_field($post['amazon-product-show-list-price']);
		$mydata['amazon-product-show-used-price']		= sanitize_text_field($post['amazon-product-show-used-price']);
		//$mydata['amazon-product-show-saved-amt']		= sanitize_text_field($post['amazon-product-show-saved-amt']);
		//$mydata['amazon-product-timestamp'] 			= sanitize_text_field($post['amazon-product-timestamp']);
		$mydata['amazon-product-new-title'] 			= sanitize_text_field($post['amazon-product-new-title']);
		
		if($mydata['amazon-product-isactive']=='' && $mydata['amazon-product-single-asin']==""){$mydata['amazon-product-content-location']='';}
		if($mydata['amazon-product-excerpt-hook-override']==''){$mydata['amazon-product-excerpt-hook-override']='3';}
		if($mydata['amazon-product-content-hook-override']==''){$mydata['amazon-product-content-hook-override']='3';}
		if($mydata['amazon-product-newwindow']==''){$mydata['amazon-product-newwindow']='3';}
		$mydata = apply_filters('amazon_product_in_a_post_plugin_meta_presave',$mydata);
	
		foreach ($mydata as $key => $value) { 
			if( isset($post->post_type) && $post->post_type == 'revision'){return;}
			$value = implode(',', (array)$value);
			if(get_post_meta($post_id, $key, FALSE)) {
				update_post_meta($post_id, $key, $value);
			} else {
				add_post_meta($post_id, $key, $value);
			}
			if(!$value) delete_post_meta($post_id, $key); //delete if blank
		}
	}
	
	function apipp_plugin_menu() {
		global $fullname_apipp, $shortname_apipp, $options_apipp;
		apipp_options_add_admin_page($fullname_apipp,$shortname_apipp,$options_apipp);
		add_utility_page( __('Amazon Product In a Post Plugin', 'amazon-product-in-a-post-plugin'),'Amazon Product', 'edit_posts', 'apipp-main-menu','apipp_main_page', plugins_url( '/images/aicon-16.png' , dirname(__FILE__)) );//toplevel_page_apipp-main-menu
		add_submenu_page( 'apipp-main-menu', __("Getting Started", 'amazon-product-in-a-post-plugin'), __("Getting Started", 'amazon-product-in-a-post-plugin'), 'edit_posts' , 'apipp-main-menu', 'apipp_main_page');
	  	add_submenu_page( 'apipp-main-menu', __("Amazon Product in a Post Options", 'amazon-product-in-a-post-plugin'), __("Amazon PIP Options", 'amazon-product-in-a-post-plugin'), 'manage_options' , "apipp_plugin_admin", 'apipp_options_add_subpage');
		add_submenu_page( 'apipp-main-menu', __("Shortcode Usage", 'amazon-product-in-a-post-plugin'), __('Shortcode Usage', 'amazon-product-in-a-post-plugin'), 'manage_options', 'apipp_plugin-shortcode', 'apipp_shortcode_help_page' );
		add_submenu_page( 'apipp-main-menu', __("FAQs/Help", 'amazon-product-in-a-post-plugin'), __('FAQs/Help', 'amazon-product-in-a-post-plugin'), 'manage_options', 'apipp_plugin-faqs', 'apipp_options_faq_page' );
	  	add_submenu_page( 'apipp-main-menu', __("Product Cache", 'amazon-product-in-a-post-plugin'), __("Product Cache", 'amazon-product-in-a-post-plugin'), 'edit_posts' , "apipp-cache-page", 'apipp_cache_page');
	  	add_submenu_page( 'apipp-main-menu', __("New Amazon Post", 'amazon-product-in-a-post-plugin'), __("New Amazon Post", 'amazon-product-in-a-post-plugin'), 'edit_posts' , "apipp-add-new", 'apipp_add_new_post'); //amazon-product_page_apipp-add-new
		add_submenu_page( 'apipp-main-menu', __('Layout Styles', 'amazon-product-in-a-post-plugin'), __('Layout Styles', 'amazon-product-in-a-post-plugin'), 'manage_options', 'appip-layout-styles', 'apipp_templates');
	}

function apipp_cache_page(){
	global $current_user, $wpdb;
	global $aws_plugin_version;
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.', 'amazon-product-in-a-post-plugin') );
	}
	echo '<div class="wrap">';
	echo '<h2>'.__('Amazon Product In A Post CACHE', 'amazon-product-in-a-post-plugin').'</h2>';
	if( isset( $_GET['appmsg'] ) && (int) $_GET['appmsg'] == 1 ){
		echo '<div style="background-color: rgb(255, 251, 204);" id="message" class="updated fade below-h2"><p><b>'.__('Product post has been saved. To edit, use the standard Post Edit options.', 'amazon-product-in-a-post-plugin').'</b></p></div>';
	}
	echo '	<div class="wrapper">';
	$checksql= "SELECT body,Cache_id,URL,updated,( NOW() - Updated )as Age FROM ".$wpdb->prefix."amazoncache ORDER BY Updated DESC;";
	$result = $wpdb->get_results($checksql);
	echo '<p>'.__('The product cache is stored for 60 minutes and then deleted automatically. To refetch a product, delete the cache and it will be updared on the next product load.', 'amazon-product-in-a-post-plugin').'</p>';
	echo '<br/>';
	echo '<div style="text-align:right;margin:15px"><a href="#" class="button appip-cache-del button-primary" id="appip-cache-0">'.__('Delete Cache For ALL Products', 'amazon-product-in-a-post-plugin').'</a></div>';
	echo '<table class="wp-list-table widefat fixed" cellspacing="0">';
	echo '<thead><tr><th class="manage-column manage-cache-id" style="width:75px;">'.__('Cache ID', 'amazon-product-in-a-post-plugin').'</th><th class="manage-column manage-call-ui">'.__('Unique Call UI', 'amazon-product-in-a-post-plugin').'</th><th class="manage-column manage-updated" style="width:150px;">'.__('Last Updated', 'amazon-product-in-a-post-plugin').'</th><th class="manage-column manage-last-col" style="width:100px;"></th></tr></thead>';
	echo '<tfoot><tr><th class="manage-column manage-cache-id" style="width:75px;">'.__('Cache ID', 'amazon-product-in-a-post-plugin').'</th><th class="manage-column manage-call-ui">'.__('Unique Call UI', 'amazon-product-in-a-post-plugin').'</th><th class="manage-column manage-updated" style="width:150px;">'.__('Last Updated', 'amazon-product-in-a-post-plugin').'</th><th class="manage-column manage-last-col" style="width:100px;"></th></tr></tfoot>';
	if(!empty($result) && is_array($result)){
		echo '<tbody id="the-list">';
		$appct = 0;
		foreach($result as $psxml){
			if($appct&1){	echo '<tr class="alternate iedit appip-cache-'. $psxml->Cache_id.'-row">';}else{echo '<tr class="iedit appip-cache-'. $psxml->Cache_id.'-row">';}
			echo '<td class="manage-column manage-cache-id">'. $psxml->Cache_id.'</td>';
			echo '<td class="manage-column manage-call-ui">'. $psxml->URL.' ( <a href="#" class="xml-show">show xml cache data</a> )<textarea style="display:none;width:100%;height:150px;">'.htmlspecialchars($psxml->body).'</textarea></td>';
			echo '<td class="manage-column manage-updated">'. $psxml->updated.'</td>';
			echo '<td class="manage-column manage-last-col"><a href="#" class="button appip-cache-del" id="appip-cache-'. $psxml->Cache_id.'">'.__('delete cache', 'amazon-product-in-a-post-plugin').'</a></td>';
			echo '</tr>';
			$appct++;
		}
	}else{
		echo '<tbody id="the-list"><tr class="alternate iedit appip-cache-'. $psxml->Cache_id.'-row"><td colspan="4">'.__('no cached products at this time.', 'amazon-product-in-a-post-plugin').'</td></tr>';
	}
	echo '</tbody>';
	echo '</table>';
	echo '		<div style="text-align:right;margin:15px"><a href="#" class="button appip-cache-del button-primary" id="appip-cache-0">'.__('Delete Cache For ALL Products', 'amazon-product-in-a-post-plugin').'</a></div>';
	echo '	</div>';
	echo '</div>';
}

function apipp_shortcode_help_page(){
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.', 'amazon-product-in-a-post-plugin') );
	}
	$pageTxtArr = array();
	$pageTxtArr[] = '<div class="wrap">';
	$pageTxtArr[] = '	<h2>'.__('Amazon Product In a Post Shortcode Usage', 'amazon-product-in-a-post-plugin').'</h2>';
	$pageTxtArr[] = '	<div class="wrapper appip_shortcode_help">';
	$pageTxtArr[] = '		<p>'.__('Since Version 3.5.1, a new shortcode system was put into place. The new Shortcode is ','amazon-product-in-a-post-plugin').'<code>[AMAZONPRODUCTS asin="B0084IG8TM"]</code> (' . __('instead of','amazon-product-in-a-post-plugin').' <code>[AMAZONPRODUCT=B0084IG8TM]</code>). <br>';
	$pageTxtArr[] = __('			Note the', 'amazon-product-in-a-post-plugin').' <code>S</code> on <code>AMAZONPRODUCTS</code>. '.__('The old method is still supported, but has limited functionality so it is recommended that you switch to the new shortcode when you can.', 'amazon-product-in-a-post-plugin').'</p>';
	$pageTxtArr[] = '		<p>'.__('And additional Shortcode has been added to make adding elements of a product into the text of the page easier. The additional shortcode is', 'amazon-product-in-a-post-plugin').' <code>[amazon-elements]</code>. '.__('This new shortcode has many parameters and is very useful for adding bits and pieces of a product to the text.', 'amazon-product-in-a-post-plugin').'</p>';
	$pageTxtArr[] = '		<p>'.__('For more information about ','amazon-product-in-a-post-plugin').'<code>[amazon-elements]</code>, <a href="#amazonelements">'.__('click here','amazon-product-in-a-post-plugin').'</a>.</p>';
	$pageTxtArr[] = '		<hr/">';
	$pageTxtArr[] = '		<h2><a name="amazonproducts"></a>[AMAZONPRODUCTS] '.__('Shortcode', 'amazon-product-in-a-post-plugin').'</h2>';
	$pageTxtArr[] = '		<p>'.__('The shortcode should be used as follows:', 'amazon-product-in-a-post-plugin').'</p>';
	$pageTxtArr[] = '		<p>'.__('Usage in the most basic form is simply the Shortcode and the ASIN written as (where the XXXXXXXXX is the Amazon ASIN):', 'amazon-product-in-a-post-plugin').'<br>';
	$pageTxtArr[] = '			<code>[AMAZONPRODUCTS asin="XXXXXXXXXX"]</code>';
	$pageTxtArr[] = '			<p>'.__('There are additional parameters that can be added if you need them. The parameters are', 'amazon-product-in-a-post-plugin').'<br><code>locale</code>, <code>desc</code>, <code>features</code>, <code>listprice</code>, <code>partner_id</code>, <code>private_key</code>, and <code>public_key</code></p>';
	$pageTxtArr[] = '			<p>'.__('A description of each parameter:', 'amazon-product-in-a-post-plugin').'</p>';
	$pageTxtArr[] = '		<ul>';
	$pageTxtArr[] = '			<li><code>asin</code> &mdash; '. __('this is the ASIN or ASINs up to 10 comma separated.', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '			<li><code>locale</code> &mdash; '. __('this is the Amazon locale you want to get the product from, i.e., com, co.uk, fr, etc. default is your plugin setting.', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '			<li><code>desc</code> &mdash; '. __('using 1 shows Amazon description (if available) and 0 hides it &mdash; default is 0.', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '			<li><code>features</code> &mdash; '. __('using 1 shows Amazon Features (if available) and 0 hides it - default is 0.', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '			<li><code>listprice</code> &mdash; '. __('using 1 shows the list price and 0 hides it &mdash; default is 1.', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '			<li><code>partner_id</code> &mdash; '. __('allows you to add a different parent ID if different for other locale &mdash; default is ID in settings.', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '			<li><code>private_key</code> &mdash; '. __('allows you to add different private key for locale if different &mdash; default is private key in settings.', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '			<li><code>public_key</code> &mdash; '. __('allows you to add a different private key for locale if different &mdash; default is public key in settings.', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '		</ul>';
	$pageTxtArr[] = '			<p>'.__('Examples of it&rsquo;s usage:', 'amazon-product-in-a-post-plugin').'</p>';
	$pageTxtArr[] = '		<ul>';
	$pageTxtArr[] = '			<li>'.__('If you want to add a .com item and you have the same partner id, public key, private key and want the features showing:<br>', 'amazon-product-in-a-post-plugin');
	$pageTxtArr[] = '				<code>[AMAZONPRODUCTS asin="B0084IG8TM" features="1" locale="com"]</code></li>';
	$pageTxtArr[] = '			<li>'.__('If you want to add a .com item and you have a different partner id, public key, private key and want the description showing but features not showing:<br>', 'amazon-product-in-a-post-plugin');
	$pageTxtArr[] = '				<code>[AMAZONPRODUCTS asin="B0084IG8TM,B005LAIHPE" locale="com" public_key="AKIAJDRNJ6O997HKGXW" private_key="Nzg499eVysc5yjcZwrIV3bhDti/OGyRHEYOWO005" partner_id="mynewid-20"]</code></li>';
	$pageTxtArr[] = '			<li>'.__('If you just want to use your same locale but want 2 items with no list price and features showing:<br>', 'amazon-product-in-a-post-plugin');
	$pageTxtArr[] = '				<code>[AMAZONPRODUCTS asin="B0084IG8TM,B005LAIHPE" features="1" listprice="0"]</code></li>';
	$pageTxtArr[] = '			<li>'.__('If you just want 2 products with regular settings:<br>', 'amazon-product-in-a-post-plugin');
	$pageTxtArr[] = '				<code>[AMAZONPRODUCTS asin="B0084IG8TM,B005LAIHPE"]</code></li>';
	$pageTxtArr[] = '			<li>'.__('If you want to add text to a product:<br>', 'amazon-product-in-a-post-plugin');
	$pageTxtArr[] = '				<code>[AMAZONPRODUCTS asin="B0084IG8TM"]your text can go here![/AMAZONPRODUCTS]</code></li>';
	$pageTxtArr[] = '		</ul>';
	$pageTxtArr[] = '		<hr/>';
	$pageTxtArr[] = '		<div class="appip_elements_code"><a name="amazonelements"></a>';
	$pageTxtArr[] = '<h2>[amazon-elements] '.__('Shortcode', 'amazon-product-in-a-post-plugin').'</h2>';
	$pageTxtArr[] = '			<p>'.__('shortcode implementation for elements only &mdash; for when you may only want specific element(s) like the title, price and image or image and description, or the title and the buy now button, etc.', 'amazon-product-in-a-post-plugin').'</p>';
	$pageTxtArr[] = '			<ul>';
	$pageTxtArr[] = '				<li><code>asin</code> &mdash; '.__('the Amazon ASIN (up to 10 comma sep).<span style="color:#ff0000;"> Required </span>', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li><code>locale</code> &mdash; '.__('the amazon locale, i.e., co.uk, es. This is handy of you need a product from a different locale than your default one. Applies to all ASINs in list. (optional)', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li><code>gallery</code> &mdash; '.__('use a value of 1 to show extra photos if a product has them. Applies to all ASINs in list. (optional)', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li><code>partner_id</code> &mdash; '.__('your amazon partner id. default is the one in the options. You can set a different one here if you have a different one for another locale or just want to split them up between multiple ids. Applies to all ASINs in list. (optional)', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li><code>private_key</code> &mdash; '.__('amazon private key. Default is one set in options. You can set a different one if needed for another locale. Applies to all ASINs in list. (optional)', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li><code>public_key</code> &mdash; '.__('amazon public key. Default is one set in options. You can set a different one if needed for another locale. Applies to all ASINs in list. (optional)', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li><code>showformat</code> &mdash; '.__('show or hide the format in the title i.e., &quot;Some Title (DVD)&quot; or &quot;Some Title (BOOK)&quot;. 1 to show 0 to hide. Applies to all ASINs. Default is 1. (optional)', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li><code>msg_instock</code> &mdash; '.__('message to display when an image is in stock. Applies to all ASINs. (optional)', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li><code>msg_outofstock</code> &mdash; '.__('message to display when an image is out of stock. Applies to all ASINs in list. (optional)', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li><code>target</code> &mdash; '.__('default is &quot;_blank&quot;. Applies to all ASINs in list. (optional)', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li><code>fields</code> &mdash; '.__('Fields you want to return. And valid return field form Amazon API (you could see API for list) or common fields of: title, lg-image,md-image,sm-image, large-image-link,description (or desc),ListPrice, new-price,LowestUsedPrice, button. You should have at least one field when using this shortcode, as no field will return a blank result. Applies to all ASINs in list. (optional)', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li><code>labels</code> &mdash; '.__('Labels that correspond to the fields (if you want custom labels). They should match the fields and be comma separated and :: separated for the field name and value i.e., field name::label text,field-two::value 2, etc. These can be ASIN specific. If you have 2 ASINs, the first label field will correspond to the first ASIN, the second to the second one, and so on. (optional)', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li><code>button_url</code> &mdash; '.__('URL for a button image, if you want to use a different image than the default one. ASIN Specific - separate the list of URLs with a comma to correspond with the ASIN. i.e., if you had 3 ASINs and wanted the first and third to have custom buttons, but the second to have the default button, use <code>button_url="http://first.com/image1.jpg,,http://first.com/image1.jpg"</code> (optional)', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '			</ul>';
	$pageTxtArr[] = '			<p>'.__('Example of the new elements shortcode usage:', 'amazon-product-in-a-post-plugin').'</p>';
	$pageTxtArr[] = '			<ul>';
	$pageTxtArr[] = '				<li>'.__('if you want to have a product with only a large image, the title and button, you would use:', 'amazon-product-in-a-post-plugin').'<br>';
	$pageTxtArr[] = '					<code>[amazon-element asin=&quot;0753515032&quot; fields=&quot;title,lg-image,large-image-link,button&quot;]</code></li>';
	$pageTxtArr[] = '				<li>'.__('If you want that same product to have the description, you would use:', 'amazon-product-in-a-post-plugin').'<br>';
	$pageTxtArr[] = '					<code>[amazon-element asin=&quot;0753515032&quot; fields=&quot;title,lg-image,large-image-link,<span style="color:#FF0000;">desc</span>,button&quot;]</code></li>';
	$pageTxtArr[] = '				<li>'.__('If you want that same product to have the list price and the new price, you would use:', 'amazon-product-in-a-post-plugin').'<br>';
	$pageTxtArr[] = '					<code>[amazon-element asin=&quot;0753515032&quot; fields=&quot;title,lg-image,large-image-link,desc,<span style="color:#FF0000;">ListPrice,new-price,button&quot; msg_instock=&quot;in Stock&quot; msg_outofstock=&quot;no more left!&quot;</span>]</code><br>';
	$pageTxtArr[] = __('					The msg_instock and msg_outofstock are optional fields.', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('If you want to add som of your own text to a product, and makeit part of the post, you could do something like this:<br>', 'amazon-product-in-a-post-plugin');
	$pageTxtArr[] = '					<code>[amazon-element asin=&quot;0753515032&quot; fields=&quot;title,lg-image,large-image-link&quot; labels=&quot;large-image-link::click for larger image:,title-wrap::h2,title::Richard Branson: Business Stripped Bare&quot;]Some normal content text here.[amazon-element asin=&quot;0753515032&quot; fields=&quot;desc,gallery,ListPrice,new-price,LowestUsedPrice,button&quot; labels=&quot;desc::Book Description:,ListPrice::SRP:,new-price::New From:,LowestUsedPrice::Used From:&quot; msg_instock=&quot;Available&quot;]</code></li>';
	$pageTxtArr[] = '			</ul>';
	$pageTxtArr[] = '			<h4>'.__('Available Fields for the shortcode:', 'amazon-product-in-a-post-plugin').'</h4>';
	$pageTxtArr[] = '			<h3>'.__('Common Items', 'amazon-product-in-a-post-plugin').'</h3>';
	$pageTxtArr[] = __('			These are generally common in all products (if available)', 'amazon-product-in-a-post-plugin');
	$pageTxtArr[] = '			<ul class="as_code">';
	$pageTxtArr[] = '				<li>'.__('ASIN', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('URL', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('Title', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('SmallImage', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('MediumImage', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('LargeImage', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('AddlImages', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('Feature', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('Format', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('PartNumber', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('ProductGroup', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('ProductTypeName', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('ISBN', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('ItemDesc', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('ListPrice', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('SKU', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('UPC', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('CustomerReviews', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '			</ul>';
	$pageTxtArr[] = '			<h3>'.__('Offer/Pricing Elements', 'amazon-product-in-a-post-plugin').'</h3>';
	$pageTxtArr[] = __('			These are generally returned for most products.', 'amazon-product-in-a-post-plugin');
	$pageTxtArr[] = '			<ul class="as_code">';
	$pageTxtArr[] = '				<li>'.__('LowestNewPrice', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('LowestUsedPrice', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('LowestRefurbishedPrice', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('LowestCollectiblePrice', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('MoreOffersUrl', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('NewAmazonPricing', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('TotalCollectible', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('TotalNew', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('TotalOffers', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('TotalRefurbished', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('TotalUsed', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '			</ul>';
	$pageTxtArr[] = '			<h3>'.__('Items Attributes', 'amazon-product-in-a-post-plugin').'</h3>';
	$pageTxtArr[] = __('			Available only to their select product groups and not available in all locales. Try it first to see if it returns a value. For example, the Actor field is not going to be returned if the product is a computer or some form of electronics, but would be returned if the product was a DVD or Blu-ray Movie.', 'amazon-product-in-a-post-plugin');
	$pageTxtArr[] = '			<ul class="as_code">';
	$pageTxtArr[] = '				<li>'.__('Actor', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('Artist', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('AspectRatio', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('AudienceRating', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('AudioFormat', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('Author', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('Binding', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('Brand', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('CatalogNumberList', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('Category', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('CEROAgeRating', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('ClothingSize', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('Color', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('Creator', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('Department', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('Director', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('EAN', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('EANList', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('Edition', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('EISBN', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('EpisodeSequence', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('ESRBAgeRating', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('Genre', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('HardwarePlatform', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('HazardousMaterialType', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('IsAdultProduct', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('IsAutographed', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('IsEligibleForTradeIn', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('IsMemorabilia', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('IssuesPerYear', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('ItemDimensions', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('ItemPartNumber', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('Label', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('Languages', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('LegalDisclaimer', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('MagazineType', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('Manufacturer', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('ManufacturerMaximumAge', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('ManufacturerMinimumAge', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('ManufacturerPartsWarrantyDescription', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('MediaType', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('Model', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('ModelYear', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('MPN', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('NumberOfDiscs', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('NumberOfIssues', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('NumberOfItems', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('NumberOfPages', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('NumberOfTracks', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('OperatingSystem', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('PackageDimensions', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('PackageDimensionsWidth', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('PackageDimensionsHeight', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('PackageDimensionsLength', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('PackageDimensionsWeight', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('PackageQuantity', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('PictureFormat', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('Platform', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('ProductTypeSubcategory', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('PublicationDate', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('Publisher', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('RegionCode', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('ReleaseDate', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('RunningTime', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('SeikodoProductCode', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('ShoeSize', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('Size', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('Studio', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('SubscriptionLength', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('TrackSequence', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('TradeInValue', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('UPCList', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('Warranty', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '				<li>'.__('WEEETaxValue ', 'amazon-product-in-a-post-plugin').'</li>';
	$pageTxtArr[] = '			</ul>';
	$pageTxtArr[] = '		</div>';
	$pageTxtArr[] = '	</div>';
	$pageTxtArr[] = '</div>';
	echo implode("\n",$pageTxtArr);
	unset($pageTxtArr);
}	
function apipp_main_page(){
	global $current_user, $wpdb;
	global $aws_plugin_version;
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.', 'amazon-product-in-a-post-plugin'));
	}
	echo '<div class="wrap">';
	echo'<style type="text/css">small{font-size:13px;color:#777;line-height: 19px;}.steps-wrapper div{margin-left:15px;}.steps-wrapper div p{margin-left:25px;}.steps-wrapper div img{margin:20px 10px 20px 10px;}.steps-wrapper ul{margin-left: 25px;list-style-type: none;margin: 25px 0 25px 28px;border-left: 10px solid #eaeaea;padding-left: 16px;}</style>';
	echo '<h2>'.__('Amazon Product In A Post - GETTING STARTED', 'amazon-product-in-a-post-plugin').'</h2>';
	echo '	<div class="wrapper">';?>
<div class="steps-wrapper">
<p>There are 2 steps to using this plug-in to make additional income as an Amazon Affiliate. The first is to sign up for an Amazon Affiliate Account. The second is to get a set of Product Advertising API keys so the plug-in can access the product API and return the correct products. Both of these steps are a little intense, but if you have about 15-20 minutes, you can set up everything you need to start making money.</p>
<div>
	<h2>Step 1 - Getting Your Amazon Affiliate/Partner ID</h2>
	<p>Sign up for your Amazon Affiliate/Partner account at one of the following URLs (choose the correct link based on your Amazon location):
		<ul>
			<li>Brazil (br): <a href="http://associados.amazon.com.br/gp/associates/apply/main.html">http://associados.amazon.com.br/gp/associates/apply/main.html</a> </li>
			<li>Canada (ca): <a href="http://associates.amazon.ca/gp/associates/apply/main.html">http://associates.amazon.ca/gp/associates/apply/main.html</a> </li>
			<li>China (cn): <a href="http://associates.amazon.ca/gp/associates/apply/main.html">http://associates.amazon.cn/gp/associates/apply/main.html</a> </li>
			<li>France (fr): <a href="http://partenaires.amazon.fr/gp/associates/apply/main.html">http://partenaires.amazon.fr/gp/associates/apply/main.html</a> </li>
			<li>Germany (de): <a href="http://partnernet.amazon.de/gp/associates/apply/main.html">http://partnernet.amazon.de/gp/associates/apply/main.html</a> </li>
			<li>India (in): <a href="http://affiliate-program.amazon.in/gp/associates/apply/main.html">http://affiliate-program.amazon.in/gp/associates/apply/main.html</a> </li>
			<li>Italy (it): <a href="http://programma-affiliazione.amazon.it/gp/associates/apply/main.html">http://programma-affiliazione.amazon.it/gp/associates/apply/main.html</a> </li>
			<li>Japan (jp): <a href="http://affiliate.amazon.co.jp/gp/associates/apply/main.html">http://affiliate.amazon.co.jp/gp/associates/apply/main.html</a> </li>
			<li>Spain (es): <a href="http://afiliados.amazon.es/gp/associates/apply/main.html">http://afiliados.amazon.es/gp/associates/apply/main.html</a> </li>
			<li>United Kingdom (co.uk): <a href="http://affiliate-program.amazon.co.uk/gp/associates/apply/main.html">http://affiliate-program.amazon.co.uk/gp/associates/apply/main.html</a> </li>
			<li>United States (com): <a href="http://affiliate-program.amazon.com/gp/associates/apply/main.html">http://affiliate-program.amazon.com/gp/associates/apply/main.html</a> </li>
		</ul>
	</p>
	<p>Amazon requires that you have a different affiliate ID for each country (aka, locale).</p>
	<p>Since the Affiliate signup has not changed much over the years, and it is not too difficult, I will not go into it in any more detail. Follow the steps until you are issued your affiliate partner ID. Paste that into the plug-in options page.</p>
</div>
<div>
	<h2>Step 2 - Getting your API Keys</h2>
	<p>Amazon requires ALL API users to have a different, secure set of keys to make API calls. Because of this, we cannot add any keys to the plug-in for you. You must sign up for your own set and put the into the plug-in options fields on the settings page by yourself.</p>
	<p><span class="updated">IMPORTANT! DO NOT give out your Amazon Access Key ID or your Secret Access Key to just anyone. Intentionally disclosing your Secret Key to other parties is against Amazon's terms of use and is considered grounds for account suspension or deletion (without payment of any due earnings). They take the key security very seriously and you can be held accountable for any misuse of your keys, should you give them out to anyone (trust me, I know). So keep them secret. If you request help from up to solve an issue, we may ask you to change your keys after we are done helping you - just so you can feel safe and secure about the secrecy of your keys.</span></p>
	<p>Sign up for your account at one of the following URLs (choose the correct link based on your Amazon location) :</p>
	<p>
	<ul>
		<li>Brazil (br): <a target="_blank" style="outline: 0px; color: rgb(33, 117, 155);" href="http://associados.amazon.com.br/gp/associates/apply/main.html">http://associados.amazon.com.br/gp/associates/apply/main.html </a></li>
		<li>Canada (ca): <a target="_blank" style="outline: 0px; color: rgb(33, 117, 155);" href="https://associates.amazon.ca/gp/advertising/api/detail/main.html">https://associates.amazon.ca/gp/advertising/api/detail/main.html</a></li>
		<li>China (cn): <a target="_blank" style="outline: 0px; color: rgb(33, 117, 155);" href="https://associates.amazon.cn/gp/advertising/api/detail/main.html">https://associates.amazon.cn/gp/advertising/api/detail/main.html</a></li>
		<li>France (fr): <a target="_blank" style="outline: 0px; color: rgb(33, 117, 155);" href="https://partenaires.amazon.fr/gp/advertising/api/detail/main.html">https://partenaires.amazon.fr/gp/advertising/api/detail/main.html</a></li>
		<li>Germany (de): <a target="_blank" style="outline: 0px; color: rgb(33, 117, 155);" href="https://affiliate-program.amazon.com/gp/advertising/api/detail/main.html">https://affiliate-program.amazon.com/gp/advertising/api/detail/main.html</a></li>
		<li>India (in): <a target="_blank" style="outline: 0px; color: rgb(33, 117, 155);" href="https://affiliate-program.amazon.in/gp/advertising/api/detail/main.html">https://affiliate-program.amazon.in/gp/advertising/api/detail/main.html</a></li>
		<li>Italy (it): <a target="_blank" style="outline: 0px; color: rgb(33, 117, 155);" href="https://programma-affiliazione.amazon.it/gp/advertising/api/detail/main.html">https://programma-affiliazione.amazon.it/gp/advertising/api/detail/main.html</a></li>
		<li>Japan (jp): <a target="_blank" style="outline: 0px; color: rgb(33, 117, 155);" href="https://affiliate.amazon.co.jp/gp/advertising/api/detail/agreement.html">https://affiliate.amazon.co.jp/gp/advertising/api/detail/agreement.html</a></li>
		<li>Spain (es): <a target="_blank" style="outline: 0px; color: rgb(33, 117, 155);" href="https://afiliados.amazon.es/gp/advertising/api/detail/main.html">https://afiliados.amazon.es/gp/advertising/api/detail/main.html</a></li>
		<li>United Kingdom (co.uk): <a target="_blank" style="outline: 0px; color: rgb(33, 117, 155);" href="https://affiliate-program.amazon.co.uk/gp/flex/advertising/api/sign-in.html">https://affiliate-program.amazon.co.uk/gp/flex/advertising/api/sign-in.html</a></li>
		<li>United States (com): <a target="_blank" style="outline: 0px; color: rgb(33, 117, 155);" href="https://affiliate-program.amazon.com/gp/flex/advertising/api/sign-in.html">https://affiliate-program.amazon.com/gp/flex/advertising/api/sign-in.html</a></li>
	</ul>
	</p>
	<p>Start by creating a new user account. if you have an Amazon account set 
	up from the previous step of getting your Affiliate ID, you can login using 
	that information and skip to step 2e).<br><img border="0" src="<?php echo plugins_url('/images/signup-step1.png',dirname(__FILE__));?>" width="417" height="289"></p>
	</div>
	
	<div>
	<h2>Step 2b</h2>
	<p>For a New Account, Enter Your Name, Email Address and Password twice, then 
	click create an account.<br><img border="0" src="<?php echo plugins_url('/images/signup-step2.png',dirname(__FILE__));?>" width="452" height="263"></p>
	</div>
	
	<div>
	<h2>Step 2c</h2>
	<p>Enter all of the other required information and check the agreement box (remember to read it!). Then enter the capcha image text and complete the registration.<br><img border="0" src="<?php echo plugins_url('/images/signup-step3.png',dirname(__FILE__));?>" width="700" height="1114"></p>
	</div>
	
	<div>
	<h2>Step 2d</h2>
	<p>This is the success page that you come to when you complete the process. Click the MANAGE ACCOUNT link.<br><img border="0" src="<?php echo plugins_url('/images/signup-step4.png',dirname(__FILE__));?>" width="700" height="599"></p>
	</div>
	
	<div>
	<h2>Step 2e</h2>
	<p>If you already had an account, you should have seen this page when you 
	logged in. <br>
	This is the main management account screen (as of June 10, 2013). Click the "Click Here" link under ACCESS IDENTIFIERS section to get the 
	API keys.<br><img border="0" src="<?php echo plugins_url('/images/signup-step5.png',dirname(__FILE__));?>" width="700" height="408"></p>
	</div>
	
	<div>
	<h2>Step 2f</h2>
	<p>You will be required to Log In when you go at access some information (as 
	a security precaution from Amazon). This time be sure to select that you are 
	returning user (already have an account) use your login information you just 
	created or had previously.<br><img border="0" src="<?php echo plugins_url('/images/signup-step6.png',dirname(__FILE__));?>" width="700" height="558"></p>
	</div>
	
	<div>
	<h2>Step 2g</h2>
	<p>This will take you to the Security Credentials Page. Note that if you have Keys for other AWS products, you may have to select the Product Advertising API from the list to access that page.<br>Also - note that this management page WILL change in the near future to the new Amazon 
	IAM Management Console. They provide a link to the new console but eventually you will need to access it from their IAM services login.</p>
	<p>Scroll down the page to the Access Keys section and note the Amazon Access Key ID (sometimes referred to as Public Key). That is what you will put in the Amazon Access Key ID box in the plug-in options.<img border="0" src="<?php echo plugins_url('/images/signup-step7.png',dirname(__FILE__));?>" width="700" height="822"></p>
	<p>Click SHOW to see the Secret Access Key (sometimes referred to as Secret Key).</p>
	</div>
	
	<div>
	<h2>Step 2h</h2>
	<p>In the Secret Access Key box that shows, copy the key into the plug-in options page for the Secret Access Key field.<br><img border="0" src="<?php echo plugins_url('/images/signup-step8.png',dirname(__FILE__));?>" width="700" height="299"></p>
	</div>
	
	
	<div>
	<h2>Step 2i - MISC Information</h2>
	<p>IF YOU DO use the new Amazon IAM Management Console, your Access Key ID will be located under the "Your Security Credentials" page. They will NOT show you your Secret Access Key here any longer. If you loose it, you MUST generate a new Root Key.</p><p>After you generate the Root Key, it will serve the browser with a csv file that has both the Access Key ID and the Secret Access Key inside. In the very near future, they will not provide the Secret Key over the web page, you WILL have to download it if you generate the keys.<br><img border="0" src="<?php echo plugins_url('/images/signup-step-misc1.png',dirname(__FILE__));?>" width="545" height="360">	<img border="0" src="<?php echo plugins_url('/images/signup-step-misc2.png',dirname(__FILE__));?>" width="545" height="358"></p>
	</div>
</div>
	<?php
	echo '	</div>';
	echo '</div>';
}
function apipp_options_faq_page(){
		include_once(ABSPATH . WPINC . '/feed.php');
		echo '
		<div class="wrap">
			<style type="text/css">
				.faq-item{border-bottom:1px solid #CCC;padding-bottom:10px;margin-bottom:10px;}
				.faq-item span.qa{color: #21759B;display: block;float: left;font-family: serif;font-size: 17px;font-weight: bold;margin-left: 0;margin-right: 5px;}
				 h3.qa{color: #21759B;margin:0px 0px 10px 0;font-family: serif;font-size: 17px;font-weight: bold;}
				.faq-item .qa-content p:first-child{margin-top:0;}
				.apipp-faq-links {border-bottom: 1px solid #CCCCCC;list-style-position: inside;margin:10px 0 15px 35px;}
				.apipp-faq-answers{list-style-position: inside;margin:10px 0 15px 35px;}
				.toplink{text-align:left;}
				.qa-content div > code{background: none repeat scroll 0 0 #EFEFEF;border: 1px solid #CCCCCC;display: block;margin-left: 35px;overflow-y: auto;padding: 10px 20px;white-space: nowrap;width: 90%;}
			</style>
			<div class="icon32" style="background: url('. plugins_url( "/",dirname(__FILE__)) . 'images/aicon.png) no-repeat transparent;"><br/></div>
		 	<h2>'.__('Amazon Product in a Post FAQs/Help', 'amazon-product-in-a-post-plugin').'</h2>
			<div align="left"><p>'.sprintf(__('The FAQS are now on a feed that can be updated on the fly. If you have a question and don\'t see an answer, please send an email to %1$s and ask your question. If it is relevant to the plugin, it will be added to the FAQs feed so it will show up here. Please be sure to include the plugin you are asking a question about (Amazon Product in a Post Plugin), the Debugging Key (located on the options page) and any other information like your WordPress version and examples if the plugin is not working correctly for you. THANKS!', 'amazon-product-in-a-post-plugin'),'<a href="mailto:plugins@fischercreativemedia.com">plugins@fischercreativemedia.com</a>').'</p>
			<hr noshade color="#C0C0C0" size="1" />
		';
		$rss 			= fetch_feed('http://www.fischercreativemedia.com/?feed=apipp_faqs');
		$linkfaq 		= array();
		$linkcontent 	= array();
		if (!is_wp_error( $rss ) ) : 
		    $maxitems 	= $rss->get_item_quantity(100); 
		    $rss_items 	= $rss->get_items(0, $maxitems); 
		endif;
			$aqr = 0;
		    if ($maxitems != 0){
			    foreach ( $rss_items as $item ) :
			    	$aqr++; 
			    	$linkfaq[]		= '<li class="faq-top-item"><a href="#faq-'.$aqr.'">'.esc_html( $item->get_title() ).'</a></li>';
				    $linkcontent[] 	= '<li class="faq-item"><a name="faq-'.$aqr.'"></a><h3 class="qa"><span class="qa">Q. </span>'.esc_html( $item->get_title() ).'</h3><div class="qa-content"><span class="qa answer">A. </span>'.$item->get_content().'</div><div class="toplink"><a href="#faq-top">top &uarr;</a></li>';
			    endforeach;
			}
		echo '<a name="faq-top"></a><h2>'.__('Table of Contents', 'amazon-product-in-a-post-plugin').'</h2>';
		echo '<ol class="apipp-faq-links">';
			echo implode("\n",$linkfaq);
		echo '</ol>';
		echo '<h2>'.__('Questions/Answers', 'amazon-product-in-a-post-plugin').'</h2>';
		echo '<ul class="apipp-faq-answers">';
			echo implode("\n",$linkcontent);
		echo '</ul>';
		echo '
			</div>
		</div>';
}

	function apipp_templates(){
		echo '<div class="wrap">';
		echo '<h2>'.__('Amazon Styling Options', 'amazon-product-in-a-post-plugin').'</h2>';
		echo '<div id="wpcontent-inner">';
		echo 'This is a future feature.';
		echo '</div>';
		echo '</div>';
	}
	
function apipp_add_new_post(){
	global $user_ID;
	global $current_user;
	get_currentuserinfo();
    $myuserpost = $current_user->ID;
	echo '<div class="wrap"><h2>'.__('Add New Amazon Product Post', 'amazon-product-in-a-post-plugin').'</h2>';
	if(isset($_GET['appmsg']) && $_GET['appmsg']=='1'){	echo '<div style="background-color: rgb(255, 251, 204);" id="message" class="updated fade below-h2"><p><b>'.__('Product post has been saved. To edit, use the standard Post Edit options.', 'amazon-product-in-a-post-plugin').'</b></p></div>';}
	echo '<p>'.__('This function will allow you to add a new post for an Amazon Product - no need to create a post then add the ASIN. Once you add a Product Post, you can edit the information with the normal Post Edit options.', 'amazon-product-in-a-post-plugin').'</p>';
	$ptypes = get_post_types(array('public' => true));
	$ptypeHTML = '<div class="apip-posttypes">';
	$taxonomies = get_taxonomies(array(),'objects');
	$section = '';
	$section .= '<tr class="apip-extra-pad-bot taxonomy_blocks taxonomy_block_page"><td align="left" valign="top">'.__('Category/Taxonomy for Pages', 'amazon-product-in-a-post-plugin').':</td><td align="left">';
	$section .= '<div>'.__('No Categories/Taxonomy Available for Pages.','amazon-product-in-a-post-plugin').'</div>';
	$section .= '</td></tr>';

	if(! empty( $taxonomies )){
		foreach($taxonomies as $key => $taxCat){
			if(isset($taxCat->object_type)&& is_array($taxCat->object_type)){
				foreach($taxCat->object_type as $tcpost){
					if(in_array($tcpost,$ptypes) && ($tcpost != 'nav_menu_item' && $tcpost != 'attachment' && $tcpost != 'revision')){
						$argsapp = array( 'taxonomy' => $key,'orderby' => 'name','hide_empty' => 0);
						$termsapp = get_terms($key, $argsapp);
						$countapp = count($termsapp);
						if('post_format' == $key || 'post_tag' == $key ){
						}else{
							$section .= '<tr class="apip-extra-pad-bot taxonomy_blocks taxonomy_block_'.$tcpost.'"><td align="left" valign="top">'.__('Category/Taxonomy for ', 'amazon-product-in-a-post-plugin').$tcpost.':</td><td align="left">';
							if ($countapp > 0) {
								foreach ($termsapp as $term) {
									$section .= '<div class="appip-new-post-cat"><input type="checkbox" name="post_category['.$tcpost.']['.$key.'][]" value="' . $term->term_id . '" /> ' . $term->name . '</div>';
								}
							}else{
								$section .= '<div>'.__('No Categories/Taxonomy Available for this Post type.','amazon-product-in-a-post-plugin').'</div>';
							}
							$section .= '</td></tr>';
						}
					}
				}
			}
		}
	}
	if(! empty( $ptypes )){
		foreach($ptypes as $ptype){
			if($ptype != 'nav_menu_item' && $ptype != 'attachment' && $ptype != 'revision'){
				if($ptype == 'post'){$addlpaaiptxt = ' checked="checked"';}else{$addlpaaiptxt = '';}
				$ptypeHTML .= '<div class="apip-ptype"><label><input class="apip-ptypecb" group="appiptypes" type="radio" name="post_type" value="'.$ptype.'"'.$addlpaaiptxt.' /> '.$ptype.'</label></div>';
			}
		}
	}
	$ptypeHTML .= '</div>';
	$extrasec = array();
	$extrasec[] = '&nbsp;&nbsp;<input type="checkbox" name="amazon-product-amazon-desc" value="1" /> <label for="amazon-product-amazon-desc"><strong>' . __("Show Amazon Description?", 'amazon-product-in-a-post-plugin' ) . '</strong></label> <em>'.__('if available. This will be IN ADDITION TO your own content.','amazon-product-in-a-post-plugin').'</em><br />';
	$extrasec[] = '&nbsp;&nbsp;<input type="checkbox" name="amazon-product-show-gallery" value="1" /> <label for="amazon-product-show-gallery"><strong>' . __("Show Image Gallery?", 'amazon-product-in-a-post-plugin' ) . '</strong></label> <em>'.__('if available (Consists of Amazon Approved images only). Not all products have an Amazon Image Gallery.','amazon-product-in-a-post-plugin').'</em><br />';
	$extrasec[] = '&nbsp;&nbsp;<input type="checkbox" name="amazon-product-show-features" value="1" /> <label for="amazon-product-show-features"><strong>' . __("Show Amazon Features?", 'amazon-product-in-a-post-plugin' ) . '</strong></label> <em>'.__('if available. Not all items have this feature.','amazon-product-in-a-post-plugin').'</em><br />';
	$extrasec[] = '&nbsp;&nbsp;<input type="checkbox" name="amazon-product-show-used-price" value="1" /> <label for="amazon-product-show-used-price"><strong>' . __("Show Amazon Used Price?", 'amazon-product-in-a-post-plugin' ) . '</strong></label> <em>'.__('if available. Not all items have this feature.','amazon-product-in-a-post-plugin').'</em><br />';
	$extrasec[] = '&nbsp;&nbsp;<input type="checkbox" name="amazon-product-show-list-price" value="1" /> <label for="amazon-product-show-list-price"><strong>' . __("Show Amazon List Price?", 'amazon-product-in-a-post-plugin' ) . '</strong></label> <em>'.__('if available. Not all items have this feature.','amazon-product-in-a-post-plugin').'</em><br />';
	//$extrasec[] = '&nbsp;&nbsp;<input type="checkbox" name="amazon-product-show-saved-amt" value="1" /> <label for="amazon-product-show-saved-amt"><strong>' . __("Show Saved Amount?", 'amazon-product-in-a-post-plugin' ) . '</strong></label> <em>'.__('if available. Not all items have this feature.','amazon-product-in-a-post-plugin').'</em><br />';
	//$extrasec[] = '&nbsp;&nbsp;<input type="checkbox" name="amazon-product-timestamp" value="1" /> <label for="amazon-product-show-timestamp"><strong>' . __("Show Price Timestamp?", 'amazon-product-in-a-post-plugin' ) . '</strong></label> <em>'.__('for example:','amazon-product-in-a-post-plugin').'</em>'.__('<div class="appip-em-sample">&nbsp;&nbsp;Amazon.com Price: $32.77 (as of 01/07/2008 14:11 PST - <span class="appip-tos-price-cache-notice-tooltip" title="">Details</span>)<br/>&nbsp;&nbsp;Amazon.com Price: $32.77 (as of 14:11 PST - <span class="appip-tos-price-cache-notice-tooltip" title="">More info</span>)</div>','amazon-product-in-a-post-plugin').'<br />';
	echo '<form method="post" action="">
		<input type="hidden" name="amazon-product-isactive" id="amazon-product-isactive" value="1" />
		<input type="hidden" name="post_save_type_apipp" id="post_save_type_apipp" value="1" />
		<input type="hidden" name="post_author" id="post_author" value="'.$myuserpost.'" />
		<input type="hidden" name="amazon-product-content-hook-override" id="amazon-product-content-hook-override" value="2" />
		<div align="left">
			<table border="0" cellpadding="2" cellspacing="0" class="apip-new-pppy">
				<tr>
					<td align="left" valign="top">'.__('Title', 'amazon-product-in-a-post-plugin').':</td>
					<td align="left"><input type="text" name="post_title" size="65" /><br/><em>If you want the post title to be the title of the product, you can leave this blank and the plugin will try to set the product title as the Post title.</em></td>
				</tr>
				<tr>
					<td align="left" valign="top">'.__('Post Status', 'amazon-product-in-a-post-plugin').':</td>
					<td align="left"><select size="1" name="post_status" >
					<option selected>draft</option>
					<option>publish</option>
					<option>private</option>
					</select></td>
				</tr>
				<tr>
					<td align="left" valign="top">'.__('Post Type', 'amazon-product-in-a-post-plugin').':</td>
					<td align="left">'.$ptypeHTML.'</td>
				</tr>
				<tr>
					<td align="left" valign="top">'.__('Amazon ASIN Number', 'amazon-product-in-a-post-plugin').':</td>
					<td align="left"><input type="text" name="amazon-product-single-asin" size="29" />(can use up to 10 comma separated ASINs)</td>
				</tr>
				<tr class="apip-extra-pad-bot">
					<td align="left" valign="top">'.__('Post Content', 'amazon-product-in-a-post-plugin').':</td>
					<td align="left">
					<textarea rows="11" name="post_content" id="post_content_app" cols="56"></textarea></td>
				</tr>
				<tr class="apip-extra-pad-bot">
					<td align="left" valign="top">'.__('Product Location', 'amazon-product-in-a-post-plugin').':</td>
					<td align="left">
						&nbsp;&nbsp;<input type="radio" name="amazon-product-content-location[1][]" value="1"  checked /> '.__('<strong>Above Post Content </strong><em>- Default - Product will be first then post text</em>', 'amazon-product-in-a-post-plugin').'<br />
						&nbsp;&nbsp;<input type="radio" name="amazon-product-content-location[1][]" value="3" /> '.__('<strong>Below Post Content</strong><em> - Post text will be first then the Product</em>', 'amazon-product-in-a-post-plugin').'<br />
						&nbsp;&nbsp;<input type="radio" name="amazon-product-content-location[1][]" value="2" /> '.__('<strong>Post Text becomes Description</strong><em> - Post text will become part of the Product layout</em>', 'amazon-product-in-a-post-plugin').'<br />
					</td>
				</tr>
				<tr class="apip-extra-pad-bot">
					<td align="left" valign="top">'.__('Additional Items', 'amazon-product-in-a-post-plugin').':</td>
					<td align="left">'.implode("\n",$extrasec).'</td>
				</tr>
				'.$section.'
			</table>
			<br/><input type="submit" value="'.__('Create Amazon Product Post & Return Here','amazon-product-in-a-post-plugin').'" name="createpost" class="button-primary" /> <!--input type="submit" value="'.__('Create Amazon Product Post & Edit NOW','amazon-product-in-a-post-plugin').'" name="createpost" class="button-primary" /-->
		</div>
	</form>
	</div>';
}