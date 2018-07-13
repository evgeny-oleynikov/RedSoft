<?php

class rScatalog extends rSobject
{
 private $sql;

 public static $fields = array(
  'cn',
  'stock',
  'price',
  'brand',
 );

 public function __construct()
 {
 }

 public function getNodeInfo($id)
 {
  if ($id > 0)
  {
   return sql_fetch_array('SELECT cleft,cright,clevel FROM catalog WHERE id = %d', $id);
  }
  return false;
 }

 public function getElementInfo($id)
 {
  if ($id > 0)
  {
   return sql_fetch_hash("SELECT * FROM catalog WHERE id=%d", $id);
  }
  return false;
 }

 function clear($cn = '')
 {
  sql_query('TRUNCATE catalog');
  $fld_names = '';
  $fld_values= '';
  sql_query("INSERT INTO catalog (cleft,cright,clevel,cn) VALUES (1,2,0,'%s')", $cn);
  return sql_insert_id();
 }


 public function insert($id, $data)
 {
  if (!list($leftId, $rightId, $level) = $this->getNodeInfo($id)) return false;
  $f = 'cleft,cright,clevel';
  $d = ($rightId).','.($rightId+1).','.($level+1);
  foreach ($data as $k => $v)
  {
   if (!in_array($k, self::$fields)) continue;
   $f .= ',';
   $d .= ',';
   $f .= $k;
   $d .= "'".sql_escape($v)."'";
  }

  $this->sql = 'UPDATE catalog SET '
   . 'cleft=IF(cleft>'.$rightId.',cleft+2,cleft),'
   . 'cright=IF(cright>='.$rightId.',cright+2,cright)' 
   . 'WHERE cright>='.$rightId;
  sql_query($this->sql);
  sql_query("INSERT INTO catalog (".$f.") VALUES(".$d.")");
  return sql_insert_id();
 }

 function move($id, $parent_id)
 {
  if (!(list($leftId, $rightId, $level) = $this->getNodeInfo($id))) return false;
  if (!(list($leftIdP, $rightIdP, $levelP) = $this->getNodeInfo($parent_id))) return false;
  if ($id == $parent_id || $leftId == $leftIdP || ($leftIdP >= $leftId && $leftIdP <= $rightId))
   return false;
  if ($leftIdP < $leftId && $rightIdP > $rightId && $levelP < $level - 1)
  {
   $sql = 'UPDATE catalog SET '
    .'clevel=IF(clevel BETWEEN '.$leftId.' AND '.$rightId.', clevel'.sprintf('%+d', -($level-1)+$levelP).', clevel), '
    .'cright=IF(cright BETWEEN '.($rightId+1).' AND '.($rightIdP-1).', cright-'.($rightId-$leftId+1).', '
    .'IF(cleft BETWEEN '.($leftId).' AND '.($rightId).', cright+'.((($rightIdP-$rightId-$level+$levelP)/2)*2 + $level - $levelP - 1).', cright)),  '
    .'cleft=IF(cleft BETWEEN '.($rightId+1).' AND '.($rightIdP-1).', cleft-'.($rightId-$leftId+1).', ' 
    .'IF(cleft BETWEEN '.$leftId.' AND '.($rightId).', cleft+'.((($rightIdP-$rightId-$level+$levelP)/2)*2 + $level - $levelP - 1).', cleft)) '
    . 'WHERE cleft BETWEEN '.($leftIdP+1).' AND '.($rightIdP-1); 
  } elseif($leftIdP < $leftId)
  { 
   $this->sql = 'UPDATE catalog SET ' 
    . 'clevel=IF(cleft BETWEEN '.$leftId.' AND '.$rightId.', clevel'.sprintf('%+d', -($level-1)+$levelP).', clevel), ' 
    . 'cleft=IF(cleft BETWEEN '.$rightIdP.' AND '.($leftId-1).', cleft+'.($rightId-$leftId+1).', ' 
     . 'IF(cleft BETWEEN '.$leftId.' AND '.$rightId.', cleft-'.($leftId-$rightIdP).', cleft) ' 
    . '), ' 
    . 'cright=IF(cright BETWEEN '.$rightIdP.' AND '.$leftId.', cright+'.($rightId-$leftId+1).', ' 
     . 'IF(cright BETWEEN '.$leftId.' AND '.$rightId.', cright-'.($leftId-$rightIdP).', cright) ' 
    . ') ' 
    . 'WHERE cleft BETWEEN '.$leftIdP.' AND '.$rightId 
    .' OR cright BETWEEN '.$leftIdP.' AND '.$rightId; 
  } else
  { 
   $this->sql = 'UPDATE catalog SET ' 
    . 'clevel=IF(cleft BETWEEN '.$leftId.' AND '.$rightId.', clevel'.sprintf('%+d', -($level-1)+$levelP).', clevel), ' 
    . 'cleft=IF(cleft BETWEEN '.$rightId.' AND '.$rightIdP.', cleft-'.($rightId-$leftId+1).', ' 
     . 'IF(cleft BETWEEN '.$leftId.' AND '.$rightId.', cleft+'.($rightIdP-1-$rightId).', cleft)' 
    . '), ' 
    . 'cright=IF(cright BETWEEN '.($rightId+1).' AND '.($rightIdP-1).', cright-'.($rightId-$leftId+1).', ' 
     . 'IF(cright BETWEEN '.$leftId.' AND '.$rightId.', cright+'.($rightIdP-1-$rightId).', cright) ' 
    . ') ' 
    . 'WHERE cleft BETWEEN '.$leftId.' AND '.$rightIdP 
    . ' OR cright BETWEEN '.$leftId.' AND '.$rightIdP; 
  } 
  return sql_query($this->sql); 
 } 

