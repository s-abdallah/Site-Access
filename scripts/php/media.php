<?php
  //require_once("config/config.php");


  $m = new MEDIA($l, PAGIITEMS);

  // this will handle all things read from and writing to the data source files
  class MEDIA extends DB{

    // set the base path of where we can access data, etc.
    var $sizes = [[],null];
    var $recID = "";
    var $post = [];
    var $fileParts = [];
    var $responses = [
      "success" => [
        "Successfully uploaded your media item.",
        "Successfully saved external link.",
        "Successfully uploaded and processed batch upload."
      ],
      "failure" => [
        "There was a problem uploading your media item to the system.",
        "There was a problem saving your data to the system.",
        "Unable to resize source image.",
        "Unable to save image.",
        "Unable to generate thumbnail."
      ]
    ];
    var $uploadPath = "../_uploads/"; // can be tweaked for custom installs, otherwise it expects the _uploads dir to be at the root of the site the CMS is being used on

    private $logs;
    private $_items;
    private $_page;
    private $_count;

    // class constructor that gets called by default along with instantiation of class
    function __construct(LOGS $a, $c=""){
        $this->logs = $a;
        $this->_items = $c;
    }





    // this will check to see if a specific tool is allowed and supposed to show the media manager for users to assign media to an entry
    // a = the tool that we are working with
    function MEDIA_ALLOWED($a=""){
      if($a != ""){
        return true;
      }
    }





    // this will check to make sure that the GD library is installed and working, otherwise disable adding new media
    function MEDIA_CHECKSUPPORT(){
      $return = false;
      if(extension_loaded('gd')){ // we will expand this to be both GD and image magik at some point
        $return = true;
      }
      return $return;
    }





    // this function will take in an id and write it to the main media records file
    function MEDIA_ID($a=""){
      $id = $this->DB_GENERATEID();
      $file = fopen("data/media/data.json","a");
      $data = $id."\n";
      fwrite($file,$data);
      fclose($file);
      $this->recID = $id;
      return $id;
    }





    // this will handle getting the parts of the filename that we will need as we go
    // a = the source media item object
    function MEDIA_FILENAME($a=""){
      $fileNameParts = explode(".",$a['name']);
      // $savedFileName = (isset($this->post['filename']) && $this->post['filename'] != ""?strtolower($this->post['filename']):strtolower($fileNameParts[0]));
      // $savedFileName = strtolower($this->post['filename']);
      $savedFileName = $this->post['filename'];
      if(file_exists($this->uploadPath.$savedFileName.".".$fileNameParts[1])){
        $savedFileName .= "_".date("mdYHis");
      }
      $this->fileParts = [$a,$fileNameParts,$savedFileName]; // source, name and extension, adjusted filename
      //print_r($this->fileParts);
      //die();
      return [$fileNameParts,$savedFileName];
    }





    // this method will save media information that was entered or updated
    // a = any GET parameters that may have been passed
    // b = any post variables that may exist
    // c = any files that were selected
    function MEDIA_SAVE($a="",$b="",$c=""){
      $this->post = $b;
      $return = ["failure",$this->responses['failure'][0]]; // set the return to the failure state to begin
      if(isset($a['rid']) && $a['rid'] != ""){  // perform an update to an existing media item
        $result = $this->MEDIA_RECORD_UPDATE($a['rid'],$b);  // update the data for the record id
        $return = ["success",$this->responses['success'][0]];
      }else{  // add a new media item to the system
        // $source = strtolower($c['filetoupload']);
        $source = $c['filetoupload'];
        $id = $this->MEDIA_ID($b);  // get a new random id and create a new record to store data in

        // if the user actually uploaded a file
        if($source['name'] != ""){
          $result = $this->MEDIA_FILENAME($source); // check the filename in case there is a conflict
          //return $this->fileParts;
          if($result){
            // echo $this->MEDIA_UPLOAD();  // save the stored object source and destination
            $result = $this->MEDIA_UPLOAD();  // save the stored object source and destination
          }
        }else{  // this is for an external link reference
          $result = $this->MEDIA_RECORD_ADD();
        }
        $return = ["success",$this->responses['success'][0]];
      }
      return $return;
    }





    // this method will take a zip of files and batch upload them to the system
    // a = the specific folder wihin temp that was expanded
    // b = the post variables from the form
    function MEDIA_BATCH($a="",$b=""){
      if(isset($a) && $a != ""){

        // loop through each item in the directory to actually save it to the system
        $dir = new DirectoryIterator("temp/".$a);
        $dirCnt = 0;
        foreach($dir as $fileinfo){
          if(!$fileinfo->isDot() && !$fileinfo->isDir()){
            $dirCnt++;
          }
        }
        $cnt = 0;
        foreach($dir as $fileinfo){

          $this->post = $b;

          if(!$fileinfo->isDot() && !$fileinfo->isDir()){

            $id = $this->MEDIA_ID("");  // get a new random id and create a new record to store data in

            if($fileinfo->getFilename() != ""){

              // need to check the filename to make sure that there are no conflicts
              $fileNameParts = explode(".",$fileinfo->getFilename());
              // $savedFileName = strtolower($fileNameParts[0]);
              $savedFileName = $fileNameParts[0];


              if(file_exists($this->uploadPath.$savedFileName.".".$fileNameParts[1])){
                $savedFileName .= "_".date("mdYHis");
              }


              $fileDeets = [];
              $fileDeets['name'] = $savedFileName.".".$fileNameParts[1];
              $fileDeets['type'] = $fileNameParts[1];
              $fileDeets['tmp_name'] = "batch uploaded";
              $fileDeets['error'] = "0";
              $fileDeets['size'] = filesize("temp/".$a."/".$fileNameParts[0].".".$fileNameParts[1]);

              $this->post['filename'] = $savedFileName;
              $this->post['caption'] = $savedFileName;

              $this->fileParts = [$fileDeets,$fileNameParts,$savedFileName,$fileinfo->getFilename()]; // source, name and extension, adjusted filename

              $result = $this->MEDIA_BATCHUPLOAD($a);  // save the stored object source and destination


              $cnt++;
              if($cnt >= $dirCnt){

                // once we are all done actually saving, delete all of the items and then the parent zip folder from temp
                //$this->MEDIA_BATCHCLEAN("temp/".$a);

                return ["success",$this->responses['success'][2]];  // done successfully
              }
            }
          }
        }
      }else{
        return ["failure",$this->responses['failure'][0]];
      }
    }





    // this function will delete temp files used in the batch upload process
    function MEDIA_BATCHCLEAN($dirPath){
      if(is_dir($dirPath)){
        $objects = scandir($dirPath);
        foreach($objects as $object){
          if($object != "." && $object !=".."){
            if(filetype($dirPath . DIRECTORY_SEPARATOR . $object) == "dir"){
              $this->MEDIA_BATCHCLEAN($dirPath . DIRECTORY_SEPARATOR . $object);
            }else{
              unlink($dirPath . DIRECTORY_SEPARATOR . $object);
            }
          }
        }
        reset($objects);
        rmdir($dirPath);
      }
    }





    // this function will save a media file that was uploaded to the system
    // a = the batch folder that we need to look within
    function MEDIA_BATCHUPLOAD($a=""){

      $this->MEDIA_SIZES($this->fileParts[1][1]); // determine if we need to resize anything that we are saving

      $thumb = true;
      $ext = array("mp4", "gif");
      // copy the file to upload folder
      if(in_array($this->fileParts[1][1], $ext)){
        copy("temp/".$a."/".$this->fileParts[3],strtolower($this->uploadPath.$this->fileParts[2]).".".$this->fileParts[1][1]);
        $thumb = false;
      }

      if(count($this->sizes[0]) > 0 && $thumb){ // we have sizes to save out for the media item that was uploaded

        // copy the original media item in temp to uploads to keep a permanent copy of the original image
        // if(file_exists($this->uploadPath.$savedFileName.".".$fileNameParts[1])){
        //   $savedFileName .= "_".date("mdYHis");
        // }
        copy("temp/".$a."/".$this->fileParts[3],$this->uploadPath.$this->fileParts[2].".".$this->fileParts[1][1]);

        // now use GD to create all of the other sizes
        // list($oWidth,$oHeight) = getimagesize("temp/".$a."/".$this->fileParts[2].".".$this->fileParts[1][1]);
        // list($oWidth,$oHeight) = getimagesize($this->uploadPath.$this->fileParts[2].".".$this->fileParts[1][1]);
        list($oWidth,$oHeight) = getimagesize("temp/".$a."/".$this->fileParts[3]);

        // determine if we need to work with jpg, png, or gif and then convert them all to jpg when we save them out
        switch($this->fileParts[1][1]){
          case "jpg":
              $processType = "imagecreatefromjpeg";
              break;
          case "png":
              $processType = "imagecreatefrompng";
              break;
          case "gif":
              $processType = "imagecreatefromgif";
              break;
        }

        // dynamically reference the proper function to process the image that was uploaded
        // $source = $processType("temp/".$a."/".$this->fileParts[2].".".$this->fileParts[1][1]);
        $source = $processType("temp/".$a."/".$this->fileParts[3]);

        // loop through and build out each size of the image that we want to store
        $c = 0;
        $ssKeys = array_keys($this->sizes[0]);
        foreach($this->sizes[0] as $nS){
          $result = $this->MEDIA_RESIZE($nS,$source,[$oWidth,$oHeight],$ssKeys[$c]);
          if(!$result){
            break;
          }
          $c++;
          if($c == count($this->sizes[0])){
            // now we are done with sizing, we can move on to create the thumbnail
            $result = $this->MEDIA_THUMBNAIL($source,[$oWidth,$oHeight]);
            if($result){
              //imagedestroy($source);
              $result = $this->MEDIA_RECORD_ADD();
              if($result){  // everything went smoothly, success
                return $this->responses['success'][0];
              }else{  // something went wrong
                return $this->responses['failure'][1];
              }
            }else{  // something really went wrong
              return $this->responses['failure'][4];
            }
          }
        }
      }else{  // non-sizeable media item was uploaded
        $result = move_uploaded_file("temp/".$a."/".$this->fileParts[2].".".$this->fileParts[1][1],$this->uploadPath.$this->fileParts[2].".".$this->fileParts[1][1]);
        if($result){  // we were able to upload okay, try and create the data file
          $result = $this->MEDIA_RECORD_ADD();
          if($result){  // we were able to create the data file so we should be done
            return $this->responses['success'][0];
          }else{  // we were not able to create the data file so we will alert the user
            return $this->responses['failure'][1];
          }
        }else{  // we were not able to upload the file so we will alert the user
          // add record for other extensions
          if(in_array($this->fileParts[1][1], $ext)) {
            $result = $this->MEDIA_RECORD_ADD();
          }
          return $this->responses['failure'][0];
        }
      }
    }





    // this function will save a media file that was uploaded to the system
    // function MEDIA_SAVE(){
    function MEDIA_UPLOAD(){

      $this->MEDIA_SIZES($this->fileParts[1][1]); // determine if we need to resize anything that we are saving

      // determine if the temp/ directory exists, and if not create it
      // if(!file_exists("temp")){
      //   mkdir("temp",0755,true);
      // }
      //
      // // check to make sure that the upload path exists at the root of the site, and if not create it
      // if(!file_exists($this->uploadPath)){
      //   mkdir($this->uploadPath,0755,true);
      // }


      if(count($this->sizes[0]) > 0){ // we have sizes to save out for the media item that was uploaded

        // save the original image to temp so that we can work on it from there and not touch the original
        $result = move_uploaded_file($this->fileParts[0]['tmp_name'],"temp/".$this->fileParts[2].".".$this->fileParts[1][1]);

        // copy the original media item in temp to uploads to keep a permanent copy of the original image
        copy("temp/".$this->fileParts[2].".".$this->fileParts[1][1],$this->uploadPath.$this->fileParts[2].".".$this->fileParts[1][1]);

        // now use GD to create all of the other sizes
        list($oWidth,$oHeight) = getimagesize("temp/".$this->fileParts[2].".".$this->fileParts[1][1]);

        // determine if we need to work with jpg, png, or gif and then convert them all to jpg when we save them out
        switch($this->fileParts[1][1]){
          case "jpg":
              $processType = "imagecreatefromjpeg";
              break;
          case "png":
              $processType = "imagecreatefrompng";
              break;
          case "gif":
              $processType = "imagecreatefromgif";
              break;
        }

        // dynamically reference the proper function to process the image that was uploaded
        $source = $processType("temp/".$this->fileParts[2].".".$this->fileParts[1][1]);

        // loop through and build out each size of the image that we want to store
        $c = 0;
        $ssKeys = array_keys($this->sizes[0]);
        foreach($this->sizes[0] as $nS){
          $result = $this->MEDIA_RESIZE($nS,$source,[$oWidth,$oHeight],$ssKeys[$c]);
          if(!$result){
            break;
          }
          $c++;
          if($c == count($this->sizes[0])){
            // now we are done with sizing, we can move on to create the thumbnail
            $result = $this->MEDIA_THUMBNAIL($source,[$oWidth,$oHeight]);
            if($result){
              imagedestroy($source);
              unlink("temp/".$this->fileParts[2].".".$this->fileParts[1][1]); // remove the temp image to keep things tidy
              $result = $this->MEDIA_RECORD_ADD();
              if($result){  // everything went smoothly, success
                return $this->responses['success'][0];
              }else{  // something went wrong
                return $this->responses['failure'][1];
              }
            }else{  // something really went wrong
              return $this->responses['failure'][4];
            }
          }
        }
      }else{  // non-sizeable media item was uploaded









        $result = move_uploaded_file($this->fileParts[0]['tmp_name'],$this->uploadPath.$this->fileParts[2].".".$this->fileParts[1][1]);
        if($result){  // we were able to upload okay, try and create the data file






          // somewhere in here we need to determine if this is a zip file, and whether or not we need to unpack the contents into a fodler structure or keep it all together
          // this will future complicate the removal process and we will need to loop through and remove all folders, etc for unpacked files






          if($this->fileParts[1][1] === "zip" && json_decode($this->DB_READFILE("config/uploads.json"),true)['unpackzip']){ // unpack zip, keep original package just in case
            $zip = new ZipArchive;
            if($zip->open($this->uploadPath."/".$this->fileParts[2].".zip")){  // opened the zip file successfully
              $zip->extractTo($this->uploadPath."/".$this->fileParts[2]."/"); // use the filename as the folder in which to unpack everything
              $zip->close();
              $this->logs->LOGTHIS(["activity"],"MEDIA|UNZIP|".$this->recID."|".$this->recID);
            }else{  // unpack failed
              $this->logs->LOGTHIS(["errors"],"MEDIA|UNZIP|".$this->recID."|".$this->recID);
            }
          }

          $result = $this->MEDIA_RECORD_ADD();
          if($result){  // we were able to create the data file so we should be done
            return $this->responses['success'][0];
          }else{  // we were not able to create the data file so we will alert the user
            return $this->responses['failure'][1];
          }
        }else{  // we were not able to upload the file so we will alert the user
          return $this->responses['failure'][0];
        }
      }
    }





    // this will create a thumbnail to be used by the CMS only
    // a = the source image that we are going to be working on brought in via GD
    // b = the original height and width values
    function MEDIA_THUMBNAIL($a="",$b=""){
      $tSize = [300,300]; // this is specific to thumbnails used within the CMS only
      if($b[0] > $b[1]){ // work with the width
        $nSize = [(int)($b[0] / ($b[1] / $tSize[1])),$tSize[1]];
      }else{  // work with the height
        $nSize = [$tSize[0],(int)($b[1] / ($b[0] / $tSize[0]))];
      }
      $sized = imagecreatetruecolor($nSize[0],$nSize[1]);
      imagecopyresampled($sized,$a,0,0,0,0,$nSize[0],$nSize[1],$b[0],$b[1]);
      $sized = imagecrop($sized,array("x"=>(int)(($nSize[0] / 2) - ($tSize[0] / 2)),"y"=>0,"width"=>$tSize[0],"height"=>$tSize[1]));

      // check to decide what type of image we are dealing with
      if($this->fileParts[1][1] === "jpg"){
        $result = imagejpeg($sized,$this->uploadPath.$this->fileParts[2]."-thumbnail.jpg",70);
      }else if($this->fileParts[1][1] === "png"){
        $result = imagepng($sized,$this->uploadPath.$this->fileParts[2]."-thumbnail.png",7);
      }

      imagedestroy($sized);
      return $result;
    }




    // need to add fix below to store images according to the label of the filesize rather than the dimensions for easier use on the front-end through implosion of filename




    // this will take a source image and resize it according to the values that are passed in
    // a = an array of width and height values that the source should be sized to
    // b = the source image that we are going to be working on brought in via GD
    // c = the original height and width values
    // d = the key value of the size that we are trying to save
    function MEDIA_RESIZE($a="",$b="",$c="",$d=""){

      $sizeName = array_keys($a);

      if($a[0] == 0 || $a[1] == 0){ // we will be resizing an image as we only have one dimension
        if($a[0] != 0 && $a[1] == 0){ // work with the width
          $nSize = [$a[0],(int)($c[1] / ($c[0] / $a[0]))];
        }else{  // work with the height
          $nSize = [(int)($c[0] / ($c[1] / $a[1])),$a[1]];
        }
        $sized = imagecreatetruecolor($nSize[0],$nSize[1]);
        imagecopyresampled($sized,$b,0,0,0,0,$nSize[0],$nSize[1],$c[0],$c[1]);
      }else{  // we will be cropping an image here
        if($c[0] > $c[1]){ // work with the width
          $nSize = [(int)($c[0] / ($c[1] / $a[1])),$a[1]];
        }else{  // work with the height
          $nSize = [$a[0],(int)($c[1] / ($c[0] / $a[0]))];
        }
        $sized = imagecreatetruecolor($nSize[0],$nSize[1]);
        imagecopyresampled($sized,$b,0,0,0,0,$nSize[0],$nSize[1],$c[0],$c[1]);
        $sized = imagecrop($sized,array("x"=>(int)(($nSize[0] / 2) - ($a[0] / 2)),"y"=>0,"width"=>$a[0],"height"=>$a[1]));
      }
      $this->sizes[1] .= "[".$nSize[0].",".$nSize[1]."]";  // this will capture the actual size that was generated

      if($this->fileParts[1][1] === "jpg"){
        // $result = imagejpeg($sized,$this->uploadPath.$this->fileParts[2]."-".$nSize[0]."x".$nSize[1].".jpg",70);
        $result = imagejpeg($sized,$this->uploadPath.$this->fileParts[2]."-".strtolower($d).".jpg",70);
      }else if($this->fileParts[1][1] === "png"){
        // $result = imagepng($sized,$this->uploadPath.$this->fileParts[2]."-".$nSize[0]."x".$nSize[1].".png",7);
        $result = imagepng($sized,$this->uploadPath.$this->fileParts[2]."-".strtolower($d).".png",7);
      }

      imagedestroy($sized);
      return $result;
    }





    // this is where we will actually add a new record of data to the system for a new media item
    function MEDIA_RECORD_ADD(){

      // determine the type of item we are adding a record for to the system
      if(!isset($this->post['link']) || $this->post['link'] == ""){
        $recordType = $this->fileParts[1][1];
      }else{
        if(strpos($this->post['link'],"vimeo") !== false || strpos($this->post['link'],"youtube") !== false){
          $recordType = "mov";
        }else if(strpos($this->post['link'],"neovrx") !== false){
          $recordType = "vr";
        }else{
          $recordType = "link";
        }
      }

      // add the record of all of the data for this media item
      $file = fopen("data/media/recs/".$this->recID.".json","w+");
      $record = "{\n\t";

      $keys = array_keys($this->post);
      $cnt = 0;

      if(isset($this->post)){ // we only need to loop through the post items if they exist
        foreach($this->post as $p){
            //$record .= ($cnt > 0?",\n\t":"")."\"".$keys[$cnt]."\":\"".$p."\"";
            // $record .= ($cnt > 0?",\n\t":"")."\"".$keys[$cnt]."\":\"".htmlentities(str_replace(array("\n","\t","\r"),"",$p))."\"";
            $record .= ($cnt > 0?",\n\t":"")."\"".$keys[$cnt]."\":\"".htmlentities(str_replace(array("\n","\t","\r"),"",$p), ENT_QUOTES)."\"";  // convert quotes in JSON
            $cnt++;
        }
      }

      $record .= ",\n\t";

      $record .= "\"sizes\":".json_encode($this->sizes[1]).",\n\t";
      $record .= "\"filepath\":\"".(isset($this->fileParts[2])?$this->fileParts[2]:"")."\",\n\t";
      $record .= "\"filetype\":\"".$recordType."\"";

      if(isset($this->fileParts[0]) && count($this->fileParts[0]) > 0){
        $record .= ",\n\t";
        $keys = array_keys($this->fileParts[0]);
        $cnt = 0;

        foreach($this->fileParts[0] as $f){
          if($keys[$cnt] == "name"){  // to force the custom name to be recorded as the name for easier use and removal
            $record .= ($cnt > 0?",\n\t":"")."\"".$keys[$cnt]."\":\"".strtolower($this->fileParts[2]).".".strtolower($this->fileParts[1][1])."\"";
            // be sure to force everythign to lower case before we save it!!!!!!!!
          }else{
            $record .= ($cnt > 0?",\n\t":"")."\"".$keys[$cnt]."\":\"".$f."\"";
          }
          // $record .= ($cnt > 0?",\n\t":"")."\"".$keys[$cnt]."\":\"".$f."\"";
          $cnt++;
        }
      }

      $record .= "\n}";
      $result = fwrite($file,$record);
      fclose($file);

      // track this in the activity log
      $this->logs->LOGTHIS(["activity"],"MEDIA|ADD|".$this->recID."|".$this->recID);

      // need to make sure that we clear out any of the class vars to make sure that they will not affect another entry
      $this->sizes = [[],null];
      $this->recID = "";
      $this->post = [];
      $this->fileParts = [];

      return $result;
    }





    // this method will update an existing media item when the user is editing from the manage interface
    // a = the id of the record to be updated
    // b = the post variables from the form to be used during the update
    function MEDIA_RECORD_UPDATE($a,$b){
      $data = $this->DB_READFILE("data/media/recs/".$a.".json");
      $data = $this->DB_JSON_DECODE($data,true);
      //$result = $this->DB_READFILE("data/media/recs/".$a.".json");


      $data['timestamp'] = $b['timestamp'];
      $data['caption'] = $b['caption'];
      $data['subcaption'] = $b['subcaption'];
      // $data['tags'] = str_replace(", ",",",$b['tags']);
      $data['tags'] = $b['tags'];

      // if the link was updated, set it to be the new one
      if(isset($b['link'])){
        $data['link'] = $b['link'];
      }


      // need to write the data back to the file that we read it from
      // add the record of all of the data for this media item
      $file = fopen("data/media/recs/".$a.".json","w+");
      $record = "{\n\t";
      $logData = "";
      $keys = array_keys($data);
      $cnt = 0;

      foreach($data as $p){
          // $record .= ($cnt > 0?",\n\t":"")."\"".$keys[$cnt]."\":\"".$p."\"";
          // $record .= ($cnt > 0?",\n\t":"")."\"".$keys[$cnt]."\":\"".htmlentities(str_replace(array("\n","\t","\r"),"",$p))."\"";
          $record .= ($cnt > 0?",\n\t":"")."\"".$keys[$cnt]."\":\"".htmlentities(str_replace(array("\n","\t","\r"),"",$p), ENT_QUOTES)."\"";  // convert quotes in JSON
          $logData .= ($cnt > 0?",":"").$p;
          $cnt++;
      }

      //$record .= ",\n\t";

      $record .= "\n}";
      $result = fwrite($file,$record);
      fclose($file);


      // track this in the activity log
      //$this->LOGTHIS(["activity"],"MEDIA|EDIT|".$a);
      $this->logs->LOGTHIS(["activity"],"MEDIA|EDIT|".$logData."|".$a);


      return $result;
    }





    // this method will remove a specific media item from the system
    // a = the array of media items that we want to remove
    function MEDIA_REMOVE($a){

      // we need to find any matched media items in _uploads, and unlink them
      $foundThese = explode(",",$a);

      foreach($foundThese as $fT){

        $data = json_decode($this->DB_READFILE("data/media/recs/".$fT.".json"),true);

        //echo "LOOK IN ALL OF THE RELATIONSHIP FILES AND REMOVE THIS RECORD: ".$fT."\n";
        foreach(scandir("data/media/relations") as $file){
          $edit = false;
          if ('.' === $file || '..' === $file || '.DS_Store' === $file) continue; // ignore dots
          $rel = json_decode($this->DB_READFILE("data/media/relations/".$file),true);
          $keys = array_keys($rel);
          $cnt = 0;
          foreach($rel as $rL){
            $cnt2 = 0;
            foreach($rL as $rLL){
              // this is where we prune out the record from the array
              if(trim($rLL) === trim($fT)){
                array_splice($rel[$keys[$cnt]],$cnt2,1);
                $edit = true;
                break;
              }
              $cnt2++;
            }
            $cnt++;
          }

          if($edit){
            // write the data back to the same file so that it is updated!!!
            $rel = json_encode($rel);
            //echo $rel."\n";
            $file = fopen("data/media/relations/".$file,"w");
            fwrite($file,$rel);
            fclose($file);
          }

        }

        //echo "FIND ANY MEDIA CONTAINING THIS FILENAME: ".$data['filepath']."\n";

        // remove the original image, if there is one
        if($data['filetype'] === "mov" || $data['filetype'] === "vr"){  // we are going to remove a custom thumbnail
          if(isset($data['name']) && $data['name'] != "" && file_exists("../_uploads/".$data['name'])){
            unlink("../_uploads/".$data['name']);
          }
        }else if($data['filetype'] === "zip"){





          if(file_exists("../_uploads/".$data['filename'])){
            // this is where we will recursively remove the matched folder and any and all contents
            $dir = "../_uploads/".$data['filename'];
            $it = new RecursiveDirectoryIterator($dir,RecursiveDirectoryIterator::SKIP_DOTS);
            $files = new RecursiveIteratorIterator($it,RecursiveIteratorIterator::CHILD_FIRST);
            foreach($files as $file){
                if($file->isDir()){ // remove a nested dir
                    rmdir($file->getRealPath());
                }else{  // remove nested files
                    unlink($file->getRealPath());
                }
            }
            rmdir($dir);  // remove the extracted folder
          }

          // rmove the original zip file
          unlink("../_uploads/".$data['name']);





        }else{ // this is a regular image type
          if(file_exists("../_uploads/".$data['filepath'].".".$data['filetype'])){
            unlink("../_uploads/".$data['filepath'].".".$data['filetype']);
          }
        }




        // now remove all of the other sizes, etc. that may have been created
        if($data['filepath'] != ""){  // we need to make sure that we have a file to remove
          foreach(scandir("../_uploads") as $file){
            if ('.' === $file || '..' === $file || '.DS_Store' === $file) continue; // ignore dots
            if(strpos(trim($file),trim($data['filepath']."-")) !== false){
              //echo $file."\n";
              unlink("../_uploads/".$file); // remove the file
            }
          }
        }

        //echo "REMOVE THIS RECORD: ".$fT."\n";
        unlink("data/media/recs/".$fT.".json"); // remove the record

        //echo "REMOVE THE ENTRY: ".$fT." FROM THE data/media/data.json FILE\n\n\n";
        $file = fopen("data/media/data.json", "r");
        $recs = array();
        while(!feof($file)){
          $line = fgets($file);
          $line = str_replace(array("\n"),"",$line);
          if($line != "" && $line != trim($fT)){
            $recs[] = $line;
          }
        }
        fclose($file);

        // need to write the updated recs back to the data.json file
        $recs = implode("\n",$recs);
        $file = fopen("data/media/data.json","w");
        fwrite($file,$recs."\n");
        fclose($file);

        // log that this media item was removed from the system
        $this->logs->LOGTHIS(["activity"],"MEDIA|REMOVE|".$fT."|".$fT);

      }



      return true;
    }





    // get an array of all of the supported file types
    function MEDIA_TYPES(){
        //$return[] = json_decode($this->DB_READFILE("uploads/uploads.json"),true);
        $return[] = json_decode($this->DB_READFILE("config/uploads.json"),true);
        return $return[0]['allowed'];
    }





    // get an array of all of the defined sizes to save images
    // a = the extension type of the media item that is being uploaded
    function MEDIA_SIZES($a=""){

      // the first thing to check is the type of file that was uploaded to make sure that it needs to be sized
      if(!in_array($a,$this->MEDIA_NORESIZE())){
        // $res = json_decode($this->DB_READFILE("uploads/uploads.json"),true);
        $res = json_decode($this->DB_READFILE("config/uploads.json"),true);
        $sKeys = array_keys($res['sizes']);
        $ii = 0;
        foreach($res['sizes'] as $r){
          $this->sizes[0][$sKeys[$ii]] = $r;
          $ii++;
        }
      }

    }





    // determine what should and should not be res-sized as it is uplaoded
    function MEDIA_NORESIZE(){
        // $return[] = json_decode($this->DB_READFILE("uploads/uploads.json"),true);
        $return[] = json_decode($this->DB_READFILE("config/uploads.json"),true);
        return $return[0]['noresize'];
    }





    // get a count of all of the media items in the system that match a specific type
    // a = the specific type of media we are looking for, empty for all types
    function MEDIA_COUNT($a = ""){
      $cnt = new FilesystemIterator("data/media/recs", FilesystemIterator::SKIP_DOTS);
      // return (iterator_count($cnt) - 1);
      return iterator_count($cnt);
    }





    // get the count of related media
    // a = the id of the media item
    // b = the rid of the entry in the system
    function MEDIA_MAP_COUNT($a="",$b=""){
      if(file_exists($this->dataStore."/media/relations/".$b.".json")){
        $data = json_decode($this->DB_READFILE($this->dataStore."/media/relations/".$b.".json"),true);
        return count($data[$a]);
      }else{
        return 0;
      }
    }





    // this method will map a relationship between a media item and a specific entry in the system
    // a = the id of the media item
    // b = the rid of the entry in the system
    // c = the specific field that we are looking to find a match within
    // d = the tool that we are working within
    function MEDIA_MAP($a="",$b="",$c="",$d=""){
      $data = json_decode($this->DB_READFILE("data/media/relations/".$b.".json"),true);
      $fieldData = $data[$c];

      // look to see if the relationship already exists, and if it does remove it, otherwise add it
      $exists = false;
      $cnt = 0;
      foreach($fieldData as $fD){
        if($fD === $a){ // we have a match, remove it
          $exists = true;
          array_splice($fieldData,$cnt,1);
          break;  // no need to go on, we found a match
        }
        $cnt++;
      }
      if($exists === false){  // we did not find a match, so add it
        $fieldData[] = $a;
      }

      // update the data for the specific field so that we can write it back to the relationship file
      $data[$c] = $fieldData;

      // need to reactivate this once I get the multi-field thing sorted out
      $file = fopen("data/media/relations/".$b.".json","w+");
      fwrite($file,json_encode($data));
      fclose($file);
      //$result = $data;

      // need to check and see if we need to be aware of a field max for the media being selected
      $tool = json_decode($this->DB_READFILE("config/tools.json"),true);
      // if(isset($tool[strtoupper($d)]['media'])){
      if(isset($tool[strtoupper($d)]['fields'][$c]['max'])){
        // return (count($fieldData) < intval($tool[strtoupper($d)]['fields'][$c]['max'])?"empty":"full");
        return (count($fieldData) >= intval($tool[strtoupper($d)]['fields'][$c]['max'])?"full":"empty");
      }else{
        return "empty";
      }
    }





    // build a list of media items, defaults to all or you can pass in a type to be shown
    // a = the specific type of media we are looking for, empty for all types, pass type or id to reference
    // b = max results to return per page
    // c = current page number
    function MEDIA_LIST($a="",$b="",$c=""){
      //print_r($a);
      //return $a;
      // if(!isset($a['rid']) || $a['rid'] == "" || !file_exists("data/media/relations/".$a['rid'].".json")){
      //$file = "";


      //print_r($a);


      $return = array();
      $data = array();
      // if(!isset($a['rid']) || $a['rid'] == ""){ // if we are looking to grab all media
      if(!isset($a['rid'])){
        $file = fopen("data/media/data.json", "r");


        // this will need to become a secondary method call to show this type of media list properly, or this needs to be read into an array to loop??????

        //echo $_GET['field'];

        // there is a repeating need here to create a method to grab data and create an array from it, do that in DB?????????????????


        // in order to paginate this section will need to be changed to only read specific lines from the data source

        if($b == "" || $c == ""){   // we are going to read in everything that we can at once
          while(!feof($file)){
            $line = fgets($file);
            if($line != ""){
              //$rec = array();
              $recId = str_replace(array("\n"),"",$line);
              $rec = json_decode($this->DB_READFILE("data/media/recs/".$recId.".json"),true);
              $rec['rid'] = $recId;
              $data[] = $rec;
            }
          }
        }else{  // we are trying to show paginated results on the screen
          $cnt = 0;
          $start = (($c - 1) * $b);
          $stop = ($b * $c);

          //echo "RANGE: ".$start." - ".$stop;

          while(!feof($file)){
            $line = fgets($file);
            if($line != "" && $cnt >= $start && $cnt < $stop){
              //$rec = array();
              $recId = str_replace(array("\n"),"",$line);
              $rec = json_decode($this->DB_READFILE("data/media/recs/".$recId.".json"),true);
              $rec['rid'] = $recId;
              $data[] = $rec;

            }
            $cnt++;
          }
        }



        // while(!feof($file)){
        //   $line = fgets($file);
        //   if($line != ""){
        //     //$rec = array();
        //     $recId = str_replace(array("\n"),"",$line);
        //     $rec = json_decode($this->DB_READFILE("data/media/recs/".$recId.".json"),true);
        //     $rec['rid'] = $recId;
        //     $data[] = $rec;
        //   }
        // }

        fclose($file);

      }else{  // if we are looking to grab all related media to a specific record
        if($a['rid'] != "" && file_exists("data/media/relations/".$a['rid'].".json")){
          //$file = fopen("data/media/relations/".$a['rid'].".json", "r");

          $rel = $this->DB_READFILE("data/media/relations/".$a['rid'].".json");
          $recs = $this->DB_JSON_DECODE($rel,true);

          //print_r($recs[$_GET['field']]);


          // this will require a slightly different method OR call to the same method above to properly display the media
          if(isset($_GET['field']) && $_GET['field'] != ""){


            //$recs = (isset($_GET['field']) && $_GET['field'] != ""?$recs[$_GET['field']]:$recs);

            if(isset($recs[$_GET['field']])){
              $recs = $recs[$_GET['field']];
            //}
            // if(count($recs) > 0){ // check to make sure that there is data to look at, otherwise ignore it
            //   foreach($recs as $r){
            //     $rec = json_decode($this->DB_READFILE("data/media/recs/".$r.".json"),true);
            //     $rec['rid'] = $r;
            //     $data[] = $rec;
            //   }
            // }
              foreach($recs as $r){
                $rec = json_decode($this->DB_READFILE("data/media/recs/".$r.".json"),true);
                $rec['rid'] = $r;
                $data[] = $rec;
              }
            // }else{
            //   //$recs = $recs;
            //
            //   foreach($recs as $r){
            //     $rec = json_decode($this->DB_READFILE("data/media/recs/".$r.".json"),true);
            //     $rec['rid'] = $r;
            //     $data[] = $rec;
            //   }
            }
          }
          //if(count($recs) > 0){ // check to make sure that there is data to look at, otherwise ignore it
            // foreach($recs as $r){
            //   $rec = json_decode($this->DB_READFILE("data/media/recs/".$r.".json"),true);
            //   $rec['rid'] = $r;
            //   $data[] = $rec;
            // }
          //}
        }
      }
      //$recs = array();

      //echo $_GET['field'];

      //print_r($data);
      //print_r($data[$_GET['field']]);
      //die();

      // this whole section needs to be re-worked to account for their possibly being multiple media fields for a given tool
      // if we need to handle filters on the data being returned
      if(count($data) > 0){
        foreach($data as $d){

        //}
        //while(!feof($file)){
            //$line = fgets($file);
            //if($line != ""){
                //$recId = str_replace(array("\n"),"",$line);
                //$data = json_decode($this->DB_READFILE("data/media/recs/".$recId.".json"),true);
                //$data['rid'] = $recId;

                // if we are trying to filter by one of the options on the media page
                if(count($a) > 1){
                  $passFilter = true;
                  if(isset($a['filter_type']) && $a['filter_type'] != ""){ // check the file type
                    if(trim(strtolower($a['filter_type'])) == "jpg" || trim(strtolower($a['filter_type'])) == "png"){
                      if((trim(strtolower($d['filetype'])) === "jpg" || trim(strtolower($d['filetype'])) === "png") && $passFilter == true){
                        $passFilter = true;
                      }else{
                        $passFilter = false;
                      }
                    }else{
                      if(trim(strtolower($a['filter_type'])) == trim(strtolower($d['filetype'])) && $passFilter == true){
                        $passFilter = true;
                      }else{
                        $passFilter = false;
                      }
                    }
                    // if(trim(strtolower($a['filter_type'])) == trim(strtolower($d['filetype'])) && $passFilter == true){
                    //   $passFilter = true;
                    // }else{
                    //   $passFilter = false;
                    // }
                  }
                  if(isset($a['filter_keyword']) && $a['filter_keyword'] != ""){ // check the file type
                    if(strpos(trim(strtolower($d['tags'])),trim(strtolower($a['filter_keyword']))) !== false && $passFilter == true){
                      $passFilter = true;
                    }else{
                      $passFilter = false;
                    }
                  }
                  if(isset($a['filter_startdate']) && $a['filter_startdate'] != "" && isset($a['filter_enddate']) && $a['filter_enddate'] != ""){ // check a date range for when the file was created

                    // convert the timestamp captured in the data file to be yyyymmdd so that we can numerically compare it
                    $dateStamp = date_format(date_create($d['timestamp']),"Ymd");
                    $start = date_format(date_create($a['filter_startdate']),"Ymd");
                    $end = date_format(date_create($a['filter_enddate']),"Ymd");
                    if($dateStamp >= $start && $dateStamp <= $end && $passFilter == true){
                      $passFilter = true;
                    }else{
                      $passFilter = false;
                    }
                  }
                  if($passFilter == true){
                    $return[] = $d; // read the data record
                  }
                }else{
                  $return[] = $d;
                }


            //}
        }
        // fclose($file);
      }
      return $return; // return the media records that we found
    }


    // this method will build out the pagination items for each page and how many page requirement
    // a = count of all items
    function PAGI_INIT($a="") {
      $count_pages = ceil($a / $this->_items);
      if(isset($_GET['page']) && $_GET['page']!="")
        $page = $_GET['page'];
      else
        $page = '1';
        ($page > $count_pages||!is_numeric($page))?$page=1:'';
      if($page!=1)
        $start = ($page - 1) * $this->_items;
      else
      $start = 0;

      $this->_page = $page;
      $this->_count = $count_pages;
      return $start;
    }

    // this method will build out the pagination options url that can be used to go throw pages
    // a = the array of get parameters that was passed to the page that the filter is running on
    // b = the new parameter name
    // c = the new parameter value
    function PAGI_BUILDPAGIURL($a="",$b="",$c=""){
      $params = $a;
      unset($params[$b]);
      $params[$b] = $c;
      return basename($_SERVER['PHP_SELF']).'?'.http_build_query($params);
    }


    // this method will build out the pagination list 'first previous numbers next last'
    function PAGI_SET() {
      $return = "";
      $return .= '<ul>';

      $first = $this->_page - 1;
      if ($first == 0) {
        $return .= '<li> <a href="" class="disabled neopaginationoff" title="can\'t access first page"> 	« First </a>  </li>';
      } else {
        $return .= '<li> <a href="' . $this->PAGI_BUILDPAGIURL($_GET,'page',1) . '" title="click here to go to The First News Page"> 	« First </a>  </li>';
      }

      $prev = $this->_page - 1;
      if ($prev == 0) {
        $return .= '<li> <a href="" class="disabled neopaginationoff" title="can\'t access previous page"> ‹ Previous </a>  </li>';
      } else {
        $return .= '<li> <a href="' . $this->PAGI_BUILDPAGIURL($_GET,'page',$prev) . '" title="click to go to Previous Page"> ‹ Previous </a>  </li>';
      }

      $return .= '<li>';
      $return .= '<form name="pagination" class="pagination" method="get" action="">';
      $return .= '<select class="neopagi neoselect" >';
      for ($i=1; $i<=$this->_count; $i++) {
        if($i==$this->_page) :
          $return .= '<option disabled selected> ' . $i . ' </option>';
        else :
          $return .= '<option value="' . $this->PAGI_BUILDPAGIURL($_GET,'page',$i) . '"> ' . $i . ' </option>';
        endif;
      }
      $return .= '</select>';
      $return .= '</form>';
      $return .= '</li>';

      $next = $this->_page + 1;
      if ($next > $this->_count) {
        $return .= '<li> <a href="" class="disabled neopaginationoff" title="can\'t access next page"> 	Next › </a>  </li>';
      } else {
        $return .= '<li> <a href="' . $this->PAGI_BUILDPAGIURL($_GET,'page',$next) . '" title="click to go to Next Page"> 	Next › </a>  </li>';
      }

      $last = $this->_page + 1;
      if ($next > $this->_count) {
        $return .= '<li> <a href="" class="disabled neopaginationoff" title="can\'t access last page"> Last » </a>  </li>';
      } else {
        $return .= '<li> <a href="' . $this->PAGI_BUILDPAGIURL($_GET,'page',$this->_count) . '" title="click here to go to The Last News Page"> Last » </a>  </li>';
      }

      $return .= '</ul>';
      if(isset($_GET['tool'])) { $return = ""; }
      return $return;
    }


    // this method will save CSV information that was entered or updated
    // a = any post variables that may exist
    // b = any files that were selected
    function CSV_SAVE($a="",$b=""){
      $this->post = $a;
      $return = $data = $params = $notfound = $rids = array();

      // need to check and see if we need to be aware of a field max for the media being selected
      $tool = json_decode($this->DB_READFILE("config/tools.json"),true);
      $fields = $tool[strtoupper($this->post['tools'])]['fields'];

      $return = ["failure",$this->responses['failure'][0]]; // set the return to the failure state to begin
      // save the original image to temp so that we can work on it from there and not touch the original
      $result = move_uploaded_file($b['filetoupload']['tmp_name'],"temp/import.csv");
      $file = fopen('temp/import.csv', 'r');
      $i = 0;
      while (($line = fgetcsv($file)) !== FALSE) {
        //$line is an array of the csv elements
        if ( $i==0 ) {
          foreach ($fields as $k => $f) { // check if field in main CSV File
            if (!in_array($k, $line)) {
              $notfound[$this->post['tools'].'_'.$k] = 'N/A';
            }
          }
          foreach ($line as $k => $l) {
            $params[] = $this->post['tools'].'_'.$l;
          }
          $data[] = $params;
        } else {
          $item = array();
          foreach ($line as $k => $l) {
            if(array_filter($line)) {
              $item[$params[$k]] = $l;
            }
          }
          if (!empty($item)) : $data[] = $item; endif;
        }
        $i++;
      }
      fclose($file);
      if (!empty($data)) {
        // clean all records
        $files = scandir('data/'.$this->post['tools'].'/recs/');
        foreach ($files as $k => $f) {
          if (\strpos($f, '.json') !== false) {
            $rids[] = str_replace('.json', '', $f);
          }
        }
        $return = ["success", $this->responses['success'][0], $data, $notfound, $rids];

      }
      return $return;
    }


  }
?>
