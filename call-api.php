<?php
$url = "http://www.example.com/RedSoft/api.php";
//$content = json_encode(array('getItem' => 12));
//$content = json_encode(array('searchItemsByName' => 'Кольцо'));
//$content = json_encode(array('searchItemsByBrand' => "Levi's"));
//$content = json_encode(array('searchItemsByBrand' => array("Levi's","LG")));
//$content = json_encode(array('getChildren' => 2));
//$content = json_encode(array('getChildren' => 'Бытовая электронника'));
//$content = json_encode(array('getChildrenAll' => 2));
//$content = json_encode(array('getChildrenAll' => 'Бытовая электронника'));
//$content = json_encode(array('getChildrenItems' => 3));
//$content = json_encode(array('getChildrenItems' => 'Телевизоры'));
//$content = json_encode(array('getChildrenItemsAll' => 2));
$content = json_encode(array('getChildrenItemsAll' => 'Бытовая электронника'));

$curl = curl_init($url);
curl_setopt($curl, CURLOPT_HEADER, false);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HTTPHEADER,
        array("Content-type: application/json"));
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $content);

$json_response = curl_exec($curl);

curl_close($curl);

$response = json_decode($json_response, true);
var_dump($response);
?>