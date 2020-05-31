<?php
while($ret<=3) {
  $url="http://api.openweathermap.org/data/2.5/onecall?lat=" . $cities[$i]['CITY_LAT'] ."&lon=" . $cities[$i]['CITY_LON'] . "&exclude=daily,minutely,hourly&mode=json&units=" . $unit . "&lang=" . $lang . "&appid=" . $apiKey;
  if($this->config['debug_level']>=2) debmes('[DBG] --- [fact] '.$url, 'openweather');
  $data =  getURL($url);
  if($this->config['debug_level']>=2) debmes('[DBG] +++ [fact] '.$data, 'openweather');
  $curWeather = json_decode($data);
  if ($curWeather->current) {
    $obj=$cities[$i]['LINKED_OBJECT'];
    $date = date("d.m.Y G:i:s T Y", $curWeather->current->dt);
    //main
    if (stripos($cities[$i]['EXCLUDE_PRP'], 'temperature')===false)         sg($obj.'.temperature', round($curWeather->current->temp, $round));
    if (stripos($cities[$i]['EXCLUDE_PRP'], 'temp_feels_like')===false)     sg($obj.'.temp_feels_like', round($curWeather->current->feels_like, $round));
    if (stripos($cities[$i]['EXCLUDE_PRP'], 'pressure')===false)            sg($obj.'.pressure', round($curWeather->current->pressure, $round));
    if (stripos($cities[$i]['EXCLUDE_PRP'], 'pressure_mmhg')===false)       sg($obj.'.pressure_mmhg', round(ConvertPressure($curWeather->current->pressure, "hpa", "mmhg", 2), $round));
    if (stripos($cities[$i]['EXCLUDE_PRP'], 'humidity')===false)            sg($obj.'.humidity', round($curWeather->current->humidity, $round));
    if (stripos($cities[$i]['EXCLUDE_PRP'], 'dew_point ')===false)          sg($obj.'.dew_point ', round($curWeather->current->dew_point, $round));
    if (stripos($cities[$i]['EXCLUDE_PRP'], 'uvindex')===false)             sg($obj.'.uvindex', $curWeather->current->uvi);
    if (stripos($cities[$i]['EXCLUDE_PRP'], 'visibility')===false)          sg($obj.'.visibility', $curWeather->current->visibility);
    //wind
    if (stripos($cities[$i]['EXCLUDE_PRP'], 'wind_direction')===false)      sg($obj.'.wind_direction', round($curWeather->current->wind_deg, $round));
    if (stripos($cities[$i]['EXCLUDE_PRP'], 'wind_direction_text')===false) sg($obj.'.wind_direction_text', getWindDirection(round($curWeather->current->wind_deg, $round)));
    if (stripos($cities[$i]['EXCLUDE_PRP'], 'wind_direction_full')===false) sg($obj.'.wind_direction_full', getWindDirection(round($curWeather->current->wind_deg, $round), true));
    if (stripos($cities[$i]['EXCLUDE_PRP'], 'wind_speed')===false)          sg($obj.'.wind_speed', round($curWeather->current->wind_speed, $round));
    //weather
    if (stripos($cities[$i]['EXCLUDE_PRP'], 'condCode')===false)            sg($obj.'.condCode', $curWeather->current->weather[0]->id);
    if (stripos($cities[$i]['EXCLUDE_PRP'], 'weather_type')===false)        sg($obj.'.weather_type', $curWeather->current->weather[0]->description);
    if (stripos($cities[$i]['EXCLUDE_PRP'], 'image')===false)               sg($obj.'.image', $curWeather->current->weather[0]->icon);
    //clouds, snow, rain
    if (stripos($cities[$i]['EXCLUDE_PRP'], 'clouds')===false)              sg($obj.'.clouds', $curWeather->current->clouds);
    if (stripos($cities[$i]['EXCLUDE_PRP'], 'rain1h')===false)              sg($obj.'.rain', isset($curWeather->current->rain->{'1h'}) ? $curWeather->current->rain->{'1h'} : '');
    if (stripos($cities[$i]['EXCLUDE_PRP'], 'snow1h')===false)              sg($obj.'.snow', isset($curWeather->current->snow->{'1h'}) ? $curWeather->current->snow->{'1h'} : '');
    if (stripos($cities[$i]['EXCLUDE_PRP'], 'data_update')===false)         sg($obj.'.data_update', $date);

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
