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
        echo"<pre>";
        echo $stopName;
        echo $stopID;
        echo PHP_EOL;
        echo "<pre>";
    	break;
    }
};



/*
	get route ID of first 10 results from departure
 */
$DepartureUrl = "/v3/departures/route_type/0/stop/$stopID?look_backwards=false&max_results=1";
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

echo "route id:";
echo"<pre>";
print_r($arrayTemp);
echo "<pre>";
echo PHP_EOL;


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
		array_push($arrayRnD, ['Route_ID'=> $Temp, 'Direction_ID'=> $directions['direction_id'],]);
	};
}
echo "arrayRnd:";
echo"<pre>";
print_r($arrayRnD);
echo "<pre>";
echo PHP_EOL;

/*
	get run ID of first 2 results of each pairs from departure
 */
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
                'RouteID'=>$RD['Route_ID'],'DirectionID'=>$RD['Direction_ID'],
                'PlateForm'=>$departures["platform_number"],'RunId'=>$departures['run_id'],
                'EstTime'=>substr($departures["estimated_departure_utc"],11,5),
                ]);
	}
}

echo "run ID:";
echo"<pre>";
print_r($arrayRunID);
echo "<pre>";
echo PHP_EOL;


/*
	Get Final stop names
 */
$res = [];
//$platform = array();
foreach ($arrayRunID as $Run) {
	$PatternURL = "/v3/pattern/run/". $Run['RunId'] ."/route_type/0?expand=stop&stop_id=". $stopID;
	$pattern = generateURL($PatternURL, $UserID, $key);
	$content3 = file_get_contents($pattern);
	$obj3 = json_decode($content3, true);

	$finalstop = $obj3['stops'];
	$stopArray = array();
	foreach ($obj3['stops'] as $finalstop) {
			$stopArray[] = $finalstop['stop_name'] ;
		}
    array_push($res, [
        'RouteID'=>$Run['RouteID'],'DirectionID'=>$Run['DirectionID'],
        'RunId'=>$Run['RunId'],'PlateForm'=>$Run['PlateForm'],
        'EstTime'=>$Run['EstTime'],'stops'=>$stopArray,]);
}

//combine necessary array data

echo "last:";

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