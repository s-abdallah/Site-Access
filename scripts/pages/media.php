<?php

// if we are in manage or select mode
if(isset($_GET['action']) && ($_GET['action'] === "manage" || $_GET['action'] === "select")){

  // $filters = $m->MEDIA_BUILDFILTERS($_GET);
  $filters = $f->FORM_BUILDFILTERS($_GET);

  // if we are in manage mode, we need to let the user remove media items
  // if($_GET['action'] != "select"){
  if($_GET['action'] === "manage"){
    $filters .= "</ul></form><!-- <p>NOTE: To remove a media item, click (or press) and hold until item is highlighted with a red X</p> --><p><a class=\"button error js-removemedia\" title=\"Click here to enter removal mode\">Select Media To Be Removed</a></p>";
  }



  $thumbnails = "";

  // regardless of what we are getting below, we need to check and see if we have an mid and whether that file exists to NOT show items that have already been selected in the select panel!!!!!
  $previouslySelected = array();
  if(isset($_GET['mid']) && file_exists("data/media/relations/".$_GET['mid'].".json")){

    // need to add a check in here to see if we are looking for values that fall within a specific field
    $checkData = json_decode($db->DB_READFILE("data/media/relations/".$_GET['mid'].".json"),true);

    // if the field element exists in the data
    if(isset($checkData[$_GET['field']])){
      $previouslySelected = $checkData[$_GET['field']];
    }
  }

  // loop through and build out the grid of media items to be displayed
  $data = $m->MEDIA_LIST((isset($_GET)?$_GET:""));
  $pagination = $start = '';
  if(isset($data[0]) && !empty(isset($data[0]))){
    $start = $m->PAGI_INIT(count($data));
    $item = 1;
    // start the thumbnails
    $thumbnails .="<ul class=\"neoGeffect neoGeffect-1".(isset($_GET['rid']) && $_GET['rid'] != ""?" allowreorder":"")."\">";
    foreach($m->MEDIA_LIST((isset($_GET)?$_GET:"")) as $key => $t){
      if($start == $key && !empty($t['rid'])):
        if(!in_array(trim($t['rid']),$previouslySelected)){

          switch($t['filetype']){
            case "link":  // allows for both default link thumbnail or custom from upload if desired and provided
              if(isset($t['filepath']) && isset($t['name'])){
                // $thumbnail = [$t['link'],"\"../_uploads/".$t['filepath']."-thumbnail".".jpg\"","class=\"magnific-iframe\""];
                $thumbnail = [$t['link'],"\"../_uploads/".str_replace(".","-thumbnail.",$t['name'])."\"","class=\"magnific-iframe\""];
              }else{
                $thumbnail = [$t['link'],"\"ui/linkicon.jpg\"","class=\"magnific-iframe \""];
              }
              break;
            case "vr":
              //$thumbnail = [$t['filepath'],"\"ui/vricon.jpg\"","class=\"magnific-iframe\""];

              if(isset($t['filepath']) && isset($t['name'])){
                $thumbnail = [$t['link'],"\"../_uploads/".str_replace(".","-thumbnail.",$t['name'])."\"","class=\"magnific-iframe\""];
              }else{
                $thumbnail = [$t['link'],"\"ui/vricon.jpg\"","class=\"magnific-iframe\""];
              }

              break;
            case "pdf":
              $thumbnail = ["\"../_uploads/".$t['filepath'].".".$t['filetype']."\"","\"ui/pdficon.jpg\"","class=\"magnific-iframe\""];
              break;
            case "zip":
              $thumbnail = ["\"../_uploads/".$t['filepath'].".".$t['filetype']."\"","\"ui/zipicon.jpg\"",""];
              break;
            case "mov":
              if(isset($t['filepath']) && isset($t['name'])){
                // $thumbnail = [$t['link'],"\"../_uploads/".$t['filepath']."-thumbnail".".jpg\"","class=\"magnific-iframe\""];
                $thumbnail = [$t['link'],"\"../_uploads/".str_replace(".","-thumbnail.",$t['name'])."\"","class=\"magnific-iframe\""];
              }else{
                $thumbnail = [$t['link'],"\"ui/videoicon.jpg\"","class=\"magnific-iframe \""];
              }
              //$thumbnail = [$t['filepath'],"\"ui/videoicon.jpg\"","class=\"magnific-iframe\""];
              break;
            case "mp4":
              $thumbnail = ["\"../_uploads/".$t['filepath'].".".$t['filetype']."\"","\"ui/mp4icon.png\"","class=\"magnific-iframe\""];
              break;
            case "gif":
              $thumbnail = ["\"../_uploads/".$t['filepath'].".".$t['filetype']."\"","\"ui/gificon.png\"","class=\"magnific-iframe\""];
              break;
            default: // handle an image type here
              $thumbnail = ["\"../_uploads/".$t['filepath'].".".$t['filetype']."\"","\"../_uploads/".$t['filepath']."-thumbnail".".".$t['filetype']."\"","class=\"magnific-single\""];
              break;
          }

          $thumbnails .= "<li id=\"".trim($t['rid'])."\" class=\"animate ".(isset($_GET['rid']) && $_GET['rid'] != ""?" dragtosetorder":"")."\" data-anim-type=\"bounceIn\" data-anim-delay=\"200\" >";
          $thumbnails .= "<figure>";
          $thumbnails .= "<img src=".$thumbnail[1]." alt=\"".$t['filepath']."\" />";
          $thumbnails .= "<figcaption>";
          $thumbnails .= "<h3>".strtoupper(trim($t['caption']))."</h3>";
          $thumbnails .= "<span>TAGS: ".ucwords(trim($t['tags']))."</span>";

            if(!isset($_GET['rid']) || $_GET['rid'] === ""){
          $thumbnails .= "<div class=\"neo__btn itzel\">";
          $thumbnails .= "<a title=\"Click here to view this item\" href=".$thumbnail[0]." data-title=\"".ucwords(trim($t['caption']))."\" ".trim($thumbnail[2])."><i class=\"icon icon-eye\"></i><span>View</span></a> ";
          $thumbnails .= "</div>";
            }

            if(!isset($_GET['action']) || $_GET['action'] != "select"){ // don't allow edit or remove during select mode
          $thumbnails .= "<div class=\"neo__btn itzel edit\">";
          $thumbnails .= "<a title=\"Click here to edit this item\" class=\"magnific-iframe\" href=\"media.php?action=edit&rid=".$t['rid']."\" data-title=\"".ucwords(trim($t['caption']))."\" ><i class=\"icon icon-write\"></i><span>Edit</span></a> ";
          $thumbnails .= "</div>";
            }
          $thumbnails .= "<div title=\"Click here to select this item\" class=\"select js-addtorecord\" ".(isset($_GET['tool'])?"data-tool=\"".$_GET['tool']."\"":"")." ".(isset($_GET['field']) && $_GET['field'] != ""?"data-field=\"".$_GET['field']."\"":"")." id=\"".$t['rid']."\" ".($_GET['action'] === "select"?"style=\"display: block;\"":"")." data-rid=\"".(isset($_GET['mid'])?$_GET['mid']:(isset($_GET['rid'])?$_GET['rid']:""))."\">";
          $thumbnails .= "<span class=\"fa fa-bookmark ".(isset($_GET['rid']) && $_GET['rid'] != ""?"selected":"")." fa-3x\"></span></div>";
          $thumbnails .= "</figcaption>";
          $thumbnails .= "</figure>";

            if(!isset($_GET['action']) || $_GET['action'] != "select"){ // don't allow edit or remove during select mode
          $thumbnails .= "<div title=\"Click here to flag this item for removal\" class=\"remove js-flagforremoval\" data-removeid=\"".$t['rid']."\"><span class=\"icon icon-trash\"></span></div>";
          // $thumbnails .= "<div class=\"neo__btn itzel remove\">";
          // $thumbnails .= "<a title=\"Click here to flag this item for removal\" class=\"\" data-title=\"".ucwords(trim($t['caption']))."\"  ><i class=\"icon icon-trash\"></i><span>Remove</span></a> ";
          // $thumbnails .= "</div>";
            }

          $thumbnails .= "</li>";
        }
      if ($item==PAGIITEMS) { break; }
      $item++; $start++;
      endif;
    }
    $thumbnails .= "</ul>";
    $pagination = $m->PAGI_SET();
  }
  if($thumbnails == ""){
    if(isset($_GET['rid'])){
      $thumbnails = "<p>You have not yet added any media items. <br/> Please click on the \"Select Media Items\" button below to begin.</p>";
    }else{
      $thumbnails = "<p>There were no items found matching your filter parameters. <br/> Please adjust and try again.</p>";
    }
  }
}else{



  // I think more of this can be moved into the media class (which I may need to split as it is getting really big!) or maybe even move some into the config file??????

  // are we trying to edit something that already exists?
  if(isset($_GET['rid'])){
    $data = $db->DB_RECORD_GET("media",$_GET['rid']);
  }

  // figure out the max file size that is allowed
  $max_size = (int)ini_get('post_max_size');
  $upload_max = (int)ini_get('upload_max_filesize');
  $max_size = ($max_size >= $upload_max?$max_size:$upload_max);
  $timeStamp = date_format(date_create(),"Y-m-d H:i:s");

  // get the list of allowed file types
  $fileTypes = $m->MEDIA_TYPES();
  $supportedFileTypes = "";
  foreach($fileTypes as $f){
      $supportedFileTypes .= ($supportedFileTypes == ""?"":", ").$f;
  }
}

?>
