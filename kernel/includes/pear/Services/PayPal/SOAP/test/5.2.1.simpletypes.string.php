<?php
require_once("Services/PayPal/SOAP/test/test.utility.php");
require_once("Services/PayPal/SOAP/Base.php");
$soap_base = new SOAP_Base();
$prefix = "5.2.1 String";

$msg = '<?xml version="1.0" encoding="UTF-8"?>

<SOAP-ENV:Envelope SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
<SOAP-ENV:Body>
<m:echoStringResponse xmlns:m="http://soapinterop.org/">
<return xsi:type="xsd:string">hello world</return>
</m:echoStringResponse>
</SOAP-ENV:Body>
</SOAP-ENV:Envelope>
';

$expect = 'hello world';
$val = parseMessage($msg);
if (string_compare($expect, $val)) {
    print "$prefix Deserialize Message OK\n";
} else {
    print "$prefix Deserialize Message FAILED\n";
}

# serialize a soap value
$expect = '<inputString xsi:type="xsd:string">hello world</inputString>';
$v = new SOAP_Value("inputString","string","hello world");
$val = $v->serialize($soap_base);
if (string_compare($expect, $val)) {
    print "$prefix Serialize Type OK\n";
} else {
    print "$prefix Serialize Type FAILED, expected $expect, got $val\n";
}
# serialize a soap value with unknown type
$expect = "hello world";
$val = $soap_base->_decode($v);
if (string_compare($expect, $val)) {
    print "$prefix Deserialize known SOAP_Value OK\n";
} else {
    print "$prefix Deserialize known SOAP_Value FAILED\n";
}

# serialize a soap value with unknown type
$expect = '<inputString xsi:type="xsd:string">hello world</inputString>';
$v = new SOAP_Value("inputString","","hello world");
$val = $v->serialize($soap_base);
if (string_compare($expect, $val)) {
    print "$prefix Serialize Unknown Type OK\n";
} else {
    print "$prefix Serialize Unknown Type FAILED\n";
}

# serialize a soap value with unknown type
$expect = "hello world";
$val = $soap_base->_decode($v);
if (string_compare($expect, $val)) {
    print "$prefix Deserialize Unknown SOAP_Value OK\n";
} else {
    print "$prefix Deserialize Unknown SOAP_Value FAILED\n";
}
?>