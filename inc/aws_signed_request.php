<?php
//define('WP_DEBUG', false);
//hash_hmac code from comment by Ulrich in http://mierendo.com/software/aws_signed_query/
//sha256.inc.php from http://www.nanolink.ca/pub/sha256/ 

if(!function_exists('aws_hash_hmac')){
	function aws_hash_hmac($algo, $data, $key, $raw_output=False){
		// RFC 2104 HMAC implementation for php. Creates a sha256 HMAC.
		// Eliminates the need to install mhash to compute a HMAC. Hacked by Lance Rushing. source: http://www.php.net/manual/en/function.mhash.php. modified by Ulrich Mierendorff to work with sha256 and raw output
		$b = 64; // block size of md5, sha256 and other hash functions
		if (strlen($key) > $b){
			$key = pack("H*",$algo($key));
		}
		$key = str_pad($key, $b, chr(0x00));
		$ipad = str_pad('', $b, chr(0x36));
		$opad = str_pad('', $b, chr(0x5c));
		$k_ipad = $key ^ $ipad ;
		$k_opad = $key ^ $opad;
		$hmac = $algo($k_opad . pack("H*", $algo($k_ipad . $data)));
		if ($raw_output){
			return pack("H*", $hmac);
		}else{
			return $hmac;
		}
	} 
}

if(!function_exists('appip_get_XML_structure')){
	function appip_get_XML_structure ($xmldata,$cached=0){
		if($xmldata==''){return False;}
		ini_set ('track_errors', '1');
		$xmlreaderror = false;
		$charset = get_bloginfo( 'charset' ) =='' ? 'UTF-8' : get_bloginfo( 'charset' );
		$parser = xml_parser_create ($charset);
		xml_parser_set_option ($parser, XML_OPTION_SKIP_WHITE, 1);
		xml_parser_set_option ($parser, XML_OPTION_CASE_FOLDING, 0);
		if (!xml_parse_into_struct ($parser, $xmldata, $vals, $index)) {$xmlreaderror = true;}
		xml_parser_free ($parser);
		if (!$xmlreaderror) {
			$result = array ();
			$i = 0;
			if (isset ($vals [$i]['attributes'])){
				foreach (array_keys ($vals [$i]['attributes']) as $attkey){
					$attributes [$attkey] = $vals [$i]['attributes'][$attkey];
				}
			}
			$result [$vals [$i]['tag']] = array_merge ($attributes, GetChildren ($vals, $i, 'open'));
		}
		ini_set ('track_errors', '0');
		$result['CachedAPPIP'] = $cached;
		return $result;
	}
}

if(!function_exists('GetChildren')){
	function GetChildren ($vals, &$i, $type){
		if ($type == 'complete') {
			if (isset ($vals [$i]['value']))
				return ($vals [$i]['value']);
			else
				return '';
		}
		$children = array (); // Contains node data
		/* Loop through children */
		while ($vals [++$i]['type'] != 'close') {
			$type = $vals [$i]['type'];
			// first check if we already have one and need to create an array
			if (isset ($children [$vals [$i]['tag']])) {
				if (is_array ($children [$vals [$i]['tag']])) {
					$temp = array_keys ($children [$vals [$i]['tag']]);
					// there is one of these things already and it is itself an array
					if (is_string ($temp [0])) {
						$a = $children [$vals [$i]['tag']];
						unset ($children [$vals [$i]['tag']]);
						$children [$vals [$i]['tag']][0] = $a;
					}
				} else {
					$a = $children [$vals [$i]['tag']];
					unset ($children [$vals [$i]['tag']]);
					$children [$vals [$i]['tag']][0] = $a;
				}
				$children [$vals [$i]['tag']][] = GetChildren ($vals, $i, $type);
			} else
				$children [$vals [$i]['tag']] = GetChildren ($vals, $i, $type);
			// I don't think I need attributes but this is how I would do them:
			if (isset ($vals [$i]['attributes'])) {
				$attributes = array ();
				foreach (array_keys ($vals [$i]['attributes']) as $attkey)
				$attributes [$attkey] = $vals [$i]['attributes'][$attkey];
				// now check: do we already have an array or a value?
				if (isset ($children [$vals [$i]['tag']])) {
					// case where there is an attribute but no value, a complete with an attribute in other words
					if ($children [$vals [$i]['tag']] == '') {
						unset ($children [$vals [$i]['tag']]);
						$children [$vals [$i]['tag']] = $attributes;
					}
					// case where there is an array of identical items with attributes
					elseif (is_array ($children [$vals [$i]['tag']])) {
						$index = count ($children [$vals [$i]['tag']]) - 1;
						// probably also have to check here whether the individual item is also an array or not or what... all a bit messy
						if ($children [$vals [$i]['tag']][$index] == '') {
							unset ($children [$vals [$i]['tag']][$index]);
							$children [$vals [$i]['tag']][$index] = $attributes;
						}
						if(!is_array($children [$vals [$i]['tag']][$index])){
							$children [$vals [$i]['tag']][$index] = $attributes;
						}else{
							$children [$vals [$i]['tag']][$index] = array_merge ($children [$vals [$i]['tag']][$index], $attributes);
						}
					} else {
						$value = $children [$vals [$i]['tag']];
						unset ($children [$vals [$i]['tag']]);
						$children [$vals [$i]['tag']]['value'] = $value;
						$children [$vals [$i]['tag']] = array_merge ($children [$vals [$i]['tag']], $attributes);
					}
				} else
					$children [$vals [$i]['tag']] = $attributes;
			}
		}
	
		return $children;
	}
}

if(!function_exists('FormatASINResult')){
	//main function for single product created by Don Fischer http://www.fischercreativemedia.com
	function FormatASINResult($Result){ 
		$Item = isset($Result['ItemLookupResponse']['Items']['Item']) ? $Result['ItemLookupResponse']['Items']['Item'] : false;
		$cache = isset($Result['CachedAPPIP']) ? $Result['CachedAPPIP'] : 0 ;
		
		$errors = '';
		if(isset($Result['ItemLookupErrorResponse']['Error']['Code'])){
			$errors = "<"."!-- HIDDEN APIP ERROR: ".$Result['ItemLookupErrorResponse']['Error']['Code'].": ".$Result['ItemLookupErrorResponse']['Error']['Message']."-->"."\n";
		}elseif(isset($Result['ItemLookupErrorResponse']['Error'][0])){
			foreach($Result['ItemLookupErrorResponse']['Error'] as $temperr){
				$errors .=  "<"."!-- HIDDEN APIP ERROR: ". $temperr['Code'].": ".$temperr['Message']."-->"."\n";
			}
		}elseif(isset($Result['ItemLookupErrorResponse']['Items']['Request']['Errors']['Error'])){
			if(!empty($Result['ItemLookupErrorResponse']['Items']['Request']['Errors'])){
				foreach($Result['ItemLookupErrorResponse']['Items']['Request']['Errors'] as $error){
					$errors .= "<"."!-- HIDDEN APIP ERROR: ". $error['Code'].': '.$error['Message']."-->"."\n";
				}
			}else{
				$errors .= '';
			}
		}

		if(isset($Item[0])){
			$Itema = $Item;
			foreach($Itema as $Item){
				$Item['CachedAPPIP'] = $cache;
				$RetValNew[] = GetAPPIPReturnValArray($Item,$errors);
			}
		}elseif($Item != false ){
			$Item['CachedAPPIP'] = $cache;
			$RetValNew[] = GetAPPIPReturnValArray($Item,$errors);
		}else{
			$RetValNew[] = array('Error'=> "{$errors}",'NoData' => 1);
		}
		return $RetValNew;  
	}
}

