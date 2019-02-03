<?php
/*
library
*/
require 'vendor/autoload.php';
use google\appengine\api\users\User;
use google\appengine\api\users\UserService;
use Google\Cloud\Datastore\DatastoreClient;

//get user
$user = UserService::getCurrentUser();
//use and build data store service
$projID= "cc-2019-lab4";
$datastore = new DatastoreClient(['projectId' => $projID]);

// hardcoded data:
$search_data = "East Richmond Station";
echo "search data: $search_data".'<br>';
//account login and log out
if (isset($user)) {
	echo sprintf('Welcome, %s! (<a href="%s">sign out</a>) <br>',
		$user->getNickname(),
		UserService::createLogoutUrl('/'));
		if (isset($_GET["station"])){
			add_fav($datastore, $search_data,$user);
		}
		// favorite button
		echo 'favorite button:';
		echo "<form action='index.php' metnod ='GET'>";
		echo '<input type="hidden" name ="station"'. "value = '$search_data' />";
		echo "<input type = 'submit'/></form>";
		
}
	else {
	echo sprintf('<a href="%s">Sign in or register</a>',
		UserService::createLoginUrl('/'));
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
$query = searchForStation($datastore,$search_data,$user,$key);
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
/*
function: checkData
@param: DatastoreClient datastore - the datastore we have created.
		$search_data - the input data comes from search bar.
		User $user - the object of logged in user
@return: null = data not exsist.
		 $result - the result of the query
*/
function searchForStation(DatastoreClient $datastore, $search_data,User $user,$key)
{
	$result = $datastore->lookup($key);
	return $result;
}
?>