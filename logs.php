<?php
require_once "config/config.php";
/************************************************************
logs.php

This file will generate a table view to list the results
of the requested log entries, there is also an option to
build a quick CSV of the requested data

Parameters:
tool - from GET

Return:
a table view of records OR a downloadable CSV file

 ************************************************************/
if (isset($_GET['csv']) && $_GET['csv'] === "true") { // build a CSV
    $fileName = strtoupper($_GET['type']) . "_" . date("Y-m-d") . ".csv";
    // set the correct headers so that the file will automatically try and download
    header("Content-Type: text/csv; charset=utf-8");
    header("Content-Disposition: attachment; filename=" . $fileName);
    $csv = $l->LOGCSV(strtolower($_GET['type']), $fileName, array("Performed By", "Tool", "Action", "Data", "Timestamp")); // build the csv file for download
    die(); // no need to go any further in this case
} else { // show the data on the screen
    $records = $l->LOG_LIST(strtolower($_GET['type']), 0, true);
}
?>
<?php include "includes/pagestart.php";?>
</head>


<body id="logs">

  <?php include "includes/header.php";?>

  <div id="loaderpanel"><div class="loader">Loading...</div></div>

  <div id="contentarea">
      <h1>Logs - <?=ucwords($_GET['type'])?></h1>

      <section class="neo__datatable animate" data-anim-type="fadeIn" data-anim-delay="200">
        <a href="logs.php?type=<?=strtolower($_GET['type'])?>&csv=true" title="Click here to download the data as a CSV" target="_blank" class="button">Download as CSV</a>
        <br/>
        <?=$records?>
        <br/>
        <a href="logs.php?type=<?=strtolower($_GET['type'])?>&csv=true" title="Click here to download the data as a CSV" target="_blank" class="button">Download as CSV</a>
      </section>

  </div>

  <?php include "includes/footer.php";?>
<?php include "includes/pageend.php";?>
