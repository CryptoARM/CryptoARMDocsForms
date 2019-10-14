<?php

use Trusted\CryptoARM\Docs;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Main\Application;
use Bitrix\Main\ModuleManager;

require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php";
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/trusted.cryptoarmdocsforms/install/index.php';

$app = Application::getInstance();
$context = $app->getContext();
$docRoot = $context->getServer()->getDocumentRoot();

if (!trusted_cryptoarmdocsforms::coreModuleInstalled()) {
    require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");
    echo CAdminMessage::ShowMessage(Loc::getMessage("TR_CA_DOCS_NO_CORE_MODULE"));
    return false;
}
switch (trusted_cryptoarmdocsforms::CoreAndModuleAreCompatible()) {
    case "updateCore":
        require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");
        echo CAdminMessage::ShowMessage(getMessage("TR_CA_DOCS_UPDATE_CORE_MODULE") . intval(ModuleManager::getVersion("trusted.cryptoarmdocsforms")) . Loc::getMessage("TR_CA_DOCS_UPDATE_CORE_MODULE2"));
        return false;
        break;
    case "updateModule":
        require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");
        echo CAdminMessage::ShowMessage(Loc::getMessage("TR_CA_DOCS_UPDATE_FORMS_MODULE"));
        return false;
        break;
    default: break;
}

Loader::includeModule("trusted.cryptoarmdocsforms");
if (CModule::IncludeModuleEx(TR_CA_DOCS_CORE_MODULE) == MODULE_DEMO_EXPIRED) {
    echo GetMessage("TR_CA_DOCS_MODULE_DEMO_EXPIRED");
    return false;
};
Loader::includeModule(TR_CA_DOCS_CORE_MODULE);

Loc::loadMessages($docRoot . "/bitrix/modules/" . TR_CA_DOCS_CORE_MODULE . "/admin/trusted_cryptoarm_docs.php");

// current user rights for the module
$POST_RIGHT = $APPLICATION->GetGroupRight(TR_CA_DOCS_CORE_MODULE);

$sTableID = "Form_ID";
$oSort = new CAdminSorting($sTableID, 'SORT', 'asc');
// main list object
$lAdmin = new CAdminList($sTableID, $oSort);

$reloadTableJs = $sTableID . '.GetAdminList("")';

function CheckFilter() {
    global $FilterArr, $lAdmin;
    foreach ($FilterArr as $f)
        global $$f;
    // return false on errors
    return count($lAdmin->arFilterErrors) == 0;
}

$FilterArr = array(
    "find_iblock_elem_id",
    "find_iblock_name",
    "find_user_id",
    "find_date_create",
);

$lAdmin->InitFilter($FilterArr);

if (CheckFilter()) {
    $arFilter = array(
        "ID" => $find_iblock_elem_id,
        "IBLOCK_ID" => $find_iblock_name,
        "CREATED_BY" => $find_user_id,
        "DATA_CREATE" => $find_date_create,
    );
}

if (($arID = $lAdmin->GroupAction()) && $POST_RIGHT == "W") {

    // selected = checkbox "for all"
    if ($_REQUEST['action_target'] == 'selected') {
        $forms = Docs\Form::getIBlockElements($by, $order, $arFilter);
        $ids = array();
        foreach ($forms as $ID) {
            $ids[] = $ID["ID"];
        }
    } else {
        foreach ($arID as $ID) {
            $ids[] = IntVal($ID);
        }
    }

    switch ($_REQUEST['action']) {
        case "remove":
            echo '<script>';
            echo 'window.parent.trustedCA.removeForm(' . json_encode($ids) . ', () => { window.parent.' . $reloadTableJs . ' })';
            echo '</script>';
            break;
    }
}

$forms = Docs\Form::getIBlockElements($by, $order, $arFilter);

// convert list to the CAdminResult class
$rsData = new CAdminResult($forms, $sTableID);

// page-by-page navigation
$rsData->NavStart();

// send page selector to the main object $lAdmin
$lAdmin->NavText("<p>" . $rsData->GetNavPrint(Loc::getMessage("TR_CA_DOCS_NAV_TEXT_BY_FORM")) . "</p>");

