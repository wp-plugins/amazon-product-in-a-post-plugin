<?php
// Tools
global $appipBulidBox;
//ACTIONS
	add_action('init', 'apipp_parse_new', 1, 0); 
	add_action('admin_menu', create_function("$appipBulidBox","if( function_exists( 'add_meta_box' ))add_meta_box( 'amazonProductInAPostBox1', __( 'Amazon Product In a Post Settings', 'appplugin' ), 'amazonProductInAPostBox1', 'post', 'normal', 'high' );"));
	add_action('admin_menu', create_function("$appipBulidBox","if( function_exists( 'add_meta_box' ))add_meta_box( 'amazonProductInAPostBox1', __( 'Amazon Product In a Post Settings', 'appplugin' ), 'amazonProductInAPostBox1', 'page', 'normal', 'high' );"));
	add_action('admin_menu', 'apipp_plugin_menu');
	
//FUNCTIONS
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
	  	add_menu_page('Amazon Product In a Post New', 'New Amazon PIP', 'edit_posts', 'apipp-add-new', 'apipp_add_new_post',plugins_url( '/images/aicon-16.png' , dirname(__FILE__)));
		add_submenu_page( 'apipp-add-new', 'FAQs/Help', 'FAQs/Help', 'manage_options', $shortname_apipp.'_plugin-faqs', 'apipp_options_faq_page' );
	  	add_submenu_page('apipp-add-new', "{$fullname_apipp} Options", "Amazon PIP Options", 'manage_options' , $shortname_apipp."_plugin_admin", 'apipp_options_add_subpage');
		//add_submenu_page( 'apipp-add-new', 'Layout Styles', 'Layout Styles', 'manage_options', 'appip-layout-styles', 'imw_gen_term_order');
	  	//add_submenu_page('apipp-add-new', 'New Product Post', 'New Product Post', 'edit_posts', 'apipp-add-new', 'apipp_add_new_post');
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

