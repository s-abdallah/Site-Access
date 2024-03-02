<?php


  $l = new LOGS($e);

  // this will handle all things read from and writing to the data source files
  class LOGS extends DB{


    // var $dataPath = "data/users";
    //var $dataPath = "security/logs";


    // class constructor that gets called by default along with instantiation of class
    // function __construct($a="") {
    //     //$this->basePath = $a;
    // }

    function __construct(EMAIL $a){
        $this->email = $a;
    }





    // this will build a csv file for download
    // a = the type of log to use as the soure
    // b = the name of the file to be used
    // c = the column headers to use
    function LOGCSV($a="",$b="",$c=[]){
      if($a != "" && $b != "" && count($c) > 0){
        $return = fopen('php://output', 'w');
        fputcsv($return, $c);
        $recs = $this->LOG_LIST($a,0,false);
        foreach($recs as $r){
          $data = explode("|",$r);
          fputcsv($return, array($data[0],$data[1],$data[2],$data[3],$data[5]));
        }
        $this->LOGTHIS(["security"],"LOGS|CSV|CSV file created: ".$b."|00000-0");  // log this action
        return $return;
      }else{
        $this->LOGTHIS(["errors"],"LOGS|CSV|Could not create requested file: ".$b."|00000-0");  // log this action
      }
    }





    // this will generate a log file for tracking purposes
    // a = the type of log file that we want to write
    // b = the data to be logged
    function LOGTHIS($a="",$b=""){
      foreach($a as $log){
        // get the user data for the person who performed the action
        //$user = json_decode($this->DB_READFILE("security/users/recs/".strtolower($_SESSION['userid']).".json"),true);
        // $data = $this->DB_READFILE("security/users/recs/".strtolower($_SESSION['userid']).".json");
        // $user = $this->DB_JSON_DECODE($data,true);
        // $file = fopen("security/logs/".strtolower($log)."/data.json","a");
        // fwrite($file,$user['users_full_name']."|".$b."|".date("Y-m-d H:i:s")."\n");
        // fclose($file);
        // $result = $this->LOG_WRITE(strtolower($log),$b);

        $b = strip_tags($b);  // strip any tags out of the data to be written to the logs as we don't care about it here

        $result = $this->LOG_WRITE(strtolower($log),$b);  // we were unable to write a log entry for some reason
        if($result === false){
          $eresult = $this->email->EMAIL_BUILDMESSAGE(SUPPORTEMAIL,"Sitecontrol Error Detected In ".PROJECTNAME,"The following error notification was detected:\n\nThere was an issue writing data to the ".$log." log.\n\nLog data: ".$b,true);
          //if($eresult !== true){
            //die("Critical errors were encountered:<br /><br />- unable to write to ".$log." log");
            //$this->LOG_DIE(["unable to write to ".$log." log","unable to send error email to ".SUPPORTEMAIL]);
          //}
          $reason = array("unable to write to ".$log." log, data = ".$b);
          if($eresult !== true){$reason[] = "unable to send error email to ".SUPPORTEMAIL;}
          $this->APP_DIE($reason);
          //return $result; // prevent the code from going any further
        }


        // do we need to send an email if we are dealing with a security or error issue?
        if(SUPPORTEMAIL && SUPPORTEMAIL != "" && strtolower($log) === "error" && $result !== false){ // email to support that something went wrong
          //$message = "Sitecontrol Error Detected In ".PROJECTNAME,"The following error notification was detected:\n\n".$b;
          $result = $this->email->EMAIL_BUILDMESSAGE(SUPPORTEMAIL,"Sitecontrol Error Detected In ".PROJECTNAME,"The following error notification was detected:\n\n".$b,true);
          if($result !== true){ // we could not send an email, generate an error entry in the log
            //$this->LOGTHIS(["error"],"LOGS|EMAIL|Could not send email: ".$result."|00000-0");
            $this->LOG_WRITE("errors","LOGS|EMAIL|Could not send error notification email|00000-0");
          }
        }else if(ADMINEMAIL && ADMINEMAIL != "" && strtolower($log) === "security" && $result !== false){ // email the site admin any security notices
          //$message = "Sitecontrol Security Notification For ".PROJECTNAME,"A security notification was detected:\n\n".$b;
          $result = $this->email->EMAIL_BUILDMESSAGE(ADMINEMAIL,"Sitecontrol Security Notification For ".PROJECTNAME,"A security notification was detected:\n\n".$b,true);
          if($result !== true){ // we could not send an email, generate an error entry in the log
            //$this->LOGTHIS(["error"],"LOGS|EMAIL|Could not send email: ".$result."|00000-0");
            $this->LOG_WRITE("errors","LOGS|EMAIL|Could not send security notification email|00000-0");
          }
        }
      }
    }





    // this will actually write the data to the log files
    // a = the log file to write to
    // b = the data that we want to add to the log entry
    // returns true or false depending on whether a write error was encountered
    function LOG_WRITE($a="",$b=""){
      if(isset($_SESSION['userid']) && $_SESSION['userid'] !== ""){
        $data = $this->DB_READFILE("security/users/recs/".strtolower($_SESSION['userid']).".json");
        $user = $this->DB_JSON_DECODE($data,true);
        $user = $user['users_full_name'];
      }else{
        $user = "System Monitor";
      }
      $file = fopen("security/logs/".$a."/data.json","a");
      $result = fwrite($file,$user."|".$b."|".date("Y-m-d H:i:s")."\n"); // return result of this in case of error
      fclose($file);
      return ($result === false?false:true);
    }





    // this will return a list of log entries from the specified log file
    // a = the log file to read from
    // b = the number of items to return
    // c = format into a table view, true or false
    function LOG_LIST($a="",$b=0,$c=false){
      $return = "";
      $file = file("security/logs/".strtolower($a)."/data.json");
      $file = array_reverse($file);
      $cnt = 0;
      if($c) {
        $return .= "<table class=\"neotable display\" cellspacing=\"0\" width=\"100%\"><thead><tr><th>Performed By</th><th>Tool</th><th>Action</th><th>Data</th><th>Timestamp</th></tr></thead><tfoot><tr><th>Performed By</th><th>Tool</th><th>Action</th><th>Data</th><th>Timestamp</th></tr></tfoot><tbody>";
      } else {
        $return = array();
      }
      foreach($file as $line) {
        if($line != "") {
          if(!$c) { // this is for raw data to be returned
            $return[] = str_replace(array("\n"),"",$line);
          } else {  // this is for matched data to be returned

            $data = str_replace(array("\n"),"",$line);
            $data = explode("|",$data);

            $rid = $data[4];


            // what will the action be for this record
            $action = "none";
            $title = "This record cannot be edited from this location";
            if(strtolower($data[1]) === "users"){
              if(file_exists("security/users/recs/".$rid.".json")){
                $action = "edit";
                $title = "Click here to edit this record";
              }
            }else if(strtolower($data[1]) !== "media"){
              if(file_exists("data/".strtolower($data[1])."/recs/".$rid.".json")){
                $action = "edit";
                $title = "Click here to edit this record";
              }
            }

            // set the value to be returned
            $return .= "<tr data-tool='".strtolower($data[1])."' data-action='".$action."' data-rid='".$rid."' title='".$title."'><td>".$data[0]."</td><td>".$data[1]."</td><td>".$data[2]."</td><td>".$data[3]."</td><td>".$data[5]."</td></tr>";
          }
        }
        $cnt++;
        if($b > 0 && $cnt >= $b){  // we hit the max that was requested if any, break out, otherwise run to end
          break;
        }
      }
      if($c) {
        $return .= "</tbody></table>";
      }
      //echo $cnt;


      // add in an option to truncate the records to 100 for easier use and faster loading...no need to keep items older than that for refernce??????  This should not delay the short list appearing on the dashboard though!!!!!!!!!
      $this->LOG_TRUNCATE($a);


      return $return;
    }



    // truncate a log file back to 100 entries
    // a = the log file to trim
    // no return
    function LOG_TRUNCATE($a=""){
      $file = file("security/logs/".strtolower($a)."/data.json");
      $file = array_reverse($file);
      $recs = array();
      $cnt=0;
      foreach($file as $line){
        if($line != ""){
          $recs[] = str_replace(array("\n"),"",$line);
          $cnt++;
        }
        if($cnt == LOGMAXLENGTH){ // we have reached the cap that we want to stay under
          break;
        }
      }

      // now we can write the updated data back to the file
      $recs = array_reverse($recs); // restore the order to be written to the file
      $data = "";
      foreach($recs as $r){
        $data .= $r."\n";
      }
      $file = fopen("security/logs/".strtolower($a)."/data.json","w+");
      fwrite($file,$data);
      fclose($file);
    }





  }
?>
