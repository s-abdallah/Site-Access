<?php
require_once "config/config.php";

// let's check to make sure that GD is installed and working, otherwise throw an error
if (!$m->MEDIA_CHECKSUPPORT()) {
    $db->APP_DIE(["No support for media manipulation found.  You will be able to manage media already uploaded but will not be able to add any new media."]);
}

$response = ["&nbsp;", ""]; // prime the response value to be used in the form

// if the form was posted let's handle it and save the media that was added or has been updated
if (isset($_POST) && count($_POST) > 0) {

    $result = $m->CSV_SAVE($_POST, $_FILES);
    $csv = $data = $notfound = array();
    $notfound = $result[3];
    if ($result[0] == 'success') {
        $csv = $result[2];

        foreach ($result[4] as $id) {
            $res = $t->TOOLS_REMOVE($id, strtolower($_POST['tools']));
        }
    }

    // save the new record or the edits that were made
    foreach ($csv as $key => $value) {
        if ($key != 0) {
            $save = array();
            $save = array_merge($value, $notfound);
            // figure out what we need to use as the id
            $id = (isset($_GET['rid']) ? trim($_GET['rid']) : $db->DB_GENERATEID());
            $result = $t->TOOLS_SAVE($id, $save, $_POST['tools'], $_GET['action']);
        }
    }

    // we need to clean out the temp folder
    sleep(3);
    unlink("temp/import.csv");

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

// GET ALL Available TOOLS
$toolsOption = '';

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
    <h1>CSV Upload</h1>
    <form name="media_batchupload" id="media_batchupload" action="import.php?action=add" enctype="multipart/form-data" method="post">
      <input type="hidden" name="maxfilesize" id="maxfilesize" value="<?=$max_size?>" />
      <input type="hidden" name="filetypes" id="filetypes" value="<?=str_replace(" ", "", $supportedFileTypes)?>" />
      <input type="hidden" name="timestamp" id="timestamp" value="<?=$timeStamp?>" />
      <input type="hidden" name="caption" id="caption" value="" />
      <input type="hidden" name="subcaption" id="subcaption" value="" />
      <input type="hidden" name="link" id="link" value="" />
      <input type="hidden" name="filename" id="filename" value="" />
      <div id="response" class="<?=$response[1]?>"><?=$response[0]?></div>
      <fieldset>
          <label>CSV To Upload</label>
          <input type="file" data-type="filepath" name="filetoupload" id="filetoupload" /><p>The csv file to upload, all file within must match normal tools parameters</p>
      </fieldset>
      <fieldset>
          <label>Tools</label>
          <?php
foreach ($useImportOn as $k => $t) {
    $toolsOption .= '<option value="' . $t . '">' . $t . '</option>';
}
echo "<select data-type=\"select\" class=\"neochosen\" name=\"tools\" id=\"neotools\" required=\"required\"> " . $toolsOption . " </select>";
?>
      </fieldset>
      <fieldset>
          <input id="batch_cancel" name="batch_cancel" type="button" value="Cancel" data-type="tool_cancel" title="Click here to cancel">
          <input type="submit" value="Upload" name="batch_submit" id="batch_submit" data-type="batch_submit" data-confirm="Are you sure you want to batch upload this CSV file? You are going to lose current data!" title="Click here to upload" />
      </fieldset>
    </form>
  </section>

</div>
<?php
include "includes/footer.php";
include "includes/pageend.php";
?>
