<?php
//VARIABLES
	global $fullname_apipp, $shortname_apipp, $options_apipp, $thedefaultapippstyle;

	$fullname_apipp = "Amazon Product In a Post Plugin";
	$shortname_apipp = "apipp";
	
	$options_apipp= array (
					array(	"name" => "General Settings",
							"type" => "heading"),

					array(	"name" => "Amazon Affiliate ID",
							"desc" => "Your Amazon Affiliate ID<br /><br />",
				    		"id" => $shortname_apipp."_amazon_associateid",
				    		"type" => "text"),

					
					array(	"name" => "Your Amazon Locale/Region",
							"desc" => "The Locale to use for Amazon API Calls (ca,com,co.uk,de,fr,jp) Default is 'com' for US.<br /><br />",
				    		"id" => $shortname_apipp."_amazon_locale",
				    		"type" => "select",
				    		"options" => array(
				    			"0" => array("value" => "com","text" => "US (default)"),
				    			"1" => array("value" => "ca","text" => "Canada"),
				    			"2" => array("value" => "co.uk","text" => "United Kingdom"),
				    			"3" => array("value" => "de","text" => "Germany"),
				    			"4" => array("value" => "fr","text" => "France"),
				    			"5" => array("value" => "jp","text" => "Japan")
				    		 )),
					array(	"name" => "Use My Custom Styles?",
							"desc" => "If you want to use your own styles, check this box and enter them below.<br /><br />",
				    		"id" => $shortname_apipp."_product_styles_mine",
				    		"type" => "checkbox"),
					
					array(	"name" => "Product Styles",
							"desc" => "Your Custom styles can go here.<br /><br />",
				    		"id" => $shortname_apipp."_product_styles",
				    		"type" => "textareabig"),
				    
	);



