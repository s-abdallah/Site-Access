<?php
  /**************************************************
  CLASS: ##USERS

  Description:
  This class handles anything related to users of the
  system - log in, log out, password, create accounts
  , etc.

  Parameters:
  l = logs class
  e = email class

  **************************************************/

  $u = new USERS($l,$e);

  class USERS extends DB{

    var $dataPath = "security/users";
    private $logs,$email;


    /**************************************************
    CONSTRUCTOR: ##EMAIL

    Description:
    Main class constructor

    Parameters:
    a = logs object
    b = email object

    **************************************************/
    function __construct(LOGS $a,EMAIL $b){
        $this->logs = $a;
        $this->email = $b;
    }
    /*************************************************/





    /**************************************************
    METHOD: ##USERS_COUNT

    Description:
    Grab a count of all of the users currently listed
    in the system regardless of status.  Will NOT count
    the neoscape dev account in the total

    Parameters:
    NA

    Returns:
    count of users (less neoscape dev)

    **************************************************/
    function USERS_COUNT(){
      $cnt = 0;
      $file = file($this->dataPath."/data.json");
      foreach($file as $line){
        $cnt++;
      }
      return ($cnt - 1);
    }
    /*************************************************/





    /**************************************************
    METHOD: ##USERS_GETNAME

    Description:
    This method will get the full name for the user
    that is currently logged in

    Parameters:
    session val for userid

    Returns:
    full name of user that is logged in

    **************************************************/
    function USERS_GETNAME(){
      return json_decode($this->DB_READFILE($this->dataPath."/recs/".$_SESSION['userid'].".json"),true)['users_full_name'];
    }
    /*************************************************/





    /**************************************************
    METHOD: ##USERS_LIST

    Description:
    This method will generate a dropdown list of all
    users that are in the system or a single record

    Parameters:
    rid = if we are looking at a specific record

    Returns:
    dropdown list of users

    **************************************************/
    function USERS_LIST($rid=""){
      $file = fopen($this->dataPath."/data.json", "r");
      $recs = array();
      while(!feof($file)){
        $line = fgets($file);
        if($line != ""){
          $recs[] = str_replace(array("\n"),"",$line);
        }
      }
      fclose($file);

      // how do we want to order the returned set of records
      $ordered = array();
      $cnt = 0;
      foreach($recs as $r){
        $data = json_decode($this->DB_READFILE($this->dataPath."/recs/".$r.".json"),true);
        $data['rid'] = $r;
        $ordered[] = array("content" => $data); // edited to work with new content structure
        $cnt++;
      }

      usort($ordered,$this->DB_SORT("users_full_name","ASC"));

      // build out the dropdown list of items to edit
      $return = "<form class=\"neo__formlist\"><fieldset name=\"editme\" id=\"editme\"><label>Select Item To Edit</label><select name=\"selecttoedit\" id=\"selecttoedit\"><option>----------</option>";
      foreach($ordered as $o){
        $data = json_decode($this->DB_READFILE($this->dataPath."/recs/".$o['content']['rid'].".json"),true);
        if($o['content']['users_permissions_level'] != "D"){  // hide developer
          $return .= "<option value=\"".$o['content']['rid']."\" ".($rid == $o['content']['rid']?"selected=\"selected\"":"").">".$data["users_full_name"]."</option>";
        }
      }
      $return .= "</select><p>Please select an item from the list above that you would like to view</p></fieldset></form>";
      return $return;
    }
    /*************************************************/





    /**************************************************
    METHOD: ##USERS_SAVE

    Description:
    This method will handle saving a new user or edit
    to an existing user

    Parameters:
    a = the id of the record
    b = the post data that we need to work with
    c = the action (add, edit, remove)

    Returns:
    success of failure of saving/updating the user

    **************************************************/
    function USERS_SAVE($a="",$b=[],$c=""){
      if($a != "" && count($b) > 1 && $c != ""){

        // check to see if the data file exists for the users, if not, create it below, otherwise do nothing
        if(!file_exists($this->dataPath."/data.json")){
          $result = mkdir($this->dataPath,0755,true);     // create the folder
          if(!$result){ // failure
            $this->logs->LOGTHIS(["error"],"USERS|SYSTEM ERROR|Could not change permissions on: ".$this->dataPath."|".$a);
            return false;
          }else{  // success
            $file = fopen($this->dataPath."/data.json","w");
            $result = fwrite($file,"");
            fclose($file);
            if(!$result){ // failure
              $this->logs->LOGTHIS(["error"],"USERS|WRITE ERROR|Unable to create file: ".$this->dataPath."/data.json|".$a);
              return false;
            }else{
              $result = mkdir($this->dataPath."/recs",0755,true); // create the folder to hold the actual data records in it
              if(!$result){ // failure
                $this->logs->LOGTHIS(["error"],"USERS|SYSTEM ERROR|Could not change permissions on: ".$this->dataPath."/recs|".$a);
                return false;
              }
            }
          }
        }

        // add a new record to the users data file if a user record file is not found, this means it is a new record
        $adduser = false;
        if(!file_exists($this->dataPath."/recs/".$a.".json")){
          $file = fopen($this->dataPath."/data.json","a");
          $data = $a."\n";
          $result = fwrite($file,$data);
          fclose($file);
          if(!$result){ // failure
            $this->logs->LOGTHIS(["error"],"USERS|WRITE ERROR|Unable to write to file: ".$this->dataPath."/recs/".$a.".json|".$a);
            return false;
          }
          $adduser = true;
        }

        // this will decide if we are creating a new record OR updating an existing one
        $toTest = '';
        if(file_exists($this->dataPath."/recs/".$a.".json")){
          $toTest = json_decode($this->DB_READFILE($this->dataPath."/recs/".$a.".json"),true);
        }

        $file = fopen($this->dataPath."/recs/".$a.".json","w+");
        $data = "{\n\t";
        $logData = "";
        $keys = array_keys($b);
        $cnt = 0;
        foreach($b as $p){
          if(strpos(strtolower($keys[$cnt]),"password") !== false){  // encrypt any password fields
            if ($adduser) {
              $p = md5($p);
            }
            if(isset($toTest['users_password']) && trim($p) != trim($toTest['users_password'])){ // this is a new password
              $p = md5($p);
            }
          }
          $data .= ($cnt > 0?",\n\t":"")."\"".$keys[$cnt]."\":\"".$p."\"";
          $logData .= ($cnt > 0?",":"").$p;
          $cnt++;
        }
        $data .= "\n}";
        $result = fwrite($file,$data);
        fclose($file);
        if(!$result){ // failure writing file
          $this->logs->logs->LOGTHIS(["error"],"USERS|WRITE ERROR|".$logData."|".$a);
          return false;
        }else{  // success
          $this->logs->LOGTHIS(["security","activity"],"USERS|".($b['users_status'] == "S"?"SUSPENDED|":strtoupper($c)."|").$logData."|".$a);
          return true;
        }
      }
    }
    /*************************************************/





    /**************************************************
    METHOD: ##USERS_NOTIFY

    Description:
    This method will send an email to a user that was
    just added to the system and let them know where
    and how to log in

    Parameters:
    ???????? - need to finish this method

    Returns:
    NA

    **************************************************/
    function USERS_NOTIFY(){
      //$result = $this->email->EMAIL_BUILDMESSAGE($a,"Sitecontrol Password Reset Request",$message,true);
    }
    /*************************************************/





    /**************************************************
    METHOD: ##USERS_REMOVE

    Description:
    This method will remove a user account from the
    system

    Parameters:
    a = the id of the record to purge

    Returns:
    the result of the remove: true or false

    **************************************************/
    function USERS_REMOVE($a=""){
      if($a != ""){

        // clear the record from the data files
        $file = $this->dataPath."/data.json";
        $file_contents = file_get_contents($file);
        $fh = fopen($file,"w");
        $file_contents = str_replace(array($a."\n"),"",$file_contents);
        $result = fwrite($fh, $file_contents);
        fclose($fh);
        if(!$result){ // failed to update the data file
          $this->logs->LOGTHIS(["error"],"USERS|WRITE ERROR|".$logData."|".$a);
          return false;
        }else{  // data file was updated, moving on

          // we need to log this first BEFORE the record pointer is removed below
          $logData = implode(",",json_decode($this->DB_READFILE($this->dataPath."/recs/".$a.".json"),true));
          $result = unlink($this->dataPath."/recs/".$a.".json");  // unlink the existing file
          if(!$result){ // failed to remove record file
            $this->logs->LOGTHIS(["error"],"USERS|DELETE ERROR|".$logData."|".$a);
            return false;
          }else{  // success
            $this->logs->LOGTHIS(["security","activity"],"USERS|REMOVE|".$logData."|".$a);
            return true;
          }
        }
      }else{
        $this->logs->LOGTHIS(["error"],"USERS|DELETE ERROR|Null or invalid record id provided|".$a);
        return false;
      }
    }
    /*************************************************/





    /**************************************************
    METHOD: ##USERS_LOGIN

    Description:
    This method will check to try and find a user in
    the system that matches the email address first,
    and then will check that passwords match

    Parameters:
    a = the post of the form (randomly generated field names)

    Returns:
    false if no matching user, otherwise jump to index

    **************************************************/
    function USERS_LOGIN($a=""){

      // let's get the names of the generated fields
      $u = $_SESSION['loginfields'][0];
      $p = $_SESSION['loginfields'][1];
      unset($_SESSION['loginfields']); // clear the fields in the session for security
      $result = $this->USERS_FINDMATCH($a[$u]);
      if($result !== false){  // we found a match, now to check the password
        if(md5(trim($a[$p])) === trim($result[0]['users_password'])){ // password match
          $_SESSION['userid'] = trim($result[1]);
          header("location:index.php"); // valid user, jump to index
          exit();
        }
      }

      // send false, no user found
      return false;
    }
    /*************************************************/





    /**************************************************
    METHOD: ##USERS_LOGOUT

    Description:
    This method will log out an existing user and then
    clear the session

    Parameters:
    relies on user session

    Returns:
    jump to login

    **************************************************/
    function USERS_LOGOUT(){
      unset($_SESSION['userid']);
      session_destroy();
      header("location:login.php");
    }
    /*************************************************/





    /**************************************************
    METHOD: ##USERS_FINDMATCH

    Description:
    This method will search for a user in the system
    given a piece of data to use

    Parameters:
    a = the piece of data and type to use to search

    Returns:
    false if not found OR the user data record

    **************************************************/
    function USERS_FINDMATCH($a=""){
      $file = fopen($this->dataPath."/data.json", "r");
      $recs = array();
      while(!feof($file)){
        $line = fgets($file);
        if($line != ""){
          $recs[] = str_replace(array("\n"),"",$line);
        }
      }
      fclose($file);
      foreach($recs as $r){
        $data = json_decode($this->DB_READFILE($this->dataPath."/recs/".$r.".json"),true);
        if(trim($data['users_email']) === trim($a)){
          return [$data,$r]; // match, return user data
        }
      }
      return false;
    }
    /*************************************************/





    /**************************************************
    METHOD: ##USERS_RESETPWORD

    Description:
    This method will reset the password if it can find
    a matched account and then send that user an email
    to let them know it was changed and include the
    temp password

    Parameters:
    a = the piece of data and type to use to search

    Returns:
    done regardless so as to not alert user whether it
    found an account or not for security

    **************************************************/
    function USERS_RESETPWORD($a=""){

      // see if we can find a match using the data that we were provided with
      $result = $this->USERS_FINDMATCH($a);
      if($result !== false){  // we found a match!

        // found a match, generate a new temp password
        $newPword = substr($this->DB_GENERATEID(),0,8); // unhashed to send to the user in the email

        // save the password (hashed) to the account
        $result[0]['users_password'] = md5($newPword);
        $file = fopen($this->dataPath."/recs/".$result[1].".json","w+");
        fwrite($file,json_encode($result[0]));
        fclose($file);

        // send an email to the address that was provided by the user and matched with the new password in it
        $message = "You are receiving this email as a result of a password reset request on your Sitecontrol account.  If you did not request this reset please contact your system administrator.\n\n\nYour temporary account password is: ".$newPword."\n\n\nWe strongly recommend that you log into your Sitecontrol account (http://".$_SERVER['SERVER_NAME'].dirname($_SERVER["REQUEST_URI"]).") and change your password as soon as possible.\n\n\n***** This message was automatically generated, please do not respond *****";

        $result = $this->email->EMAIL_BUILDMESSAGE($a,"Sitecontrol Password Reset Request",$message,true);

        if($result == 1){  // mail worked
          $this->logs->LOGTHIS(["security"],"USERS|RESET|A password reset for a known account was performed|".$a);
        }else{  // mail failed
          $this->logs->LOGTHIS(["errors"],"USERS|RESET|Could not send password reset email|".$a);
        }
      }else{  // we did not find a match!
        $this->logs->LOGTHIS(["security"],"USERS|RESET|A password reset was attempted for an unknown account|".$a);
      }

      // we are done regardless of what happened
      return "done";
    }
    /*************************************************/
  }
?>
