<?php
define('YANDEXCPP_TTL', 'Яндекс.Деньги (ЦПП)');
define('YANDEXCPP_DSCR', 'Интеграция с <a href="http://money.yandex.ru" target="_top">Яндекс.Деньгами</a> по методу «<a href="http://money.yandex.ru/doc.xml?id=459801" target="_top">Центр Приема Платежей</a>».<br />Настройки подключения к системе вы можете получить у Яндекса.');

define('YANDEXCPP_CFG_SHOPID_TTL', 'Shop ID');
define('YANDEXCPP_CFG_SHOPID_DSCR', 'Идентификатор магазина в ЦПП - уникальное значение, присваивается Магазину платежной системой');

define('YANDEXCPP_CFG_SCID_TTL','scid');
define('YANDEXCPP_CFG_SCID_DSCR','Номер витрины магазина в ЦПП. Выдается ЦПП.');

define('YANDEXCPP_CFG_BANKID_TTL', 'Bank ID');
define('YANDEXCPP_CFG_BANKID_DSCR', 'Идентификатор процессингового центра платежной системы');

define('YANDEXCPP_CFG_TARGETBANKID_TTL', 'Target bank ID');
define('YANDEXCPP_CFG_TARGETBANKID_DSCR', 'Идентификатор процессингового центра платежной системы');

define('YANDEXCPP_CFG_MODE_TTL', 'Режим работы модуля');
define('YANDEXCPP_CFG_MODE_DSCR', 'Определяет адрес (URL), куда будут отправлены данные о платеже');

define('YANDEXCPP_TXT_TESTMODE', 'Тестовый');
define('YANDEXCPP_TXT_LIVEMODE', 'Рабочий');

define('YANDEXCPP_CFG_TARGETCURRENCY_TTL', 'Валюта платежей');
define('YANDEXCPP_CFG_TARGETCURRENCY_DSCR', 'Выберите Рубли для рабочего режима и Деморубли для тестового');

define('YANDEXCPP_CFG_TRANSCURRENCY_TTL', 'Валюта платежей в Вашем магазине');
define('YANDEXCPP_CFG_TRANSCURRENCY_DSCR', 'Выберите из списка валют Вашего интернет-магазина валюту, которая соответствует Рублям или Деморублям (валюте системы Яндекс.Деньги). Необходимо для перерасчета стоимости заказа.');

define('YANDEXCPP_TXT_RUR', 'Рубли');
define('YANDEXCPP_TXT_DEMORUR', 'Деморубли');

define('YANDEXCPP_TXT_PROCESS', 'Оплатить через Яндекс.Деньги сейчас!');

define('YANDEXCPP_CFG_SHOPPASSWORD_TTL', 'Секретный пароль');
define('YANDEXCPP_CFG_SHOPPASSWORD_DSCR', 'используется при расчете криптографического хэша.');
define('YANDEXCPP_CFG_ORDERSTATUS_TTL', 'Статус заказа после подтверждения оплаты');
define('YANDEXCPP_CFG_ORDERSTATUS_DSCR', 'Все оплаченные на сайте заказы будут автоматически переведены в выбранный статус (по факту получения сообщения от сервера yandexmoney).');

define('YANDEXCPP_CUST_CHECKURL_TTL', 'checkURL');
define('YANDEXCPP_CUST_CHECKURL_DSCR', 'URL(https), на который отправляется запрос «Проверка заказа».<br><strong>Указанный в этом поле адрес скопируйте и сохраните в соответствующем поле внутри вашего аккаунта Yandex ЦПП.</strong>');
define('YANDEXCPP_CUST_RESULTURL_TTL', 'paymentAvisoURL');
define('YANDEXCPP_CUST_RESULTURL_DSCR', 'URL(https), на который отправляется запрос «Уведомление об оплате».<br><strong>Указанный в этом поле адрес скопируйте и сохраните в соответствующем поле внутри вашего аккаунта Yandex ЦПП.</strong>');
define('YANDEXCPP_CUST_SUCCESURL_TTL', 'successURL');
define('YANDEXCPP_CUST_SUCCESURL_DSCR', 'URL для кнопки "возврат в магазин" на странице, отображаемой покупателю после успешной оплаты.<br><strong>Указанный в этом поле адрес скопируйте и сохраните в соответствующем поле внутри вашего аккаунта Yandex ЦПП.</strong>');
define('YANDEXCPP_CUST_FAILURE_TTL', 'failURL');
define('YANDEXCPP_CUST_FAILURE_DSCR', 'URL для кнопки "возврат в магазин" на странице, отображаемой покупателю после неуспешной оплаты.<br><strong>Указанный в этом поле адрес скопируйте и сохраните в соответствующем поле внутри вашего аккаунта Yandex ЦПП.</strong>');

define('YANDEXCPP_CFG_DEVELOPER_TTL','Режим отладки');
define('YANDEXCPP_CFG_DEVELOPER_DSCR','Включите этот режим для отладки - все параметры обращения к модулю будут записываться в лог модуля');
?>