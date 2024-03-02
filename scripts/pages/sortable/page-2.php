<article class="sortable__item" data-order="2">
  <div class="item animate" data-anim-type="<?= $animtype; ?>" data-anim-delay="<?=$animdelay; ?>">
    <legend><span class="fa fa-pencil"></span> User Manager</legend>
    You currently have <?=$uCount?> user accounts on this system.<br />
    <br />
    <a href="users.php?action=add" title="Add New User" class="button">Add</a><a href="users.php?action=edit" title="Edit Existing User" class="button">Edit</a><a href="users.php?action=remove" title="Remove Existing User" class="button">Remove</a>
  </div>
</article>
