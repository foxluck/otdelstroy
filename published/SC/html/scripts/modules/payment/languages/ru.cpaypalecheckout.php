<?php
define('CPAYPALECHECKOUT_TTL', 'PayPal Express Checkout');
define('CPAYPALECHECKOUT_DSCR', 'Прием платежей по PayPal по методу Express Checkout.');
define('CPAYPALECHECKOUT_TXT_TEST','Тестовый (Sandbox)');
define('CPAYPALECHECKOUT_TXT_LIVE','Рабочий');
define('CPAYPALECHECKOUT_TXT_DEFAULT', 'по умолчанию');
define('CPAYPALECHECKOUT_TXT_ERROR_CHECKOUT', 'Обратитесь к администратору магазина для получения дополнительной информации.');
define('CPAYPALECHECKOUT_TXT_CHECKOUT_CANCELED', 'Ваш платеж не был принят.');
define('CPAYPALECHECKOUT_TXT_CHECKOUT_SUCCESS', 'Ваш платеж успешно принят! Пожалуйста, нажмите "Оформить заказ!" для подтверждения платежа и завершения оформления заказа.');

define('CPAYPALECHECKOUT_CFG_USERNAME_TTL', 'API Username');
define('CPAYPALECHECKOUT_CFG_USERNAME_DSCR', 'Введите API Username, которое было сгенерировано для Вас при подписке на PayPal Payments Pro');

define('CPAYPALECHECKOUT_CFG_PASSWORD_TTL', 'Пароль');
define('CPAYPALECHECKOUT_CFG_PASSWORD_DSCR', 'Введите пароль, который Вы указывали при подписке на PayPal Payments Pro');

define('CPAYPALECHECKOUT_CFG_CERTPATH_TTL', 'Сертификат PayPal');
define('CPAYPALECHECKOUT_CFG_CERTPATH_DSCR', 'В аккаунте на PayPal скачайте файл-сертификат (API certificate) и выберите этот файл в этой форме');

define('CPAYPALECHECKOUT_CFG_MODE_TTL', 'Режим работы');
define('CPAYPALECHECKOUT_CFG_MODE_DSCR', '');

define('CPAYPALECHECKOUT_CFG_PAYMENTACTION_TTL', 'Способ авторизации платежа');
define('CPAYPALECHECKOUT_CFG_PAYMENTACTION_DSCR', 'Sale для автоматического списания полной суммы заказа со счета клиента; Authorization и Order - только авторизация карты, уточнение суммы к списанию и само списание производятся вручную в Вашем аккаунте на PayPal');

define('CPAYPALECHECKOUT_CFG_ORDERSTATUS_TTL', 'Статус заказа после удачного оформления');
define('CPAYPALECHECKOUT_CFG_ORDERSTATUS_DSCR', 'Вы можете выбрать статус заказа, который будет присваиваться всем заказам, оплата по которым была успешно авторизована. Выберите "по умолчанию", если Вы хотите, чтобы заказы приобретали статус новых заказов, который Вы можете настроить в разделе администрирования "Настройки"');

define('CPAYPALECHECKOUT_CFG_CURRENCY_TTL', 'Доллары США');
define('CPAYPALECHECKOUT_CFG_CURRENCY_DSCR', 'Сумма заказа, передаваемая в PayPal, указывается в долларах США. Выберите валюту из списка, которая представляет собой доллары США - это необходимо для корректного пересчета суммы заказа в доллары. Если валюта не выбрана, сумма не будет пересчитываться.');

define('CPAYPALECHECKOUT_CFG_NOSHIPPING_TTL', 'Отключить для покупателя возможность выбора адреса доставки на сайте PayPal');
define('CPAYPALECHECKOUT_CFG_NOSHIPPING_DSCR', '');

define('CPAYPALECHECKOUT_USERINFO_PREFIX', 'PayPal подтвердил следующий адрес доставки заказа:<br>');
?>