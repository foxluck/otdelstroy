<?php

class UGUsersPartnersAction extends UGViewAction
{
	protected $id;
	protected $partner_id;
	protected $info;

	public function __construct()
	{
        // Customer
		if (Env::isPost()) {
        	$this->partner_id = Env::Post('C_ID', Env::TYPE_BASE64, 0);
        } else {
        	$this->partner_id = Env::Get('C_ID', Env::TYPE_BASE64, 0);
        }

        // Yourself
        if (!$this->id) {
        	$this->id = User::getContactId();
        }
    	$this->info = Contact::getInfo($this->id);
		if (!$this->info) {
			throw new UserException(_s('Contact not found'), 'Contact with ID '.$this->id.' not found');
		}
		// Contact (partner)
		$contact_info = Contact::getInfo($this->partner_id);
		if(!$contact_info['C_LANGUAGE']) {
			$this->partner_language = strtoupper(Wbs::getDbkeyObj()->getLanguage());
		} else {
			$this->partner_language = strtoupper($contact_info['C_LANGUAGE']);
		}
		$this->partner_jobs = $this->getPartnerJobs();

        parent::__construct();
		
        if (Env::Post('act')) {
				
			switch (Env::Post('act')) {
        		case 'delete':
				
					if($res = $this->deleteResponse(Env::Post('id'))) {

//						$partner_jobs = $this->getPartnerJobs();
						$enc_jobs = array();
						foreach($this->partner_jobs as $key=>$row) {
							$enc_jobs[] = "$key: ['{$row['CPPJ_AVGVOTE']}', {$row['CPPJ_VOTEAMNT']}]";
						}
						$enc_jobs = '{'.join(', ', $enc_jobs).'}';
						exit($enc_jobs);
					} else {
						exit($res);
					}

				case 'edit':
					exit($this->editResponse(Env::Post('id'), Env::Post('content'), Env::Post('comment')));

				case 'save_portfolio':
//					exit($this->savePortfolio(Env::Post('id'), Env::Post('content')));
					$fields = explode('&', Env::Post('data'));
					foreach($fields as $f){
						$key_value = explode('=', $f);
						eval('$'.urldecode($key_value[0]).' = "'.str_replace('"', '\"', urldecode($key_value[1])).'";');
					}
					$CPP_ID = Env::Post('CPP_ID');
					
					if($CPP_ID && !empty($project[$CPP_ID])) {

						$this->savePortfolio($CPP_ID, $project[$CPP_ID]);
					}
					Url::go('/'.User::getAppId().'/?mod=users&C_ID='.base64_encode($this->partner_id).'&act=partners&tpl=portfolio');

					
				case 'delete_portfolio':
					exit($this->deletePortfolio(Env::Post('id')));

				case 'save_jobs':
				
					$fields = explode('&', Env::Post('data'));
					foreach($fields as $f){
						$key_value = explode('=', $f);
						eval('$'.urldecode($key_value[0]).' = "'.str_replace('"', '\"', urldecode($key_value[1])).'";');
					}
					
					if(!empty($new_jobs) && is_array($new_jobs)) {

						$jobs = $this->getAllJobs();
						foreach($jobs as $j) {
							$id = $j['CPJ_ID'];
							if(empty($new_jobs[$id])) {
								$job = array();
								$job['CPPJ_MIN'] = $job['CPPJ_MAX'] = 0;
							} else {
								$job = $new_jobs[$id];
								$job['CPPJ_MIN'] = preg_replace('/\s/', '', $job['CPPJ_MIN']);
								$job['CPPJ_MAX'] = preg_replace('/\s/', '', $job['CPPJ_MAX']);
							}
							$job['CPJ_ID'] = $id;
							$job['C_ID'] = $this->partner_id;

							if(empty($job['checked'])) {
								if(isset($this->partner_jobs[$id])) {
									$this->deleteJob($id);
								}
							} else {
								if(isset($this->partner_jobs[$id])) {
									$this->updateJob($job);
								} else {
									$this->addJob($job);
								}
							}
						}
						Url::go('/'.User::getAppId().'/?mod=users&C_ID='.base64_encode($this->partner_id).'&act=partners&tpl=jobs');
					}
					exit('empty jobs fields');

				case 'save_moderated':

					exit($this->saveModerated(Env::Post('value') == 'true'));

				case 'save_info':
				
					$fields = explode('&', Env::Post('data'));
					foreach($fields as $f){
						$key_value = explode('=', $f);
						eval('$'.urldecode($key_value[0]).' = "'.str_replace('"', '\"', urldecode($key_value[1])).'";');
					}
					if(empty($data) || !is_array($data)) {
						exit('Error: empty required field(s)');
					}
					if(!isset($data['CP_RECIEVE_NOTICE'])) {
						$data['CP_RECIEVE_NOTICE'] = 0;
					}
					
					$this->updateInfo($data);

					Url::go('/'.User::getAppId().'/?mod=users&C_ID='.base64_encode($this->partner_id).'&act=partners&tpl=info');
					break;

				case 'activity':
				
					$MTC_ID = Env::Post('id');

					$model = new DbModel();

					$sql = "SELECT * FROM MT_CUSTOMER WHERE MTC_ID=i:MTC_ID";
					$customer = $model->prepare($sql)->query(array('MTC_ID'=>$MTC_ID))->fetchAssoc();


					if($customer) {
						foreach($customer as $key=>$val) {
							$customer[$key] = trim($val);
						}
					} else {
						$customer = array();
					}
		
					// MT customer section
					//
					$addr = join(', ', array($customer['MTC_COUNTRY'], $customer['MTC_CITY'], $customer['MTC_ADDRESS']));

					$addr = array();
					if($customer['MTC_COUNTRY']) {
						$addr[] = $customer['MTC_COUNTRY'];
					}
					if($customer['MTC_CITY']) {
						$addr[] = $customer['MTC_CITY'];
					}
					if($customer['MTC_ADDRESS']) {
						$addr[] = $customer['MTC_ADDRESS'];
					}
					if($addr) {
						$customer['address'] = join(', ', $addr);
					}

					$addr = array();
					if($customer['MTC_JUR_COUNTRY']) {
						$addr[] = $customer['MTC_JUR_COUNTRY'];
					}
					if($customer['MTC_JUR_CITY']) {
						$addr[] = $customer['MTC_JUR_CITY'];
					}
					if($customer['MTC_JUR_ADDRESS']) {
						$addr[] = $customer['MTC_JUR_ADDRESS'];
					}
					if($addr) {
						$customer['jur_address'] = join(', ', $addr);
					}
		
					// Activity section
					//
					$apps = $wahost = $arhost = $domains = array();
					
					if($customer) {
					
						$sql = "SELECT APP_ID FROM MT_WAOS_APPS a JOIN MT_LICENSE l ON l.MTL_ID=a.MTL_ID
							WHERE l.MTL_ISSUE_MTC_ID=i:MTC_ID AND l.MTL_LICENSE_STATUS='ISSUED'";
						$all_apps = $model->prepare($sql)->query($customer)->fetchAll(null, true);

						foreach($all_apps as $app) {
							if(!empty($apps[$app])) {
								$apps[$app]['count']++;
							} else {
								$apps[$app] = Rights::getApplicationInfo($app);
								$apps[$app]['count'] = 1;
							}
						}

						$aa_locale = @file_get_contents('../AA/localization/aa.'.User::getLang());

						$sql = "SELECT * FROM MT_WAHOST_ACCOUNT WHERE ACC_MTC_ID=i:MTC_ID";
						$wahost = $model->prepare($sql)->query($customer)->fetchAll();
						
						foreach($wahost as $key=>$wah) {
							$wah['expired'] = false;
							$wah['plan'] = $wah['ACC_PLAN'];
							if($wah['ACC_BILLING_DATE'] && $wah['ACC_BILLING_DATE'] < date('Y-m-d')) {
								$wah['expired'] = true;
							}
							
							if($aa_locale && preg_match('/tariff_'.$wah['ACC_PLAN'].'_label\t[^\t]+\t[^\t]+\t([^\n]+)/', $aa_locale, $match)) {
								$wah['plan'] = $match[1];
							}
							$wahost[$key] = $wah;
						}

						$sql = "SELECT * FROM MT_ARHOST_ACCOUNT WHERE MTAA_MTC_ID=i:MTC_ID";
						$arhost = $model->prepare($sql)->query($customer)->fetchAll();

						foreach($arhost as $key=>$arh) {
							$arh['expired'] = false;
							$arh['plan'] = $arh['MTAA_PLAN'];
							if($arh['MTAA_EXPIRE_DATE'] && $arh['MTAA_EXPIRE_DATE'] < date('Y-m-d')) {
								$arh['expired'] = true;
							}
							/*
							if(preg_match('/^([^;]+)/', $arh['MTAA_PARAMS'], $match)) {
								$arh['plan'] = $match[1];
							}
							*/
							$arhost[$key] = $arh;
						}

						//$sql = "SELECT * FROM MT_DOMAIN_NAMES WHERE MTDN_MTC_ID=i:MTC_ID";
						$sql="SELECT MTDR_DOMAIN_NAME, MTDR_EXPIRE_DATE, MTDR_REG_DATE FROM MT_DOMAIN_REG D, MT_DOMREG_CONTACT C 
									WHERE C.MTCC_ID = D.MTDR_MTCC_ID AND C.MTCC_CANCEL_DATE IS NULL AND D.MTDR_CANCEL_DATE = 0 AND C.MTCC_MTC_ID =i:MTC_ID";
						$domains = $model->prepare($sql)->query($customer)->fetchAll();
						foreach($domains as $key=>$dom) {
							$dom['expired'] = $dom['new'] = false;
							if(!$dom['MTDR_REG_DATE']) {
								$dom['new'] = true;
							} elseif(!$dom['MTDR_EXPIRE_DATE'] || $dom['MTDR_EXPIRE_DATE'] < date('Y-m-d')) {
								$dom['expired'] = true;
							}
							$domains[$key] = $dom;
						}

					}
					
					// Orders section
					//
					$products = array();
					$products_cnt = 0;

					$sql = "SELECT DISTINCT mto.MTO_ID, mto.MTP_ID, mto.MTO_AMOUNT, mto.MTO_CUR, mto.MTO_DATE, mto.MTO_ORDER_STATUS FROM MT_ORDER mto
							LEFT JOIN MT_CUSTOMER mtc ON mto.MTC_ID=mtc.MTC_ID
							WHERE mtc.MTC_ID=i:MTC_ID ORDER BY mto.MTO_ORDER_STATUS";
					$model = new DbModel();
					$orders = $model->prepare($sql)->query(array('MTC_ID'=>$MTC_ID))->fetchAll('MTO_ID');
					$mt_status_names = array(
						'CHARGEBACK'       => 'Chargeback',
						'DELETED'          => 'Deleted',
						'NEW'              => 'New',
						'PAID'             => 'Shipped',
						'REFUND'           => 'Refund',
						'WTG_CONFIRMATION' => 'Waiting confirmation'
					);
					$mt_status_styles = array(
						'CHARGEBACK'       => 'color:#FF0000;',
						'DELETED'          => 'color:#AAAAAA;',
						'NEW'              => 'color:#2B831C;',
						'PAID'             => 'color:#E68B2C;',
						'REFUND'           => 'color:#D600B5;',
						'WTG_CONFIRMATION' => 'color:#D600B5;'
					);
					foreach ($mt_status_names as $st_id => $st_name) {
						foreach($orders as $key => $ord) {
							if ($ord['MTO_ORDER_STATUS'] == $st_id) {
								if (!isset($products[$st_id])) {
									$products[$st_id] = array();
								}
								if (!isset($products[$st_id]['amount'])) {
									$products[$st_id]['amount'] = array();
								}
								if (!isset($products[$st_id]['amount'][$ord['MTO_CUR']])) {
									$products[$st_id]['amount'][$ord['MTO_CUR']] = 0;
								}
								if (!isset($products[$st_id]['count'])) {
									$products[$st_id]['count'] = 0;
								}
								if (!isset($products[$st_id]['style'])) {
									$products[$st_id]['style'] = $mt_status_styles[$st_id];
								}
								$products[$st_id]['amount'][$ord['MTO_CUR']] += $ord['MTO_AMOUNT'];
								$products[$st_id]['count'] ++;
								$products_cnt++;
							}
						}
					}
					
					foreach ($products as $st_id => &$order) {
						$amount_arr = array();
					   foreach ($order['amount'] as $cur => $amount) {
						   $amount_arr[] = number_format($amount, 0, '', ' ').'&nbsp;'.$cur;
					   }
					   $order['amount'] = implode('; ', $amount_arr);
					}

					$this->smarty->assign('customer', $customer);

					$this->smarty->assign('apps', $apps);
					$this->smarty->assign('wahost', $wahost);
					$this->smarty->assign('arhost', $arhost);
					$this->smarty->assign('domains', $domains);
					
					$this->smarty->assign('products', $products);
					$this->smarty->assign('products_count', $products_cnt);
					
					$this->smarty->assign('MTC_ID', $MTC_ID);
					//$this->smarty->assign('tpl', 'activity');
					
					break;
			}
		}

	}
	
