<?php

require_once "config/config.php";

/************************************************************
index.php

This is the main index file for the CMS, it will contain
quick access to manage users, media, and a few entries
from each of the log indexes that are stored

 ************************************************************/

// get the name of the user that is currently logged in to the system
$username = $u->USERS_GETNAME();

// show a few of the most recent activity entries
$activity = $l->LOG_LIST("activity", DASHLOGMAX, true);

// show a few of the most recent security entries
$security = $l->LOG_LIST("security", DASHLOGMAX, true);

// show a few of the most recent error entries
$errors = $l->LOG_LIST("errors", DASHLOGMAX, true);

// get a count of all of the media items in the system
$mCount = $m->MEDIA_COUNT();

// get a count of all of the user accounts in the system
$uCount = $u->USERS_COUNT();

?>
<?php include "includes/pagestart.php";?>
</head>
<body id="dashboard">
  <?php include "includes/header.php";?>
  <div id="loaderpanel"><div class="loader">Loading...</div></div>
  <div id="contentarea">
    <h1>Hello <?=$username?>!</h1>

    <?php
$sortUI = "";
if (isset($_SESSION['userid']) && !empty($_SESSION['userid'])) {
    $sortUI = $_SESSION['userid'];
}
?>
    <section class="sortable__list js-sortable" data-page="home" data-user="<?=$sortUI;?>">
      <?php
$json = file_get_contents("scripts/json/home.json");
$data = json_decode($json, true);
$t = 0;
if (isset($data)):
    $animdelay = 100;
    $animtype = 'fadeIn';
    foreach ($data as $key => $value):
        if ($key == $_SESSION['userid']) {
            $t = 1;
            foreach ($value as $k => $v) {
                require_once "scripts/pages/sortable/page-" . $v . ".php";
                $animdelay = $animdelay + 100;
            }
        }
    endforeach;
endif;
if ($t == 0) {
    $animdelay = 100;
    for ($i = 1; $i < 6; $i++) {
        require_once "scripts/pages/sortable/page-" . $i . ".php";
        $animdelay = $animdelay + 100;
    }
}
?>
    </section>

  </div>
  <?php include "includes/footer.php";?>
<?php include "includes/pageend.php";?>
