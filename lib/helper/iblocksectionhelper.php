<?php
namespace Frizus\Reviews\Helper;

use Bitrix\Iblock\SectionTable;

class IBlockSectionHelper
{
    // TODO кэш
    public static function getSections($iblockId)
    {
        static $rowsByIBlockId = [];

        if (!isset($iblockId) ||
            ($iblockId === '') ||
            (filter_var($iblockId, FILTER_VALIDATE_INT) === false) ||
            !(intval($iblockId) > 0)
        ) {
            return [];
        }

        if (!isset($rowsByIBlockId[$iblockId])) {
            $result = SectionTable::getList([
                'select' => ['ID', 'NAME', 'DEPTH_LEVEL'],
                'filter' => ['IBLOCK_ID' => $iblockId],
                'order' => ['LEFT_MARGIN' => 'ASC'],
            ]);

            $rowsByIBlockId[$iblockId] = [];
            while ($row = $result->fetch()) {
                $rowsByIBlockId[$iblockId][$row['ID']] = str_repeat('. ', $row['DEPTH_LEVEL'] - 1) . $row['NAME'];
            }
        }

        return $rowsByIBlockId[$iblockId];
    }
}