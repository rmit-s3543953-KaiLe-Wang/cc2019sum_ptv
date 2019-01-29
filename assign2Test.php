<?php
$UserID = '3001008';
$key = 'e3251427-f68b-4535-a093-1d380e17e5dc';

$SearchUrl = "/v3/search/Richmond?route_types=0";
$signedUrl = generateURL($SearchUrl, $UserID, $key);
$content = file_get_contents($signedUrl);
$obj = json_decode($content, true);
$stops = $obj['stops'];

//echo $Num;
//print_r($obj);
$index =0;
foreach ($obj['stops'] as $stops)
{

    if(strcasecmp("Richmond station",$stops['stop_name'])==0)
    {
    	echo "Stop Name:". $stops['stop_name'] ."\n";
    	echo '<br/>';
    	echo "Stop_ID:". $stops['stop_id'] ."\n";
    	echo '<br/>';
    	break;
    }
   $index++;
};


$DirectionUrl = "/v3/directions/route/8";
$DepartureUrl = "http://timetableapi.ptv.vic.gov.au/v3/departures/route_type/0/stop/1145/route/8?direction_id=7&look_backwards=false&max_results=10&include_cancelled=false";
$Pattern = "http://timetableapi.ptv.vic.gov.au/v3/pattern/run/949921/route_type/0?expand=route";



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
 
	$signature = strtoupper(hash_hmac("sha1", $Url, $key, false));
	return "http://timetableapi.ptv.vic.gov.au" . $Url . "&signature=" . $signature;
}

function drawResponse($signedUrl)
{
   $content = file_get_contents($signedUrl);
   $obj = json_decode($content, true);
   foreach ($obj['directions'] as $directions)
{
    echo "Direction_ID:". $directions['direction_id'] ."\n";
    echo '<br/>';
    echo "Direction_Name:". $directions['direction_name'] ."\n";
    echo '<br/>';
};
}

?>