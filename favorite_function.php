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
$search_data = "Richmond Station";
echo "search data: $search_data".'<br>';
//account login and log out
if (isset($user)) {
	echo sprintf('Welcome, %s! (<a href="%s">sign out</a>) <br>',
		$user->getNickname(),
		UserService::createLogoutUrl('/'));
		//add_fav($datastore, $search_data,$user);
		if (isset($_POST["station"])){
			echo "<br> value of post:".$_POST['station'];
			add_fav($datastore, $search_data,$user);
			echo '<br> operation started';
		}
		// favorite button
		echo 'favorite button:';
		echo "<form action='/' metnod ='POST'>";
		echo '<input type="hidden" name ="station"'. "value = '$search_data' />";
		echo "<input type = 'submit'/></form>";
		//print_r($_POST['station']);
		
}
	else {
	echo sprintf('<a href="%s">Sign in or register</a>',
		UserService::createLoginUrl('/'));
}

/*
upload data to data store
*/
function add_fav(DatastoreClient $datastore, $search_data,User $user)
{
$key = $datastore->key('station');
$task_favorite = $datastore ->entity($key,['user'=>$user->getEmail(),'station' =>$search_data]);
$datastore->insert($task_favorite);
echo "task compelete";
} 
?>