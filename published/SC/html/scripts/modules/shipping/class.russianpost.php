<?php
/**
 * @connect_module_class_name RussianPost
 *
 */
define('RUSSIANPOST_ZONES_TBL', DBTABLE_PREFIX.'rpost_zones');
 
class RussianPost extends ShippingRateCalculator{

	var $language = 'rus';
	var $default_logo = 'http://www.webasyst.net/collections/design/shipping-icons/russianpost.gif';
	
	/*
	Parent methods redifinition
	*/

	function allow_shipping_to_address($_Address){

		if((int) $_Address["countryID"]!=$this->_getSettingValue('CONF_RUSSIANPOST_COUNTRY'))return 0;
		if(!(int)$_Address["zoneID"])return 0;
		if(!ZoneBelongsToCountry($_Address["zoneID"], $_Address["countryID"]))return 0;
		
		return 1;
	}

	function calculate_shipping_rate($_Order, $_Address, $_ServID = 0){

		$sql = '
			SELECT zoneNumber FROM '.RUSSIANPOST_ZONES_TBL.' 
			WHERE module_id="'.$this->ModuleConfigID.'" AND countryID="'.$_Address['countryID'].'" AND zoneID="'.$_Address['zoneID'].'"
		';
		@list($ZoneNumber) = db_fetch_row(db_query($sql));
		if(!isset($ZoneNumber) || !intval($ZoneNumber) || $ZoneNumber>5)return -1;
		
		$Weight = $this->_getOrderWeight($_Order);
		$Weight = $this->_convertMeasurement($Weight, CONF_WEIGHT_UNIT, 'KGS');
		
		if($Weight>$this->_getSettingValue('CONF_RUSSIANPOST_MAX_WEIGHT'))return -1;

		$GroundCost 	= $this->_getSettingValue('CONF_RUSSIANPOST_HALFCOST_'.$ZoneNumber) + $this->_getSettingValue('CONF_RUSSIANPOST_OVERHALFCOST_'.$ZoneNumber)*ceil((($Weight<0.5?0.5:$Weight)-0.5)/0.5);
		
		$AirCost 		= $GroundCost+$this->_getSettingValue('CONF_RUSSIANPOST_AIR');
		
		if ( $Weight>$this->_getSettingValue('CONF_RUSSIANPOST_DIFFICULT_WEIGHT') || $this->_getSettingValue('CONF_RUSSIANPOST_CAUTION') ){
		
			$GroundCost *= 1.3;
			$AirCost *= 1.3;
		}
		
		$OrderAmount = $this->_convertCurrency($_Order['order_amount'], 0, $this->_getSettingValue('CONF_RUSSIANPOST_CURRENCY'));
		
		$GroundCost += $OrderAmount*($this->_getSettingValue('CONF_RUSSIANPOST_COMMISION')/100);
		$AirCost += $OrderAmount*($this->_getSettingValue('CONF_RUSSIANPOST_COMMISION')/100);

		$Rates = array();
		
		$Rates[] = array(
			'name' => 'Наземный транспорт',
			'id' => 1,
			'rate' => $this->_convertCurrency($GroundCost, $this->_getSettingValue('CONF_RUSSIANPOST_CURRENCY'), 0),
			);
		$Rates[] = array(
			'name' => '"Авиа"',
			'id' => 2,
			'rate' => $this->_convertCurrency($AirCost, $this->_getSettingValue('CONF_RUSSIANPOST_CURRENCY'), 0),
			);
			
		$is_free_shipping = true;
		foreach ($_Order['orderContent']['cart_content'] as $product) {
			if (!$product['free_shipping']) {
				$is_free_shipping = false;
				break;
			}
		}
		if ($is_free_shipping) {
			return array();
		}
		elseif($_ServID != 1 && $_ServID !=2){
			return $Rates;
		}else{
		
			return array($Rates[$_ServID-1]);
		}
	}
	
	/*
	 Abstract methods redifinition
	 */
	function _InitVars(){
		
		parent::_initVars();
		$this->connected_printforms[] = 'russianpost';
		$this->title = 'Почта России';
		$this->description = 'Расчет стоимости доставки по алгоритму, опубликованному на сайте Почты России (отправление посылок).<br>Более подробная информация - в Руководстве Пользователя Shop-Script';
		$this->sort_order = 0;
		$this->Settings = array(
			'CONF_RUSSIANPOST_CURRENCY',
			'CONF_RUSSIANPOST_COUNTRY',
			'CONF_RUSSIANPOST_ZONES',
			'CONF_RUSSIANPOST_AIR',
			'CONF_RUSSIANPOST_CAUTION',
			'CONF_RUSSIANPOST_MAX_WEIGHT',
			'CONF_RUSSIANPOST_DIFFICULT_WEIGHT',
			'CONF_RUSSIANPOST_COMMISION',
			'CONF_RUSSIANPOST_HALFCOST_1',
			'CONF_RUSSIANPOST_HALFCOST_2',
			'CONF_RUSSIANPOST_HALFCOST_3',
			'CONF_RUSSIANPOST_HALFCOST_4',
			'CONF_RUSSIANPOST_HALFCOST_5',
			'CONF_RUSSIANPOST_OVERHALFCOST_1',
			'CONF_RUSSIANPOST_OVERHALFCOST_2',
			'CONF_RUSSIANPOST_OVERHALFCOST_3',
			'CONF_RUSSIANPOST_OVERHALFCOST_4',
			'CONF_RUSSIANPOST_OVERHALFCOST_5',
			);
	}
	
