<?php
require_once "config/config.php";

$response = "&nbsp;";
$responseClass = "";
$path = "data/" . $_GET['tool'];

// this is if we want to remove a record from the system completely
if (isset($_GET['remove']) && $_GET['remove'] === "true" && !isset($_GET['completed'])) {

    $result = $t->TOOLS_REMOVE($_GET['rid'], strtolower($_GET['tool']));

    // jump back to the base remove list interface for the user to remove another one if desired
    //header("location:tool.php?tool=".$_GET['tool']."&action=remove&completed=".($result?"true":"false"));
    header("location:tool.php?tool=" . $_GET['tool'] . "&action=remove&completed=true");
    exit();
}

// if a record was successfully removed from the system, we should let them know that it was successful
if ($_GET['action'] === "remove" && isset($_GET['completed']) && $_GET['completed'] === "true" && !isset($_GET['rid'])) {
    $responseClass = "success";
    $response = "SUCCESS: The record has been removed from the system.";
} else if ($_GET['action'] === "remove" && isset($_GET['completed']) && $_GET['completed'] === "false" && !isset($_GET['rid'])) {
    $responseClass = "error";
    $reponse = "ERROR: Your request could not be completed.  Please check the error logs.";
}

// if we are saving the form data after a successful form submission
if (isset($_POST) && count($_POST) > 0) {

    // figure out what we need to use as the id
    $id = (isset($_GET['rid']) ? trim($_GET['rid']) : $db->DB_GENERATEID());

    // save the new record or the edits that were made
    $result = $t->TOOLS_SAVE($id, $_POST, $_GET['tool'], $_GET['action']);

    // set the response message for an edit or an add (which as a link to edit)
    if ($result === true) {
        $responseClass = "success";
        if ($_GET['action'] == "add") {
            $response = "SUCCESS: Your entry has been saved! - <a href=\"tool.php?tool=" . $_GET['tool'] . "&action=edit&rid=" . $id . "\" title=\"\">Click here to make changes</a>";
        } else {
            $response = "SUCCESS: Your changes have been saved!";
        }
    }
}

// let's sort out what the requested action is and what the form should display
$form = $f->FORM_BUILD($_GET['tool'], $_GET['action'], (isset($_GET['rid']) ? $_GET['rid'] : "")); // always pass an rid????
$records = "";
// if we are editing or removing, build out a drop down list of content that can be chosen
if (isset($_GET['action']) && ($_GET['action'] == "edit" || $_GET['action'] == "remove")) {
    // the tool, the id of the record being edited, what fields to show in the results, how to order the results
    $records = $db->DB_CONTENT_LIST($_GET['tool'], (isset($_GET['rid']) ? $_GET['rid'] : ""), (isset($form[1]) && count($form[1]) > 0 ? $form[1] : ""));
}
?>
<?php include "includes/pagestart.php";?>
</head>
<body id="tools">
  <?php include "includes/header.php";?>
  <div id="loaderpanel"><div class="loader">Loading...</div></div>
  <div id="contentarea">
      <h1>Tools - <?=ucwords($_GET['tool'])?> - <?=ucwords($_GET['action'])?></h1>

      <?php if ($_GET['action'] != 'all') {?>
        <?php if (isset($records)) {
    echo $records;
}
    ?>
        <form class="noe__tool neo__forms" name="<?=$_GET['tool']?>_<?=$_GET['action']?>" id="<?=$_GET['tool']?>_<?=$_GET['action']?>" action="<?=basename($_SERVER['PHP_SELF'])?>?tool=<?=$_GET['tool']?>&action=<?=$_GET['action']?><?=(isset($_GET['rid']) ? "&rid=" . $_GET['rid'] : "")?>" method="post" enctype="multipart/form-data">
            <div id="response" class="<?=$responseClass?>"><?=$response?></div>
            <?=$form[0]?>
        </form>
      <?php } else {?>
        <section class="neo__alltools animate" data-anim-type="fadeIn" data-anim-delay="200">
          <?=$result = $t->TOOLS_ALL($_GET['tool'], $_GET['action']);?>
        </section>
      <?php }?>

  </div>
  <?php include "includes/backtotop.php";?>
  <?php include "includes/footer.php";?>
<?php include "includes/pageend.php";?>
