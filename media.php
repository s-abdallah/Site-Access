<?php
require_once "config/config.php";

// set the fileName to be loaded
$fileName = "media";

// let's check to make sure that GD is installed and working, otherwise throw an error
if (!$m->MEDIA_CHECKSUPPORT()) {
    $db->APP_DIE(["No support for media manipulation found.  You will be able to manage media already uploaded but will not be able to add any new media."]);
}

// this will remove selected media from the system
if (isset($_GET['remove']) && $_GET['remove'] === "true" && isset($_POST['items']) && $_POST['items'] != "") {
    echo ($m->MEDIA_REMOVE($_POST['items']));
    exit();
}

// this is a fix to allow media to be associated to entries without needing to create a new script
if (isset($_POST['map']) && $_POST['map'] == "true") {
    echo ($m->MEDIA_MAP($_POST['id'], $_POST['rid'], $_POST['field'], $_POST['tool']));
    exit();
}

// this is a fix to allow us to get a count of media associated to entries without needing to create a new script
if (isset($_POST['mapcount']) && $_POST['mapcount'] == "true") {
    echo ($m->MEDIA_MAP_COUNT($_POST['id'], $_POST['rid']));
    exit();
}

// this will open an existing relation file for media and rewrite contents for a specific section of it
if (isset($_POST['reorder']) && $_POST['reorder'] == "true") {
    $data = json_decode($db->DB_READFILE("data/media/relations/" . $_POST['rid'] . ".json"), true);
    $data[$_POST['field']] = $_POST['order'];

    // now to write the new order back to the file to be stored permanently
    $file = fopen("data/media/relations/" . $_POST['rid'] . ".json", "w+");
    $result = fwrite($file, json_encode($data));
    fclose($file);
    echo "success";
    exit();
}

$response = ["&nbsp;", ""]; // prime the response value to be used in the form

// $maxResultsPerPage = 5;

include "includes/pagestart.php";
?>
</head>
<body id="media">
<?php

// if we are on a page that is NOT being opened in a lightbox, we need to show the main header of the page
if (isset($_GET['action']) && ($_GET['action'] != "select" && $_GET['action'] != "edit") && !isset($_GET['full'])) {
    include "includes/header.php";
}

// if the form was posted let's handle it and save the media that was added or has been updated
if (isset($_POST) && count($_POST) > 0) {
    $result = $m->MEDIA_SAVE($_GET, $_POST, $_FILES);
    $response = [$result[1], $result[0]];
}

// check to see if there is a script file for this page that needs to be loaded in
// all PHP logic should be done in external script and then variable results echoed/used on this page
if (file_exists("scripts/pages/" . $fileName . ".php")) {
    require_once "scripts/pages/" . $fileName . ".php";
}

?>
<div id="loaderpanel"><div class="loader">Loading...</div></div>
<div id="contentarea" <?php if (isset($_GET['action']) && ($_GET['action'] == "select" || $_GET['action'] == "edit") || isset($_GET['full'])) {echo "class=\"noheader\"";}?>>
  <?php if (isset($_GET['action']) && $_GET['action'] != "select") {?><h1>Uploads - <?php echo ucwords($_GET['action']); //echo (isset($_GET['rid']) && $_GET['rid'] != ""?" - ".$data['filepath']:""); ?></h1><?php }?>

    <?php if (!isset($_GET['action']) || ($_GET['action'] === "add" || $_GET['action'] === "edit")) {?>

      <?php require_once "scripts/pages/media-form.php";?>

    <?php } else if (isset($_GET['action']) || $_GET['action'] === "manage") {?>

      <div class="neo__media-confirm">
        <form id="remove_confirm" name="remove_confirm">
          [ <span>0</span> ] Items Selected For Removal<br />
          <input id="remove_cancel" name="remove_cancel" type="button" value="Cancel" data-type="remove_cancel" title="Click here to cancel">
          <input type="submit" value="Confirm Removal" name="remove_submit" id="remove_submit" data-type="remove_submit" title="Click here to remove selected upload" />
        </form>
      </div>
      <div class="neo__media-filter">
        <?=$filters?>
      </div>
      <div class="neo__pagination">
        <?=$pagination?>
      </div>
      <div class="neo__media-list" >
        <?=$thumbnails?>
      </div>
      <div class="neo__pagination">
          <?=$pagination?>
      </div>
    <?php }?>
</div>
<?php if (isset($_GET['action']) && ($_GET['action'] != "select" && $_GET['action'] != "edit") && !isset($_GET['full'])) {include "includes/backtotop.php";include "includes/footer.php";}?>
<?php include "includes/pageend.php";?>
