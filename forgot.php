<?php
require_once "config/config.php";
/************************************************************
forgot.php

This file provides a way for users to request that their
account password be reset.

 ************************************************************/

// set the default response message and style
$responseOptions = array(
    "SUCCESS: An email was sent to the address for the account | <a href=\"login.php\" title=\"Click here to access your account\">Access My Account</a>",
    "ERROR: Please enter your account number or email address | <a href=\"login.php\" title=\"Click here to access your account\">Access My Account</a>",
    "<a href=\"login.php\" title=\"Click here to access your account\">Access My Account</a>",
);
$response = [$responseOptions[2], ""];

// if the form was submitted, let's try and find a matching account and reset the password
if (isset($_POST['forgotpword']) && $_POST['forgotpword'] !== "") {
    $result = $u->USERS_RESETPWORD($_POST['forgotpword']);
    $response = [$responseOptions[0], "success"];
} else if (isset($_POST['forgotpword']) && $_POST['forgotpword'] === "") {
    $response = [$responseOptions[1], "error"];
}
?>
<?php include "includes/pagestart.php";?>
</head>
<body id="login">

  <?php include "includes/header.php";?>

  <div id="contentarea">

    <section class="neo__loginarea">
      <div>

        <h1>Forgot Password</h1>
        <form name="forgot" id="forgot" action="forgot.php" method="post">

          <fieldset >
            <input type="text" name="forgotpword" id="forgotpword" />
            <label>Username</label>
            <p>account number or email address</p>
          </fieldset>

          <fieldset class="<?=$response[1]?>">
            <label id="messages"><?=$response[0]?></label>
          </fieldset>

          <fieldset>
            <input type="submit" value="Reset Password" name="forgot_submit" id="forgot_submit" data-type="forgot_submit" title="Click here to reset your accounts password" />
          </fieldset>

        </form>

      </div>
    </section>

  </div>

  <?php include "includes/footer.php";?>
<?php include "includes/pageend.php";?>
