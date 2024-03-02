<?php

require_once "config/config.php";

// this is if we want to remove a record from the system completely
if (isset($_GET['remove']) && $_GET['remove'] === "true" && !isset($_GET['completed'])) {
    $result = $u->USERS_REMOVE($_GET['rid']);

    // jump back to the base remove list interface for the user to remove another one if desired
    header("location:users.php?action=remove&completed=" . ($result ? "true" : "false"));
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

// we need to clear any messages once the user selects a new record from say the edit/remove dropdown!!!!!!!!!!!!

// if we are saving the form data after a successful form submission
if (isset($_POST) && count($_POST) > 0) {

    // generate a new random ID to be used to track this record forever
    //$id = $db->DB_GENERATEID();
    $id = (isset($_GET['rid']) && $_GET['rid'] != "" ? $_GET['rid'] : $db->DB_GENERATEID()); // either a new id or use the one that we are editing

    // save the new record or the edits that were made
    $result = $u->USERS_SAVE($id, $_POST, $_GET['action']);

    // set the response message for an edit or an add (which as a link to edit)
    if ($result === true) {
        $responseClass = "success";
        if ($_GET['action'] == "add") {
            $response = "SUCCESS: Your entry has been saved! - <a href=\"users.php?action=edit&rid=" . $id . "\" title=\"Click here to make changes\">Click here to make changes</a>";
        } else {
            $response = "SUCCESS: Your changes have been saved!";
        }
    } else {
        $responseClass = "error";
        $reponse = "ERROR: Your request could not be completed.  Please check the error logs.";
    }
}

// let's sort out what the requested action is and what the form should display
$form = $f->FORM_BUILD("users", $_GET['action'], (isset($_GET['rid']) ? $_GET['rid'] : ""));

// if we are editing or removing, build out a drop down list of content that can be chosen
if (isset($_GET['action']) && ($_GET['action'] == "edit" || $_GET['action'] == "remove")) {
    $records = $u->USERS_LIST((isset($_GET['rid']) ? $_GET['rid'] : ""));
}

// need to think about a way to handle grouping results coming back in the select to edit dropdown????!!!!!????

?>
<?php include "includes/pagestart.php";?>
</head>
<body id="users">
  <?php include "includes/header.php";?>
  <div id="loaderpanel"><div class="loader">Loading...</div></div>
  <div id="contentarea">

    <section class="neeo_users">
      <h1>Users - <?=ucwords($_GET['action'])?></h1>
      <?php if (isset($records)) {
    echo $records;
}
?>
      <form name="users_<?=$_GET['action']?>" id="users_<?=$_GET['action']?>" class="neo__forms <?=$_GET['action']?>" action="users.php?action=<?=$_GET['action']?><?=(isset($_GET['rid']) ? "&rid=" . $_GET['rid'] : "")?>" method="post" enctype="multipart/form-data">
          <div id="response" class="<?php if (isset($response)) {
    echo $responseClass;
}
?>"><?php if (isset($response)) {
    echo $response;
}
?></div>
          <?=$form[0]?>
      </form>
    </section>

  </div>
  <?php include "includes/footer.php";?>
<?php include "includes/pageend.php";?>
