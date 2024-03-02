<?php



  // need to look into locking a file when an edit is requested to prevent overlapping edits!!!!!!!!!!!!!!!!!!!!!!!!!!












    $db = new DB($basePath);

    // this will handle all things read from and writing to the data source files
    class DB{

      // set the base path of where we can access data, etc.
      var $basePath = "";


      // class constructor that gets called by default along with instantiation of class
      function __construct($a){
        //echo "hello world!";
        //echo $a;
        $this->basePath = $a;
      }





      // this function will generate a new id to be used to store a record of data
      function DB_GENERATEID(){
        return md5(microtime(true));
      }





      // this is a quick way to set the base path that we may need to use along the way
      function DB_SETBASEPATH($a){
        $this->basePath = $a;
      }





      // this function will sort data that is passed in along with the direction to order it in
      function DB_SORT($key,$dir){
        if($dir === "ASC"){
          return function($a, $b)use($key){
            return strnatcmp($a['content'][$key], $b['content'][$key]);
          };
        }else{
          return function($a, $b)use($key){
            return strnatcmp($b['content'][$key],$a['content'][$key]);
          };
        }
      }





      // this function will order the return to filter lists
      function DB_SORT_LIST($key,$dir){
        if($dir === "ASC"){
          return function($a, $b)use($key){
            return strnatcmp($a[$key], $b[$key]);
          };
        }else{
          return function($a, $b)use($key){
            return strnatcmp($b[$key],$a[$key]);
          };
        }
      }





      // this method will gather up data for the requested content
      // the tool that we want to find data for
      // the fields that we would like returned from the data set
      // the fields that we would like to order the data by (array)
      // the direction that we would like to order the data by (array)
      // the where clause to return select content results
      // a limit value to tell us the index to start on in the results and then how many to return (null for all)
      // whether we want to gather up media items related to the records that we are looking at
      function DB_CONTENT_GET($tool,$fields="*",$orderby,$orderdir,$getMedia=false,$where="",$limit=""){
        $return = array();

        $limitReturn = false;

        // read through the recs file to determine everything that we will need to look through
        // this needs to be changed to only look at JSON rather than plain text as that is slow
        if(file_exists($this->basePath."/data/".strtolower($tool)."/data.json")){
          $file = fopen($this->basePath."/data/".strtolower($tool)."/data.json", "r");
          $recs = array();

          $i = 0;

          // let's figure out our limits if any
          if(isset($limit) && $limit != ""){
            if(isset($limit[1]) && $limit[1] > 0){
              $limitReturn = intVal($limit[1]);
            }
          }

          while(!feof($file)){
            $line = fgets($file);
            if($line != ""){
              $recs[] = str_replace(array("\n"),"",$line);
              $i++;
            }
          }
          fclose($file);

          foreach($recs as $r){
            // $data = $this->DB_READFILE("data/".strtolower($tool)."/recs/".$r.".json");
            // $fArray = $this->DB_JSON_DECODE($data,true);  // extract the record data
            $fArray = json_decode($this->DB_READFILE("data/".strtolower($tool)."/recs/".$r.".json"),true);
            $fKeys = array_keys($fArray); // key out the data so that we can target elements
            $returnThis = true;

            if(is_array($fields) && count($fields) > 0){  // we have been given an array of fields that we want to include in the return

              if(isset($where) && $where != ""){  // we are checking a where clause
                $returnThis = false;
                foreach($where as $w){
                  if(strpos($w," = ") > 0){
                    $whereParts = explode("=",$w);
                    $orWhere = explode("||",$whereParts[1]);
                    $oMatch = false;
                    foreach($orWhere as $oW){
                      if(strtoupper($fArray[trim($whereParts[0])]) == strtoupper(trim($oW))){
                        $returnThis = true;
                        $oMatch = true;
                      }
                    }
                    if(!$oMatch){
                      $returnThis = false;
                      break;
                    }
                  }else if(strpos($w," ! ") > 0){
                    $whereParts = explode("!",$w);
                    $orWhere = explode("||",$whereParts[1]);
                    $oMatch = false;
                    foreach($orWhere as $oW){
                      if(strtoupper($fArray[trim($whereParts[0])]) != strtoupper(trim($oW))){
                        $returnThis = true;
                        $oMatch = true;
                      }
                      else{
                        $oMatch = false;
                        $returnThis = false;
                        break;
                      }
                    }
                    if(!$oMatch){
                      $returnThis = false;
                      break;
                    }
                  }else if(strpos($w," < ") > 0){ // lt's check for a range search
                    $whereParts = explode("<",$w);
                    $oMatch = false;
                    if(floatval(trim($whereParts[0])) < floatval($fArray[trim($tool)."_".trim($whereParts[1])]) && floatval($fArray[trim($tool)."_".trim($whereParts[1])]) < floatval(trim($whereParts[2]))){
                      $returnThis = true;
                      $oMatch = true;
                    }
                    else{
                      $oMatch = false;
                      $returnThis = false;
                      break;
                    }
                    if(!$oMatch){
                      $returnThis = false;
                      break;
                    }
                  }
                }
                if($returnThis){
                  foreach($fields as $fld){
                    if($fld != "id"){
                      $rArray[$fld] = $fArray[$fld];
                    }
                  }
                  $rArray["id"] = $r;
                  $return[$r] = array("content" => $rArray);
                }
              }else{  // show it all, there is no where clause
                foreach($fields as $fld){
                  if($fld != "id"){
                    $rArray[$fld] = $fArray[$fld];
                  }
                }
                $rArray["id"] = $r;
                $return[$r] = array("content" => $rArray);
              }
            }else{  // we are simply going to return everything

              if(isset($where) && $where != ""){  // we are checking where clause options
                $returnThis = false;
                //print_r($where);
                foreach($where as $w){
                  //echo $w;
                  if(strpos($w," = ") > 0){
                    $whereParts = explode("=",$w);
                    $orWhere = explode("||",$whereParts[1]);
                    $oMatch = false;
                    foreach($orWhere as $oW){
                      if(strtoupper($fArray[trim($whereParts[0])]) == strtoupper(trim($oW))){
                        $returnThis = true;
                        $oMatch = true;
                      }
                      else{
                        $oMatch = false;
                        $returnThis = false;
                        break;
                      }
                    }
                    if(!$oMatch){
                      $returnThis = false;
                      break;
                    }
                  }else if(strpos($w," ! ") > 0){
                    $whereParts = explode("!",$w);
                    $orWhere = explode("||",$whereParts[1]);
                    $oMatch = false;
                    foreach($orWhere as $oW){
                      if(strtoupper($fArray[trim($whereParts[0])]) != strtoupper(trim($oW))){
                        $returnThis = true;
                        $oMatch = true;
                      }
                      else{
                        $oMatch = false;
                        $returnThis = false;
                        break;
                      }
                    }
                    if(!$oMatch){
                      $returnThis = false;
                      break;
                    }
                  }else if(strpos($w," < ") > 0){ // lt's check for a range search
                    $whereParts = explode("<",$w);
                    //echo $tool;
                    //print_r($whereParts);
                    //echo floatval($fArray[trim($tool)."_".trim($whereParts[1])])."\n";
                    $oMatch = false;
                    // if(floatval(trim($whereParts[0])) < floatval($fArray[trim($tool)."_".trim($whereParts[1])]) && floatval($fArray[trim($tool)."_".trim($whereParts[1])]) < floatval(trim($whereParts[2]))){
                    if(floatval($fArray[trim($tool)."_".trim($whereParts[1])]) > floatval(trim($whereParts[0])) && floatval($fArray[trim($tool)."_".trim($whereParts[1])]) < floatval(trim($whereParts[2]))){
                      $returnThis = true;
                      $oMatch = true;
                    }
                    else{
                      $oMatch = false;
                      $returnThis = false;
                      break;
                    }
                    if(!$oMatch){
                      $returnThis = false;
                      break;
                    }
                  }
                }
                if($returnThis){
                  $rArray = $fArray;
                  $rArray["id"] = $r;
                  $return[$r] = array("content" => $rArray);
                }
              }else{  // show it all, there is no where clause
                $rArray = $fArray;
                $rArray["id"] = $r;
                $return[$r] = array("content" => $rArray);
              }
            }

            // is the user requesting that we also return related media for the record we are looking at
            if($getMedia === true && file_exists($this->basePath."/data/media/relations/".$r.".json") && $returnThis === true){
              $media = array();
              $mediaRecs = json_decode($this->DB_READFILE("data/media/relations/".$r.".json"),true);
              $mediaCats = array_keys($mediaRecs);
              $cnt = 0;
              foreach($mediaRecs as $mR){
                $cntt = 0;
                $media[$mediaCats[$cnt]] = $mR;
                foreach($mR as $mediaRec){
                  $file = json_decode($this->DB_READFILE("data/media/recs/".$mediaRec.".json"),true);
                  $media[$mediaCats[$cnt]][$cntt] = $file;
                  $cntt++;
                }
                $cnt++;
              }
              $return[$r]["media"] = $media;  // add a media section to return array
            }
          }
          if(isset($orderby) && count($orderby) > 0 && isset($orderdir) && count($orderdir) > 0){
            $cnt = 0;
            foreach($orderby as $oB){
              if(!empty($oB)) :
                usort($return,$this->DB_SORT($oB,$orderdir[$cnt]));
                $cnt++;
              endif;
            }
          }
        }

        if(!$limitReturn){
          return $return;
        }else{  // we are not limiting the return results
          $limited = [];
          for($l=$limit[0];$l < ($limit[0] + $limitReturn);$l++){
            if(isset($return[$l]) && $return[$l] != "''"){
              $limited[] = $return[$l];
            }
          }
          return $limited;
        }
      }



      // this will update a specific record worth of information
      // tool
      // record id to look at
      // the where clause to set updates content results
      function DB_RECORD_UPDATE($tool,$recId,$where){
        $return = "";
        if($tool === "users"){
          // $data = $this->DB_READFILE("security/users/recs/".$recId.".json");
          // $return = $this->DB_JSON_DECODE($data,true);
          $return = json_decode($this->DB_READFILE("security/users/recs/".$recId.".json"),true);
        }else{
          $file = "data/".strtolower($tool)."/recs/".$recId.".json";
          $record = json_decode($this->DB_READFILE($file),true);
          // update record values
          if (!empty($record)) {
            if(isset($where) && $where != ""){  // we are checking where clause options
              $whereParts = array();
              foreach($where as $w){
                if(strpos($w," .= ") > 0){
                  $whereParts = explode(".=",$w);
                  ($record[trim($whereParts[0])] ? $record[trim($whereParts[0])] = $record[trim($whereParts[0])].','.trim($whereParts[1]) : $record[trim($whereParts[0])] = trim($whereParts[1]));
                } elseif (strpos($w," ?= ") > 0) {
                  $whereParts = explode("?=",$w);
                  ( isset($record[trim($whereParts[0])]) ? $record[trim($whereParts[0])][] = trim($whereParts[1]) : $record[trim($whereParts[0])][] = trim($whereParts[1]));
                } elseif (strpos($w," !?= ") > 0) {
                  $whereParts = explode("!?=",$w);
                  if (($key = array_search(trim($whereParts[1]), $record[trim($whereParts[0])])) !== false) {
                    unset($record[trim($whereParts[0])][$key]);
                  }
                } else {
                  $whereParts = explode("=",$w);
                  ($record[trim($whereParts[0])] ? $record[trim($whereParts[0])] = $whereParts[1] : '');
                }
              }
            }
            $data = "{\n\t";
            $logData = "";
            $keys = array_keys($record);
            $cnt = 0;
            foreach($record as $r){
              if (is_array($r)) {
                $logDataA = '';
                $data .= ($cnt > 0?",\n\t":"")."\"".$keys[$cnt]."\":{";
                $logDataA .= '{';
                foreach ($r as $k => $v) {
                  $data .= "\"".$k."\"".":"."\"".str_replace("'", "", $v)."\"";
                  $logDataA .= str_replace("'", "", $v);
                  if($v != end($r)) : $data .= ","; $logDataA .= ","; endif;
                }
                $logDataA .= '}';
                $data .="}";
                $logData .= ($cnt > 0?",":"").$logDataA;
              } elseif (strpos($w," !?= ") > 0) {
                  $whereParts = explode("!?=",$w);
                  if (($key = array_search(trim($whereParts[1]), $record[trim($whereParts[0])])) !== false) {
                    unset($record[trim($whereParts[0])][$key]);
                  }
              } else {
                $data .= ($cnt > 0?",\n\t":"")."\"".$keys[$cnt]."\":\"".trim($r)."\"";
                $logData .= ($cnt > 0?",":"").$r;
              }
              $cnt++;
            }
            $data .= "\n}";
            $file = fopen($this->basePath.$file,"w+");
            $result = fwrite($file,$data);
            fclose($file);
            if(!$result){
              $return = 'error during saveing';
            } else {
              $return =  true;
            }
          } else {
            $return = 'record not found';
          }
          return $return;
        }
      }



      // this will get a specific record worth of information
      // tool
      // record id to look at
      // do we want to show media or not
      function DB_RECORD_GET($tool,$recId,$getMedia = false){
        $return = "";
        if($tool === "users"){
          // $data = $this->DB_READFILE("security/users/recs/".$recId.".json");
          // $return = $this->DB_JSON_DECODE($data,true);
          $return = json_decode($this->DB_READFILE("security/users/recs/".$recId.".json"),true);
        }else{
          // $data = $this->DB_READFILE("data/".strtolower($tool)."/recs/".$recId.".json");
          // $return = $this->DB_JSON_DECODE($data,true);
          $return = json_decode($this->DB_READFILE("data/".strtolower($tool)."/recs/".$recId.".json"),true);

          // do we want to return media as well?
          if($getMedia === true && file_exists($this->basePath."/data/media/relations/".$recId.".json")){
            $media = array();
            $recs = json_decode($this->DB_READFILE("data/media/relations/".$recId.".json"),true);
            //print_r($recs);
            $tempKeys = array_keys($recs);
            $cnt=0;
            foreach($recs as $rec){
              //print_r($rec[0]);
              foreach($rec as $r){
                //print_r($r);
                $item = json_decode($this->DB_READFILE("data/media/recs/".$r.".json"),true);
                $media[$tempKeys[$cnt]][] = $item;
              }
              //print_r($item);
              $cnt++;
            }
            $return['media'] = $media;
          }

        }
        return $return;
      }





      // this function will gather up the records for a specific tool as a dropdown list
      // need a way to pass in only what we want to show in the list, and how to order/group them if necessary
      function DB_CONTENT_LIST($tool,$rid,$showMe){
        $file = fopen("data/".strtolower($tool)."/data.json", "r");
        $recs = array();
        while(!feof($file)){
          $line = fgets($file);
          if($line != ""){
            $recs[] = str_replace(array("\n"),"",$line);
          }
        }
        fclose($file);

        // how do we want to order the returned set of records
        // $data = $this->DB_READFILE("config/tools.json");
        // $orderBy = $this->DB_JSON_DECODE($data,true);
        $orderBy = json_decode($this->DB_READFILE("config/tools.json"),true);
        $ordered = array();
        $cnt = 0;
        foreach($recs as $r){
          // $data = $this->DB_READFILE("data/".strtolower($tool)."/recs/".$r.".json");
          // $data = $this->DB_JSON_DECODE($data,true);
          $data = json_decode($this->DB_READFILE("data/".strtolower($tool)."/recs/".$r.".json"),true);
          $data['rid'] = $r;
          $ordered[] = $data;
          $cnt++;
        }

        // if we want to force an order on the results
        // sort the results that are coming back to be shown in the list
        usort($ordered,$this->DB_SORT_LIST($showMe[0],"ASC"));

        // build out the dropdown list of items to edit
        $return = "<form class=\"neo__formlist neo__forms\"><fieldset name=\"editme\" id=\"editme\"><label>Select Item To Edit</label><select name=\"selecttoedit\" class=\"neochosen\" id=\"selecttoedit\"><option>----------</option>";

        foreach($ordered as $o){
          // $data = $this->DB_READFILE("data/".strtolower($tool)."/recs/".$o['rid'].".json");
          // $data = $this->DB_JSON_DECODE($data,true);
          $data = json_decode($this->DB_READFILE("data/".strtolower($tool)."/recs/".$o['rid'].".json"),true);
          if(trim(strtolower($tool)) != "users" || (trim(strtolower($tool)) === "users" && $o['users_permissions_level'] != "D")){  // hide developer
            $rec = "";
            $fields = array_keys($data);
            if(isset($showMe) && $showMe != ""){

              foreach($showMe as $f){

                // check to see if we are looking for find cross-linked content in another tool or not
                $checkMe = $orderBy[strtoupper($tool)]['fields'][str_replace(strtolower($tool)."_","",$f)];
                if($checkMe['type'] === "select" && count($checkMe['options']) == 1){
                  $searchParts = explode(":",$checkMe['options']);
                  // $linkedData = $this->DB_READFILE("data/".strtolower($searchParts[0])."/recs/".$data[$f].".json");
                  // $linkedData = $this->DB_JSON_DECODE($linkedData,true);
                  $linkedData = json_decode($this->DB_READFILE("data/".strtolower($searchParts[0])."/recs/".$data[$f].".json"),true);
                  $rec .= ($rec != "" && $linkedData[$searchParts[0]."_".$searchParts[1]] != ""?" - ":"").$linkedData[$searchParts[0]."_".$searchParts[1]];
                }else{
                  $rec .= ($rec != "" && $data[$f] != ""?" - ":"").$data[$f];
                }

              }

            }else{  // just show the first field if nothing was speciically selected as this is generally a name or title
              $rec .= ($rec != "" && $data[$fields[0]] != ""?" - ":"").$data[$fields[0]];
            }
            $return .= "<option value=\"".$o['rid']."\" ".($rid == $o['rid']?"selected=\"selected\"":"").">".strip_tags(html_entity_decode($rec))."</option>";
          }
        }
        $return .= "</select><p>Please select an item from the list above that you would like to view</p></fieldset></form>";
        return $return;
      }





      // this function will decode json data
      function DB_JSON_DECODE($a="",$b=true){
        //echo $a;
        //die();
        return json_decode($a,$b);
      }





      // this function will read in a json file to memory so that we can use it for something
      function DB_READFILE($a){
        if(file_exists($this->basePath.$a)) {
          return file_get_contents($this->basePath.$a);
        }
      }


      // this function implodes an array with optional key inclusion
      function DB_REC_IMPLODE(array $array, $glue = ',', $include_keys = false, $trim_all = true) {
      	$glued_string = '';
      	// Recursively iterates array and adds key/value to glued string
      	array_walk_recursive($array, function($value, $key) use ($glue, $include_keys, &$glued_string)
      	{
      		$include_keys and $glued_string .= $key.$glue;
      		$glued_string .= $value.$glue;
      	});
      	// Removes last $glue from string
      	strlen($glue) > 0 and $glued_string = substr($glued_string, 0, -strlen($glue));
      	// Trim ALL whitespace
      	$trim_all and $glued_string = preg_replace("/(\s)/ixsm", '', $glued_string);
      	return (string) $glued_string;
      }


      // this will force the app to die and generate a critical error message on the screen
      // a = the array of messages to show when the app dies
      function APP_DIE($a=""){
        $msg = "CRITICAL ERROR".(count($a) > 1?"S":"")." DETECTED<br /><br />The following critical error".(count($a) > 1?"s":"")." ".(count($a) > 1?"were":"was")." detected:<br />";
        foreach($a as $errorMsg){
          $msg .= "- ".$errorMsg."<br />";
        }

        $msg .= "<br />Please email ".SUPPORTEMAIL." for assistance and provide the content of this error message.";
        die($msg);
      }





    }
?>
