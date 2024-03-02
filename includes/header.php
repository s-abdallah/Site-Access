<header>
  <?php if(isset($_SESSION['userid']) && $_SESSION['userid'] !== ""){ ?>
  <nav>
    <div>
      <a href="#" class="nav-open">
        <div class="inner"></div>
      </a>
    </div>
    <ul>
      <li class="dashboard"><a href="index.php" title="Dashboard">Dashboard</a></li>
      <li class="tools" title="Tools"><a href="" class="not-active">Tools<i></i></a>
        <?=$t->TOOLS_LIST(true)?>
      </li>
      <li class="media" title="Media"><a href="" class="not-active">Uploads<i></i></a>
          <ul>
            <li><a href="media.php?action=add" title="Upload New Item">Upload New Item</a></li>
            <li><a href="mediabatch.php?action=add" title="Batch Upload Multiple Items">Batch Upload</a></li>
            <li><a href="media.php?action=manage" title="Manage Uploads">Manage Uploads</a></li>
          </ul>
      </li>
      <li class="logs" title="Logs"><a href="" class="not-active">Logs<i></i></a>
          <ul>
            <li><a href="logs.php?type=activity" title="Activity">Activity</a></li>
            <li><a href="logs.php?type=errors" title="Errors">Errors</a></li>
            <li><a href="logs.php?type=security" title="Security">Security</a></li>
            <li><a href="snapshots.php?action=add" title="Snapshots">Snapshots</a></li>
            <li><a href="import.php?action=add" title="Import CSV">Import CSV</a></li>
          </ul>
      </li>
      <li class="users"><a href="" class="not-active">Users<i></i></a>
        <ul>
          <li><a href="users.php?action=add" title="Activity">Add New User</a></li>
          <li><a href="users.php?action=edit" title="Errors">Edit Existing Users</a></li>
          <li><a href="users.php?action=remove" title="Security">Remove Users</a></li>
        </ul>
      </li>
      <?php if(TESTMODE){ ?>
      <li class="query"><a href="query.php" title="Query Builder">Query Builder</a></li>
      <li class="help"><a href="help.php" title="Help">Help</a></li>
      <?php } ?>
      <li><a href="login.php?logout" title="Logout">Logout</a></li>
    </ul>
  </nav>
  <?php } ?>
</header>


<?php include("includes/nav.php"); ?>
