<?php
define('INTERSHIPPERMODULE_TTL', 'InterShipper');
define('INTERSHIPPERMODULE_DSCR', 'Расчет стоимости доставки в UPS, USPS, DHL, FedEx.<br>Требуется наличие аккаунта на www.intershipper.com');

define('INTERSHIPPER_CFG_USERNAME_TTL', 'Имя пользователя в системе InterShipper');
define('INTERSHIPPER_CFG_USERNAME_DSCR', 'Введите информацию о Вашей учетной записи в InterShipper');

define('INTERSHIPPER_CFG_PASSWORD_TTL', 'Пароль для учетной записи  в системе InterShipper');
define('INTERSHIPPER_CFG_PASSWORD_DSCR', 'Введите информацию о Вашей учетной записи в InterShipper');

define('INTERSHIPPER_CFG_CLASSES_TTL', 'Типы доставки');
define('INTERSHIPPER_CFG_CLASSES_DSCR', 'Отметьте предлагаемые пользователю типы (классы) доставки');

define('INTERSHIPPER_CFG_SHIPMETHOD_TTL', 'Как посылка попадет к доставляющей компании');
define('INTERSHIPPER_CFG_SHIPMETHOD_DSCR', 'Выберите способ доставки отправлений к компании-перевозчику');

define('INTERSHIPPER_CFG_SHMOPTION_TTL', 'Дополнительная информация к способу получения посылки компанией доставки');
define('INTERSHIPPER_CFG_SHMOPTION_DSCR', 'Укажите дополнительную информацию в зависимости от выбранного способа доставки отправления перевозчику');

define('INTERSHIPPER_CFG_PACKAGING_TTL', 'Упаковка');
define('INTERSHIPPER_CFG_PACKAGING_DSCR', 'Выберите способ упаковки отправлений (посылок)');

define('INTERSHIPPER_CFG_CONTENTS_TTL', 'Содержимое посылок');
define('INTERSHIPPER_CFG_CONTENTS_DSCR', 'Охарактеризуйте вид отправляемых товаров');

define('INTERSHIPPER_CFG_INSURANCE_TTL', 'Страховка посылок');
define('INTERSHIPPER_CFG_INSURANCE_DSCR', 'Введите процент от стоимости заказа (пример: 10%), точную сумму (пример: 15.96) или оставьте поле пустым, если страховка не нужна');

define('INTERSHIPPER_CFG_USD_TTL', 'Валюта "Доллары США"');
define('INTERSHIPPER_CFG_USD_DSCR', 'Стоимость доставки, расчитываемая сервером InterShipper, указывается в долларах США. Выберите валюту Вашего магазина, которая представляет собой доллары США для корректного пересчета стоимости доставки в другие валюты.');

define('INTERSHIPPER_CFG_CARRIERS_TTL', 'Компании-перевозчики');
define('INTERSHIPPER_CFG_CARRIERS_DSCR', 'Отметьте галочками те компании, услугами которых Вы пользуетесь. Стоимость доставки будет посылки будет расчитываться через каждую из выбранных компаний.');

define('INTERSHIPPER_CFG_STATE_TTL', 
	'Штат/провинция отправителя');
define('INTERSHIPPER_CFG_STATE_DSCR', 
	'Укажите штат/провинцию, из которой отправляются заказы');
	
define('INTERSHIPPER_CFG_POSTAL_TTL', 
	'Почтовый код (индекс, ZIP-код) отправителя');
define('INTERSHIPPER_CFG_POSTAL_DSCR', 
	'Укажите почтовый индекс (zip) места отправления заказов');
	
define('INTERSHIPPER_CFG_COUNTRY_TTL', 
	'Страна отправителя');
define('INTERSHIPPER_CFG_COUNTRY_DSCR', 
	'InterShipper расчитывает стоимость доставки только для отправлений с территории США. Выберите США в списке стран');
	
define('INTERSHIPPER_CFG_CITY_TTL', 
	'Город');
define('INTERSHIPPER_CFG_CITY_DSCR', 
	'Введите название города, из которого будут производиться отправления');
	
	
define('INTERSHIPPER_TXT_CARRIER_ACCOUNT', 
	'Укажите Ваше имя пользователя учетной записи в этой компании:<br>(если у Вас нет учетной записи, оставьте поле пустым)');
define('INTERSHIPPER_TXT_CARRIER_INVOICED', 
	'Я получаю счета непосредственно от компании-перевозчика');

?>