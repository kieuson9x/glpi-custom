<?php

include("../inc/includes.php");

Session::checkCentralAccess();
global $DB;

// Todo_S: Doc computer_id
$computerId = isset($_GET['computer_id']) ? $_GET['computer_id'] : 0;
// $target = isset($_GET['target']) ? $_GET['target'] : null;

$computer = new Computer();
$infocom = new Infocom();

$result = false;

// Todo_S: Tim kiem infoCom tuong ung voi computer_id
if ($infocom->getFromDBforDevice($computer->getType(), $computerId)) {
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

// Tra ve success hay failed
echo ($result ? "success" : "failed");