 function delete($id)
 {
  if (!list($leftId, $rightId, $level) = $this->getNodeInfo($id)) return 0;
  if (!sql_query('DELETE FROM catalog WHERE id=\''.$id.'\'')) return false;
  $this->sql = 'UPDATE catalog SET '
   . 'cleft=IF(cleft BETWEEN '.$leftId.' AND '.$rightId.',cleft-1,cleft),'
   . 'cright=IF(cright BETWEEN '.$leftId.' AND '.$rightId.',cright-1,cright),'
   . 'clevel=IF(cleft BETWEEN '.$leftId.' AND '.$rightId.',clevel-1,clevel),'
   . 'cleft=IF(cleft>'.$rightId.',cleft-2,cleft),'
   . 'cright=IF(cright>'.$rightId.',cright-2,cright) '
   . 'WHERE cright>'.$leftId
  ;
  return sql_query($this->sql);
 }

 function deleteAll($id)
 {
  if (!list($leftId, $rightId, $level) = $this->getNodeInfo($id)) return 0;
  if (!sql_query('DELETE FROM catalog WHERE cleft BETWEEN '.$leftId.' AND '.$rightId))
   return false;
  $deltaId = ($rightId - $leftId)+1;
  $this->sql = 'UPDATE catalog SET '
   . 'cleft=IF(cleft>'.$leftId.',cleft-'.$deltaId.',cleft),'
   . 'cright=IF(cright>'.$leftId.',cright-'.$deltaId.',cright) '
   . 'WHERE cright>'.$rightId
  ;
  return sql_query($this->sql);
 }

 function enumChildrenAll($id)
 {
  $res = $this->enumChildren($id, 1, 0);
  return $res;
 }

 function enumChildren($id, $start_level = 1, $end_level = 1)
 {
  if($start_level < 0) fatal(_T('j2sql_tree: unknow start level '));

  $whereSql1 = ' AND catalog.clevel';
  $whereSql2 = '_catalog.clevel+';
  if(!$end_level)
  {
   $whereSql = $whereSql1.'>='.$whereSql2.(int)$start_level;
  } else
  {
   $whereSql = ($end_level <= $start_level) 
    ? $whereSql1.'='.$whereSql2.(int)$start_level
    : ' AND catalog.clevel BETWEEN _catalog.clevel+'.(int)$start_level
     .' AND _catalog.clevel+'.(int)$end_level;
  }

  $this->sql = $this->sqlComposeSelect(array(
   '',
   '',
   'catalog _catalog, catalog',
   '_catalog.id=\''.$id.'\''
    .' AND catalog.cleft BETWEEN _catalog.cleft AND _catalog.cright'
    .$whereSql,
   '',
   '',
   '',
   ''
  ));
  return sql_query($this->sql);
 }

