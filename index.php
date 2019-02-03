<?php
session_start();
//library
require 'vendor/autoload.php';
use google\appengine\api\users\User;
use google\appengine\api\users\UserService;
use Google\Cloud\Datastore\DatastoreClient;
$projID= "cc-2019-lab4";
?>
<!doctype html>
<html>
<head>
    <link type="text/css" rel="stylesheet" href="css/style.css"/>
    <meta charset="utf-8">
</head>
<nav>
 <div>
  <ul>
      <li><a href="index.php"><?php
	  //get user
	$user = UserService::getCurrentUser();
	//use and build data store service
	$projID= "cc-2019-lab4";
	$datastore = new DatastoreClient(['projectId' => $projID]);
	// hardcoded data:
	//$search_data = "East Richmond Station";
	//echo "search data: $search_data".'<br>';
	//account login and log out
	if (isset($user)) {
	echo sprintf('Welcome, %s! (<img src= "image/SignIn.png" height="40px" width="40px"><a href="%s">sign out</a>) <br>',
		$user->getNickname(),
		UserService::createLogoutUrl('/'));
	}
	else {
		echo sprintf('<img src= "image/SignIn.png" height="40px" width="40px"><a href="%s">Sign in or register</a>',
		UserService::createLoginUrl('/'));
	}

	  ?>
	  </a></li>
      <li><a href="index.php">HOME</a></li>
      </ul>
</div>
</nav>
<body>
    <title>Train tracker</title>
<?php include('head.inc'); ?>
    <main>
      <div class="main_box">
<?php
include('Api_Request_code_New.php');

	//if (no search bar variable has sent)
	//then: display nothing.
	//else:
	//the station list is for storing the destination message.

//	$destination_list=array("station1","station2","station3");
//	//departure time for each route.
//	$departure_time = array ("16:32","5:02","7:32");
//	//the details for each route.
//	$stop_list[$destination_list[0]]=array("Flinder street","Southern cross","flagstaff","parliament","--------","--------","Clifton hill","------","collingwood","north richmond", "west richmond", "westgath","--------","--------","Clifton hill","------","collingwood","north richmond", "west richmond", "westgath");
//	$stop_list[$destination_list[1]]=array("Flinder street","Southern cross","flagstaff","parliament");
//	$stop_list[$destination_list[2]]=array("Flinder street","-------","flagstaff","parliament");
//	//platform number.
//	$departure_platform[$destination_list[0]]=1;
//	$departure_platform[$destination_list[1]]=3;
//	$departure_platform[$destination_list[2]]=7;
	//display all the messages.
//	for ($i=0;$i<sizeof($destination_list);$i++)
//	{
//       $destination_name =$destination_list[$i];
//	   echo '<div class="platform_box">';
//	   echo '<div class="platform_number">'.$departure_platform[$destination_list[$i]].'</div>';
//       echo '<h2 class="destination">'.$destination_name.'</h2>';
//       echo '<div style="display:block;margin:auto;padding:10px;font-size:30px;">'.$departure_time[$i].'</div>';
//       echo '<div class="stop_list">';
//       foreach ($stop_list[$destination_list[$i]] as $value)
//       {
//           echo "$value <br>";
//       }
//       echo '</div></div>';
//	}

/*
favorite function implementation
*/
if(!empty($stops['stop_name']))
	echo "Search result: ".$stops['stop_name'];
if (isset($user)&&!empty($stops['stop_name'])){
// favorite button
		$search_data=$stops['stop_name'];
		//echo 'favorite button:';
		echo "<form action='/' metnod ='GET'>";
		echo '<input type="hidden" name ="station"'. "value = '$search_data' />";
		echo "<input type = 'submit' value ='add to favorite'/></form>";
		if (isset($_GET["station"])){
			$datastore = new DatastoreClient(['projectId' => $projID]);
			add_fav($datastore, $search_data,$user);
		}
}
for ($i=0;$i<sizeof($destination_list);$i++)
{
    $destination_name =$destination_list[$i];
    echo '<div class="platform_box">';
    echo '<div class="platform_number">'.$departure_platform[$i].'</div>';
    echo '<h2 class="destination">'.$destination_name.' Line</h2>';
    echo '<div class="estTime">'.$departure_time[$i].'</div>';
    echo '<div class="stop_list">';
    foreach ($stop_list[$i] as $value)
    {
        echo "$value <br>";
    }
    echo '</div></div>';
}

/*
function: upload data to data store
@param: DatastoreClient datastore - the datastore we have created.
		$search_data - the input data comes from search bar.
		User $user - the object of logged in user
@return: nothing
*/
function add_fav(DatastoreClient $datastore, $search_data,User $user)
{
//set keys
$key = $datastore->key('favorite',$user->getEmail());
$task_favorite = $datastore ->entity($key);
//generate query result.
$query = $datastore->lookup($key);
$station=$query['station'];
// make empty transaction.
$transaction = $datastore->transaction();
//if no station, insert one as favorite
if($station == null){
	$task_favorite['station']=$search_data;
	$datastore->insert($task_favorite);
	echo "insert task compelete";
}
else {
	//if station has found in datastore
	//1. if same station, delete the record
	if($station == $search_data){
	echo "same data found, will delete the record";
	$transaction->delete($key);
	$transaction->commit();
	}
	//2. else, update the old record.
	else{
		echo "record will be updated.";
		$task_favorite['station']=$search_data;
		$transaction->update($task_favorite,array('allowOverwrite'=>true));
		$transaction->commit();
	}
}
}
?>
    </div>
    </main>
<?php
include('footer.inc');
?>
  </body>  

</html>