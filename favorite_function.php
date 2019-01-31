<?php
/*
library
*/
use google\appengine\api\users\User;
use google\appengine\api\users\UserService;
use Google\Cloud\Datastore\DatastoreClient;

//get user
$user = UserService::getCurrentUser();
//use and build data store service
$projID= "cc-2019-lab4";
$datastore = new DatastoreClient(['projectId' => $projID]);

// hardcoded data:
$search_data = "Richmond Station";
echo "search data: $search_data".'<br>';
//account login and log out
if (isset($user)) {
	echo sprintf('Welcome, %s! (<a href="%s">sign out</a>) <br>',
		$user->getNickname(),
		UserService::createLogoutUrl('/'));
		// favorite button
		echo 'favorite button:';
		echo "<form metnod ='POST'><input type = 'submit' name = '$search_data' value = '$search_data' />";
		if (isset($_POST[$search_data])){
			add_fav($datastore, $search_data,$user);
		}
}
	else {
	echo sprintf('<a href="%s">Sign in or register</a>',
		UserService::createLoginUrl('/'));
}

/*
upload data to data store
*/
function add_fav(DatastoreClient $datastore, $search_data,$user)
{
$key = $datastore->key('station');
$task_favorite = $datastore ->entity($key,['user'=>$user,'station' =>$search_data]);
$datastore->insert($task_favorite);
return $task_favorite;
} 
?>