<?php

function select_image($scr_name,$pr1,$pr2,$prinfo,$tile,$layer1, $layer2,$action,$tiles_array, $layers_array,$layersN_array) {

  global $firstyears_array, $defyear;
  $output = '';
<<<<<<< HEAD
  $output .= "<form action=\"".$scr_name."\" method=\"get\">\n";
//  $output .= "<legend>Select a tile and the date from the available lists. Move the slider to compare the products</legend>\n";
  $output .= "<table style=\"width:80%; margin-right:auto; margin-left:auto\">";
=======
  $output .= "<table style=\"width:80%; margin-right:auto; margin-left:auto; box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);\">";
>>>>>>> tiles
  $output .= "<tr>";
  $output .= "<th style=\"text-align: center; color: #222222\">";
  $output .= "Tile";
  $output .= "</th>";
  $output .= "<th style=\"text-align: center; color: #222222\">";
  $output .= "Date - Time";
  $output .= "</th>";
  $output .= "</th>";
  $output .= "<th style=\"text-align: center; color: #222222\">";
  $output .= "Composites";
  $output .= "</th>";
  $output .= "</tr>";

  $output .= "<tr>";
  #set up the tile selection
  $output .= "<form id=\"selectform\" action=\"".$scr_name."\" method=\"get\">\n";
  $output .= "<th rowspan=\"2\">";
  $output .= "<select class=\"drop-selection\" onchange=\" this.form.pr1.value='None'; this.form.pr2.value='None'; this.form.submit(); \" name=\"tile\">\n";
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
  


  #set up the time selection for pr1
  $output .= "<th>";
  $output .= "<span style=\"display:inline-block; width: 1.5em;\"></span><select class=\"drop-selection\" onchange=\"this.form.submit()\" name=\"pr1\">\n";
  //$output .= "<span style=\"display:inline-block; width: 1.5em;\"></span><select class=\"drop-selection\" name=\"pr1\">\n";
  for ($y=0;$y<count($prinfo);$y++) {
    if ($prinfo[$y][1] !== $pr1) {
      $output .= "<option value=".$prinfo[$y][1].">".$prinfo[$y][0][0]. '/' .$prinfo[$y][0][1].'/' .$prinfo[$y][0][2]. " - ".$prinfo[$y][0][3]. "</option>\n";
      //print_r("not selected");
    }
    else {
      $output .= "<option value=".$prinfo[$y][1]." selected=\"selected\">".$prinfo[$y][0][0]. '/' .$prinfo[$y][0][1].'/' .$prinfo[$y][0][2]." - ".$prinfo[$y][0][3]. "</option>\n";
      //print_r("selected");
      $address1 = $prinfo[$y][2];
      $latlon1 = array($prinfo[$y][3][0],$prinfo[$y][3][1]);
    }
  }
  $output .= "</select>\n";
  $output .= "</th>";

  #set up the composite/band selection for pr1
  $output .= "<th>";
  $output .= "<select class=\"drop-selection\" onchange=\"this.form.submit()\" name=\"layer1\">\n";
  //$output .= "<select class=\"drop-selection\" name=\"layer1\">\n";
  for ($p=0;$p<count($layers_array);$p++) {
    if ($layers_array[$p] !== $layer1) {
      $output .= "<option value=".$layers_array[$p].">".$layersN_array[$p]."</option>\n";
    }
    else {
      $output .= "<option value=".$layers_array[$p]." selected=\"selected\">".$layersN_array[$p]."</option>\n";
    }
  }
  $output .= "</select>\n";
  $output .= "</th>";
  $output .= "</tr>";

  $output .= "<tr>";
  #set up the year selection
  $output .= "<th>";
  $output .= "<span style=\"display:inline-block; width: 1.5em;\"></span><select class=\"drop-selection\" onchange=\"this.form.submit()\" name=\"pr2\">\n";
  //$output .= "<span style=\"display:inline-block; width: 1.5em;\"></span><select class=\"drop-selection\" name=\"pr2\">\n";
  for ($y=0;$y<count($prinfo);$y++) {
    if ($prinfo[$y][1] !== $pr2) {
      $output .= "<option value=".$prinfo[$y][1].">".$prinfo[$y][0][0]. '/' .$prinfo[$y][0][1].'/' .$prinfo[$y][0][2]." - ".$prinfo[$y][0][3]. "</option>\n";
    }
    else {
      $output .= "<option value=".$prinfo[$y][1]." selected=\"selected\">".$prinfo[$y][0][0]. '/' .$prinfo[$y][0][1].'/' .$prinfo[$y][0][2]." - ".$prinfo[$y][0][3]. "</option>\n";
      $address2 = $prinfo[$y][2];
      $latlon2 = array($prinfo[$y][3][0],$prinfo[$y][3][1]);
    }
  }
  $output .= "</select>\n";
  $output .= "</th>";

  #set up the composite/band selection
  $output .= "<th>";
  $output .= "<select class=\"drop-selection\" onchange=\"this.form.submit()\" name=\"layer2\">\n";
  //$output .= "<select class=\"drop-selection\" name=\"layer2\">\n";
  for ($p=0;$p<count($layers_array);$p++) {
    if ($layers_array[$p] !== $layer2) {
      $output .= "<option value=".$layers_array[$p].">".$layersN_array[$p]."</option>\n";
    }
    else {
      $output .= "<option  value=".$layers_array[$p]." selected=\"selected\">".$layersN_array[$p]."</option>\n";
    }
  }
  $output .= "</select>\n";
  $output .= "</th>";
  $output .= "</tr>";

  if ($pr1 !== 'None' && $pr2 !== 'None'){
     $output .= "<tr>";
<<<<<<< HEAD
     $output .= "<td  style=\"text-align: center; font-size:15px\" colspan=\"2\">";
     $output .= "<strong>You are comparing Sentinel-2 products:</strong><br>";
     $output .= $pr1."<br>".$pr2;
=======
     $output .= "<td  style=\"text-align: center; font-size:15px\" colspan=\"3\">";
     if ($pr1 == $pr2){
        $output .= "<strong>You are comparing the same Sentinel-2 product:</strong><br>";
        $output .= $pr1;
     }else{
        $output .= "<strong>You are comparing Sentinel-2 products:</strong><br>";
        $output .= $pr1."<br>".$pr2;
     }
>>>>>>> tiles
     $output .= "</td>";
     $output .= "</tr>";
  }
  $output .= "</table>\n";
  $output .= "</form>";

  return [$output,$address1,$address2,$latlon1,$latlon2];
}