function GetAPPIPReturnValArray($Item,$Errors){
 	//processor function for product created by Don Fischer http://www.fischercreativemedia.com
    $ItemAttr 									= isset($Item['ItemAttributes']) ? $Item['ItemAttributes'] : array();
    $ItemOffSum 								= isset($Item['OfferSummary']) ? $Item['OfferSummary'] : array();
    $ItemOffers 								= isset($Item['Offers']) ? $Item['Offers'] : array();
    $ItemAmazOffers								= isset($Item['Offers']) ? $Item['Offers'] : array();
  	$ImageSM									= isset($Item['SmallImage']['URL']) ? $Item['SmallImage']['URL'] : '';
  	$ImageMD 									= isset($Item['MediumImage']['URL']) ? $Item['MediumImage']['URL'] : '';
  	$ImageLG 									= isset($Item['LargeImage']['URL']) ? $Item['LargeImage']['URL'] : '';
  	$ImageSets									= isset($Item['ImageSets']['ImageSet']) ? $Item['ImageSets']['ImageSet'] : '';
	$DetailPageURL 								= isset($Item['DetailPageURL']) ? $Item['DetailPageURL'] : array();
    $ASIN 										= isset($Item['ASIN']) ? $Item['ASIN'] : array();
    $ItemRev									= isset($Item['CustomerReviews']) ? $Item['CustomerReviews'] : array();
	$DescriptionAmz								= isset($Item["EditorialReviews"]["EditorialReview"]) ? $Item["EditorialReviews"]["EditorialReview"] : array();
	$cached										= isset($Item["CachedAPPIP"]) ? $Item["CachedAPPIP"] : 0;

// IMAGES
	if($ImageSM == '' && $ImageSets != ''){
		if(isset($ImageSets[0])){
			$ImageSM = isset($ImageSets[0]['SmallImage']['URL']) ? $ImageSets[0]['SmallImage']['URL'] : '';
		}else{
			$ImageSM = isset($ImageSets['SmallImage']['URL']) ? $ImageSets['SmallImage']['URL'] : '' ;
		}
	}
	if($ImageMD == '' && $ImageSets != ''){
		if(isset($ImageSets[0])){
			$ImageMD = isset($ImageSets[0]['MediumImage']['URL']) ? $ImageSets[0]['MediumImage']['URL'] : '';
		}else{
			$ImageMD = isset($ImageSets['MediumImage']['URL']) ? $ImageSets['MediumImage']['URL'] : '' ;
		}
	}
	if($ImageLG == '' && $ImageSets != ''){
		if(isset($ImageSets[0])){
			$ImageLG = isset($ImageSets[0]['LargeImage']['URL']) ? $ImageSets[0]['LargeImage']['URL'] : '';
		}else{
			$ImageLG = isset($ImageSets['LargeImage']['URL']) ? $ImageSets['LargeImage']['URL'] : '' ;
		}
	}
	
// REVIEWS
	$appHasReviews 								= isset($ItemRev['HasReviews']) ? isset($ItemRev['HasReviews']) : 'false';
	$appCustomerReviews 						= $appHasReviews == 'true' ? $ItemRev['IFrameURL'] : '';

//ITEM ATTRIBS
	$appActor 									= isset($ItemAttr['Actor']) ? ( is_array( $ItemAttr["Actor"] ) ? implode( ", ", $ItemAttr["Actor"] ) : $ItemAttr["Actor"] ) : '';
	$appArtist 									= isset($ItemAttr['Artist']) ? ( is_array( $ItemAttr["Artist"] ) ? implode( ", ", $ItemAttr["Artist"] ) : $ItemAttr["Artist"] ) : '';
	$appAspectRatio 							= isset($ItemAttr['AspectRatio']) ? ( is_array( $ItemAttr["AspectRatio"] ) ? implode( ", ", $ItemAttr["AspectRatio"] ) : $ItemAttr["AspectRatio"] ) : '';
	$appAudienceRating 							= isset($ItemAttr['AudienceRating']) ? ( is_array( $ItemAttr["AudienceRating"] ) ? implode( ", ", $ItemAttr["AudienceRating"] ) : $ItemAttr["AudienceRating"] ) : '';
	$appAudioFormat 							= isset($ItemAttr['AudioFormat']) ? ( is_array( $ItemAttr["AudioFormat"] ) ? implode( ", ", $ItemAttr["AudioFormat"] ) : $ItemAttr["AudioFormat"] ) : '';
	$appAuthor 									= isset($ItemAttr['Author']) ? ( is_array( $ItemAttr["Author"] ) ? implode( ", ", $ItemAttr["Author"] ) : $ItemAttr["Author"] ) : '';
	$appBinding 								= isset($ItemAttr['Binding']) ? ( is_array( $ItemAttr["Binding"] ) ? implode( ", ", $ItemAttr["Binding"] ) : $ItemAttr["Binding"] ) : '';
	$appBrand 									= isset($ItemAttr['Brand']) ? ( is_array( $ItemAttr["Brand"] ) ? implode( ", ", $ItemAttr["Brand"] ) : $ItemAttr["Brand"] ) : '';
	$appCatalogNumberList 						= isset($ItemAttr['CatalogNumberList']) ? ( is_array( $ItemAttr["CatalogNumberList"] ) ? implode( ", ", $ItemAttr["CatalogNumberList"] ) : $ItemAttr["CatalogNumberList"] ) : '';
	$appCategory 								= isset($ItemAttr['Category']) ? ( is_array( $ItemAttr["Category"] ) ? implode( ", ", $ItemAttr["Category"] ) : $ItemAttr["Category"] ) : '';
	$appCEROAgeRating 							= isset($ItemAttr['CEROAgeRating']) ? ( is_array( $ItemAttr["CEROAgeRating"] ) ? implode( ", ", $ItemAttr["CEROAgeRating"] ) : $ItemAttr["CEROAgeRating"] ) : '';
	$appClothingSize 							= isset($ItemAttr['ClothingSize']) ? ( is_array( $ItemAttr["ClothingSize"] ) ? implode( ", ", $ItemAttr["ClothingSize"] ) : $ItemAttr["ClothingSize"] ) : '';
	$appColor 									= isset($ItemAttr['Color']) ? ( is_array( $ItemAttr["Color"] ) ? implode( ", ", $ItemAttr["Color"] ) : $ItemAttr["Color"] ) : '';
	$appCreator 								= isset($ItemAttr['Creator']) ? ( is_array( $ItemAttr["Creator"] ) ? implode( ", ", $ItemAttr["Creator"] ) : $ItemAttr["Creator"] ) : '';
	$appDepartment 								= isset($ItemAttr['Department']) ? ( is_array( $ItemAttr["Department"] ) ? implode( ", ", $ItemAttr["Department"] ) : $ItemAttr["Department"] ) : '';
	$appDirector 								= isset($ItemAttr['Director']) ? ( is_array( $ItemAttr["Director"] ) ? implode( ", ", $ItemAttr["Director"] ) : $ItemAttr["Director"] ) : '';
	$appEAN 									= isset($ItemAttr['EAN']) ? ( is_array( $ItemAttr["EAN"] ) ? implode( ", ", $ItemAttr["EAN"] ) : $ItemAttr["EAN"] ) : '';
	$appEANList									= isset($ItemAttr['EANList']) ? ( is_array( $ItemAttr["EANList"] ) ? implode( ", ", $ItemAttr["EANList"] ) : $ItemAttr["EANList"] ) : '';
	$appEdition 								= isset($ItemAttr['Edition']) ? ( is_array( $ItemAttr["Edition"] ) ? implode( ", ", $ItemAttr["Edition"] ) : $ItemAttr["Edition"] ) : '';
	$appEISBN 									= isset($ItemAttr['EISBN']) ? ( is_array( $ItemAttr["EISBN"] ) ? implode( ", ", $ItemAttr["EISBN"] ) : $ItemAttr["EISBN"] ) : '';
	$appEpisodeSequence 						= isset($ItemAttr['EpisodeSequence']) ? ( is_array( $ItemAttr["EpisodeSequence"] ) ? implode( ", ", $ItemAttr["EpisodeSequence"] ) : $ItemAttr["EpisodeSequence"] ) : '';
	$appESRBAgeRating 							= isset($ItemAttr['ESRBAgeRating']) ? ( is_array( $ItemAttr["ESRBAgeRating"] ) ? implode( ", ", $ItemAttr["ESRBAgeRating"] ) : $ItemAttr["ESRBAgeRating"] ) : '';
	$appFeature 								= isset($ItemAttr['Feature']) ? ( is_array( $ItemAttr["Feature"] ) ? implode( ", ", $ItemAttr["Feature"] ) : $ItemAttr["Feature"] ) : '';
	$appFormat 									= isset($ItemAttr['Format']) ? ( is_array( $ItemAttr["Format"] ) ? implode( ", ", $ItemAttr["Format"] ) : $ItemAttr["Format"] ) : '';
	$appGenre 									= isset($ItemAttr['Genre']) ? ( is_array( $ItemAttr["Genre"] ) ? implode( ", ", $ItemAttr["Genre"] ) : $ItemAttr["Genre"] ) : '';
	$appHardwarePlatform 						= isset($ItemAttr['HardwarePlatform']) ? ( is_array( $ItemAttr["HardwarePlatform"] ) ? implode( ", ", $ItemAttr["HardwarePlatform"] ) : $ItemAttr["HardwarePlatform"] ) : '';
	$appHazardousMaterialType 					= isset($ItemAttr['HazardousMaterialType']) ? ( is_array( $ItemAttr["HazardousMaterialType"] ) ? implode( ", ", $ItemAttr["HazardousMaterialType"] ) : $ItemAttr["HazardousMaterialType"] ) : '';
	$appIsAdultProduct 							= isset($ItemAttr['IsAdultProduct']) ? ( is_array( $ItemAttr["IsAdultProduct"] ) ? implode( ", ", $ItemAttr["IsAdultProduct"] ) : $ItemAttr["IsAdultProduct"] ) : '';
	$appIsAutographed 							= isset($ItemAttr['IsAutographed']) ? ( is_array( $ItemAttr["IsAutographed"] ) ? implode( ", ", $ItemAttr["IsAutographed"] ) : $ItemAttr["IsAutographed"] ) : '';
	$appISBN 									= isset($ItemAttr['ISBN']) ? ( is_array( $ItemAttr["ISBN"] ) ? implode( ", ", $ItemAttr["ISBN"] ) : $ItemAttr["ISBN"] ) : '';
	$appIsEligibleForTradeIn 					= isset($ItemAttr['IsEligibleForTradeIn']) ? ( is_array( $ItemAttr["IsEligibleForTradeIn"] ) ? implode( ", ", $ItemAttr["IsEligibleForTradeIn"] ) : $ItemAttr["IsEligibleForTradeIn"] ) : '';
	$appIsMemorabilia 							= isset($ItemAttr['IsMemorabilia']) ? ( is_array( $ItemAttr["IsMemorabilia"] ) ? implode( ", ", $ItemAttr["IsMemorabilia"] ) : $ItemAttr["IsMemorabilia"] ) : '';
	$appIssuesPerYear 							= isset($ItemAttr['IssuesPerYear']) ? ( is_array( $ItemAttr["IssuesPerYear"] ) ? implode( ", ", $ItemAttr["IssuesPerYear"] ) : $ItemAttr["IssuesPerYear"] ) : '';
	$appItemDimensions 							= isset($ItemAttr['ItemDimensions']) ? ( is_array( $ItemAttr["ItemDimensions"] ) ? $ItemAttr["ItemDimensions"] : $ItemAttr["ItemDimensions"] ) : '';
	$appItemPartNumber 							= isset($ItemAttr['ItemPartNumber']) ? ( is_array( $ItemAttr["ItemPartNumber"] ) ? implode( ", ", $ItemAttr["ItemPartNumber"] ) : $ItemAttr["ItemPartNumber"] ) : '';
	$appLabel 									= isset($ItemAttr['Label']) ? ( is_array( $ItemAttr["Label"] ) ? implode( ", ", $ItemAttr["Label"] ) : $ItemAttr["Label"] ) : '';
	$appLanguages 								= isset($ItemAttr['Languages']["Language"]) ? $ItemAttr["Languages"]["Language"] : '';
	$appLegalDisclaimer 						= isset($ItemAttr['LegalDisclaimer']) ? ( is_array( $ItemAttr["LegalDisclaimer"] ) ? implode( ", ", $ItemAttr["LegalDisclaimer"] ) : $ItemAttr["LegalDisclaimer"] ) : '';
	$appListPrice 								= isset($ItemAttr['ListPrice']) ? ( $ItemAttr["ListPrice"]["FormattedPrice"] . ' ' . $ItemAttr["ListPrice"]["CurrencyCode"] ) : 0;
	$appMagazineType 							= isset($ItemAttr['MagazineType']) ? ( is_array( $ItemAttr["MagazineType"] ) ? implode( ", ", $ItemAttr["MagazineType"] ) : $ItemAttr["MagazineType"] ) : '';
	$appManufacturer 							= isset($ItemAttr['Manufacturer']) ? ( is_array( $ItemAttr["Manufacturer"] ) ? implode( ", ", $ItemAttr["Manufacturer"] ) : $ItemAttr["Manufacturer"] ) : '';
	$appManufacturerMaximumAge 					= isset($ItemAttr['ManufacturerMaximumAge']) ? ( is_array( $ItemAttr["ManufacturerMaximumAge"] ) ? implode( ", ", $ItemAttr["ManufacturerMaximumAge"] ) : $ItemAttr["ManufacturerMaximumAge"] ) : '';
	$appManufacturerMinimumAge 					= isset($ItemAttr['ManufacturerMinimumAge']) ? ( is_array( $ItemAttr["ManufacturerMinimumAge"] ) ? implode( ", ", $ItemAttr["ManufacturerMinimumAge"] ) : $ItemAttr["ManufacturerMinimumAge"] ) : '';
	$appManufacturerPartsWarrantyDescription 	= isset($ItemAttr['ManufacturerPartsWarrantyDescription']) ? ( is_array( $ItemAttr["ManufacturerPartsWarrantyDescription"] ) ? implode( ", ", $ItemAttr["ManufacturerPartsWarrantyDescription"] ) : $ItemAttr["ManufacturerPartsWarrantyDescription"] ) : '';
	$appMediaType 								= isset($ItemAttr['MediaType']) ? ( is_array( $ItemAttr["MediaType"] ) ? implode( ", ", $ItemAttr["MediaType"] ) : $ItemAttr["MediaType"] ) : '';
	$appModel 									= isset($ItemAttr['Model']) ? ( is_array( $ItemAttr["Model"] ) ? implode( ", ", $ItemAttr["Model"] ) : $ItemAttr["Model"] ) : '';
	$appModelYear 								= isset($ItemAttr['ModelYear']) ? ( is_array( $ItemAttr["ModelYear"] ) ? implode( ", ", $ItemAttr["ModelYear"] ) : $ItemAttr["ModelYear"] ) : '';
	$appMPN										= isset($ItemAttr['MPN']) ? ( is_array( $ItemAttr["MPN"] ) ? implode( ", ", $ItemAttr["MPN"] ) : $ItemAttr["MPN"] ) : '';
	$appNumberOfDiscs 							= isset($ItemAttr['NumberOfDiscs']) ? ( is_array( $ItemAttr["NumberOfDiscs"] ) ? implode( ", ", $ItemAttr["NumberOfDiscs"] ) : $ItemAttr["NumberOfDiscs"] ) : '';
	$appNumberOfIssues 							= isset($ItemAttr['NumberOfIssues']) ? ( is_array( $ItemAttr["NumberOfIssues"] ) ? implode( ", ", $ItemAttr["NumberOfIssues"] ) : $ItemAttr["NumberOfIssues"] ) : '';
	$appNumberOfItems 							= isset($ItemAttr['NumberOfItems']) ? ( is_array( $ItemAttr["NumberOfItems"] ) ? implode( ", ", $ItemAttr["NumberOfItems"] ) : $ItemAttr["NumberOfItems"] ) : '';
	$appNumberOfPages 							= isset($ItemAttr['NumberOfPages']) ? ( is_array( $ItemAttr["NumberOfPages"] ) ? implode( ", ", $ItemAttr["NumberOfPages"] ) : $ItemAttr["NumberOfPages"] ) : '';
	$appNumberOfTracks 							= isset($ItemAttr['NumberOfTracks']) ? ( is_array( $ItemAttr["NumberOfTracks"] ) ? implode( ", ", $ItemAttr["NumberOfTracks"] ) : $ItemAttr["NumberOfTracks"] ) : '';
	$appOperatingSystem 						= isset($ItemAttr['OperatingSystem']) ? ( is_array( $ItemAttr["OperatingSystem"] ) ? implode( ", ", $ItemAttr["OperatingSystem"] ) : $ItemAttr["OperatingSystem"] ) : '';
	$appPackageDimensions 						= isset($ItemAttr['PackageDimensions']) ? ( is_array( $ItemAttr["PackageDimensions"] ) ? $ItemAttr["PackageDimensions"] : $ItemAttr["PackageDimensions"] ) : '';
	$appPackageDimensionsWidth 					= isset($ItemAttr['PackageDimensions']['Width']) ?  is_array( $ItemAttr["PackageDimensions"]['Width']) ? strpos($ItemAttr["PackageDimensions"]['Width']['Units'],'hundredths-') !== false ? ( $ItemAttr["PackageDimensions"]['Width']['value'] / 100 ) .' '. str_replace( 'hundredths-','', $ItemAttr["PackageDimensions"]['Width']['Units'] ) : $ItemAttr["PackageDimensions"]['Width']['value'] .' '. $ItemAttr["PackageDimensions"]['Width']['Units'] : '' : '';
	$appPackageDimensionsHeight 				= isset($ItemAttr['PackageDimensions']['Height']) ?  is_array( $ItemAttr["PackageDimensions"]['Height']) ? strpos($ItemAttr["PackageDimensions"]['Height']['Units'],'hundredths-') !== false ? ( $ItemAttr["PackageDimensions"]['Height']['value'] / 100 ) .' '. str_replace( 'hundredths-','', $ItemAttr["PackageDimensions"]['Height']['Units'] ) : $ItemAttr["PackageDimensions"]['Height']['value'] .' '. $ItemAttr["PackageDimensions"]['Height']['Units'] : '' : '';
	$appPackageDimensionsLength 				= isset($ItemAttr['PackageDimensions']['Length']) ?  is_array( $ItemAttr["PackageDimensions"]['Length']) ? strpos($ItemAttr["PackageDimensions"]['Length']['Units'],'hundredths-') !== false ? ( $ItemAttr["PackageDimensions"]['Length']['value'] / 100 ) .' '. str_replace( 'hundredths-','', $ItemAttr["PackageDimensions"]['Length']['Units'] ) : $ItemAttr["PackageDimensions"]['Length']['value'] .' '. $ItemAttr["PackageDimensions"]['Length']['Units'] : '' : '';
	$appPackageDimensionsWeight					= isset($ItemAttr['PackageDimensions']['Weight']) ?  is_array( $ItemAttr["PackageDimensions"]['Weight']) ? strpos($ItemAttr["PackageDimensions"]['Weight']['Units'],'hundredths-') !== false ? ( $ItemAttr["PackageDimensions"]['Weight']['value'] / 100 ) .' '. str_replace( 'hundredths-','', $ItemAttr["PackageDimensions"]['Weight']['Units'] ) : $ItemAttr["PackageDimensions"]['Weight']['value'] .' '. $ItemAttr["PackageDimensions"]['Weight']['Units'] : '' : '';
	$appPackageQuantity 						= isset($ItemAttr['PackageQuantity']) ? ( is_array( $ItemAttr["PackageQuantity"] ) ? implode( ", ", $ItemAttr["PackageQuantity"] ) : $ItemAttr["PackageQuantity"] ) : '';
	$appPartNumber 								= isset($ItemAttr['PartNumber']) ? ( is_array( $ItemAttr["PartNumber"] ) ? implode( ", ", $ItemAttr["PartNumber"] ) : $ItemAttr["PartNumber"] ) : '';
	$appPictureFormat 							= isset($ItemAttr['PictureFormat']) ? ( is_array( $ItemAttr["PictureFormat"] ) ? implode( ", ", $ItemAttr["PictureFormat"] ) : $ItemAttr["PictureFormat"] ) : '';
	$appPlatform 								= isset($ItemAttr['Platform']) ? ( is_array( $ItemAttr["Platform"] ) ? implode( ", ", $ItemAttr["Platform"] ) : $ItemAttr["Platform"] ) : '';
	$appProductGroup 							= isset($ItemAttr['ProductGroup']) ? ( is_array( $ItemAttr["ProductGroup"] ) ? implode( ", ", $ItemAttr["ProductGroup"] ) : $ItemAttr["ProductGroup"] ) : '';
	$appProductTypeName 						= isset($ItemAttr['ProductTypeName']) ? ( is_array( $ItemAttr["ProductTypeName"] ) ? implode( ", ", $ItemAttr["ProductTypeName"] ) : $ItemAttr["ProductTypeName"] ) : '';
	$appProductTypeSubcategory 					= isset($ItemAttr['ProductTypeSubcategory']) ? ( is_array( $ItemAttr["ProductTypeSubcategory"] ) ? implode( ", ", $ItemAttr["ProductTypeSubcategory"] ) : $ItemAttr["ProductTypeSubcategory"] ) : '';
	$appPublicationDate 						= isset($ItemAttr['PublicationDate']) ? ( is_array( $ItemAttr["PublicationDate"] ) ? implode( ", ", $ItemAttr["PublicationDate"] ) : $ItemAttr["PublicationDate"] ) : '';
	$appPublisher 								= isset($ItemAttr['Publisher']) ? ( is_array( $ItemAttr["Publisher"] ) ? implode( ", ", $ItemAttr["Publisher"] ) : $ItemAttr["Publisher"] ) : '';
	$appRegionCode 								= isset($ItemAttr['RegionCode']) ? ( is_array( $ItemAttr["RegionCode"] ) ? implode( ", ", $ItemAttr["RegionCode"] ) : $ItemAttr["RegionCode"] ) : '';
	$appReleaseDate 							= isset($ItemAttr['ReleaseDate']) ? ( is_array( $ItemAttr["ReleaseDate"] ) ? implode( ", ", $ItemAttr["ReleaseDate"] ) : $ItemAttr["ReleaseDate"] ) : '';
	$appRunningTime 							= isset($ItemAttr['RunningTime']) ? ( is_array( $ItemAttr["RunningTime"] ) ? implode( " ", $ItemAttr["RunningTime"] ) : $ItemAttr["RunningTime"] ) : '';
	$appSeikodoProductCode 						= isset($ItemAttr['SeikodoProductCode']) ? ( is_array( $ItemAttr["SeikodoProductCode"] ) ? implode( ", ", $ItemAttr["SeikodoProductCode"] ) : $ItemAttr["SeikodoProductCode"] ) : '';
	$appShoeSize 								= isset($ItemAttr['ShoeSize']) ? ( is_array( $ItemAttr["ShoeSize"] ) ? implode( ", ", $ItemAttr["ShoeSize"] ) : $ItemAttr["ShoeSize"] ) : '';
	$appSize 									= isset($ItemAttr['Size']) ? ( is_array( $ItemAttr["Size"] ) ? implode( ", ", $ItemAttr["Size"] ) : $ItemAttr["Size"] ) : '';
	$appSKU 									= isset($ItemAttr['SKU']) ? ( is_array( $ItemAttr["SKU"] ) ? implode( ", ", $ItemAttr["SKU"] ) : $ItemAttr["SKU"] ) : '';
	$appStudio 									= isset($ItemAttr['Studio']) ? ( is_array( $ItemAttr["Studio"] ) ? implode( ", ", $ItemAttr["Studio"] ) : $ItemAttr["Studio"] ) : '';
	$appSubscriptionLength 						= isset($ItemAttr['SubscriptionLength']) ? ( is_array( $ItemAttr["SubscriptionLength"] ) ? implode( ", ", $ItemAttr["SubscriptionLength"] ) : $ItemAttr["SubscriptionLength"] ) : '';
	$appTitle 									= isset($ItemAttr['Title']) ? ( is_array( $ItemAttr["Title"] ) ? implode( ", ", $ItemAttr["Title"] ) : $ItemAttr["Title"] ) : '';
	$appTrackSequence 							= isset($ItemAttr['TrackSequence']) ? ( is_array( $ItemAttr["TrackSequence"] ) ? implode( ", ", $ItemAttr["TrackSequence"] ) : $ItemAttr["TrackSequence"] ) : '';
	$appTradeInValue 							= isset($ItemAttr['TradeInValue']) ? ( is_array( $ItemAttr["TradeInValue"] ) ? implode( ", ", $ItemAttr["TradeInValue"] ) : $ItemAttr["TradeInValue"] ) : '';
	$appUPC 									= isset($ItemAttr['UPC']) ? ( is_array( $ItemAttr["UPC"] ) ? implode( ", ", $ItemAttr["UPC"] ) : $ItemAttr["UPC"] ) : '';
	$appUPCList 								= isset($ItemAttr['UPCList']) ? ( is_array( $ItemAttr["UPCList"] ) ? implode( ", ", $ItemAttr["UPCList"] ) : $ItemAttr["UPCList"] ) : '';
	$appWarranty 								= isset($ItemAttr['Warranty']) ? ( is_array( $ItemAttr["Warranty"] ) ? implode( ", ", $ItemAttr["Warranty"] ) : $ItemAttr["Warranty"] ) : '';
	$appWEEETaxValue  							= isset($ItemAttr['WEEETaxValue ']) ? ( is_array( $ItemAttr["WEEETaxValue "] ) ? implode( ", ", $ItemAttr["WEEETaxValue "] ) : $ItemAttr["WEEETaxValue "] ) : '';

 //OFFER SUMMARY
	$appTotalNew 								= isset($ItemOffSum['TotalNew']) ? ( is_array( $ItemOffSum["TotalNew"] ) ? implode( ", ", $ItemOffSum["TotalNew"] ) : $ItemOffSum["TotalNew"] ) : '';
	$appTotalUsed 								= isset($ItemOffSum['TotalUsed']) ? ( is_array( $ItemOffSum["TotalUsed"] ) ? implode( ", ", $ItemOffSum["TotalUsed"] ) : $ItemOffSum["TotalUsed"] ) : '';
	$appTotalRefurbished 						= isset($ItemOffSum['TotalRefurbished']) ? ( is_array( $ItemOffSum["TotalRefurbished"] ) ? implode( ", ", $ItemOffSum["TotalRefurbished"] ) : $ItemOffSum["TotalRefurbished"] ) : '';
	$appTotalCollectible 						= isset($ItemOffSum['TotalCollectible']) ? ( is_array( $ItemOffSum["TotalCollectible"] ) ? implode( ", ", $ItemOffSum["TotalCollectible"] ) : $ItemOffSum["TotalCollectible"] ) : '';
	$appLowestNewPrice 							= isset($ItemOffSum['LowestNewPrice']['FormattedPrice']) ?  $ItemOffSum["LowestNewPrice"]['FormattedPrice'] .' '.$ItemOffSum["LowestNewPrice"]["CurrencyCode"] : 0;
	$appLowestUsedPrice							= isset($ItemOffSum['LowestUsedPrice']['FormattedPrice']) ?  $ItemOffSum["LowestUsedPrice"]['FormattedPrice'] .' '.$ItemOffSum["LowestUsedPrice"]["CurrencyCode"] : 0;
	$appLowestRefurbishedPrice 					= isset($ItemOffSum['LowestRefurbishedPrice']['FormattedPrice']) ?  $ItemOffSum["LowestRefurbishedPrice"]['FormattedPrice'] .' '.$ItemOffSum["LowestRefurbishedPrice"]["CurrencyCode"] : 0;
	$appLowestCollectiblePrice 					= isset($ItemOffSum['LowestCollectiblePrice']['FormattedPrice']) ?  $ItemOffSum["LowestCollectiblePrice"]['FormattedPrice'] .' '.$ItemOffSum["LowestCollectiblePrice"]["CurrencyCode"] : 0;

 //OFFERS
	$appTotalOffers 							= isset($ItemOffers['TotalOffers']) ? ( is_array( $ItemOffers["TotalOffers"] ) ? implode( ", ", $ItemOffers["TotalOffers"] ) : $ItemOffers["TotalOffers"] ) : '';
	$appMoreOffersUrl 							= isset($ItemOffers['MoreOffersUrl']) ? $ItemOffers["MoreOffersUrl"]  : '';		
	$appTotalOfferPages							= isset($ItemOffers['TotalOfferPages']) ? ( is_array( $ItemOffers["TotalOfferPages"] ) ? implode( ", ", $ItemOffers["TotalOfferPages"] ) : $ItemOffers["TotalOfferPages"] ) : '';
	$isPriceHidden 								= $lowestNewPrice=='Too low to display' ? 1 : 0;
	if(!isset($ItemAmazOffers['Offers'][0])){
		$ItemAmazOfftemp = $ItemAmazOffers['Offer'];
		unset($ItemAmazOffers['Offer']);
		$ItemAmazOffers['Offer'][0] = $ItemAmazOfftemp; 
	}
	foreach($ItemAmazOffers['Offer'] as $amzOffers){
		if(isset($amzOffers['OfferAttributes'])){
			if($amzOffers['OfferListing']['Price']['FormattedPrice'] == '0 Out of Stock'){$amzOffers['OfferListing']['Price']['FormattedPrice'] = 'Out of Stock';}
			if($amzOffers['OfferListing']['AmountSaved']['FormattedPrice'] =='0'){$amzOffers['OfferListing']['AmountSaved']['FormattedPrice'] = '';}
			$atype = $amzOffers['OfferAttributes']['Condition'];
			$newAmzPricing[$atype]['List'] = $appListPrice;
			$newAmzPricing[$atype]['Price'] =  $amzOffers['OfferListing']['Price']['FormattedPrice'] . ' ' . $amzOffers['OfferListing']['Price']['CurrencyCode'];
			$newAmzPricing[$atype]['Saved'] =  $amzOffers['OfferListing']['AmountSaved']['FormattedPrice'] . ' ' . $amzOffers['OfferListing']['AmountSaved']['CurrencyCode'];
			$newAmzPricing[$atype]['SavedPercent'] =  $amzOffers['OfferListing']['PercentageSaved'];
			$newAmzPricing[$atype]['IsEligibleForSuperSaverShipping'] = $amzOffers['OfferListing']['IsEligibleForSuperSaverShipping'];
		}
	}
	
	if($appTotalNew > 0){ 
		$newAmzPricing['NewFrom']['List'] = $appListPrice;
		$newAmzPricing['NewFrom']['Price'] = 'New from '.$appLowestNewPrice;
	}
	if($appTotalUsed > 0){ 
		$newAmzPricing['UsedFrom']['List'] = $appListPrice;
		$newAmzPricing['UsedFrom']['Price'] = 'Used from '.$appLowestUsedPrice;
	}
	if($appTotalRefurbished > 0){ 
		$newAmzPricing['RefurbishedFrom']['List'] = $appListPrice;
		$newAmzPricing['RefurbishedFrom']['Price'] = 'Refurbished from '.$appLowestRefurbishedPrice;
	}
	if($appTotalCollectible > 0){ 
		$newAmzPricing['CollectibleFrom']['List'] = $appListPrice;
		$newAmzPricing['CollectibleFrom']['Price'] = 'Collectible from '. $appLowestCollectiblePrice;
	}
	
	if(!isset($ItemOffers['OfferListing']['Price'])){
		$SalePrice = $ItemOffers['OfferListing']['Price'];
	}else{
		$SalePrice = $ItemOffSum['LowestNewPrice']['Amount'];
	}

	$OfferListingId = $ItemOffers['OfferListing']['OfferListingId'];
	if(is_array($appLanguages)){
		$appipLantemp2 = array();
		foreach($appLanguages as $appipLantemp){
			if(isset($appipLantemp["Name"]) && isset($appipLantemp["Type"])){
				$appipLantemp2[] =  $appipLantemp["Name"] .' ('. $appipLantemp["Type"] .')';
			}
		}
		$appLanguages = implode( ", ", $appipLantemp2 );
	} 
	
	if(isset($ItemAttr["ListPrice"]["Amount"]) && isset($SalePrice['Amount']) ){
		$SavingsPrice =  number_format(($ItemAttr["ListPrice"]["Amount"]/ 100.0),2) - number_format(($SalePrice['Amount'] / 100.0), 2);
		if($ItemAttr["ListPrice"]["Amount"]!=0){
			$SavingsPercent = ($SavingsPrice / number_format($ItemAttr["ListPrice"]["Amount"]/100,2))*100;
		}else{
			$SavingsPercent = 0;
		}
	}else{
		$SavingsPrice =  0;
		$SavingsPercent = 0;
	}
	global $show_format;
	if($show_format==1){
		$appTitle 				= ($appBinding != '' ) ? $appTitle .' ('.$appBinding.')' : $appTitle;
	}
	if(isset($DescriptionAmz[0])){
		foreach($DescriptionAmz as $descarr){
			if(isset($descarr['Source'])){$tmpsrc = $descarr['Source'];}
			if(isset($descarr['Content'])){$tmpcon = $descarr['Content'];}
			$EDescprition[]		= array('Source'=>$tmpsrc,'Content'=>$tmpcon);
		}
	}else{
		if(isset($DescriptionAmz['Source'])){$tmpsrc = $DescriptionAmz['Source'];}
		if(isset($DescriptionAmz['Content'])){$tmpcon = $DescriptionAmz['Content'];}
		$EDescprition[]			= array('Source'=>$tmpsrc,'Content'=>$tmpcon);
	}
	$ImageSetsArray 		= array();
	if(isset($ImageSets[0])){
		foreach($ImageSets as $imgset){
			if($imgset['LargeImage']['URL'] != $ImageLG){
				$ImageSetsArray[] = '<a rel="appiplightbox-'.$ASIN.'" href="'.$imgset['LargeImage']['URL'] .'"><img src="'.$imgset['SwatchImage']['URL'].'" class="apipp-additional-image"/></a>'."\n";
			}
		}
	}elseif(isset($ImageSets['SwatchImage'])){
		if($ImageSets['LargeImage']['URL'] != $ImageLG){
			$ImageSetsArray[] = '<a rel="appiplightbox-'.$ASIN.'" href="'.$ImageSets['LargeImage']['URL'] .'"><img src="'.$ImageSets['SwatchImage']['URL'].'" class="apipp-additional-image"/></a>'."\n";
		}
	}

    $RetVal = array(
//default items
	'ASIN' 									=> "{$ASIN}",
	'Errors' 								=> "{$Errors}",
    'URL' 									=> "{$DetailPageURL}",
	'Title' 								=> "{$appTitle}",
	'SmallImage' 							=> "{$ImageSM}",
	'MediumImage' 							=> "{$ImageMD}",
	'LargeImage' 							=> "{$ImageLG}",
	'AddlImages'							=> implode('',$ImageSetsArray),
	'PriceHidden' 							=> "{$isPriceHidden}",
	'CustomerReviews' 						=> "{$appCustomerReviews}",
	
//item attribs
	"Actor" 								=> "{$appActor}",
	"Artist" 								=> "{$appArtist}",
	"AspectRatio" 							=> "{$appAspectRatio}",
	"AudienceRating" 						=> "{$appAudienceRating}",
	"AudioFormat" 							=> "{$appAudioFormat}",
	"Author" 								=> "{$appAuthor}",
	"Binding" 								=> "{$appBinding}",
	"Brand" 								=> "{$appBrand}",
	"CatalogNumberList" 					=> "{$appCatalogNumberList}",
	"Category" 								=> "{$appCategory}",
	"CEROAgeRating" 						=> "{$appCEROAgeRating}",
	"ClothingSize" 							=> "{$appClothingSize}",
	"Color" 								=> "{$appColor}",
	"Creator" 								=> "{$appCreator}",
	"Department" 							=> "{$appDepartment}",
	"Director" 								=> "{$appDirector}",
	"EAN" 									=> "{$appEAN}",
	"EANList" 								=> "{$appEANList}",
	"Edition" 								=> "{$appEdition}",
	"EISBN" 								=> "{$appEISBN}",
	"EpisodeSequence" 						=> "{$appEpisodeSequence}",
	"ESRBAgeRating" 						=> "{$appESRBAgeRating}",
	"Feature" 								=> "{$appFeature}",
	"Format" 								=> "{$appFormat}",
	"Genre" 								=> "{$appGenre}",
	"HardwarePlatform" 						=> "{$appHardwarePlatform}",
	"HazardousMaterialType" 				=> "{$appHazardousMaterialType}",
	"IsAdultProduct" 						=> "{$appIsAdultProduct}",
	"IsAutographed" 						=> "{$appIsAutographed}",
	"ISBN" 									=> "{$appISBN}",
	"IsEligibleForTradeIn" 					=> "{$appIsEligibleForTradeIn}",
	"IsMemorabilia" 						=> "{$appIsMemorabilia}",
	"IssuesPerYear" 						=> "{$appIssuesPerYear}",
	'ItemDesc' 								=>  $EDescprition,
	"ItemDimensions" 						=> "{$appItemDimensions}",
	"ItemPartNumber" 						=> "{$appItemPartNumber}",
	"Label" 								=> "{$appLabel}",
	"Languages" 							=> "{$appLanguages}",
	"LegalDisclaimer" 						=> "{$appLegalDisclaimer}",
	"ListPrice" 							=> "{$appListPrice}",
	"MagazineType" 							=> "{$appMagazineType}",
	"Manufacturer" 							=> "{$appManufacturer}",
	"ManufacturerMaximumAge"				=> "{$appManufacturerMaximumAge}",
	"ManufacturerMinimumAge" 				=> "{$appManufacturerMinimumAge}",
	"ManufacturerPartsWarrantyDescription" 	=> "{$appManufacturerPartsWarrantyDescription}",
	"MediaType" 							=> "{$appMediaType}",
	"Model" 								=> "{$appModel}",
	"ModelYear" 							=> "{$appModelYear}",
	"MPN" 									=> "{$appMPN}",
	"NumberOfDiscs" 						=> "{$appNumberOfDiscs}",
	"NumberOfIssues" 						=> "{$appNumberOfIssues}",
	"NumberOfItems" 						=> "{$appNumberOfItems}",
	"NumberOfPages" 						=> "{$appNumberOfPages}",
	"NumberOfTracks" 						=> "{$appNumberOfTracks}",
	"OperatingSystem" 						=> "{$appOperatingSystem}",
	"PackageDimensions"						=> "{$appPackageDimensions}",
	"PackageDimensionsWidth" 				=> "{$appPackageDimensionsWidth}",
	"PackageDimensionsHeight" 				=> "{$appPackageDimensionsHeight}",
	"PackageDimensionsLength" 				=> "{$appPackageDimensionsLength}",
	"PackageDimensionsWeight" 				=> "{$appPackageDimensionsWeight}", 
	"PackageQuantity" 						=> "{$appPackageQuantity}",
	"PartNumber" 							=> "{$appPartNumber}",
	"PictureFormat" 						=> "{$appPictureFormat}",
	"Platform" 								=> "{$appPlatform}",
	"ProductGroup" 							=> "{$appProductGroup}",
	"ProductTypeName" 						=> "{$appProductTypeName}",
	"ProductTypeSubcategory" 				=> "{$appProductTypeSubcategory}",
	"PublicationDate" 						=> "{$appPublicationDate}",
	"Publisher" 							=> "{$appPublisher}",
	"RegionCode" 							=> "{$appRegionCode}",
	"ReleaseDate" 							=> "{$appReleaseDate}",
	"RunningTime" 							=> "{$appRunningTime}",
	"SeikodoProductCode" 					=> "{$appSeikodoProductCode}",
	"ShoeSize" 								=> "{$appShoeSize}",
	"Size" 									=> "{$appSize}",
	"SKU" 									=> "{$appSKU}",
	"Studio" 								=> "{$appStudio}",
	"SubscriptionLength" 					=> "{$appSubscriptionLength}",
	"TrackSequence" 						=> "{$appTrackSequence}",
	"TradeInValue" 							=> "{$appTradeInValue}",
	"UPC" 									=> "{$appUPC}",
	"UPCList" 								=> "{$appUPCList}",
	"Warranty" 								=> "{$appWarranty}",
	"WEEETaxValue " 						=> "{$appWEEETaxValue}",

// Offers
	"LowestNewPrice" 						=> "{$appLowestNewPrice}",
	"LowestUsedPrice" 						=> "{$appLowestUsedPrice}",
	"LowestRefurbishedPrice" 				=> "{$appLowestRefurbishedPrice}",
	"LowestCollectiblePrice" 				=> "{$appLowestCollectiblePrice}",
	"TotalCollectible" 						=> "{$appTotalCollectible}",
	"TotalNew" 								=> "{$appTotalNew}",
	"TotalOfferPages" 						=> "{$appTotalOfferPages}",
	"TotalOffers" 							=> "{$appTotalOffers}",
	"TotalRefurbished"						=> "{$appTotalRefurbished}",
	"TotalUsed"							 	=> "{$appTotalUsed}",
	"NewAmazonPricing" 						=> "{$newAmzPricing}",
	"TotalAmzOffers" 						=> "{$appTotalOffers}",
	"MoreOffersUrl" 						=> "{$appMoreOffersUrl}",
	"TotalOfferPages" 						=> $appTotalOfferPages,
	"CachedAPPIP"							=> $cached,
     );
    return $RetVal;  
} 
  

