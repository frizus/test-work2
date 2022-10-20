<?php
namespace Frizus\Reviews\Controller;

use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Engine\ActionFilter\Authentication;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;

class Reviews extends Controller
{
    /**
     * TODO поиграть с тегированным кешированием общего количества и постранично (приводить limit к общей форме - например, 25, 50, 100)
     */
    public function indexAction()
    {
        $iblockId = Option::get('frizus.reviews', 'REVIEWS_IBLOCK');

        if (!isset($iblockId) || ($iblockId === '')) {
            $this->addError(new Error('Внутренняя ошибка', 'no_reviews_iblock'));
            return;
        }

        Loader::includeModule('iblock');

        $limit = $this->getLimit();
        $count = ElementTable::getCount(['IBLOCK_ID' => $iblockId, 'ACTIVE' => 'Y']);
        $page = $this->getPage($count, $limit);

        $data = [
            'all_count' => $count,
            'limit' => $limit,
            'page' => $page,
        ];

        if ($count > 0) {
            $data['list'] = [];

            $result = \CIBlockElement::GetList(
                ['ID' => 'ASC'],
                ['IBLOCK_ID' => $iblockId, 'ACTIVE' => 'Y'],
                false,
                ['nPageSize' => $limit, 'iNumPage' => $page],
                ['ID', 'NAME', 'IBLOCK_ID']
            );
            $result->DBNavStart();
            while ($row = $result->GetNextElement(true, false)) {
                $fields = $row->GetFields();
                $properties = $row->GetProperties();

                $cityPropertyValue = $properties['CITY']['VALUE'];
                if (is_array($cityPropertyValue) && !empty($cityPropertyValue)) {
                    $city = $cityPropertyValue['SECTION_NAME'] ?? ($cityPropertyValue['IBLOCK_ID'] . ' ' . $cityPropertyValue['SECTION_ID']);
                } else {
                    $city = null;
                }

                $data['list'][] = [
                    'fields' => [
                        'id' => $fields['ID'],
                        'name' => $fields['NAME'],
                    ],
                    'properties' => [
                        'city' => $city,
                        'rating' => $properties['RATING']['VALUE'],
                    ],
                ];
            }
        }

        return $data;
    }

    protected function getLimit()
    {
        $limit = $this->request->getQuery('limit');

        if (!isset($limit) ||
            ($limit === '') ||
            (filter_var($limit, FILTER_VALIDATE_INT) === false) ||
            !(intval($limit) > 0)
        ) {
            $limit = 10;
        } else {
            $limit = intval($limit);
            if ($limit > 500) {
                $limit = 500;
            }
        }

        return $limit;
    }

    protected function getPage($count, $limit)
    {
        $page = $this->request->getQuery('page');

        if (!isset($page) ||
            ($page === '') ||
            (filter_var($page, FILTER_VALIDATE_INT) === false) ||
            !(intval($page) > 0)
        ) {
            $page = 1;
        } else {
            $page = intval($page);
        }

        $maxPages = (int)floor($count / $limit);
        if(($count % $limit) > 0)
        {
            $maxPages++;
        }

        if ($page > $maxPages) {
            $page = $maxPages;
        }

        return $page;
    }

    protected function getDefaultPreFilters()
    {
        return [
            new Authentication(),
        ];
    }
}
