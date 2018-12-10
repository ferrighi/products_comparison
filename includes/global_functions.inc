<?php

function find_yesterday() {

  # Find default. 
  $deftime  = mktime(date("H"),date("i"),0,date("m"),date("d"),date("Y")) -24*60*60;
  $defdate  = getdate($deftime);
  $defyear  = sprintf("%4d",$defdate["year"]);
  $defmonth = sprintf("%02d",$defdate["mon"]);
  $defday   = sprintf("%02d",$defdate["mday"]);

  $defaults = array($defyear, $defmonth, $defday);

  return $defaults;
}


function get_date($year,$month,$day) {

  list($defyear, $defmonth, $defday) = find_yesterday();

  $date = array($year,$month,$day);
 
  $year  = sprintf("%4d",$date[0]);
  $month = sprintf("%02d",$date[1]);
  $day   = sprintf("%02d",$date[2]);

  if (!$day || $day == "00") {   # Use yesterday as default
    $year  = $defyear;
    $month = $defmonth;
    $day   = $defday;
   }
  $daysmonth = cal_days_in_month(CAL_GREGORIAN,$month,$year);
  if ($day > $daysmonth) {
    $day = $daysmonth;
   }
 
  return array($year,$month,$day);
}

function monthnum2monthstr($monnum) {

  $monthnames = array("january","february","march","april","may","june",
		      "july","august","september","october","november","december");

  if ($monnum < 1 || $monnum > 12) {
    return "NA";
  }
  else {
    return $monthnames[$monnum-1];
  }
}