$lAdmin->AddHeaders(
    array(
        array(
            "id" => "IBLOCK_ELEMENT_ID",
            "content" => Loc::getMessage("TR_CA_DOCS_COL_IBLOCK_ELEMENT_ID"),
            "sort" => "ID",
            "default" => true,
        ),
        array(
            "id" => "IBLOCK_NAME",
            "content" => Loc::getMessage("TR_CA_DOCS_COL_IBLOCK_NAME"),
            "sort" => "IBLOCK_NAME",
            "default" => true,
        ),
        array(
            "id" => "USER_NAME",
            "content" => Loc::getMessage("TR_CA_DOCS_FILTER_USER_NAME"),
            "sort" => "USER_NAME",
            "default" => true,
        ),
        array(
            "id" => "DATE_CREATE",
            "content" => Loc::getMessage("TR_CA_DOCS_COL_DATE_CREATE"),
            "sort" => "DATE_CREATE",
            "default" => true,
        ),
        array(
            "id" => "DOCS",
            "content" => Loc::getMessage("TR_CA_DOCS_COL_DOCS"),
            "default" => true,
        ),
    )
);

while ($arRes = $rsData->NavNext(true)) {

    $row = &$lAdmin->AddRow($arRes["ID"], $arRes);

    $iBlockElementId = $arRes["ID"];

    $iBlockElementIdViewField = "[<a href='";
    $iBlockElementIdViewField .= "/bitrix/admin/iblock_element_edit.php?IBLOCK_ID=" . $arRes["IBLOCK_ID"] . "&type=tr_ca_docs_form&ID=" . $iBlockElementId . "'";
    $iBlockElementIdViewField .= "title='" . Loc::getMessage("TR_CA_DOCS_IBLOCK_ELEMENT") . "'>";
    $iBlockElementIdViewField .= $iBlockElementId;
    $iBlockElementIdViewField .= "</a>]";

    $iBlockNameViewField = $arRes["IBLOCK_NAME"];

    $userId = $arRes["CREATED_BY"];

    $dateCreateViewField = $arRes["DATE_CREATE"];

    $userNameViewField = Docs\Utils::getUserName($userId) . "<br />";
    $userNameViewField .= "[<a href='/bitrix/admin/user_edit.php?ID=" . $userId . "'";
    $userNameViewField .= "title='" . Loc::getMessage("TR_CA_DOCS_USER_PROFILE") . "'>";
    $userNameViewField .= Docs\Utils::getUserLogin($userId);
    $userNameViewField .= "</a>]<br />";
    $userNameViewField .= "<small><a href='mailto:";
    $userNameViewField .= Docs\Utils::getUserEmail($userId);
    $userNameViewField .= "' title='" . Loc::getMessage("TR_CA_DOCS_MAILTO_USER") . "'>";
    $userNameViewField .= Docs\Utils::getUserEmail($userId);
    $userNameViewField .= "</a></small>";

    $docs = Docs\Database::getDocumentsByPropertyTypeAndValue("FORM", $iBlockElementId);
    $docList = $docs->getList();

    $docViewField = "<table class='trca-doc-table'>";
    foreach ($docList as $doc) {
        $docId = $doc->getId();
        $docName = $doc->getName();
        $docType = Docs\Utils::getTypeString($doc);
        if ($doc->getStatus() === DOC_STATUS_NONE) {
            $docStatus = "";
        } else {
            $docStatus = Loc::getMessage("TR_CA_DOCS_STATUS") . Docs\Utils::getStatusString($doc);
        }
        $docViewField .= "<tr>";
        $docViewField .= "<td>";
        $docViewField .= "<input class='trca-verify-button' type='button'";
        if ($doc->getType() === DOC_TYPE_FILE) {
            $docViewField .= "disabled ";
        }
        $docViewField .= "value='i' onclick='trustedCA.verify([";
        $docViewField .= $docId . "])' title='" . Loc::getMessage("TR_CA_DOCS_VERIFY_DOC") . "'/>";
        $docViewField .= "<a class='trca-tn-document' title='" . Loc::getMessage("TR_CA_DOCS_DOWNLOAD_DOC") . " ";
        $docViewField .= Loc::getMessage("TR_CA_DOCS_OPEN_QUOTE") . $doc->getName() . Loc::getMessage("TR_CA_DOCS_CLOSE_QOUTE");
        $docViewField .= "' onclick='trustedCA.download([";
        $docViewField .= $docId . "], true)'>" . $docName . "</a>";
        $docViewField .= "</td>";
        $docViewField .= "<td>" . $docType . "<br />";
        $docViewField .= $docStatus . "</td>";
        $docViewField .= "</tr>";
    }
    $docViewField .= "</table>";


    $row->AddViewField("IBLOCK_ELEMENT_ID", $iBlockElementIdViewField);
    $row->AddViewField("IBLOCK_NAME", $iBlockNameViewField);
    $row->AddViewField("USER_NAME", $userNameViewField);
    $row->AddViewField("DATE_CREATE", $dateCreateViewField);
    $row->AddViewField("DOCS", "<small>" . $docViewField . "</small>");

    // context menu
    $arActions = array();

    $arActions[] = array(
        "ICON" => "delete",
        "DEFAULT" => false,
        "TEXT" => Loc::getMessage("TR_CA_DOCS_ACT_REMOVE"),
        "ACTION" => $lAdmin->ActionDoGroup($iBlockElementId, "remove"),
    );

    // apply context menu to the row
    $row->AddActions($arActions);
}

