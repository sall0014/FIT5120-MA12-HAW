<?php
require_once('../wp-load.php');
date_default_timezone_set("Australia/Melbourne");
//get date from the datepicker form
$request_date = $_POST['tide-date'];
    if (empty($request_date)) {
        $request_date = date("Y-m-d");
    } 
global $wpdb;
//call data from the database
$result = $wpdb->get_results( "SELECT tide_time, round(min(tide_height),2) AS minHeight FROM `tide_rockshelves` WHERE tide_date = '$request_date' AND location_name = 'Tongue Point'");

echo "<style>";
echo "body {fontsize-20;}";

echo ".rec { padding: 8px 5px 5px 8px;  border: 0px solid #ccc; text-align: center; background-color: #b3ecff;}";
echo "</style></head>";
echo "<body>";

echo "<div class=\"rec\">";
echo "<tr> </tr>";
echo "<b>".date("l jS F Y", strtotime(strval($request_date)))."</b>". "<br>";  
//show the data
foreach ($result as $row) {
    $tide_height = $row-> minHeight;
    echo "The lowest tide height is at: ".$tide_height." meters"."<br>";
    if ($tide_height >= 0.5){
        echo "It is a ". "<i style='color:red;'><b>RISKY</b></i>". " day to travel to the Tongue Point!";
    }else{
    echo "When: ". "<b>".date('g:iA', strtotime(strval($row-> tide_time)))."</b>"." - ". "<b>".date('g:iA', strtotime(strval($row-> tide_time.'+2 hours')))."</b>"."<br>";
    echo "<br>";
        
    }
}
echo "</table></div>";

?>