	public function prepareData()
	{
		$jobs = $this->getAllJobs();
	
//		$partner_jobs = $this->getPartnerJobs();
		foreach($this->partner_jobs as $key=>$row) {
			$this->partner_jobs[$key]['vote_star']  = sprintf('%02d', round(($row['CPPJ_AVGVOTE'] - floor($row['CPPJ_AVGVOTE'])) * 10));
		}

		$partner_info = Contact::getInfo($this->partner_id);
		$partner_info = array_merge($partner_info, $this->getPartnerInfo());
		$partner_info['description'] = nl2br(htmlspecialchars($partner_info['CP_DESCRIPTION']));
		$partner_info['short_description'] = nl2br(htmlspecialchars($partner_info['CP_SHORT_DESCRIPTION']));
		$partner_info['created'] = WbsDateTime::getTime(strtotime($partner_info['CP_CREATED']));

		if(strtolower($partner_info['C_LANGUAGE']) == 'rus') {
			$partner_domain = 'ru';
			$db_suffix = '';
			$partner_currency = _('RUR');
		} else {
			$partner_domain = 'net';
			$db_suffix = '_ENG';
			$partner_currency = _('USD');
		}
		
		$portfolio = $this->getPartnerPortfolio();

		foreach($portfolio as $key=>$row) {
			$row['thumb'] = $row['CPP_IMG'] ? base64_encode('/'.$this->partner_id.'/'.preg_replace('/^(.+)(\.[^\.]+)$/', '$1_thumb$2', $row['CPP_IMG'])) : false;
			$row['img'] = $row['CPP_IMG'] ? base64_encode('/'.$this->partner_id.'/'.$row['CPP_IMG']) : false;
			$row['based_id'] = base64_encode($row['CPP_ID']);
			$row['description'] = nl2br(htmlspecialchars($row['CPP_DESCR']));

			$p_jobs = $this->getPortfolioJobs($row['CPP_ID'], $db_suffix);
			
			$row['jobs'] = $p_jobs;
			$row['job_names'] = join(', ', $p_jobs);

			$portfolio[$key] = $row;
		}

		$responses = $this->getPartnerResponses();
		foreach($responses as $key=>$row) {
			$row['escaped_content'] = nl2br(htmlspecialchars($row['CPR_CONTENT']));
			$row['escaped_comment'] = nl2br(htmlspecialchars($row['CPR_PARTNER_COMMENT']));
			$row['date'] = WbsDateTime::fromMySQL($row['CPR_CREATED']);

			$responses[$key] = $row;
		}

		$p_votes = $this->getPartnerVotes();
		$votes = array();
		foreach($p_votes as $row) {
			$votes[$row['MTC_ID']][$row['CPJ_ID']] = $row['CPG_VALUE'];
		}

		require_once('../common/scripts/contact.php');
		foreach($contact_fields as $key=>$val) {
			$this->smarty->assign($key, $val);
		}

/*
		$customer_info = $this->getCustomerInfo();
		$customer_domain = (strtolower($customer_info['MTC_LANGUAGE']) == 'rus') ? 'ru' : 'net';
*/
		$this->smarty->assign('home_url', 'http://webasyst.'.$partner_domain.'/community/partners/?pid='.$this->partner_id);

		$this->smarty->assign('partner_id', base64_encode($this->partner_id));
		$this->smarty->assign('jobs', $jobs);
		$this->smarty->assign('partner_jobs', $this->partner_jobs);
		if(strtoupper($this->partner_language) == 'RUS') {
			$this->smarty->assign('job_name_field', 'CPJ_NAME');
		} else {
			$this->smarty->assign('job_name_field', 'CPJ_NAME_ENG');
		}

		$this->smarty->assign('portfolio', $portfolio);
		$this->smarty->assign('responses', $responses);
		$this->smarty->assign('votes', $votes);
		$this->smarty->assign('jobs', $this->getJobs());
		$this->smarty->assign('partner_info', $partner_info);

		$this->smarty->assign('job_name_field', 'CPJ_NAME'.$db_suffix);
		$this->smarty->assign('partner_currency', $partner_currency);
		$this->smarty->assign('partner_domain', $partner_domain);

		$this->smarty->assign('job_name_field', (strtolower(User::getLang()) == 'rus') ? 'CPJ_NAME' : 'CPJ_NAME_ENG');

		$this->smarty->assign('tpl', Env::Get('tpl'));
	}