$lAdmin->AddFooter(
    array(
        array(
            "title" => Loc::getMessage("MAIN_ADMIN_LIST_SELECTED"),
            "value" => $rsData->SelectedRowsCount()
        ),
        array(
            "counter" => true,
            "title" => Loc::getMessage("MAIN_ADMIN_LIST_CHECKED"),
            "value" => "0"
        ),
    )
);

$lAdmin->AddGroupActionTable(
    array(
        "remove" => Loc::getMessage("TR_CA_DOCS_ACT_REMOVE"),
    )
);


// alternative output - ajax or excel
$lAdmin->CheckListMode();

$APPLICATION->SetTitle(Loc::getMessage("TR_CA_DOCS_TITLE_BY_FORM"));

// separates preparing of data and output
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

$oFilter = new CAdminFilter(
    $sTableID . "_filter",
    array(
        Loc::getMessage("TR_CA_DOCS_FILTER_IBLOCK_ELEM_ID"),
        Loc::getMessage("TR_CA_DOCS_FILTER_IBLOCK_NAME"),
        Loc::getMessage("TR_CA_DOCS_FILTER_USER_ID"),
    )
);
$reloadDocJS = $sTableID . ".GetAdminList('')";
?>

<a id="trca-reload-doc" onclick="<?= $reloadDocJS ?>"></a>

<?php
if (!Docs\Utils::isSecure()) {
    echo BeginNote(), Loc::getMessage("TM_DOCS_MODULE_HTTP_WARNING"), EndNote();
}
?>

<form name="find_form" method="get" action="<?= $APPLICATION->GetCurPage() ?>">
    <?php $oFilter->Begin(); ?>

    <tr>
        <td>
            <?= Loc::getMessage("TR_CA_DOCS_FILTER_IBLOCK_ELEM_ID") . ":" ?>
        </td>
        <td>
            <input type="text" name="find_iblock_elem_id" size="47"
                   value="<?= htmlspecialchars($find_iblock_elem_id) ?>">
        </td>
    </tr>

    <tr>
        <td>
            <?= Loc::getMessage("TR_CA_DOCS_FILTER_IBLOCK_NAME") . ":" ?>
        </td>
        <td>
            <?php
            $iBlocksId = Docs\Form::getIBlocks();

            $arr = [
                "reference_id" => [""
                ],
                "reference" => [
                    ""
                ]
            ];

            foreach ($iBlocksId as $id => $name) {
                $arr["reference_id"][] = $id;
                $arr["reference"][] = $name;
            }
            echo SelectBoxFromArray("find_iblock_name", $arr, $find_iblock_name, Loc::getMessage("POST_ALL"), "");
            ?>
        </td>
    </tr>

    <tr>
        <td>
            <?= Loc::getMessage("TR_CA_DOCS_FILTER_USER_ID") . ":" ?>
        </td>
        <td>
            <input type="text" name="find_user_id" size="47" value="<?= htmlspecialchars($find_user_id) ?>">
        </td>
    </tr>

    <? /*
    <tr>
        <td>
            <?= Loc::getMessage("TR_CA_DOCS_FILTER_USER_NAME") . ":" ?>
        </td>
        <td>
            <input type="text" name="find_user_name" size="47" value="<?= htmlspecialchars($find_user_name) ?>">
        </td>
    </tr>

    <tr>
        <td>
            <?= Loc::getMessage("TR_CA_DOCS_FILTER_USER_EMAIL") . ":" ?>
        </td>
        <td>
            <input type="text" name="find_user_email" size="47" value="<?= htmlspecialchars($find_user_email) ?>">
        </td>
    </tr>
    */ ?>

    <?php
    $oFilter->Buttons(array("table_id" => $sTableID, "url" => $APPLICATION->GetCurPage(), "form" => "find_form"));
    $oFilter->End();
    ?>

</form>

<script>
    let trustedCAUploadHandler = (data) => {
        <?= $reloadTableJs ?>
    };

    let trustedCACancelHandler = (data) => {
        <?= $reloadTableJs ?>
    };
</script>

<?
$lAdmin->DisplayList();
?>

<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php"); ?>

