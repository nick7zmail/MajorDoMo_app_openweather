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
  //онлайн виджеты
  if($this->tab=='on_widgets') {
    $widgets=scandir(DIR_TEMPLATES.$this->name."/widgets/online");
    $i=0;
    foreach($widgets as $widget) {
      if($widget=='.' || $widget=='..') continue;
      $filename=DIR_TEMPLATES.$this->name."/widgets/online/".$widget;
      $widget_name=preg_replace('/.html/', '', $widget);
      $out_arr['APIKEY']=$rec['APIKEY'];
      $out_arr['CITY_ID']=$rec['CITY_ID'];

      $p=new parser($filename, $out_arr, $this);
      $out['WIDGETS'][$i]['HTML_OF_WIDGETS']=$p->result;
      $out['WIDGETS'][$i]['HTML_OF_CODE']='<code><b>&#091#module name="app_openweather" vid="'.$id.'" widget="'.  $widget_name.'"#&#093 </b></code><br /><br />';
      if($widget_name=="online.widget12" || $widget_name=="online.widget22") {
        $out['WIDGETS'][$i]['HTML_OF_CODE']='<br />'.$out['WIDGETS'][$i]['HTML_OF_CODE'];
      }
      $i++;
    }
  }
  //оффлайн виджеты
  if($this->tab=='of_widgets') {
    $widgets=scandir(DIR_TEMPLATES.$this->name."/widgets/");
    $i=0;
    foreach($widgets as $widget) {
      if(stripos($rec['APIKEY_METHOD'], 'fact')!==false) {

        if($widget=='.' || $widget=='..' || $widget=='online' || stripos($widget, 'fact')===false) continue;
        $filename=DIR_TEMPLATES.$this->name."/widgets/".$widget;
        $widget_name=preg_replace('/.html/', '', $widget);

        $out_arr['OBJ']=$rec['LINKED_OBJECT'];
        $out_arr['ICON']=gg($rec['LINKED_OBJECT'].'.image');
        $out_arr['W_NAME']=$rec['TITLE'];
      } elseif(stripos($rec['APIKEY_METHOD'], 'forecast')!==false) {

        if($widget=='.' || $widget=='..' || $widget=='online' || stripos($widget, 'forecast')===false) continue;
        $filename=DIR_TEMPLATES.$this->name."/widgets/".$widget;
        $widget_name=preg_replace('/.html/', '', $widget);
        $out_arr['W_NAME']=$rec['TITLE'];
        for($j=0; $j<=6; $j++) {
          $obj=$rec['LINKED_OBJECT'];
          if($j) $obj=$rec['LINKED_OBJECT'].'_'.$j;
          $out_arr['FORECAST'][$j]['WEEK_DAY']=date('D', strtotime(preg_replace(array('/\((\d+):(\d+)\)/i'), '', gg($obj.'.date'))));
          $out_arr['FORECAST'][$j]['OBJ']=$obj;
          $out_arr['FORECAST'][$j]['ICON']=gg($obj.'.image');
        }
      }


      $p=new parser($filename, $out_arr, $this);
      $result=$p->result;

      //замена $%obj.prop на значения
      if (preg_match_all('/%(\w{2,}?)\.(\w{2,}?)%/isu', $result, $m)) {
         $total = count($m[0]);
         for ($q = 0; $q < $total; $q++) {
            $result = str_replace($m[0][$q], getGlobal($m[1][$q] . '.' . $m[2][$q]), $result);
         }
      }

      $out['WIDGETS'][$i]['HTML_OF_WIDGETS']=$result;
      $out['WIDGETS'][$i]['HTML_OF_CODE']='<code><b>&#091#module name="app_openweather" vid="'.$id.'" widget="'.  $widget_name.'"#&#093 </b></code><br /><br />';
      $i++;
    }
  }

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
   global $main_city;
   if(!isset($main_city)) $main_city=0; else $main_city=1;
   if($rec['MAIN_CITY']!=$main_city) {
     $rec['MAIN_CITY']=$main_city;
     sg('ThisComputer.lat', $rec['CITY_LAT']);
     sg('ThisComputer.lon', $rec['CITY_LON']);
     $sqlmain=SQLSelectOne("SELECT * FROM $table_name WHERE MAIN_CITY='1'");
     $sqlmain['MAIN_CITY']=0;
     SqlUpdate($table_name, $sqlmain);
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
    if($rec['APIKEY_METHOD']=='fact' || $rec['APIKEY_METHOD']=='fact_one') {
      if(!$rec['LINKED_OBJECT']) {
        $rec['LINKED_OBJECT']='ow_fact_'.$rec['ID'];
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
    if($rec['APIKEY_METHOD']=='forecast_one'){
      if(!$rec['LINKED_OBJECT']) {
        $rec['LINKED_OBJECT']='ow_forecast_'.$rec['ID'];
        addClassObject('ow_forecast', $rec['LINKED_OBJECT']);
        SQLUpdate($table_name, $rec);
        $ow_forecast_interval=7;
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