	protected function getPartnerJobs()
	{
		$model = new DbModel();
		$sql = "SELECT PJ.*, J.* FROM CP_PARTNER_JOB PJ LEFT JOIN CP_JOB J ON J.CPJ_ID=PJ.CPJ_ID WHERE PJ.C_ID=i:C_ID AND J.CPJ_IS_"
			.$this->partner_language."=1 ORDER BY J.CPJ_SORT, J.CPJ_NAME";
		return $model->prepare($sql)->query(array('C_ID'=>$this->partner_id))->fetchAll('CPJ_ID');
	}

	protected function getAllJobs()
	{
		$model = new DbModel();
		$sql = "SELECT * FROM CP_JOB WHERE CPJ_IS_".$this->partner_language."=1 ORDER BY CPJ_SORT, CPJ_NAME";
		return $model->query($sql)->fetchAll('CPJ_ID');
	}

	protected function deleteJob($CPJ_ID)
	{
		$model = new DbModel();
		$sql = "DELETE FROM CP_PARTNER_JOB WHERE C_ID=i:C_ID AND CPJ_ID=i:CPJ_ID LIMIT 1";
		$model->prepare($sql)->query(array('C_ID'=>$this->partner_id, 'CPJ_ID'=>$CPJ_ID));
		return true;
	}

