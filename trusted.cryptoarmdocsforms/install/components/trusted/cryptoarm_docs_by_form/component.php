<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Trusted\CryptoARM\Docs;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Config\Option;

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/trusted.cryptoarmdocsforms/install/index.php';

$trusted_cryptoarmdocsforms = new trusted_cryptoarmdocsforms();
if (!$trusted_cryptoarmdocsforms->coreModuleInstalled()) {
    echo ShowMessage(Loc::getMessage("TR_CA_DOCS_NO_CORE_MODULE"));
    return false;
}
switch ($trusted_cryptoarmdocsforms->CoreAndModuleAreCompatible()) {
    case "updateCore":
        echo ShowMessage(Loc::getMessage("TR_CA_DOCS_UPDATE_CORE_MODULE") . intval(ModuleManager::getVersion("trusted.cryptoarmdocsforms")) . Loc::getMessage("TR_CA_DOCS_UPDATE_CORE_MODULE2"));
        return false;
    case "updateModule":
        echo ShowMessage(Loc::getMessage("TR_CA_DOCS_UPDATE_FORMS_MODULE"));
        return false;
    default:
		break;
}

Loader::includeModule('trusted.cryptoarmdocsforms');
if (CModule::IncludeModuleEx(TR_CA_DOCS_CORE_MODULE) == MODULE_DEMO_EXPIRED) {
    echo GetMessage("TR_CA_DOCS_MODULE_DEMO_EXPIRED");
    return false;
}

if (!Docs\Utils::checkAuthorization()) {
    echo '<font color="#FF0000">ERROR not authorized</font>';
    return;
}

if (Docs\Utils::isAdmin($USER->GetID())) {
    $arResult["PERMISSION_REMOVE"] = true;
} else {
    $arResult["PERMISSION_REMOVE"] = $arParams["PERMISSION_REMOVE"] === "Y" ? true : false;
}

$arResult["FORMS"] = Docs\Form::getIBlockElements("ID", "DESC", ["CREATED_BY" => $USER->GetID()]);

$this->IncludeComponentTemplate();
