<?

use Bitrix\Main\Loader;
use Frizus\Reviews\Helper\IBlockSectionHelper;

define("PUBLIC_AJAX_MODE", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if (!$USER->IsAdmin()) {
    return;
}

Loader::includeModule('iblock');
Loader::includeModule('frizus.reviews');

// TODO json
$sections = IBlockSectionHelper::getSections($_GET['iblockId']);
?>
<select>
    <?foreach ($sections as $id => $name):?>
        <option value="<?=$id?>"><?=$name?></option>
    <?endforeach?>
</select>
