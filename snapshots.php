<?php
require_once "config/config.php";

$snapPath = "../_snapshots/"; // can be tweaked for custom installs, otherwise it expects the _snapshots dir to be at the root of the site the CMS is being used on
date_default_timezone_set('America/New_York');

// this is a fix to allow snapshot to be installed
if (isset($_POST['install']) && $_POST['install'] == "true") {
    # Autoload the dependencies
    require_once "bridge/backup.php";
    $bkup = new BACKUP($snapPath);
    // set the snapshot
    $bkup->setZip();
    // install the current one.
    echo $result = $bkup->installData($_POST['path']);
    exit();
}

// this is a fix to allow snapshot to be installed
if (isset($_POST['delete']) && $_POST['delete'] == "true") {
    # Autoload the dependencies
    require_once "bridge/backup.php";
    $bkup = new BACKUP($snapPath);
    // delete the current one.
    echo $result = $bkup->deleteData($_POST['path']);
    exit();
}

// if the form was posted let's handle it and save the media that was added or has been updated
if (isset($_POST) && count($_POST) > 0) {
    $snap = $_POST['snap'];
    if ($snap) {
        # Autoload the dependencies
        require_once "bridge/backup.php";
        $bkup = new BACKUP($snapPath);
        // set the snapshot
        $bkup->setZip();
    }
}

// get all snapshots
$snapshots = array();
$i = 0;
foreach (glob($snapPath . '*.zip*') as $file) {
    $f = str_replace('../_snapshots/', '', $file);
    $snapshots[$i]['name'] = str_replace('.zip', '', $f);
    $snapshots[$i]['path'] = $file;
    $snapshots[$i]['time'] = date("F d Y H:i:s.", filemtime($file));
    $i++;
}
// print_r($snapshots);

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
    <h1>Snapshots</h1>
    <form name="media_snapshots" id="media_snapshots" action="snapshots.php?action=add" enctype="multipart/form-data" method="post">
      <input type="hidden" name="snap" id="snap" value="true" />
      <fieldset>
        <label>Using a data snapshot, you can back up a data and restore it to an earlier version, Click on snapshot button automatically takes occasional snapshots of your data.</label>
        <br/><br/>
        <label>To manually take a snapshot, click on snapshot button!</label>
      </fieldset>
      <fieldset>
        <input type="submit" value="Snapshot" name="snapshot_submit" id="snapshot_submit" data-type="snapshot_submit" title="Click here to take a snapshot" />
      </fieldset>
    </form>

    <table class="neotable display" cellspacing="0" width="100%" id="neosnapt">
      <thead>
        <tr>
          <th>Snapshots</th>
          <th>Date</th>
          <th>Action</th>
        </tr>
      </thead>
      <tfoot>
        <tr>
          <th>Snapshots</th>
          <th>Date</th>
          <th>Action</th>
        </tr>
      </tfoot>

      <tbody>
        <?php if (isset($snapshots) && !empty($snapshots)) {
    foreach ($snapshots as $key => $value) {
        echo "<tr id=\"" . $value['name'] . "\">";
        echo "<td>" . $value['name'] . "</td>";
        echo "<td>" . $value['time'] . "</td>";
        echo "<td><a href=\"snapshots.php?action=add\" data-name=\"" . $value['name'] . "\" data-path=\"" . $value['path'] . "\" class=\"install_snapshot\">Install</a> &nbsp;|&nbsp; <a href=\"snapshots.php?action=delete\" data-name=\"" . $value['name'] . "\" data-path=\"" . $value['path'] . "\" class=\"delete_snapshot\">Delete</a> &nbsp;|&nbsp; <a href=\"" . $value['path'] . "\" title=\"click here to download this snapshot\"  download>Download</a></td>";
        echo "</tr>";
    }
}?>
      </tbody>

    </table>

  </section>

</div>
<?php
include "includes/footer.php";
include "includes/pageend.php";
?>
