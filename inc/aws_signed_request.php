<?php
define('DEBUG', false);
//hash_hmac code from comment by Ulrich in http://mierendo.com/software/aws_signed_query/
//sha256.inc.php from http://www.nanolink.ca/pub/sha256/ 

function aws_hash_hmac($algo, $data, $key, $raw_output=False){
  // RFC 2104 HMAC implementation for php.
  // Creates a sha256 HMAC.
  // Eliminates the need to install mhash to compute a HMAC
  // Hacked by Lance Rushing
  // source: http://www.php.net/manual/en/function.mhash.php
  // modified by Ulrich Mierendorff to work with sha256 and raw output
  
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
//GetXMLTree and GetChildren code from http://whoooop.co.uk/2005/03/20/xml-to-array/

function GetXMLTree ($xmldata){
	if($xmldata==''){return False;}
	// we want to know if an error occurs
	ini_set ('track_errors', '1');

	$xmlreaderror = false;

	$parser = xml_parser_create ('ISO-8859-1');
	xml_parser_set_option ($parser, XML_OPTION_SKIP_WHITE, 1);
	xml_parser_set_option ($parser, XML_OPTION_CASE_FOLDING, 0);
	if (!xml_parse_into_struct ($parser, $xmldata, $vals, $index)) {
		$xmlreaderror = true;
	}
	xml_parser_free ($parser);

	if (!$xmlreaderror) {
		$result = array ();
		$i = 0;
		if (isset ($vals [$i]['attributes']))
			foreach (array_keys ($vals [$i]['attributes']) as $attkey)
			$attributes [$attkey] = $vals [$i]['attributes'][$attkey];

		$result [$vals [$i]['tag']] = array_merge ($attributes, GetChildren ($vals, $i, 'open'));
	}
	ini_set ('track_errors', '0');
	return $result;
}

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

function FormatASINResult($Result){ //main function for single product 
    $Item 					= $Result['ItemLookupResponse']['Items']['Item'];
    $ItemAttr 				= $Item['ItemAttributes'];
  	$ImageSM 				= $Item['SmallImage']['URL'];
  	$ImageMD 				= $Item['MediumImage']['URL'];
  	$ImageLG 				= $Item['LargeImage']['URL'];
  	$lowestNewPrice 		= $Item["OfferSummary"]["LowestNewPrice"]["FormattedPrice"];
  	$lowestUsedPrice 		= $Item["OfferSummary"]["LowestUsedPrice"]["FormattedPrice"];
    $TotalNew 				= $Item["OfferSummary"]["TotalNew"];
    $TotalUsed 				= $Item["OfferSummary"]["TotalUsed"];
    $TotalCollectible 		= $Item["OfferSummary"]["TotalCollectible"];
    $TotalRefurbished 		= $Item["OfferSummary"]["TotalRefurbished"];

  	if($lowestNewPrice=='Too low to display'){$isPriceHidden=1;}else{$isPriceHidden=0;}
    if(!isset($Item['Offers']['Offer']['OfferListing']['Price'])){$SalePrice = $Item['Offers']['Offer']['OfferListing']['Price'];}else{$SalePrice = $Item['OfferSummary']['LowestNewPrice']['Amount'];}
    if(is_array($ItemAttr["Binding"])){$Binding = implode(", ", $ItemAttr["Binding"]);}else{$Binding = $ItemAttr["Binding"];}
    if(is_array($ItemAttr["Author"])){$Author = implode(", ", $ItemAttr["Author"]);}else{$Author = $ItemAttr["Author"];}
    if(is_array($ItemAttr["Director"])){$Director = implode(", ", $ItemAttr["Director"]);}else{$Director = $ItemAttr["Director"];}
    if(is_array($ItemAttr["Actor"])){$Actors = implode(", ", $ItemAttr["Actor"]);}else{$Actors = $ItemAttr["Actor"];}
    if(is_array($ItemAttr["Format"])){$Formats = implode(", ", $ItemAttr["Format"]);}else{$Formats = $ItemAttr["Format"];}
    if(is_array($ItemAttr["Languages"]["Language"])){$Languages = implode(", ", $ItemAttr["Languages"]["Language"]);}else{$Languages = $ItemAttr["Languages"]["Language"];}
    if(is_array($ItemAttr["AudienceRating"])){$Rating = implode(", ", $ItemAttr["AudienceRating"]);}else{$Rating = $ItemAttr["AudienceRating"];}
    if(is_array($ItemAttr["RunningTime"])){$RunTime = $ItemAttr["RunningTime"]["value"].' '.$ItemAttr["RunningTime"]["Units"];}else{$RunTime = '';}
   
    $OfferListingId 		= $Item['Offers']['Offer']['OfferListing']['OfferListingId'];
	if(isset($ItemAttr["ListPrice"]["FormattedPrice"])){
		$ListPrice 		= $ItemAttr["ListPrice"]["FormattedPrice"] . ' ' . $ItemAttr["ListPrice"]["CurrencyCode"];
	}else{
		$ListPrice 		= '0';
	}
	$ReleaseDate 	= $ItemAttr["ReleaseDate"];

	if(isset($ItemAttr["ListPrice"]["Amount"]) && isset($SalePrice['Amount']) ){
		$SavingsPrice =  number_format(($ItemAttr["ListPrice"]["Amount"]/ 100.0),2) - number_format(($SalePrice['Amount'] / 100.0), 2);
		if($ItemAttr["ListPrice"]["Amount"]!=0){$SavingsPercent = ($SavingsPrice / number_format($ItemAttr["ListPrice"]["Amount"]/100,2))*100;}else{$SavingsPercent = 0;}
	}else{
		$SavingsPrice =  0;
		$SavingsPercent = 0;
	}
    $RetVal = array('ASIN' => $Item['ASIN'],
                    'ProductGroup' => $Item['ItemAttributes']['ProductGroup'],
				    'SmallImage' => $ImageSM,
				    'MediumImage' => $Item['MediumImage']['URL'],
				    'LargeImage' => $ImageLG,
                    'Title' => $Item['ItemAttributes']['Title']. ' ('.$Binding.')',
                    'URL' => $Item['DetailPageURL'],
                    'TotalOffers' => $Item['Offers']['TotalOffers'],
                    'Amount' => $SalePrice['Amount'] / 100.0,
                    'FormattedAmount' => '$'.($SalePrice['Amount'] / 100.0 ) . ' '. $SalePrice['CurrencyCode'],
                    'Currency' => $SalePrice['CurrencyCode'],
                    'SalePrice' => $SalePrice['FormattedPrice'],
                    'ReleaseDate' => $ReleaseDate,
                    'ListPrice' => $ListPrice,
                    'Binding' => $Binding,
                    'Author' => $Author,
                    'SavingsPrice' => '$'.$SavingsPrice,
                    'SavingsPercent' => number_format($SavingsPercent,0),
				    'Director' => $Director,
				    'Actors' => $Actors,
				    'Rating' => $Rating,
				    'Formats' => $Formats,
				    'Languages' => $Languages,
				    'OfferListingId' => $OfferListingId,
				    'RunTime' => $RunTime,
				    'LowestNewPrice' => $lowestNewPrice,
				    'LowestUsedPrice' => $lowestUsedPrice,
				    'PriceHidden' => $isPriceHidden,
				    'TotalNew' => $TotalNew,
				    'TotalUsed' => $TotalUsed,
                    'Errors' => $Result['itemlookuperrorresponse']['error']
                   );
    return $RetVal;  
  } 
  
//FormatSearchResult by Don Fischer

function FormatSearchResult($Result){
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
					'Author' => $Author,
					'Binding' => $Binding,
					'EAN' => $EAN,
					'Edition' => $Edition,
					'Features' => $Features, 
					'Languages' => $Languages,
					'ISBN' => $ISBN,
					'Label' => $Label,
					'ListPriceAmount' => $ListPriceAmount,
					'ListPriceCurrencyCode' => $ListPriceCurrencyCode,
					'ListPriceFormattedPice' => $ListPriceFormattedPrice,
					'Manufacturer' => $Manufacturer,
					'NumberOfItems' => $NumberOfItems,
					'ItemNumberOfPages' => $ItemNumberOfPages,
					'PackageDimensionsHeight' => $PackageDimensionsHeight, 
					'PackageDimensionsLength' => $PackageDimensionsLength, 
					'PackageDimensionsWeight' => $PackageDimensionsWeight, 
					'PackageDimensionsWidth' => $PackageDimensionsWidth, 
					'ProductGroup' => $ProductGroup,
					'ProductTypeName' => $ProductTypeName,
					'PublicationDate' => $PublicationDate,
					'Publisher' => $Publisher,
					'ReadingLevel' => $ReadingLevel,
					'ReleaseDate' => $ReleaseDate,
					'Studio' => $Studio,
					'Title' => $Title,
					'Price' => $Price,
					'PackageDimensions' => $PackageDimensions,
					'PackageWeight' => $PackageWeight,
					'Pages' => $Pages
                   );
    
    if(DEBUG)
    {
      echo('<br/><br/>');      
      print_r($RetVal);
      echo('<br/><br/>');
    }
                 
    return $RetVal;  
  } 

