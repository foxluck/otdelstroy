<?php
class Currency extends Object {

	var $CID;
	var $currency_value;
	var $sort_order;
	var $currency_iso_3;
	var $Name;
	var $display_template;
	var $decimal_symbol;
	var $decimal_places;
	var $thousands_delimiter;

	function checkInfo(){
			
		$vars = $this->getVars();
			
		if(LanguagesManager::ml_isEmpty('Name', $vars)){
			return PEAR::raiseError(sprintf(translate('msg_field_required'), translate('ADMIN_CURRENCY_NAME')));
		}
			
		foreach ($vars as $var => $value){

			switch ($var){
				case 'currency_iso_3':
				case 'display_template':
					if(!$value){
						$field_names = array(
						'currency_iso_3' => translate("curr_iso3"),
						'display_template' => 'curr_output_format',
						);
						return PEAR::raiseError(sprintf(translate('msg_field_required'), translate($field_names[$var])));
					}
					break;
			}
		}
	}

	function loadFromArray($data){

		LanguagesManager::ml_fillFields(CURRENCY_TYPES_TABLE, $data);
		parent::loadFromArray($data, true);
			
		//$this->display_template = str_replace('&rouble;',
		//'<img src="http://www.artlebedev.ru/tools/technogrette/html/rouble/ruble.gif" class="ruble-img" />
		//<span class="ruble">руб.</span>',$this->display_template);
			
		//$this->display_template = str_replace('&rouble;','<span class="rur">p<span>уб.</span></span>',$this->display_template);
	}

	function loadByCID($CID){
			
		$Register = &Register::getInstance();
		$DBHandler = &$Register->get(VAR_DBHANDLER);
		/* @var $DBHandler DataBase */
			
		$DBRes = $DBHandler->ph_query('SELECT * FROM ?#CURRENCY_TYPES_TABLE WHERE CID=?', $CID);
		if(!$DBRes->getNumRows())return false;
			
		$this->loadFromArray($DBRes->fetchAssoc());
	}

	function save(){
			
		$Register = &Register::getInstance();
		$DBHandler = &$Register->get(VAR_DBHANDLER);
		/* @var $DBHandler DataBase */
			
		if($this->CID){

			$sql = '
					UPDATE ?#CURRENCY_TYPES_TABLE 
					SET 
						'.LanguagesManager::sql_prepareFieldUpdate('Name', $this->getVars()).',
						'.LanguagesManager::sql_prepareFieldUpdate('display_template', $this->getVars()).', 
						currency_value=?currency_value, sort_order=?sort_order,currency_iso_3=?currency_iso_3,
						decimal_symbol=?decimal_symbol, decimal_places=?decimal_places, thousands_delimiter="'.xEscapeSQLstring($this->thousands_delimiter).'"
					WHERE CID=?CID
				';
		}else{

			$name_inj = LanguagesManager::sql_prepareFieldInsert('Name', $this->getVars());
			$tpl_inj = LanguagesManager::sql_prepareFieldInsert('display_template', $this->getVars());
			$sql = "
					 INSERT ?#CURRENCY_TYPES_TABLE ({$name_inj['fields']}, {$tpl_inj['fields']}, currency_iso_3, currency_value, sort_order, decimal_symbol, decimal_places, thousands_delimiter) 
					 VALUES({$name_inj['values']}, {$tpl_inj['values']}, ?currency_iso_3,?currency_value,?sort_order, ?decimal_symbol, ?decimal_places, '".xEscapeSQLstring($this->thousands_delimiter)."')
				";
		}
		$DBRes = $DBHandler->ph_query($sql, $this->getVars());
		if(!$this->CID){
			$this->CID = $DBRes->getInsertID();
		}
	}

