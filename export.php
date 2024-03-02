<?php
require_once "config/config.php";
/************************************************************
export.php

This file will receive a request to generate a file to be
exported and contain specific information

Parameters:
tool - from GET

Return:
a CSV export direct to download

 ************************************************************/

if (isset($_GET['tool']) && $_GET['tool'] != "") { // build a CSV only if we have enough info to start
    $tool = $_GET['tool'];
    $fileName = strtoupper($tool) . "_" . date("Y-m-d") . ".csv";

    // need to gather up the data for the requested tool so that it can be exported with correct column headers, etc.
    $file = fopen("data/" . strtolower($tool) . "/data.json", "r");
    $recs = array();
    while (!feof($file)) {
        $line = fgets($file);
        if ($line != "") {
            $recs[] = str_replace(array("\n"), "", $line);
        }
    }
    fclose($file);

    // set the correct headers so that the file will automatically try and download
    header("Content-Type: text/csv; charset=utf-8");
    header("Content-Disposition: attachment; filename=" . $fileName);
    $export = fopen('php://output', 'w');

    // loop through the data and add it to the CSV file that we are going to export
    $cnt = 0;
    foreach ($recs as $r) {
        $data = json_decode($db->DB_READFILE("data/" . strtolower($tool) . "/recs/" . $r . ".json"), true);

        // add multiple value to data
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                $str = '';
                $str = implode(", ", $v);

                $data[$k] = $str;
            }
        }

        if ($cnt == 0) {
            $params = array();
            foreach (array_keys($data) as $j => $t) {
                $params[] = str_replace(strtolower($tool) . '_', '', $t);
            }
            // fputcsv($export,array_keys($data));
            fputcsv($export, $params);
        }
        $rec = [];
        foreach ($data as $d) {

            $rec[] = $d;
        }
        fputcsv($export, $rec);
        $cnt++;
    }
}
