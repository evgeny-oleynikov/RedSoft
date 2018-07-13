<?php

function & rSsingleton($class)
{
 $res = NULL;
 $args = func_get_args();
 if (!count($args)) return $res;
 $class = array_shift($args);
 if (class_exists($class))
 {
  if (!$class::$instance)
  {
   new $class($args);
  }
  return $class::$instance;
 }
 return $res;
}

class rSsingleton extends rSobject
{
 public static $instance = NULL;

 public function __construct()
 {
  self::$instance = $this;
 }
}
?>