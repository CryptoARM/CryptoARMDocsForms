<?php
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Application;
use Bitrix\Main\ModuleManager;
// use Bitrix\Main\EventManager;
// use Bitrix\Main\Loader;
use Trusted\CryptoARM\Docs;

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/trusted.cryptoarmdocsforms/include.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/trusted.cryptoarmdocsforms/classes/IBlock.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/trusted.cryptoarmdocs/classes/Database.php';

Loc::loadMessages(__FILE__);



Class trusted_cryptoarmdocsforms extends CModule
{
    // Required by the marketplace standards
    const MODULE_ID = "trusted.cryptoarmdocsforms";
    var $MODULE_ID = "trusted.cryptoarmdocsforms";
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $PARTNER_NAME;
    var $PARTNER_URI;

    function trusted_cryptoarmdocsforms()
    {
        self::__construct();
    }

    function __construct()
    {
        $arModuleVersion = array();
        include __DIR__ . "/version.php";
        $this->MODULE_NAME = Loc::getMessage("TR_CA_DOCS_MODULE_NAME3");
        $this->MODULE_DESCRIPTION = Loc::getMessage("TR_CA_DOCS_MODULE_DESCRIPTION3");
        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->PARTNER_NAME = GetMessage("TR_CA_DOCS_PARTNER_NAME");
        $this->PARTNER_URI = GetMessage("TR_CA_DOCS_PARTNER_URI");
    }

    function DoInstall()
    {
        global $DOCUMENT_ROOT, $APPLICATION;

        include __DIR__ . "/version.php";

        if (!self::d7Support() || !self::coreModuleInstalled()
        || !self::ModuleIsRelevant(ModuleManager::getVersion("trusted.cryptoarmdocs"), $arModuleVersion["VERSION"])
        || !self::ModuleIsRelevant($arModuleVersion["VERSION"], ModuleManager::getVersion("trusted.cryptoarmdocs"))) {
            $APPLICATION->IncludeAdminFile(
                Loc::getMessage("MOD_INSTALL_TITLE"),
                 $DOCUMENT_ROOT . "/bitrix/modules/" . self::MODULE_ID . "/install/step_cancel.php"
            );
        }
        self::InstallFiles();
         // $this->CreateDocsDir();
        self::InstallModuleOptions();
          // $this->InstallDB();
          self::InstallIb();
          self::InstallMailEvents();
        ModuleManager::registerModule(self::MODULE_ID);
    }

    function d7Support()
    {
        return CheckVersion(ModuleManager::getVersion("main"), "14.00.00");
    }

    function coreModuleInstalled()
    {
        return IsModuleInstalled("trusted.cryptoarmdocs");
    }

    // Used to compare versions of core and module
    function ModuleIsRelevant($module1, $module2)
    {
        $module1 = explode(".", $module1);
        $module2 = explode(".", $module2);
        if (intval($module2[0])>intval($module1[0])) return false;
            elseif (intval($module2[0])<=intval($module1[0])) return true;
    }

    function InstallFiles()
    {
        CopyDirFiles(
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . self::MODULE_ID . "/install/components/",
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/",
            true, true
        );
        CopyDirFiles(
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . self::MODULE_ID . "/install/admin/",
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin/",
            true, false
        );
        // CopyDirFiles(
        //     $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/js/",
        //     $_SERVER["DOCUMENT_ROOT"] . "/bitrix/js/",
        //     true, true
        // );
        // CopyDirFiles(
        //     $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/themes/",
        //     $_SERVER["DOCUMENT_ROOT"] . "/bitrix/themes/",
        //     true, true
        // );
        return true;
    }

    // function CreateDocsDir()
    // {
    //     $docsDir = $_SERVER["DOCUMENT_ROOT"] . "/docs/";
    //     if (!file_exists($docsDir)) {
    //         mkdir($docsDir);
    //     }
    // }

    function InstallModuleOptions()
    {
        $options = array(
            'DOCUMENTS_DIR' => '/docs/',
            // 'MAIL_EVENT_ID' => 'TR_CA_DOCS_MAIL_BY_ORDER',
            // 'MAIL_EVENT_ID_TO' => 'TR_CA_DOCS_MAIL_TO',
            // 'MAIL_EVENT_ID_SHARE' => 'TR_CA_DOCS_MAIL_SHARE',
            'MAIL_EVENT_ID_FORM' => 'TR_CA_DOCS_MAIL_FORM',
            'MAIL_EVENT_ID_FORM_TO_ADMIN' => 'TR_CA_DOCS_MAIL_FORM_TO_ADMIN',
        );
        foreach ($options as $name => $value) {
            if (!Option::get('trusted.cryptoarmdocs', $name, '')) {
                Option::set('trusted.cryptoarmdocs', $name, $value);
            }
        }
    }

    // function InstallDB()
    // {
    //     global $DB;
    //     $sql = "CREATE TABLE IF NOT EXISTS `tr_ca_docs` (
    //                 `ID` int(11) NOT NULL AUTO_INCREMENT,
    //                 `NAME` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
    //                 `DESCRIPTION` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
    //                 `PATH` text COLLATE utf8_unicode_ci DEFAULT NULL,
    //                 `TYPE` tinyint(1) DEFAULT '0',
    //                 `STATUS` tinyint(1) DEFAULT '0',
    //                 `PARENT_ID` int(11) DEFAULT NULL,
    //                 `CHILD_ID` int(11) DEFAULT NULL,
    //                 `HASH` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
    //                 `SIGNATURES` text COLLATE utf8_unicode_ci,
    //                 `SIGNERS` text COLLATE utf8_unicode_ci,
    //                 `BLOCK_BY` int(11) DEFAULT NULL,
    //                 `BLOCK_TOKEN` varchar(36) DEFAULT NULL,
    //                 `BLOCK_TIME` datetime DEFAULT '1000-01-01 00:00:00',
    //                 `TIMESTAMP_X` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    //             PRIMARY KEY (`ID`),
    //             KEY `fk_tr_ca_docs_tr_ca_docs1_idx` (`PARENT_ID`)
    //         ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
    //     $DB->Query($sql);

    //     $sql = "CREATE TABLE IF NOT EXISTS `tr_ca_docs_property` (
    //                 `ID` int(11) NOT NULL AUTO_INCREMENT,
    //                 `DOCUMENT_ID` int(11) DEFAULT NULL,
    //                 `TYPE` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
    //                 `VALUE` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
    //             PRIMARY KEY (`ID`),
    //             KEY `fk_tr_ca_docs_property_tr_ca_docs_idx` (`DOCUMENT_ID`)
    //         ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
    //     $DB->Query($sql);
    // }

    function InstallIb() {
        Docs\IBlock::install();
    }

    function InstallMailEvents()
    {
        $obEventType = new CEventType;
        $events = array(
            // by order
            // array(
            //     "LID" => "ru",
            //     "EVENT_NAME" => "TR_CA_DOCS_MAIL_BY_ORDER",
            //     "NAME" => Loc::getMessage("TR_CA_DOCS_MAIL_EVENT_NAME"),
            //     "DESCRIPTION" => Loc::getMessage("TR_CA_DOCS_MAIL_EVENT_DESCRIPTION"),
            // ),

            // // to
            // array(
            //     "LID" => "ru",
            //     "EVENT_NAME" => "TR_CA_DOCS_MAIL_TO",
            //     "NAME" => Loc::getMessage("TR_CA_DOCS_MAIL_EVENT_TO_NAME"),
            //     "DESCRIPTION" => Loc::getMessage("TR_CA_DOCS_MAIL_EVENT_TO_DESCRIPTION"),
            // ),

            // // share
            // array(
            //     "LID" => "ru",
            //     "EVENT_NAME" => "TR_CA_DOCS_MAIL_SHARE",
            //     "NAME" => Loc::getMessage("TR_CA_DOCS_MAIL_EVENT_SHARE_NAME"),
            //     "DESCRIPTION" => Loc::getMessage("TR_CA_DOCS_MAIL_EVENT_SHARE_DESCRIPTION"),
            // ),

            // send completed form to user
            array(
                "LID" => "ru",
                "EVENT_NAME" => "TR_CA_DOCS_MAIL_FORM",
                "NAME" => Loc::getMessage("TR_CA_DOCS_MAIL_EVENT_FORM_NAME"),
                "DESCRIPTION" => Loc::getMessage("TR_CA_DOCS_MAIL_EVENT_FORM_DESCRIPTION"),
            ),

            // send completed form to admin
            array(
                "LID" => "ru",
                "EVENT_NAME" => "TR_CA_DOCS_MAIL_FORM_TO_ADMIN",
                "NAME" => Loc::getMessage("TR_CA_DOCS_MAIL_EVENT_FORM_TO_ADMIN_NAME"),
                "DESCRIPTION" => Loc::getMessage("TR_CA_DOCS_MAIL_EVENT_FORM_TO_ADMIN_DESCRIPTION"),
            ),
        );
        foreach ($events as $event) {
            $obEventType->add($event);
        }

        $obEventMessage = new CEventMessage;
        $sites = CSite::GetList($by = "sort", $order = "asc", array("ACTIVE" => "Y"));
        $siteIds = array();
        while ($site = $sites->Fetch()) {
            $siteIds[] = $site["ID"];
        }
        $templates = array(
            // by order
            // 'MAIL_TEMPLATE_ID' => array(
            //     "ACTIVE" => "Y",
            //     "EVENT_NAME" => "TR_CA_DOCS_MAIL_BY_ORDER",
            //     "LID" => $siteIds,
            //     "EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
            //     "EMAIL_TO" => "#EMAIL#",
            //     "SUBJECT" => Loc::getMessage("TR_CA_DOCS_MAIL_TEMPLATE_SUBJECT"),
            //     "BODY_TYPE" => "html",
            //     "MESSAGE" => Loc::getMessage("TR_CA_DOCS_MAIL_TEMPLATE_BODY"),
            // ),

            // // to
            // 'MAIL_TEMPLATE_ID_TO' => array(
            //     "ACTIVE" => "Y",
            //     "EVENT_NAME" => "TR_CA_DOCS_MAIL_TO",
            //     "LID" => $siteIds,
            //     "EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
            //     "EMAIL_TO" => "#EMAIL#",
            //     "SUBJECT" => Loc::getMessage("TR_CA_DOCS_MAIL_TEMPLATE_TO_SUBJECT"),
            //     "BODY_TYPE" => "html",
            //     "MESSAGE" => Loc::getMessage("TR_CA_DOCS_MAIL_TEMPLATE_TO_BODY"),
            // ),

            // // share
            // 'MAIL_TEMPLATE_ID_SHARE' => array(
            //     "ACTIVE" => "Y",
            //     "EVENT_NAME" => "TR_CA_DOCS_MAIL_SHARE",
            //     "LID" => $siteIds,
            //     "EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
            //     "EMAIL_TO" => "#EMAIL#",
            //     "SUBJECT" => Loc::getMessage("TR_CA_DOCS_MAIL_TEMPLATE_SHARE_SUBJECT"),
            //     "BODY_TYPE" => "html",
            //     "MESSAGE" => Loc::getMessage("TR_CA_DOCS_MAIL_TEMPLATE_SHARE_BODY"),
            // ),

            // send completed form to user
            'MAIL_TEMPLATE_ID_FORM' => array(
                "ACTIVE" => "Y",
                "EVENT_NAME" => "TR_CA_DOCS_MAIL_FORM",
                "LID" => $siteIds,
                "EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
                "EMAIL_TO" => "#EMAIL#",
                "SUBJECT" => Loc::getMessage("TR_CA_DOCS_MAIL_TEMPLATE_FORM_SUBJECT"),
                "BODY_TYPE" => "html",
                "MESSAGE" => Loc::getMessage("TR_CA_DOCS_MAIL_TEMPLATE_FORM_BODY"),
            ),

            // send completed form to admin
            'MAIL_TEMPLATE_ID_FORM_TO_ADMIN' => array(
                "ACTIVE" => "Y",
                "EVENT_NAME" => "TR_CA_DOCS_MAIL_FORM_TO_ADMIN",
                "LID" => $siteIds,
                "EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
                "EMAIL_TO" => "#EMAIL#",
                "SUBJECT" => Loc::getMessage("TR_CA_DOCS_MAIL_TEMPLATE_FORM_TO_ADMIN_SUBJECT"),
                "BODY_TYPE" => "html",
                "MESSAGE" => Loc::getMessage("TR_CA_DOCS_MAIL_TEMPLATE_FORM_TO_ADMIN_BODY"),
            ),
        );
        foreach ($templates as $templateName => $template) {
            $templateId = $obEventMessage->add($template);
            Option::set("trusted.cryptoarmdocs", $templateName, $templateId);
        }
    }

    function DoUninstall()
    {
        global $DOCUMENT_ROOT, $APPLICATION;

        $context = Application::getInstance()->getContext();
        $request = $context->getRequest();
        $step = (int)$request["step"];

        if ($step < 2) {
            $APPLICATION->IncludeAdminFile(
                Loc::getMessage("MOD_UNINSTALL_TITLE"),
                $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . 'trusted.cryptoarmdocsforms' . "/install/unstep1.php"
            );
        }
        // if ($step == 2) {

        $deleteiblocks = $request["deleteiblocks"];
        if ($deleteiblocks == "Y") {
            trusted_cryptoarmdocsforms::UnInstallIb();
        }

        self::UnInstallModuleOptions();
        //     $deletedata = $request["deletedata"];
        //     if ($deletedata == "Y") {
        //         $this->UnInstallDB();
        //         $this->UnInstallIb();
        //     }
        self::UnInstallMailEvents();

            // if (IsModuleInstalled('trusted.cryptoarmdocsbp')) {
            //     CModule::includeModule('trusted.cryptoarmdocsbp');
            //     trusted_cryptoarmdocsbp::DoUninstall();
            // }

        self::UnInstallFiles();
        ModuleManager::unRegisterModule(self::MODULE_ID);
            // $APPLICATION->IncludeAdminFile(
            //     Loc::getMessage("MOD_UNINSTALL_TITLE"),
            //     $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/unstep2.php"
            // );
        //}
    }

    function UnInstallFiles()
    {
        // DeleteDirFilesEx("/bitrix/components/trusted/cryptoarm_docs_by_user/");
        // DeleteDirFilesEx("/bitrix/components/trusted/cryptoarm_docs_by_order/");
        // if ($this->crmSupport()) {
        //     DeleteDirFilesEx("/bitrix/components/trusted/cryptoarm_docs_crm/");
        // }
        DeleteDirFilesEx("/bitrix/components/trusted/cryptoarm_docs_form/");
        DeleteDirFilesEx("/bitrix/components/trusted/cryptoarm_docs_by_form/");
        // DeleteDirFilesEx("/bitrix/components/trusted/cryptoarm_docs_upload/");
        // DeleteDirFilesEx("/bitrix/components/trusted/docs/");
        DeleteDirFiles(
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . self::MODULE_ID . "/install/admin/",
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin"
        );
        // DeleteDirFilesEx("/bitrix/js/" . $this->MODULE_ID);
        // DeleteDirFiles(
        //     $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/themes/.default/",
        //     $_SERVER["DOCUMENT_ROOT"] . "/bitrix/themes/.default/"
        // );
        // DeleteDirFilesEx("/bitrix/themes/.default/icons/" . $this->MODULE_ID);

        return true;
    }

    function UnInstallModuleOptions()
    {
        $options = array(
            // 'DOCUMENTS_DIR',
            // 'MAIL_EVENT_ID',
            // 'MAIL_TEMPLATE_ID',
            // 'MAIL_EVENT_ID_TO',
            // 'MAIL_TEMPLATE_ID_TO',
            // 'MAIL_EVENT_ID_SHARE',
            // 'MAIL_TEMPLATE_ID_SHARE',
            // 'MAIL_EVENT_ID_FORM',
            // 'MAIL_TEMPLATE_ID_FORM',
            'MAIL_EVENT_ID_FORM_TO_ADMIN',
            'MAIL_TEMPLATE_ID_FORM_TO_ADMIN',
        );
        foreach ($options as $option) {
            Option::delete(
                self::MODULE_ID,
                array('name' => $option)
            );
        }
    }

    // function UnInstallDB()
    // {
    //     global $DB;
    //     if (Loader::includeModule('bizproc')) {
    //         $docs = Docs\Database::getDocuments();
    //         foreach ($docs->getList() as $doc) {
    //             $doc->remove();
    //         }
    //     }
    //     $sql = "DROP TABLE IF EXISTS `tr_ca_docs`";
    //     $DB->Query($sql);
    //     $sql = "DROP TABLE IF EXISTS `tr_ca_docs_property`";
    //     $DB->Query($sql);
    // }

    function UnInstallIb() {
        Docs\IBlock::uninstall();
    }

    function UnInstallMailEvents()
    {
        $events = array(
            // 'TR_CA_DOCS_MAIL_BY_ORDER',
            // 'TR_CA_DOCS_MAIL_TO',
            // 'TR_CA_DOCS_MAIL_SHARE',
            'TR_CA_DOCS_MAIL_FORM',
            'TR_CA_DOCS_MAIL_FORM_TO_ADMIN',
        );
        foreach ($events as $event) {
            $eventMessages = CEventMessage::GetList(
                $by = 'id',
                $order = 'desc',
                array('TYPE' => $event)
            );
            $eventMessage = new CEventMessage;
            while ($template = $eventMessages->Fetch()) {
                $eventMessage->Delete((int)$template['ID']);
            }
            $eventType = new CEventType;
            $eventType->Delete($event);
        }
    }

    // function dropDocumentChain($id)
    // {
    //     global $DB;
    //     // Try to find parent doc
    //     $sql = 'SELECT `PARENT_ID` FROM `tr_ca_docs` WHERE `ID`=' . $id;
    //     $res = $DB->Query($sql)->Fetch();
    //     $parentId = $res["PARENT_ID"];

    //     $sql = 'DELETE FROM `tr_ca_docs`'
    //         . 'WHERE ID = ' . $id;
    //     $DB->Query($sql);
    //     $sql = 'DELETE FROM `tr_ca_docs_property`'
    //         . 'WHERE DOCUMENT_ID = ' . $id;
    //     $DB->Query($sql);

    //     if ($parentId) {
    //         $this->dropDocumentChain($parentId);
    //     }
    // }
}