	function setDefault(){
			
		$mult = $this->currency_value;
			
		$Register = &Register::getInstance();
		$DBHandler = &$Register->get(VAR_DBHANDLER);
		/* @var $DBHandler DataBase */
			
		$r_dbq = array(
		CURRENCY_TYPES_TABLE => "UPDATE ?#CURRENCY_TYPES_TABLE SET currency_value = currency_value/{$mult}",
		PRODUCTS_TABLE => "UPDATE ?#PRODUCTS_TABLE SET Price = Price*{$mult}, list_price = list_price*{$mult}, shipping_freight = shipping_freight*{$mult}",
		ORDERS_TABLE => "UPDATE ?#ORDERS_TABLE SET currency_value = currency_value/{$mult}, order_amount = order_amount*{$mult}, shipping_cost = shipping_cost*{$mult}, order_discount = order_discount*{$mult}",
		ORDERED_CARTS_TABLE => "UPDATE ?#ORDERED_CARTS_TABLE SET Price = Price*{$mult}",
		ORDER_PRICE_DISCOUNT_TABLE => "UPDATE ?#ORDER_PRICE_DISCOUNT_TABLE SET price_range = price_range*{$mult}",
		PRODUCTS_OPTIONS_SET_TABLE => "UPDATE ?#PRODUCTS_OPTIONS_SET_TABLE SET price_surplus = price_surplus*{$mult}",
		//TAX_RATES_TABLE => "UPDATE ?#TAX_RATES_TABLE SET `value` = `value`*{$mult}",
		//TAX_RATES_ZONES_TABLE => "UPDATE ?#TAX_RATES_ZONES_TABLE SET `value` = `value`*{$mult}",
		//TAX_ZIP_TABLE => "UPDATE ?#TAX_ZIP_TABLE SET `value` = `value`*{$mult}",
		DBTABLE_PREFIX."_courier_rates__0" => "UPDATE ".DBTABLE_PREFIX."_courier_rates SET rate = rate*{$mult} WHERE isPercent<>1",
		DBTABLE_PREFIX."_courier_rates__1" => "UPDATE ".DBTABLE_PREFIX."_courier_rates SET orderAmount = orderAmount*{$mult}",
		DBTABLE_PREFIX."_courier_rates2__0" => "UPDATE ".DBTABLE_PREFIX."_courier_rates2 SET rate = rate*{$mult} WHERE isPercent<>1",
		//DBTABLE_PREFIX."_courier_rates2__1" => "UPDATE ".DBTABLE_PREFIX."_courier_rates2 SET orderAmount = orderAmount*{$mult}",
		DBTABLE_PREFIX."_module_payment_invoice_jur" => "UPDATE ".DBTABLE_PREFIX."_module_payment_invoice_jur SET RUR_rate = RUR_rate/{$mult}",
		DBTABLE_PREFIX."_module_shipping_bycountries_byzones_rates" => "UPDATE ".DBTABLE_PREFIX."_module_shipping_bycountries_byzones_rates SET shipping_rate = shipping_rate*{$mult}",
		DBTABLE_PREFIX."_module_shipping_bycountries_byzones_rates_percent" => "UPDATE ".DBTABLE_PREFIX."_module_shipping_bycountries_byzones_rates_percent SET shipping_rate = shipping_rate*{$mult}",
		TBL_DISCOUNT_COUPONS => "update ".TBL_DISCOUNT_COUPONS." set discount_absolute = discount_absolute*{$mult}"
		);
		foreach ($r_dbq as $table => $dbq){
			if(!db_table_exists(preg_replace('/__\d+/u', '', $table)))continue;
			$DBHandler->ph_query($dbq);
		}

		_setSettingOptionValue('CONF_DEFAULT_CURRENCY', $this->CID);
		$this->currency_value = 1;
		$this->save();
	}

	function getUnitsView($units){
			
		$conv_amount = $this->convertUnits($units);
		return $this->getView($conv_amount);
	}

	function getView($conv_amount){
		return str_replace('{value}', number_format ( $conv_amount, $this->decimal_places , $this->decimal_symbol, $this->thousands_delimiter=='_'?' ':$this->thousands_delimiter), $this->display_template);
	}

