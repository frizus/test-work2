<?

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Frizus\Reviews\Helper\IBlockHelper;

$moduleId = 'frizus.reviews';
Loc::loadMessages($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/main/options.php');

if (!$USER->IsAdmin()) {
    $APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));
}

Loader::includeModule($moduleId);
Loader::includeModule('iblock');
$iblocks = IBlockHelper::getIBlocks();

$options = [
    'REVIEWS_IBLOCK' => [
        'LABEL' => 'Инфоблок отзывов',
        'DESCRIPTION' => '<a href="/api/reviews" target="_blank">Посмотреть отзывы</a>',
        'VALIDATOR' => function(&$value) use ($iblocks) {
            if (($value !== '') && !array_key_exists($value, $iblocks)) {
                $value = null;
            }
        },
        'INPUT' => function($name) use($moduleId, $iblocks) {
            $value = Option::get($moduleId, $name);
            $haveValue = isset($value) && ($value !== '');
            $input = '<select name="' . $name . '">';
            $input .= '<option value="">Не выбрано</option>';
            foreach ($iblocks as $id => $name) {
                $input .= '<option value="' . $id . '"';
                if ($haveValue && (strval($id) === $value)) {
                    $input .= ' selected';
                }
                $input .= '>';
                $input .= $name;
                $input .= '</option>';
            }
            $input .= '</select>';
            return $input;
        }
    ],
];

$tabControl = new CAdminTabControl("tabControl", array(
    array(
        "DIV" => "edit1",
        "TAB" => GetMessage("MAIN_TAB_SET"),
        "TITLE" => GetMessage("MAIN_TAB_TITLE_SET")
    ),
));

if ((!empty($save) || !empty($restore)) && $REQUEST_METHOD == "POST" && check_bitrix_sessid()) {
    if (!empty($restore)) {
        foreach ($options as $name => $parameters)
        {
            if (array_key_exists('DEFAULT', $parameters)) {
                Option::set($moduleId, $name, $parameters['DEFAULT']);
            } else {
                Option::delete($moduleId, [
                    'name' => $name
                ]);
            }
        }
        CAdminMessage::ShowMessage(array("MESSAGE" => "Восстановлены значения по умолчанию.", "TYPE" => "OK"));
    } else {
        foreach ($options as $name => $parameters) {
            if (array_key_exists($name, $_POST)) {
                $value = htmlspecialcharsback($_POST[$name]);
                $parameters['VALIDATOR']($value);

                if (isset($value)) {
                    if ($value === '') {
                        Option::delete($moduleId, [
                            'name' => $name,
                        ]);
                    } else {
                        Option::set($moduleId, $name, $value);
                    }
                }
            }
        }

        CAdminMessage::ShowMessage(array("MESSAGE" => "Значения сохранены.", "TYPE" => "OK"));
    }
}
$tabControl->Begin();
?>

<form method="post" action="<?=$APPLICATION->GetCurPage()?>?mid=<?=urlencode($mid)?>&lang=<?=LANGUAGE_ID?>">
    <? $tabControl->BeginNextTab(); ?>
    <? foreach ($options as $name => $parameters): ?>
        <tr>
            <td width="50%">
                <label for="field-<?=$name?>"><?=$parameters['LABEL']?>:</label>
                <?if (array_key_exists('DESCRIPTION', $parameters)):?>
                    <br><?=$parameters['DESCRIPTION']?>
                <?endif?>
            </td>
            <td width="50%">
                <?=$parameters['INPUT']($name)?>
            </td>
        </tr>
    <? endforeach ?>

    <? $tabControl->Buttons(); ?>
    <input type="submit" name="save" value="<?= GetMessage("MAIN_SAVE") ?>"
           title="<?= GetMessage("MAIN_OPT_SAVE_TITLE") ?>" class="adm-btn-save">
    <input type="submit" name="restore" title="<?= GetMessage("MAIN_HINT_RESTORE_DEFAULTS") ?>"
           OnClick="return confirm('<?= AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING")) ?>')"
           value="<?= GetMessage("MAIN_RESTORE_DEFAULTS") ?>">
    <?= bitrix_sessid_post(); ?>
    <? $tabControl->End(); ?>
</form>