//aws_signed_request code from http://mierendo.com/software/aws_signed_query/
function aws_signed_request($region, $params, $public_key, $private_key){
    /*
    Copyright (c) 2009 Ulrich Mierendorff

    Permission is hereby granted, free of charge, to any person obtaining a
    copy of this software and associated documentation files (the "Software"),
    to deal in the Software without restriction, including without limitation
    the rights to use, copy, modify, merge, publish, distribute, sublicense,
    and/or sell copies of the Software, and to permit persons to whom the
    Software is furnished to do so, subject to the following conditions:

    The above copyright notice and this permission notice shall be included in
    all copies or substantial portions of the Software.

    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
    IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
    FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
    THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
    LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
    FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
    DEALINGS IN THE SOFTWARE.
    */
    
    /*
    Parameters:
        $region - the Amazon(r) region (ca,com,co.uk,de,fr,jp)
        $params - an array of parameters, eg. array("Operation"=>"ItemLookup", "ItemId"=>"B000X9FLKM", "ResponseGroup"=>"Small")
        $public_key - your "Access Key ID"
        $private_key - your "Secret Access Key"
    */
    
	global $apip_usefileget, $apip_usecurlget;
	
    // some paramters
    $method = "GET";
    //$host = "ecs.amazonaws.".$region; //old API
    $host = "webservices.amazon.".$region; //new API 12-2011
    $uri = "/onca/xml";
    
    // additional parameters
    $params["Service"] = "AWSECommerceService";
    $params["AWSAccessKeyId"] = $public_key;
    $params["Timestamp"] = gmdate("Y-m-d\TH:i:s\Z");
    $params["Version"] = "2011-08-01"; //"2009-03-31";
 	$keyurl = $params['AssociateTag'].$params['IdType'].$params['ItemId'].$params['Operation'];
   
    // sort the parameters
    ksort($params);
    // create the canonicalized query
    $canonicalized_query = array();
    foreach ($params as $param=>$value){
        $param = str_replace("%7E", "~", rawurlencode($param));
        $value = str_replace("%7E", "~", rawurlencode($value));
        $canonicalized_query[] = $param."=".$value;
   }
    $canonicalized_query = implode("&", $canonicalized_query);
  
    // create the string to sign
    $string_to_sign = $method."\n".$host."\n".$uri."\n".$canonicalized_query;
   
    // calculate HMAC with SHA256 and base64-encoding
    $signature = base64_encode(aws_hash_hmac("sha256", $string_to_sign, $private_key, True));
    
    // encode the signature for the request
    $signature = str_replace("%7E", "~", rawurlencode($signature));
   
    // create request
    $request = "https://".$host.$uri."?".$canonicalized_query."&Signature=".$signature;

    if(DEBUG){
      echo('<br/><br/>');
      echo($request);
      echo('<br/><br/>');
    }    
    
    // do request
    	// first check cache check
		global $wpdb;
		$body = "";
		$maxage = 1;
		$checksql= "SELECT Body, ( NOW() - Updated ) as Age FROM ".$wpdb->prefix."amazoncache WHERE URL = '" . $keyurl . "' AND NOT( Body LIKE '%AccountLimitExceeded%') AND NOT( Body LIKE '%SignatureDoesNotMatch%') AND NOT( Body LIKE '%InvalidParameterValue%');";
		$result = $wpdb->get_results($checksql);
		
		if (count($result) > 0){
			if ($result[0]->Age <= 6001 && $result[0]->Body != ''){ //that would be 60 min 1 seconds on MYSQL value
				$pxml = GetXMLTree($result[0]->Body);
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
					if(strpos($xbody,'AccountLimitExceeded') >= 1){return 'exceeded';}
					if(strpos($xbody,'SignatureDoesNotMatch') >= 1){return 'no signature match';}
					if(strpos($xbody,'InvalidParameterValue') >= 1){return 'not valid';}
					$updatesql ="INSERT IGNORE INTO ".$wpdb->prefix."amazoncache (URL, Body, Updated) VALUES ('$keyurl', '$xbody', NOW()) ON DUPLICATE KEY UPDATE Body='$xbody', Updated=NOW();";
					$wpdb->query($updatesql);
					$pxml = GetXMLTree($response);
					return $pxml;
				}
			}
		}else{ //if not cached (less than 1 hour ago) OR Error in CACHE - get new
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
			if(strpos($xbody,'AccountLimitExceeded') >= 1){return 'exceeded';}
			if(strpos($xbody,'SignatureDoesNotMatch') >= 1){return 'no signature match';}
			if(strpos($xbody,'InvalidParameterValue') >= 1){return 'not valid';}
			$updatesql ="INSERT IGNORE INTO ".$wpdb->prefix."amazoncache (URL, Body, Updated) VALUES ('$keyurl', '$xbody', NOW()) ON DUPLICATE KEY UPDATE Body='$xbody', Updated=NOW();";
			$wpdb->query($updatesql);
			$pxml = GetXMLTree($response);
			return $pxml;
		}
	return False;
}
?>