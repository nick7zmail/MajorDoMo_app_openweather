<?php
/**
* РџРѕРіРѕРґР° РѕС‚ OpenWeatherMap
* @package project
* @author Wizard <sergejey@gmail.com>
* @copyright http://majordomo.smartliving.ru/ (c)
* @version 0.1 (wizard, 19:04:47 [Apr 20, 2020])
*/
//
//
class app_openweather extends module {
/**
* app_openweather
*
* Module class constructor
*
* @access private
*/
function __construct() {
  $this->name="app_openweather";
  $this->title="<#LANG_APP_OPENWEATHER#>";
  $this->module_category="<#LANG_SECTION_APPLICATIONS#>";
  $this->checkInstalled();
}
/**
* saveParams
*
* Saving module parameters
*
* @access public
*/
function saveParams($data=1) {
 $p=array();
 if (IsSet($this->id)) {
  $p["id"]=$this->id;
 }
 if (IsSet($this->view_mode)) {
  $p["view_mode"]=$this->view_mode;
 }
 if (IsSet($this->edit_mode)) {
  $p["edit_mode"]=$this->edit_mode;
 }
 if (IsSet($this->tab)) {
  $p["tab"]=$this->tab;
 }
 if (IsSet($this->vid)) {
  $p["vid"]=$this->vid;
 }
 if (IsSet($this->widget)) {
  $p["widget"]=$this->widget;
 }
 return parent::saveParams($p);
}
/**
* getParams
*
* Getting module parameters from query string
*
* @access public
*/
function getParams() {
  global $id;
  global $mode;
  global $view_mode;
  global $edit_mode;
  global $tab;
  global $vid;
  global $widget;
  if (isset($id)) {
   $this->id=$id;
  }
  if (isset($mode)) {
   $this->mode=$mode;
  }
  if (isset($view_mode)) {
   $this->view_mode=$view_mode;
  }
  if (isset($edit_mode)) {
   $this->edit_mode=$edit_mode;
  }
  if (isset($tab)) {
   $this->tab=$tab;
  }
  if (isset($vid)) {
   $this->vid=$vid;
  }
  if (isset($widget)) {
   $this->widget=$widget;
  }
}
/**
* Run
*
* Description
*
* @access public
*/
function run() {
 global $session;
  $out=array();
  if ($this->action=='admin') {
   $this->admin($out);
  } else {
   $this->usual($out);
  }
  if (IsSet($this->owner->action)) {
   $out['PARENT_ACTION']=$this->owner->action;
  }
  if (IsSet($this->owner->name)) {
   $out['PARENT_NAME']=$this->owner->name;
  }
  $out['VIEW_MODE']=$this->view_mode;
  $out['EDIT_MODE']=$this->edit_mode;
  $out['MODE']=$this->mode;
  $out['ACTION']=$this->action;
  $out['TAB']=$this->tab;
  $this->data=$out;
  $p=new parser(DIR_TEMPLATES.$this->name."/".$this->name.".html", $this->data, $this);
  $this->result=$p->result;
}
/**
* BackEnd
*
* Module backend
*
* @access public
*/
function admin(&$out) {
  $this->getConfig();
  $out['module_mode']=$this->config['module_mode'];
  $out['ow_round']=$this->config['ow_round'];
  $out['debug_level']=$this->config['debug_level'];
  $out['pictures_from']=$this->config['pictures_from'];
  if ($this->view_mode=='update_settings') {
    $this->config['module_mode']=gr('module_mode');
    $this->config['ow_round']=gr('ow_round');
    $this->config['debug_level']=gr('debug_level');
    $this->config['pictures_from']=gr('pictures_from');
    $this->saveConfig();
    $this->redirect("?");
  }

 if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
  $out['SET_DATASOURCE']=1;
 }
 if ($this->data_source=='app_openweather_cities' || $this->data_source=='') {
  if ($this->view_mode=='' || $this->view_mode=='search_app_openweather_cities') {
   $this->search_app_openweather_cities($out);
  }
  if ($this->view_mode=='edit_app_openweather_cities') {
   $this->edit_app_openweather_cities($out, $this->id);
  }
  if ($this->view_mode=='delete_app_openweather_cities') {
   $this->delete_app_openweather_cities($this->id);
   $this->redirect("?");
  }
 }
}
/**
* FrontEnd
*
* Module frontend
*
* @access public
*/
function usual(&$out) {
 $this->admin($out);
    if ($this->ajax) {
        global $op;
		if ($op == 'country_change') {
            header("HTTP/1.0: 200 OK\n");
            header('Content-Type: text/html; charset=utf-8');
			$result['result']='ok';
			$i=0;
			ini_set('memory_limit', '-1');
			global $cname;
      global $cityid;
			$city_data=json_decode(file_get_contents(DIR_MODULES.$this->name.'/city.list.json'), true);
			foreach($city_data as $v){
				if ( $v['country'] != $cname) continue;
				$result['cities'][$i]['name']=$v['name'];
				$result['cities'][$i]['id']=$v['id'];
        if($cityid && $cityid==$v['id']) $result['cities'][$i]['selected']=true;
				$result['cities'][$i]['lat']=$v['coord']['lat'];
				$result['cities'][$i]['lon']=$v['coord']['lon'];
				$i++;
			}
			$volume  = array_column($result['cities'], 'name');
			array_multisort($volume, SORT_ASC, $result['cities']);
			echo json_encode($result).PHP_EOL;
		}
	}
  $this->getConfig();
  $id=$this->vid;
  $table_name='app_openweather_cities';
  $rec=SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");
  if(stripos($this->widget, 'online')!==false) {
    $ptn_file=DIR_TEMPLATES.$this->name."/widgets/online/".$this->widget.'.html';
    $out_arr['APIKEY']=$rec['APIKEY'];
    $out_arr['CITY_ID']=$rec['CITY_ID'];
    $p=new parser($ptn_file, $out_arr, $this);
    $out['HTML_OUT']=$p->result;
  }
  if(stripos($this->widget, 'fact')!==false) {
    $ptn_file=DIR_TEMPLATES.$this->name."/widgets/".$this->widget.'.html';
    $out_arr['OBJ']=$rec['LINKED_OBJECT'];
    $out_arr['ICON']=gg($rec['LINKED_OBJECT'].'.image');
    $out_arr['W_NAME']=$rec['TITLE'];
    $p=new parser($ptn_file, $out_arr, $this);
    $out['HTML_OUT']=$p->result;
  }
  if(stripos($this->widget, 'forecast')!==false) {
    $ptn_file=DIR_TEMPLATES.$this->name."/widgets/".$this->widget.'.html';
    for($j=0; $j<=6; $j++) {
      $obj=$rec['LINKED_OBJECT'];
      if($j) $obj=$rec['LINKED_OBJECT'].'_'.$j;
      //$out_arr['FORECAST'][$j]['WEEK_DAY']=date('D', strtotime(preg_replace(array('/\((\d+):(\d+)\)/i'), '', gg($obj.'.date'))));
      $arr=array(1=>"Пн", 2=>"Вт", 3=>"Ср", 4=>"Чт", 5=>"Пт", 6=>"Сб", 7=>"Вс");
      $out_arr['FORECAST'][$j]['WEEK_DAY']=$arr[date('N', strtotime(preg_replace(array('/\((\d+):(\d+)\)/i'), '', gg($obj.'.date'))))];
      $out_arr['FORECAST'][$j]['OBJ']=$obj;
      $out_arr['FORECAST'][$j]['ICON']=gg($obj.'.image');
    }
    $out_arr['W_NAME']=$rec['TITLE'];
    $p=new parser($ptn_file, $out_arr, $this);
    $out['HTML_OUT']=$p->result;
  }
}
/**
* app_openweather_cities search
*
* @access public
*/
 function search_app_openweather_cities(&$out) {
  require(DIR_MODULES.$this->name.'/app_openweather_cities_search.inc.php');
 }
