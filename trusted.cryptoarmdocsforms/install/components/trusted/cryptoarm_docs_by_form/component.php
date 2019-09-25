<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Trusted\CryptoARM\Docs;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;

if (!(IsModuleInstalled('trusted.cryptoarmdocs'))) {
    echo GetMessage("TR_CA_DOCS_MODULE_CORE_DOES_NOT_EXIST");
    return false;
}

if (CModule::IncludeModuleEx('trusted.cryptoarmdocs') == MODULE_DEMO_EXPIRED) {
    echo GetMessage("TR_CA_DOCS_MODULE_DEMO_EXPIRED");
    return false;
};

Loader::includeModule('trusted.cryptoarmdocsforms');

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
