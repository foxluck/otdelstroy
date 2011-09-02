<?php
define('SHIPPING_MODULE_USPS_TTL','USPS');
define('SHIPPING_MODULE_USPS_DSCR','USPS Web Tools. Расчет стоимости доставки в USPS. Доставка только из США.<br>Необходимо наличие аккаунта на www.usps.com<br>Подробная информация по установке и запуску этого модуля приведены в Руководстве Пользователя');

define('USPS_CONF_ZIPORIGINATION_TTL','Почтовый индекс (Zip) отправителя');
define('USPS_CONF_ZIPORIGINATION_DSCR','Максимальная длина 5 символов');

define('USPS_CONF_USERID_TTL','USPS User ID');
define('USPS_CONF_USERID_DSCR','Логин к Вашей учетной записи USPS');

define('USPS_CONF_PASSWORD_TTL','USPS Password');
define('USPS_CONF_PASSWORD_DSCR','Пароль к Вашей учетной записи USPS');

define('USPS_CONF_PACKAGESIZE_TTL','Размер упаковки');
define('USPS_CONF_PACKAGESIZE_DSCR','Только для внутренних перевозок (внутри США). Подробнее на www.usps.com');

define('USPS_CONF_MACHINABLE_TTL','Machinable');
define('USPS_CONF_MACHINABLE_DSCR', 'Может ли товар транспортироваться сортироваться машиной? Только для внутренних перевозок (внутри США). Подробнее на www.usps.com');

define('USPS_CONF_DOMESTIC_SERVS_TTL','Доступные сервисы внутренних перевозок');
define('USPS_CONF_DOMESTIC_SERVS_DSCR','Внутри США');

define('USPS_CONF_INTERNATIONAL_SERVS_TTL','Допустимые сервисы международных перевозок');
define('USPS_CONF_INTERNATIONAL_SERVS_DSCR','');

define('USPSSHIPPINGMODULE_CFG_ENABLE_ERROR_LOG_TTL', 'Включить запись ошибочных ответов сервера USPS');
define('USPSSHIPPINGMODULE_CFG_ENABLE_ERROR_LOG_DSCR', 'В случае ошибки расчета стоимости доставки, сообщение об ошибке записывается в файл temp/usps_errors.log');

define('USPS_CONF_USD_CURRENCY_TTL', 'Валюта "Доллары США"');
define('USPS_CONF_USD_CURRENCY_DSCR', 'Стоимость доставки, расчитываемая USPS, указывается в долларах США. Выберите валюту Вашего магазина, которая представляет собой доллары США для корректного пересчета стоимости доставки в другие валюты.');
?>