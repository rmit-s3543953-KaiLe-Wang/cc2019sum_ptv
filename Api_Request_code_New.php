<?php
date_default_timezone_set('Australia/Sydney');
$input;
$isFirstTime=True;
// if there is input but GET is lost
if(empty($_GET["search"])&& !empty($_SESSION["search"]))
{
	$input=$_SESSION["search"];
	//echo "<br>1st, $input<br>";
	$_SESSION["search"]=array();
	$isFirstTime=False;
}
// if no input is here e.g. 1st time open, then use favorite value, if none, then not display anything.
else if (empty($_GET["search"])&& empty($_SESSION["search"]))
{
	//echo "<br>2nd<br>";
	$key = $datastore->key('favorite',$user->getEmail());
	//generate query result.
	$query = $datastore->lookup($key);
	$station=$query['station'];
	if ($station ==null)
		$isFirstTime=True;
	else
	{
		$isFirstTime=False;
		$input = $station;
	}
}
else{
	$input = $_GET["search"];
	//if get is not empty, means that there is a request, thus save it on history.
	if (isset($user)){
	$key = $datastore->key('history',$user->getEmail());
    $task = $datastore -> entity($key);
    $query = $datastore->lookup($key);
	$records=explode(",",$query['station']);
	$numberOfRecords=count($records);
	//if there is no recording, then insert one new entity.
	$search_data=$input;
      if ($numberOfRecords==0||($numberOfRecords==1&&$query==null))
      {
        $task['station']=$search_data;
        $datastore->insert($task);
      }
      // else, update new record under previous entity.
      else{
        $transaction = $datastore->transaction();
        //echo "<br>update function<br>";
        $update_string=$query['station'].','.$search_data;
        $task['station']=$update_string;
        $transaction->update($task,array('allowOverwrite'=>true));
        $transaction->commit();
      }
	$_SESSION["search"]=$input;
	}
	$isFirstTime=False;
	//echo '<br>3rd,'.$input.','.$_SESSION["search"].'<br>';
}
if ($isFirstTime==false){
$inSearch= preg_replace('/\s+/', '%20', $input);
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
    if(strcasecmp("$input station",$stops['stop_name'])==0||strcasecmp("$input",$stops['stop_name'])==0)
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

$finalRes = array_reverse($res);

$route_list = array_column($finalRes,'Route_Name');
$stop_list = array_column($finalRes,'Stops');
$departure_platform = array_column($finalRes,'Platform_Number');
$departure_time = array_column($finalRes,'Estimate_Time');
$route_Temp = array_unique($route_list);

//echo"<pre>";
//print_r($stopArray);
//echo "longlist";
//print_r($finalRes);
//print_r($route_list);
//print_r($route_Temp);
//print_r($departure_platform);
//print_r($departure_time);
//print_r($stop_list);
//echo "<pre>";

}
function cmp_route_asc($a, $b){
    if ($a['Route_ID'] == $b['Route_ID']){
        return 0;
    }
    return ($a['Route_ID'] < $b['Route_ID'])? 1 : -1;
}

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