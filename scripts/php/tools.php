<?php


  $t = new TOOLS($l);

  // this will handle all things read from and writing to the data source files
  class TOOLS extends DB{


    var $dataPath = "";
    private $logs;


    // class constructor that gets called by default along with instantiation of class
    function __construct(LOGS $a) {
        $this->logs = $a;
    }





    // this function will get a list of all of the tools available to the user
    // a = whether or not we want to format the list or just get records back
    // b = the id of the user that is logged in so that we can verify that they have access to certain tools
    function TOOLS_LIST($a=false){
        $data = json_decode($this->DB_READFILE("config/tools.json"),true);
        $keys = array_keys($data);
        if($a){
          $return = "<ul data-title=\"tools\">";
        }else{
          $return = array();
        }
        $cnt = 0;
        foreach($data as $d){
          if(trim(strtolower($keys[$cnt])) != "users"){

            $options = $data[$keys[$cnt]]['options'];

            // check to see if there are any related records for edit or remove, and if not make the option inactive as a tool!!!
            if(file_exists("data/".trim(strtolower($keys[$cnt]))."/data.json")){

                // this needs to be converted to read each row of the file and build an array to loop through later
                $file = fopen("data/".trim(strtolower($keys[$cnt]))."/data.json", "r");
                $i = 0;
                while(!feof($file)){
                    $line = fgets($file);
                    $i++;
                }
                fclose($file);
            }
            if($a){
              $actions = "";
              foreach($options as $t){
                  $actions .= " / <a href=\"tool.php?tool=".trim(strtolower($keys[$cnt]))."&action=".strtolower($t).(strtolower($t) === "add"?"&rid=".$this->DB_GENERATEID():"")."\" title=\"".ucwords(strtolower($t)." ".trim($d['title']))."\">".$t."</a>";
              }
              //$return .= "<li data-id=\"".trim(strtoupper($keys[$cnt]))."\">".trim($d['title']).":".$actions." [<a href=\"export.php?tool=".trim(strtolower($keys[$cnt]))."\" title=\"Click here to export all data for this tool\" target=\"_blank\">EXPORT</a>]</li>";
              $return .= "<li data-id=\"".trim(strtoupper($keys[$cnt]))."\">".trim($d['title']).":".$actions." / <a href=\"export.php?tool=".trim(strtolower($keys[$cnt]))."\" title=\"Click here to export all data for this tool\" target=\"_blank\">EXPORT</a></li>";
              //$return .= "<li data-id=\"".trim(strtoupper($keys[$cnt]))."\">".trim($d['title'])."".$actions."</li>";
            }else{
              $return[] = array(trim(strtoupper($keys[$cnt])),trim($d['title']),$options);
            }
          }
          $cnt++;
        }
        if($a){
          $return .= "</ul>";
        }
        return $return;
    }



    // this function will get a list of all of the tools available to the user
    // a = whether or not we want to format the list or just get records back
    // b = the id of the user that is logged in so that we can verify that they have access to certain tools
    function TOOLS_LEFTLIST($a=false){
      $data = json_decode($this->DB_READFILE("config/tools.json"),true);
      $keys = array_keys($data);
      if($a) {
        $return = "<ul data-title=\"tools\">";
      } else {
        $return = array();
      }
      $cnt = 0;
      foreach($data as $d) {
        if(trim(strtolower($keys[$cnt])) != "users"){
          $options = $data[$keys[$cnt]]['options'];
          // check to see if there are any related records for edit or remove, and if not make the option inactive as a tool!!!
          if(file_exists("data/".trim(strtolower($keys[$cnt]))."/data.json")){
            // this needs to be converted to read each row of the file and build an array to loop through later
            $file = fopen("data/".trim(strtolower($keys[$cnt]))."/data.json", "r");
            $i = 0;
            while(!feof($file)){
                $line = fgets($file);
                $i++;
            }
            fclose($file);
          }
          if($a) {
            $actions = "";
            foreach($options as $t) {
              $actions .="<li><a href=\"tool.php?tool=".trim(strtolower($keys[$cnt]))."&action=".strtolower($t).(strtolower($t) === "add"?"&rid=".$this->DB_GENERATEID():"")."\" title=\"".ucwords(strtolower($t)." ".trim($d['title']))."\">".$t."</a></li>";
            }
            $actions .="<li><a href=\"export.php?tool=".trim(strtolower($keys[$cnt]))."\" title=\"Click here to export all data for this tool\" target=\"_blank\">EXPORT</a></li>";
            $return .= '<li>';
            $return .= '<a href="#"><i class="fa fa-folder-o"></i>'.trim($d['title']).'</a>';
            $return .= '<h2><i class="fa fa-folder-o"></i>'.trim($d['title']).'</h2>';
            $return .= '<ul>';
            $return .= $actions;
            $return .= '</ul>';
            $return .= '</li>';
          } else {
            $return[] = array(trim(strtoupper($keys[$cnt])),trim($d['title']),$options);
          }
        }
        $cnt++;
      }
      if($a) {
        $return .= "</ul>";
      }
      return $return;
    }




    // this will handle saving a new record of data for a specific tool, or editing an existing record
    // a = the id of the record
    // b = the post data that we need to work with
    // c = the tool being used
    // d = the action (add, edit, remove)
    function TOOLS_SAVE($a="",$b=[],$c="",$d=""){

      $this->dataPath = "data/".strtolower($c);

      if($a != "" && count($b) > 1 && $c != "" && $d != ""){

        // check to see if the data file exists for the specified record, if not, create it below, otherwise do nothing
        if(!file_exists($this->dataPath."/data.json")){
            mkdir($this->dataPath,0755,true);     // create the folder, we may need to update the perms as this is really wide open
            $file = fopen($this->dataPath."/data.json","w");
            fwrite($file,"");
            fclose($file);
            mkdir($this->dataPath."/recs",0755,true); // create the folder to hold the actual data records in it
        }

        // add a new record if a record file is not found
        if(!file_exists($this->dataPath."/recs/".$a.".json")){
            $file = fopen($this->dataPath."/data.json","a");
            $data = $a."\n";
            fwrite($file,$data);
            fclose($file);
        }

        $file = fopen($this->dataPath."/recs/".$a.".json","w+"); // this will decide if we are creating a new record OR updating an existing one
        $data = "{\n\t";
        $keys = array_keys($b);
        $cnt = 0;
        foreach($b as $p){
          // check the user password
          if(strtolower($keys[$cnt]) === "users_password"):$p = md5($p);endif;
          //$data .= ($cnt > 0?",\n\t":"")."\"".$keys[$cnt]."\":\"".addslashes($p)."\"";
          //$data .= ($cnt > 0?",\n\t":"")."\"".$keys[$cnt]."\":\"".htmlentities($p, ENT_QUOTES)."\"";  // convert quotes in JSON
          // check if the data coming in array
          if (is_array($p)) {
            $vArray = array();
            foreach ($p as $v) : $vArray[] = htmlentities(str_replace(array("\n","\t","\r"),"",$v), ENT_QUOTES); endforeach;
            $value = json_encode($vArray, JSON_FORCE_OBJECT);
          } else {
            $value = '"'.htmlentities(str_replace(array("\n","\t","\r"),"",$p), ENT_QUOTES).'"';
          }
          $data .= ($cnt > 0?",\n\t":"")."\"".$keys[$cnt]."\":".$value."";  // convert quotes in JSON
          $cnt++;
        }
        $data .= "\n}";
        fwrite($file,$data);
        fclose($file);
        //$logData = implode(",",json_decode($this->DB_READFILE($this->dataPath."/recs/".$a.".json"),true));
        $arr = json_decode($this->DB_READFILE($this->dataPath."/recs/".$a.".json"),true);
        $logData = $this->DB_REC_IMPLODE($arr);
        $this->logs->LOGTHIS(["activity"],strtoupper($c)."|".strtoupper($d)."|".$logData."|".$a);
        return true;
      } else {
        $this->logs->LOGTHIS(["errors"],strtoupper($c)."|SAVE ERROR|Null or invalid record id or tool provided|".$a);
        return false;
      }
    }





    // this will remove a specific tools record from the system
    // a = the id of the record to purge
    // b = the tool that we are working with
    function TOOLS_REMOVE($a="",$b=""){
      if($a != "" && $b != ""){

        $this->dataPath = "data/".strtolower($b);

        $logData = implode(",",json_decode($this->DB_READFILE($this->dataPath."/recs/".$a.".json"),true));

        if($logData == ""){
          $logData = "data unavailable";
        }

        // clear the record from the data files
        $file = $this->dataPath."/data.json";
        $file_contents = file_get_contents($file);
        $fh = fopen($file,"w");
        $file_contents = str_replace(array($a."\n"),"",$file_contents);
        $result = fwrite($fh, $file_contents);
        fclose($fh);
        if(!$result){ // failed to update the data file
          $this->logs->LOGTHIS(["errors"],strtoupper($b)."|WRITE ERROR|".$logData."|".$a);
          return false;
        }else{  // data file was updated, moving on
          // we need to log this first BEFORE the record pointer is removed below
          //$logData = implode(",",json_decode($this->DB_READFILE($this->dataPath."/recs/".$a.".json"),true));
          $result = unlink($this->dataPath."/recs/".$a.".json");  // unlink the existing file
          if(!$result){ // failed to remove record file
            $this->logs->LOGTHIS(["errors"],strtoupper($b)."|DELETE ERROR|".$logData."|".$a);
            return false;
          }else{  // success
            $this->logs->LOGTHIS(["security","activity"],strtoupper($b)."|REMOVE|".$logData."|".$a);
            return true;
          }
        }
      }else{
        $this->logs->LOGTHIS(["errors"],strtoupper($b)."|DELETE ERROR|Null or invalid record id or tool provided|".$a);
        return false;
      }
    }



    // this will handle get all records of data for a specific tool, or editing an existing record
    // a = the tool being used
    // b = the action (add, edit, remove)
    function TOOLS_ALL($a="",$b=""){
      if($a != "" && $b != ""){
        // get the listby
        $listby = array();
        $tools = file_get_contents("config/tools.json");
        $list = json_decode($tools, true);
        foreach ($list[strtoupper($a)]['fields'] as $k => $v) {
          if (isset($v['listall'])) : $listby[] = $k; endif;
        }
        $return = "";
        $this->dataPath = "data/".strtolower($a);
        // check to see if the data file exists for the specified record, if not, create it below, otherwise do nothing
        if(file_exists($this->dataPath."/data.json") && !empty($listby)) {
          $s = file_get_contents($this->dataPath."/data.json");
          preg_match_all('/"[^"]+"|\S+/', $s, $matches);
          if (is_array($matches[0])) {
            $return .= "<table class=\"neotable display\" cellspacing=\"0\" width=\"100%\">";
            foreach ($matches[0] as $key => $value) {
              $file = $this->dataPath."/recs/".$value.'.json';
              if(file_exists($file)) {
                $json = file_get_contents($file);
                $data = json_decode($json, true);
                if ($key==0) {
                  $return .="<thead><tr>";
                    foreach ($data as $k => $v) :
                      $field = str_replace($a."_","",$k);
                      if (in_array($field, $listby)) : $return .="<th>" . $field . "</th>"; endif;
                    endforeach;
                  $return .="<th>Actions</th></tr></thead>";
                  $return .="<tfoot><tr>";
                    foreach ($data as $k => $v) :
                      $field = str_replace($a."_","",$k);
                      if (in_array($field, $listby)) : $return .="<th>" . $field . "</th>"; endif;
                    endforeach;
                  $return .="<th>Actions</th></tr></tfoot><tbody>";
                }
                $return .="<tr>";
                $i = 0;
                foreach ($data as $k => $v) :
                  $field = str_replace($a."_","",$k);
                  if (in_array($field, $listby)) :
                    $return .="<td>".$data[$a.'_'.$listby[$i]]."</td>";
                    $i++;
                  endif;
                endforeach;
                  $return .="<td class=\"action\"><a href=\"tool.php?tool=".trim(strtolower($a))."&action=edit&rid=".$value."\" title=\"Edit\" ><i class=\"fa fa-edit\" ></i></a><a href=\"tool.php?tool=".trim(strtolower($a))."&action=remove&rid=".$value."\" title=\"Delete\" ><i class=\"fa fa-trash-o\" ></i></a></td>";
                $return .="</tr>";
              }
            }
            $return .="</tbody></table>";
          }
        }
        return $return;
      }
    }



  }
?>
