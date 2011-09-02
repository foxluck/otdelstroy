// Оригинальное сообщение
var doc="Hello world";


// Загружаем объект закрытого ключа (для формирования подписи)
var UserSecretKey=new ActiveXObject ("Libipriv.SecretKey");
// Загружаем объект открытого ключа (для проверки подписи)
var UserPublicKey=new ActiveXObject ("Libipriv.PublicKey");
// Загружаем объект для работы с ЭЦП
var Signer=new ActiveXObject ("Libipriv.Signer");

// Загружаем закрытый ключ из файла "secret.key", кодовая фраза - "1111111111"
if(UserSecretKey.LoadFromFile("secret.key","1111111111"))
{
// Загружаем открытый ключ из файла "pubkeys.key" с серийным номером "17033"
	if(UserPublicKey.LoadFromFile("pubkeys.key",17033))
	{
// Подписываем оригинальное сообщение закрытым ключем
		var signmessage=Signer.Sign(doc,UserSecretKey);
		if(signmessage!="")
		{
			WScript.Echo(signmessage);
// Проверяем подпись открытым ключем
			var message=Signer.Verify(signmessage,UserPublicKey);
			if(message!="")
				WScript.Echo(message);
			else WScript.Echo(Signer.ErrMsg);
		}else WScript.Echo(Signer.ErrMsg);
	}else WScript.Echo(UserPublicKey.ErrMsg);
}else WScript.Echo(UserSecretKey.ErrMsg);
