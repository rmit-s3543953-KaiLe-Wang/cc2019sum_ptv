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
  <ul><li><a href="index.php">HOME</a></li>
      <li><?php
	  //get user
	$user = UserService::getCurrentUser();
	// hardcoded data:
	//$search_data = "East Richmond Station";
	//echo "search data: $search_data".'<br>';
	//account login and log out
	if (isset($user)) {
	echo sprintf('Welcome, %s! (<img src= "image/SignIn.png" height="40px" width="40px"><a href="%s">sign out) </a>',
		$user->getNickname(),
		UserService::createLogoutUrl('/'));
	echo '<li><a href="history.php">history</a></li>';
	
	}
	else {
		echo sprintf('<img src= "image/SignIn.png" height="40px" width="40px"><a href="%s">Sign in or register </a>',
		UserService::createLoginUrl('/'));
	}
	  ?>
      </li></ul>
</div>
</nav>
<body>
    <title>Train tracker</title>
    <main>
      <div class="main_box">
<?php
	$i=0;
	$datastore = new DatastoreClient(['projectId' => $projID]);
	$key = $datastore->key('history',$user->getEmail());
      $task = $datastore -> entity($key);
      $query = $datastore->lookup($key);
      //suggest: put $query['station'] in SESSION array, then explode() it.
      $records=explode(",",$query['station']);
      echo "history:";
      foreach ($records as $value)
      {
		  $i++;
        echo "<br>$i".': '."$value<br>";
      }
?>
    </div>
    </main>
<?php
include('footer.inc');
?>
  </body>  

</html>