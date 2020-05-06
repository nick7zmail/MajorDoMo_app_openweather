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
    if (stripos($cities[$i]['EXCLUDE_PRP'], 'temperature')===false)         sg($obj.'.temperature', round($curWeather->main->temp, $round));
    if (stripos($cities[$i]['EXCLUDE_PRP'], 'weather_type')===false)        sg($obj.'.weather_type', $curWeather->weather[0]->description);
    if (stripos($cities[$i]['EXCLUDE_PRP'], 'wind_direction')===false)      sg($obj.'.wind_direction', round($curWeather->wind->deg, $round));
    if (stripos($cities[$i]['EXCLUDE_PRP'], 'wind_direction_text')===false) sg($obj.'.wind_direction_text', getWindDirection(round($curWeather->wind->deg, $round)));
    if (stripos($cities[$i]['EXCLUDE_PRP'], 'wind_direction_full')===false) sg($obj.'.wind_direction_full', getWindDirection(round($curWeather->wind->deg, $round), true));
    if (stripos($cities[$i]['EXCLUDE_PRP'], 'wind_speed')===false)          sg($obj.'.wind_speed',round($curWeather->wind->speed, $round));
    if (stripos($cities[$i]['EXCLUDE_PRP'], 'humidity')===false)            sg($obj.'.humidity', round($curWeather->main->humidity, $round));
    if (stripos($cities[$i]['EXCLUDE_PRP'], 'pressure')===false)            sg($obj.'.pressure', round($curWeather->main->pressure, $round));
    if (stripos($cities[$i]['EXCLUDE_PRP'], 'pressure_mmhg')===false)       sg($obj.'.pressure_mmhg', round(ConvertPressure($fact->pressure, "hpa", "mmhg", 2), $round));
    if (stripos($cities[$i]['EXCLUDE_PRP'], 'image')===false)               sg($obj.'.image', $curWeather->weather[0]->icon);
    if (stripos($cities[$i]['EXCLUDE_PRP'], 'clouds')===false)              sg($obj.'.clouds', $curWeather->clouds->all);
    if (stripos($cities[$i]['EXCLUDE_PRP'], 'rain')===false)                sg($obj.'.rain', isset($curWeather->main->rain) ? $fact->rain : '');
    if (stripos($cities[$i]['EXCLUDE_PRP'], 'condCode')===false)            sg($obj.'.condCode', $curWeather->weather[0]->id);
    if (stripos($cities[$i]['EXCLUDE_PRP'], 'data_update')===false)         sg($obj.'.data_update', $date);

    /*$sunInfo = GetSunInfo();
    if ($sunInfo)
    {
     $sunRise = $sunInfo["sunrise"];
     $sunSet = $sunInfo["sunset"];
     $dayLength = $sunSet - $sunRise;

     if (stripos($cities[$i]['EXCLUDE_PRP'], 'sunrise')===false) sg($obj.'.sunrise', $sunRise);
     if (stripos($cities[$i]['EXCLUDE_PRP'], 'sunset')===false) sg($obj.'.sunset', $sunSet);
     if (stripos($cities[$i]['EXCLUDE_PRP'], 'day_length')===false) sg($obj.'.day_length', $dayLength);
     if (stripos($cities[$i]['EXCLUDE_PRP'], 'transit')===false) sg($obj.'.transit', $sunInfo["transit"]);
     if (stripos($cities[$i]['EXCLUDE_PRP'], 'civil_twilight_begin')===false) sg($obj.'.civil_twilight_begin', $sunInfo["civil_twilight_begin"]);
     if (stripos($cities[$i]['EXCLUDE_PRP'], 'civil_twilight_end')===false) sg($obj.'.civil_twilight_end', $sunInfo["civil_twilight_end"]);
   }*/
    break;
  } else {
    if($this->config['debug_level']>=1) debmes('[ERR] '.$curWeather->cod.': '.$curWeather->message, 'openweather');
    return false;
  }
  $ret++;
}
