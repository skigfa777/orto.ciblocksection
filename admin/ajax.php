<?php

header('Content-Type: application/json; charset=utf-8');

define("ADMIN_MODULE_NAME", "orto.ciblocksection");
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");
CModule::IncludeModule("iblock");
CModule::IncludeModule("orto.ciblocksection");

$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

if (!filter_input(INPUT_SERVER, 'HTTP_X_REQUESTED_WITH') && !$id) {
    die();
}

$section = new Orto\CIBlockSection();
$section->IBLOCK_ID = 17;
$section->SECTION_ID = $id;

$newElementId = $section->copy();

$result["message"] = "";

if (!$newElementId) {
    $result["message"] = $section->LAST_ERROR;
} else {
    $result["message"] = $section->LAST_MESSAGE;
}

echo json_encode($result);