 function getFromCatalog($c, $r = false)
 {
  $res = array();
  $info = $this->getElementInfo($c);
  if (!$info)
  {
   $c = sql_fetch("SELECT id FROM catalog WHERE cn='%s'", $c);
   if (!$c) return $res;
   $info = $this->getElementInfo($c);
   if (!$info) return $res;
  }
  if (!$r)
  {
   $result = $this->enumChildren($info['id']);
  } else
  {
   $result = $this->enumChildrenAll($info['id']);
  }
  while ($row = sql_row_hash($result))
  {
   $res[$row['id']] = $row;
  }
  return $res;
 }

 function getItemsFromCatalog($c, $r = false)
 {
  $res = array();
  $info = $this->getElementInfo($c);
  if (!$info)
  {
   $c = sql_fetch("SELECT id FROM catalog WHERE cn='%s'", $c);
   if (!$c) return $res;
   $info = $this->getElementInfo($c);
   if (!$info) return $res;
  }
  if (!$r)
  {
   $result = $this->enumChildren($info['id']);
  } else
  {
   $result = $this->enumChildrenAll($info['id']);
  }
  while ($row = sql_row_hash($result))
  {
   if ($row['cleft'] + 1 != $row['cright']) continue;
   $res[$row['id']] = $row;
  }
  return $res;
 }

 function searchCN($cn)
 {
  $result = sql_query("SELECT DISTINCT * FROM catalog WHERE cn LIKE '%%%s%%'", $cn);
  $res = array();
  while ($row = sql_row_hash($result))
  {
   $res[$row['id']] = $row;
  }
  return $res;
 }

 function searchBrand($brand)
 {
  $b = '';
  if (is_array($brand) && count($brand))
  {
   foreach ($brand as $v)
   {
    if ($b) $b .= ',';
    $b .= "'".sql_escape($v)."'";
   }
   if ($b) $b = "brand IN ($b)";
  } else if (is_scalar($brand) && $brand)
  {
   $b = "brand='".sql_escape($brand)."'";
  }
  $res = array();
  if ($b)
  {
   $result = sql_query("SELECT DISTINCT * FROM catalog WHERE $b");
   while ($row = sql_row_hash($result))
   {
    $res[$row['id']] = $row;
   }
  }
  return $res;
 }

 function enumPath($id, $showRoot=false)
 {
  $this->sql = $this->sqlComposeSelect(array(
   '',
   '',
   'catalog _catalog, catalog',
   '_catalog.id=\''.$id.'\''
    .' AND _catalog.cleft BETWEEN catalog.cleft AND catalog.cright'
    .(($showRoot) ? '' : ' AND catalog.clevel>0'),
   '',
   '',
   'catalog.cleft'
  ));
  return sql_query($this->sql);
 }

 function getParent($id, $level=1)
 {
  if($level < 1) return 0;

  $this->sql = $this->sqlComposeSelect(array(
   '',
   '',
   'catalog _catalog, catalog',
   '_catalog.id=\''.$id.'\''
    .' AND _catalog.cleft BETWEEN catalog.cleft AND catalog.cright'
    .' AND catalog.clevel=_catalog.clevel-'.(int)$level,
   '',
   '',
   ''
  ));
  return sql_query($this->sql);
 }

 function getParentID($id, $level=1)
 {
  if($level < 1) return 0;
  if (!$this->qryFields)  $this->qryFields = "catalog.id";
  $this->sql = $this->sqlComposeSelect(array(
   '',
   '',
   'catalog _catalog, catalog',
   '_catalog.id=\''.$id.'\''
    .' AND _catalog.cleft BETWEEN catalog.cleft AND catalog.cright'
    .' AND catalog.clevel=_catalog.clevel-'.(int)$level,
   '',
   '',
   ''
  ));
  return sql_fetch($this->sql);
 }

 function total()
 {
  return sql_fetch("SELECT count(*) FROM catalog WHERE id!=1");
 }

