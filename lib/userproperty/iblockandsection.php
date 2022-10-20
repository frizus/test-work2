<?php
namespace Frizus\Reviews\UserProperty;

use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Application;
use Bitrix\Main\Page\Asset;
use Frizus\Reviews\Helper\IBlockHelper;
use Frizus\Reviews\Helper\IBlockSectionHelper;

/**
 * TODO привязку по внешнему коду для совместимости с миграциями
 */
class IBlockAndSection
{
    public const USER_TYPE = 'frizus_reviews_iblock_and_section';

    public static function GetPropertyDescription()
    {
        return [
            'PROPERTY_TYPE' => PropertyTable::TYPE_STRING,
            'USER_TYPE' => IBlockAndSection::USER_TYPE,
            'DESCRIPTION' => 'Привязка к разделу любого инфоблока',
            'GetPublicViewHTML' => array(__CLASS__, 'GetPublicViewHTML'),
            'GetPublicEditHTML' => array(__CLASS__, 'GetPublicEditHTML'),
            'GetAdminListViewHTML' => array(__CLASS__, 'GetAdminListViewHTML'),
            'GetPropertyFieldHtml' => array(__CLASS__, 'GetPropertyFieldHtml'),
            'ConvertToDB' => array(__CLASS__, 'ConvertToDB'),
            'ConvertFromDB' => array(__CLASS__, 'ConvertFromDB'),
            'GetLength' =>array(__CLASS__, 'GetLength'),
            'PrepareSettings' =>array(__CLASS__, 'PrepareSettings'),
            'GetSettingsHTML' =>array(__CLASS__, 'GetSettingsHTML'),
            'GetUIFilterProperty' => array(__CLASS__, 'GetUIFilterProperty')
        ];
    }

    public static function GetPublicViewHTML($arProperty, $value, $strHTMLControlName)
    {
        if (!is_array($value['VALUE'])) {
            $value = static::ConvertFromDB($arProperty, $value);
        }

        $ar = $value['VALUE'];

        if (is_array($ar) && !empty($ar)) {
            if (isset($strHTMLControlName['MODE']) && in_array($strHTMLControlName['MODE'], ['CSV_EXPORT', 'SIMPLE_TEXT'], true)) {
                return $ar['IBLOCK_ID'] . ' ' . $ar['SECTION_ID'];
            }

            return $ar['IBLOCK_ID'] . ' ' . $ar['SECTION_ID'];
        }

        return '';
    }

    public static function GetAdminListViewHTML($arProperty, $value, $strHTMLControlName)
    {
        if (!is_array($value['VALUE'])) {
            $value = static::ConvertFromDB($arProperty, $value);
        }

        $ar = $value['VALUE'];

        if (is_array($ar) && !empty($ar)) {
            $iblocks = IBlockHelper::getIBlocks();
            $html = '';
            $exists = false;
            $iblockId = $ar['IBLOCK_ID'];
            $sectionId = $ar['SECTION_ID'];
            if (array_key_exists($iblockId, $iblocks)) {
                $iblock = $iblocks[$iblockId];
                $exists = true;
                $html .= '<div>Инфоблок: ' . $iblock . '</div>';
                $sections = IBlockSectionHelper::getSections($iblockId);

                if (array_key_exists($sectionId, $sections)) {
                    $html .= '<div>Раздел: '. $sections[$sectionId] . '</div>';
                } else {
                    $html .= '<div>Раздел: <i>'. $sectionId . '</i></div>';
                }
            }

            if (!$exists) {
                $html .= '<div>Инфоблок: <i>' . $iblockId . '</div>';
                $html .= '<div>Раздел: <i>' . $sectionId . '</i></div>';
            }

            return $html;
        }
    }

