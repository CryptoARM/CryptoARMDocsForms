<?php
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Application;
use Bitrix\Main\ModuleManager;
use Trusted\CryptoARM\Docs;

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/trusted.cryptoarmdocsforms/include.php';

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

        if (!self::d7Support() || !self::coreModuleInstalled() || self::CoreAndModuleAreCompatible() !== "ok" ) {
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
    function CoreAndModuleAreCompatible()
    {
        include __DIR__ . "/version.php";
        $coreVersion = explode(".", ModuleManager::getVersion("trusted.cryptoarmdocs"));
        $moduleVersion = explode(".", $arModuleVersion["VERSION"]);
        if (intval($moduleVersion[0])>intval($coreVersion[0])) {
            $res = "updateCore";
        } elseif (intval($moduleVersion[0])<intval($coreVersion[0])) {
            $res = "updateModule";
        } else $res = "ok";

        return $res;
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
        return true;
    }

    function InstallModuleOptions()
    {
        $options = array(
            'MAIL_EVENT_ID_FORM' => 'TR_CA_DOCS_MAIL_FORM',
            'MAIL_EVENT_ID_FORM_TO_ADMIN' => 'TR_CA_DOCS_MAIL_FORM_TO_ADMIN',
        );
        foreach ($options as $name => $value) {
            if (!Option::get('trusted.cryptoarmdocs', $name, '')) {
                Option::set('trusted.cryptoarmdocs', $name, $value);
            }
        }
    }

    function InstallIb() {
        Docs\IBlock::install();
    }

    function InstallMailEvents()
    {
        $obEventType = new CEventType;
        $events = array(
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
                $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . self::MODULE_ID . "/install/unstep1.php"
            );
        }

        $deleteiblocks = $request["deleteiblocks"];
        if ($deleteiblocks == "Y") {
            trusted_cryptoarmdocsforms::UnInstallIb();
        }

        self::UnInstallModuleOptions();
        self::UnInstallMailEvents();
        self::UnInstallFiles();
        ModuleManager::unRegisterModule(self::MODULE_ID);
    }

    function UnInstallFiles()
    {
        DeleteDirFilesEx("/bitrix/components/trusted/cryptoarm_docs_form/");
        DeleteDirFilesEx("/bitrix/components/trusted/cryptoarm_docs_by_form/");
        DeleteDirFiles(
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . self::MODULE_ID . "/install/admin/",
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin"
        );

        return true;
    }

    function UnInstallModuleOptions()
    {
        $options = [
            'MAIL_EVENT_ID_FORM',
            'MAIL_TEMPLATE_ID_FORM',
            'MAIL_EVENT_ID_FORM_TO_ADMIN',
            'MAIL_TEMPLATE_ID_FORM_TO_ADMIN',
        ];
        foreach ($options as $option) {
            Option::delete(
                self::MODULE_ID,
                array('name' => $option)
            );
        }
    }

    function UnInstallIb() {
        Docs\IBlock::uninstall();
    }

    function UnInstallMailEvents()
    {
        $events = array(
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
}

