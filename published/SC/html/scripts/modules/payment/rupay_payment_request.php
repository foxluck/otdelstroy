<?php
// RUPAY payment module

/**
 * @subpackage Payment
class CRupayPaymentRequest extends PaymentModule {
	var $type = PAYMTD_TYPE_ONLINE;
	function _initVars(){
	function _initSettingFields(){
	function after_processing_html( $orderID ){
		$order_amount = round(100*$order["order_amount"] * $RUpay_curr_rate)/100;