 function sqlComposeSelect($arSql)
 {
  if (!strstr($arSql[0], "DISTINCT") && !@strstr($this->qryFields, "DISTINCT"))
  {
   $arSql[0] = "DISTINCT " . $arSql[0];
  }
  $joinTypes = array('join'=>1, 'cross'=>1, 'inner'=>1, 'straight'=>1, 'left'=>1, 'natural'=>1, 'right'=>1);
  $this->sql = 'SELECT '.$arSql[0].' ';
  if(!empty($this->qryParams)) $this->sql .= $this->sqlParams.' ';

  if(empty($arSql[1]) && empty($this->qryFields))
  {
   $this->sql .= "catalog.*";
  } else
  {
   if(!empty($arSql[1])) $this->sql .= $arSql[1];
   if(!empty($this->qryFields)) $this->sql .= ((empty($arSql[1])) ? '' : ',') . $this->qryFields;
  }
  $this->sql .= ' FROM ';

  $isJoin = '';
  if (!empty($this->qryTables))
  {
   $isJoin = ($tblAr=explode(' ',trim($this->qryTables))) && (@$joinTypes[strtolower($tblAr[0])]);
  }
  if(empty($arSql[2]) && empty($this->qryTables))
  {
   $this->sql .= 'catalog';
  } else
  {
   if(!empty($arSql[2])) $this->sql .= $arSql[2];
   if(!empty($this->qryTables))
   {
    if(!empty($arSql[2])) $this->sql .= (($isJoin)?' ':',');
    elseif($isJoin) $this->sql .= 'catalog ';
    $this->sql .= $this->qryTables;
   }
  }
  if(!empty($this->qryJoin))
  {
   $this->sql .= ' ' .$this->qryJoin;
  }
  if((!empty($arSql[3])) || (!empty($this->qryWhere)))
  {
   $this->sql .= ' WHERE ' . $arSql[3] . ' ';
   if(!empty($this->qryWhere)) $this->sql .= (empty($arSql[3])) ? $this->qryWhere : 'AND('.$this->qryWhere.')';
  }
  if((!empty($arSql[4])) || (!empty($this->qryGroupBy)))
  {
   $this->sql .= ' GROUP BY ' . $arSql[4] . ' ';
   if(!empty($this->qryGroupBy)) $this->sql .= (empty($arSql[4])) ? $this->qryGroupBy : ','.$this->qryGroupBy;
  }
  if((!empty($arSql[5])) || (!empty($this->qryHaving)))
  {
   $this->sql .= ' HAVING ' . $arSql[5] . ' ';
   if(!empty($this->qryHaving)) $this->sql .= (empty($arSql[5])) ? $this->qryHaving : 'AND('.$this->qryHaving.')';
  }
  if((!empty($arSql[6])) || (!empty($this->qryOrderBy)))
  {
   $this->sql .= ' ORDER BY ' . $arSql[6] . ' ';
   if(!empty($this->qryOrderBy)) $this->sql .= (empty($arSql[6])) ? $this->qryOrderBy : ','.$this->qryOrderBy;
  }
  if(!empty($arSql[7])) $this->sql .= ' LIMIT '.$arSql[7];
  elseif(!empty($this->qryLimit)) $this->sql .= ' LIMIT '.$this->qryLimit;

  if($this->sqlNeedReset) $this->sqlReset();
  return $this->sql;
 }

 function sqlReset()
 {
  $this->qryParams = ''; $this->qryFields = ''; $this->qryTables = ''; 
  $this->qryWhere = ''; $this->qryGroupBy = ''; $this->qryHaving = ''; 
  $this->qryOrderBy = ''; $this->qryLimit = '';
  return true;
 }

 function sqlSetReset($resetMode) { $this->sqlNeedReset = ($resetMode) ? true : false; }

 function sqlParams($param='')  { return (empty($param)) ? $this->qryParams : $this->qryParams = $param; }
 function sqlFields($param='')  { return (empty($param)) ? $this->qryFields : $this->qryFields = $param; }
 function sqlSelect($param='')  { return $this->sqlFields($param); }
 function sqlTables($param='')  { return (empty($param)) ? $this->qryTables : $this->qryTables = $param; }
 function sqlFrom($param='')    { return $this->sqlTables($param); }
 function sqlJoin($param='')    { return (empty($param)) ? $this->qryJoin : $this->qryJoin = $param; }
 function sqlWhere($param='')   { return (empty($param)) ? $this->qryWhere : $this->qryWhere = $param; }
 function sqlGroupBy($param='') { return (empty($param)) ? $this->qryGroupBy : $this->qryGroupBy = $param; }
 function sqlHaving($param='')  { return (empty($param)) ? $this->qryHaving : $this->qryHaving = $param; }
 function sqlOrderBy($param='') { return (empty($param)) ? $this->qryOrderBy : $this->qryOrderBy = $param; }
 function sqlLimit($param='')   { return (empty($param)) ? $this->qryLimit : $this->qryLimit = $param; }
}
?>