    public static function GetPublicEditHTML($arProperty, $value, $strHTMLControlName)
    {
        if (!is_array($value['VALUE'])) {
            $value = static::ConvertFromDB($arProperty, $value);
        }

        if (isset($strHTMLControlName['MODE']) && ($strHTMLControlName['MODE'] === 'SIMPLE')) {
            return static::selectIBlockAndSection($arProperty, $value, $strHTMLControlName);
        }

        return static::selectIBlockAndSection($arProperty, $value, $strHTMLControlName);
    }

    public static function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
    {
        $strHTMLControlName["VALUE"] = htmlspecialcharsEx($strHTMLControlName["VALUE"]);

        if (!is_array($value['VALUE'])) {
            $value = static::ConvertFromDB($arProperty, $value);
        }

        return static::selectIBlockAndSection($arProperty, $value, $strHTMLControlName);
    }

    public static function ConvertToDB($arProperty, $value)
    {
        $return = false;

        if (!is_array($value)) {
            $value = static::getValueFromString($value, true);
        } elseif (isset($value['VALUE']) && !is_array($value['VALUE'])) {
            $value['VALUE'] = static::getValueFromString($value, false);
        }

        $defaultValue = isset($value['DEFAULT_VALUE']) && $value['DEFAULT_VALUE'] === true;

        if (is_array($value) && array_key_exists('VALUE', $value)) {
            if (isset($value['VALUE']['IBLOCK_ID']) &&
                isset($value['VALUE']['SECTION_ID']) &&
                (filter_var($value['VALUE']['IBLOCK_ID'], FILTER_VALIDATE_INT) !== false) &&
                (intval($value['VALUE']['IBLOCK_ID']) > 0) &&
                (filter_var($value['VALUE']['SECTION_ID'], FILTER_VALIDATE_INT) !== false) &&
                (intval($value['VALUE']['SECTION_ID']) > 0) &&
                array_key_exists($value['VALUE']['IBLOCK_ID'], IBlockHelper::getIBlocks()) &&
                array_key_exists($value['VALUE']['SECTION_ID'], IBlockSectionHelper::getSections($value['VALUE']['IBLOCK_ID']))
            ) {
                $return = [
                    'VALUE' => serialize([
                        'IBLOCK_ID' => strval($value['VALUE']['IBLOCK_ID']),
                        'SECTION_ID' => strval($value['VALUE']['SECTION_ID']),
                    ]),
                ];
                $value['DESCRIPTION'] = trim($value['DESCRIPTION']);

                if ($value['DESCRIPTION'] !== '') {
                    $return['DESCRIPTION'] = $value['DESCRIPTION'];
                }
            }
        }

        return $return;
    }

    public static function ConvertFromDB($arProperty, $value)
    {
        $return = false;

        if (!is_array($value['VALUE'])) {
            $return = [
                'VALUE' => unserialize($value['VALUE']),
            ];

            if ($return['VALUE'] === false) {
                $return = [
                    'VALUE' => [],
                ];
            }

            if ($value['DESCRIPTION']) {
                $return['DESCRIPTION'] = $value['DESCRIPTION'];
            }
        }

        // TODO возвращать полный массив и посмотреть как у свойства привязки к элементам в другой ключ массива свойства сохраняется этот элемент
        if (is_array($return['VALUE'])) {
            $ar = $return['VALUE'];

            if (array_key_exists('IBLOCK_ID', $ar)) {
                $iblocks = IBlockHelper::getIBlocks();

                if (array_key_exists($ar['IBLOCK_ID'], $iblocks)) {
                    $sections = IBlockSectionHelper::getSections($ar['IBLOCK_ID']);

                    if (array_key_exists($ar['SECTION_ID'], $sections)) {
                        $return['VALUE']['IBLOCK_NAME'] = $iblocks[$ar['IBLOCK_ID']];
                        $return['VALUE']['SECTION_NAME'] = $sections[$ar['SECTION_ID']];
                    }
                }
            }
        }

        return $return;
    }

    public static function PrepareSettings($arProperty)
    {
       return [];
    }

