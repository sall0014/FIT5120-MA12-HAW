<?php
  header('Access-Control-Allow-Origin: *');
  $location_id = $_REQUEST["locations"];
  $forecast_type = $_REQUEST["forecasts"];
  $date = $_REQUEST["startDate"];
  $url = "https://api.willyweather.com.au/v2/N2FkOTJkOWFkZDA4NWIzOTZlODhkOD/locations/".$location_id."/weather.json?forecasts=".$forecast_type."&days=1&startDate=".$date;

  if(!$url) {
    echo "You need to pass in a target URL.";
    return;
  }

  $response = "";
  
  switch (getMethod()) {
    case 'GET':
      $response = makeGetRequest($url);
      break;
    default:
      echo "This proxy only supports GET REQUESTS.";
      return;
  }

  echo $response;

  function getMethod() {
    return $_SERVER["REQUEST_METHOD"]; 
  }

  function getPostData() {
    return http_build_query($_POST);
  }

  function getPutOrDeleteData($url) {
    $data = substr(file_get_contents('php://input'), strlen($url));
    return $data;
  }

  function makePostRequest($data, $url) {
    $httpHeader = array(
    'Content-Type: application/json',
    'Content-Length: ' . strlen($data));
    
    return makePutOrPostCurl('POST', $data, true, $httpHeader, $url);
  }

  function makePutRequest($data, $url) {

    return makePutOrPostCurl('PUT', $data, true, $httpHeader, $url);
  }

  function makeDeleteRequest($url) {
    $ch = initCurl($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
  }

  function makeGetRequest($url) {
    $ch = initCurl($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
  }

  function makePutOrPostCurl($type, $data, $returnTransfer, $httpHeader, $url) {

    $ch = initCurl($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, $returnTransfer);
    
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
  }

  function initCurl($url) {
    $httpHeader = array(
    'Content-Type: application/x-www-form-urlencoded');

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeader);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.2; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/32.0.1667.0 Safari/537.36');

    return $ch;
  }


?>