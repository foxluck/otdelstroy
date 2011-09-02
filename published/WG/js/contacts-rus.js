jQuery.extend(jQuery.validator.messages, {
	maxlength: jQuery.validator.format("Количество символов не должно превышать {0}."),
	year: jQuery.validator.format("Неправильная дата."),
	primarymessage: ["Главное имя должно быть заполнено.", "Хотя бы одно из полей главного имени должно быть заполнено."],
	captcha: "Неправильный код.",
	email:"Неправильный email.",
	required:"Поле обязательно для заполнения"
});