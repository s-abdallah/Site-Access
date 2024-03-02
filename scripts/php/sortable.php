<?php
  $userid = '';
  if (isset($_POST['data']) && !empty($_POST['data'])) {
    $result = $_POST['data'];
    $page = $_POST['page'];
    $userid = $_POST['user'];
    if (empty($userid)) {
      $userid = '4438bfa741090c5ae1bdf5e29c2712cd';
    }
    $file = '../json/'.$page.'.json';
    $json = file_get_contents($file);
    $data = json_decode($json, true);
    $edit = false;
    foreach ($data as $key => $entry) {
      if ($entry['userid'] == $userid): $edit = true; endif;
    }
    // update the sort value for exists users
    if ($edit) {
      foreach ($data as $key => $entry) {
        if ($key == $userid) {
          $data[$key] = $result;
        }
      }
    } else {
      $data[$userid] = $result;
    }

    file_put_contents('../json/'.$page.'.json', json_encode($data, JSON_FORCE_OBJECT));
  }
?>
