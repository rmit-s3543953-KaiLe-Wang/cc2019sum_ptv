<?php
include('head.inc');
include('nav.inc');
?>
<body>
    <title>Train tracker</title>
    <main>
      <div class="main_box">
	  
<?php
	//if (no search bar variable has sent)
	//then: display nothing.
	//else:
	$station_list=array("station1","station2","station3");
	for ($i=0;$i<sizeof($station_list);$i++)
	{
       $station_name =$station_list[$i];
	   echo '<div class="platform_box">';
       echo  "<h2 >$station_name</h2>";
       echo "time:";
       echo "station 1, station 2, -------, station 3</div>";
	}  
?>
    </div>
    </main>
<?php
include('footer.inc');
?>
  </body>  

</html>