	function convertUnits($units, $dec_places = false){
			
		$amount = $this->currency_value*$units;
		if($dec_places)
		$amount = $this->round($amount);
		return $amount;
	}

	/**
	 * @return Currency
	 */
	static function getDefaultCurrencyInstance(){
			
		static $defaultCurrencyInstance;
		if(!is_object($defaultCurrencyInstance)){

			$defaultCurrencyInstance = new Currency();
			$defaultCurrencyInstance->loadByCID(CONF_DEFAULT_CURRENCY);
		}
			
		return $defaultCurrencyInstance;
	}

	/**
	 * @return Currency
	 */
	static function getSelectedCurrencyInstance(){
			
		static $selectedCurrencyInstance;
		if(!is_object($selectedCurrencyInstance)){

			$customerEntry = Customer::getAuthedInstance();
			$selectedCurrencyInstance = new Currency();
			if(Customer::is_inited_object($customerEntry)){
					
				$selectedCurrencyInstance->loadByCID($customerEntry->CID);
			}elseif(isset($_SESSION["current_currency"]) && $_SESSION["current_currency"]){
					
				$selectedCurrencyInstance->loadByCID($_SESSION["current_currency"]);
			}

			if(!$selectedCurrencyInstance->CID){
					
				$selectedCurrencyInstance = Currency::getDefaultCurrencyInstance();
			}

			if(!$selectedCurrencyInstance->CID){
					
				$selectedCurrencyInstance->loadFromArray(db_phquery_fetch(DBRFETCH_ASSOC, 'SELECT * FROM ?#CURRENCY_TYPES_TABLE LIMIT 1'));
			}
		}
			
		$_SESSION["current_currency"] = $selectedCurrencyInstance->CID;

		return $selectedCurrencyInstance;
	}

	function convertToUnits($conv_price, $dec_places = false){
			
		$units = $conv_price/$this->currency_value;
		if($dec_places)
		$units = $this->round($units);

		return $units;
	}

	function round($amount){
			
		return round($amount, $this->decimal_places);
	}

	function getJSCurrencyInstance(){
			
		$thousands_delimiter = $this->thousands_delimiter == '_'?'&nbsp;':$this->thousands_delimiter;
		$display_template = str_replace(array('\'', '\\'), array('\\\'', '\\\\'), $this->display_template);
		$decimal_places = str_replace(array('\'', '\\'), array('\\\'', '\\\\'), $this->decimal_places);
		$decimal_symbol = str_replace(array('\'', '\\'), array('\\\'', '\\\\'), $this->decimal_symbol);
		$thousands_delimiter = str_replace(array('\'', '\\'), array('\\\'', '\\\\'), $thousands_delimiter);

		$tpl = "
var defaultCurrency = {
	display_template: '{$display_template}',
	decimal_places: '{$decimal_places}',
	decimal_symbol: '{$decimal_symbol}',
	thousands_delimiter: '{$thousands_delimiter}',
	getView: function (price){return this.display_template.replace(/\{value\}/, number_format(price, this.decimal_places, this.decimal_symbol, this.thousands_delimiter));}
	};
";
		return $tpl;
	}