/**
* app_openweather_cities edit/add
*
* @access public
*/
 function edit_app_openweather_cities(&$out, $id) {
  require(DIR_MODULES.$this->name.'/app_openweather_cities_edit.inc.php');
 }
/**
* app_openweather_cities delete record
*
* @access public
*/
 function delete_app_openweather_cities($id) {
  $rec=SQLSelectOne("SELECT * FROM app_openweather_cities WHERE ID='$id'");
  // some action for related tables
  SQLExec("DELETE FROM app_openweather_cities WHERE ID='".$rec['ID']."'");
 }
 function propertySetHandle($object, $property, $value) {
   $table='app_openweather_cities';
   $properties=SQLSelect("SELECT ID FROM $table WHERE LINKED_OBJECT LIKE '".DBSafe($object)."' AND LINKED_PROPERTY LIKE '".DBSafe($property)."'");
   $total=count($properties);
   if ($total) {
    for($i=0;$i<$total;$i++) {
     //to-do
    }
   }
 }

 function processSubscription($event, $details='') {
  if ($event=='HOURLY') {
    $this->getConfig();
    $cities=SQLSelect("SELECT * FROM app_openweather_cities ORDER BY ID");
    $total=count($cities);
    //check lang
    $lang = SETTINGS_SITE_LANGUAGE;
    if ($lang == 'ua') {
      $lang = 'uk';
    }
    if ($lang == 'lv') {
      $lang = 'la';
    }

    for($i=0;$i<$total;$i++) {

        if (!isset($cities[$i]['CITY_ID'])){
          if($this->config['debug_level']>=1) debmes('[ERR] rec '.$cities[$i]['ID'].' has not CITY_ID value, cant continue', 'openweather');
          continue;
        }
        $apiKey=$cities[$i]['APIKEY'];
        $api_method=$cities[$i]['APIKEY_METHOD'];
        $unit = 'metric';
        $round=intval($this->config['ow_round']);

        if($api_method=='fact'){
          require(DIR_MODULES.$this->name.'/app_openweather_get_weather_fact.inc.php');
        } elseif($api_method=='fact_one') {
          require(DIR_MODULES.$this->name.'/app_openweather_get_weather_factone.inc.php');
        } elseif($api_method=='forecast_5') {
          require(DIR_MODULES.$this->name.'/app_openweather_get_weather_forecast5.inc.php');
        } elseif($api_method=='forecast_16') {
          require(DIR_MODULES.$this->name.'/app_openweather_get_weather_forecast16.inc.php');
        } elseif($api_method=='forecast_one') {
          require(DIR_MODULES.$this->name.'/app_openweather_get_weather_forecastone.inc.php');
        }
    }
  }
 }
