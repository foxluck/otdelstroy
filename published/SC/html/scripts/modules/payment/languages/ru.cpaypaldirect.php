<?php
define('CPAYPALDIRECT_TTL', 'PayPal Direct');
define('CPAYPALDIRECT_DSCR', 'Обработка кредитных карт через платежную систему PayPal по методу Direct Payment. Информация о кредитной карте вводится покупателем на вашем сайте и затем передается на сервер PayPal.');
define('CPAYPALDIRECT_TXT_FNAME', 'Имя владельца карты:');
define('CPAYPALDIRECT_TXT_LNAME', 'Фамилия владельца карты:');
define('CPAYPALDIRECT_TXT_CCTYPE', 'Тип кредитной карты:');
define('CPAYPALDIRECT_TXT_CCNUMBER', 'Номер кредитной карты:');
define('CPAYPALDIRECT_TXT_CVV2','CVV:');
define('CPAYPALDIRECT_TXT_EXPDATE','Срок действия карты:');
define('CPAYPALDIRECT_TXT_TEST','Тестовый (Sandbox)');
define('CPAYPALDIRECT_TXT_LIVE','Рабочий');
define('CPAYPALDIRECT_TXT_DEFAULT', 'по умолчанию');

define('CPAYPALDIRECT_CFG_USERNAME_TTL', 'API Username');
define('CPAYPALDIRECT_CFG_USERNAME_DSCR', 'Введите API Username, которое было сгенерировано для Вас при подписке на PayPal Payments Pro');

define('CPAYPALDIRECT_CFG_PASSWORD_TTL', 'Пароль');
define('CPAYPALDIRECT_CFG_PASSWORD_DSCR', 'Введите пароль, который Вы указывали при подписке на PayPal Payments Pro');

define('CPAYPALDIRECT_CFG_CERTPATH_TTL', 'Сертификат PayPal');
define('CPAYPALDIRECT_CFG_CERTPATH_DSCR', 'В аккаунте на PayPal скачайте файл-сертификат (API certificate) и выберите этот файл в этой форме');

define('CPAYPALDIRECT_CFG_MODE_TTL', 'Режим работы');
define('CPAYPALDIRECT_CFG_MODE_DSCR', '');

define('CPAYPALDIRECT_CFG_PAYMENTACTION_TTL', 'Способ авторизации платежа');
define('CPAYPALDIRECT_CFG_PAYMENTACTION_DSCR', 'Sale для автоматического списания полной суммы заказа со счета клиента; Authorization - только авторизация карты, уточнение суммы к списанию и само списание производятся вручную в Вашем аккаунте на PayPal');

define('CPAYPALDIRECT_CFG_ORDERSTATUS_TTL', 'Статус заказа после удачного оформления');
define('CPAYPALDIRECT_CFG_ORDERSTATUS_DSCR', 'Вы можете выбрать статус заказа, который будет присваиваться всем заказам, оплата по которым была успешно авторизована. Выберите "по умолчанию", если Вы хотите, чтобы заказы приобретали статус новых заказов, который Вы можете настроить в разделе администрирования "Настройки"');

define('CPAYPALDIRECT_CFG_CURRENCY_TTL', 'Валюта - доллары США');
define('CPAYPALDIRECT_CFG_CURRENCY_DSCR', 'Сумма заказа, передаваемая в PayPal, указывается в долларах США. Выберите валюту из списка, которая представляет собой доллары США - это необходимо для корректного пересчета суммы заказа в доллары. Если валюта не выбрана, сумма не будет пересчитываться.');

define('CPAYPALDIRECT_TXT_STATUS_COMMENT','Номер транзакции в PayPal # %s');
?>