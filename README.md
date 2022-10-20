# Установка
1. Скопировать репозиторий в папку `frizus.reviews` в папку проекта `/local/modules/`
2. Установить модуль [sprint.migration](https://github.com/andreyryabin/sprint.migration)
3. Добавить в `/bitrix/.settings.php`
```php
  'routing' => [
      'value' => [
          'config' => ['api.php'],
      ],
  ],
```
и заменить в .htaccess

```apache
RewriteCond %{REQUEST_FILENAME} !/bitrix/urlrewrite.php$
RewriteRule ^(.*)$ /bitrix/urlrewrite.php [L]
```
на
```apache
RewriteCond %{REQUEST_FILENAME} !/bitrix/routing_index.php$
RewriteRule ^(.*)$ /bitrix/routing_index.php [L]
```
4. Установить модуль `frizus.reviews`, `spint.migration`
5. Установить миграции `Настройки -> Миграции для разработчиков -> Миграции (cfg)`
6. Задать инфоблок отзывов в `/bitrix/admin/settings.php?mid=frizus.reviews`
7. Смотреть вывод отзывов по ссылке `/api/reviews`

# Использование
Поддерживаются параметры запроса `limit` и `page`. По умолчанию `limit` равен `10`.

Тестировал на последней версии Битрикс.
Есть свои недостатки, особенно, у нового свойства идет привязка к инфоблоку и разделу по id и это не работает с миграциями.