// Functions

	function apipp_options_add_subpage(){
		global $fullname_apipp, $shortname_apipp, $options_apipp;
		apipp_options_admin_page($fullname_apipp, $shortname_apipp, $options_apipp);
	}
	
	function apipp_options_add_admin_page($themename,$shortname,$options) {
	    if ( basename(__FILE__) == 'amazon-product-in-a-post-options.php' ) {
	    
	        if ( 'save' == $_REQUEST['action'] && $_REQUEST[$shortname.'_option']== $shortname ) {
	                foreach ($options as $value) {
						if($value['type'] != 'multicheck'){
	                    	update_option( $value['id'], $_REQUEST[ $value['id'] ] ); 
						}else{
							foreach($value['options'] as $mc_key => $mc_value){
								$up_opt = $value['id'].'_'.$mc_key;
								update_option($up_opt, $_REQUEST[$up_opt] );
							}
						}
					}
	
	                foreach ($options as $value) {
						if($value['type'] != 'multicheck'){
	                    	if( isset( $_REQUEST[ $value['id'] ] ) ) { update_option( $value['id'], $_REQUEST[ $value['id'] ]  ); } else { delete_option( $value['id'] ); } 
						}else{
							foreach($value['options'] as $mc_key => $mc_value){
								$up_opt = $value['id'].'_'.$mc_key;						
								if( isset( $_REQUEST[ $up_opt ] ) ) { update_option( $up_opt, $_REQUEST[ $up_opt ]  ); } else { delete_option( $up_opt ); } 
							}
						}
					}
	                header("Location: admin.php?page=".$shortname."_plugin_admin&saved=true");
	                die;
	
	        } else if( 'reset' == $_REQUEST['action'] && $_REQUEST[$shortname.'_option']== $shortname ) {
	
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
	            header("Location: admin.php?page=".$shortname."_plugin_admin&reset=true");
	            die;
	
	        }
	    }
	
	
	}
	
	function apipp_options_admin_page($themename, $shortname, $options) {
	    if ( $_REQUEST['saved'] ) echo '<div id="message" class="updated fade"><p><strong>'.$themename.' settings saved.</strong></p></div>';
	    if ( $_REQUEST['reset'] ) echo '<div id="message" class="updated fade"><p><strong>'.$themename.' settings reset.</strong></p></div>';
	?>
	<div class="wrap"><div id="icon-amazon" class="icon32"><br /></div>
	<h2><?php echo $themename; ?> options</h2>
	<form method="post" action="">
	<input type="hidden" name="<?php echo $shortname; ?>_option" id="<?php echo $shortname; ?>_option" value="<?php echo $shortname; ?>" />
	<table class="optiontable">
	<?php foreach ($options as $value) { 
		
		switch ( $value['type'] ) {
			case 'text':
			apipp_option_wrapper_header($value);
			?>
			        <input style="width:240px;" name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>" type="<?php echo $value['type']; ?>" value="<?php if ( get_settings( $value['id'] ) != "") { echo get_settings( $value['id'] ); } else { echo $value['std']; } ?>" />
			<?php
			apipp_option_wrapper_footer($value);
			break;
			
			case 'select':
			apipp_option_wrapper_header($value);
			?>
		            <select style="width:240px;" name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>">
		                <?php foreach ($value['options'] as $option) { ?>
		                <option<?php if ( get_settings( $value['id'] ) == $option["value"]) { echo ' selected="selected"'; } elseif ($option["value"] == $value['std']) { echo ' selected="selected"'; } ?> value="<?php echo $option["value"]; ?>"><?php echo $option["text"]; ?></option>
		                <?php } ?>
		            </select>
			<?php
			apipp_option_wrapper_footer($value);
			break;
			
			//////////////////////////////////
			//This is the category select code
			//	Code courtesy of Nathan Rice
			case 'cat_select':
			apipp_option_wrapper_header($value);
			$categories = get_categories('hide_empty=0');
			?>
		            <select style="width:240px;" name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>">
						<?php foreach ($categories as $cat) {
						if ( get_settings( $value['id'] ) == $cat->cat_ID) { $selected = ' selected="selected"'; } else { $selected = ''; }
						$opt = '<option value="' . $cat->cat_ID . '"' . $selected . '>' . $cat->cat_name . '</option>';
						echo $opt; } ?>
		            </select>
			<?php
			apipp_option_wrapper_footer($value);
			break;
			//end category select code
			//////////////////////////
			
			case 'textarea':
			$ta_options = $value['options'];
			apipp_option_wrapper_header($value);
			?>
					<textarea name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>" style="width:400px;height:100px;"><?php 
					if( get_settings($value['id']) != "") {
							echo stripslashes(get_settings($value['id']));
						}else{
							echo $value['std'];
					}?></textarea>
			<?php
			apipp_option_wrapper_footer($value);
			break;
			
			case 'textareabig':
			$ta_options = $value['options'];
			apipp_option_wrapper_header($value);
			?>
					<textarea name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>" style="font-size:10px;width:650px;height:500px;"><?php 
					if( get_settings($value['id']) != "") {
							echo stripslashes(get_settings($value['id']));
						}else{
							echo $value['std'];
					}?></textarea>
			<?php
			apipp_option_wrapper_footer($value);
			break;
	
			case "radio":
			apipp_option_wrapper_header($value);
			
	 		foreach ($value['options'] as $key=>$option) { 
					$radio_setting = get_settings($value['id']);
					if($radio_setting != ''){
			    		if ($key == get_settings($value['id']) ) {
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
							if(get_settings($value['id'])){
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
					$checkbox_setting = get_settings($pn_key);
					if($checkbox_setting != ''){
			    		if (get_settings($pn_key) ) {
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
			    <td colspan="2" style="text-align: center;"><h3><?php echo $value['name']; ?></h3></td>
			</tr>
			<?php
			break;
			
			default:
	
			break;
		}
	}
	?>
	
	</table>
	
	<p class="submit">
	<input name="save" type="submit" value="Save changes" />    
	<input type="hidden" name="action" value="save" />
	</p>
	</form>
	<form method="post">
	<input type="hidden" name="<?php echo $shortname; ?>_option" id="<?php echo $shortname; ?>_option" value="<?php echo $shortname; ?>" />
	<p class="submit">
	<input name="reset" type="submit" value="Reset" />
	<input type="hidden" name="action" value="reset" />
	</p>
	</form>
	
	<?php
	}
	function apipp_option_wrapper_header($values){
		?>
		<tr valign="top"> 
		    <th scope="row" style="text-align:left;"><?php echo $values['name']; ?>:</th>
		    <td>
		<?php
	}
	
	function apipp_option_wrapper_footer($values){
		?>
		    </td>
		</tr>
		<tr valign="top">
			<td>&nbsp;</td><td><small><?php echo $values['desc']; ?></small></td>
		</tr>
		<?php 
	}
$thedefaultapippstyle='
	table.amazon-product-table {
		border-collapse : collapse;
		border : 0 none !important ;
		width : 100%;
	}
	table.amazon-product-table td {
		border : 0 none !important ;
		padding : 0 !important ;
	}
	div.amazon-image-wrapper {
		text-align : center;
		width : 170px;
		float : left;
		padding : 0 10px 0 10px;
	}
	table.amazon-product-table hr {
		display : block;
	}
	span.amazon-tiny {
		font-size : 10px;
	}
	div.amazon-buying {
		text-align : left;
	}
	h2.amazon-asin-title {
		margin : 0 0 5px 0;
		line-height : 1.25;
		font-size : 10pt;
	}
	span.asin-title {
		text-align : left;
	}
	span.amazon-author {
		color : #666;
		font-size : 12px;
	}
	span.amazon-starring-label {
		color : #999;
		font-size : 10px;
	}
	span.amazon-director-label {
		color : #999;
		font-size : 10px;
	}
	span.amazon-rating-label {
		color : #999;
		font-size : 10px;
	}
	span.amazon-starring {
		color : #666;
		font-size : 12px;
	}
	span.amazon-director {
		color : #666;
		font-size : 12px;
	}
	span.amazon-rating {
		color : #666;
		font-size : 12px;
	}
	table.amazon-product-price {
		border-collapse : collapse;
		border : 0 none;
		width : auto;
		padding : 0 !important ;
	}
	table.amazon-product-price a img.amazon-image {
		background-color : transparent !important ;
		border : 0 none !important ;
	}
	td.amazon-post-text {
		text-align : left;
		padding : 0 !important ;
	}
	td.amazon-list-price-label {
		font-size : 10px;
		color : #666;
		text-align : left;
		width : 125px;
	}
	td.amazon-list-price {
		width : 75%;
		text-decoration : line-through;
		text-align : left;
	}
	td.amazon-price-label {
		font-size : 10px;
		color : #666;
		text-align : left;
		width : 125px;
	}
	td.amazon-price {
		font-size : 14px;
		color : #800000;
		font-weight : bold;
		text-align : left;
	}
	td.amazon-new-label {
		font-size : 10px;
		color : #666;
		text-align : left;
		width : 125px;
	}
	td.amazon-new {
		font-size : 14px;
		color : #800000;
		text-align : left;
		font-weight : bold;
	}
	td.amazon-used-label {
		font-size : 10px;
		color : #666;
		text-align : left;
		width : 125px;
	}
	td.amazon-used {
		color : #666;
		text-align : left;
	}
	div.amazon-dates {
		padding : 0 !important ;
		text-align : left;
	}
	div.amazon-dates span.amazon-preorder {
		font-weight : bold;
		color : #d16601;
		text-align : left;
	}
	div.amazon-dates span.amazon-release-date {
		font-size : 10px;
		color : #666;
		text-align : left;
	}
	';

?>