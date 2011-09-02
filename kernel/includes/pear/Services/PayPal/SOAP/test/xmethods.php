<html><body>
<?php
//
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Shane Caraveo <Shane@Caraveo.com>   Port to PEAR and more   |
// | Authors: Dietrich Ayala <dietrich@ganx4.com> Original Author         |
// +----------------------------------------------------------------------+
//
// $Id: xmethods.php,v 1.1 2005/08/15 16:29:31 colson Exp $
//
// include soap client class
include("Services/PayPal/SOAP/Client.php");

print "getting wsdl list from xmethods...\n";
$soapclient = new SOAP_Client("http://www.xmethods.net/wsdl/query.wsdl","wsdl");
$summary = $soapclient->call("getAllServiceSummaries",array());

# !@#^%$ php needs real timeouts on windows
$skip = array('Unisys Weather Web Service');

print "getting wsdls in list...\n";
foreach ($summary as $info) {
    if (in_array($info['name'],$skip)) continue;
    print "retrieving {$info['name']}...";
    $wsdl = new SOAP_WSDL($info['wsdlURL']);
    if ($wsdl->fault) {
        print $wsdl->fault->getMessage()."\n";
    } else {
        print "wsdl parsed OK\n";
    }
}
print "\n\n";


?>
</html></body>
