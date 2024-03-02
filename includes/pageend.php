<?php
// if(isset($_GET['tool']) && $_GET['tool'] == "mapmarkers"){
if (USEMAPS && isset($_GET['tool']) && in_array(trim($_GET['tool']), $useMapsOn) && $_GET['action'] != "select") {
    echo "<script async defer src=\"https://maps.googleapis.com/maps/api/js?key=" . MAPSAPIKEY . "&callback=initMap\"></script>";
}
?>
<script src="scripts/js/init.js"></script>
</body>
</html>
