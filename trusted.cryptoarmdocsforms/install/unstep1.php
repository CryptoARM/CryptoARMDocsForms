<?php

use Bitrix\Main\Localization\Loc;

if (!check_bitrix_sessid()) {
    return;
}

Loc::loadMessages(__FILE__);

$APPLICATION->SetTitle(Loc::getMessage("TR_CA_DOCS_UNINSTALL_TITLE"));
?>

<form action="<?= $APPLICATION->GetCurPage() ?>">
    <?= bitrix_sessid_post() ?>
    <input type="hidden" name="lang" value="<?= LANG ?>">
    <input type="hidden" name="id" value="trusted.cryptoarmdocsforms">
    <input type="hidden" name="uninstall" value="Y">
    <input type="hidden" name="step" value="2">
    <? echo CAdminMessage::ShowMessage(Loc::getMessage("MOD_UNINST_WARN")) ?>

    <div style="border-top: 1px solid;
                border-bottom: 1px solid;
                border-color: #BDCADB;
                margin: 16px 0;
                display: inline-block;
                padding: 15px 30px 15px 18px;">
            <p>
                <input type="checkbox" name="deleteiblocks" id="deleteiblocks" value="Y">
                <label for="deleteiblocks"><? echo Loc::getMessage("TR_CA_DOCS_UNINST_DELETE_IBLOCKS") ?></label>
            </p>
            <p><? echo nl2br(Loc::getMessage("TR_CA_DOCS_UNINST_SAVE_PROMPT2")) ?></p>
    </div>
    <br>
    <input type="submit" name="uninst" value="<? echo Loc::getMessage("MOD_UNINST_DEL") ?>">
</form>
