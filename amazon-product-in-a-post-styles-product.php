<?php
require_once('../../../wp-blog-header.php');
if(!isset($_GET['style'])){
	//use default;
	$defaultapippstyles=get_option("apipp_product_styles_default");
	$amazonStylesToUse = $defaultapippstyles;
	if(get_option("apipp_product_styles")==''){update_option("apipp_product_styles",$amazonStylesToUse);}
}else{
	//there Styles
	$thereapippstyles = get_option("apipp_product_styles"); //the styles in the admin
	$amazonStylesToUse = $thereapippstyles;
}
header("Status: 200");
header("Pragma: public");
header("Content-type: text/css");
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
?>
<?php print($amazonStylesToUse);?>