/**
* Install
*
* Module installation routine
*
* @access private
*/
 function install($data='') {
  subscribeToEvent($this->name, 'HOURLY');
  //======удаляем остатки старого модуля=========
  $className = 'openweather';
  $classid = SQLSelectOne("SELECT ID FROM classes WHERE TITLE LIKE '" . DBSafe($className) . "'");
  if ($classid['ID']) {
    $obj1_rec = SQLSelectOne("SELECT ID FROM objects WHERE CLASS_ID='" . $classid['ID'] . "' AND TITLE LIKE '" . DBSafe('ow_fact') . "'");
    $obj2_rec = SQLSelectOne("SELECT ID FROM objects WHERE CLASS_ID='" . $classid['ID'] . "' AND TITLE LIKE '" . DBSafe('ow_setting') . "'");
    if($obj2_rec['ID']) {
      say('Внимание, модуль Openweather был обновлён до последней версии. К сожалению нам не удалось сохранить ваши настройки, но это компенсируется тем, что модуль стал гораздо лучше и стабильнее.',2);
      say('Пожалуйста создайте записи с местоположением в новом модуле. Напоминаем ваш API-ключ: '.gg('ow_setting.api_key').'.',2);
      say('Attention, the Openweather module has been updated to the latest version. Unfortunately, we were unable to save your settings, but the module has become much better and more stable.', 2);
      say('Please create entries in the new module. We remind you of your API key: '.gg('ow_setting.api_key').'.', 2);
      debmes('Внимание, модуль Openweather был обновлён до последней версии. К сожалению нам не удалось сохранить ваши настройки, но это компенсируется тем, что модуль стал гораздо лучше и стабильнее. Пожалуйста создайте записи с местоположением в новом модуле. Напоминаем ващ API-ключ: '.gg('ow_setting.api_key').'.');
      debmes('Attention, the Openweather module has been updated to the latest version. Unfortunately, we were unable to save your settings, but the module has become much better and more stable. Please create entries in the new module. We remind you of your API key: '.gg (' ow_setting.api_key ').'. ');
    }
    if($obj1_rec['ID'] || $obj2_rec['ID']) {
      SQLExec("delete from pvalues where property_id in (select id FROM properties where object_id in (select id from objects where class_id = (select id from classes where title = 'openweather')))");
      SQLExec("delete from properties where object_id in (select id from objects where class_id = (select id from classes where title = 'openweather'))");
      SQLExec("delete from objects where class_id = (select id from classes where title = 'openweather')");
      SQLExec("delete from classes where title = 'openweather'");
    }
  }
  //=============================================

  addClass('openweather');
  addClass('ow_fact', 'openweather');
  addClass('ow_forecast', 'openweather');
  addClassProperty('ow_fact', 'temperature', 7);
  parent::install();
 }
