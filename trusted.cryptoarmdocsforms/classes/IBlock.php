<?php

namespace Trusted\CryptoARM\Docs;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

Loader::includeModule('iblock');

class IBlock {
    public static function install() {
        return self::createType();
    }

    public static function uninstall() {
        $iBlockElements[] = Form::getIBlockElements("ID", "ASC", ["IBLOCK_TYPE" => TR_CA_IB_TYPE_ID]);
        foreach ($iBlockElements as $iBlockElement) {
            \CIBlockElement::Delete($iBlockElement);
        }

        $iBlockIds = Form::getIBlocksId();
        foreach ($iBlockIds as $iBlockId) {
            \CIBlock::Delete($iBlockId);
        }

        \CIBlockType::Delete(TR_CA_IB_TYPE_ID);
    }

    public static function createType() {
        global $DB;
        $ibType = new \CIBlockType();

        // Check if ib type already exists
        if ($ibType->GetByID(TR_CA_IB_TYPE_ID)->Fetch()) {
            return true;
        }

        $ibTypeFields = array(
            'ID' => TR_CA_IB_TYPE_ID,    // Unique ID
            'SECTIONS' => 'N',      // Subsections
            'IN_RSS' => 'N',        // RSS export
            'LANG' => array(
                'en' => array(
                    'NAME' => 'CryptoARM Forms',
                    'SECTION_NAME' => 'Sections',
                    'ELEMENT_NAME' => 'Elements'
                ),
                'ru' => array(
                    'NAME' => Loc::getMessage('TR_CA_IB_TYPE_NAME'),
                    'SECTION_NAME' => Loc::getMessage('TR_CA_IB_TYPE_SECTION_NAME'),
                    'ELEMENT_NAME' => Loc::getMessage('TR_CA_IB_TYPE_ELEMENT_NAME')
                ),
            ),
        );

        $DB->StartTransaction();
        $res = $ibType->add($ibTypeFields);
        if (!$res) {
            $DB->Rollback();
            return false;
        } else {
            $DB->Commit();
            return true;
        }
    }
}