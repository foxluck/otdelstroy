<?php
/**
* Database_Placeholder: placeholder support for most SQL interfaces.
* (C) 2005 Dmitry Koterov, http://forum.dklab.ru/users/DmitryKoterov/
*
* This library is free software; you can redistribute it and/or
* modify it under the terms of the GNU Lesser General Public
* License as published by the Free Software Foundation; either
* version 2.1 of the License, or (at your option) any later version.
* See http://www.gnu.org/copyleft/lesser.html
*
* Этот файл содержит две полезных функции для упрощения работы
* с SQL-запросами в программах на PHP, а также повышения
* защищенности скриптов.
*
* @version 2.20;
*/

// При ошибке в sql_placeholder_ex() возвращается запрос с
// указанным ниже префиксом.
@define("PLACEHOLDER_ERROR_PREFIX", "ERROR: ");

/**
 * Разбирает шаблон запроса и сохраняет положения всех placeholder-ов в нем для дальнейшей быстрой подстановки.
 * Возвращает структуру вида:
 *	list(
 * 		list(
 *  		 $key,    // имя placeholder-а
 *  		 $type,   // '@'|'%'|'#'|''
 *  		 $start,  // положение placeholder-а
 *  		 $length  // длина placeholder-а
 *  		),
 * 		$tmpl,     // исходный шаблон запроса
 *  	$has_named // есть ли в шаблоне именованный placeholder?
 * 		)
 * @param string $tmpl исходный шаблон запроса
 * @return array 
 */
function sql_compile_placeholder($tmpl) {
  $compiled  = array();
  $p         = 0;  // текущая позиция в строке
  $i         = 0;  // счетчик placeholder-ов
  $has_named = false;
  while (false !== ($start = $p = strpos($tmpl, "?", $p))) {
    // Определяем тип placeholder-а.
    switch ($c = substr($tmpl, ++$p, 1)) {
      case '%': case '@': case '#': case '&':
        $type = $c; ++$p; break;
      default:
        $type = ''; break;
    }
    // Проверяем, именованный ли это placeholder: "?keyname"
    if (preg_match('/^((?:[^\s[:punct:]]|_)+)/u', substr($tmpl, $p), $pock)) {
      $key = $pock[1];
      if ($type != '#') $has_named = true;
      $p += strlen($key);
    } else {
      $key = $i;
      if ($type != '#') $i++;
    }
    // Сохранить запись о placeholder-е.
    $compiled[] = array($key, $type, $start, $p - $start);
  }
  return array($compiled, $tmpl, $has_named);
}


