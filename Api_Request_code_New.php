<?php
/*
	developerID and Key for API
 */
$UserID = '3001008';
$key = 'e3251427-f68b-4535-a093-1d380e17e5dc';


/*
	get stop name & ID through search url
 */
$SearchUrl = "/v3/search/Richmond?route_types=0&include_addresses=false&include_outlets=false&match_stop_by_suburb=true&match_route_by_suburb=false&match_stop_by_gtfs_stop_id=false";

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
$DepartureUrl = "/v3/departures/route_type/0/stop/$stopID?look_backwards=false&max_results=2&include_cancelled=false&expand=route";
$departure = generateURL($DepartureUrl, $UserID, $key);
$content1 = file_get_contents($departure);
$obj1 = json_decode($content1, true);
$departures = $obj1['departures'];
$routes = $obj1['routes'];

$arrayTemp = [];
$arrayRouteName = [];
foreach ($obj1['departures'] as $departures) {
		//array_push($arrayTemp, $departures['route_id']);
	array_push($arrayTemp, [
            'Route_ID'=>$departures['route_id'],
            'Direction_ID'=>$departures['direction_id'],
            //'Route_Name'=>$routes['route_name'],
            'Platform_Number'=>$departures["platform_number"],
            'Run_ID'=>$departures['run_id'],
            'EstTime'=>substr($departures["estimated_departure_utc"],11,5),
        ]);
}

//echo "<pre>";
//print_r($arrayTemp);
//echo "<pre>";

//$arrayTemp = array_flip($arrayTemp);
//$arrayTemp = array_keys($arrayTemp);


/*
	get direction id and all put in an array with route id in pairs
 */
/*
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
echo "<pre>";
print_r($arrayRnD);
echo "<pre>";
*/

/*
	get run ID of first 5 results from departure
 */
/*
$arrayRunID = [];
foreach ($arrayRnD as $RD) {
	$DepartureUrl2 = "/v3/departures/route_type/0/stop/". $stopID ."/route/". $RD['Route_ID'] ."?direction_id=". $RD['Direction_ID'] ."&look_backwards=false&max_results=2&include_cancelled=false";
	$departure2 = generateURL($DepartureUrl2, $UserID, $key);
	//echo $departure2;
	//echo "<br/>";
	$content4 = file_get_contents($departure2);
	$obj4 = json_decode($content4, true);
	$departures = $obj4['departures'];

	foreach ($obj4['departures'] as $departures) {

		array_push($arrayRunID, [
        'Platform_Number'=> $departures['platform_number'],
        'Run_ID'=> $departures['run_id'],
    	]);

		//array_push($arrayRunID, $departures['run_id']);
	}
}

echo "<pre>";
print_r($arrayRunID);
echo "<pre>";
*/

/*
	Get Final stop names
 */
$res = array();
//$platform = array();
foreach ($arrayTemp as $Temp) {
	$PatternURL = "/v3/pattern/run/". $Temp['Run_ID'] ."/route_type/0?expand=stop&stop_id=". $stopID;
	$pattern = generateURL($PatternURL, $UserID, $key);
	$content3 = file_get_contents($pattern);
	$obj3 = json_decode($content3, true);
	$finalstop = $obj3['stops'];

	$RouteURL = "/v3/routes/". $Temp['Route_ID'];
	$route = generateURL($RouteURL, $UserID, $key);
	$content4 = file_get_contents($route);
	$object = json_decode($content4, true);
	$routes = $object['route'];


	$stopArray = array();
	foreach ($obj3['stops'] as $finalstop) {
			$stopArray[] = $finalstop['stop_name'] ;
		}

	array_push($res, [
		'Route_ID' => $Temp['Route_ID'], 
		'Route_Name'=> $routes['route_name'],
        'Direction_ID' => $Temp['Direction_ID'], 
        'Run_ID' => $Temp['Run_ID'], 
        'Platform_Number'=> $Temp['Platform_Number'],
        'Estimate_Time' => $Temp['EstTime'],
        'Stops'=> $stopArray,
    	]);
	//$res[] = $stopArray;
}

/*
foreach ($arrayTemp as $Temp){
	$RouteURL = "/v3/routes/". $Temp['Route_ID'];
	$route = generateURL($RouteURL, $UserID, $key);
	$content4 = file_get_contents($route);
	$object = json_decode($content4, true);
	$routes = $object['route'];

	array_push($arrayRouteName, [
		'Route_ID'=>$routes['route_id'],
		'Route_Name'=>$routes['route_name'],
	]);
}
*/
echo"<pre>";
print_r($res);
echo "<pre>";


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