/**
* Uninstall
*
* Module uninstall routine
*
* @access public
*/
 function uninstall() {
  SQLExec('DROP TABLE IF EXISTS app_openweather_cities');
  unsubscribeFromEvent($this->name, 'HOURLY');
  $this->getConfig();
  if($this->config['debug_level']>=2) debmes('[DBG][uninstall] unsubscribed from event HOURLY', 'openweather');
  SQLExec("delete from pvalues where property_id in (select id FROM properties where object_id in (select id from objects where class_id = (select id from classes where title = 'openweather')))");
  SQLExec("delete from pvalues where property_id in (select id FROM properties where object_id in (select id from objects where class_id = (select id from classes where title = 'ow_fact')))");
  SQLExec("delete from pvalues where property_id in (select id FROM properties where object_id in (select id from objects where class_id = (select id from classes where title = 'ow_forecast')))");
  SQLExec("delete from properties where object_id in (select id from objects where class_id = (select id from classes where title = 'openweather'))");
  SQLExec("delete from properties where object_id in (select id from objects where class_id = (select id from classes where title = 'ow_fact'))");
  SQLExec("delete from properties where object_id in (select id from objects where class_id = (select id from classes where title = 'ow_forecast'))");
  SQLExec("delete from objects where class_id = (select id from classes where title = 'openweather') or class_id = (select id from classes where title = 'ow_fact') or class_id = (select id from classes where title = 'ow_forecast')");
  SQLExec("delete from classes where title = 'openweather' or title = 'ow_fact' or title = 'ow_forecast'");
  parent::uninstall();
 }
/**
* dbInstall
*
* Database installation routine
*
* @access private
*/
 function dbInstall($data) {
/*
app_openweather_cities -
*/
  $data = <<<EOD
 app_openweather_cities: ID int(10) unsigned NOT NULL auto_increment
 app_openweather_cities: TITLE varchar(100) NOT NULL DEFAULT ''
 app_openweather_cities: COUNTRY varchar(255) NOT NULL DEFAULT ''
 app_openweather_cities: CITY_NAME varchar(255) NOT NULL DEFAULT ''
 app_openweather_cities: CITY_LAT varchar(255) NOT NULL DEFAULT ''
 app_openweather_cities: CITY_LON varchar(255) NOT NULL DEFAULT ''
 app_openweather_cities: CITY_ID varchar(255) NOT NULL DEFAULT ''
 app_openweather_cities: CITY_UPDATED varchar(255) NOT NULL DEFAULT ''
 app_openweather_cities: APIKEY varchar(255) NOT NULL DEFAULT ''
 app_openweather_cities: APIKEY_METHOD varchar(255) NOT NULL DEFAULT ''
 app_openweather_cities: OW_ROUND varchar(255) NOT NULL DEFAULT ''
 app_openweather_cities: OW_INTERVAL varchar(255) NOT NULL DEFAULT ''
 app_openweather_cities: OW_FORECAST_DAYS varchar(255) NOT NULL DEFAULT ''
 app_openweather_cities: MAIN_CITY varchar(10) NOT NULL DEFAULT ''
 app_openweather_cities: EXCLUDE_PRP varchar(255) NOT NULL DEFAULT ''
 app_openweather_cities: LINKED_OBJECT varchar(100) NOT NULL DEFAULT ''
 app_openweather_cities: LINKED_PROPERTY varchar(100) NOT NULL DEFAULT ''
EOD;
  parent::dbInstall($data);
 }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgQXByIDIwLCAyMDIwIHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
