<?php
include('nav.inc');
include('head.inc');
?>
<body>
    <title>Train tracker</title>
    <main>
      <div class="main_box">

<?php
include('Api_Test.php');

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

for ($i=0;$i<sizeof($destination_list);$i++)
{
    $destination_name =$destination_list[$i];
    echo '<div class="platform_box">';
    echo '<div class="platform_number">'.$departure_platform[$i].'</div>';
    echo '<h2 class="destination">'.$destination_name.'</h2>';
    echo '<div style="display:block;margin:auto;padding:10px;font-size:30px;">'.$departure_time[$i].'</div>';
    echo '<div class="stop_list">';
    foreach ($stop_list[$i] as $value)
    {
        echo "$value <br>";
    }
    echo '</div></div>';
}

?>
    </div>
    </main>
<?php
include('footer.inc');
?>
  </body>  

</html>