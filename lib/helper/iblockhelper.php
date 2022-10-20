<?php
namespace Frizus\Reviews\Helper;

use Bitrix\Iblock\IblockTable;

class IBlockHelper
{
    // TODO тегированный кеш
    public static function getIBlocks($lang = null)
    {
        static $rowsByLang = [];

        if (isset($lang) && (!is_string($lang) || ($lang === ''))) {
            return [];
        }

        $lang = $lang ?? LANGUAGE_ID;

        if (!isset($rowsByLang[$lang])) {
            $result = IblockTable::getList([
                'select' => ['ID', 'NAME', 'TYPE_NAME' => 'TYPE.LANG_MESSAGE.NAME'],
                'filter' => ['TYPE.LANG_MESSAGE.LANGUAGE_ID' => $lang],
                'order' => ['TYPE.SORT' => 'ASC', 'TYPE.ID' => 'ASC', 'SORT' => 'ASC', 'NAME' => 'ASC'],
            ]);

            $rowsByLang[$lang] = [];
            while ($row = $result->fetch()) {
                $rowsByLang[$lang][$row['ID']] = $row['NAME'] . ' [' . $row['TYPE_NAME'] . ']';
            }
        }

        return $rowsByLang[$lang];
    }
}