<?php

  $f = new FORMS();

    // this will handle all things read from and writing to the data source files
    class FORMS extends DB{

        // set the base path of where we can access data, etc.
        //var $basePath = "";

        // this is an array of all of the US states for use in the form
        var $states = array("AL"=>"ALABAMA","AK"=>"ALASKA","AS"=>"AMERICAN SAMOA","AZ"=>"ARIZONA","AR"=>"ARKANSAS","CA"=>"CALIFORNIA","CO"=>"COLORADO","CT"=>"CONNECTICUT","DE"=>"DELAWARE","DC"=>"DISTRICT OF COLUMBIA","FM"=>"FEDERATED STATES OF MICRONESIA","FL"=>"FLORIDA","GA"=>"GEORGIA","GU"=>"GUAM GU","HI"=>"HAWAII","ID"=>"IDAHO","IL"=>"ILLINOIS","IN"=>"INDIANA","IA"=>"IOWA","KS"=>"KANSAS","KY"=>"KENTUCKY","LA"=>"LOUISIANA","ME"=>"MAINE","MH"=>"MARSHALL ISLANDS","MD"=>"MARYLAND","MA"=>"MASSACHUSETTS","MI"=>"MICHIGAN","MN"=>"MINNESOTA","MS"=>"MISSISSIPPI","MO"=>"MISSOURI","MT"=>"MONTANA","NE"=>"NEBRASKA","NV"=>"NEVADA","NH"=>"NEW HAMPSHIRE","NJ"=>"NEW JERSEY","NM"=>"NEW MEXICO","NY"=>"NEW YORK","NC"=>"NORTH CAROLINA","ND"=>"NORTH DAKOTA","MP"=>"NORTHERN MARIANA ISLANDS","OH"=>"OHIO","OK"=>"OKLAHOMA","OR"=>"OREGON","PW"=>"PALAU","PA"=>"PENNSYLVANIA","PR"=>"PUERTO RICO","RI"=>"RHODE ISLAND","SC"=>"SOUTH CAROLINA","SD"=>"SOUTH DAKOTA","TN"=>"TENNESSEE","TX"=>"TEXAS","UT"=>"UTAH","VT"=>"VERMONT","VI"=>"VIRGIN ISLANDS","VA"=>"VIRGINIA","WA"=>"WASHINGTON","WV"=>"WEST VIRGINIA","WI"=>"WISCONSIN","WY"=>"WYOMING","AE"=>"ARMED FORCES AFRICA  CANADA  EUROPE  MIDDLE EAST","AA"=>"ARMED FORCES AMERICA (EXCEPT CANADA)","AP"=>"ARMED FORCES PACIFIC");




        // class constructor that gets called by default along with instantiation of class
        function __construct($a=""){
          //$this->basePath = $a;
        }





        // this will figure out the form that we want to build to be shown on the screen
        // a = tool
        // b = action
        // c = record id
        function FORM_BUILD($a,$b,$c){
            $data = json_decode($this->DB_READFILE("config/tools.json"),true);
            $rec = "";
            if(isset($c) && $c != "" && $b != "add"){
              $rec = $this->DB_RECORD_GET($a,$c);    // grab the data for this record so that it can be added to each form element
            }
            $return = array("",array());
            $keys = array_keys($data[strtoupper($a)]['fields']);
            $cnt = 0;

            // need to disable fields in remove mode as well as in edit mode if they have not yet picked a record to edit
            $status = "";
            if(($b == "edit" || $b == "remove") && $c == ""){
              $status = "disabled=\"disabled\"";
            }else if($b == "remove" && $c != ""){
              $status = "disabled=\"disabled\"";
            }

            // check if the fields in group or not
            $cg = 0; $toolGroup = $editGroup =array();
            foreach($data[strtoupper($a)]['fields'] as $k => $g){
              if( array_key_exists('cloneG', $g) ){
                $data[strtoupper($a)]['fields'][$k]['cloneP'] = $cg + 1;
                $toolGroup[] = $k;
                $cg++;
              }
            }


            foreach($data[strtoupper($a)]['fields'] as $f){

              // need to build out the matching field type so that it can be added to the form and determine if there is a value to show
              $curVal = "";
              if($b === "edit" || $b === "remove"){
                if($f['type'] == "time"){ // this one will have multiple data values stored together for a multi part field
                  $curVal = (isset($rec) && isset($rec[$a."_".$keys[$cnt]."_hours"])?[$rec[$a."_".$keys[$cnt]."_hours"],$rec[$a."_".$keys[$cnt]."_minutes"]]:"");
                }else if($f['type'] == "latlon"){ // specific to map markers to get lat and lon
                  $curVal = (isset($rec) && isset($rec[$a."_".$keys[$cnt]."_lat"])?[$rec[$a."_".$keys[$cnt]."_lat"],$rec[$a."_".$keys[$cnt]."_lon"]]:"");
                }else{  // single field, single value
                  $curVal = (isset($rec) && isset($rec[$a."_".$keys[$cnt]])?$rec[$a."_".$keys[$cnt]]:"");
                }
              }

              // get multiselect options
              $multi = $multiList = '';
              if($f['type']=='multiselect') {
                $multi = json_decode($this->DB_READFILE("config/addons/".$f['options'].".json"),true);
                if (is_array($multi)) {
                  $milts = '';
                  if (is_array($curVal)) {
                    foreach ($multi as $op => $s) :
                      $is_selected = (in_array($op, $curVal)) ? 'selected' : '';
                      $multiList .= (in_array($op, $curVal)) ? "<li>".$s."</li>" : '';
                      $milts .="<option ".$is_selected." value=\"".$op."\">".$s."</option>";
                    endforeach;
                  } else {
                    foreach ($multi as $op => $s) : $milts .="<option value=\"".$op."\">".$s."</option>"; endforeach;
                  }
                }
              }

              if (is_array($curVal)) {

                if ($cg==0) {
                  // check if type is a multiple selected
                  if($f['type']=='multiselect') {
                    if (is_array($multi)) {
                      $field = $this->FORM_FIELD($a,$b,$f,$keys[$cnt],$curVal,$status,$c,$milts,$multiList);
                      // build out the fieldset for the form element
                      $return[0] .= $this->FORM_FIELDSET($f, $field,$keys[$cnt],$b,$c,$cg);
                    }
                  } else {
                    foreach ($curVal as $cKey => $cVal) {
                      $field = $this->FORM_FIELD($a,$b,$f,$keys[$cnt],$cVal,$status,$c);
                      // build out the fieldset for the form element
                      $return[0] .= $this->FORM_FIELDSET($f, $field,$keys[$cnt],$b,$c,$cg,$cKey);
                    }
                  }

                } else {
                  foreach ($curVal as $cKey => $cVal) {
                    $editGroup[$cKey]['field'][] = $this->FORM_FIELD($a,$b,$f,$keys[$cnt],$cVal,$status,$c);
                    $editGroup[$cKey]['f'][] = $f;
                    $editGroup[$cKey]['keys'][] = $keys[$cnt];
                    $editGroup[$cKey]['action'][] = $b;
                  }
                  if ($keys[$cnt] == end($toolGroup)) {
                    // build out the fieldset for the form element
                    $return[0] .= $this->FORM_GROUPSET($editGroup,$toolGroup,$cg,$c);
                  }
                }
              } else {
                // get the multiselect options
                if($f['type']=='multiselect') {
                  if (is_array($multi)) {
                    $field = $this->FORM_FIELD($a,$b,$f,$keys[$cnt],$curVal,$status,$c,$milts,$multiList);
                    // build out the fieldset for the form element
                    $return[0] .= $this->FORM_FIELDSET($f, $field,$keys[$cnt],$b,$c,$cg);
                  }
                } else {
                  $field = $this->FORM_FIELD($a,$b,$f,$keys[$cnt],$curVal,$status,$c);
                  // build out the fieldset for the form element
                  $return[0] .= $this->FORM_FIELDSET($f, $field,$keys[$cnt],$b,$c,$cg);
                }
              }

              // if we are going to list records by this field, store that to be passed back
              if(isset($f['listby']) && $f['listby'] >= 0){
                $return[1][$f['listby']] = $a."_".$keys[$cnt];
              }
              $cnt++;
            }

            // add a cancel and submit button here to finish up the form
            $submitValue = "Save Changes";
            switch ($b) {
              case "add":
                $submitValue = "Save Entry";
                break;
              case "edit":
                $submitValue = "Save Changes";
                break;
              case "remove":
                $submitValue = "Remove";
                break;
            }




            // on click of cancel button, return to main tools list
            // $return[0] .= "<fieldset><label id=\"messages\">* Denotes a required field</label></fieldset><fieldset><input id=\"".$a."_".$b."_cancel\" name=\"".$a."_".$b."_cancel\" data-type=\"tool_cancel\" type=\"button\" value=\"Cancel\" title=\"Click here to cancel\"><input id=\"".$a."_".$b."_submit\" name=\"".$a."_".$b."_submit\" data-type=\"tool_submit\" type=\"button\" value=\"".$submitValue."\" title=\"Click here to ".strtolower($submitValue)."\"".($c != ""?"":$status)."></fieldset>";
            // ksort($return[1]);

            $return[0] .= "<fieldset><label id=\"messages\">* Denotes a required field</label></fieldset><fieldset><input id=\"".$a."_".$b."_submit\" name=\"".$a."_".$b."_submit\" data-type=\"tool_submit\" type=\"button\" value=\"".$submitValue."\" title=\"Click here to ".strtolower($submitValue)."\"".($c != ""?"":$status)."></fieldset>";
            ksort($return[1]);  // turned off the cancel button as it no longer makes sense...perhaps turn it back on as a clear???


            return $return;
        }



        // this function will build out a form fieldset
        // a = tool [ array with all the tools value comes from the json file ]
        // b = field [ this is the print input field for the form it came from FORM_FIELD Function ]
        // c = keys [ the field key will use to set the label for the input ]
        // d = action [ to determine the action for that tool add/edit/delete ]
        // e = rid [ this is the record id ]
        // g = group [ to set the group wrapper div and how many fields into that group ]
        // s = switch [ to switch clone/remove button by default will be hidden if zero will be clone otherwise will be remove ]
        function FORM_FIELDSET($a, $b, $c, $d, $e, $g=0,$s=""){
          $return = "";

          // check if the field in group add div to make group
          ( $g!=0 && isset($a['cloneP']) && $a['cloneP']==1 )? $return .= "<div class='neocloneG ".(isset($a['clone']) && $a['clone']===true?"gclone ":"")." neo__cloneG'>" :'';

          $return .= "<fieldset class=\"check-value ".(isset($a['clone']) && $a['clone']===true?"clone ":"")."animate\" data-anim-type=\"fadeIn\" data-anim-delay=\"200\" >";
          $return .= $b;
          $return .= "<label>".(isset($a['required']) && $a['required']===true?"* ":"").ucwords(str_replace("_"," ",$c)).($a['type'] == "media" && isset($a['max']) && $a['max'] != ""?"<br />Max = ".$a['max']:"")."</label>";
          $return .= "<p>".$a['description']."</p>";

          if ( $d=='add' && $e!="" && $g==0 ) {
            $return .= (isset($a['clone']) && $a['clone']===true?"<a href=\"\" title=\"click here to clone\" class=\"neo__Aclone neoclone\" data-text=\"remove\" data-toggle-class=\"neoDclone\">clone</a> ":"");
          }

          if ( $d=='edit' && $e!="" && $g==0 ) {
            if ($s==0) {
              $return .= (isset($a['clone']) && $a['clone']===true?"<a href=\"\" title=\"click here to clone\" class=\"neo__Aclone neoclone\" data-text=\"remove\" data-toggle-class=\"neoDclone\">clone</a> ":"");
            } else {
              $return .= (isset($a['clone']) && $a['clone']===true?"<a href=\"\" title=\"click here to remove\" class=\"neo__Aclone neo__Dclone neoDclone\" data-text=\"remove\" data-toggle-class=\"neoDclone\">remove</a> ":"");
            }
          }

          $return .= "</fieldset>";
          // add Clone Button for the group
          if ( $g!=0 && isset($a['cloneP']) && $a['cloneP']==$g ) {
            $return .= (isset($a['clone']) && $a['clone']===true?"<a href=\"\" title=\"click here to clone\" class=\"neo__AGclone neoGclone\" data-text=\"remove\" data-toggle-class=\"neoDGclone\">clone</a> ":"");
            $return .="</div>";

          }
          return $return;
        }



        // this function will build out a form fieldset
        // a = array of all group elemets
        // b = array of tools name
        // c = rid [ this is the record id ]
        // g = group [ to set the group wrapper div and how many fields into that group ]
        function FORM_GROUPSET($a, $b, $g, $c){
          $return = "";
          for ($i=0; $i < count($a); $i++) {

            if (isset($a[$i])) {
            $return .= "<div class='neocloneG ".(isset($a[$i]['f'][0]['clone']) && $a[$i]['f'][0]['clone']===true?"gclone ":"")." neo__cloneG'>";

              for ($j=0; $j < count($a[$i]); $j++) {
                // get the tools array
                if (isset($a[$i]['f'][$j])) {
                  $t = $a[$i]['f'][$j];
                  $return .= "<fieldset class=\"check-value ".(isset($t['clone']) && $t['clone']===true?"clone ":"")."animate\" data-anim-type=\"fadeIn\" data-anim-delay=\"200\" >";
                  $return .= $a[$i]['field'][$j];

                  $return .= "<label>".(isset($t['required']) && $t['required']===true?"* ":"").ucwords(str_replace("_"," ",$b[$j])).($t['type'] == "media" && isset($t['max']) && $t['max'] != ""?"<br />Max = ".$t['max']:"")."</label>";
                  $return .= "<p>".$t['description']."</p>";

                  $return .= "</fieldset>";
                }

              }

              if ($i==0) {
                $return .= (isset($a[$i]['f'][$i]['clone']) && $a[$i]['f'][$i]['clone']===true?"<a href=\"\" title=\"click here to clone\" class=\"neo__AGclone neoGclone\" data-text=\"remove\" data-toggle-class=\"neoDGclone\">clone</a> ":"");
              } else {
                $return .= (isset($a[$i]['f'][0]['clone']) && $a[$i]['f'][0]['clone']===true?"<a href=\"\" title=\"click here to remove\" class=\"neo__AGclone neo__DGclone neoDGclone\" data-text=\"remove\" data-toggle-class=\"neoDGclone\">remove</a> ":"");
              }

              $return .= "</div>";
            }

          }
          return $return;
        }



        // this function will build out a form field based on the type and it being required or not
        function FORM_FIELD($tool,$action,$field,$name,$value,$status,$rid="",$multi="",$multiList=""){
          $testCall = "FORM_FIELD_".strtoupper($field['type']);
          return $this->$testCall($_GET,$tool,$action,$field,$name,$value,$status,$rid,$multi,$multiList);

          //switch ($field['type']) {
            // case "text":
            //   return "<input type=\"text\" data-type=\"text\" name=\"".$tool."_".$name."\" id=\"".$tool."_".$name."\" ".(isset($field['fieldlength'])?"maxlength=\"".$field['fieldlength']."\"":"")." ".(isset($field['required']) && $field['required']===true?"required=\"required\"":"")." ".(isset($field['readonly']) && $field['readonly']===true?"readonly=\"readonly\"":"")." value=\"".(isset($value) && $value != ""?$value:"")."\" ".($action == "remove"?"readonly=\"readonly\"":"").$status." />";
            //   break;
            // case "name":
            //   return "<input type=\"text\" data-type=\"name\" name=\"".$tool."_".$name."\" id=\"".$tool."_".$name."\" ".(isset($field['fieldlength'])?"maxlength=\"".$field['fieldlength']."\"":"")." ".(isset($field['required']) && $field['required']===true?"required=\"required\"":"")." ".(isset($field['readonly']) && $field['readonly']===true?"readonly=\"readonly\"":"")." value=\"".(isset($value) && $value != ""?$value:"")."\" ".($action == "remove"?"readonly=\"readonly\"":"").$status." />";
            //   break;
            // case "latlon":
            //   return "<div class=\"maparea\"><div id=\"mapcanvas\">Google Map</div><input type=\"text\" data-type=\"lat\" name=\"".$tool."_".$name."_lat\" id=\"".$tool."_".$name."_lat\" readonly=\"readonly\" placeholder=\"latitude\" value=\"".(isset($value) && $value != ""?$value[0]:"")."\"".$status." required=\"required\" /><input type=\"text\" data-type=\"lon\" name=\"".$tool."_".$name."_lon\" id=\"".$tool."_".$name."_lon\" readonly=\"readonly\" placeholder=\"longitude\" value=\"".(isset($value) && $value != ""?$value[1]:"")."\"".$status." required=\"required\" /></div>";
            //   break;
            // case "password":
            //   return "<input type=\"password\" data-type=\"password\" name=\"".$tool."_".$name."\" id=\"".$tool."_".$name."\" ".(isset($field['fieldlength'])?"maxlength=\"".$field['fieldlength']."\"":"")." ".(isset($field['required']) && $field['required']===true?"required=\"required\"":"")." ".(isset($field['readonly']) && $field['readonly']===true?"readonly=\"readonly\"":"")." value=\"".(isset($value) && $value != ""?$value:"")."\" ".($action == "remove"?"readonly=\"readonly\"":"").$status." /> ".(trim(strtolower($action)) == "add"?"<a class=\"button js-showpassword\" title=\"Click here to show password\">Show Password</a>":"")."";
            //   break;
            // case "email":
            //   return "<input type=\"text\" data-type=\"email\" name=\"".$tool."_".$name."\" id=\"".$tool."_".$name."\" ".(isset($field['required']) && $field['required']===true?"required=\"required\"":"")." ".(isset($field['placeholder']) && $field['placeholder'] != ""?"placeholder=\"".$field['placeholder']."\"":"")." title=\"Click to select date\" value=\"".(isset($value) && $value != ""?$value:"")."\" ".($action == "remove"?"readonly=\"readonly\"":"").$status." />";
            //   break;
            // case "title":
            //   return "<input type=\"text\" data-type=\"title\" name=\"".$tool."_".$name."\" id=\"".$tool."_".$name."\" ".(isset($field['fieldlength'])?"maxlength=\"".$field['fieldlength']."\"":"")." ".(isset($field['required']) && $field['required']===true?"required=\"required\"":"")." ".(isset($field['readonly']) && $field['readonly']===true?"readonly=\"readonly\"":"")." value=\"".(isset($value) && $value != ""?$value:"")."\" ".($action == "remove"?"readonly=\"readonly\"":"").$status." />";
            //   break;
            // case "orderweight":
            //   return "<input type=\"text\" data-type=\"orderweight\" name=\"".$tool."_".$name."\" id=\"".$tool."_".$name."\" required=\"required\" maxlength=\"3\" value=\"".(isset($value) && $value != ""?$value:"0")."\" ".($action == "remove"?"readonly=\"readonly\"":"").$status." />";
            //   break;
            // case "date":
            //   return "<input type=\"text\" data-type=\"date\" name=\"".$tool."_".$name."\" id=\"".$tool."_".$name."\" ".(isset($field['required']) && $field['required']===true?"required=\"required\"":"")." ".(isset($field['placeholder']) && $field['placeholder'] != ""?"placeholder=\"".$field['placeholder']."\"":"")." title=\"Click to select date\" value=\"".(isset($value) && $value != ""?$value:"")."\" ".($action == "remove"?"readonly=\"readonly\"":"").$status." />";
            //   break;
            // case "time":
            //   return "<select data-type=\"time\" name=\"".$tool."_".$name."_hours\" id=\"".$tool."_".$name."_hours\" ".($action == "remove"?"readonly=\"readonly\"":"").$status." ".(isset($field['required']) && $field['required']===true?"required=\"required\"":"")."><option value=\"\">HH</option><option value=\"\">----------</option>".$this->TIME_FILL("H",(isset($value[0]) && $value[0] != ""?$value[0]:""))."</select><select data-type=\"time\" name=\"".$tool."_".$name."_minutes\" id=\"".$tool."_".$name."_minutes\" ".($action == "remove"?"readonly=\"readonly\"":"").$status." ".(isset($field['required']) && $field['required']===true?"required=\"required\"":"")."><option value=\"\">MM</option><option value=\"\">----------</option>".$this->TIME_FILL("M",(isset($value[1]) && $value[1] != ""?$value[1]:""))."</select>";
            //   break;
            // case "copy":
            //   return "<textarea ".(isset($field['richeditor']) && $field['richeditor'] === true?"class=\"richeditor\"":"")." name=\"".$tool."_".$name."\" id=\"".$tool."_".$name."\" ".(isset($field['required']) && $field['required']===true?"required=\"required\"":"")." ".(isset($field['readonly']) && $field['readonly']===true?"readonly=\"readonly\"":"")." ".($action == "remove"?"readonly=\"readonly\"":"").$status.">".(isset($value) && $value != ""?$value:"")."</textarea>";
            //   break;
            // case "state":
            //   return "<select data-type=\"state\" name=\"".$tool."_".$name."\" id=\"".$tool."_".$name."\" ".($action == "remove"?"readonly=\"readonly\"":"").$status." ".(isset($field['required']) && $field['required']===true?"required=\"required\"":"")."><option value=\"\">----------</option>".$this->STATES_LIST($this->states,"all",(isset($value) && $value != ""?$value:""))."</select>";
            //   break;
            // case "select":
            //   return "<select data-type=\"select\" name=\"".$tool."_".$name."\" id=\"".$tool."_".$name."\" ".($action == "remove"?"readonly=\"readonly\"":"").$status." ".(isset($field['required']) && $field['required']===true?"required=\"required\"":"")."><option value=\"\">----------</option>".$this->DROPDOWN_FILL($tool,$name,(isset($value) && $value != ""?$value:""))."</select>";
            //   break;
            // case "media":
            //   return "<iframe data-id=\"selectmedia\" id=\"selectmedia\" name=\"selectmedia\" class=\"".($status != ""?"disabled":"")."\" src=\"".($status == ""?"media.php?tool=".$_GET['tool']."&action=select&rid=".$rid."&field=".$name:"")."\"></iframe><p>When selecting new media items or removing media items you DO NOT need to save record changes below, they are saved automatically.<br />Drag and drop items above to re-order them.</p>".($status == ""?"<a class=\"button magnific-iframe\" data-field=\"".$name."\" href=\"media.php?tool=".$_GET['tool']."&action=select&mid=".$rid."&field=".$name."\" title=\"Click here to select media items\" ".(isset($field['max']) && $this->checkMediaMax($rid,$name,$field['max'])?"data-imax=\"".$field['max']."\" style=\"display: none\"":"").">Select Media Items</a>":"");
            //   break;
          //}
        }

        //

        // // the following methods will build out specific form elements
        function FORM_FIELD_TEXT($get,$tool,$action,$field,$name,$value,$status,$rid=""){
          // set input attributes
          $neoID = ($name=="tags"&&!preg_match('/disabled/',$status)?"$name":"".$tool."_".$name."");
          $neoName = $tool."_".$name;
          $neoValue = (isset($value) && $value != ""?$value:"");
          $neoFieldlength = (isset($field['fieldlength'])?"maxlength=\"".$field['fieldlength']."\"":"");
          $neoRequired = (isset($field['required']) && $field['required']===true?"required=\"required\"":"");
          $neoReadonly = (isset($field['readonly']) && $field['readonly']===true?"readonly=\"readonly\"":"");
          $neoRemove = ($action == "remove"?"readonly=\"readonly\"":"");
          // check clone option
          if(isset($field['clone']) && $field['clone']===true): $neoName = $neoName."[]"; endif;
          return "<input type=\"text\" data-type=\"text\" name=\"".$neoName."\" id=\"".$neoID."\" ".$neoFieldlength." ".$neoRequired." ".$neoReadonly." value=\"".$neoValue."\" ".$neoRemove.$status." />";
        }

        function FORM_FIELD_NAME($get,$tool,$action,$field,$name,$value,$status,$rid=""){
          return "<input type=\"text\" data-type=\"name\" name=\"".$tool."_".$name."\" id=\"".$tool."_".$name."\" ".(isset($field['fieldlength'])?"maxlength=\"".$field['fieldlength']."\"":"")." ".(isset($field['required']) && $field['required']===true?"required=\"required\"":"")." ".(isset($field['readonly']) && $field['readonly']===true?"readonly=\"readonly\"":"")." value=\"".(isset($value) && $value != ""?$value:"")."\" ".($action == "remove"?"readonly=\"readonly\"":"").$status." />";
        }

        function FORM_FIELD_LATLON($get,$tool,$action,$field,$name,$value,$status,$rid=""){
          return "<div class=\"maparea\"><div id=\"mapcanvas\">Google Map</div><input type=\"text\" data-type=\"lat\" name=\"".$tool."_".$name."_lat\" id=\"".$tool."_".$name."_lat\" readonly=\"readonly\" placeholder=\"latitude\" value=\"".(isset($value) && $value != ""?$value[0]:"")."\"".$status." required=\"required\" /><input type=\"text\" data-type=\"lon\" name=\"".$tool."_".$name."_lon\" id=\"".$tool."_".$name."_lon\" readonly=\"readonly\" placeholder=\"longitude\" value=\"".(isset($value) && $value != ""?$value[1]:"")."\"".$status." required=\"required\" /></div>";
        }

        function FORM_FIELD_PASSWORD($get,$tool,$action,$field,$name,$value,$status,$rid=""){
          return "<input type=\"password\" data-type=\"password\" name=\"".$tool."_".$name."\" id=\"".$tool."_".$name."\" ".(isset($field['fieldlength'])?"maxlength=\"".$field['fieldlength']."\"":"")." ".(isset($field['required']) && $field['required']===true?"required=\"required\"":"")." ".(isset($field['readonly']) && $field['readonly']===true?"readonly=\"readonly\"":"")." value=\"".(isset($value) && $value != ""?$value:"")."\" ".($action == "remove"?"readonly=\"readonly\"":"").$status." /> ".(trim(strtolower($action)) == "add"?"<a class=\"button js-showpassword\" title=\"Click here to show password\">Show Password</a>":"")."";
        }

        function FORM_FIELD_EMAIL($get,$tool,$action,$field,$name,$value,$status,$rid=""){
          return "<input type=\"text\" data-type=\"email\" name=\"".$tool."_".$name."\" id=\"".$tool."_".$name."\" ".(isset($field['required']) && $field['required']===true?"required=\"required\"":"")." ".(isset($field['placeholder']) && $field['placeholder'] != ""?"placeholder=\"".$field['placeholder']."\"":"")." title=\"Click to select date\" value=\"".(isset($value) && $value != ""?$value:"")."\" ".($action == "remove"?"readonly=\"readonly\"":"").$status." />";
        }

        function FORM_FIELD_TITLE($get,$tool,$action,$field,$name,$value,$status,$rid=""){
          return "<input type=\"text\" data-type=\"title\" name=\"".$tool."_".$name."\" id=\"".$tool."_".$name."\" ".(isset($field['fieldlength'])?"maxlength=\"".$field['fieldlength']."\"":"")." ".(isset($field['required']) && $field['required']===true?"required=\"required\"":"")." ".(isset($field['readonly']) && $field['readonly']===true?"readonly=\"readonly\"":"")." value=\"".(isset($value) && $value != ""?$value:"")."\" ".($action == "remove"?"readonly=\"readonly\"":"").$status." />";
        }

        function FORM_FIELD_ORDERWEIGHT($get,$tool,$action,$field,$name,$value,$status,$rid=""){
          return "<input type=\"text\" data-type=\"orderweight\" name=\"".$tool."_".$name."\" id=\"".$tool."_".$name."\" required=\"required\" maxlength=\"3\" value=\"".(isset($value) && $value != ""?$value:"0")."\" ".($action == "remove"?"readonly=\"readonly\"":"").$status." />";
        }

        function FORM_FIELD_DATE($get,$tool,$action,$field,$name,$value,$status,$rid=""){
          return "<input type=\"text\" data-type=\"date\" name=\"".$tool."_".$name."\" id=\"".$tool."_".$name."\" ".(isset($field['required']) && $field['required']===true?"required=\"required\"":"")." ".(isset($field['placeholder']) && $field['placeholder'] != ""?"placeholder=\"".$field['placeholder']."\"":"")." title=\"Click to select date\" value=\"".(isset($value) && $value != ""?$value:"")."\" ".($action == "remove"?"readonly=\"readonly\"":"").$status." />";
        }

        function FORM_FIELD_TIME($get,$tool,$action,$field,$name,$value,$status,$rid=""){
          return "<select data-type=\"time\" class=\"neochosen\" name=\"".$tool."_".$name."_hours\" id=\"".$tool."_".$name."_hours\" ".($action == "remove"?"readonly=\"readonly\"":"").$status." ".(isset($field['required']) && $field['required']===true?"required=\"required\"":"")."><option value=\"\">HH</option><option value=\"\">----------</option>".$this->TIME_FILL("H",(isset($value[0]) && $value[0] != ""?$value[0]:""))."</select><select data-type=\"time\" name=\"".$tool."_".$name."_minutes\" id=\"".$tool."_".$name."_minutes\" ".($action == "remove"?"readonly=\"readonly\"":"").$status." ".(isset($field['required']) && $field['required']===true?"required=\"required\"":"")."><option value=\"\">MM</option><option value=\"\">----------</option>".$this->TIME_FILL("M",(isset($value[1]) && $value[1] != ""?$value[1]:""))."</select>";
        }

        function FORM_FIELD_COPY($get,$tool,$action,$field,$name,$value,$status,$rid=""){
          return "<textarea ".(isset($field['richeditor']) && $field['richeditor'] === true?"class=\"richeditor\"":"")." name=\"".$tool."_".$name."\" id=\"".$tool."_".$name."\" ".(isset($field['required']) && $field['required']===true?"required=\"required\"":"")." ".(isset($field['readonly']) && $field['readonly']===true?"readonly=\"readonly\"":"")." ".($action == "remove"?"readonly=\"readonly\"":"").$status.">".(isset($value) && $value != ""?$value:"")."</textarea>";
        }

        function FORM_FIELD_STATE($get,$tool,$action,$field,$name,$value,$status,$rid=""){
          return "<select data-type=\"state\" class=\"neochosen has-value\" name=\"".$tool."_".$name."\" id=\"".$tool."_".$name."\" ".($action == "remove"?"readonly=\"readonly\"":"").$status." ".(isset($field['required']) && $field['required']===true?"required=\"required\"":"")."><option value=\"\">----------</option>".$this->STATES_LIST($this->states,"all",(isset($value) && $value != ""?$value:""))."</select>";
        }

        function FORM_FIELD_SELECT($get,$tool,$action,$field,$name,$value,$status,$rid=""){
          return "<select data-type=\"select\" class=\"neochosen has-value\" name=\"".$tool."_".$name."\" id=\"".$tool."_".$name."\" ".($action == "remove"?"readonly=\"readonly\"":"").$status." ".(isset($field['required']) && $field['required']===true?"required=\"required\"":"")."><option value=\"\">----------</option>".$this->DROPDOWN_FILL($tool,$name,(isset($value) && $value != ""?$value:""))."</select>";
        }

        // function FORM_FIELD_MEDIA($get,$tool,$action,$field,$name,$value,$status,$rid=""){
        //   return "<iframe data-id=\"selectmedia\" id=\"selectmedia\" name=\"selectmedia\" ".(isset($field['required']) && $field['required']===true?"required=\"required\"":"")." class=\"".($status != ""?"disabled":"")."\" src=\"".($status == ""?"media.php?tool=".$get['tool']."&action=select&rid=".$rid."&field=".$name:"")."\"></iframe><p>When selecting new media items or removing media items you DO NOT need to save record changes below, they are saved automatically.<br />Drag and drop items above to re-order them.</p>".($status == ""?"<a class=\"button magnific-iframe\" data-field=\"".$name."\" href=\"media.php?tool=".$get['tool']."&action=select&mid=".$rid."&field=".$name."\" title=\"Click here to select media items\" ".(isset($field['max']) && $this->checkMediaMax($rid,$name,$field['max'])?"data-imax=\"".$field['max']."\" style=\"display: none\"":"").">Select Media Items</a>":"");
        // }

        function FORM_FIELD_MEDIA($get,$tool,$action,$field,$name,$value,$status,$rid=""){
          return "<iframe data-id=\"selectmedia\" data-rid=\"".$rid."\" id=\"".$name."\" name=\"".$name."\" ".(isset($field['required']) && $field['required']===true?"required=\"required\"":"")." class=\"".($status != ""?"disabled":"")."\" src=\"".($status == ""?"media.php?tool=".$get['tool']."&action=select&rid=".$rid."&field=".$name:"")."\"></iframe><p>When selecting new media items or removing media items you DO NOT need to save record changes below, they are saved automatically.<br />Drag and drop items above to re-order them.</p>".($status == ""?"<a class=\"button magnific-iframe\" data-field=\"".$name."\" href=\"media.php?tool=".$get['tool']."&action=select&mid=".$rid."&field=".$name."\" title=\"Click here to select media items\" ".(isset($field['max']) && $this->checkMediaMax($rid,$name,$field['max'])?"data-imax=\"".$field['max']."\" style=\"display: none\"":"").">Select Media Items</a>":"");
        }


        function FORM_FIELD_MULTISELECT($get,$tool,$action,$field,$name,$value,$status,$rid="",$multi="",$multiList=""){
          return "<div class=\"neo__multiselect\" ><div><h2>Make Your Choose</h2><select ".(isset($field['required']) && $field['required']===true?"required=\"required\"":"")." class=\"neochosen-deselect neomultis has-value\" data-placeholder=\"Choose a ".ucwords($name)."\" name=\"".$tool."_".$name."[]\" id=\"".$tool."_".$name."\" ".($action == "remove"?"readonly=\"readonly\"":"").$status." multiple><option disabled=\"disabled\" >----------</option>".$multi."</select></div><div><h2>Preview</h2><ul class=\"".$tool."_".$name."\">".$multiList."</ul></div></div>";
        }




        // check to see if a field has already used up the max that it is allowed to store
        // a = the rid of the relation file that we need to look in
        // b = the name of the field that we want to check
        // c = the max number of media items that this field can hold
        function checkMediaMax($a="",$b="",$c=0){
          $cnt = 0;
          if(file_exists("data/media/relations/".$a.".json")){  // check to make sure that relationship file exists
            $rel = json_decode($this::DB_READFILE("data/media/relations/".$a.".json"),true);
            if(isset($rel[$b])){  // check to make sure that the element we are counting exists
              $cnt = count($rel[$b]);
            }
          }
          return ($cnt < $c?false:true);
        }





        // this method will build out the filter options that can be used to sort through various media items
        // a = the array of get parameters that was passed to the page that the filter is running on
        function FORM_BUILDFILTERS($a=""){
          $return = "";
          $return = "<form name=\"filter\" id=\"filter\" class=\"neo__forms\" method=\"get\" action=\"media.php\"><input type=\"hidden\" name=\"action\" id=\"action\" value=\"".$a['action']."\" />".(isset($a['rid']) && $a['rid'] != ""?"<input type=\"hidden\" name=\"rid\" id=\"rid\" value=\"".$a['rid']."\" />":"").(isset($a['mid']) && $a['mid'] != ""?"<input type=\"hidden\" name=\"mid\" id=\"mid\" value=\"".$a['mid']."\" />":"").(isset($a['field']) && $a['field'] != ""?"<input type=\"hidden\" name=\"field\" id=\"field\" value=\"".$a['field']."\" />":"").(isset($a['tool']) && $a['tool'] != ""?"<input type=\"hidden\" name=\"tool\" id=\"tool\" value=\"".$a['tool']."\" />":"")."<strong>Filter By:</strong> <ul>";

          // filter by type options
          $fileTypes = [["Images","jpg"],["Links","link"],["Movies","mov"],["PDFs","pdf"],["VR","vr"],["ZIPs","zip"]];
          $return .= "<li><fieldset><select name=\"filter_type\" id=\"filter_type\" size=\"1\" title=\"Filter by type of media\"><option value=\"\">Select Type</option>";
          foreach($fileTypes as $fT){
            $return .= "<option ".(isset($a['filter_type']) && $a['filter_type'] == $fT[1]?"selected=\"selected\"":"")." value=\"".$fT[1]."\">".$fT[0]."</option>";
          }
          $return .= "</select><p>TYPE</p></fieldset></li>";

          // filter by date range option
          // placeholder=\"Start Date\"
          $return .= "<li><fieldset class=\"check-value\"><input type=\"text\" name=\"filter_startdate\" id=\"filter_startdate\" data-type=\"date\" readonly=\"readonly\" title=\"Filter by a specific start and end date\" value=\"".(isset($a['filter_startdate']) && $a['filter_startdate'] != ""?$a['filter_startdate']:"")."\" /><label>Start Date</label><p>DATE</p></fieldset> <fieldset class=\"check-value\"><input type=\"text\" name=\"filter_enddate\" id=\"filter_enddate\" data-type=\"date\" readonly=\"readonly\" title=\"Filter by a specific start and end date\" value=\"".(isset($a['filter_enddate']) && $a['filter_enddate'] != ""?$a['filter_enddate']:"")."\" /><label>End Date</label><p>DATE</p></fieldset></li>";

          // filter by keyword
          $return .= "<li><fieldset><input id=\"neotags\" type=\"text\" title=\"Filter using specific keywords\" class=\"tags\" value=\"".(isset($a['filter_keyword'])?$a['filter_keyword']:"")."\" /> <p>TAG</p>   <input type=\"hidden\" name=\"filter_keyword\" id=\"filter_keyword\" title=\"Filter using specific keywords\" value=\"".(isset($a['filter_keyword'])?$a['filter_keyword']:"")."\" /></fieldset></li>";

          // add a clear and submit button
          $return .= "<li><input type=\"submit\" value=\"Apply Filter\" title=\"Click here to apply your filter parameters\" data-type=\"filter_submit\" /> <input type=\"button\" value=\"Reset\" data-type=\"filter_cancel\" title=\"Click here to clear your filter\" /></li>";

          return $return;
        }





        // this function will take a supplied array of labels and values and turn them into options in a select field
        // tool = the name of the tool that we are working with
        // fieldname = the name of the specific field we are interested in
        // value = the current vlaue of the field to set selected on if a match is found
        function DROPDOWN_FILL($tool,$fieldname,$value){
          $data = json_decode($this->DB_READFILE("config/tools.json"),true);
          $data = $data[strtoupper($tool)]['fields'][$fieldname]['options'];
          $return = "";
          if(is_array($data) === true){
            foreach($data as $d){
              $return .= "<option value=\"".$d[1]."\" ".($value != "" && $value == $d[1]?"selected=\"selected\"":"").">".$d[0]."</option>";
            }
          }else{
            $dataParts = explode(":",$data);
            $theseFields = explode(",",$dataParts[1]);
            $getFields = array("id");
            foreach($theseFields as $tF){
              $getFields[] = $dataParts[0]."_".$tF;
            }
            $this->basePath = getcwd()."/";
            $res = $this->DB_CONTENT_GET($dataParts[0],$getFields,array($dataParts[0]."_".$theseFields[0]),array("ASC"),false);
            foreach($res as $r){
              $optionTitle = "";
              $resKeys = array_keys($r['content']);
              foreach($resKeys as $rK){
                if($rK != "id"){
                  $optionTitle .= ($optionTitle != ""?" - ":"").$r['content'][$rK];
                }
              }
              $return .= "<option value=\"".$r['content']["id"]."\" ".($value != "" && $value == $r['content']["id"]?"selected=\"selected\"":"").">".$optionTitle."</option>";
            }
          }
          return $return;
        }





        // this function will fill in time options for time fields
        // a = are we looking to generate hour or minute values
        // value = the current value of the field to set selected on if a match is found
        function TIME_FILL($a="H",$value=""){
          $return = "";
          // will be based on 24 hour clock, but will show the am/pm translation for hours
          if($a == "H"){
            for($i=0;$i<24;$i++){
              $v = ($i<10?"0".$i:$i);
              $return .= "<option value=\"".$v."\" ".($value != "" && $value == $v?"selected=\"selected\"":"").">".$v."</option>";
            }
          }else if($a == "M"){
            for($i=0;$i<60;$i++){
              $v = ($i<10?"0".$i:$i);
              $return .= "<option value=\"".$v."\" ".($value != "" && $value == $v?"selected=\"selected\"":"").">".$v."</option>";
            }
          }
          return $return;
        }





        // this function will build a full or partial list of states
        function STATES_LIST($a,$b,$c){
          $return = "";
          $keys = array_keys($a);
          $cnt = 0;
          foreach($a as $state){
            $return .= "<option value=\"".$keys[$cnt]."\" ".(isset($c) && $c == $keys[$cnt]?"selected=\"selected\"":"").">".ucwords(strtolower($state))."</option>";
            $cnt++;
          }
          return $return;
        }
    }
?>
