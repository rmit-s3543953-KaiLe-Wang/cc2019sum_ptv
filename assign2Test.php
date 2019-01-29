<?php
/*
	developerID and Key for API
 */
$UserID = '3001008';
$key = 'e3251427-f68b-4535-a093-1d380e17e5dc';


/*
	get stop name & ID through search url
 */
$SearchUrl = "/v3/search/Richmond?route_types=0";
$search = generateURL($SearchUrl, $UserID, $key);
$content = file_get_contents($search);
$obj = json_decode($content, true);
$stops = $obj['stops'];

$index =0;
$stopName = '';
$stopID = '';
foreach ($obj['stops'] as $stops)
{
    if(strcasecmp("Richmond station",$stops['stop_name'])==0)
    {
    	$stopName .= $stops['stop_name'];
    	$stopID .= $stops['stop_id'];
    	break;
    }
};



/*
	get route ID of first 10 results from departure
 */
$DepartureUrl = "/v3/departures/route_type/0/stop/$stopID?max_results=1";
$departure = generateURL($DepartureUrl, $UserID, $key);
$content1 = file_get_contents($departure);
$obj1 = json_decode($content1, true);
$departures = $obj1['departures'];

$arrayTemp = [];
foreach ($obj1['departures'] as $departures) {
		array_push($arrayTemp, $departures['route_id']);
}
$arrayTemp = array_flip($arrayTemp);
$arrayTemp = array_keys($arrayTemp);


/*
	get direction id and all put in an array with route id in pairs
 */
$arrlength=count($arrayTemp);
$arrayRnD = [];

foreach ($arrayTemp as $Temp) {
  	$DirectionUrl = "/v3/directions/route/$Temp";
	$direction = generateURL($DirectionUrl, $UserID, $key);
	$content2 = file_get_contents($direction);
	$obj2 = json_decode($content2, true);
	//print_r($obj2);
	$directions = $obj2['directions'];

	foreach ($obj2['directions'] as $directions)
	{
		array_push($arrayRnD, [
        'Route_ID'=> $Temp,
        'Direction_ID'=> $directions['direction_id'],
    	]);
	};
} 


/*
	Get Final stop names
 */
$res = array();
foreach ($arrayRnD as $RnD) {
	$StopURL = "/v3/stops/route/". $RnD['Route_ID'] ."/route_type/0?direction_id=". $RnD['Direction_ID'];
	$finalstop = generateURL($StopURL, $UserID, $key);
	$content3 = file_get_contents($finalstop);
	$obj3 = json_decode($content3, true);

	$finals = $obj3['stops'];
	$stopArray = array();
	foreach ($obj3['stops'] as $finals) {
			$stopArray[] = $finals['stop_name'] ;
		}
		$res[] = $stopArray;
}

print_r($res[0]);

/*
	Function to form an requirest URL
 */

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

?>