function FormatSearchResult($Result){
	//FormatSearchResult by Don Fischer
	return; //not used at this time
	$Item = $Result;
	$Author = $Item["Author"];
	$Binding = $Item["Binding"];
	$EAN = $Item["EAN"];
	$Edition = $Item["Edition"];
	$Features = $Item["Feature"]; //array
	$Languages = $Item["Languages"]["Language"]; //array
	$ISBN = $Item["ISBN"];
	$Label = $Item["Label"];
	$ListPriceAmount = $Item["ListPrice"]["Amount"];
	$ListPriceCurrencyCode = $Item["ListPrice"]["CurrencyCode"];
	$ListPriceFormattedPrice = $Item["ListPrice"]["FormattedPrice"];
	$Manufacturer = $Item["Manufacturer"];
	$NumberOfItems = $Item["NumberOfItems"];
	$NumberOfPages = $Item["NumberOfPages"];
	$PackageDimensionsHeight = $Item["PackageDimensions"]["Height"]; //array
	$PackageDimensionsLength = $Item["PackageDimensions"]["Length"]; //array
	$PackageDimensionsWeight = $Item["PackageDimensions"]["Weight"]; //array
	$PackageDimensionsWidth = $Item["PackageDimensions"]["Width"]; //array
	$ProductGroup = $Item["ProductGroup"];
	$ProductTypeName = $Item["ProductTypeName"];
	$PublicationDate = $Item["PublicationDate"];
	$Publisher = $Item["Publisher"];
	$ReadingLevel = $Item["ReadingLevel"];
	$ReleaseDate = $Item["ReleaseDate"];
	$Studio = $Item["Studio"];
	$Title = $Item["Title"];
	
	//shortcut keys
	$Price = $ListPriceFormattedPrice . ' ' .$ListPriceCurrencyCode;
	$PackageDimensions = ($PackageDimensionsLength["value"] / 100) .'in x ' . ($PackageDimensionsWidth["value"] / 100). 'in x '. ($PackageDimensionsHeight["value"] / 100) .'in';
	$PackageWeight = ($PackageDimensionsWeight["value"] / 100).'lbs.';
	$Pages = $NumberOfPages;
	$RetVal = array(
		'Author' => "{$Author}",
		'Binding' => "{$Binding}",
		'EAN' => "{$EAN}",
		'Edition' => "{$Edition}",
		'Features' => "{$Features}", 
		'Languages' => "{$Languages}",
		'ISBN' => "{$ISBN}",
		'Label' => "{$Label}",
		'ListPriceAmount' => "{$ListPriceAmount}",
		'ListPriceCurrencyCode' => "{$ListPriceCurrencyCode}",
		'ListPriceFormattedPice' => "{$ListPriceFormattedPrice}",
		'Manufacturer' => "{$Manufacturer}",
		'NumberOfItems' => "{$NumberOfItems}",
		'ItemNumberOfPages' => "{$ItemNumberOfPages}",
		'PackageDimensionsHeight' => "{$PackageDimensionsHeight}", 
		'PackageDimensionsLength' => "{$PackageDimensionsLength}", 
		'PackageDimensionsWeight' => "{$PackageDimensionsWeight}", 
		'PackageDimensionsWidth' => "{$PackageDimensionsWidth}", 
		'ProductGroup' => "{$ProductGroup}",
		'ProductTypeName' => "{$ProductTypeName}",
		'PublicationDate' => "{$PublicationDate}",
		'Publisher' => "{$Publisher}",
		'ReadingLevel' => "{$ReadingLevel}",
		'ReleaseDate' => "{$ReleaseDate}",
		'Studio' => "{$Studio}",
		'Title' => "{$Title}",
		'Price' => "{$Price}",
		'PackageDimensions' => "{$PackageDimensions}",
		'PackageWeight' => "{$PackageWeight}",
		'Pages' => $Pages
	);
	if(WP_DEBUG){echo('<br/>WP_DEBUG:<br/>');print_r($RetVal);echo('<br/><br/>');}
	return $RetVal;  
} 
  