	protected function updateJob($job)
	{
		$model = new DbModel();
		$sql = "UPDATE CP_PARTNER_JOB SET CPPJ_MIN=i:CPPJ_MIN, CPPJ_MAX=i:CPPJ_MAX WHERE C_ID=i:C_ID AND CPJ_ID=i:CPJ_ID LIMIT 1";
		$model->prepare($sql)->query($job);
		return true;
	}

	protected function addJob($job)
	{
		$model = new DbModel();
		$sql = "INSERT INTO CP_PARTNER_JOB SET C_ID=i:C_ID, CPJ_ID=i:CPJ_ID, CPPJ_MIN=i:CPPJ_MIN, CPPJ_MAX=i:CPPJ_MAX, CPPJ_CREATED=NOW()
			ON DUPLICATE KEY UPDATE CPPJ_MIN=i:CPPJ_MIN, CPPJ_MAX=i:CPPJ_MAX";
		$model->prepare($sql)->query($job);
		return true;
	}

	protected function getPartnerInfo()
	{
		$model = new DbModel();
		$sql = "SELECT * FROM CP_PARTNER WHERE CP_C_ID=i:C_ID";
		return $model->prepare($sql)->query(array('C_ID'=>$this->partner_id))->fetchAssoc();
	}

	protected function getPartnerPortfolio()
	{
		$model = new DbModel();
		$sql = "SELECT * FROM CP_PORTFOLIO WHERE C_ID=i:C_ID ORDER BY CPP_CREATED DESC";
		return $model->prepare($sql)->query(array('C_ID'=>$this->partner_id))->fetchAll();
	}

