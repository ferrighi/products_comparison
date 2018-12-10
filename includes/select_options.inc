<?php

// define function to select only one platformuct
//function select_image($scr_name,$year,$month,$day,$platform,$pbn,$tile,$action,$platforms,$pbn_array,$tiles_array) {
//function select_image($scr_name,$year,$month,$day,$platform,$tile,$action,$platforms,$tiles_array) {
function select_image($scr_name,$date1,$date2,$dates,$tile,$action,$tiles_array) {

  global $firstyears_array, $defyear;
  $output = '';
  $output .= "<form action=\"".$scr_name."\" method=\"get\">\n";
  $output .= "<legend>Select a tile and the date from the available lists. Move the slider to compare the products</legend>\n";
  $output .= "<table>";
  $output .= "<tr>";
  $output .= "<th style=\"text-align: center; color: #222222\">";
  $output .= "Tile";
  $output .= "</th>";
  $output .= "<th style=\"text-align: center; color: #222222\">";
  $output .= "Product";
  $output .= "</th>";
  $output .= "<th style=\"text-align: center; color: #222222\">";
  $output .= "Date - Time";
  $output .= "</th>";
  $output .= "</tr>";

  $output .= "<tr>";
  #set up the tile selection
  $output .= "<th rowspan=\"2\">";
  $output .= "<select class=\"drop-selection\" onchange=\"this.form.date1.value='None'; this.form.date2.value='None'; this.form.submit()\" name=\"tile\">\n";
  //$output .= "<select class=\"drop-selection\" onchange=\"this.form.submit()\" name=\"tile\">\n";
  for ($p=0;$p<count($tiles_array);$p++) {
    if ($tiles_array[$p] !== $tile) {
      $output .= "<option>".$tiles_array[$p]."</option>\n";
    }
    else {
      $output .= "<option selected=\"selected\">".$tiles_array[$p]."</option>\n";
    }
  }
  $output .= "<br>";
  $output .= "</select>\n";
  $output .= "</th>";

  $output .= "<th style=\"text-align: center; color: #222222\">";
  $output .= "First";
  $output .= "</th>";
  

  #set up the year selection
  $output .= "<th>";
  $output .= "<select class=\"drop-selection\" onchange=\"this.form.submit()\" name=\"date1\">\n";
  for ($y=0;$y<count($dates);$y++) {
    if ($dates[$y][1] !== $date1) {
      $output .= "<option value=".$dates[$y][1].">".$dates[$y][0][0]. '/' .$dates[$y][0][1].'/' .$dates[$y][0][2]. " - ".$dates[$y][0][3]. "</option>\n";
    }
    else {
      $output .= "<option value=".$dates[$y][1]." selected=\"selected\">".$dates[$y][0][0]. '/' .$dates[$y][0][1].'/' .$dates[$y][0][2]." - ".$dates[$y][0][3]. "</option>\n";
    }
  }
  $output .= "</select>\n";
  $output .= "</th>";

  $output .= "</tr>";

  $output .= "<tr>";

  $output .= "<th style=\"text-align: center; color: #222222\">";
  $output .= "Second";
  $output .= "</th>";

  #set up the year selection
  $output .= "<th>";
  $output .= "<select class=\"drop-selection\" onchange=\"this.form.submit()\" name=\"date2\">\n";
  for ($y=0;$y<count($dates);$y++) {
    if ($dates[$y][1] !== $date2) {
      $output .= "<option value=".$dates[$y][1].">".$dates[$y][0][0]. '/' .$dates[$y][0][1].'/' .$dates[$y][0][2]." - ".$dates[$y][0][3]. "</option>\n";
    }
    else {
      $output .= "<option value=".$dates[$y][1]." selected=\"selected\">".$dates[$y][0][0]. '/' .$dates[$y][0][1].'/' .$dates[$y][0][2]." - ".$dates[$y][0][3]. "</option>\n";
    }
  }
  $output .= "</select>\n";
  $output .= "</th>";

  $output .= "</tr>";

  $output .= "</table>";
  $output .= "</form>\n";

  return $output;
}
