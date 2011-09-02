var SD=17031;
var AP=17032;
var OP=17034;
var SecretKey="secret.key";
var PublicKeys="pubkeys.key";
var Passwd="1111111111";
var BankKeySerial=64182;
var UserSecretKey;
var UserPublicKey;
var Signer;


function UrlEncode(src)
{
	var tmp=String(escape(src));
	var dst=new String();
	for(var i=0;i<tmp.length;i++)
	{
		if(tmp.charAt(i)=='+')
			dst+="%2b";
		else
			dst+=tmp.charAt(i);
	}
	return dst.toString();
}

function Initialize()
{
	UserSecretKey=new ActiveXObject ("Libipriv.SecretKey");
	UserPublicKey=new ActiveXObject ("Libipriv.PublicKey");
	Signer=new ActiveXObject ("Libipriv.Signer");

	if(UserSecretKey.LoadFromFile(SecretKey,Passwd))
	{
		if(UserPublicKey.LoadFromFile(PublicKeys,BankKeySerial))
			return true;
		else WScript.Echo(UserPublicKey.ErrMsg);
	}else WScript.Echo(UserSecretKey.ErrMsg);
	return false;
}

function SendRequest(request,url,trace)
{
	var signed=Signer.Sign(request,UserSecretKey);
	if(signed=="")
		return "";

	if(trace) WScript.Echo(signed);

	request="inputmessage="+UrlEncode(signed);

	if(trace) WScript.Echo(request);

	var http=new ActiveXObject("WinHttp.WinHttpRequest.5.1");
	http.Open("POST",url,false);
	http.Send(request);

	if(http.Status=="200")
	{
		if(trace) WScript.Echo(http.ResponseText());

		var response=Signer.Verify(http.ResponseText(),UserPublicKey);
		if(response!="")
		{
			if(trace) WScript.Echo(response);
			return response;
		}
		else
			WScript.Echo(Signer.ErrMsg);
	}else
		WScript.Echo("HTTP code: "+http.Status);
	return "";
}

if(Initialize())
{
	var rc=SendRequest("SD="+SD+"\r\nAP="+AP+"\r\nOP="+OP+"\r\nSESSION=123456\r\nNUMBER=8888888888\r\nACCOUNT=\r\nAMOUNT=10",
		"http://payment.cyberplat.ru/cgi-bin/mt/mt_pay_check.cgi",false);
	WScript.Echo(rc);
}