    public static function GetSettingsHTML($arProperty, $strHTMLControlName, &$arPropertyFields)
    {
        $arPropertyFields = [
            'HIDE' => ['ROW_COUNT', 'COL_COUNT'],
        ];

        return '';
    }

    // TODO сделать фильтр
    public static function GetUIFilterProperty($property, $strHTMLControlName, &$fields)
    {
        $fields['type'] = 'custom_entity';
        $fields['property'] = $property;
        $fields['customRender'] = ["\\Bitrix\\Iblock\\Helpers\\Filter\\Property", 'render'];
        $fields['customFilter'] = ["\\Bitrix\\Iblock\\Helpers\\Filter\\Property", 'addFilter'];
        $fields['operators'] = array(
            'default' => '=',
            'exact' => '=',
        );
    }

    protected static function getValueFromString($value, $getFull = false)
    {
        $value = strval($value);

        if ($value !== '') {
            $matches = null;
            if (preg_match('#^(?<iblock_id>\d+) (?<section_id>\d+)$#', $value, $matches)) {
                $value = [
                    'IBLOCK_ID' => $matches['iblock_id'],
                    'SECTION_ID' => $matches['section_id'],
                ];
            } else {
                $value = [];
            }
        } else {
            $value = [];
        }

        if ($getFull) {
            return [
                'VALUE' => $value,
            ];
        }

        return $value;
    }

    protected static function selectIBlockAndSection($arProperty, $value, $strHTMLControlName)
    {
        static $scriptsCalled;
        if (!isset($scriptsCalled)) {
            $scriptsCalled = true;
            \CJSCore::Init(['jquery']);
            Asset::getInstance()->addCss('/bitrix/css/frizus.reviews/select-iblock-and-section.css');
            Asset::getInstance()->addJs('/bitrix/js/frizus.reviews/select-iblock-and-section.js');
            $asset = Asset::getInstance();
            $asset->addString('
<script type="text/javascript">
    (function($, window, document) {
        $(document).ready(function() {
            $(\'.frizus-reviews-iblock-and-section\').bitrixIBlockAndSection({
                url: \'/bitrix/tools/frizus.reviews/section.php\'
            })
        })
    })(jQuery, window, document)
</script>
');
        }

        $ar = $value['VALUE'];
        $haveValue = array_key_exists('IBLOCK_ID', $ar) &&
            array_key_exists('SECTION_ID', $ar);
        $haveIBlockSelected = false;

        $html = '';
        $html .= '<div class="frizus-reviews-iblock-and-section">';
        $html .= '<select class="iblock-and-section-iblock" name="' . $strHTMLControlName['VALUE'] . '[IBLOCK_ID]">';
        $html .= '<option value="">Выберите инфоблок</option>';
        foreach (IBlockHelper::getIBlocks() as $id => $name) {
            $html .= '<option value="' . $id . '"';
            if ($haveValue && (strval($id) === $ar['IBLOCK_ID'])) {
                $html .= ' selected';
                $haveIBlockSelected = true;
            }
            $html .= '>';
            $html .= $name;
            $html .= '</option>';
        }
        $html .= '</select>';

        $html .= '<select class="iblock-and-section-section" name="' . $strHTMLControlName['VALUE'] . '[SECTION_ID]"';
        if ($haveIBlockSelected) {
            $sections = IBlockSectionHelper::getSections($ar['IBLOCK_ID']);
        }
        if (
            ($haveIBlockSelected && !array_key_exists($ar['SECTION_ID'], $sections)) ||
            !$haveIBlockSelected
        ) {
            $html .= ' style="display: none"';
        }
        $html .= '>';
        $html .= '<option value="">Выберите раздел</option>';
        if ($haveIBlockSelected) {
            foreach ($sections as $id => $name) {
                $html .= '<option value="' . $id . '"';
                if ((strval($id) === $ar['SECTION_ID'])) {
                    $html .= ' selected';
                }
                $html .= '>';
                $html .= $name;
                $html .= '</option>';
            }
        }
        $html .= '</select>';
        return $html;
    }
}