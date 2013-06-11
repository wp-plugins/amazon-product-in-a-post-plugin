<?php
// Tools
global $appipBulidBox;
//ACTIONS
	add_action('init', 'apipp_parse_new', 1, 0); 
	add_action('admin_menu', create_function("$appipBulidBox","if( function_exists( 'add_meta_box' ))add_meta_box( 'amazonProductInAPostBox1', __( 'Amazon Product In a Post Settings', 'appplugin' ), 'amazonProductInAPostBox1', 'post', 'normal', 'high' );"));
	add_action('admin_menu', create_function("$appipBulidBox","if( function_exists( 'add_meta_box' ))add_meta_box( 'amazonProductInAPostBox1', __( 'Amazon Product In a Post Settings', 'appplugin' ), 'amazonProductInAPostBox1', 'page', 'normal', 'high' );"));
	add_action('admin_menu', 'apipp_plugin_menu');
	add_action( 'network_admin_notices', 'appip_warning_notice');
	add_action( 'admin_notices', 'appip_warning_notice');
	
	
//FUNCTIONS
	function appip_warning_notice(){
		if( $_REQUEST['dismissmsg'] == '1'){update_option('appip_dismiss_msg',1);}
		$appip_publickey 	= get_option('apipp_amazon_publickey');
		$appip_privatekey 	= get_option('apipp_amazon_secretkey');
		$appip_partner_id 	= get_option('apipp_amazon_associateid');
		$appip_dismiss 		= get_option('appip_dismiss_msg',0);
		if($appip_publickey =='' || $appip_privatekey ==''){
			echo '<div class="error"><h2><strong>Amazon Product in a Post Important Message!</strong></h2><p>Please note: You need to add your Access Key ID and Secrect Access Key to the <a href="admin.php?page=apipp_plugin_admin">options page</a> before the plugin will display any Amazon Products!</p></div>';
		}elseif($appip_partner_id =='' && $appip_dismiss == 0){
			echo '<div class="error"><h2><strong>Amazon Product in a Post Important Message!</strong></h2><p>You need to enter your Amazon Partner ID in order to get credit for any products sold. <a href="admin.php?page=apipp_plugin_admin">enter your partner id here</a> or you can <a href="admin.php?page=apipp_plugin_admin&dismissmsg=1">dismiss this message</a></p></div>';
		}
	}
	function apipp_parse_new(){ //Custom Save Post items for Quick Add
		if(isset($_POST['createpost'])){ //form saved
			$teampappcats = array();
			global $post;
			if(isset($_POST['post_category_count']) && $_POST['post_type'] == 'post' ){
				$totalcategories = $_POST['post_category_count'];
				for($i=0;$i<=$totalcategories;$i++){
						$teampappcats[$i] = $_POST['post_category'.$i];
				}
				$post_array = array(
					'post_author' 	=> $_POST['post_author'],
				    'post_title' 	=> $_POST['post_title'],
				    'post_status' 	=> $_POST['post_status'],
				    'post_type' 	=> $_POST['post_type'],
				    'post_content' 	=> $_POST['post_content'],
				    'post_category' => $teampappcats,
				);
				$createdpostid = wp_insert_post($post_array);
			}else{
				$post_array = array(
					'post_author' 	=> $_POST['post_author'],
				    'post_title' 	=> $_POST['post_title'],
				    'post_status' 	=> $_POST['post_status'],
				    'post_type' 	=> $_POST['post_type'],
				    'post_content' 	=> $_POST['post_content'],
				    'post_parent' 	=> 0,
				    'post_category' => ''
				    
				);
				$createdpostid = wp_insert_post($post_array,'false');
			}
			if($createdpostid!=''){
				$newpost = get_post($createdpostid);
				ini_set('display_errors', 0);
				amazonProductInAPostSavePostdata($createdpostid,$newpost);
				header("Location: admin.php?page=apipp-add-new&appmsg=1");
			}else{
				header("Location: admin.php?page=apipp-add-new&appmsg=2");
			}
		}else{
			add_action('save_post', 'amazonProductInAPostSavePostdata', 1, 2); // save the custom fields
		}
		if(isset($_GET['appip_debug']) && ($_GET['appip_debug'] == get_option('apipp_amazon_debugkey') && get_option('apipp_amazon_debugkey') !='')){
			global $wpdb;
			global $aws_plugin_version;
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
			echo '$private_key: '. get_option('apipp_amazon_secretkey').'<br/>'; 
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
		// Use nonce for verification ... ONLY USE ONCE!
		echo '<input type="hidden" name="amazonpipp_noncename" id="amazonpipp_noncename" value="' . wp_create_nonce( plugin_basename(__FILE__) ) . '" />';
		echo '<input type="hidden" name="post_save_type_apipp" id="post_save_type_apipp" value="1" />';
		// The actual fields for data entry
		if(get_option('apipp_amazon_associateid')==''){
			echo '<div style="background-color: rgb(255, 251, 204);" id="message" class="updated fade below-h2"><p><b>WARNING:</b> You will not get credit for Amazon purchases until you add your Amazon Affiliate ID on the <a href="admin.php?page=apipp_plugin_admin">options</a> page.</p></div>';
		}
		echo '<label for="amazon-product-isactive"><b>' . __("Product is Active?", 'appplugin' ) . '</b></label> ';
		if(get_post_meta($post->ID, 'amazon-product-isactive', true)!=''){$menuhide="checked";}else{$menuhide="";}
		echo '<br /><br />&nbsp;&nbsp;<input type="checkbox" name="amazon-product-isactive" value="1" '.$menuhide.' /> <i>if checked the product will be live</i><br /><br />';
		echo '<label for="amazon-product-content-hook-override"><b>' . __("Hook into Content?", 'appplugin' ) . '</b></label> ';
		if(get_post_meta($post->ID, 'amazon-product-content-hook-override', true)=='2' || (get_post_meta($post->ID, 'amazon-product-content-hook-override', true)=='' && get_option('apipp_hook_content')==true)){$hookcontent="checked";}else{$hookcontent="";}
		echo '<br /><br />&nbsp;&nbsp;<input type="checkbox" name="amazon-product-content-hook-override" value="2" '.$hookcontent.' /> <i>if checked the product will be added when <code>the_content()</code> is used. On by default unless set in options.</i><br /><br />';
		echo '<label for="amazon-product-excerpt-hook-override"><b>' . __("Hook into Excerpt?", 'appplugin' ) . '</b></label> ';
		if(get_post_meta($post->ID, 'amazon-product-excerpt-hook-override', true)=='2' || (get_post_meta($post->ID, 'amazon-product-excerpt-hook-override', true)=='' && get_option('apipp_hook_excerpt')==true)){$hookexcerpt="checked";}else{$hookexcerpt="";}
		echo '<br /><br />&nbsp;&nbsp;<input type="checkbox" name="amazon-product-excerpt-hook-override" value="2" '.$hookexcerpt.' /> <i>if checked the product will be added to the EXCERPT when <code>the_excerpt()</code> is used. Off by default unless set in options.</i><br /><br />';
		echo '<label for="amazon-product-singular-only"><b>' . __("Show Only on Single Page?", 'appplugin' ) . '</b></label> ';
		if(get_post_meta($post->ID, 'amazon-product-singular-only', true)=='1'){$singleonly="checked";}else{$singleonly="";}
		echo '<br /><br />&nbsp;&nbsp;<input type="checkbox" name="amazon-product-singular-only" value="1" '.$singleonly.' /> <i>if checked the product will only show when in single view. In other words, only when on the permalink page. Off by default.</i><br /><br />';
		echo '<label for="amazon-product-newwindow"><b>' . __("Open Product Link in New Window?", 'appplugin' ) . '</b></label> ';
		if(get_post_meta($post->ID, 'amazon-product-newwindow', true)=='2' || (get_post_meta($post->ID, 'amazon-product-newwindow', true)=='' && get_option('apipp_open_new_window')==true)){$newwin="checked";}else{$newwin="";}
		echo '<br /><br />&nbsp;&nbsp;<input type="checkbox" name="amazon-product-newwindow" value="2" '.$newwin.' /> <i>if checked the product will open a new browser window. Off by default unless set in options.</i><br /><br />';
		//echo '<label for="amazon-product-showusbutton"><b>' . __("Show Amazon.com button along with Local button?", 'appplugin' ) . '</b></label> ';
		//if(get_post_meta($post->ID, 'amazon-product-showusbutton', true)=='1' || (get_post_meta($post->ID, 'amazon-product-showusbutton', true)=='' && get_option('apipp_open_showusbutton')==true)){$newwin="checked";}else{$newwin="";}
		//echo '<br /><br />&nbsp;&nbsp;<input type="checkbox" name="amazon-product-showusbutton" value="2" '.$newwin.' /> <i>if checked and your Locale is set to anything other than US(.com), an addtional Buy button with Amazon.com will be shown. Off by default.</i><br /><br />';
	
		echo '<label for="amazon-product-content-location"><b>' . __("Where would you like your product to show within the post?", 'appplugin' ) . '</b></label>';
		echo '<br /><br />&nbsp;&nbsp;<input type="radio" name="amazon-product-content-location" value="1" '. ((get_post_meta($post->ID, 'amazon-product-content-location', true)==='1') || (get_post_meta($post->ID, 'amazon-product-content-location', true)=='') ? "checked" : '') .' /> Above Post Content - <i>Default - Product will be first then post text</i><br />';
		echo '&nbsp;&nbsp;<input type="radio" name="amazon-product-content-location" value="3" '. ((get_post_meta($post->ID, 'amazon-product-content-location', true)==='3') ? "checked" : '') .' /> Below Post Content - <i>Post text will be first then the Product</i><br />';
		echo '&nbsp;&nbsp;<input type="radio" name="amazon-product-content-location" value="2" '. ((get_post_meta($post->ID, 'amazon-product-content-location', true)==='2') ? "checked" : '') .' /> Post Text becomes Description - <i>Post text will become part of the Product layout</i><br /><br />';
		
		echo '<br /><label for="amazon-product-single-asin"><b>' . __("Amazon Product ASIN (ISBN-10)", 'appplugin' ) . '</b></label> ';
		echo '<br /><br />&nbsp;&nbsp;<input type="text" name="amazon-product-single-asin" id="amazon-product-single-asin" value="'.get_post_meta($post->ID, 'amazon-product-single-asin', true).'" /> <i>You will need to get this from <a href="http://amazon.com/">Amazon.com</a></i><br /><br />';
	
	}
	
	/* When the post is saved, saves our custom data */
	function amazonProductInAPostSavePostdata($post_id, $post) {
		if($post_id==''){$post_id=$post->ID;}
		if(!isset($_POST['post_save_type_apipp'])){return;}
		$mydata = array();
		$mydata['amazon-product-isactive'] = $_POST['amazon-product-isactive'];
		$mydata['amazon-product-content-location'] = $_POST['amazon-product-content-location'];
		$mydata['amazon-product-single-asin'] = $_POST['amazon-product-single-asin'];
		$mydata['amazon-product-excerpt-hook-override'] = $_POST['amazon-product-excerpt-hook-override'];
		$mydata['amazon-product-content-hook-override'] = $_POST['amazon-product-content-hook-override'];
		$mydata['amazon-product-newwindow'] = $_POST['amazon-product-newwindow'];
		//$mydata['amazon-product-showusbutton'] = $_POST['amazon-product-showusbutton'];
		$mydata['amazon-product-singular-only'] = $_POST['amazon-product-singular-only'];
		if($mydata['amazon-product-isactive']=='' && $mydata['amazon-product-single-asin']==""){$mydata['amazon-product-content-location']='';}
		if($mydata['amazon-product-excerpt-hook-override']==''){$mydata['amazon-product-excerpt-hook-override']='3';}
		if($mydata['amazon-product-content-hook-override']==''){$mydata['amazon-product-content-hook-override']='3';}
		if($mydata['amazon-product-newwindow']==''){$mydata['amazon-product-newwindow']='3';}
		
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
		$mydata['amazon-product-isactive'] 				= $post['amazon-product-isactive'];
		$mydata['amazon-product-content-location'] 		= $post['amazon-product-content-location'];
		$mydata['amazon-product-single-asin']			= $post['amazon-product-single-asin'];
		$mydata['amazon-product-excerpt-hook-override'] = $post['amazon-product-excerpt-hook-override'];
		$mydata['amazon-product-content-hook-override'] = $post['amazon-product-content-hook-override'];
		$mydata['amazon-product-newwindow'] 			= $post['amazon-product-newwindow'];
		$mydata['amazon-product-singular-only']			= $post['amazon-product-singular-only'];
		if($mydata['amazon-product-isactive']=='' && $mydata['amazon-product-single-asin']==""){$mydata['amazon-product-content-location']='';}
		if($mydata['amazon-product-excerpt-hook-override']==''){$mydata['amazon-product-excerpt-hook-override']='3';}
		if($mydata['amazon-product-content-hook-override']==''){$mydata['amazon-product-content-hook-override']='3';}
		if($mydata['amazon-product-newwindow']==''){$mydata['amazon-product-newwindow']='3';}
		
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
		add_utility_page( 'Amazon Product In a Post Plugin','Amazon Product', 'edit_posts', 'apipp-main-menu','apipp_main_page', plugins_url( '/images/aicon-16.png' , dirname(__FILE__)) );
	  	add_submenu_page( 'apipp-main-menu', "Getting Started", "Getting Started", 'edit_posts' , 'apipp-main-menu', 'apipp_main_page');
	  	add_submenu_page( 'apipp-main-menu', "{$fullname_apipp} Options", "Amazon PIP Options", 'manage_options' , "apipp_plugin_admin", 'apipp_options_add_subpage');
		add_submenu_page( 'apipp-main-menu', 'Shortcode Usage', 'Shortcode Usage', 'manage_options', 'apipp_plugin-shortcode', 'apipp_shortcode_help_page' );
		add_submenu_page( 'apipp-main-menu', 'FAQs/Help', 'FAQs/Help', 'manage_options', $shortname_apipp.'_plugin-faqs', 'apipp_options_faq_page' );
	  	add_submenu_page( 'apipp-main-menu', "Product Cache", "Product Cache", 'edit_posts' , "apipp-cache-page", 'apipp_cache_page');
	  	add_submenu_page( 'apipp-main-menu', "New Amazon Post", "New Amazon Post", 'edit_posts' , "apipp-add-new", 'apipp_add_new_post');
		//add_submenu_page( 'apipp-main-menu', 'Layout Styles', 'Layout Styles', 'manage_options', 'appip-layout-styles', 'apipp_templates');
	  	//add_submenu_page('apipp-main-menu', 'New Product Post', 'New Product Post', 'edit_posts', 'apipp-add-new', 'apipp_add_new_post');
	}

function apipp_cache_page(){
	global $current_user, $wpdb;
	global $aws_plugin_version;
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	echo '
		<script type="text/javascript">
			jQuery(function() {
				jQuery(\'.appip-cache-del\').live(\'click\',function(eventa){
					var r=confirm("Are you sure you want to delete this cache?");
					if (r==true){
						var buttonid = jQuery(this).attr(\'id\');
						var appbtnid = buttonid.replace("appip-cache-","");
						jQuery.ajax({
							url: "'.get_bloginfo('url').'/",
							beforeSend: function ( xhr ) {
								xhr.overrideMimeType("text/plain; charset=x-user-defined");
							},
							data:{"appip-cache-id":appbtnid,"appip-cache-del":"dodel"}
						}).done(function ( data ) {
							if( console && console.log ) {
								console.log("cache ", data);
							}
							if(data == "deleted"){
								jQuery("."+buttonid+"-row").remove();
							}else{
								alert("there was an error - the cache could not be delete");
							}
						});
						eventa.preventDefault();
						return;
					}else{
						eventa.preventDefault();
						return;
					}
				});
			});
		</script>
	';
	echo '<div class="wrap">';
	echo '<div id="icon-amazon" class="icon32"><br /></div><h2>Amazon Product In A Post CACHE</h2>';
		if($_GET['appmsg']=='1'){	echo '<div style="background-color: rgb(255, 251, 204);" id="message" class="updated fade below-h2"><p><b>Product post has been saved. To edit, use the standard Post Edit options.</b></p></div>';}
	echo '	<div class="wrapper"><br />';
	$checksql= "SELECT body,Cache_id,URL,updated,( NOW() - Updated )as Age FROM ".$wpdb->prefix."amazoncache ORDER BY Updated DESC;";
	$result = $wpdb->get_results($checksql);
	echo '<br/>';
	echo '<h3>Amazon Product in a Post Plugin CACHE</h3>';
	echo '<table class="wp-list-table widefat fixed" cellspacing="0">';
	echo '<thead><tr><th class="manage-column" style="width:75px;">Cache ID</th><th class="manage-column">Unique Call UI</th><th class="manage-column" style="width:150px;">Last Updated</th><th class="manage-column" style="width:100px;"></th></tr></thead>';
	echo '<tfoot><tr><th class="manage-column" style="width:75px;">Cache ID</th><th class="manage-column">Unique Call UI</th><th class="manage-column" style="width:150px;">Last Updated</th><th class="manage-column" style="width:100px;"></th></tr></tfoot>';
	if(!empty($result)){
		echo '<tbody id="the-list">';
		$appct = 0;
		foreach($result as $psxml){
			if($appct&1){	echo '<tr class="alternate iedit appip-cache-'. $psxml->Cache_id.'-row">';}else{echo '<tr class="iedit appip-cache-'. $psxml->Cache_id.'-row">';}
			//echo '<td>'.htmlspecialchars($psxml->body).'</td>';
			echo '<td>'. $psxml->Cache_id.'</td>';
			echo '<td>'. $psxml->URL.'<textarea style="display:none;">'.htmlspecialchars($psxml->body).'</textarea></td>';
			echo '<td>'. $psxml->updated.'</td>';
			echo '<td><a href="#" class="button appip-cache-del" id="appip-cache-'. $psxml->Cache_id.'">delete cache</a></td>';
			echo '</tr>';
			$appct++;
		}
	}else{
		echo '<tbody id="the-list"><tr class="alternate iedit appip-cache-'. $psxml->Cache_id.'-row"><td colspan="4">no cached products at this time</td></tr>';
	}
	echo '</tbody>';
	echo '</table>';
	echo '	</div>';
	echo '</div>';
}

function apipp_shortcode_help_page(){
	global $current_user, $wpdb;
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	echo '<div class="wrap">';
	echo '<div id="icon-amazon" class="icon32"><br /></div><h2>Amazon Product In a Post Shortcode Usage</h2>';
	if($_GET['appmsg']=='1'){	echo '<div style="background-color: rgb(255, 251, 204);" id="message" class="updated fade below-h2"><p><b>Product post has been saved. To edit, use the standard Post Edit options.</b></p></div>';}
	echo '	<div class="wrapper" style="font-size:14px;"><br />
<p>As of Version 3.5.1, there will be a new shortcode system in place. The new Shortcode will be <code style="font-family:monospace;font-size:14px;">[AMAZONPRODUCTS asin="B0084IG8TM"]</code> (instead of <code style="font-family:monospace;font-size:14px;">[AMAZONPRODUCT=B0084IG8TM]</code>). <br>Note the <code style="font-family:monospace;font-size:14px;">S</code> on <code style="font-family:monospace;font-size:14px;">AMAZONPRODUCTS</code>. The old method is still supported, but has limited functionality so it is recommended that you switch to the new shortcode when you can.<br>
<br>
And additional Shortcode has been added to make adding elements of a product into the text of the page easier. The additional shortcode is <code style="font-family:monospace;font-size:14px;">[amazon-elements]</code>. This new shortcode has many parameters and is very useful for adding bits and pieces of a product to the text. </p>
<p>For more information about <code style="font-family:monospace;font-size:14px;">[amazon-elements]</code>, 
<a href="#amazonelements">click here</a>.<br>
<br>
<h3>[AMAZONPRODUCTS] Shortcode</h3>
<a name="amazonproducts"></a>The New shortcode should be used as follows:<br>
<br>
Usage in the most basic form is simply the Shortcode and the ASIN written as (where the XXXXXXXXX is the Amazon ASIN):<br>
<code style="font-family:monospace;font-size:14px;">[AMAZONPRODUCTS asin="XXXXXXXXXX"]</code><br>
<br>
There are additional parameters that can be added if you need them. The parameters are<br><code style="font-family:monospace;font-size:14px;">locale</code>, <code style="font-family:monospace;font-size:14px;">desc</code>, <code style="font-family:monospace;font-size:14px;">features</code>, <code style="font-family:monospace;font-size:14px;">listprice</code>, <code style="font-family:monospace;font-size:14px;">partner_id</code>, <code style="font-family:monospace;font-size:14px;">private_key</code>, and <code style="font-family:monospace;font-size:14px;">public_key</code><br>
<br>
A description of each parameter:</p>
<ul style="margin-left:25px;list-style-type:disc;">
	<li><code style="font-family:monospace;font-size:14px;">asin</code> &mdash; this is the ASIN or ASINs up to 10 comma separated</li>
	<li><code style="font-family:monospace;font-size:14px;">locale</code> &mdash; this is the Amazon locale you want to get the product from, i.e., com, co.uk, fr, etc. default is your plugin setting</li>
	<li><code style="font-family:monospace;font-size:14px;">desc</code> &mdash; using 1 shows Amazon description (if available) and 0 hides it &mdash; default is 0.</li>
	<li><code style="font-family:monospace;font-size:14px;">features</code> &mdash; using 1 shows Amazon Features (if available) and 0 hides it - default is 0.</li>
	<li><code style="font-family:monospace;font-size:14px;">listprice</code> &mdash; using 1 shows the list price and 0 hides it &mdash; default is 1.</li>
	<li><code style="font-family:monospace;font-size:14px;">partner_id</code> &mdash; allows you to add a different parent ID if different for other locale &mdash; default is ID in settings.</li>
	<li><code style="font-family:monospace;font-size:14px;">private_key</code> &mdash; allows you to add different private key for locale if different &mdash; default is private key in settings.</li>
	<li><code style="font-family:monospace;font-size:14px;">public_key</code> &mdash; allows you to add a different private key for locale if different &mdash; default is public key in settings.</li>
</ul>
<p>Examples of it&rsquo;s usage:</p>
<ul style="margin-left:25px;list-style-type:disc;">
	<li>If you want to add a .com item and you have the same partner id, public key, private key and want the features showing:<br>
	<code style="font-family:monospace;font-size:14px;">[AMAZONPRODUCTS asin="B0084IG8TM" features="1" locale="com"]</code></li>
	<li>If you want to add a .com item and you have a different partner id, public key, private key and want the description showing but features not showing:<br>
	<code style="font-family:monospace;font-size:14px;">[AMAZONPRODUCTS asin="B0084IG8TM,B005LAIHPE" locale="com" public_key="AKIAJDRNJ6O997HKGXW" private_key="Nzg499eVysc5yjcZwrIV3bhDti/OGyRHEYOWO005" partner_id="mynewid-20"]</code></li>
	<li>If you just want to use your same locale but want 2 items with no list price and features showing:<br>
	<code style="font-family:monospace;font-size:14px;">[AMAZONPRODUCTS asin="B0084IG8TM,B005LAIHPE" features="1" listprice="0"]</code></li>
	<li>If you just want 2 products with regular settings:<br>
	<code style="font-family:monospace;font-size:14px;">[AMAZONPRODUCTS asin="B0084IG8TM,B005LAIHPE"]</code></li>
	<li>If you want to add text to a product:<br>
	<code style="font-family:monospace;font-size:14px;">[AMAZONPRODUCTS asin="B0084IG8TM"]your text can go here![/AMAZONPRODUCTS]</code></li>
</ul><a name="amazonelements"></a>
<div style="clear:both;margin-top:55px;">
<h3>[amazon-elements] Shortcode</h3></div>
<p>New shortcode implementation for elements only &mdash; for when you may only want specific element(s) like the title, price and image or image and description, or the title and the buy now button, etc.</p>
<ul style="margin-left:25px;list-style-type:disc;">
	<li><code style="font-family:monospace;font-size:14px;">asin</code> &mdash; the Amazon ASIN (up to 10 comma sep).<span style="color:#ff0000;"> Required </span></li>
	<li><code style="font-family:monospace;font-size:14px;">locale</code> &mdash; the amazon locale, i.e., co.uk, es. This is handy of you need a product from a different locale than your default one. Applies to all ASINs in list. (optional)</li>
	<li><code style="font-family:monospace;font-size:14px;">gallery</code> &mdash; use a value of 1 to show extra photos if a product has them. Applies to all ASINs in list. (optional)</li>
	<li><code style="font-family:monospace;font-size:14px;">partner_id</code> &mdash; your amazon partner id. default is the one in the options. You can set a different one here if you have a different one for another locale or just want to split them up between multiple ids. Applies to all ASINs in list. (optional)</li>
	<li><code style="font-family:monospace;font-size:14px;">private_key</code> &mdash; amazon private key. Default is one set in options. You can set a different one if needed for another locale. Applies to all ASINs in list. (optional)</li>
	<li><code style="font-family:monospace;font-size:14px;">public_key</code> &mdash; amazon public key. Default is one set in options. You can set a different one if needed for another locale. Applies to all ASINs in list. (optional)</li>
	<li><code style="font-family:monospace;font-size:14px;">showformat</code> &mdash; show or hide the format in the title i.e., &quot;Some Title (DVD)&quot; or &quot;Some Title (BOOK)&quot;. 1 to show 0 to hide. Applies to all ASINs. Default is 1. (optional) </li>
	<li><code style="font-family:monospace;font-size:14px;">msg_instock</code> &mdash; message to display when an image is in stock. Applies to all ASINs. (optional) </li>
	<li><code style="font-family:monospace;font-size:14px;">msg_outofstock</code> &mdash; message to display when an image is out of stock. Applies to all ASINs in list. (optional)</li>
	<li><code style="font-family:monospace;font-size:14px;">target</code> &mdash; default is &quot;_blank&quot;. Applies to all ASINs in list. (optional)</li>
	<li><code style="font-family:monospace;font-size:14px;">fields</code> &mdash; Fields you want to return. And valid return field form Amazon API (you could see API for list) or common fields of: title, lg-image,md-image,sm-image, large-image-link,description (or desc),ListPrice, new-price,LowestUsedPrice, button. You should have at least one field when using this shortcode, as no field will return a blank result. Applies to all ASINs in list. (optional)</li>
	<li><code style="font-family:monospace;font-size:14px;">labels</code> &mdash; Labels that correspond to the fields (if you want custom labels). They should match the fields and be comma separated and :: separated for the field name and value i.e., field name::label text,field-two::value 2, etc. These can be ASIN specific. If you have 2 ASINs, the first label field will correspond to the first ASIN, the second to the second one, and so on. (optional).</li>
	<li><code style="font-family:monospace;font-size:14px;">button_url</code> &mdash; URL for a button image, if you want to use a different image than the default one. ASIN Specific - separate the list of URLs with a comma to correspond with the ASIN. i.e., if you had 3 ASINs and wanted the first and third to have custom buttons, but the second to have the default button, use <code style="font-family:monospace;font-size:14px;">button_url="http://first.com/image1.jpg,,http://first.com/image1.jpg"</code> (optional)</li>
</ul>
Example of the new elements shortcode usage:
<ul style="margin-left:25px;list-style-type:disc;">
	<li>if you want to have a product with only a large image, the title and 
	button, you would use:<br>
	<code style="font-family:monospace;font-size:14px;">[amazon-element asin=&quot;0753515032&quot; fields=&quot;title,lg-image,large-image-link,button&quot;]</code></li>
	<li>If you want that same product to have the description, you would use:<br>
	<code style="font-family:monospace;font-size:14px;">[amazon-element asin=&quot;0753515032&quot; fields=&quot;title,lg-image,large-image-link,<font color="#FF0000">desc</font>,button&quot;]</code></li>
	<li>If you want that same product to have the list price and the new price, 
	you would use:<br>
	<code style="font-family:monospace;font-size:14px;">[amazon-element asin=&quot;0753515032&quot; fields=&quot;title,lg-image,large-image-link,desc,<font color="#FF0000">ListPrice,new-price,button&quot; msg_instock=&quot;in 
	Stock&quot; msg_outofstock=&quot;no more left!&quot;</font>]<br>
	</code>The msg_instock and msg_outofstock are optional fields.</li>
	<li>If you want to add som of your own text to a product, and makeit part of 
	the post, you could do something like this:<code style="font-family:monospace;font-size:14px;"><br>
	[amazon-element asin=&quot;0753515032&quot; fields=&quot;title,lg-image,large-image-link&quot; labels=&quot;large-image-link::click for larger image:,title-wrap::h2,title::Richard Branson: Business Stripped Bare&quot;]Some normal content text here.[amazon-element asin=&quot;0753515032&quot; fields=&quot;desc,gallery,ListPrice,new-price,LowestUsedPrice,button&quot; labels=&quot;desc::Book Description:,ListPrice::SRP:,new-price::New From:,LowestUsedPrice::Used From:&quot; msg_instock=&quot;Available&quot;]</code></li>
</ul>
<p>Available Fields for the shortcode:</p>
<h4>Common Items</h4>
These are generally common in all products (if available)
<ul style="font-family:monospace;margin-left:15px;">
<li>ASIN</li>
<li>URL</li>
<li>Title</li>
<li>SmallImage</li>
<li>MediumImage</li>
<li>LargeImage</li>
<li>AddlImages</li>
<li>Feature</li>
<li>Format</li>
<li>PartNumber</li>
<li>ProductGroup</li>
<li>ProductTypeName</li>
<li>ISBN</li>
<li>ItemDesc</li>
<li>ListPrice</li>
<li>SKU</li>
<li>UPC</li>
<li>CustomerReviews</li>
</ul>
<h4>Offer/Pricing Elements</h4>
These are generally returned for most products. 
<ul style="font-family:monospace;margin-left:15px;">
<li>LowestNewPrice</li>
<li>LowestUsedPrice</li>
<li>LowestRefurbishedPrice</li>
<li>LowestCollectiblePrice</li>
<li>MoreOffersUrl</li>
<li>NewAmazonPricing</li>
<li>TotalCollectible</li>
<li>TotalNew</li>
<li>TotalOffers</li>
<li>TotalRefurbished</li>
<li>TotalUsed</li>
</ul>
<h4>Items Attributes</h4>
Available only to their select product groups and not available in all locales. Try it first 
to see if it returns a value. <br/>
For example, the Actor field is not going to be returned if the product is a computer 
or some form of electronics, but would be returned if the product was a DVD or 
Blu-ray Movie. 
<ul style="font-family:monospace;margin-left:15px;">
<li>Actor</li>
<li>Artist</li>
<li>AspectRatio</li>
<li>AudienceRating</li>
<li>AudioFormat</li>
<li>Author</li>
<li>Binding</li>
<li>Brand</li>
<li>CatalogNumberList</li>
<li>Category</li>
<li>CEROAgeRating</li>
<li>ClothingSize</li>
<li>Color</li>
<li>Creator</li>
<li>Department</li>
<li>Director</li>
<li>EAN</li>
<li>EANList</li>
<li>Edition</li>
<li>EISBN</li>
<li>EpisodeSequence</li>
<li>ESRBAgeRating</li>
<li>Genre</li>
<li>HardwarePlatform</li>
<li>HazardousMaterialType</li>
<li>IsAdultProduct</li>
<li>IsAutographed</li>
<li>IsEligibleForTradeIn</li>
<li>IsMemorabilia</li>
<li>IssuesPerYear</li>
<li>ItemDimensions</li>
<li>ItemPartNumber</li>
<li>Label</li>
<li>Languages</li>
<li>LegalDisclaimer</li>
<li>MagazineType</li>
<li>Manufacturer</li>
<li>ManufacturerMaximumAge</li>
<li>ManufacturerMinimumAge</li>
<li>ManufacturerPartsWarrantyDescription</li>
<li>MediaType</li>
<li>Model</li>
<li>ModelYear</li>
<li>MPN</li>
<li>NumberOfDiscs</li>
<li>NumberOfIssues</li>
<li>NumberOfItems</li>
<li>NumberOfPages</li>
<li>NumberOfTracks</li>
<li>OperatingSystem</li>
<li>PackageDimensions</li>
<li>PackageDimensionsWidth</li>
<li>PackageDimensionsHeight</li>
<li>PackageDimensionsLength</li>
<li>PackageDimensionsWeight</li>
<li>PackageQuantity</li>
<li>PictureFormat</li>
<li>Platform</li>
<li>ProductTypeSubcategory</li>
<li>PublicationDate</li>
<li>Publisher</li>
<li>RegionCode</li>
<li>ReleaseDate</li>
<li>RunningTime</li>
<li>SeikodoProductCode</li>
<li>ShoeSize</li>
<li>Size</li>
<li>Studio</li>
<li>SubscriptionLength</li>
<li>TrackSequence</li>
<li>TradeInValue</li>
<li>UPCList</li>
<li>Warranty</li>
<li>WEEETaxValue </li>
</ul>';
	echo '	</div>';
	echo '</div>';

}	
function apipp_main_page(){
	global $current_user, $wpdb;
	global $aws_plugin_version;
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	echo '<div class="wrap">';
	echo'<style type="text/css">small{font-size:13px;color:#777;line-height: 19px;}.steps-wrapper div{margin-left:15px;}.steps-wrapper div p{margin-left:25px;}.steps-wrapper div img{margin:20px 10px 20px 10px;}.steps-wrapper ul{margin-left: 25px;list-style-type: none;margin: 25px 0 25px 28px;border-left: 10px solid #eaeaea;padding-left: 16px;}</style>';
	echo '<div id="icon-amazon" class="icon32"><br /></div><h2>Amazon Product In A Post - GETTING STARTED</h2>';
	echo '	<div class="wrapper"><br /><br/>';?>
<div class="steps-wrapper">
<p>There are 2 steps to using this plug-in to make additional income as an Amazon Affiliate. The first is to sign up for an Amazon Affiliate Account. The second is to get a set of Product Advertising API keys so the plug-in can access the product API and return the correct products. Both of these steps are a little intense, but if you have about 15-20 minutes, you can set up everything you need to start making money.</p>
<div>
	<h2>Step 1 - Getting Your Amazon Affiliate/Partner ID</h2>
	<p>Sign up for your Amazon Affiliate/Partner account at one of the following URLs (choose the correct link based on your Amazon location):
		<ul>
			<li>US: <a href="http://www.amazon.com/associates">http://www.amazon.com/associates</a> </li>
			<li>CA: <a href="http://www.amazon.ca/associates">http://www.amazon.ca/associates</a> </li>
			<li>UK: <a href="http://www.amazon.co.uk/associates">http://www.amazon.co.uk/associates</a> </li>
			<li>DE: <a href="http://partnernet.amazon.de/gp/associates/join/main.html">http://partnernet.amazon.de/gp/associates/join/main.html</a> </li>
			<li>FR: <a href="http://partenaires.amazon.fr/gp/associates/join">http://partenaires.amazon.fr/gp/associates/join</a> </li>
			<li>JP: <a href="http://www.amazon.co.jp/associates">http://www.amazon.co.jp/associates</a> </li>
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
		<li>United States (com): <a target="_blank" style="outline: 0px; color: rgb(33, 117, 155);" href="https://affiliate-program.amazon.com/gp/flex/advertising/api/sign-in.html">https://affiliate-program.amazon.com/gp/flex/advertising/api/sign-in.html</a></li>
		<li>United Kingdom (co.uk): <a target="_blank" style="outline: 0px; color: rgb(33, 117, 155);" href="https://affiliate-program.amazon.co.uk/gp/flex/advertising/api/sign-in.html">https://affiliate-program.amazon.co.uk/gp/flex/advertising/api/sign-in.html</a></li>
		<li>Canada (ca): <a target="_blank" style="outline: 0px; color: rgb(33, 117, 155);" href="https://associates.amazon.ca/gp/advertising/api/detail/main.html">https://associates.amazon.ca/gp/advertising/api/detail/main.html</a></li>
		<li>Germany (de): <a target="_blank" style="outline: 0px; color: rgb(33, 117, 155);" href="https://affiliate-program.amazon.com/gp/advertising/api/detail/main.html">https://affiliate-program.amazon.com/gp/advertising/api/detail/main.html</a></li>
		<li>France (fr): <a target="_blank" style="outline: 0px; color: rgb(33, 117, 155);" href="https://partenaires.amazon.fr/gp/advertising/api/detail/main.html">https://partenaires.amazon.fr/gp/advertising/api/detail/main.html</a></li>
		<li>Japan (jp): <a target="_blank" style="outline: 0px; color: rgb(33, 117, 155);" href="https://affiliate.amazon.co.jp/gp/advertising/api/detail/agreement.html">https://affiliate.amazon.co.jp/gp/advertising/api/detail/agreement.html</a></li>
		<li>Spain (es): <a target="_blank" style="outline: 0px; color: rgb(33, 117, 155);" href="https://afiliados.amazon.es/gp/advertising/api/detail/main.html">https://afiliados.amazon.es/gp/advertising/api/detail/main.html</a></li>
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
		 	<h2>Amazon Product in a Post FAQs/Help</h2>
			<div align="left"><p>The FAQS are now on a feed that can be updated on the fly. If you have a question and don\'t see an answer, please send an email to <a href="mailto:plugins@fischercreativemedia.com">plugins@fischercreativemedia.com</a> and ask your question. If it is relevant to the plugin, it will be added to the FAQs feed so it will show up here. Please be sure to include the plugin you are asking a question about (Amazon Product in a Post Plugin), the Debugging Key (located on the options page) and any other information like your WordPress version and examples if the plugin is not working correctly for you. THANKS!</p>
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
		echo '<a name="faq-top"></a><h2>Table of Contents</h2>';
		echo '<ol class="apipp-faq-links">';
			echo implode("\n",$linkfaq);
		echo '</ol>';
		echo '<h2>Questions/Answers</h2>';
		echo '<ul class="apipp-faq-answers">';
			echo implode("\n",$linkcontent);
		echo '</ul>';
		echo '
			</div>
		</div>';
}

	function apipp_templates(){
		//noting yet
	}
	
	function apipp_add_new_post(){
	global $user_ID;
	global $current_user;
	get_currentuserinfo();
    $myuserpost = $current_user->ID;
		echo '<div class="wrap"><div id="icon-amazon" class="icon32"><br /></div><h2>Add New Amazon Product Post</h2>';
		if($_GET['appmsg']=='1'){	echo '<div style="background-color: rgb(255, 251, 204);" id="message" class="updated fade below-h2"><p><b>Product post has been saved. To edit, use the standard Post Edit options.</b></p></div>';}
		echo '<br />This function will allow you to add a new post for an Amazon Product - no need to create a post then add the ASIN.<br />Once you add a Product Post, you can edit the information with the normal Post Edit options.<br />';
		?>	<form method="post" action="">
				<input type="hidden" name="amazon-product-isactive" id="amazon-product-isactive" value="1" />
				<input type="hidden" name="post_save_type_apipp" id="post_save_type_apipp" value="1" />
				<input type="hidden" name="post_author" id="post_author" value="<?php echo $myuserpost;?>" />
				<input type="hidden" name="amazon-product-content-hook-override" id="amazon-product-content-hook-override" value="1" />
				<div align="left">
					<table border="0" cellpadding="2" cellspacing="0" class="apip-new-pppy">
						<tr>
							<td align="left" valign="top">Title</td>
							<td align="left"><input type="text" name="post_title" size="65" /></td>
						</tr>
						<tr>
							<td align="left" valign="top">Post Status</td>
							<td align="left"><select size="1" name="post_status" >
							<option selected>draft</option>
							<option>publish</option>
							<option>private</option>
							</select></td>
						</tr>
						<tr>
							<td align="left" valign="top">Post Type</td>
							<td align="left">
							<?php
								$ptypes = get_post_types();
								$ptypeHTML = '<div class="apip-posttypes">';
								foreach($ptypes as $ptype){
									if($ptype != 'nav_menu_item' && $ptype != 'attachment' && $ptype != 'revision'){
										if($ptype == 'post'){$addlpaaiptxt = ' checked="checked"';}else{$addlpaaiptxt = '';}
								    	$ptypeHTML .= '<div class="apip-ptype"><label><input class="apip-ptypecb" group="appiptypes" type="radio" name="post_type" value="'.$ptype.'"'.$addlpaaiptxt.' /> '.$ptype.'</label></div>';
									}
								}
								$ptypeHTML .= '</div>';
								echo $ptypeHTML;
							?>
							</td>
						</tr>
						<tr>
							<td align="left" valign="top">Amazon Product ASIN Number</td>
							<td align="left"><input type="text" name="amazon-product-single-asin" size="29" /> 
							(may also be called ISBN-10)</td>
						</tr>
						<tr class="apip-extra-pad-bot">
							<td align="left" valign="top">Post Content</td>
							<td align="left">
							<textarea rows="11" name="post_content" id="post_content_app" cols="56"></textarea></td>
						</tr>
						<tr class="apip-extra-pad-bot">
							<td align="left" valign="top">Product Location</td>
							<td align="left">
					&nbsp;&nbsp;<input type="radio" name="amazon-product-content-location" value="1"  checked /> Above Post Content - <i>Default - Product will be first then post text</i><br />
					&nbsp;&nbsp;<input type="radio" name="amazon-product-content-location" value="3" /> Below Post Content - <i>Post text will be first then the Product</i><br />
					&nbsp;&nbsp;<input type="radio" name="amazon-product-content-location" value="2" /> Post Text becomes Description - <i>Post text will become part of the Product layout</i><br />
			</td>
						</tr>
						<tr class="apip-extra-pad-bot">
							<td align="left" valign="top">Post Category</td>
							<td align="left"><?php 
									$categories = get_categories('hide_empty=0');	
									$ii=0;
									foreach($categories as $cat) {
										echo '&nbsp;&nbsp;<input type="checkbox" name="post_category'.$ii,'" value="' . $cat->cat_ID . '" /> ' . $cat->cat_name . '<br />';
										$ii=$ii+1;
									} 
								 ?>
									<input type="hidden" name="post_category_count" value="<?php echo $ii-1;?>" />
							</td>
						</tr>
						<tr class="apip-extra-pad-bot apip-extra-pad-top">
							<td align="left" valign="top">&nbsp;</td>
							<td align="left">
							<input type="submit" value="Create Post" name="createpost" /></td>
						</tr>
					</table>
				</div>
			</form>
			</div>
		<?php }
