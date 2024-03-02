<?php if(isset($_SESSION['userid']) && $_SESSION['userid'] !== ""){ ?>
<div id="leftmenu" class="animate" data-anim-type="zoomInLeftLarge" data-anim-delay="500">
  <nav>
    <h2><i class="fa fa-reorder collapsed"></i></h2>
    <ul>
        <li class="dashboard"><a href="index.php" title="Dashboard" class="main">Dashboard</a></li>
        <li class="tools">
          <a href="#" class="main"><i class="fa fa-usb"></i>Tools</a>
          <h2><i class="fa fa-usb"></i>Tools</h2>
          <?=$t->TOOLS_LEFTLIST(true)?>
        </li>
        <li class="media">
          <a href="#" class="main"><i class="fa fa-upload"></i>Uploads</a>
          <h2><i class="fa fa-upload"></i>Uploads</h2>
          <ul>
            <li><a href="media.php?action=add" title="Upload New Item">Upload New Item</a></li>
            <li><a href="mediabatch.php?action=add" title="Batch Upload Multiple Items">Batch Upload</a></li>
            <li><a href="media.php?action=manage" title="Manage Uploads">Manage Uploads</a></li>
          </ul>
        </li>
        <li class="logs">
          <a href="#" class="main"><i class="fa fa-calendar"></i>Logs</a>
          <h2><i class="fa fa-calendar"></i>Logs</h2>
          <ul>
            <li><a href="logs.php?type=activity" title="Activity">Activity</a></li>
            <li><a href="logs.php?type=errors" title="Errors">Errors</a></li>
            <li><a href="logs.php?type=security" title="Security">Security</a></li>
            <li><a href="snapshots.php?action=add" title="Snapshots">Snapshots</a></li>
            <li><a href="import.php?action=add" title="Import CSV">Import CSV</a></li>
          </ul>
        </li>
        <li class="users">
          <a href="#" class="main"><i class="fa fa-users"></i>Users</a>
          <h2><i class="fa fa-users"></i>Users</h2>
          <ul>
            <li><a href="users.php?action=add" title="Activity">Add New User</a></li>
            <li><a href="users.php?action=edit" title="Errors">Edit Existing Users</a></li>
            <li><a href="users.php?action=remove" title="Security">Remove Users</a></li>
          </ul>
        </li>
        <?php if(TESTMODE){ ?>
          <li class="query"><a href="query.php" class="main" title="Query Builder">Query Builder</a></li>
          <li class="help"><a href="help.php" class="main" title="Help">Help</a></li>
        <?php } ?>
        <li><a href="login.php?logout" class="main" title="Logout">Logout</a></li>
    </ul>
  </nav>
</div>
<?php } ?>
