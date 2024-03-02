<?php
require_once "config/config.php";
/************************************************************
login.php

This file is the main login screen for users to securely
access the CMS.  It also handles logout requests.

 ************************************************************/

// has the user requested to logout of the system?
if (isset($_GET['logout'])) {
    $u->USERS_LOGOUT();
}

// set the default response message and style
$responseOptions = array(
    "ERROR: Invalid account | <a href=\"forgot.php\" title=\"Click here to have your password reset\">Forgot your password?</a>",
    "<a href=\"forgot.php\" title=\"Click here to have your password reset\">Forgot your password?</a>",
);
$response = [$responseOptions[1], ""];

// if the form was submitted, check their login creds to see if it is not empty and valid
if (isset($_POST['logmein']) && $_POST['logmein'] === "true") {
    if (isset($_POST[$_SESSION['loginfields'][0]]) && $_POST[$_SESSION['loginfields'][0]] !== "" && isset($_POST[$_SESSION['loginfields'][1]]) && $_POST[$_SESSION['loginfields'][1]] !== "") { // quick validation for empty
        $result = $u->USERS_LOGIN($_POST);
        if (!$result) {
            $response = [$responseOptions[0], "error"];
        }
    } else {
        $response = [$responseOptions[0], "error"];
    }
}

// get random field names for user nad pword to prevent brute-force attack
$randId = $db->DB_GENERATEID();
$fieldNames = ["user" . $randId, "pword" . $randId];
$_SESSION['loginfields'] = $fieldNames;
?>
<?php include "includes/pagestart.php";?>
</head>
<body id="login">

  <?php include "includes/header.php";?>

  <div id="contentarea">

      <section class="neo__loginarea">
        <div>
          <h1>Login</h1>

          <form name="login" id="login" class="neo__forms" action="login.php" method="post">
            <input type="hidden" name="logmein" id="logmein" value="true" />

            <fieldset>
              <input type="text" name="<?=$fieldNames[0]?>" id="<?=$fieldNames[0]?>" />
              <label>Username</label>
              <p>account number or email address</p>
            </fieldset>

            <fieldset>
              <input type="password" name="<?=$fieldNames[1]?>" id="<?=$fieldNames[1]?>" />
              <label>Password</label>
            </fieldset>

            <fieldset class="<?=$response[1]?>">
              <label id="messages"><?=$response[0]?></label>
            </fieldset>

            <fieldset>
              <input type="submit" value="Access My Account" name="login_submit" id="login_submit" data-type="login_submit" title="Click here to access your account" />
            </fieldset>

          </form>

        </div>
      </section>

  </div>




  <?php include "includes/footer.php";?>
<?php include "includes/pageend.php";?>
