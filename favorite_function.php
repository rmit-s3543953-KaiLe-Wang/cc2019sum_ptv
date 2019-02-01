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
		//add_fav($datastore, $search_data,$user);
		if (isset($_GET["station"])){
			//echo "<br> value of post:".$_GET['station'];
			
			add_fav($datastore, $search_data,$user);
		}
		// favorite button
		echo 'favorite button:';
		echo "<form action='/' metnod ='GET'>";
		echo '<input type="hidden" name ="station"'. "value = '$search_data' />";
		echo "<input type = 'submit'/></form>";
		//print_r($_POST['station']);
		
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
$key = $datastore->key('station');
$task_favorite = $datastore ->entity($key,['user'=>$user->getEmail(),'station' =>$search_data]);
$query = checkData($datastore,$search_data,$user);
if($query==null){	
	$datastore->insert($task_favorite);
	echo "insert task compelete";
}
else {
	echo "it is testing part";
	echo "<pre>";
	foreach ($query as $entity){
	echo $entity['station'];
	echo "<pre>";
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
function checkData(DatastoreClient $datastore, $search_data,User $user)
{
	$query = $datastore->query()->kind('Favorite')->filter('user','=',$user->getEmail());
	$result = $datastore->runQuery($query);
	if(empty($result))
	{
		return null;
	}
	else
		return $result;
}
?>