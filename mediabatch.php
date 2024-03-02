<?php
require_once "config/config.php";

// let's check to make sure that GD is installed and working, otherwise throw an error
if (!$m->MEDIA_CHECKSUPPORT()) {
    $db->APP_DIE(["No support for media manipulation found.  You will be able to manage media already uploaded but will not be able to add any new media."]);
}

$response = ["&nbsp;", ""]; // prime the response value to be used in the form

// if the form was posted let's handle it and save the media that was added or has been updated
if (isset($_POST) && count($_POST) > 0) {

    function runSave($a = "")
    {
        global $m, $response, $_POST;
        $result = $m->MEDIA_BATCH($a, $_POST); // pass the location of the temp folder to process
        $response = [$result[1], $result[0]];
        if ($result[0] == "success") {
            // we need to clean out the temp folder
            sleep(3);
            $m->MEDIA_BATCHCLEAN("temp/" . $a);
        }
    }

    // handle the extraction of the zip here
    $zip = new ZipArchive;
    if ($zip->open($_FILES['filetoupload']['tmp_name'])) { // opened the zip file successfully
        $tempName = str_replace(".zip", "", $_FILES['filetoupload']['name']);
        $zip->extractTo("temp/" . $tempName);
        if ($zip->close()) { // if we are done closing the connection, move on to the saving each
            runSave($tempName);
        }
    } else { // unpack failed
        die("unzip failed");
    }
}

// figure out the max file size that is allowed
$max_size = (int) ini_get('post_max_size');
$upload_max = (int) ini_get('upload_max_filesize');
$max_size = ($max_size >= $upload_max ? $max_size : $upload_max);
$timeStamp = date_format(date_create(), "Y-m-d H:i:s");

// get the list of allowed file types
$fileTypes = $m->MEDIA_TYPES();
$supportedFileTypes = "";
foreach ($fileTypes as $f) {
    $supportedFileTypes .= ($supportedFileTypes == "" ? "" : ", ") . $f;
}

include "includes/pagestart.php";
?>
</head>
<body id="media">
<?php

include "includes/header.php";
?>
<div id="loaderpanel"><div class="loader">Loading...</div></div>
<div id="contentarea">

  <section class="neo__medoabatch">
    <h1>Batch Upload</h1>
    <form name="media_batchupload" id="media_batchupload" action="mediabatch.php?action=add" enctype="multipart/form-data" method="post">
      <input type="hidden" name="maxfilesize" id="maxfilesize" value="<?=$max_size?>" />
      <input type="hidden" name="filetypes" id="filetypes" value="<?=str_replace(" ", "", $supportedFileTypes)?>" />
      <input type="hidden" name="timestamp" id="timestamp" value="<?=$timeStamp?>" />
      <input type="hidden" name="caption" id="caption" value="" />
      <input type="hidden" name="subcaption" id="subcaption" value="" />
      <input type="hidden" name="link" id="link" value="" />
      <input type="hidden" name="filename" id="filename" value="" />
      <div id="response" class="<?=$response[1]?>"><?=$response[0]?></div>
      <fieldset>
          <label>Batch To Upload</label>
          <input type="file" data-type="filepath" name="filetoupload" id="filetoupload" /><p>The zip file to upload, all files within must match normal media parameters</p>
      </fieldset>
      <fieldset>
          <!-- <label>Tags</label> -->
          <input type="text" data-type="title" name="tags" id="tags" placeholder="house,dog,man walking" /><p>Comma separated list of tags to apply to all items being uploaded</p>
      </fieldset>
      <fieldset>
          <input id="batch_cancel" name="batch_cancel" type="button" value="Cancel" data-type="tool_cancel" title="Click here to cancel">
          <input type="submit" value="Upload" name="batch_submit" id="batch_submit" data-type="batch_submit" title="Click here to upload" />
      </fieldset>
    </form>
  </section>

</div>
<?php
include "includes/footer.php";
include "includes/pageend.php";
?>
