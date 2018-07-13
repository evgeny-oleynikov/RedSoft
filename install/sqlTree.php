<?php
class iu_sqlTree
{
 public $version = 2;

 public function v1()
 {
  sql_query("DROP TABLE IF EXISTS catalog");
  sql_query("CREATE TABLE catalog (
    id int(10) unsigned NOT NULL auto_increment,
    cleft int(10) unsigned NOT NULL default '0',
    cright int(10) unsigned NOT NULL default '0',
    clevel int(10) unsigned NOT NULL default '0',
    cn varchar(512) NOT NULL default '',
    stock int(10) unsigned NOT NULL default '0',
    price double NOT NULL default '0',
    brand varchar(512) NOT NULL default '',
    PRIMARY KEY  (id),
    KEY cleft (cleft,cright,clevel),
    KEY cn (cn),
    KEY stock (stock),
    KEY price (price),
    KEY brand (brand)
   ) DEFAULT CHARSET=utf8mb4");
 }

 public function v2()
 {
  sql_query("REPLACE INTO catalog (id,cleft,cright,clevel,cn) VALUES (1,1,2,0,'Каталог товаров')");
 }
}
?>