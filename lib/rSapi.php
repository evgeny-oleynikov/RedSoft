<?php

class rSapi extends rSobject
{
 static $c = false;

 public function __construct()
 {
  if (!self::$c) self::$c = new rScatalog();
  parent::__construct();
 }

 public static function getItem($id)
 {
  $res = self::$c->getElementInfo($id);
  unset($res['cleft']);
  unset($res['cright']);
  unset($res['clevel']);
  return $res;
 }

 public static function searchItemsByName($cn)
 {
  $res = self::$c->searchCN($cn);
  foreach ($res as $k => $v)
  {
   unset($res[$k]['cleft']);
   unset($res[$k]['cright']);
   unset($res[$k]['clevel']);
  }
  return $res;
 }

 public static function searchItemsByBrand($brand)
 {
  $res = self::$c->searchBrand($brand);
  foreach ($res as $k => $v)
  {
   unset($res[$k]['cleft']);
   unset($res[$k]['cright']);
   unset($res[$k]['clevel']);
  }
  return $res;
 }

 public static function getChildren($node)
 {
  $res = self::$c->getFromCatalog($node);
  foreach ($res as $k => $v)
  {
   unset($res[$k]['cleft']);
   unset($res[$k]['cright']);
   unset($res[$k]['clevel']);
  }
  return $res;
 }

 public static function getChildrenItems($node)
 {
  $res = self::$c->getItemsFromCatalog($node);
  foreach ($res as $k => $v)
  {
   unset($res[$k]['cleft']);
   unset($res[$k]['cright']);
   unset($res[$k]['clevel']);
  }
  return $res;
 }

 public static function getChildrenAll($node)
 {
  $res = self::$c->getFromCatalog($node, true);
  foreach ($res as $k => $v)
  {
   unset($res[$k]['cleft']);
   unset($res[$k]['cright']);
   unset($res[$k]['clevel']);
  }
  return $res;
 }

 public static function getChildrenItemsAll($node)
 {
  $res = self::$c->getItemsFromCatalog($node, true);
  foreach ($res as $k => $v)
  {
   unset($res[$k]['cleft']);
   unset($res[$k]['cright']);
   unset($res[$k]['clevel']);
  }
  return $res;
 }
}
?>
