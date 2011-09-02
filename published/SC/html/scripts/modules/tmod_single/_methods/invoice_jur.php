<?php
/* @var $smarty Smarty */
$smarty = &Core::getSmarty();

	//счет на оплату
	function _my_formatPrice($price)
	{
		$price = round($price*100)/100;
		if (round($price*10) == $price*10 && round($price)!=$price) 
		$price = "$price"."0"; //to avoid prices like 17.5 - write 17.50 instead
		return _formatPrice($price);
	}

	function number2string($n,$rod) //перевести число $n в строку. Число обязательно должно быть 0 < $n < 1000. $rod указывает на род суффикса (0 - женский, 1 - мужской; например, "рубль" - 1, "тысяча" - 0).
	{
		$n = round($n);
		$a = floor($n / 100);
		$b = floor(($n - $a*100) / 10);
		$c = $n % 10;

		$s = "";
		switch($a)
		{
			case 1: $s = "сто";
			break;
			case 2: $s = "двести";
			break;
			case 3: $s = "триста";
			break;
			case 4: $s = "четыреста";
			break;
			case 5: $s = "пятьсот";
			break;
			case 6: $s = "шестьсот";
			break;
			case 7: $s = "семьсот";
			break;
			case 8: $s = "восемьсот";
			break;
			case 9: $s = "девятьсот";
			break;
		}
		$s .= " ";
		if ($b != 1)
		{
		   switch($b)
		   {
			case 1: $s .= "десять";
			break;
			case 2: $s .= "двадцать";
			break;
			case 3: $s .= "тридцать";
			break;
			case 4: $s .= "сорок";
			break;
			case 5: $s .= "пятьдесят";
			break;
			case 6: $s .= "шестьдесят";
			break;
			case 7: $s .= "семьдесят";
			break;
			case 8: $s .= "восемьдесят";
			break;
			case 9: $s .= "девяносто";
			break;
		   }
		   $s .= " ";
		   switch($c)
		   {
			case 1: $s .= $rod ? "один" : "одна";
			break;
			case 2: $s .= $rod ? "два" : "две";
			break;
			case 3: $s .= "три";
			break;
			case 4: $s .= "четыре";
			break;
			case 5: $s .= "пять";
			break;
			case 6: $s .= "шесть";
			break;
			case 7: $s .= "семь";
			break;
			case 8: $s .= "восемь";
			break;
			case 9: $s .= "девять";
			break;
		   }
		}
		else //...дцать
		{
		   switch($c)
		   {
			case 0: $s .= "десять";
			break;
			case 1: $s .= "одиннадцать";
			break;
			case 2: $s .= "двенадцать";
			break;
			case 3: $s .= "тринадцать";
			break;
			case 4: $s .= "четырнадцать";
			break;
			case 5: $s .= "пятьнадцать";
			break;
			case 6: $s .= "шестьнадцать";
			break;
			case 7: $s .= "семьнадцать";
			break;
			case 8: $s .= "восемьнадцать";
			break;
			case 9: $s .= "девятьнадцать";
			break;
		   }
		}
		return $s;
	}

	function create_string_representation_of_a_number( $n )
		// создает строковое представление суммы. Например $n = 123.
		// результат будет "Сто двадцать три рубля 00 копеек"
	{
		//разделить сумма на разряды: единицы, тысячи, миллионы, миллиарды (больше миллиардов не проверять :) )

		$billions = floor($n / 1000000000);
		$millions = floor( ($n-$billions*1000000000) / 1000000);
		$grands = floor( ($n-$billions*1000000000-$millions*1000000) / 1000);
		$roubles = floor( ($n-$billions*1000000000-$millions*1000000-$grands*1000) );//$n % 1000;

		//копейки
		$kop = round ( $n*100 - round( floor($n)*100 ) );
		if ($kop < 10) $kop = "0".(string)$kop;

		$s = "";
		if ($billions > 0)
		{
			$t = "ов";
			$temp = $billions % 10;
			if (floor(($billions % 100)/10) != 1)
			{
				if ($temp == 1) $t = "";
				else if ($temp >=2 && $temp <= 4) $t = "а";
			}
			$s .= number2string($billions,1)." миллиард$t ";
		}
		if ($millions > 0)
		{
			$t = "ов";
			$temp = $millions % 10;
			if (floor(($millions % 100)/10) != 1)
			{
				if ($temp == 1) $t = "";
				else if ($temp >=2 && $temp <= 4) $t = "а";
			}
			$s .= number2string($millions,1)." миллион$t ";
		}
		if ($grands > 0)
		{
			$t = "";
			$temp = $grands % 10;
			if (floor(($grands % 100)/10) != 1)
			{
				if ($temp == 1) $t = "а";
				else if ($temp >=2 && $temp <= 4) $t = "и";
			}
			$s .= number2string($grands,0)." тысяч$t ";
		}
		if ($roubles > 0)
		{
			$rub = "ей";
			$temp = $roubles % 10;
			if (floor(($roubles % 100)/10) != 1)
			{
				if ($temp == 1) $rub = "ь";
				else if ($temp >=2 && $temp <= 4) $rub = "я";
			}
			$s .=  number2string($roubles,1)." рубл$rub ";
		}

		{
			$kp = "ек";
			$temp = $kop % 10;
			if (floor(($kop % 100)/10) != 1)
			{
				if ($temp == 1) $kp = "йка";
				else if ($temp >=2 && $temp <= 4) $kp = "йки";
			}

			$s .= "$kop копе$kp";
		}

		//теперь сделать первую букву заглавной
		if ($roubles>0 || $grands>0 || $millions>0 || $billions>0)
		{
			$cnt=0; while($s[$cnt]==" ") $cnt++;
			$s[$cnt] = chr( ord($s[$cnt])- 32 );
		}

		return $s;
	}

	//assign core Smarty variables
	if (!isset($_GET["orderID"]) || !isset($_GET["order_time"]) || !isset($_GET["customer_email"]) || !isset($_GET["moduleID"]))
	{
		die ("Заказ не найден в базе данных");
	}

	$InvoiceModule = PaymentModule::getInstance($_GET['moduleID']);
	$smarty->assign('InvoiceModule', $InvoiceModule);
	
	$_GET["orderID"] = (int) $_GET["orderID"];

	$q = db_query("select count(*) from ".ORDERS_TABLE." where orderID=".$_GET["orderID"]." and order_time='".base64_decode($_GET["order_time"])."' and customer_email='".base64_decode($_GET["customer_email"])."'") or die (db_error());
	$row = db_fetch_row($q);

	if ($row[0] == 1) //заказ найден в базе данных
	{
		$order = ordGetOrder( $_GET["orderID"] ); //order details
		//define smarty vars
		$smarty->hassign( "billing_lastname", $order["billing_lastname"] );
		$smarty->hassign( "billing_firstname", $order["billing_firstname"] );
		$smarty->hassign( "billing_city", $order["billing_city"] );
		$smarty->hassign( "billing_address", $order["billing_address"] );
		$smarty->hassign( "orderID", $_GET["orderID"] );
		$smarty->hassign( "order_time", $order["order_time"] );

		if (!$InvoiceModule->is_installed()) //модуль не установлен
		{
			die ("Модуль выписки счетов не установлен");
		}

		//сумма счета
		$sql = "select company_name, company_inn, nds_included, nds_rate, RUR_rate from ".DBTABLE_PREFIX."_module_payment_invoice_jur where orderID=".$_GET["orderID"]." AND module_id='".$InvoiceModule->ModuleConfigID."'";

		$q = db_query($sql);
		$row = db_fetch_row($q);
		if ($row) //сумма найдена в файле с описанием счета
		{
			$smarty->hassign( "customer_companyname", $row["company_name"] );
			$smarty->hassign( "customer_inn",  $row["company_inn"] );
			$nds_rate = (float) $row["nds_rate"];
			$RUR_rate = (float) $row["RUR_rate"];
			$nds_included = !strcmp((string)$row["nds_included"],"1") ? 1 : 0;
		}
		else //информация о счет не найдена
		{
			die ("Счет не найден в базе данных");
		}

		//заказанные товары
		$order_content = ordGetOrderContent( $_GET["orderID"] ); 
		$amount = 0;
		foreach( $order_content as $key => $val)
		{
			$order_content[$key]["Price"] = _my_formatPrice ( $order_content[$key]["Price"] * $RUR_rate );
			$order_content[$key]["Price_x_Quantity"] = _my_formatPrice ( $val["Quantity"] * $val["Price"] * $RUR_rate );
			$amount += (float) str_replace(",","",$order_content[$key]["Price_x_Quantity"]);
		}

		$shipping_rate = $order["shipping_cost"]*$RUR_rate;

		$order["discount_value"] = round((float)$order["order_discount"] * $amount)/100;

		$smarty->hassign( "order_discount", $order["order_discount"] );
		$smarty->hassign( "order_discount_value", _my_formatPrice($order["discount_value"]) );

		$amount += $shipping_rate; //+стоимость доставки

		$smarty->hassign( "order_content", $order_content );
		$smarty->hassign( "order_content_items_count", count($order_content) + 1 );
		$smarty->hassign( "order_subtotal", _my_formatPrice($amount) );

		if ($nds_rate <= 0) //показать НДС
		{
			$smarty->hassign( "order_tax_amount", "нет" );
			$smarty->hassign( "order_tax_amount_string", "нет" );
		}
		else
		{
			//налог не расчитывается на стоимость доставки
			//если вы хотите, чтобы налог расчитывался и на стоимость доставки замените ниже
			// '($amount-$shipping_rate)' на '$amount'

			if (!$nds_included) //налог включен
			{
				$tax_amount = round ( ($amount-$shipping_rate-$order["discount_value"]) * $nds_rate ) / 100;

				$amount += $tax_amount;
			}
			else //прибавить налог
			{
				$tax_amount = round ( 100 * ($amount-$shipping_rate-$order["discount_value"]) * $nds_rate / ($nds_rate+100) ) / 100;
			}
			$smarty->hassign( "order_tax_amount", _my_formatPrice($tax_amount) );
			$smarty->hassign( "order_tax_amount_string", create_string_representation_of_a_number($tax_amount) );

		}

		$smarty->hassign( "order_total", _my_formatPrice($order["order_amount"]*$RUR_rate) ); //$amount
		$smarty->hassign( "order_total_string", create_string_representation_of_a_number($order["order_amount"]*$RUR_rate) );

		//доставка
		if ($shipping_rate > 0)
		{
			$smarty->hassign( "shipping_type", $order["shipping_type"] );
			$smarty->hassign( "shipping_rate", _my_formatPrice($shipping_rate) );
		}
	}
	else
	{
		die ("Заказ не найден в базе данных");
	}

	$smarty->assign("shopping_cart_url",""); //путь к файлу логотипа 
?>
