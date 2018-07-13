<?php

class rSsql extends rSsingleton
{
 public static $instance = NULL;
 public static $db = false;
 public static $errorMessage = '';
 public static $id = false;
 private static $transaction = false;
 public static $conf = NULL;
 public static $lastSQL = '';

 public function __construct($args)
 {
  self::$instance = $this;
  if (!self::$conf)
  {
   include_once "lib/config.php";
   if (isset($conf))
   {
    self::$conf = $conf;
   }
  }
  if (!self::$db && is_array($args) && count($args) == 3)
  {
   $dsn = array_shift($args);
   $user = array_shift($args);
   $passwd = array_shift($args);
  } else
  {
   $dsn = self::$conf['dbdsn'];
   $user = self::$conf['dbuser'];
   $passwd = self::$conf['dbpassword'];
  }
  self::$db = false;
  if (!is_array($dsn)) $dsn = array($dsn);
  foreach ($dsn as $dsni)
  {
   try
   {
    self::$db = new PDO($dsni, $user, $passwd);
    self::$errorMessage = '';
    break;
   } catch (PDOException $e)
   {
    self::$errorMessage = 'Connection failed: ' . $e->getMessage();
   }
  }
  if (isset(self::$conf['dbcharset']) && self::$conf['dbcharset'])
  {
   sql_query("SET character_set_client=".self::$conf['dbcharset']);
   sql_query("SET character_set_connection=".self::$conf['dbcharset']);
   sql_query("SET character_set_results=".self::$conf['dbcharset']);
  }
  if (isset(self::$conf['tz']))
  {
   date_default_timezone_set(self::$conf['tz']);
  } else
  {
   date_default_timezone_set('UTC');
  }
  if (isset(self::$conf['locale']))
  {
   setlocale(LC_ALL, self::$conf['locale']);
   setlocale(LC_TIME, self::$conf['locale']);
  }
 }

 public static function query()
 {
  $args = func_get_args();
  if (count($args) == 1 && is_array($args[0]))
  {
   $args = $args[0];
  }
  if (!self::$db)
  {
   self::$errorMessage = 'Database not opened';
   return false;
  }
  if (count($args) < 1)
  {
   self::$errorMessage = 'Empty query';
   return false;
  }
  $query = array_shift($args);
  if (count($args))
  {
   foreach ($args as $k => $v)
   {
    $args[$k] = rSsql::quote($v);
   }
   $query = vsprintf($query, $args);
  }
  self::$id = false;
  self::$lastSQL = $query;
  if (($result = self::$db->query($query)))
  {
   self::$id = self::$db->lastInsertId();
   self::$errorMessage = '';
  } else
  {
   self::setError();
  }
  return $result;
 }

 public static function setError()
 {
  $e = self::$db->errorInfo();
  if (!isset($e[2]))
  {
   self::$errorMessage = 'Database connection error';
  } else
  {
   self::$errorMessage = $e[2];
  }
 }

 public static function fetch()
 {
  $args = func_get_args();
  if (count($args) == 1 && is_array($args[0]))
  {
   $args = $args[0];
  }
  $result = self::query($args);
  if ($result)
  {
   $row = $result->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT);
   $result->closeCursor();
   if (isset($row[0]))
   {
    return $row[0];
   }
  }
  return false;
 }

 public static function fetchHash()
 {
  $args = func_get_args();
  if (count($args) == 1 && is_array($args[0]))
  {
   $args = $args[0];
  }
  $result = self::query($args);
  if ($result)
  {
   $row = $result->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT);
   $result->closeCursor();
   if (isset($row))
   {
    return $row;
   }
  }
  return false;
 }

 public static function row($result, $mode = PDO::FETCH_NUM)
 {
  if (!is_object($result) || get_class($result) != 'PDOStatement')
  {
   self::$errorMessage = 'Unvalid argument';
   return false;
  }
  $row = $result->fetch($mode, PDO::FETCH_ORI_NEXT);
  if (!$row)
  {
   $result->closeCursor();
  }
  return $row;
 }

 public static function rowHash($result)
 {
  return self::row($result, PDO::FETCH_ASSOC);
 }

 public static function rowCount($result)
 {
  if (!is_object($result) || get_class($result) != 'PDOStatement')
  {
   self::$errorMessage('Unvalid argument');
   return false;
  }
  return $result->rowCount();
 }

 public static function quote($str)
 {
  if (is_array($str)) $str = array_shift($str);
  return addslashes($str);
 }

 public static function begin()
 {
  if (!self::$db)
  {
   self::$errorMessage = 'Database not opened';
   return false;
  }
  if (self::$transaction)
  {
   self::$errorMessage = 'Has active transaction';
   return false;
  }
  $res = self::$db->beginTransaction();
  if (!$res)
  {
   self::setError();
   return false;
  }
  self::$transaction = true;
  return true;
 }

 public static function commit()
 {
  if (!self::$transaction)
  {
   self::$errorMessage = 'Has not active transaction';
   return false;
  }
  $res = self::$db->commit();
  self::$transaction = false;
  if (!$res)
  {
   self::setError();
   return false;
  }
  return true;
 }

 public static function rollback()
 {
  if (!self::$transaction)
  {
   self::$errorMessage = 'Has not active transaction';
   return false;
  }
  $res = self::$db->rollBack();
  self::$transaction = false;
  if (!$res)
  {
   self::setError();
   return false;
  }
  return true;
 }

 public static function id()
 {
  return self::$id;
 }

 public static function error()
 {
  return self::$errorMessage;
 }
}

function sql_query()
{
 return rSsql::query(func_get_args());
}

function sql_fetch()
{
 return rSsql::fetch(func_get_args());
}

function sql_fetch_array()
{
 $res = rSsql::fetchHash(func_get_args());
 $v = false;
 if (is_array($res))
 {
  $v = array();
  foreach ($res as $v1)
  {
   $v[] = $v1;
  }
 }
 return $v;
}

function sql_fetch_hash()
{
 return rSsql::fetchHash(func_get_args());
}

function sql_row($arg)
{
 return rSsql::row($arg);
}

function sql_row_hash($arg)
{
 return rSsql::rowHash($arg);
}

function sql_num_rows($arg)
{
 return rSsql::rowCount($arg);
}

function sql_escape($arg)
{
 return rSsql::quote($arg);
}

function sql_insert_id()
{
 return rSsql::id();
}

function sql_begin()
{
 return rSsql::begin();
}

function sql_commit()
{
 return rSsql::commit();
}

function sql_cancel()
{
 return rSsql::cancel();
}

function sql_error()
{
 return rSsql::error();
}
?>
