<?php
// Database installation
ini_set("include_path", './'.PATH_SEPARATOR.$_SERVER['DOCUMENT_ROOT'].PATH_SEPARATOR.dirname(__FILE__)."/");
require_once("lib/rSini.php");
$c = new rScatalog();
$id1 = $c->clear('Каталог');

$id2 = $c->insert($id1, array('cn' => 'Ювелирные изделия'));
$id3 = $c->insert($id2, array('cn' => 'Кольца'));
$c->insert($id3, array('cn' => 'Золотое кольцо с аметистом','price' => '999.99','brand'=>'Курский ювелирный завод', 'stock' => 10));
$c->insert($id3, array('cn' => 'Золотое кольцо с александритом','price' => '100000.00','brand'=>'Курский ювелирный завод', 'stock' => 10));

$id3 = $c->insert($id2, array('cn' => 'Серьги'));
$c->insert($id3, array('cn' => 'Серьги с александритом','price' => '200000.00','brand'=>'Курский ювелирный завод', 'stock' => 3));

$id2 = $c->insert($id1, array('cn' => 'Одежда'));
$id3 = $c->insert($id2, array('cn' => 'Джинсы'));
$c->insert($id3, array('cn' => '501 светлые','price' => '7000.00','brand'=>'Levi\'s', 'stock' => 300));
$c->insert($id3, array('cn' => '501 темные','price' => '7000.00','brand'=>'Levi\'s', 'stock' => 500));
$c->insert($id3, array('cn' => '504 темные','price' => '8000.00','brand'=>'Levi\'s', 'stock' => 200));

$id2 = $c->insert($id1, array('cn' => 'Бытовая электронника'));

$id3 = $c->insert($id2, array('cn' => 'BD проигрыватели'));
$c->insert($id3, array('cn' => 'CXUHD Black','price' => '10000.00','brand'=>'Cambridge Audio', 'stock' => 10));
$c->insert($id3, array('cn' => 'Ultra HD UBD-M8500','price' => '25000.00','brand'=>'Samsung', 'stock' => 12));

$id3 = $c->insert($id2, array('cn' => 'Телевизоры'));
$c->insert($id3, array('cn' => 'FLTV-32B100','price' => '9600.00','brand'=>'Fusion', 'stock' => 11));
$c->insert($id3, array('cn' => '43LH570V','price' => '26400.00','brand'=>'LG', 'stock' => 22));

echo "Done.";
?>