<?php
	global $appip_text_lgimage;
	global $appip_text_listprice; 
	global $appip_text_newfrom; 
	global $appip_text_usedfrom;
	global $appip_text_instock;
	global $appip_text_outofstock; 
	global $appip_text_author;
	global $appip_text_starring;
	global $appip_text_director;
	global $appip_text_reldate;
	global $appip_text_preorder;
	global $appip_text_releasedon;
	global $appip_text_notavalarea;
	global $buyamzonbutton;
	global $addestrabuybutton;
	global $awspagequery;
	global $apip_language;
	global $aws_partner_locale;

//added in 1.8 for language
	if(get_option('apipp_amazon_language')==''){update_option('apipp_amazon_language','en');}
	$aws_partner_locale	= get_option('apipp_amazon_locale'); //Amazon Locale
	$apip_language = get_option('apipp_amazon_language');
	$buyamzonbutton	= "buyamzon-button-plain.png";
	
	switch($apip_language):
	case "en": //default
		$appip_text_lgimage 	= "See larger image";
		$appip_text_listprice 	= "List Price"; 
		$appip_text_newfrom 	= "New From"; 
		$appip_text_usedfrom 	= "Used from";
		$appip_text_instock	 	= "In Stock";
		$appip_text_outofstock 	= "Out of Stock"; 
		$appip_text_author 		= "By (author)";
		$appip_text_starring 	= "Starring";
		$appip_text_director 	= "Director";
		$appip_text_reldate 	= "Release date";
		$appip_text_preorder 	= "Preorder";
		$appip_text_notavalarea = "This item is may not be available in your area. Please click the image or title of product to check pricing.";
		$appip_text_releasedon 	= "This title will be released on";
		break;
	case "fr": //French
		$appip_text_lgimage 	= "Agrandissez cette image";
		$appip_text_listprice 	= "Prix";
		$appip_text_newfrom 	= "Neuf &agrave; partir de";
		$appip_text_usedfrom 	= "D'occasion &agrave; partir de";
		$appip_text_instock 	= "En stock";
		$appip_text_outofstock 	= "&Eacute;puis&eacute;";
		$appip_text_author 		= "De";
		$appip_text_starring 	= "Avec";
		$appip_text_director 	= "R&eacute;alisateur";
		$appip_text_reldate 	= "Date de sortie";
		$appip_text_preorder 	= "Pr&eacute;-commander";
		$appip_text_notavalarea = "Cet article n'est pas disponible dans votre secteur";
		$appip_text_releasedon = "Cet article sera disponible &agrave; partir du";
		break;
	case "sp": //Spanish
		$appip_text_lgimage 	= "Ver imagen m&aacute;s grande";
		$appip_text_listprice 	= "Lista de precios";
		$appip_text_newfrom 	= "De nuevo";
		$appip_text_usedfrom 	= "De ocasion";
		$appip_text_instock	 	= "En Stock";
		$appip_text_outofstock 	= "Fuera de Stock";
		$appip_text_author 		= "Por";
		$appip_text_starring 	= "Con";
		$appip_text_director 	= "Dirigido por";
		$appip_text_reldate 	= "Fecha de salida";
		$appip_text_preorder 	= "Para pre-pedido";
		$appip_text_notavalarea = "Esta partida se puede no estar disponible en su &aacute;rea. Por favor, haga clic en la imagen o el título del producto, para comprobar la fijaci&oacute;n de precios.";
		$appip_text_releasedon 	= "Este t&iacute;tulo ser&aacute; lanzado el";
		break;
	case "ge": //German
		$appip_text_lgimage = "Gr&ouml;&szlig;eres Bild";
		$appip_text_listprice = "Preis";
		$appip_text_newfrom = "Neu ab";
		$appip_text_usedfrom = "gebraucht ab";
		$appip_text_instock = "Auf Lager";
		$appip_text_outofstock = "Nicht auf Lager";
		$appip_text_author = "Von";
		$appip_text_starring = "Mit";
		$appip_text_director = "Regisseur(e)";
		$appip_text_reldate = "Erscheinungstermin";
		$appip_text_preorder = "Vorbestellbar";
		$appip_text_notavalarea = "Dieser Artikel ist in ihrem Gebiet nicht verf&uuml;gbar";
		$appip_text_releasedon = "Dieser Artikel ist verf&uuml;gbar ab";		break;
	endswitch;
	
	//1.8 for language and buttons
	if($aws_partner_locale=='com'){$buyamzonbutton = "buyamzon-button.png";} //set back to .com for US locale
	if($aws_partner_locale=='fr' || $apip_language =='fr'){$buyamzonbutton = "buyamzon-button-fr.png";} //set back to .fr for French locale
	if($apip_language =='appplugin'){$buyamzonbutton = "buyamzon-button-plain.png";} //set back to .com for Spanish language
	if($aws_partner_locale=='co.uk'){$buyamzonbutton = "buyamzon-button-uk.png";} //set back to .uk for UK locale

?>