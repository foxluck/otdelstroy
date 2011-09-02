<?php
/**
 * @package Services_PayPal
 *
 * $Id: Error.php,v 1.2 2005/10/19 22:18:09 colson Exp $
 */

/**
 * Load parent class.
 */
require_once 'PEAR.php';

/**
 * A standard PayPal Error object
 *
 * @package  Services_PayPal
 */
class PayPal_Error extends PEAR_Error {

	/**
	 * Standard error constructor
	 *
     * @param string The error message
     * @param int An optional integer error code
	 */
	function PayPal_Error($message, $errorcode = null)
	{
		parent::PEAR_error($message, $errorcode);
	}

}
