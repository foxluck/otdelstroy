<?php
	if (CONF_BACKEND_SAFEMODE && isset($_POST['fACTION'])) //this action is forbidden when SAFE MODE is ON
	{
		Redirect( isset($_POST['fREDIRECT'])?set_query('&safemode=yes', $_POST['fREDIRECT']):set_query('&safemode=yes') );
	}
	
$smarty = &Core::getSmarty();
	
$UsersObj = &ModulesFabric::getModuleObjByKey('Users');

$UsersObj->_initUserInfo();

$customerID = isset($_GET['userID'])?$_GET['userID']:'';
		
		$sub_page = isset($_GET['sub_page'])?$_GET['sub_page']:'';
		
		$smarty->assign('CurrencyISO3', currGetAllCurrencies());
		
		$error_date_format = false;
		
		if(isset($_POST['fACTION'])){
			
			switch ($_POST['fACTION']){
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
					if(!regGetIdByLogin($_POST['PAYMENT']['customerLogin'])){
						
						$error_message = translate("err_input_login");
						break;
					}else {
						
						$_POST['PAYMENT']['customerID'] = regGetIdByLogin($_POST['PAYMENT']['customerLogin']);
						unset($_POST['PAYMENT']['customerLogin']);
					}
					
					$_POST['PAYMENT']['Amount'] = isset($_POST['PAYMENT']['Amount'])?round($_POST['PAYMENT']['Amount'], 2):0;
					
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
						$xDateTime = Time::dateTime($_POST['COMMISSION']['xDateTime'],true);
					
					#check user
					if(!regGetIdByLogin($_POST['COMMISSION']['customerLogin'])){
						
						$error_message = translate("err_input_login");
						break;
					}else {
						
						$_POST['COMMISSION']['customerID'] = regGetIdByLogin($_POST['COMMISSION']['customerLogin']);
						unset($_POST['COMMISSION']['customerLogin']);
					}
					
					$_POST['COMMISSION']['Amount'] = isset($_POST['COMMISSION']['Amount'])?round($_POST['COMMISSION']['Amount'], 2):0;
					
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
				case 'CANCEL_CUSTOMER':
					affp_cancelRecruitedCustomer($_POST['CUSTOMER']['customerID']);
					Redirect($_POST['fREDIRECT']);
					break;
			}
		}
			
		switch ($sub_page) {
			case 'edit_payment':
				#this part for edit payment
				if(isset($error_message)){
				
					$smarty->hassign('Payment', $_POST['PAYMENT']);	
					$smarty->assign('error_message', $error_message);	
				}else {
					
					$Payment = affp_getPayments('', $_GET['pID']);
					$Payment[0]['xDate'] = $Payment[0]['xDate'];
					$Payment[0]['customerLogin'] = regGetLoginById($Payment[0]['customerID']);
					
					$smarty->hassign('Payment', $Payment[0]);
				}
				
				$smarty->display("backend/custord_edit_payment.tpl.html");
				exit(1);
				break;
		
			case 'edit_commission':
				#this part for edit commission
				if(isset($error_message)){
				
					$smarty->hassign('Commission', $_POST['COMMISSION']);	
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
				if (isset($_POST['till']))$_GET['till'] = ($_POST['till']);
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
				
				$XREQUEST_URI 	= set_query('safemode=&new_commission=&delete_pay=&delete_commission=&new_pay=&till='.rawurlencode($_GET['till']).'&from='.rawurlencode($_GET['from']));
				
				if(isset($show_tables)){
					
					#get payments
					if(!isset($_GET['OrderField']))
						$_GET['OrderField'] = 'pID';
					if(!isset($_GET['OrderDiv']))
						$_GET['OrderDiv'] = 'ASC';
					$Payments = affp_getPayments(
						$customerID, 
						'', 
						Time::date($_GET['from']), 
						Time::date($_GET['till']),
						$_GET['OrderField'].' '.$_GET['OrderDiv']);

					#get commissions
					if(!isset($_GET['OrderFieldC']))
						$_GET['OrderFieldC'] = 'cID';
					if(!isset($_GET['OrderDivC']))
						$_GET['OrderDivC'] = 'ASC';
					$Commissions = affp_getCommissions(
						$customerID, 
						'', 
						Time::dateTime(Time::timeToServerTime(Time::timeStamp($_GET['from']))), 
						Time::dateTime(Time::timeToServerTime(Time::timeStamp($_GET['till']))),
						$_GET['OrderFieldC'].' '.$_GET['OrderDivC']);
					
					$smarty->hassign('Payments', ($Payments));
					$smarty->assign('PaymentsNumber', count($Payments));
					$smarty->hassign('Commissions', ($Commissions));
					$smarty->assign('CommissionsNumber', count($Commissions));
				}
				
				$gridEntry = new Grid();
				
				$gridEntry->get_sort_name = 'OrderFieldC';
				$gridEntry->get_direction_name = 'OrderDivC';
				$gridEntry->smarty_headers_name = 'GridHeadersC';
				
				$gridEntry->registerHeader('ID', 'cID', false, 'asc');
				$gridEntry->registerHeader('blog_postdate', 'xDateTime', false, 'desc');
				$gridEntry->registerHeader('str_description');
				$gridEntry->registerHeader('str_total', 'Amount', false, 'desc');
				
				$gridEntry->prepare_headers();
				
				$gridEntry = new Grid();
				
				$gridEntry->get_sort_name = 'OrderField';
				$gridEntry->get_direction_name = 'OrderDiv';
				$gridEntry->smarty_headers_name = 'GridHeadersP';
				
				$gridEntry->registerHeader('ID', 'pID', false, 'asc');
				$gridEntry->registerHeader('blog_postdate', 'xDateTime', false, 'desc');
				$gridEntry->registerHeader('str_description');
				$gridEntry->registerHeader('str_total', 'Amount', false, 'desc');
				
				$gridEntry->prepare_headers();
				
				$RecruitedCustomers = affp_getRecruitedCustomers($customerID);
				$smarty->assign('RecruitedCustomersNumber', count($RecruitedCustomers));
				$smarty->assign('RecruitedCustomers', $RecruitedCustomers);
				if(isset($_GET['delete_pay']))$smarty->assign('delete_payment', 1);
				if(isset($_GET['delete_commission']))$smarty->assign('delete_commission', 1);
				$smarty->assign('CurrDate', $CurrDate);
				$smarty->hassign('from', ($_GET['from']));
				$smarty->hassign('till', ($_GET['till']));
				$smarty->assign('Error_DateFormat', $error_date_format);
				$smarty->assign('REQUEST_URI', $XREQUEST_URI);
				$smarty->assign('show_tables', $show_tables);
				$smarty->assign("UserInfoFile", "backend/custord_custlist_affiliate.tpl.html");
				$smarty->assign('edCustomerID', $customerID);

				break;
		}
?>