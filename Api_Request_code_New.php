<?php
date_default_timezone_set('Australia/Sydney');
$input=$_GET["search"];
$inSearch= preg_replace('/\s+/', '%20', $_GET["search"]);
/*
	developerID and Key for API
 */
$UserID = '3001008';
$key = 'e3251427-f68b-4535-a093-1d380e17e5dc';

/*
	get stop name & ID through search url
 */
$SearchUrl = "/v3/search/$inSearch?route_types=0&include_addresses=false&include_outlets=false&match_stop_by_suburb=true&match_route_by_suburb=false&match_stop_by_gtfs_stop_id=false";

$search = generateURL($SearchUrl, $UserID, $key);
$content = file_get_contents($search);
$obj = json_decode($content, true);
$stops = $obj['stops'];

$index =0;
$stopName = '';
$stopID = '';
foreach ($obj['stops'] as $stops)
{
    if(strcasecmp("$input station",$stops['stop_name'])==0)
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
//            'EstTime'=>strtotime($departures["estimated_departure_utc"])
            'EstTime'=>substr($departures["estimated_departure_utc"],11,5),
        ]);
}

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

	$stopTemp = array();
	foreach ($obj3['stops'] as $finalstop) {
			$stopTemp[] = $finalstop['stop_name'];
	}
	//limite stopArray only contain the stops after user search
	$stopArray = array_slice($stopTemp,array_search($input,array_map('strtolower',$stopTemp)));

	array_push($res, [
		'Route_ID' => $Temp['Route_ID'], 
		'Route_Name'=> $routes['route_name'],
        'Direction_ID' => $Temp['Direction_ID'], 
        'Run_ID' => $Temp['Run_ID'], 
        'Platform_Number'=> $Temp['Platform_Number'],
        'Estimate_Time' => $Temp['EstTime'],
        'Stops'=> $stopArray,
    	]);
}

//sort Array format
//1st sort by plateform and get new array
usort($res,"cmp_route_asc");
function cmp_route_asc($a, $b){
    if ($a['Route_ID'] == $b['Route_ID']){
        return 0;
    }
    return ($a['Route_ID'] < $b['Route_ID'])? 1 : -1;
}

$finalRes = array_reverse($res);
$destination_list = array_column($finalRes,'Route_Name');
$stop_list = array_column($finalRes,'Stops');
$departure_platform = array_column($finalRes,'Platform_Number');
$departure_time = array_column($finalRes,'Estimate_Time');

//echo"<pre>";
//print_r($stopArray);
//echo "longlist";
//print_r($finalRes);
//print_r($destination_list);
//print_r($departure_platform);
//print_r($departure_time);
//print_r($stop_list);
//echo "<pre>";

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