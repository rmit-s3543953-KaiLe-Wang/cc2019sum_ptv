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
     <div id="logo"><img src= "image/kanngaru.png" height="60px" width="60px">Train tracker</div>
  <ul><li><a href="index.php">HOME</a></li>
      <li><?php
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
//	    echo sprintf('<a href="%s"><img src= "image/SignIn.png" height="20px" width="20px">sign out </a>',
	    echo sprintf('<a href="%s"><img src= "image/SignIn.png" height="20px" width="20px">%s(sign out)</a>',
		UserService::createLogoutUrl('/'),$user->getNickname());
	echo '<li><a href="history.php">history</a></li>';

	}
	else {
		echo sprintf('<a href="%s"><img src= "image/SignIn.png" height="20px" width="20px">Sign in or register </a>',
		UserService::createLoginUrl('/'));
	}
	  ?>
      </li>
<!--      <li><a href=""><img src= "image/SignIn.png" height="20px" width="20px">My account</a></li>-->
<!--      <li><a href=""><img src= "image/star.png" height="20px" width="20px">Favourite</a></li>-->
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

/*
favorite function implementation
*/
if(!empty($stops['stop_name'])&&$stops['stop_name']!=null){
echo "<h1 style= 'display: inline-block; margin:auto;'>".$stops['stop_name'].'</h1>';
    if (isset($user)){
// favorite button
		$search_data=$stops['stop_name'];
		//echo 'favorite button:';
		echo "<form action='index.php' method ='GET' style= 'display: inline-block; margin:auto;'>";
		echo '<input type="hidden" name ="station"'. "value = '$search_data' />";
//		echo "<input type = 'submit' value ='add to favorite'/></form>";
        echo "<button type = 'submit' value=''><img src='image/star.png' width='20px' height='20px' alt='submit' /></button></form>";
		if (isset($_GET["station"])){
			$datastore = new DatastoreClient(['projectId' => $projID]);
			$flag=add_fav($datastore, $search_data,$user);
			//if ($flag==0)
				$_SESSION["search"]=$search_data;
		}
    }

    for ($j=0;$j<sizeof($route_list);$j++) {
        if (array_key_exists($j,$route_Temp)){
            $route_name = $route_list[$j];
            echo '<div class="route_box"><h2>'. $route_name.' Line </h2><br>';
            for ($i=$j;$i<sizeof($route_list);$i++){
                if($route_Temp[$j] == $route_list[$i]){
                    $direction_name = end($stop_list[$i]);
                    echo '<div class="platform_box">';
                    echo '<div class="platform_number">'.$departure_platform[$i].'</div>';
                    echo '<div class="estTime">Scheduled: '.$departure_time[$i].'</div>';
                    echo '<div class="destination"><h3>To '.$direction_name.'</h3></div>';
                    echo '<div class="stop_list">';
                    foreach ($stop_list[$i] as $value) {
                        echo "$value <br>";
                        $direction_name = $value;
                    }
                    echo '</div></div>';
                }
            }
            echo '</div>';
        }
    }
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
	echo "<br>favorite added<br>";
	return 0;
}
else {
	//if station has found in datastore
	//1. if same station, delete the record
	if($station == $search_data){
	echo "<br>favorite deleted<br>";
	$transaction->delete($key);
	$transaction->commit();
	return 1;
	}
	//2. else, update the old record.
	else{
		echo "<br>favorite updated<br>";
		$task_favorite['station']=$search_data;
		$transaction->update($task_favorite,array('allowOverwrite'=>true));
		$transaction->commit();
		return 0;
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