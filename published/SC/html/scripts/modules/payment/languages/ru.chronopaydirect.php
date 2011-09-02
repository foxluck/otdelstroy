<?php
define('CHRONOPAYDIRECT_TTL','Chronopay (gateway)');

define('CHRONOPAYDIRECT_DSCR','Обработка кредитных карт через платежную систему Chronopay (www.chronopay.ru).<br>
Информация о карте вводится в вашем магазине и передается по защищенному протоколу на сервер Хронопей. Для работы модуля необходима поддержка CURL и наличие SSL-сертификата для домена вашего интернет-магазина.');

define('CHRONOPAYDIRECT_CFG_PRODUCT_ID_TTL','Product ID');

define('CHRONOPAYDIRECT_CFG_PRODUCT_ID_DSCR','Эта информация доступна в вашем аккаунте в системе Хронопей.');

define('CHRONOPAYDIRECT_CFG_CURCODE_TTL', 'Доллары США');
define('CHRONOPAYDIRECT_CFG_CURCODE_DSCR', 'Сумма заказа, передаваемая в Chronopay, указывается в долларах США. Выберите валюту из списка, которая представляет собой доллары США - это необходимо для корректного пересчета суммы заказа в доллары. Если валюта не выбрана, сумма не будет пересчитываться.');

define('CHRONOPAYDIRECT_TXT_ERROR_PROCESSING', 'Ошибка обработки транзакции');
define('CHRONOPAYDIRECT_INVALID_SERVER_RESPONCE','Неверный ответ сервера, попробуйте еще раз или позднее');

define('CHRONOPAYDIRECT_CFG_SHAREDSECRET_TTL', 'Shared Secret');
define('CHRONOPAYDIRECT_CFG_SHAREDSECRET_DSCR', 'Отправлен по e-mail из ChronoPay');

define('CHRONOPAYDIRECT_CFG_ORDERSTATUS_TTL', 'Статус заказа после удачного оформления');
define('CHRONOPAYDIRECT_CFG_ORDERSTATUS_DSCR', 'Вы можете выбрать статус заказа, который будет присваиваться всем заказам, оплата по которым была успешно авторизована. Выберите "по умолчанию", если Вы хотите, чтобы заказы приобретали статус новых заказов, который Вы можете настроить в разделе администрирования "Настройки"');
define('CHRONOPAYDIRECT_TXT_DEFAULT','По умолчанию');

define('CHRONOPAYDIRECT_TXT_CARDHOLDER','Имя владельца карты');
define('CHRONOPAYDIRECT_TXT_PHONE','Номер телефона');
define('CHRONOPAYDIRECT_TXT_CARD_NUMBER','Номер карты');
define('CHRONOPAYDIRECT_TXT_CVV','CVV код');
define('CHRONOPAYDIRECT_TXT_EXPIRATION','Срок действия карты');
?>