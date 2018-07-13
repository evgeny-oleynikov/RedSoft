<?php
// Database installation
ini_set("include_path", './'.PATH_SEPARATOR.$_SERVER['DOCUMENT_ROOT'].PATH_SEPARATOR.dirname(__FILE__)."/");
require_once("lib/rSini.php");
if (!sql_fetch("SHOW TABLES LIKE 'version'"))
{
 sql_query("CREATE TABLE version (
   module varchar(64) NOT NULL DEFAULT '',
   version int(11) NOT NULL DEFAULT '0',
   PRIMARY KEY (module)
  )
 ");
}
if (isset($_SERVER['HTTP_HOST'])) echo "<pre>";
echo "*** install/upgrade script\n";
if ($d = opendir(dirname(__FILE__)."/install"))
{
 while ($file = readdir($d))
 {
  if (substr($file, -4) != '.php') continue;
  $module = substr($file, 0, -4);
  include_once 'install/'.$file;
  $class = "iu_$module";
  if (!class_exists($class)) continue;
  echo "$class... ";
  $v_old = (int)sql_fetch("SELECT version FROM version WHERE module='%s'", $module);
  $u = new $class();
  $v_new = $u->version;
  $return = false;
  if ($v_old > $v_new) $return = true;
  if ($v_old == $v_new) $return = true;
  if (!$return)
  {
   printf("\nupgrading from v%d to v%d", $v_old, $v_new);
   for ($i = $v_old+1; $i <= $v_new; $i++)
   {
    printf("\n    v%d to v%d... ", $i-1, $i);
    $u->{"v$i"}();
    sql_query("REPLACE INTO version (module, version) VALUES ('%s', %d)", $module, $i);
    printf("ok");
   }
   echo "\n";
  }
 }
 closedir($d);
}
echo "ok\n";
if (isset($_SERVER['HTTP_HOST']))
{
 echo "</pre>";
}
?>