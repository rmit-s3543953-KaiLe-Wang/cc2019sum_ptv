<?php
include('head.inc');
include('nav.inc');
?>
<body>
    <title>Train tracker</title>
    <main>
      <div class="main_box">
	  <div class="search-container">
	<form action="index.php">
      <input type="text" placeholder="Search.." name="search">
      <button type="submit" ><img src= "search.png"></button>
    </form>
  </div>
<?php
	//if (no search bar variable has sent)
	//then: display nothing.
	//else:
	//for i to number_of_station:
       $station_name = "Station name";
	   echo '<div class="text_box">';
       echo  "<h2 >$station_name</h2></div>";
       
?>
    </div>
    </main>
<?php
include('footer.inc');
?>
  </body>  

</html>