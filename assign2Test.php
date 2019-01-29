<?php
$UserID = '3001008';
$key = 'e3251427-f68b-4535-a093-1d380e17e5dc';
$SearchUrl = "/v3/search/Richmond?route_types=0";
$DirectionUrl = "/v3/directions/route/8";
$signedUrl = generateURL($DirectionUrl, $UserID, $key);
drawResponse($signedUrl);

function generateURL($Url, $UserID, $key)
{
	// append developer ID to API endpoint URL
	if (strpos($Url, '?') > 0)
	{
		$Url .= "&";
	}
	else
	{
		$Url .= "?";
	}
	$Url .= "devid=" . $UserID;
 
	// hash the endpoint URL
	$signature = strtoupper(hash_hmac("sha1", $Url, $key, false));
 
	// add API endpoint, base URL and signature together
	return "http://timetableapi.ptv.vic.gov.au" . $Url . "&signature=" . $signature;
}

function drawResponse($signedUrl)
{
    echo "<p>$signedUrl</p>";
    echo "<textarea rows=\"10\" cols=\"60\">";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $signedUrl); 
    curl_setopt($ch, CURLOPT_TIMEOUT, '3'); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    echo $xmlstr = curl_exec($ch); 
    curl_close($ch);
    
    echo "</textarea>";
}

?>