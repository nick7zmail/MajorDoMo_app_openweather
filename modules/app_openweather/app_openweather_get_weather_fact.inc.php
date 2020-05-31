<?php
while($ret<=3) {
  $url = "http://api.openweathermap.org/data/2.5/weather?id=" . $cities[$i]['CITY_ID'] . "&mode=json&units=" . $unit . "&lang=" . $lang . "&appid=" . $apiKey;
  if($this->config['debug_level']>=2) debmes('[DBG] --- [fact] '.$url, 'openweather');
  $data =  getURL($url);
  if($this->config['debug_level']>=2) debmes('[DBG] +++ [fact] '.$data, 'openweather');
  $curWeather = json_decode($data);
  if ($curWeather->cod == "200") {
    $obj=$cities[$i]['LINKED_OBJECT'];
    $date = date("d.m.Y G:i:s T Y", $curWeather->dt);
    //main
    if (stripos($cities[$i]['EXCLUDE_PRP'], 'temperature')===false)         sg($obj.'.temperature', round($curWeather->main->temp, $round));
    if (stripos($cities[$i]['EXCLUDE_PRP'], 'temp_feels_like')===false)     sg($obj.'.temp_feels_like', round($curWeather->main->feels_like, $round));
    if (stripos($cities[$i]['EXCLUDE_PRP'], 'temp_min')===false)            sg($obj.'.temp_min', round($curWeather->main->temp_min, $round));
    if (stripos($cities[$i]['EXCLUDE_PRP'], 'temp_max')===false)            sg($obj.'.temp_max', round($curWeather->main->temp_max, $round));
    if (stripos($cities[$i]['EXCLUDE_PRP'], 'pressure')===false)            sg($obj.'.pressure', round($curWeather->main->pressure, $round));
    if (stripos($cities[$i]['EXCLUDE_PRP'], 'pressure_mmhg')===false)       sg($obj.'.pressure_mmhg', round(ConvertPressure($curWeather->main->pressure, "hpa", "mmhg", 2), $round));
    if (stripos($cities[$i]['EXCLUDE_PRP'], 'humidity')===false)            sg($obj.'.humidity', round($curWeather->main->humidity, $round));
    //wind
    if (stripos($cities[$i]['EXCLUDE_PRP'], 'wind_direction')===false)      sg($obj.'.wind_direction', round($curWeather->wind->deg, $round));
    if (stripos($cities[$i]['EXCLUDE_PRP'], 'wind_direction_text')===false) sg($obj.'.wind_direction_text', getWindDirection(round($curWeather->wind->deg, $round)));
    if (stripos($cities[$i]['EXCLUDE_PRP'], 'wind_direction_full')===false) sg($obj.'.wind_direction_full', getWindDirection(round($curWeather->wind->deg, $round), true));
    if (stripos($cities[$i]['EXCLUDE_PRP'], 'wind_speed')===false)          sg($obj.'.wind_speed', round($curWeather->wind->speed, $round));
    //weather
    if (stripos($cities[$i]['EXCLUDE_PRP'], 'condCode')===false)            sg($obj.'.condCode', $curWeather->weather[0]->id);
    if (stripos($cities[$i]['EXCLUDE_PRP'], 'weather_type')===false)        sg($obj.'.weather_type', $curWeather->weather[0]->description);
    if (stripos($cities[$i]['EXCLUDE_PRP'], 'image')===false)               sg($obj.'.image', $curWeather->weather[0]->icon);
    //clouds, snow, rain
    if (stripos($cities[$i]['EXCLUDE_PRP'], 'clouds')===false)              sg($obj.'.clouds', $curWeather->clouds->all);
    if (stripos($cities[$i]['EXCLUDE_PRP'], 'rain1h')===false)              sg($obj.'.rain1h', isset($curWeather->rain->{'1h'}) ? $curWeather->rain->{'1h'} : '');
    if (stripos($cities[$i]['EXCLUDE_PRP'], 'rain3h')===false)              sg($obj.'.rain3h', isset($curWeather->rain->{'3h'}) ? $curWeather->rain->{'3h'} : '');
    if (stripos($cities[$i]['EXCLUDE_PRP'], 'snow1h')===false)              sg($obj.'.snow1h', isset($curWeather->snow->{'1h'}) ? $curWeather->snow->{'1h'} : '');
    if (stripos($cities[$i]['EXCLUDE_PRP'], 'snow3h')===false)              sg($obj.'.snow3h', isset($curWeather->snow->{'3h'}) ? $curWeather->snow->{'3h'} : '');
    if (stripos($cities[$i]['EXCLUDE_PRP'], 'data_update')===false)         sg($obj.'.data_update', $date);
    //system
    //if (stripos($cities[$i]['EXCLUDE_PRP'], 'sunrise')===false)             sg($obj.'.sunrise', $curWeather->sys->sunrise);
    //if (stripos($cities[$i]['EXCLUDE_PRP'], 'sunset')===false)              sg($obj.'.sunrise', $curWeather->sys->sunset);
    //sun info
    $sunInfo = GetSunInfo(time(), $cities[$i]['CITY_LAT'], $cities[$i]['CITY_LON']);
    if ($sunInfo)
    {
     if (stripos($cities[$i]['EXCLUDE_PRP'], 'sunrise')===false)            sg($obj.'.sunrise', date("H:i", $sunInfo["sunrise"]));
     if (stripos($cities[$i]['EXCLUDE_PRP'], 'sunset')===false)             sg($obj.'.sunset', date("H:i", $sunInfo["sunset"]));
     if (stripos($cities[$i]['EXCLUDE_PRP'], 'day_length')===false)         sg($obj.'.day_length', gmdate("H:i", ($sunInfo["sunset"]-$sunInfo["sunrise"])));
     //if (stripos($cities[$i]['EXCLUDE_PRP'], 'transit')===false)            sg($obj.'.transit', $sunInfo["transit"]);
     if (stripos($cities[$i]['EXCLUDE_PRP'], 'civil_twilight_begin')===false) sg($obj.'.civil_twilight_begin', date("H:i", $sunInfo["civil_twilight_begin"]));
     if (stripos($cities[$i]['EXCLUDE_PRP'], 'civil_twilight_end')===false) sg($obj.'.civil_twilight_end', date("H:i", $sunInfo["civil_twilight_end"]));
     if ($cities[$i]['MAIN_CITY']) {
       sg('ThisComputer.SunRiseTime', date("H:i", $sunInfo["sunrise"]), 0, 'ow main city process');
       sg('ThisComputer.SunSetTime', date("H:i", $sunInfo["sunset"]), 0, 'ow main city process');
     }
   }
    break;
  } else {
    if($this->config['debug_level']>=1) debmes('[ERR] '.$curWeather->cod.': '.$curWeather->message, 'openweather');
    return false;
  }
  $ret++;
}