	function _initSettingFields(){
		
		$this->SettingsFields['CONF_RUSSIANPOST_CURRENCY'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> 'Валюта - Рубли', 
			'settings_description' 	=> 'Выберите валюту Вашего магазина, которая представляет собой рубли. Это необходимо для корректного пересчета стоимости доставки в другие валюты.', 
			'settings_html_function' 	=> 'setting_CURRENCY_SELECT(', 
			'sort_order' 			=> 10,
		);
		$this->SettingsFields['CONF_RUSSIANPOST_COUNTRY'] = array(
			'settings_value' 		=> CONF_COUNTRY, 
			'settings_title' 			=> 'Страна - Россия', 
			'settings_description' 	=> 'Пожалуйста, выбрите страну, в для которой Вы хотите настроить модуль Почты России. Данный модуль будет работать только для выбранной страны.', 
			'settings_html_function' 	=> 'setting_COUNTRY_SELECT(true,', 
			'sort_order' 			=> 20,
		);
		$this->SettingsFields['CONF_RUSSIANPOST_ZONES'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> 'Распределите области, определенные в Вашем магазине, по тарифным поясам', 
			'settings_description' 	=> '', 
			'settings_html_function' 	=> 'RussianPost::settingZones('.$this->ModuleConfigID.', "'.$this->_getSettingRealName('CONF_RUSSIANPOST_COUNTRY').'")', 
			'sort_order' 			=> 30,
		);
		$this->SettingsFields['CONF_RUSSIANPOST_AIR'] = array(
			'settings_value' 		=> '93.00', 
			'settings_title' 			=> 'Надбавка за отправление \'Авиа\'', 
			'settings_description' 	=> 'Укажите стоимость в рублях', 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 40,
		);
		$this->SettingsFields['CONF_RUSSIANPOST_CAUTION'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> 'Все посылки отправляются с отметкой "Осторожно"', 
			'settings_description' 	=> '', 
			'settings_html_function' 	=> 'setting_CHECK_BOX(', 
			'sort_order' 			=> 50,
		);
		$this->SettingsFields['CONF_RUSSIANPOST_MAX_WEIGHT'] = array(
			'settings_value' 		=> '20', 
			'settings_title' 			=> 'Максимальный вес отправления', 
			'settings_description' 	=> 'Укажите вес в килограммах', 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 60,
		);
		$this->SettingsFields['CONF_RUSSIANPOST_DIFFICULT_WEIGHT'] = array(
			'settings_value' 		=> '10', 
			'settings_title' 			=> 'Вес усложненной тарификации', 
			'settings_description' 	=> 'Укажите вес усложненной тарификации в килограммах (вес, начиная с которого к стоимости доставки посылки прибавляется 30%)', 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 70,
		);
		$this->SettingsFields['CONF_RUSSIANPOST_COMMISION'] = array(
			'settings_value' 		=> '3', 
			'settings_title' 			=> 'Плата за сумму объявленной ценности посылки', 
			'settings_description' 	=> 'В процентах. Например, укажите 3%, если с каждого рубля взимается 3 копейки.', 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 80,
		);
		
		$hCosts = array(53.45, 58.90, 77.40, 103.55, 116.65);
		for($_t = 1; $_t<=5; $_t++){
		
			$this->SettingsFields['CONF_RUSSIANPOST_HALFCOST_'.$_t] = array(
				'settings_value' 		=> $hCosts[$_t-1], 
				'settings_title' 			=> 'Стоимость отправки посылки весом до 0.5 килограмм (включительно)', 
				'settings_description' 	=> 'Укажите стоимость в рублях', 
				'settings_html_function' 	=> 'RussianPost::settingHalfCosts(0,', 
				'sort_order' 			=> 90,
			);
		}
		
		$hCosts = array(4.40, 4.70, 5.90, 7.55, 8.30);
		for($_t = 1; $_t<=5; $_t++){
		
			$this->SettingsFields['CONF_RUSSIANPOST_OVERHALFCOST_'.$_t] = array(
				'settings_value' 		=> $hCosts[$_t-1], 
				'settings_title' 			=> 'Стоимость отправки каждых дополнительных 0.5 килограмм', 
				'settings_description' 	=> 'Укажите стоимость в рублях', 
				'settings_html_function' 	=> 'RussianPost::settingHalfCosts(1,', 
				'sort_order' 			=> 100,
			);
		}
		
		if(!db_table_exists(RUSSIANPOST_ZONES_TBL)){
			
			$sql = '
				CREATE TABLE '.RUSSIANPOST_ZONES_TBL.' 
				(module_id INT UNSIGNED NOT NULL, countryID INT, zoneID INT, zoneNumber INT DEFAULT 0)
			';
			db_query($sql);
		}
		
	}

