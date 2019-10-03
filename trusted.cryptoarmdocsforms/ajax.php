<?php

use Trusted\CryptoARM\Docs;
use Bitrix\Main\Loader;

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/bx_root.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

Loader::includeModule("trusted.cryptoarmdocsforms");
Loader::includeModule(TR_CA_DOCS_CORE_MODULE);

define("NO_KEEP_STATISTIC", true);
define("BX_STATISTIC_BUFFER_USED", false);
define("NO_LANG_FILES", true);
define("NOT_CHECK_PERMISSIONS", true);

header('Content-Type: application/json; charset=' . LANG_CHARSET);

// AJAX Controller

$command = $_GET['command'];
if (isset($command)) {
    $params = $_POST;
    switch ($command) {
        case "removeForm":
            $res = Docs\Form::removeIBlockAndDocs($params);
            break;
        default:
            $res = array("success" => false, "message" => "Unknown command '" . $command . "'");
    }
}
echo json_encode($res);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");