// bool sql_placeholder_ex(mixed $tmpl, array $args, string &$errormsg)
//
// Заменяет все placeholder-ы в $tmpl на их SQL-экранированные значения
// из $args. При ошибке сохраняет диагностическое сообщение в $errormsg.
//
// Различные типы placeholder-ов:
//   ?  - заменяется на ОДНО скалярное значение.
//   ?@ - заменяется на СПИСОК: 'a', 'b', ... (например, удобно
//        использовать в запросе "SELECT ... WHERE id IN (?@)")
//   ?% - заменяется на список пар ключ=значение: k1='v1', k2='v2', ...
//        (удобно использовать в запросах "UPDATE ... SET ?%")
//
// Placeholder-ы могут быть именованными: их имя можно указывать сразу
// после спецификатора типа, например: "?k", "?@k", "?%k".
//
// Параметр $tmpl может содержать не только текстовое представление
// шаблона, но и результат работы функции sql_compile_placeholder().
// Это удобно, если нужно несколько раз выполнить SQL-запрос, имеющий
// один и тот же шаблон, но разные параметры.
//
// Если в шаблоне есть хотя бы один именованный placeholder,
// $args должен содержать список из ЕДИНСТВЕННОГО элемента. Этот
// элемент сам является ассоциативным массивом, содержащим имена
// placeholder-ов и соответствующие им значения.
//
// Если при подстановке  возникнут ошибки (например, несоответствие
// типов placeholder-а и подставляемого значения, недопустимое имя
// или номер placeholder-а и т.д.), в результирующий запрос вместо
// значения placeholder-а вставляется диагностическое сообщение.
// При этом функция возвращает false, а получившийся "фальшивый"
// запрос помещается в переменную $errormsg.
function sql_placeholder_ex($tmpl, $args, &$errormsg) {
  // Запрос уже разобран?.. Если нет, разбираем.
  if (is_array($tmpl)) {
    $compiled = $tmpl;
  } else {
    $compiled  = sql_compile_placeholder($tmpl);
  }

  list ($compiled, $tmpl, $has_named) = $compiled;

  // Если есть хотя бы один именованный placeholder, используем
  // первый аргумент в качестве ассоциативного массива.
  if ($has_named) $args = @$args[0];

  // Выполняем все замены в цикле.
  $p   = 0;       // текущее положение в строке
  $out = '';      // результирующая строка
  $error = false; // были ошибки?

  foreach ($compiled as $num=>$e) {
    list ($key, $type, $start, $length) = $e;

    // Pre-string.
    $out .= substr($tmpl, $p, $start - $p);
    $p = $start + $length;

    $repl = '';   // текст для замены текущего placeholder-а
    $errmsg = ''; // сообщение об ошибке для этого placeholder-а
    do {
      // Это placeholder-константа?
      if ($type === '#') {
        $repl = @constant($key);
        if (NULL === $repl)
          $error = $errmsg = "UNKNOWN_CONSTANT_$key";
        break;
      }
      // Обрабатываем ошибку.
      if (!isset($args[$key])) {
      	$args[$key] = '';
//        $error = $errmsg = "UNKNOWN_PLACEHOLDER_$key";
//        break;
      }
      // Вставляем значение в соответствии с типом placeholder-а.
      $a = $args[$key];
      if ($type === '') {
        // Скалярный placeholder.
        if (is_array($a)) {
          $error = $errmsg = "NOT_A_SCALAR_PLACEHOLDER_$key";
          break;
        }
        $repl = (preg_match('/^\d+\.{0,1}\d*$/u', $a)&&(strlen(floatval($a))==strlen($a)))? $a : "'".addslashes($a)."'";
        //$repl = !is_string($a)? $a : "'".addslashes($a)."'";
        break;
      }
      // Иначе это массив или список.
      if (!is_array($a)) {
        $error = $errmsg = "NOT_AN_ARRAY_PLACEHOLDER_$key";
        break;
      }
      if ($type === '@') {
        // Это список.
        foreach ($a as $v)
          $repl .= ($repl===''? "" : ",")."'".addslashes($v)."'";
      } elseif ($type === '%') {
        // Это набор пар ключ=>значение.
        $lerror = array();
        foreach ($a as $k=>$v) {
          if (!is_string($k)) {
            $lerror[$k] = "NOT_A_STRING_KEY_{$k}_FOR_PLACEHOLDER_$key";
          } else {
            $k = preg_replace('/[^a-zA-Z0-9_]/u', '_', $k);
          }
          $repl .= ($repl===''? "" : ", ").$k."='".@addslashes($v)."'";
        }
        // Если была ошибка, составляем сообщение.
        if (count($lerror)) {
          $repl = '';
          foreach ($a as $k=>$v) {
            if (isset($lerror[$k])) {
              $repl .= ($repl===''? "" : ", ").$lerror[$k];
            } else {
              $k = preg_replace('/[^a-zA-Z0-9_-]/', '_', $k);
              $repl .= ($repl===''? "" : ", ").$k."=?";
            }
          }
          $error = $errmsg = $repl;
        }
      } elseif ($type === '&'){
      	
			// Это список.
			foreach ($a as $v)
			$repl .= ($repl===''? "" : ",").'`'.addslashes($v).'`';
      }
    } while (false);
    if ($errmsg) $compiled[$num]['error'] = $errmsg;
    if (!$error) $out .= $repl;
  }
  $out .= substr($tmpl, $p);

  // Если возникла ошибка, переделываем результирующую строку
  // в сообщение об ошибке (расставляем диагностические строки
  // вместо ошибочных placeholder-ов).
  if ($error) {
    $out = '';
    $p   = 0;       // текущая позиция
    foreach ($compiled as $num=>$e) {
      list ($key, $type, $start, $length) = $e;
      $out .= substr($tmpl, $p, $start - $p);
      $p = $start + $length;
      if (isset($e['error'])) {
        $out .= $e['error'];
      } else {
        $out .= substr($tmpl, $start, $length);
      }
    }
    // Последняя часть строки.
    $out .= substr($tmpl, $p);
    $errormsg = $out;
    return false;
  } else {
    $errormsg = false;
    return $out;
  }
}


// function sql_placeholder(mixed $tmpl, $arg1 [,$arg2 ...])
//
// Замечание: см. описание функции sql_placeholder_ex() выше.
//
// Возвращает результирующий запрос после всех подстановок.
// В случае ошибки запрос будет содержать префикс "ERROR: ".
//
// Если во время подстановки произошла ошибка, (например, несоответствие
// типов), вставляет вместо значений placeholder-ов текстовое сообщение
// об ошибке и возвращает запрос в следующем виде:
//   "ERROR: шаблон с проставленными сообщениями".
// Такой запрос, конечно, породит ошибку при попытке своего выполнения.
// Вы также можете проанализировать возвращаенное значение: если оно
// начинается со строки "ERROR: ", подстановка окончилась неудачей.
//
// Вместо того, чтобы использовать массив в качестве второго параметра,
// вы можете передать значения всех неименованных placeholder-ов одно
// за одним.
//
// Если же в шаблоне есть хотя бы один именованный placeholder, функция
// ОБЯЗАНА принимать в точности два параметра, где первый - это шаблон,
// а второй - ассоциативный массив для подстановки значений именованных
// placeholder-ов.
function sql_placeholder() {
  $args = func_get_args();
  $tmpl = array_shift($args);
  $result = sql_placeholder_ex($tmpl, $args, $error);
  if ($result === false) return PLACEHOLDER_ERROR_PREFIX.$error;
  else return $result;
}


// function sql_pholder(mixed $tmpl, $arg1 [,$arg2 ...])
//
// Замечание: см. описание функции sql_placeholder() выше.
//
// Функция работает точно так же, как sql_placeholder(), однако
// в случае ошибки она возвращает false и генерирует предупреждение
// стандартными средствами, используя trigger_error().
function sql_pholder() {
  $args = func_get_args();
  $tmpl = array_shift($args);
  $result = sql_placeholder_ex($tmpl, $args, $error);
  if ($result === false) {
    $error = "Placeholder substitution error. Diagnostics: \"$error\"";
    if (function_exists("debug_backtrace")) {
      $bt = debug_backtrace();
      $error .= " in ".@$bt[0]['file']." on line ".@$bt[0]['line'];
    }
    trigger_error($error, E_USER_WARNING);
    return false;
  }
  return $result;
}

?>
