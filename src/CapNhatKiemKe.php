<?php

include("../inc/includes.php");

Session::checkCentralAccess();
global $DB;

if (!isset($_POST['callback']) || !isset($_POST['computer_id']) || !isset($_POST['users_id']) || !isset($_POST['locations_id'])) {
    return header("Location: " . $_SERVER["HTTP_REFERER"]);
}

$callback = $_POST['callback'];

$payload = [
    'computer_id'            => $_POST['computer_id'],
    'inventory_date'         => date("Y-m-d H:i"),
    'users_id'            => $_POST['users_id'],
    'locations_id'            => $_POST['locations_id'],
    'note'            => isset($_POST['note']) ? htmlspecialchars($_POST['note']) : '',
];

$DB->insertOrDie("glpi_kiemke_items", $payload);

header('Location: ' . $callback);
exit();
