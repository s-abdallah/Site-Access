<?php
require_once "config/config.php";
$form = "";

// we need to get a list of all of the tools and generate a dropdown list
$res = $t->TOOLS_LIST(false);
$tools = "<fieldset><select class=\"has-value\" name=\"query_tool\" id=\"query_tool\"><option value=\"\">----------</option>";
foreach ($res as $r) {
    $tools .= "<option " . (isset($_GET['tool']) && strtolower($r[0]) === $_GET['tool'] ? "selected=\"selected\"" : "") . " value=\"" . strtolower($r[0]) . "\">" . $r[1] . "</option>";
}
$tools .= "</select><label>Select a tool</label><p>The tool that you would like to retrieve data from</p></fieldset>";
$form .= $tools;

// need to gather up all of the fields that are available for the selected tool
if (isset($_GET['tool']) && $_GET['tool'] !== "") {

    // gather up all of the field keys to be shown as return options
    $data = json_decode($db->DB_READFILE("config/tools.json"), true);
    $keys = array_keys($data[strtoupper($_GET['tool'])]['fields']);
    $optionChecks = "";
    $optionDropdown = "";
    $cnt = 0;
    foreach ($keys as $k) {
        $optionChecks .= "<span><input type=\"checkbox\" name=\"chk_group_" . strtolower($k) . "\" id=\"chk_group_" . strtolower($k) . "\" value=\"" . strtolower($k) . "\" title=\"return " . ucwords(str_replace("_", " ", $k)) . "\" />" . ucwords(str_replace("_", " ", $k)) . "</span>";
        $optionDropdown .= "<option value=\"" . $k . "\">" . ucwords(str_replace("_", " ", $k)) . "</option>";
        $cnt++;
    }

    // select which fields you would like to have returned
    $form .= "<fieldset class=\"has-value\"><div class=\"checkboxgroup\"><span><input type=\"checkbox\" name=\"chk_group_everything\" id=\"chk_group_everything\" value=\"*\" checked=\"checked\" title=\"return Everything\" />Everything</span>" . $optionChecks . "</div><label>* What do you want returned?</label><p>When choosing everything option above, other options are disabled</p></fieldset>";

    // select the first field that the results should be ordered by
    $form .= "<fieldset><select class=\"has-value\" name=\"query_orderbyprimary\" id=\"query_orderbyprimary\"><option value=\"\">----------</option>" . $optionDropdown . "</select><label>* Primary Order By?</label><p>The field in the data that you would like to order by first</p></fieldset>";

    // select the direction that the primary order by should use
    $form .= "<fieldset><select class=\"has-value\" name=\"query_dirprimary\" id=\"query_dirprimary\"><option value=\"\">----------</option><option value=\"DESC\">Descending</option><option value=\"ASC\">Ascending</option></select><label>* Primary Order direction?</label><p>Direction for results to appear in: ASCending or DESCending</p></fieldset>";

    // select the second field that the results should be ordered by
    $form .= "<fieldset><select class=\"has-value\" name=\"query_orderbysecondary\" id=\"query_orderbysecondary\"><option value=\"\">----------</option>" . $optionDropdown . "</select><label>Secondary Order By?</label><p>The field in the data that you would like to order by second (optional)</p></fieldset>";

    // select the direction that the secondary order by should use
    $form .= "<fieldset><select class=\"has-value\" name=\"query_dirsecondary\" id=\"query_dirsecondary\"><option value=\"\">----------</option><option value=\"DESC\">Descending</option><option value=\"ASC\">Ascending</option></select><label>Secondary Order direction?</label><p>Direction for results to appear in: ASCending or DESCending (optional)</p></fieldset>";

    // does this tool support media, and should we include related media in the query?
    if (isset($data[strtoupper($_GET['tool'])]['media']) && $data[strtoupper($_GET['tool'])]['media'] == true) {
        $form .= "<fieldset><select class=\"has-value\" name=\"query_media\" id=\"query_media\"><option value=\"false\">No</option><option value=\"true\">Yes</option></select><label>Include Media?</label><p>Whether or not we should include related media items, YES can slow the fetching of results in some cases</p></fieldset>";
    }

    // are we looking to perform a certain search within the data to limit what is returned?
    $form .= "<fieldset><input type=\"text\" name=\"query_search\" id=\"query_search\" /><label>Search For</label><p>A where statement to return only matched results</p></fieldset>";

    // are we looking to limit the result count that we get back for any reason?
    $form .= "<fieldset><input type=\"text\" name=\"query_limit\" id=\"query_limit\" /><label>Limit Results</label><p>Limits on the results to be returned: start,count</p></fieldset>";

    // this is the final block that will show the custom query code to be used
    $form .= "<fieldset><textarea name=\"query_code\" id=\"query_code\" class=\"has-value\" readonly=\"readonly\" /></textarea><label>Your Custom Query Code</label><p>Copy and paste the above code into your PHP project</p></fieldset>";
}

include "includes/pagestart.php";
?>
</head>
<body id="query">
  <?php include "includes/header.php";?>
  <div id="loaderpanel"><div class="loader">Loading...</div></div>
  <div id="contentarea">

    <section class="neo__query">
      <h1>Query Builder</h1>
      <p class="info">The following tool allows you to select types of data, fields, and other options to build a custom SQL query to be used in your code.  It will then retrieve the requested information from the system and return it as an array to be used in any way you require.</p>
      <form name="query" id="query" class="neo__forms">
        <?=$form?>
      </form>
    </section>

  </div>
<?php
include "includes/footer.php";
include "includes/pageend.php";
?>
