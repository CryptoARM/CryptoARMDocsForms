<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Trusted\CryptoARM\Docs;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Application;

//checks the name of currently installed core from highest possible version to lowest
$coreIds = [
    'trusted.cryptoarmdocscrp',
    'trusted.cryptoarmdocsbusiness',
    'trusted.cryptoarmdocsstart',
];
foreach ($coreIds as $coreId) {
    $corePathDir = $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $coreId . "/";
    if (file_exists($corePathDir)) {
        $module_id = $coreId;
        break;
    }
}

$this->addExternalJS("https://cdn.jsdelivr.net/npm/vue/dist/vue.js");
CJSCore::RegisterExt(
    "components",
    [
        "js" => "/bitrix/js/" . $module_id . "/components.js",
    ]
);
CUtil::InitJSCore(['components']);

$compTitle = Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_FORM_TITLE");

?>
<form id="crypto-arm-document__by-form" method="POST">
    <div id="cryptoarm_docs_by_form">
        <trca-docs>
            <header-title title="<?= $compTitle ?>">
            </header-title>
            <docs-content>
                <?
                if (is_array($arResult["FORMS"])) {
                    foreach ($arResult["FORMS"] as $form) {
                        $formId = $form["ID"];
                        $docs = Docs\Database::getDocumentsByPropertyTypeAndValue("FORM", $formId);
                        $docList = $docs->getList();
                        $docIds = [];
                        foreach ($docList as $doc) {
                            $docsType = $doc->getType();
                            $docsStatus = $doc->getStatus();
                            $docIds[] = $doc->getId();
                        }

                        $docIds = json_encode($docIds);
                        $mainDocId = $form["NAME"];
                        $mainDoc = null;
                        if (is_numeric($mainDocId)) {
                            $mainDoc = Docs\Database::getDocumentById($mainDocId);
                        }

                        $zipName = Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_FORM_ZIP_FILE_NAME") . $form["DATE_CREATE"];

                        if ($docType == DOC_TYPE_SIGNED_FILE) {
                            if ($docStatus == DOC_STATUS_BLOCKED) {
                                $icon = "lock";
                                $iconCss = "color: red";
                                $status = Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_FORM_STATUS_BLOCKED");
                            } else {
                                $icon = "check_circles";
                                $iconCss = "color: rgb(33, 150, 243)";
                                $status = Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_FORM_STATUS_SIGN");
                            }
                        } else {
                            switch ($docStatus) {
                                case DOC_STATUS_NONE:
                                    $icon = "insert_drive_file";
                                    $iconCss = "color: green";
                                    $status = Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_FORM_STATUS_DRAFT");
                                    break;
                                case DOC_STATUS_BLOCKED:
                                    $icon = "lock";
                                    $iconCss = "color: red";
                                    $status = Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_FORM_STATUS_DRAFT");
                                    break;
                                case DOC_STATUS_CANCELED:
                                    $icon = "insert_drive_file";
                                    $iconCss = "color: red";
                                    $status = Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_FORM_STATUS_DRAFT");
                                    break;
                                case DOC_STATUS_ERROR:
                                    $icon = "error";
                                    $iconCss = "color: red";
                                    $status = Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_FORM_STATUS_DRAFT");
                                    break;
                            }
                        }
                        ?>
                        <docs-items>
                            <doc-name color="<?= $iconCss ?>"
                                      name ="<?= $form["IBLOCK_NAME"] ?>">
                            </doc-name>
                            <doc-info info= "<?= $form["DATE_CREATE"] ?>">
                            </doc-info>
                            <doc-info info="<?= $mainDocId ?>"
                                  title="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_ID"); ?>">
                            </doc-info>

                            <doc-buttons component="form">
                                    <?
                                    if ($mainDoc) {
                                        ?>
                                        <a href="<?= Docs\Form::getFirstDocument((int)$mainDocId) ?>"
                                            target="_blank"
                                            style="border:none; outline: none;">
                                            <doc-button icon="pageview"
                                                        title="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_FORM_VIEW"); ?>">
                                            </doc-button>
                                        </a>
                                        <?
                                    }
                                    if (!empty(json_decode($docIds))) {
                                        ?>
                                        <doc-button-arr icon="file_download"
                                                        :id="<?= $docIds ?>"
                                                        zipname="<?= $zipName ?>"
                                                        title="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_FORM_DOWNLOAD"); ?>"
                                                        @button-click="download">
                                        </doc-button-arr>
                                        <?
                                    }
                                    if ($arResult["PERMISSION_REMOVE"]) {
                                        ?>
                                        <doc-button icon="delete"
                                                    title="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_FORM_DELETE"); ?>"
                                                    :id="<?= $formId ?>"
                                                    @button-click="removeForm">
                                        </doc-button>
                                        <?
                                    }
                                    ?>
                            </doc-buttons>
                        </docs-items>
                        <?
                    }
                }
                ?>
            </docs-content>
        </trca-docs>
    </div>
</form>

<script>
    new Vue({
        el: '#cryptoarm_docs_by_form',
        methods: {
            download: function(id, zipname) {
                trustedCA.download(id, zipname);
            },
            removeForm: function(id) {
                trustedCA.removeForm(id, trustedCA.reloadDoc)
            }
        }
    });
</script>