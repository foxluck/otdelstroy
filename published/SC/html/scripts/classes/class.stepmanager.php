<?php
	if(class_exists('stepmanager',false))return ;

	class StepManager{
		
		/**
		 * List of past steps
		 *
		 * @var ListNode
		 */
		var $StepChain;
		var $StepDir = '.';
		var $getvar_name = 'step';
		var $default_step = 'default';
		var $allowed_steps = array();
		
		function StepManager(){
			
			$this->StepChain = null;
		}
		
		function unregisterStep($StepKey){
			
			if(!is_null($this->StepChain)){
				
				$ExistNode = &$this->StepChain->findNode($StepKey);
				/* @var $ExistNode ListNode */
				if($ExistNode instanceof listnode){
					
					$ExistNode->deleteFromList();
					return true;
				}
			}
			return false;
		}
		
		function registerStep($StepKey){

			if(is_null($this->StepChain)){
				
				$this->StepChain = new ListNode($StepKey,'');
			}else{
				
				$ExistNode = &$this->StepChain->findNode($StepKey);
				/* @var $ExistNode ListNode */
				if($ExistNode instanceof listnode){
					
					$this->StepChain = &$ExistNode;
					$b = 100;
					while (!is_null($ExistNode->NextNode)&&0<$b--){
						
						$ExistNode->NextNode->deleteFromList();
					}
				}else{

					$this->StepChain = &$this->StepChain->createNextNode($StepKey,'');
				}
				
			}
		}
		
		function printStepChain(){
			
			if(is_null($this->StepChain))return;
			
			$Node = &$this->StepChain->getFirstNode();
			print '/<a href="'.xHtmlSetQuery('step='.$Node->Key).'">'.$Node->Key.'</a>';
			
			while (!is_null($Node->NextNode)){
				
				$Node = &$Node->NextNode;
				print '/<a href="'.xHtmlSetQuery('step='.$Node->Key).'">'.$Node->Key.'</a>';
			}
		}
		
		function testStepData($StepKey = null){
			
			include($this->StepDir.'/'.$StepKey.'.td.php');
		}
		
		function testPreviousStepsData(){
			
			$CurrentStep = &$this->getCurrentStep();
			$Node = &$this->StepChain->getFirstNode();
			$StepKey = &$Node->Key;
			
			do{
				
				if($CurrentStep->Key != $Node->Key){
					
					if(file_exists($this->StepDir.'/'.$Node->Key.'.td.php'))
						include($this->StepDir.'/'.$Node->Key.'.td.php');
				}
				
				if(!is_null($Node->NextNode)){
					$Node = &$Node->NextNode;
				}
			}while (!is_null($Node->NextNode));
		}
		
		/**
		 * Return reference to current step listnode
		 *
		 * @return ListNode
		 */
		function &getCurrentStep(){
			
			return $this->StepChain;
		}
		
		/**
		 * Return step status (current, passed, ahead)
		 * @param string - step key
		 * @return string
		 */
		function getStepStatus($step_key){
			
			$CurrentStep = &$this->getCurrentStep();
			if($CurrentStep->Key == $step_key)return 'current';
			
			$Node = &$this->StepChain->getFirstNode();
			$StepKey = &$Node->Key;
			
			do{
				
				if($CurrentStep->Key == $Node->Key)break;

				if($Node->Key == $step_key)return 'passed';
				
				if(!is_null($Node->NextNode))$Node = &$Node->NextNode;
			}while (!is_null($Node->NextNode));
			
			return 'ahead';
		}
		
		function init(){
			
			$this->registerStep(isset($_GET[$this->getvar_name])&&in_array($_GET[$this->getvar_name], $this->allowed_steps)?$_GET[$this->getvar_name]:$this->default_step);
		}
		
		function exec(){
			
			$currentStep = $this->getCurrentStep();
			
			include $this->StepDir.'/'.$currentStep->getKey().'.php';
			$controller_name = str_replace('_', '', $currentStep->getKey()).'Controller'; 
			if(class_exists($controller_name)){
				
				ActionsController::exec($controller_name);
			}
		}
	}
?>