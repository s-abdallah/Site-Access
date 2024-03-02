<!doctype html>
	<html>
		<head>
			<title>SiteAccess</title>
      <meta charset="UTF-8">
			<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
			<meta name="title" content="SiteAccess" />
			<meta http-equiv="pragma" content="no-cache" />
			<meta http-equiv="revisit" content="15 days" />
			<meta http-equiv="robots" content="none" />
      <meta http-equiv="X-UA-Compatible" content="IE=edge" />
      <meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=no,minimal-ui"/>
			<link rel="shortcut icon" href="_ui/favicon/favicon.ico" />
			<link href="https://fonts.googleapis.com/css?family=Roboto:300" rel="stylesheet">
			<link rel="stylesheet" href="css/fontawesome.css" />
			<link rel="stylesheet" href="css/screen.css" />
      <link rel="stylesheet" href="css/jqueryui.css" />
      <link rel="stylesheet" href="css/jqueryuitheme.css" />
      <link rel="stylesheet" href="css/forms.css" />
			<link rel="stylesheet" href="css/magnific.css" />
			<link rel="stylesheet" href="css/datatables/layout.css" /><!--table-->
			<link rel="stylesheet" type="text/css" href="css/datatables/responsive.css"><!--table-->
			<link rel="stylesheet" type="text/css" href="css/datatables/animations.css"><!--table animations-->
			<link rel="stylesheet" type="text/css" href="css/customscrollbar.css"><!--customscrollbar-->
			<script type="text/javascript" src="scripts/js/modernizr.js"></script>

			<?php
// this is a check to only add tinymce to tool forms as needed
if (strtok(basename($_SERVER['REQUEST_URI']), '?') == "tool.php" && ALLOWWYSIWYG) {
    echo "<script src=\"config/tinymceconfig.js\"></script><script src=\"scripts/js/tinymce/tinymce.min.js\"></script>\n<script>tinymce.init(tinymceConfig);</script>";
}
?>

			<script>
				//console.log("dfhjfghjfghjfgh");
			</script>

      <script src="scripts/js/jquery.js"></script>
      <script src="scripts/js/jqueryui.js"></script>
      <script src="scripts/js/app.js"></script>
      <script src="scripts/js/forms.js"></script>
			<script src="scripts/js/magnific.js"></script>
			<script src="scripts/js/datatables/lib.js"></script><!--table-->
			<script src="scripts/js/datatables/jqueryui.js"></script><!--table-->
			<script src="scripts/js/datatables/responsive.js"></script><!--table-->
			<script src="scripts/js/datatables/responsive.jqueryui.js"></script><!--table-->
			<script src="scripts/js/datatables/visible.min.js"></script><!--table animations-->
			<script src="scripts/js/datatables/animations.min.js"></script><!--table animations-->
			<script src="scripts/js/toucheffects.js"></script>
			<script src="scripts/js/tag-it.js"></script><!--tags-->
			<script src="scripts/js/chosen.jquery.js"></script><!--select-->
			<script src="scripts/js/customscrollbar.js"></script><!--customscrollbar-->
			<script src="scripts/js/jquery.nicescroll.min.js"></script><!--customscrollbar-->
			<script src="scripts/js/multilevelpushmenu.min.js"></script>
