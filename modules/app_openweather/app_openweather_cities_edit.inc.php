<?php
/*
* @version 0.1 (wizard)
*/
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $this->getConfig();
  $table_name='app_openweather_cities';
  $rec=SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");
  if ($this->mode=='update') {
   $ok=1;
  //updating '<%LANG_TITLE%>' (varchar, required)
   $rec['TITLE']=gr('title');
   if ($rec['TITLE']=='') {
    $out['ERR_TITLE']=1;
    $ok=0;
   }
   $rec['COUNTRY']=gr('country');
  //updating 'CITY_NAME' (varchar)
   $rec['CITY_NAME']=gr('city_name');
  //updating 'CITY_LAT' (varchar)
   $rec['CITY_LAT']=gr('city_lat');
  //updating 'CITY_LON' (varchar)
   $rec['CITY_LON']=gr('city_lon');
  //updating 'CITY_ID' (varchar)
   $rec['CITY_ID']=gr('ow_city_id');
   if ($rec['CITY_ID']=='' || $rec['COUNTRY']=='') {
    $out['ERR_CITY_ID']=1;
    $ok=0;
   }
  //updating 'CITY_UPDATED' (varchar)
   $rec['CITY_UPDATED']=gr('city_updated');
  //updating 'APIKEY' (varchar)
   $rec['APIKEY']=gr('apikey');
  //updating 'APIKEY_METHOD' (varchar)
   $rec['APIKEY_METHOD']=gr('apikey_method');
   $rec['EXCLUDE_PRP']=gr('exclude_prp');
  //updating 'OW_ROUND' (varchar)
   $rec['OW_ROUND']=gr('ow_round');
  //updating 'OW_INTERVAL' (varchar)
   $rec['OW_INTERVAL']=gr('ow_interval');
  //updating '<%LANG_LINKED_OBJECT%>' (varchar)
   $rec['LINKED_OBJECT']=gr('linked_object');
  //updating '<%LANG_LINKED_PROPERTY%>' (varchar)
   $rec['LINKED_PROPERTY']=gr('linked_property');
  //UPDATING RECORD
   if ($ok) {
    if ($rec['ID']) {
     SQLUpdate($table_name, $rec); // update
    } else {
     $new_rec=1;
     $rec['ID']=SQLInsert($table_name, $rec); // adding new record
    }
    if($rec['APIKEY_METHOD']=='fact') {
      if(!$rec['LINKED_OBJECT']) {
        $rec['LINKED_OBJECT']='ow_fact_'+$rec['ID'];
        if($rec['ID']==1) $rec['LINKED_OBJECT']='ow_fact';
        addClassObject('ow_fact', $rec['LINKED_OBJECT']);
        SQLUpdate($table_name, $rec);
      }
    }
    if($rec['APIKEY_METHOD']=='forecast_5'){
      if(!$rec['LINKED_OBJECT']) {
        $rec['LINKED_OBJECT']='ow_forecast_'.$rec['ID'];
      }
      $class = SQLSelectOne("SELECT ID FROM classes WHERE TITLE = 'ow_forecast'");
      SQLExec("DELETE FROM pvalues WHERE object_id IN (SELECT ID FROM objects WHERE CLASS_ID='" . $class['ID'] . "' AND TITLE LIKE '".$rec['LINKED_OBJECT']."%')");
      SQLExec("DELETE FROM properties WHERE object_id IN (SELECT ID FROM objects WHERE CLASS_ID='" . $class['ID'] . "' AND TITLE LIKE '".$rec['LINKED_OBJECT']."%')");
      SQLExec("DELETE FROM objects WHERE CLASS_ID='" . $class['ID'] . "' AND TITLE LIKE '".$rec['LINKED_OBJECT']."%'");

      addClassObject('ow_forecast', $rec['LINKED_OBJECT']);
      SQLUpdate($table_name, $rec);
      $ow_forecast_interval=5;
      $ow_forecast_interval=$ow_forecast_interval*8;
      for ($i = 1; $i < $ow_forecast_interval; $i++)
			{
        $obj=$rec['LINKED_OBJECT'].'_'.$i;
        addClassObject('ow_forecast',  $obj);
			}
    }
    if($rec['APIKEY_METHOD']=='forecast_16'){
      if(!$rec['LINKED_OBJECT']) {
        $rec['LINKED_OBJECT']='ow_forecast_'.$rec['ID'];
        addClassObject('ow_forecast', $rec['LINKED_OBJECT']);
        SQLUpdate($table_name, $rec);
        $ow_forecast_interval=16;
        for ($i = 1; $i < $ow_forecast_interval; $i++)
  			{
          $obj=$rec['LINKED_OBJECT'].'_'.$i;
          addClassObject('ow_forecast',  $obj);
  			}
      }
    }
    $out['OK']=1;
   } else {
    $out['ERR']=1;
   }
  }
  $rec['module_mode']=$this->config['module_mode'];
  if (is_array($rec)) {
   foreach($rec as $k=>$v) {
    if (!is_array($v)) {
     $rec[$k]=htmlspecialchars($v);
    }
   }
  }
  outHash($rec, $out);