	protected function getPortfolioJobs($CPP_ID, $db_suffix)
	{
		$contact_info = Contact::getInfo($this->partner_id);
		if(!$contact_info['C_LANGUAGE']) {
			$contact_info['C_LANGUAGE'] = strtoupper(Wbs::getDbkeyObj()->getLanguage());
		}

		$model = new DbModel();
		$sql = "SELECT J.CPJ_NAME$db_suffix, PJ.CPJ_ID FROM CP_PORTFOLIO_JOB PJ LEFT JOIN CP_JOB J ON J.CPJ_ID=PJ.CPJ_ID WHERE PJ.CPP_ID=i:CPP_ID AND CPJ_IS_"
			.$contact_info['C_LANGUAGE']."=1 ORDER BY J.CPJ_SORT, J.CPJ_NAME";
		$res = $model->prepare($sql)->query(array('CPP_ID'=>$CPP_ID))->fetchAll();

		$p_jobs = array();
		foreach($res as $row) {
			$p_jobs[$row['CPJ_ID']] = $row['CPJ_NAME'.$db_suffix];
		}
		return $p_jobs;
	}

	protected function getPartnerResponses()
	{
		$model = new DbModel();
		$sql = "SELECT * FROM CP_RESPONSE WHERE C_ID=i:C_ID ORDER BY CPR_CREATED DESC";
		return $model->prepare($sql)->query(array('C_ID'=>$this->partner_id))->fetchAll('MTC_ID');
	}

