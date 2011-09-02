<?php
	class UGAjaxUsersEditAction extends UGAjaxAction 
	{	    
		protected $contact_id;
		protected $contact_info = array();
		protected $group_id;
		protected $info = array();
		
		protected $type = array();
		protected $fields = array();

		protected $errors = array();
		
		public function __construct() 
		{
			parent::__construct();
			// Get params from POST
			$this->contact_id = Env::Post("C_ID", Env::TYPE_BASE64, 0);
			$this->contact_info = Contact::getInfo($this->contact_id);
			$this->section_id = Env::Post("G_ID", false, false);
			$this->info = Env::Post('info', false, array());		
			
			$contact_type = new ContactType($this->contact_info['CT_ID']);
			$this->type = $contact_type->getType();
			$this->fields = $contact_type->getFields(true);
				
			$this->save();
		}
		
		protected function save()
		{
			$errors = array();
			if (isset($this->info['CF_ID']) && $this->info['CF_ID'] === 'null') {
				unset($this->info['CF_ID']);
			}
			$this->contact_info = Contact::save($this->contact_id, $this->info, $errors, true);
			if (!$this->contact_info) {
				foreach ($errors as $error) {
					$this->setError($error);
				}
			}		
		}
	
		/**
		 * Save error
		 *
		 * @param string $error - description
		 * @param string $id - ID of the bad element 
		 */
		public function setError($error) 
		{
			$this->errors[] = $error;	
		}	
		
		/**
		 * Returns PHP response
		 *
		 * @return array
		 */
		public function getResponse()
		{	
			$response = array(
				'status' => $this->errors ? 'ERR' : 'OK',
				'error' => $this->errors 
			);

			foreach ($this->info as $field_id => $value) {
			        if ($field_id == 'CF_ID') {
			            $v = $value;
			        } else {
			        	$v = $this->contact_info[$this->fields[$field_id]['dbname']];
    			        if ($v && $this->fields[$field_id]['dbname'] == 'C_EMAILADDRESS') {
    			            $v = array_values($v);
    			        }			            
			        }
					$response['data'][$field_id] = array(
						'id' => $field_id,
						'value' => $v === null ? "" : $v
					);
			}
			$response['data']['display_name'] = Contact::getName($this->contact_id);

			return json_encode($response);
		}
	}
		
?>