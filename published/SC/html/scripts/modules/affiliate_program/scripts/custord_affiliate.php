<?php
	if (CONF_BACKEND_SAFEMODE && isset($_POST['fACTION'])) //this action is forbidden when SAFE MODE is ON
	{
		Redirect( isset($_POST['fREDIRECT'])?set_query('&safemode=yes', $_POST['fREDIRECT']):set_query('&safemode=yes') );
	}

		include_once(DIR_FUNC.'/affiliate_functions.php');
		
		$sub_page = isset($_GET['sub_page'])?$_GET['sub_page']:'';
		
		$smarty->assign('CurrencyISO3', currGetAllCurrencies());
		
		$error_date_format = false;
		
		if(isset($_POST['fACTION'])){
			
			switch ($_POST['fACTION']){
				case 'ENABLE_AFFILIATE':
					$AffDiv = DivisionModule::getDivisionByUnicKey('affiliate_program');
					$AffDiv->setEnabled(!CONF_AFFILIATE_PROGRAM_ENABLED);
					$AffDiv->save();
					
					settingCallHtmlFunction( 'CONF_AFFILIATE_PROGRAM_ENABLED' );
					Redirect($_POST['fREDIRECT']);
					break;
				case 'SAVE_SETTINGS':
					$_POST['settingCONF_AFFILIATE_AMOUNT_PERCENT'] = floatval($_POST['settingCONF_AFFILIATE_AMOUNT_PERCENT']);
					if(!($_POST['settingCONF_AFFILIATE_AMOUNT_PERCENT']>=0 && $_POST['settingCONF_AFFILIATE_AMOUNT_PERCENT']<=100)){
						
						$smarty->assign('ErrorPercent', true);
						unset($_POST['save']);
						break;
					}
					settingCallHtmlFunction( 'CONF_AFFILIATE_EMAIL_NEW_COMMISSION' );
					settingCallHtmlFunction( 'CONF_AFFILIATE_EMAIL_NEW_PAYMENT' );
					settingCallHtmlFunction( 'CONF_AFFILIATE_AMOUNT_PERCENT' );
					Redirect($_POST['fREDIRECT']);
					break;
				case 'NEW_PAYMENT':
					#check date
					if(!Time::isValidSatandartTime($_POST['NEW_PAYMENT']['xDate'])){
					
						$smarty->assign('NEW_PAYMENT', $_POST['NEW_PAYMENT']);	
						$smarty->assign('error_new_payment', translate("affp_msg_error_date_format"));
						break;
					}else {
						
						$xDate = Time::dateTime($_POST['NEW_PAYMENT']['xDate']);
					}
					
					#check user
					$p_customerID = regGetIdByLogin($_POST['NEW_PAYMENT']['customerID']);
					if(!$p_customerID || !trim($_POST['NEW_PAYMENT']['customerID'])){
						
						$smarty->assign('error_new_payment', translate("err_input_login"));
						$smarty->assign('NEW_PAYMENT', $_POST['NEW_PAYMENT']);
						break;
					}
					$_POST['NEW_PAYMENT']['xDate'] 			= $xDate;
					$_POST['NEW_PAYMENT']['customerID'] 	= $p_customerID;
					affp_addPayment($_POST['NEW_PAYMENT']);
					
					Redirect(set_query('new_pay=ok', $_POST['fREDIRECT']));
					break;
				case 'DELETE_PAYMENT':
					if(!isset($_POST['PAYMENT']['pID']))break;
					affp_deletePayment($_POST['PAYMENT']['pID']);
					Redirect(set_query('delete_pay=ok', $_POST['fREDIRECT']));
					break;
				case 'SAVE_PAYMENT':
					#check date
					if(!Time::isValidSatandartTime($_POST['PAYMENT']['xDate'])){
					
						$error_message = translate("affp_msg_error_date_format");
						break;
					}else 
						$xDate = Time::date($_POST['PAYMENT']['xDate']);
					
					#check user
					if(!regGetIdByLogin($_POST['PAYMENT']['customerLogin']) || !trim($_POST['PAYMENT']['customerLogin'])){
						
						$error_message = translate("err_input_login");
						break;
					}else {
						
						$_POST['PAYMENT']['customerID'] = regGetIdByLogin($_POST['PAYMENT']['customerLogin']);
						unset($_POST['PAYMENT']['customerLogin']);
					}
					
					$_POST['PAYMENT']['Amount'] = isset($_POST['PAYMENT']['Amount'])?round($_POST['PAYMENT']['Amount'],2):0;
					
					$_POST['PAYMENT']['xDate'] = $xDate;
					affp_savePayment($_POST['PAYMENT']);
					print '
						<script language="javascript" type="text/javascript">
						<!--
						window.opener.document.location.href = window.opener.reloadURL;
						window.opener.focus();
						window.close();
						//-->
						</script>
						';
					exit(1);
					break;
				case 'NEW_COMMISSION':
					#check date
					if(!Time::isValidSatandartTime($_POST['NEW_COMMISSION']['xDate'])){
					
						$smarty->assign('NEW_COMMISSION', $_POST['NEW_COMMISSION']);	
						$smarty->assign('error_new_commission', translate("affp_msg_error_date_format"));
						break;
					}else {
						
						$xDateTime = Time::dateTime($_POST['NEW_COMMISSION']['xDate']);
					}
					
					#check user
					if(!regGetIdByLogin($_POST['NEW_COMMISSION']['customerLogin']) || !trim($_POST['NEW_COMMISSION']['customerLogin'])){
						
						$smarty->assign('NEW_COMMISSION', $_POST['NEW_COMMISSION']);	
						$smarty->assign('error_new_commission', translate("err_input_login"));
						break;
					}else {
						
						$_POST['NEW_COMMISSION']['customerID'] = regGetIdByLogin($_POST['NEW_COMMISSION']['customerLogin']);
						$tLogin = $_POST['NEW_COMMISSION']['customerLogin'];
						unset($_POST['NEW_COMMISSION']['customerLogin']);
					}
					
					$_POST['NEW_COMMISSION']['Amount'] = isset($_POST['NEW_COMMISSION']['Amount'])?sprintf("%.2f", $_POST['NEW_COMMISSION']['Amount']):0;
					$_POST['NEW_COMMISSION']['xDateTime'] = $xDateTime;
					unset($_POST['NEW_COMMISSION']['xDate']);

					#email to customer
					$t = '';
					$Email = '';
					$FirstName = '';
					regGetContactInfo($tLogin, $t, $Email, $FirstName, $t, $t, $t);
					
					xMailTxt($Email, translate("affp_new_commission"), 'customer.affiliate.commission_notifi.txt', 
						array(
							'customer_firstname' => $FirstName,
							'_AFFP_MAIL_NEW_COMMISSION' => str_replace('{MONEY}', $_POST['NEW_COMMISSION']['Amount'].' '.$_POST['NEW_COMMISSION']['CurrencyISO3'],translate("affp_mail_new_commission"))
							), true);
					
					affp_addCommission($_POST['NEW_COMMISSION']);
					
					Redirect(set_query('new_commission=ok', $_POST['fREDIRECT']));
					break;
				case 'DELETE_COMMISSION':
					if(!isset($_POST['COMMISSION']['cID']))break;
					affp_deleteCommission($_POST['COMMISSION']['cID']);
					Redirect(set_query('delete_commission=ok', $_POST['fREDIRECT']));
					break;
				case 'SAVE_COMMISSION':
					#check date
					if(!Time::isValidSatandartTime($_POST['COMMISSION']['xDateTime'])){
					
						$error_message = translate("affp_msg_error_date_format");
						break;
					}else 
						$xDateTime = Time::dateTime(Time::timeToServerTime(Time::timeStamp($_POST['COMMISSION']['xDateTime'])));
					
					#check user
					if(!regGetIdByLogin($_POST['COMMISSION']['customerLogin'])||!trim($_POST['COMMISSION']['customerLogin'])){
						
						$error_message = translate("err_input_login");
						break;
					}else {
						
						$_POST['COMMISSION']['customerID'] = regGetIdByLogin($_POST['COMMISSION']['customerLogin']);
						unset($_POST['COMMISSION']['customerLogin']);
					}
					
					$_POST['COMMISSION']['Amount'] = isset($_POST['COMMISSION']['Amount'])?round($_POST['COMMISSION']['Amount'],2):0;
					
					$_POST['COMMISSION']['xDateTime'] = $xDateTime;
					affp_saveCommission($_POST['COMMISSION']);
					print '
						<script language="javascript" type="text/javascript">
						<!--
						window.opener.document.location.href = window.opener.reloadURL;
						window.opener.focus();
						window.close();
						//-->
						</script>
						';
					exit(1);
					break;
			}
		}
		switch ($sub_page) {
			case 'edit_payment':
				#this part for edit payment
				if(isset($error_message)){
				
					$smarty->assign('Payment', $_POST['PAYMENT']);	
					$smarty->assign('error_message', $error_message);	
				}else {
					
					$Payment = affp_getPayments('', $_GET['pID']);
					$Payment[0]['xDate'] = $Payment[0]['xDate'];
					$Payment[0]['customerLogin'] = regGetLoginById($Payment[0]['customerID']);
					
					$smarty->hassign('Payment', ($Payment[0]));
				}
				
				$smarty->display("backend/custord_edit_payment.tpl.html");
				exit(1);
				break;
		
			case 'edit_commission':
				#this part for edit commission
				if(isset($error_message)){
				
					$smarty->assign('Commission', $_POST['COMMISSION']);	
					$smarty->assign('error_message', $error_message);	
				}else {
					
					$Commission = affp_getCommissions('', $_GET['cID']);
					$Commission[0]['xDateTime'] = $Commission[0]['xDateTime'];
					$Commission[0]['customerLogin'] = regGetLoginById($Commission[0]['customerID']);
					
					$smarty->hassign('Commission', ($Commission[0]));
				}
				
				$smarty->display("backend/custord_edit_commission.tpl.html");
				exit(1);
				break;
		
			default:
				#this part will display all tables
				/**
				 * check from-date and till-date
				 */
				if (isset($_POST['from']))$_GET['from'] = $_POST['from'];
				if (isset($_POST['till']))$_GET['till'] = $_POST['till'];
				if (!isset($_GET['from']))$_GET['from'] = '';
				else $_GET['from'] = (rawurldecode($_GET['from']));
				if (!isset($_GET['till']))$_GET['till']='';
				else $_GET['till'] = (rawurldecode($_GET['till']));
				
				$show_tables = false;
				$CurrDate = Time::standartTime(null,false);
				
				if ($_GET['from']){
					
					if(Time::isValidSatandartTime($_GET['from']))
						$show_tables = true;
					else 
						$error_date_format = true;
				}elseif(!isset($_POST['from'])){
					$_GET['from'] = Time::standartTime(date("Y-m-01"),false);
				}else {
					$error_date_format = true;
				}
				if ($_GET['till']){
					
					if(Time::isValidSatandartTime($_GET['till']))
						$show_tables = ($show_tables && true);
					else {
						
						$show_tables = false;
						$error_date_format = true;
					}
				}elseif(!isset($_POST['till'])) {
					$_GET['till'] = $CurrDate;
					$show_tables = false;
				}else {
					
					$show_tables = false;
					$error_date_format = true;
				}
				
				$XREQUEST_URI 	= set_query('&edCustomerID=&safemode=&new_commission=&delete_pay=&delete_commission=&new_pay=&till='.rawurlencode($_GET['till']).'&from='.rawurlencode($_GET['from']));
				
				if(isset($show_tables)){
					
					#get payments
					if(!isset($_GET['OrderField']))
						$_GET['OrderField'] = 'pID';
					if(!isset($_GET['OrderDiv']))
						$_GET['OrderDiv'] = 'asc';
					$Payments = affp_getPayments(
						'', '', 
						Time::dateTime(Time::timeToServerTime(Time::timeStamp($_GET['from'])),true), 
						Time::dateTime(Time::timeToServerTime(Time::timeStamp($_GET['till'])+24*3600),true),
						$_GET['OrderField'].' '.$_GET['OrderDiv']);

					#get commissions
					if(!isset($_GET['OrderFieldC']))
						$_GET['OrderFieldC'] = 'cID';
					if(!isset($_GET['OrderDivC']))
						$_GET['OrderDivC'] = 'asc';
					$Commissions = affp_getCommissions('', '', 
						Time::dateTime(Time::timeToServerTime(Time::timeStamp($_GET['from']))), 
						Time::dateTime(Time::timeToServerTime(Time::timeStamp($_GET['till'])+24*3600)),
						$_GET['OrderFieldC'].' '.$_GET['OrderDivC']);
						
					$smarty->hassign('Payments', $Payments);
					$smarty->assign('PaymentsNumber', count($Payments));
					$smarty->hassign('Commissions', $Commissions);
					$smarty->assign('CommissionsNumber', count($Commissions));
					
					$gridEntry = new Grid();
					
					$gridEntry->get_sort_name = 'OrderFieldC';
					$gridEntry->get_direction_name = 'OrderDivC';
					$gridEntry->smarty_headers_name = 'GridHeadersC';
					
					$gridEntry->registerHeader('ID', 'cID', false, 'asc');
					$gridEntry->registerHeader('blog_postdate', 'xDateTime', false, 'desc');
					$gridEntry->registerHeader('ordr_customer');
					$gridEntry->registerHeader('str_description');
					$gridEntry->registerHeader('str_total', 'Amount', false, 'desc');
					
					$gridEntry->prepare_headers();
					
					$gridEntry = new Grid();
					
					$gridEntry->get_sort_name = 'OrderField';
					$gridEntry->get_direction_name = 'OrderDiv';
					$gridEntry->smarty_headers_name = 'GridHeadersP';
					
					$gridEntry->registerHeader('ID', 'pID', false, 'asc');
					$gridEntry->registerHeader('blog_postdate', 'xDateTime', false, 'desc');
					$gridEntry->registerHeader('ordr_customer');
					$gridEntry->registerHeader('str_description');
					$gridEntry->registerHeader('str_total', 'Amount', false, 'desc');
					
					$gridEntry->prepare_headers();
				}
				
				if(isset($_GET['new_pay']))$smarty->assign('newPayStatus', '1');
				if(isset($_GET['new_commission']))$smarty->assign('newCommissionStatus', '1');
				if(isset($_GET['delete_pay']))$smarty->assign('delete_payment', 1);
				if(isset($_GET['delete_commission']))$smarty->assign('delete_commission', 1);
				$smarty->assign('CurrDate', $CurrDate);
				$smarty->assign('show_tables', $show_tables);
				$smarty->hassign('from', $_GET['from']);
				$smarty->hassign('till', $_GET['till']);
				$smarty->assign('Error_DateFormat', $error_date_format);
				$smarty->assign('REQUEST_URI', $XREQUEST_URI);
				$smarty->assign('CONF_AFFILIATE_PROGRAM_ENABLED', CONF_AFFILIATE_PROGRAM_ENABLED);
				$smarty->assign('htmlEmailNewCommission', settingCallHtmlFunction( 'CONF_AFFILIATE_EMAIL_NEW_COMMISSION' ));
				$smarty->assign('htmlEmailNewPayment', settingCallHtmlFunction( 'CONF_AFFILIATE_EMAIL_NEW_PAYMENT' ));
				$smarty->assign('htmlEnabledSettings', settingCallHtmlFunction( 'CONF_AFFILIATE_PROGRAM_ENABLED' ));
				$smarty->assign('htmlAmountPercent', settingCallHtmlFunction( 'CONF_AFFILIATE_AMOUNT_PERCENT' ));
				$smarty->assign("admin_sub_dpt", "custord_affiliate.tpl.html");
				
				if(!isset($_POST['NEW_PAYMENT']))
					$smarty->assign('NEW_PAYMENT', array('xDate'=>$CurrDate));	
				if(!isset($_POST['NEW_COMMISSION']))
					$smarty->assign('NEW_COMMISSION', array('xDate'=>$CurrDate));	
				if(isset($_GET['edCustomerID']))
					$smarty->assign('edCustomerLogin', regGetLoginById(intval($_GET['edCustomerID'])));

				break;
		}
?>