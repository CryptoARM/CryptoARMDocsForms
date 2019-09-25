<?php

global $APPLICATION;

require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/trusted.cryptoarmdocsforms/config.php";

foreach (glob(TR_CA_DOCS_FORMS_MODULE_DIR_CLASSES_GENERAL . "/*.php") as $filename) {
    require_once $filename;
}

foreach (glob(TR_CA_DOCS_FORMS_MODULE_DIR_CLASSES . "/*.php") as $filename) {
    require_once $filename;
}

// End tag should be here because it's required by the bitrix marketplace demo mode
?>
