<?
	class SBSCWidget extends Widget {
		
		function SBSCWidget () {
			$this->Id = "SBSC";
			parent::Widget ();
		}
		
		function prepare (&$preproc) {
			global $kernelStrings;
			global $language;
			
			$contentFilename = "signup_form.htm";
			if ($this->pageState->getParam("action") == "signup") {
				
				do {
					if ($this->isError($this->checkEmail($this->pageState->getParam("email"))))
						break;
					$contactData = array ("C_EMAILADDRESS" => $this->pageState->getParam("email"));
					$CF_ID = "1.";
					
					$valRes = validateContactData(0, $contactData, $language, $kernelStrings);
					if (PEAR::isError($valRes)) {
						if ($valRes->getUserInfo () == "C_EMAILADDRESS|CONTACT")
							$this->pageState->addError ("This email is already registred");
						else
							$this->pageState->addError($valRes);
						break;															
					}
					
					if ($this->pageState->getParam("mode") != "preview") {
						$res = addmodContact( $contactData, $CF_ID,ACTION_NEW, &$kernelStrings);
						if (PEAR::isError($res))
							break;
					}
					$contentFilename = "after_signup.htm";
				} while (false);
			}
			$preproc->assign ("contentFilename", $contentFilename);
		}
		
		function checkEmail ($email) {
			if (!$email)
				return PEAR::raiseError ("Empty email");
			if (!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,5})$", $email))
				return PEAR::raiseError ("Wrong email format");
			return true;
		}
	
	}
?>