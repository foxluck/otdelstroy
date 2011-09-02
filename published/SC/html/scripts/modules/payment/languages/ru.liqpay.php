<?php
define('LIQPAY_TTL','LiqPay');
define('LIQPAY_DSCR','Liq&Buy 1.2');
define('LIQPAY_CFG_MERCHANT_ID_TTL','ID продавца');
define('LIQPAY_CFG_MERCHANT_ID_DSCR','');
define('LIQPAY_CFG_SECRET_KEY_TTL','Подпись');
define('LIQPAY_CFG_SECRET_KEY_DSCR','Используется значение «Подпись для остальных операций»');
define('LIQPAY_CFG_TRANSACTION_CURRENCY_TTL','Валюта заказа');
define('LIQPAY_CFG_TRANSACTION_CURRENCY_DSCR','Валюта, в которой будет проводиться транзакция в процессинговом центре.');
define('LIQPAY_CFG_SHOP_CURRENCY_TTL','Валюта интернет-магазина');
define('LIQPAY_CFG_SHOP_CURRENCY_DSCR','Валюта, соответствующая валюте заказа.');
define('LIQPAY_CFG_GATEWAY_TTL','Способ оплаты');
define('LIQPAY_CFG_GATEWAY_DSCR','');
define('LIQPAY_CFG_ORDERSTATUS_TTL','Автоматическая смена статуса заказа');
define('LIQPAY_CFG_ORDERSTATUS_DSCR','Все заказы, оплаченные на сайте LiqPay, будут автоматически переведены в выбранный статус (после получения подтверждения от сервера LiqPay).');
define('LIQPAY_CFG_CUSTOMER_PHONE_TTL','Телефон покупателя');
define('LIQPAY_CFG_CUSTOMER_PHONE_DSCR','Выберите поле в вашей форме регистрации, соответствующее телефонному номеру покупателя');
define('LIQPAY_TXT_ITRANSACTION_ID','Номер транзакции');
define('LIQPAY_TXT_ITRANSACTION_ERROR','Транзакция отклонена');
define('LIQPAY_TXT_ITRANSACTION_WAIT','Транзакция ожидает подтверждения');
define('LIQPAY_TXT_CURRUR','Рубли');
define('LIQPAY_TXT_CURUSD','Доллары');
define('LIQPAY_TXT_CUREUR','Евро');
define('LIQPAY_TXT_CURUAH','Гривны');
define('LIQPAY_TXT_GATEWAYSELECT','По выбору покупателя');
define('LIQPAY_TXT_GATEWAYCARD','Банковской картой');
define('LIQPAY_TXT_GATEWAYPHONE','LiqPay');
define('LIQPAY_TXT_NOT_DEFINED','не указано');
define('LIQPAY_TXT_ISTATUS_CHANGED','Статус заказа изменен автоматически');
define('LIQPAY_TXT_ICOMMENT_ADDED','Комментарий добавлен автоматически');

?>