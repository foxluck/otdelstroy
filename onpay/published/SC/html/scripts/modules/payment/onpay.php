<?php

/*if (empty($locals['mdlc_conpay_title']))
{
    $Register = &Register::getInstance();
    $locals['mdlc_conpay_title'] = CONPAY_TTL;
    $locals['mdlc_conpay_description'] = CONPAY_DSCR;
    $Register->set('CURRLANG_LOCALS', $locals);
}*/
/**
 * @connect_module_class_name COnpay
 * @package DynamicModules
 * @subpackage Payment
 */
// Onpay method implementation
// 
//		http://www.onpay.ru
class COnpay extends PaymentModule
{
    var $type = PAYMTD_TYPE_ONLINE;
    //var $language = 'rus';
    var $default_logo = '/published/SC/html/scripts/images/onpay.gif';
    function _initVars()
    {
        parent::_initVars();
        $this->title = CONPAY_TTL;
        $this->description = CONPAY_DSCR;
        $this->method_title = CONPAY_TTL;
        $this->method_description = CONPAY_DSCR;
        $this->sort_order = 0;
        $this->Settings = array("CONF_PAYMENTMODULE_ONPAY_LOGIN", "CONF_PAYMENTMODULE_ONPAY_PAROL",
            "CONF_PAYMENTMODULE_ONPAY_RATE", "CONF_PAYMENTMODULE_ONPAY_ORDERSTATUS", );
    }
    function _initSettingFields()
    {
        $this->SettingsFields['CONF_PAYMENTMODULE_ONPAY_LOGIN'] = array('settings_value' => '',
            'settings_title' => CONPAY_LOGIN_TTL, 'settings_description' =>
            CONPAY_LOGIN_DSCR, 'settings_html_function' =>
            'setting_TEXT_BOX(0,', 'sort_order' => 1, );
        $this->SettingsFields['CONF_PAYMENTMODULE_ONPAY_PAROL'] = array('settings_value' => '',
            'settings_title' => CONPAY_SECRETKEY_TTL, 'settings_description' =>
            CONPAY_SECRETKEY_DSCR, 'settings_html_function' =>
            'setting_TEXT_BOX(0,', 'sort_order' => 1, );
        $this->SettingsFields['CONF_PAYMENTMODULE_ONPAY_RATE'] = array('settings_value' => '1',
            'settings_title' => CONPAY_RATE_TTL, 'settings_description' =>
            CONPAY_RATE_DSCR, 'settings_html_function' =>
            'setting_TEXT_BOX(1,', 'sort_order' => 1, );
        $this->SettingsFields['CONF_PAYMENTMODULE_ONPAY_ORDERSTATUS'] = array('settings_value' =>
            '-1', 'settings_title' => CONPAY_ORDERSTATUS_TTL, 'settings_description' =>
            CONPAY_ORDERSTATUS_DSCR,
            'settings_html_function' => 'setting_SELECT_BOX(PaymentModule::_getStatuses(),',
            'sort_order' => 1, );
    }
    function getCustomProperties()
    {
        $customProperties = array();
        $transaction_result = 'success';
        $scURL = str_replace(array("http://", "https://"), array('', ''), trim(BASE_WA_URL)) . (SystemSettings::
            is_hosted() ? 'shop/' : 'published/SC/html/scripts/');
        $scURL = "http://" . $scURL . 'callbackhandlers/onpaypaymenthandler.php';
        $customProperties[] = array('settings_title' => CONPAY_URL_API_TTL, 'settings_description' =>
            CONPAY_URL_API_DSCR,
            'control' => '<input type="text" onclick="this.select();" onfocus="this.select();" readonly="readonly" size="40" value="' .
            xHtmlSpecialChars(str_replace('?', '&', $scURL)) . '">');
        return $customProperties;
    }
    function after_processing_html($orderID)
    {
        // загрузка заказа
        $order = ordGetOrder($orderID);
        // сумма заказа
        $order_amount = $order["order_amount"];
        $exhange_rate = (float)$this->_getSettingValue('CONF_PAYMENTMODULE_ONPAY_RATE');
        if ($exhange_rate <= 0)
            $exhange_rate = 1;
        $order_amount = round($order_amount / $exhange_rate, 2);
        // описание заказа
        $desc = $description = str_replace(array('[order]','[shop]'),array($orderID,CONF_SHOP_NAME),CONPAY_PAYNOTE_DSCR);
        // получаем настройки оплаты
        $user_email = urlencode($order["customer_email"]);
        $login = $this->_getSettingValue('CONF_PAYMENTMODULE_ONPAY_LOGIN');
        $key = $this->_getSettingValue('CONF_PAYMENTMODULE_ONPAY_PAROL');
        $order_id = $orderID;
        $sum = $order_amount;
        $sum_for_md5 = (strpos($sum, ".") ? round($sum, 2) : $sum . ".0");
        $scURL = "http://".str_replace(array("http://","https://"),array('',''), trim( CONF_FULL_SHOP_URL ));      
		$path1 = urlencode(set_query('ukey=transaction_result&transaction_result=success',$scURL));
        $path2 = urlencode(set_query('ukey=transaction_result&transaction_result=failure',$scURL));
        // md5 подпись
        $md5check = md5("fix;$sum_for_md5;RUR;$order_id;yes;$key");
        // платежная ссылка
        $url = "http://secure.onpay.ru/pay/$login?pay_mode=fix&pay_for=$order_id&price=$sum&currency=RUR&convert=yes&md5=$md5check&user_email=$user_email&url_success=$path1&url_fail=$path2&note=$desc";
        // темизация
        $res = '<table width="100%">
		<tr>
		<td align="center">
		'.str_replace(array('[amount]','[order]'),array($sum,$order_id),CONPAY_PAYSTART_TTL).'
		<iframe src="' . $url .
            '" scrolling="no" frameborder="no" height="600" width="100%" align="absmiddle">
		'.str_replace('[linkurl]',$url,CONPAY_IF_NO_FRAME).'
		</iframe>
		</td>
		</tr>
		</table>';
        return $res;
    }
    /**
     *  транслитерация
     */
    function encodestring($st)
    {
        return strtr($st, array('а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' =>
            'e', 'ж' => 'g', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm',
            'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' =>
            'f', 'ы' => 'i', 'э' => 'e', 'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D',
            'Е' => 'E', 'Ж' => 'G', 'З' => 'Z', 'И' => 'I', 'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' =>
            'M', 'Н' => 'N', 'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U',
            'Ф' => 'F', 'Ы' => 'I', 'Э' => 'E', 'ё' => "yo", 'х' => "h", 'ц' => "ts", 'ч' => "ch", 'ш' =>
            "sh", 'щ' => "shch", 'ъ' => "", 'ь' => "", 'ю' => "yu", 'я' => "ya", 'Ё' => "YO", 'Х' =>
            "H", 'Ц' => "TS", 'Ч' => "CH", 'Ш' => "SH", 'Щ' => "SHCH", 'Ъ' => "", 'Ь' => "", 'Ю' =>
            "YU", 'Я' => "YA"));
    }
    /**
     *  XML ответ на check запрос
     */
    function uc_ONPAY_answer($type, $code, $pay_for, $order_amount, $order_currency, $text, $key)
    {
        //echo "$type;$pay_for;$order_amount;$order_currency;$code;$key";
        $md5 = strtoupper(md5("$type;$pay_for;$order_amount;$order_currency;$code;$key"));
        $text = $this->encodestring($text);
        echo iconv('cp1251', 'utf-8', "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<result>\n<code>$code</code>\n<pay_for>$pay_for</pay_for>\n<comment>$text</comment>\n<md5>$md5</md5>\n</result>");
        exit;
    }
    /**
     *  XML ответ на pay запрос
     */
    function uc_ONPAY_answerpay($type, $code, $pay_for, $order_amount, $order_currency, $text, $onpay_id, $key)
    {
        $md5 = strtoupper(md5("$type;$pay_for;$onpay_id;$pay_for;$order_amount;$order_currency;$code;$key"));
        $text = $this->encodestring($text);
        echo iconv('cp1251', 'utf-8', "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<result>\n<code>$code</code>\n <comment>$text</comment>\n<onpay_id>$onpay_id</onpay_id>\n <pay_for>$pay_for</pay_for>\n<order_id>$pay_for</order_id>\n<md5>$md5</md5>\n</result>");
        exit;
    }
    function transactionResultHandler($transaction_result = '', $message = '', $source = 'frontend')
    {
        foreach ($_REQUEST as $itemkey => $itemvalue)
            if (substr($itemkey, 0, 4) == 'amp;')
            {
                $_REQUEST[substr($itemkey, 4)] = $itemvalue;
                unset($_REQUEST[$itemkey]);
            }
        if (empty($_REQUEST['type']))
            exit;
        $login = $this->_getSettingValue('CONF_PAYMENTMODULE_ONPAY_LOGIN');
        $key = $this->_getSettingValue('CONF_PAYMENTMODULE_ONPAY_PAROL');
        //Ответ на запрос check от OnPay
        if ($_REQUEST['type'] == 'check')
        {
            $order_amount = $amount = $_REQUEST['order_amount'];
            $order_currency = $_REQUEST['order_currency'];
            $orderID = $order_id = $pay_for = intval($_REQUEST['pay_for']);
            $order = db_fetch_row(db_query("SELECT `order_amount` FROM ".ORDERS_TABLE." 
			WHERE `orderID`='".$orderID."'"));

            $exhange_rate = (float)$this->_getSettingValue('CONF_PAYMENTMODULE_ONPAY_RATE');
            if ($exhange_rate <= 0)
                $exhange_rate = 1;
            $order_amount2 = round($order[0] * $exhange_rate, 2);

            $res = "";
            if ($order === false)
            {
                $res = 'ERROR 13: NO ORDER';
            } elseif ($order_amount2 != $amount)
            {
                $res = 'ERROR 14: ORDER SUM HACKED';
            }
            if ($res != "")
            {
                // произошла ошибка, не разрешаем платеж
                $this->uc_ONPAY_answer($_REQUEST['type'], 2, $pay_for, $order_amount, $order_currency, $res,
                    $key);
            }
            // можно принимать деньги
            $this->uc_ONPAY_answer($_REQUEST['type'], 0, $pay_for, $order_amount, $order_currency,
                'OK', $key);
        }
        //Ответ на запрос pay от OnPay
        elseif ($_REQUEST['type'] == "pay")
        {
            $onpay_id = $_REQUEST['onpay_id'];
            $orderID = $order_id = $code = $pay_for = intval($_REQUEST['pay_for']);
            $amount = $order_amount = $_REQUEST['order_amount'];
            $order_currency = $_REQUEST['order_currency'];
            $balance_amount = $_REQUEST['balance_amount'];
            $balance_currency = $_REQUEST['balance_currency'];
            $exchange_rate = $_REQUEST['exchange_rate'];
            $paymentDateTime = $_REQUEST['paymentDateTime'];
            $md5 = $_REQUEST['md5'];
            $error = '';
            //Проверка входных данных
            if (preg_replace('/[^0-9]/ismU', '', $onpay_id) != $onpay_id)
                $error = "ERROR 1: NO ID";
            elseif (strlen($onpay_id) < 1 or strlen($onpay_id) > 32)
                $error = "ERROR 2: NO ID";
            elseif (preg_replace('/[^0-9a-z]/ismU', '', $pay_for) != $pay_for)
                $error = "ERROR 3: NO ORDER ID";
            elseif (strlen($pay_for) < 1 or strlen($pay_for) > 32)
                $error = "ERROR 4: NO ORDER ID";
            elseif (preg_replace('/[^0-9\.]/ismU', '', $order_amount) != $order_amount)
                $error = "ERROR 5: NO ORDER SUM";
            elseif (floatval($order_amount) <= 0)
                $error = "ERROR 6: NO ORDER SUM";
            elseif (preg_replace('/[^0-9\.]/ismU', '', $balance_amount) != $balance_amount)
                $error = "ERROR 7: NO ORDER SUM";
            elseif (floatval($balance_amount) <= 0)
                $error = "ERROR 8: NO ORDER SUM";
            elseif (strlen($order_currency) != 3)
                $error = "ERROR 9: NO ORDER CURRENCY";
            elseif (strlen($balance_currency) != 3)
                $error = "ERROR 10: NO ORDER CURRENCY";
            elseif (preg_replace('/[^0-9a-z\.]/ismU', '', $exchange_rate) != $exchange_rate)
                $error = "ERROR 11: NO ORDER SUM";
            elseif (strlen($exchange_rate) < 1 or strlen($exchange_rate) > 10)
                $error = "ERROR 12: NO ORDER SUM";
            // произошла ошибка, не разрешаем платеж
            if ($error != '')
                $this->uc_ONPAY_answerpay($_REQUEST['type'], 3, $pay_for, $order_amount, $order_currency,
                    $error, $onpay_id, $key);
            $res = "";
            $order = db_fetch_row(db_query("SELECT `order_amount` FROM ".ORDERS_TABLE." WHERE `orderID`='".$orderID."'"));
            $exhange_rate = (float)$this->_getSettingValue('CONF_PAYMENTMODULE_ONPAY_RATE');
            if ($exhange_rate <= 0)
                $exhange_rate = 1;
            $order_amount2 = round($order[0] * $exhange_rate, 2);
            if ($order === false)
            {
                $res = 'ERROR 13: NO ORDER';
            } elseif ($order_amount2 != $amount)
            {
                $res = 'ERROR 14: ORDER SUM HACKED';
            } elseif (strtoupper(md5($_REQUEST['type'] . ";" . $pay_for . ";" . $onpay_id . ";" . $order_amount .
            ";" . $order_currency . ";" . $key . "")) != $_REQUEST['md5'])
            {
                $res = 'ERROR 15: MD5 SIGN HACKED';
                $this->uc_ONPAY_answerpay($_REQUEST['type'], 7, $pay_for, $order_amount, $order_currency,
                    $res, $onpay_id, $key);
            }
            if ($res != "")
            {
                // произошла ошибка, не разрешаем платеж
                $this->uc_ONPAY_answerpay($_REQUEST['type'], 3, $pay_for, $order_amount, $order_currency,
                    $res, $onpay_id, $key);
            }
            // зачисляем платеж
            $statusID = $this->_getSettingValue('CONF_PAYMENTMODULE_ONPAY_ORDERSTATUS');
            $comment = str_replace(array('[order]','[payno]'),array($order_id,$onpay_id),CONPAY_ENDPAY_DSCR);
            ostSetOrderStatusToOrder($order_id, $statusID, $comment, 0, true);
            $this->uc_ONPAY_answerpay($_REQUEST['type'], 0, $pay_for, $order_amount, $order_currency, 'OK', $onpay_id,
                $key);
        }
        exit;
    }
}

?>