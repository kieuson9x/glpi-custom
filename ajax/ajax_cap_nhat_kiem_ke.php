<?php

include("../inc/includes.php");

Session::checkCentralAccess();
global $DB;

$computer = new Computer();
$infocom = new Infocom();

$result = true;
$message = "";

if (!isset($_POST['locations_id']) || $_POST['locations_id'] == 0) {
    $message = 'Vị trí chưa được chọn! Hãy quay lại và update vị trí!';
    $result = false;
}

if (!isset($_POST['callback']) || !isset($_POST['computer_id'])) {
    $result = false;
}

if ($result) {
    // Todo_S: Tim kiem infoCom tuong ung voi computer_id
    if ($infocom->getFromDBforDevice($computer->getType(), $_POST['computer_id'])) {
        // Todo_S: check co quyen update cai infoCom nay ko
        if ($infocom->canUpdateItem()) {
            // Todo_S: Update inventory_date = ngay hien tai cho field Ngay kiem ke vat ly cuoi cung
            $result = $DB->update(
                $infocom->getTable(),
                [
                    'inventory_date' => date("Y-m-d")
                ],
                [
                    'id' => $infocom->fields['id']
                ]
            );
        }
    }

    $payload = [
        'computer_id'            => $_POST['computer_id'],
        'inventory_date'         => date("Y-m-d H:i"),
        'users_id'            => Session::getLoginUserID(),
        'locations_id'            => $_POST['locations_id'],
        'prev_locations_id'      => $_POST['current_locations_id'],
        'note'            => isset($_POST['note']) ? htmlspecialchars($_POST['note']) : '',
    ];

    $DB->insertOrDie("glpi_kiemke_items", $payload);
}

echo $result ? json_encode(['success' => true]) : json_encode(['success' => false, 'message' => $message]);
