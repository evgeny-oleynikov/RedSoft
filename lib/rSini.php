<?php
// initialization
if (!function_exists('rSautoload'))
{
 function rSautoload($classname)
 {
  $file = dirname(__FILE__) . '/' . $classname . '.php';
  if (is_file($file))
  {
   include_once $file;
  }
 }

 spl_autoload_register('rSautoload');
 require_once "lib/rSsingleton.php";
 rSsingleton('rSsql');
}
?>