	static function number2string($n,$rod) //перевести число $n в строку. Число обязательно должно быть 0 < $n < 1000. $rod указывает на род суффикса (0 - женский, 1 - мужской; например, "рубль" - 1, "тысяча" - 0).
	{
		$n = round($n%1000);
		$a = floor($n / 100)*100;
		$b = floor(($n - $a) / 10)*10;
		$c = $n % 10;
		if($b==10){
			$b = $b+$c;
			$c = 0;
		}
		$s = "";
		switch($a){//сотни
			case 100: $s = "сто";			break;
			case 200: $s = "двести";		break;
			case 300: $s = "триста";		break;
			case 400: $s = "четыреста";	break;
			case 500: $s = "пятьсот";		break;
			case 600: $s = "шестьсот";	break;
			case 700: $s = "семьсот";		break;
			case 800: $s = "восемьсот";	break;
			case 900: $s = "девятьсот";	break;
		}
		$s .= ($s&&$b)?" ":'';
		switch($b){//десятки
			case 10: $s .= "десять";		break;
			case 11: $s .= "одиннадцать";	break;
			case 12: $s .= "двенадцать";	break;
			case 13: $s .= "тринадцать";	break;
			case 14: $s .= "четырнадцать";	break;
			case 15: $s .= "пятнадцать";	break;
			case 16: $s .= "шестнадцать";	break;
			case 17: $s .= "семнадцать";	break;
			case 18: $s .= "восемнадцать";	break;
			case 19: $s .= "девятнадцать";	break;
			case 20: $s .= "двадцать";		break;
			case 30: $s .= "тридцать";		break;
			case 40: $s .= "сорок";			break;
			case 50: $s .= "пятьдесят";		break;
			case 60: $s .= "шестьдесят";	break;
			case 70: $s .= "семьдесят";		break;
			case 80: $s .= "восемьдесят";	break;
			case 90: $s .= "девяносто";		break;
		}
		$s .= ($s&&$c)?" ":'';
		switch($c){//единицы
			case 1: 
				switch($rod){
					case 0:$s .= "одна";break;//ж.р. И.п.
					case 1:$s .= "один";break;//м.р. И.п. 
					case 2:$s .= "одну";break;//ж.р. Р.п.
					case 3:$s .= "один";break;//м.р. Р.п. 
				}
				break;
			case 2:
				switch($rod){
					case 0:$s .= "две";break;//ж.р. И.п.
					case 1:$s .= "два";break;//м.р. И.п.  
					case 2:$s .= "две";break;//ж.р. Р.п.
					case 3:$s .= "два";break;//м.р. Р.п.  
				}
				break;
			case 3: $s .= "три";		break;
			case 4: $s .= "четыре";		break;
			case 5: $s .= "пять";		break;
			case 6: $s .= "шесть";		break;
			case 7: $s .= "семь";		break;
			case 8: $s .= "восемь";		break;
			case 9: $s .= "девять";		break;
		}
		return $s;
	}

	/**
	 * создает строковое представление суммы. Например $n = 123.
	 * результат будет "Сто двадцать три рубля 00 копеек"
	 * 
	 * @param float $amount
	 * @return string
	 */
	static function stringView( $amount )
	{
		//разделить сумма на разряды: единицы, тысячи, миллионы, миллиарды (больше миллиардов не проверять :) )
		$n = round($amount,2);
		$billions = floor($n / 1000000000);
		$millions = floor( ($n-$billions*1000000000) / 1000000);
		$grands = floor( ($n-$billions*1000000000-$millions*1000000) / 1000);
		$roubles = floor( ($n-$billions*1000000000-$millions*1000000-$grands*1000) );//$n % 1000;

		
		//копейки
		$kop = round ( $n*100 - round( floor($n)*100 ) );
		//var_dump(array($n,$billions,$millions,$grands,$roubles,$kop));
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
			$s .= self::number2string($billions,1)." миллиард{$t} ";
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
			$s .= self::number2string($millions,1)." миллион{$t} ";
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
			$s .= self::number2string($grands,0)." тысяч{$t} ";
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
			$s .=  self::number2string($roubles,1)." рубл{$rub} ";
		}

		{
			$kp = "ек";
			$temp = $kop % 10;
			if (floor(($kop % 100)/10) != 1)
			{
				if ($temp == 1) $kp = "йка";
				else if ($temp >=2 && $temp <= 4) $kp = "йки";
			}

			$s .= "{$kop} копе{$kp}";
		}
		/*
		 //теперь сделать первую букву заглавной
		 if ($roubles>0 || $grands>0 || $millions>0 || $billions>0)
		 {
			$cnt=0; while(substr($s, $cnt, 1)==" ") $cnt++;
			$s = substr($s, $cnt, 1);
			$s[$cnt] = chr( ord($s[$cnt])- 32 );
			}
			*/
		return $s;
	}

}
?>