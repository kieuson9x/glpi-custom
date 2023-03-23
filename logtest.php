<?php
function writeToLogFile($data, $mode = 'a')
{
    // Open the log file in append mode
    $log_file = fopen(GLPI_PLUGIN_DOC_DIR . '/barcode/' . 'log.txt', $mode);

    // Convert the data to a string
    $data_str = var_export($data, true);

    // Write the data to the file
    fwrite($log_file, $data_str . "\n");

    // Close the file
    fclose($log_file);
}
