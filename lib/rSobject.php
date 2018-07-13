<?php

// $Id: rSobject.inc,v 1.0 2016/08/05 09:51:28 evg Exp $

define('OK'   ,0);
define('ERROR',1);

class rSobject
{
 // Constructor
 public function __construct()
 {
  $args = func_get_args();
  for ($i = 0; $i < count($args)-1; $i += 2)
  {
   $this->{$args[$i]} = $args[$i+1];
  }
 }

 // Add property
 public function add()
 {
  $args = func_get_args();
  for ($i = 0; $i < count($args)-1; $i += 2)
  {
   $this->$args[$i] = $args[$i+1];
  }
 }
}
?>