if(!function_exists('aws_signed_request')){
	/*
	aws_signed_request code from http://mierendo.com/software/aws_signed_query/ Copyright (c) 2009 Ulrich Mierendorff
	Parameters:
	    $region - the Amazon(r) region (ca,com,co.uk,de,fr,jp)
	    $params - an array of parameters, eg. array("Operation"=>"ItemLookup", "ItemId"=>"B000X9FLKM", "ResponseGroup"=>"Small")
	    $public_key - your "Access Key ID"
	    $private_key - your "Secret Access Key"
	*/
	function aws_signed_request($region, $params, $public_key, $private_key){
		global $apip_usefileget;
		global $apip_usecurlget;
		$method 							= "GET";
		$host 								= "webservices.amazon.".$region; //new API 12-2011
		$uri 								= "/onca/xml";
		$params["Service"] 					= "AWSECommerceService";
		$params["AWSAccessKeyId"] 			= $public_key;
		$params["Timestamp"] 				= gmdate("Y-m-d\TH:i:s\Z");
		$params["Version"] 					= "2011-08-01"; //"2009-03-31";
		$params["TruncateReviewsAt"] 		= "1";
		$params["IncludeReviewsSummary"]	= "True";
		$keyurl 							= $params['AssociateTag'].$params['IdType'].$params['ItemId'].$params['Operation'];
		$canonicalized_query 				= array();
		ksort($params);
		foreach ($params as $param=>$value){
		    $param = str_replace("%7E", "~", rawurlencode($param));
		    $value = str_replace("%7E", "~", rawurlencode($value));
		    $canonicalized_query[] = $param."=".$value;
		}
		$canonicalized_query 				= implode("&", $canonicalized_query);
		$string_to_sign 					= $method."\n".$host."\n".$uri."\n".$canonicalized_query;
		$signature 							= base64_encode(aws_hash_hmac("sha256", $string_to_sign, $private_key, True));
		$signature 							= str_replace("%7E", "~", rawurlencode($signature));
		$request 							= "http://".$host.$uri."?".$canonicalized_query."&Signature=".$signature;
		if(WP_DEBUG){echo('<br/>WP_DEBUG:<br/>');echo($request);echo('<br/><br/>');}   
	//do request
		global $wpdb;
		$body = "";
		$maxage = 1;
		$checksql= "SELECT Body, ( NOW() - Updated ) as Age FROM ".$wpdb->prefix."amazoncache WHERE URL = '" . $keyurl . "' AND NOT( Body LIKE '%AccountLimitExceeded%') AND NOT( Body LIKE '%SignatureDoesNotMatch%') AND NOT( Body LIKE '%InvalidParameterValue%');";
		$result = $wpdb->get_results($checksql);
		$userauth = 'spade';
		$purge_cache = ($_GET['purge-cache'] == '1' && $_GET['auth'] == $userauth) ? 1 : 0; 
		if (count($result) > 0){
			if ($result[0]->Age <= 6001 && $result[0]->Body != '' && $purge_cache == 0){ //that would be 60 min 1 seconds on MYSQL value
				$pxml = appip_get_XML_structure($result[0]->Body, $result[0]->Age);
				return $pxml;
			}else{
				if($apip_usefileget!='0'){
					 $response = file_get_contents($request);
				}elseif($apip_usecurlget!='0'){
				    $ch = curl_init();
				    $timeout = 5;
				    curl_setopt($ch, CURLOPT_URL, $request);
				    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
				    $data = curl_exec($ch);
				    curl_close($ch);
				    $response = $data;
				}else{
					$response = False;
				}
				if ($response === False){
					return False;
				}else{
					$xbody = trim(addslashes($response));
					$updatesql ="INSERT IGNORE INTO ".$wpdb->prefix."amazoncache (URL, Body, Updated) VALUES ('$keyurl', '$xbody', NOW()) ON DUPLICATE KEY UPDATE Body='$xbody', Updated=NOW();";
					$wpdb->query($updatesql);
					$pxml = appip_get_XML_structure($response,0);
					return $pxml;
				}
			}
		}else{ //if not cached (more than 1 hour ago) OR Error in CACHE - get new
			if($apip_usefileget!='0'){
				 $response = file_get_contents($request);
			}elseif($apip_usecurlget!='0'){
			    $ch = curl_init();
			    $timeout = 5;
			    curl_setopt($ch, CURLOPT_URL, $request);
			    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
			    $data = curl_exec($ch);
			    curl_close($ch);
			    $response = $data;
			}else{
				$response = False;
				return False;
			}

			$xbody = trim(addslashes($response));
			$updatesql ="INSERT IGNORE INTO ".$wpdb->prefix."amazoncache (URL, Body, Updated) VALUES ('$keyurl', '$xbody', NOW()) ON DUPLICATE KEY UPDATE Body='$xbody', Updated=NOW();";
			$wpdb->query($updatesql);
			$pxml = appip_get_XML_structure($response,0);
			return $pxml;
		}
		return False;
	}
}