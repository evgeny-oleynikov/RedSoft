<?php
// Database installation
ini_set("include_path", './'.PATH_SEPARATOR.$_SERVER['DOCUMENT_ROOT'].PATH_SEPARATOR.dirname(__FILE__)."/");
require_once("lib/rSini.php");
$request = json_decode(file_get_contents('php://input'), true);
//$request = json_decode($request, true);
$api = new rSapi();
if (is_array($request)) foreach ($request as $method => $arg)
{
 if (!method_exists($api, $method))
 {
  echo json_encode(array('result' => false, 'error' => 'Method doesn\'t exists'));
  break;
 }
 echo json_encode(array('result' => $api->$method($arg)), JSON_UNESCAPED_UNICODE);
 break;
} else
{
 echo json_encode(array('result' => false, 'error' => 'Bad request'));
}
?>