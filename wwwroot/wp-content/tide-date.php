<?php
require_once('../wp-load.php');
date_default_timezone_set("Australia/Melbourne");
//get date from the datepicker form
$request_date = $_POST['tide-date'];
    if (empty($request_date)) {
        $request_date = date("Y-m-d");
    } 
global $wpdb;
//call data from database
$result = $wpdb->get_results( "SELECT location_name, tide_time, round(tide_height,2) AS tide_height FROM `tide_rockshelves` WHERE tide_date = '$request_date' AND tide_height = (SELECT min(tide_height) FROM `tide_rockshelves` WHERE tide_date = '$request_date')");

echo "<style>";
echo "body {fontsize-20;}";

echo ".rec { padding: 8px 5px 5px 8px;  border: 0px solid #ccc; text-align: center; background-color: #b3ecff;}";
echo "</style></head>";
echo "<body>";

echo "<div class=\"rec\">";
echo "<tr> </tr>";
echo "<b>".date("l jS F Y", strtotime(strval($request_date)))."</b>". "<br>"."<br>";  

//show the data
foreach ($result as $row) {
    $tide_height = $row -> tide_height;
    echo "The lowest tide height is at: " .$tide_height. " meters"."<br>";
    if ($tide_height >= 0.5){
        echo "It is a ". "<i style='color:red;'><b>RISKY</b></i>". " day to travel to the rocky coast!";
    }else{
    echo "Location: ". "<b>".$row-> location_name."</b>"."<br>";
    //echo "When: ". "<b>".date('g:i A', strtotime(strval($row-> tide_time)))."</b>"."<br>";
    echo "When: ". "<b>".date('g:iA', strtotime(strval($row-> tide_time)))."</b>"." - ". "<b>".date('g:iA', strtotime(strval($row-> tide_time.'+2 hours')))."</b>"."<br>";
    $location = $row-> location_name;
    $link_name = preg_replace('/\s*/', '', strval($location));
    $link_name = strtolower($link_name);
    echo("<button onclick=\"location.href='https://iteration2.stayingsafeonrockshelves.tk/$link_name/'\">Explore $location</button>");
    echo "<br>";
    echo "<br>";
    }
}
echo "</table></div>";

?>