	protected function getPartnerVotes()
	{
		$model = new DbModel();
		$sql = "SELECT * FROM CP_GRADE WHERE C_ID=i:C_ID";
		return $model->prepare($sql)->query(array('C_ID'=>$this->partner_id))->fetchAll();
	}

	protected function getJobs()
	{
		$contact_info = Contact::getInfo($this->partner_id);
		if(!$contact_info['C_LANGUAGE']) {
			$contact_info['C_LANGUAGE'] = strtoupper(Wbs::getDbkeyObj()->getLanguage());
		}

		$model = new DbModel();
		$sql = "SELECT * FROM CP_JOB WHERE CPJ_IS_".$contact_info['C_LANGUAGE']."=1 ORDER BY CPJ_SORT, CPJ_NAME";
		return $model->prepare($sql)->query()->fetchAll('CPJ_ID');
	}

	protected function deleteResponse($MTC_ID)
	{
		$model = new DbModel();
		$sql = "DELETE FROM CP_RESPONSE WHERE MTC_ID=i:MTC_ID AND C_ID=i:C_ID LIMIT 1";
		$model->prepare($sql)->query(array('C_ID'=>$this->partner_id, 'MTC_ID'=>$MTC_ID));

		$sql = "DELETE FROM CP_GRADE WHERE MTC_ID=i:MTC_ID AND C_ID=i:C_ID";
		$model->prepare($sql)->query(array('C_ID'=>$this->partner_id, 'MTC_ID'=>$MTC_ID));
	
		$sql = "UPDATE CP_PARTNER_JOB PJ SET
			CPPJ_AVGVOTE=(SELECT AVG(CPG_VALUE) FROM CP_GRADE G WHERE G.CPJ_ID=PJ.CPJ_ID AND G.C_ID=PJ.C_ID),
			CPPJ_VOTEAMNT=(SELECT COUNT(*) FROM CP_GRADE G WHERE G.CPJ_ID=PJ.CPJ_ID AND G.C_ID=PJ.C_ID),
			CPPJ_RATE=(SELECT SUM(G.CPG_VALUE - 3) FROM CP_GRADE G WHERE G.CPJ_ID=PJ.CPJ_ID AND G.C_ID=PJ.C_ID)
			WHERE PJ.C_ID=i:C_ID";
		$model->prepare($sql)->query(array('C_ID'=>$this->partner_id));
	
		return true;
	}