function imw_gen_term_order(){
	global $current_user, $wpdb;
	echo '<div class="wrap">';
	echo '<h2 id="import">Create/Modify Layout Styles</h2>';
	echo '<div class="wrapper">';
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	if( $_REQUEST['mode'] == ''  ){
		//select Title Step 1
		?>
		<script type="text/javascript" >
			function checkUploadTermForm(tFm){
				var themsg = '';
				var total = 0;
				var posti = tFm.post_title.selectedIndex;
				if(posti == 0 ){themsg = themsg + "\n\t- Please Select Product to assign the images to.";}
				if(themsg !=''){alert("Please fix the following:"+themsg+"\n"); return false;}
				return true;
			}
		</script>
		<form method="post" enctype="multipart/form-data" action="admin.php?page=appip-layout-styles&mode=sort" name="prodform" onsubmit="return checkUploadTermForm(this);">
			<input type="hidden" value="1" name="_imgloc"/>
			<?php if ($nothere != 1){ ?>
				<br />
				<b>Select Style Name:</b><br />
				<select name="post_title" id="post_title">
					<option value="0">Select a Product...</option>
					<option value="default">default</option>
					
					<?php
					global $wpdb;
					//$result = $wpdb->get_results("SELECT ID, post_title FROM ".$wpdb->prefix."posts where (post_status='publish' or post_status='draft') and post_type='wolfeprods' order by post_title ASC");
					//foreach($result as $row) {
						//$the_prodid  = get_post_meta($row->ID,'_prd_id',true);
						//$thearr[$the_prodid] = '<option value="'.$row->ID.'">'. process_title($row->post_title)/*substr($row->post_title, 0, 50)*/.'</option>'."\r\n"; 
					//} 
					//ksort($thearr);
					//echo implode("\n",$thearr);
					?>
				</select>
				<br /><br />
			<?php } ?>
			<?php if ($nothere == ''){ ?><input type="submit" class="button" value="Continue >> " /><?php }?>
		</form>
	<?php	
	}elseif( $_REQUEST['post_title'] != '' && $_REQUEST['mode']=='sort' ){
		//order stars/dirs Step 2
		global $wpdb;
		$nothere  = 1;
		$thepostID = $_REQUEST['post_title'];
		$args = array('post_type' => 'wolfeprods','orderby'=>'term_order', 'order' => 'ASC');
		$terms = wp_get_object_terms($thepostID, 'starring', $args);
		$count = count($terms);
		echo '<div style="width: 30%; margin:10px 10px 10px 0px; padding:10px; border:0px none;float:left;"><strong>Order Stars</strong><br/><span style="font-size:10px;">drag and drop the stars into the correct order.</span>'; 
		if ( $count > 0 ){
			echo '<ul id="feature" style="width: 95%; margin:10px 10px 10px 0px; padding:10px; border:1px solid #B2B2B2; list-style:none;float:left;">';
			foreach ( $terms as $term ) {
				echo '<li id="f-'.$term->term_taxonomy_id.'" class="lineitem" style="background-color: #F1F1F1;border: 1px solid #B2B2B2;cursor: move;margin: 3px 0;overflow: hidden;padding: 2px 5px;">' . $term->name . '</li>';
			}
			echo "</ul>";
		}else{
			echo '<div style="width: 30%; margin:10px 10px 10px 0px; padding:10px; border:1px solid #B2B2B2; list-style:none;float:left;">no Stars set for this title</div>';
		}
		echo '</div>';
		$terms2 = wp_get_object_terms($thepostID, 'director', $args);
		$count = count($terms2);
		echo '<div style="width: 30%; margin:10px 10px 10px 0px; padding:10px; border:0px none;float:left;"><strong>Order Directors</strong><br/><span style="font-size:10px;">drag and drop the directors into the correct order.</span>'; 
		if ( $count > 0 ){
			echo '<ul id="spotlight" style="width: 95%; margin:10px 10px 10px 0px; padding:10px; border:1px solid #B2B2B2; list-style:none;float:left;">';
			foreach ( $terms2 as $term ) {
				echo '<li id="s-'.$term->term_taxonomy_id.'" class="lineitem" style="background-color: #F1F1F1;border: 1px solid #B2B2B2;cursor: move;margin: 3px 0;overflow: hidden;padding: 2px 5px;">' . $term->name . '</li>';
			}
			echo "</ul>";
		}else{
			echo '<div style="width: 95%; margin:10px 10px 10px 0px; padding:10px; border:1px solid #B2B2B2; list-style:none;float:left;">no Directors set for this title</div>';
		}
		echo '</div>';
		echo '<div style="clear:both;"></div>';
		echo '<input type="button" id="spotlightButton" Value="Click to Set Changes" onclick="javascript:orderStars();" />&nbsp;&nbsp;<strong id="updateText"></strong>';
		echo '&nbsp;&nbsp;<input type="button" id="spo" Value="Cancel" onclick="javascript:back();" />&nbsp;&nbsp;';
		?>
		<script type="text/javascript">
		// <![CDATA[
			function starorderaddloadevent(){
				jQuery("#feature").sortable({ placeholder: "ui-selected", revert: false,/*connectWith: "#spotlight",*/tolerance: "pointer" });
				jQuery("#spotlight").sortable({ placeholder: "ui-selected", revert: false,/*connectWith: "#feature",*/tolerance: "pointer" });
			};
			addLoadEvent(starorderaddloadevent);
			function orderStars() {
				jQuery("#featureButton").css("display", "none");
				jQuery("#updateText").html("Updating Stars Order...");
				jQuery("#spotlightButton").css("display", "none");
				jQuery("#updateText").html("Updating Director Order...");
				idListS = jQuery("#spotlight").sortable("toArray");
				idListF = jQuery("#feature").sortable("toArray");
				location.href = 'admin.php?page=term-order&mode=OrderTerms&parentID=<?php echo $thepostID ; ?>&idStringF='+idListF+'&idStringS='+idListS;
			}
		// ]]>
		</script>
	<?php
	}elseif( $_REQUEST['parentID'] != '' && $_REQUEST['mode']=='OrderTerms' ){
		//update database Step 3
		$idStringF 	= $_GET['idStringF'];
		$idStringS 	= $_GET['idStringS'];
		$mode 		= $_GET['mode'];
		$parentID 	= $_GET['parentID'];
		$IDsF 		= explode(",", $idStringF);
		$IDsS 		= explode(",", $idStringS);
		
		if($idStringF!=''){
			$resultF = count($IDsF);
			for($i = 0; $i < $resultF; $i++){
				$thei		= $i+1;
				$newID		= str_replace('f-','',$IDsF[$i]);
				if($newID!=''){
					$wpdb->query("UPDATE ".$wpdb->prefix."term_relationships SET term_order = '$thei' WHERE object_id ='$parentID' AND term_taxonomy_id = '$newID'");
				}
			}
		}
		if($idStringS!=''){
			$resultS = count($IDsS);
			for($i = 0; $i < $resultS; $i++){
				$thei		= $i+1;
				$newID		= str_replace('s-','',$IDsS[$i]);
				if($newID!=''){
					$wpdb->query("UPDATE ".$wpdb->prefix."term_relationships SET term_order = '$thei' WHERE object_id ='$parentID' AND term_taxonomy_id = '$newID'");
				}
			}
		}
		echo '<div class="updated fade"><strong>Success!</strong><br/>Stars & Director Order Updated!</div>';
		echo '<div style="clear:both;"></div>';
		echo '<input type="button" id="done" Value="Order More Stars/Directors" onclick="javascript:location.href=\'admin.php?page=term-order\';" />&nbsp;&nbsp;<strong id="updateText"></strong>';
	}
	echo '</div>';
	echo '</div>';	
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
?>