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
$projID= "weighty-arcadia-228004";
$datastore = new DatastoreClient(['projectId' => $projID]);

// hardcoded data:
// note that the history function should be triggered by "search" action.
$search_data = "East Richmond Station";
echo "search data: $search_data".'<br>';

  if (isset($user)) {
  	echo sprintf('Welcome, %s! (<a href="%s">sign out</a>) <br>',
  		$user->getNickname(),
  		UserService::createLogoutUrl('/'));
      $key = $datastore->key('history',$user->getEmail());
      $task = $datastore -> entity($key);
      $query = $datastore->lookup($key);
      //suggest: put $query['station'] in SESSION array, then explode() it.
      $records=explode(",",$query['station']);
      echo "history:";
      foreach ($records as $value)
      {
        echo "<br>$value<br>";
      }
      $numberOfRecords=count($records);
      //if there is no recording, then insert one new entity.
      if ($numberOfRecords==0||($numberOfRecords==1&&$query==null))
      {
        $task['station']=$search_data;
        $datastore->insert($task);
      }
      // else, update new record under previous entity.
      else{
        $transaction = $datastore->transaction();
        echo "<br>update function<br>";
        $update_string=$query['station'].','.$search_data;
        $task['station']=$update_string;
        $transaction->update($task,array('allowOverwrite'=>true));
        $transaction->commit();
      }
      //echo count($query->get()).'<br>';
  }
  	else {
  	echo sprintf('<a href="%s">Sign in or register</a>',
  		UserService::createLoginUrl('/'));
  }

/*
function: searchForStation
@param: DatastoreClient datastore - the datastore we have created.
		$search_data - the input data comes from search bar.
		User $user - the object of logged in user
@return: null = data not exsist.
		 $result - the result of the query
*/
function searchForStation(DatastoreClient $datastore, $key)
{
	// $result = $datastore->lookup($key);
	// return $result;
  $result = $datastore->lookup($key);
  return $result;
}

?>