	protected function editResponse($MTC_ID, $CPR_CONTENT, $CPR_PARTNER_COMMENT)
	{
		$model = new DbModel();
		$sql = "UPDATE CP_RESPONSE SET CPR_CONTENT=s:CPR_CONTENT, CPR_PARTNER_COMMENT=s:CPR_PARTNER_COMMENT WHERE MTC_ID=i:MTC_ID AND C_ID=i:C_ID LIMIT 1";
		$model->prepare($sql)->query(array('CPR_CONTENT'=>$CPR_CONTENT, 'CPR_PARTNER_COMMENT'=>$CPR_PARTNER_COMMENT, 'C_ID'=>$this->partner_id, 'MTC_ID'=>$MTC_ID));
		return true;
	}
	
	protected function savePortfolio($CPP_ID, $data)
	{
		$model = new DbModel();
		$sql = "UPDATE CP_PORTFOLIO SET CPP_NAME=s:CPP_NAME, CPP_URL=s:CPP_URL, CPP_AMOUNT=i:CPP_AMOUNT, CPP_DESCR=s:CPP_DESCR WHERE CPP_ID=i:CPP_ID AND C_ID=i:C_ID LIMIT 1";
	
		$CPP_ID = (int)$CPP_ID;
		$data['CPP_ID'] = $CPP_ID;
		$data['C_ID'] = $this->partner_id;
	
		$model->prepare($sql)->query($data);
		
		// save jobs
		//
		if(!empty($data['jobs']) && is_array($data['jobs'])) {
			foreach($data['jobs'] as $key=>$val) {
				$data['jobs'][$key] = (int)$val;
			}
			$jobs = "('$CPP_ID', '".join("'), ('$CPP_ID', '", $data['jobs'])."')";

			$sql = "DELETE FROM CP_PORTFOLIO_JOB WHERE CPP_ID=i:CPP_ID";
			$model->prepare($sql)->query($data);

			$sql = "INSERT INTO CP_PORTFOLIO_JOB (CPP_ID, CPJ_ID) VALUES $jobs";
			$model->prepare($sql)->query($data);
		}
		return true;
	}
	protected function deletePortfolio($CPP_ID)
	{
		$model = new DbModel();
		$sql = "DELETE FROM CP_PORTFOLIO WHERE CPP_ID=i:CPP_ID AND C_ID=i:C_ID LIMIT 1";
		$model->prepare($sql)->query(array('C_ID'=>$this->partner_id, 'CPP_ID'=>$CPP_ID));
		$sql = "DELETE FROM CP_PORTFOLIO WHERE CPP_ID=i:CPP_ID AND C_ID=i:C_ID LIMIT 1";
		$model->prepare($sql)->query(array('C_ID'=>$this->partner_id, 'CPP_ID'=>$CPP_ID));
		return true;
	}

	protected function saveModerated($value)
	{
		$model = new DbModel();
		$sql = "UPDATE CP_PARTNER SET CP_MODERATED=i:CP_MODERATED WHERE CP_C_ID=i:C_ID LIMIT 1";
		$model->prepare($sql)->query(array('C_ID'=>$this->partner_id, 'CP_MODERATED'=>$value));
		return true;
	}

	protected function updateInfo($data)
	{
		$model = new DbModel();
		$sql = "UPDATE CP_PARTNER SET CP_SHORT_DESCRIPTION=s:CP_SHORT_DESCRIPTION, CP_DESCRIPTION=s:CP_DESCRIPTION,
			CP_ADV_EMAIL=s:CP_ADV_EMAIL, CP_RECIEVE_NOTICE=i:CP_RECIEVE_NOTICE WHERE CP_C_ID=i:C_ID LIMIT 1";
		$data['C_ID'] = $this->partner_id;
		$model->prepare($sql)->query($data);
		return true;
	}

/*
	protected function getCustomerInfo()
	{
		$model = new DbModel();
		$sql = "SELECT * FROM MT_CUSTOMER WHERE C_ID=i:C_ID";
		return $model->prepare($sql)->query(array('C_ID'=>$this->partner_id))->fetchAssoc();
	}
*/

}
?>