	/*
	current object methods
	*/
	
	function settingHalfCosts($_ID, $_SettingID){
	
		static $CostCounters = array();
		static $CostHTML = array();

		if(!isset($CostCounters[$_ID]))$CostCounters[$_ID] = '';
		if(!isset($CostHTML[$_ID]))$CostHTML[$_ID] = '';
		$CostCounters[$_ID]++;
		$CostHTML[$_ID] .= '<tr><td>Пояс '.$CostCounters[$_ID].' </td><td>'.setting_TEXT_BOX(0, $_SettingID).'</td></tr>';
		if($CostCounters[$_ID]==5)return '<table>'.$CostHTML[$_ID].'</table>';
		else return -1;
	}
	
	function settingZones($_ModuleConfigID, $_CountryConstant){
	
		$CountryID = xEscapeSQLstring(constant($_CountryConstant));
		$_ModuleConfigID = xEscapeSQLstring($_ModuleConfigID);
		if(isset($_POST['ZoneNumbers'])){
		
			foreach($_POST['ZoneNumbers'] as $_ZoneID=>$_ZoneNumber){
			
				$_ZoneID = xEscapeSQLstring($_ZoneID);
				$_ZoneNumber = xEscapeSQLstring($_ZoneNumber);
				$sql = '
					SELECT 1 FROM '.RUSSIANPOST_ZONES_TBL.' 
					WHERE module_id="'.$_ModuleConfigID.'" AND countryID="'.$CountryID.'" AND zoneID="'.$_ZoneID.'"
				';
				list($isExists) = db_fetch_row(db_query($sql));
				if($isExists){
				
					$sql = '
						UPDATE '.RUSSIANPOST_ZONES_TBL.' SET zoneNumber="'.$_ZoneNumber.'"
						WHERE module_id="'.$_ModuleConfigID.'" AND countryID="'.$CountryID.'" AND zoneID="'.$_ZoneID.'"
					';
				}else{
				
					$sql = '
						INSERT '.RUSSIANPOST_ZONES_TBL.'
						(module_id, countryID, zoneID, zoneNumber)
						VALUES("'.$_ModuleConfigID.'","'.$CountryID.'", "'.$_ZoneID.'", "'.$_ZoneNumber.'")
					';
				}
				db_query($sql);
			}
		}
		
		$sql = '
			SELECT zoneID, zoneNumber FROM '.RUSSIANPOST_ZONES_TBL.' 
			WHERE module_id="'.$_ModuleConfigID.'" AND countryID="'.$CountryID.'"
		';
		$Result = db_query($sql);
		$ZoneNumbers = array();
		while($_Row = db_fetch_row($Result)){
		
			$ZoneNumbers[$_Row['zoneID']] = $_Row['zoneNumber'];
		}
		
		$Zones = znGetZones($CountryID);
		$ZoneHTML = array();
		
		foreach ($Zones as $_Zone){
			
			if(!isset($ZoneNumbers[$_Zone['zoneID']]))$ZoneNumbers[$_Zone['zoneID']] = '';
			$ZoneHTML[] = '<tr><td>'.$_Zone['zone_name'].'</td><td>
			<select name="ZoneNumbers['.$_Zone['zoneID'].']">
				<option value="0">***пояс не выбран***</option>
				<option value="1"'.($ZoneNumbers[$_Zone['zoneID']]==1?' selected="selected"':'').'>Пояс 1</option>
				<option value="2"'.($ZoneNumbers[$_Zone['zoneID']]==2?' selected="selected"':'').'>Пояс 2</option>
				<option value="3"'.($ZoneNumbers[$_Zone['zoneID']]==3?' selected="selected"':'').'>Пояс 3</option>
				<option value="4"'.($ZoneNumbers[$_Zone['zoneID']]==4?' selected="selected"':'').'>Пояс 4</option>
				<option value="5"'.($ZoneNumbers[$_Zone['zoneID']]==5?' selected="selected"':'').'>Пояс 5</option>
				</select></td></tr>';
		}
		return (count($Zones)?'<table>'.implode('', $ZoneHTML).'</table>':'Не определено ни одной области. Для работы модуля необходимо определить хотя бы одну область.');
	}
}
?>