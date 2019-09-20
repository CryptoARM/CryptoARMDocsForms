<?php
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;


# TODO: Use single cancel page for all terminations
# TODO: Add curl check during installation

if (!check_bitrix_sessid()) {
    return;
}

Loc::loadMessages(__FILE__);

$APPLICATION->SetTitle(Loc::getMessage("TR_CA_DOCS_INSTALL_TITLE"));

include __DIR__ . "/version.php";

?>

<form action="<?= $APPLICATION->GetCurPage() ?>">
    <input type="hidden" name="lang" value="<?= LANG ?>">
    <input type="hidden" name="id" value="trusted.cryptoarmdocsforms">
    <input type="hidden" name="install" value="N">
    <?php
        $res = trusted_cryptoarmdocsforms::CoreAndModuleAreCompatible();

        if (!CheckVersion(ModuleManager::getVersion("main"), "14.00.00")) {
            echo CAdminMessage::ShowMessage(Loc::getMessage("TR_CA_DOCS_NO_D7"));
        }
        elseif (!IsModuleInstalled('trusted.cryptoarmdocs')){
            echo CAdminMessage::ShowMessage(Loc::getMessage("TR_CA_DOCS_NO_CORE_MODULE"));
        }
        elseif ($res === "updateCore") {
            echo CAdminMessage::ShowMessage(Loc::getMessage("TR_CA_DOCS_UPDATE_CORE_MODULE") . intval($arModuleVersion["VERSION"]) . Loc::getMessage("TR_CA_DOCS_UPDATE_CORE_MODULE2"));
        }
        elseif ($res === "updateModule") {
            echo CAdminMessage::ShowMessage(Loc::getMessage("TR_CA_DOCS_UPDATE_MODULE"));
        }
        else {
            echo CAdminMessage::ShowMessage(Loc::getMessage("TR_CA_DOCS_CANCELLED"));
        }
    ?>
    <input type="submit" name="choice" value="<?= Loc::getMessage("MOD_BACK") ?>">
</form>

