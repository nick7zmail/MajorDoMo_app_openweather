<?php
while($ret<=3) {
  $url="http://api.openweathermap.org/data/2.5/forecast/daily?id=" . $cities[$i]['CITY_ID'] . "&mode=json&units=" . $unit . "&lang=" . $lang . "&cnt=16&appid=" . $apiKey;
  if($this->config['debug_level']>=2) debmes('[DBG] --- [forecast 16] '.$url, 'openweather');
  $data =  getURL($url);
  if($this->config['debug_level']>=2) debmes('[DBG] +++ [forecast 16] '.$data, 'openweather');
  $weather = json_decode($data);
  if ($weather->cod == "200") {
    $j = 0;
    foreach($weather->list as $period) {
      $obj=$cities[$i]['LINKED_OBJECT'];
      if($j) $obj=$obj.'_'.$j;

      $date = date("d.m.Y (H:i)", $period->dt);
      sg($obj.'.date', $date);
      //temp
      if (stripos($cities[$i]['EXCLUDE_PRP'], 'temperature')===false)           sg($obj.'.temperature', round($period->temp->day, $round));
      if (stripos($cities[$i]['EXCLUDE_PRP'], 'temp_morn')===false)             sg($obj.'.temp_morn', round($period->temp->morn, $round));
      if (stripos($cities[$i]['EXCLUDE_PRP'], 'temp_day')===false)              sg($obj.'.temp_day', round($period->temp->day, $round));
      if (stripos($cities[$i]['EXCLUDE_PRP'], 'temp_eve')===false)              sg($obj.'.temp_eve', round($period->temp->eve, $round));
      if (stripos($cities[$i]['EXCLUDE_PRP'], 'temp_night')===false)            sg($obj.'.temp_night', round($period->temp->night,$round));
      if (stripos($cities[$i]['EXCLUDE_PRP'], 'temp_min')===false)              sg($obj.'.temp_min', round($period->temp->min, $round));
      if (stripos($cities[$i]['EXCLUDE_PRP'], 'temp_max')===false)              sg($obj.'.temp_max', round($period->temp->max, $round));
      //feels like
      if (stripos($cities[$i]['EXCLUDE_PRP'], 'feelslike_temp_morn')===false)   sg($obj.'.feelslike_temp_morn', round($period->feels_like->morn, $round));
      if (stripos($cities[$i]['EXCLUDE_PRP'], 'feelslike_temp_day')===false)    sg($obj.'.feelslike_temp_day', round($period->feels_like->day, $round));
      if (stripos($cities[$i]['EXCLUDE_PRP'], 'feelslike_temp_eve')===false)    sg($obj.'.feelslike_temp_eve', round($period->feels_like->eve, $round));
      if (stripos($cities[$i]['EXCLUDE_PRP'], 'feelslike_temp_night')===false)  sg($obj.'.feelslike_temp_night', round($period->feels_like->night,$round));
      //wind
      if (stripos($cities[$i]['EXCLUDE_PRP'], 'wind_direction')===false)        sg($obj.'.wind_direction', round($period->deg, $round));
      if (stripos($cities[$i]['EXCLUDE_PRP'], 'wind_direction_text')===false)   sg($obj.'.wind_direction_text', getWindDirection(round($period->deg, $round)));
      if (stripos($cities[$i]['EXCLUDE_PRP'], 'wind_direction_full')===false)   sg($obj.'.wind_direction_full', getWindDirection(round($period->deg, $round), true));
      if (stripos($cities[$i]['EXCLUDE_PRP'], 'wind_speed')===false)            sg($obj.'.wind_speed', round($period->speed, $round));
      //other
      if (stripos($cities[$i]['EXCLUDE_PRP'], 'humidity')===false &&
      $period->humidity)                                                        sg($obj.'.humidity', round($period->humidity, $round));
      if (stripos($cities[$i]['EXCLUDE_PRP'], 'pressure')===false)              sg($obj.'.pressure', round($period->pressure, $round));
      if (stripos($cities[$i]['EXCLUDE_PRP'], 'pressure_mmhg')===false)         sg($obj.'.pressure_mmhg', round(ConvertPressure($period->pressure, "hpa", "mmhg", 2), $round));
      //weather
      if (stripos($cities[$i]['EXCLUDE_PRP'], 'condCode')===false)              sg($obj.'.cond_code', $period->weather[0]->id);
      if (stripos($cities[$i]['EXCLUDE_PRP'], 'weather_type')===false)          sg($obj.'.weather_type', $period->weather[0]->description);
      if (stripos($cities[$i]['EXCLUDE_PRP'], 'image')===false)                 sg($obj.'.image', $period->weather[0]->icon);
      //clouds rain snow
      if (stripos($cities[$i]['EXCLUDE_PRP'], 'clouds')===false)                sg($obj.'.clouds', $period->clouds);
      if (stripos($cities[$i]['EXCLUDE_PRP'], 'rain')===false)                  sg($obj.'.rain', isset($period->rain) ? $period->rain : 0);
      if (stripos($cities[$i]['EXCLUDE_PRP'], 'snow')===false)                  sg($obj.'.snow', isset($period->snow) ? $period->snow : 0);

      $sunInfo = GetSunInfo($period->dt, $cities[$i]['CITY_LAT'], $cities[$i]['CITY_LON']);
      if ($sunInfo)
      {
       if (stripos($cities[$i]['EXCLUDE_PRP'], 'sunrise')===false)              sg($obj.'.sunrise', date("H:i", $sunInfo["sunrise"]));
       if (stripos($cities[$i]['EXCLUDE_PRP'], 'sunset')===false)               sg($obj.'.sunset',  date("H:i", $sunInfo["sunset"]));
       if (stripos($cities[$i]['EXCLUDE_PRP'], 'day_length')===false)           sg($obj.'.day_length', gmdate("H:i", $sunInfo["sunset"]-$sunInfo["sunrise"]));
       if (stripos($cities[$i]['EXCLUDE_PRP'], 'civil_twilight_begin')===false) sg($obj.'.civil_twilight_begin', date("H:i", $sunInfo["civil_twilight_begin"]));
       if (stripos($cities[$i]['EXCLUDE_PRP'], 'civil_twilight_end')===false)   sg($obj.'.civil_twilight_end', date("H:i", $sunInfo["civil_twilight_end"]));
      }
      $j++;
    }
    if ($cities[$i]['MAIN_CITY']) {
      if($this->config['debug_level']>=1) debmes('[ERR] [settings]: main city cant be "forecast" api type. Change main city settings on "fact" api type.', 'openweather');
    }
    break;
  } else {
    if($this->config['debug_level']>=1) debmes('[ERR] '.$weather->cod.': '.$weather->message, 'openweather');
    return false;
  }
  $ret++;
}
