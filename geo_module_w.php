<?php
include($_SERVER['DOCUMENT_ROOT'].'/geoip/geo.php');
define('DEFAULT_CITY', 'Москва'); // Дефолтный город
$o = array(); // Опции
$o['charset'] = 'utf-8'; // Нужно указать требуемую кодировку, если она отличается от windows-1251
$geo = new Geo($o); // Класс геопроверки
$oGeoData = $geo->get_value('city', true); // Получение города по IP

// Создание кук для падежей
setcookie('current_city_r', '', time() + 3600 * 24 * 7, '/', '.'.extractDomain ($_SERVER['HTTP_HOST'], 2)); 
setcookie('current_city_p', '', time() + 3600 * 24 * 7, '/', '.'.extractDomain ($_SERVER['HTTP_HOST'], 2)); 

// Если получен $_POST с названием города - записываем его в куки
if (isset($_POST['city_choose'])) $choosenCity = addslashes($_POST['city_choose']);
if (!empty($choosenCity)) {
	if($choosenCity == 'Другой город') $choosenCity = DEFAULT_CITY; // Если пользователем выбран "Другой город", то устанавливается регион по-умолчанию
	setcookie('current_city', $choosenCity, time() + 3600 * 24 * 7, '/', '.'.extractDomain ($_SERVER['HTTP_HOST'], 2)); // Установка куки на доменное имя
	setcookie('user_current_city', $choosenCity, time() + 3600 * 24 * 7, '/', '.'.extractDomain ($_SERVER['HTTP_HOST'], 2)); // Вносим город в данную куку, чтобы использовать только для редиректов при истории
	$_COOKIE['current_city'] = $choosenCity; 
	$_COOKIE['user_current_city'] = $choosenCity;
} 

// Блок кода, отвечающий за присвоение города переменной $city_name для последующей работы с ней
if (isset($_COOKIE['current_city'])){
	$city_name = $_COOKIE['current_city']; // Если существуют куки, устанавливаем их
}else{
	if ($geo->get_value('country') == 'RU')
	{
		$city_name = $oGeoData;
		$_COOKIE['current_city'] = $oGeoData; // Если кук нет, показываем, что получил GeoIP
	}else{
		$city_name = DEFAULT_CITY; 
		$_COOKIE['current_city'] = DEFAULT_CITY; // Если данных с GeoIP нет - выводим дефолтный город
	}
}

// Функция выделения основного домена сайта для редиректа
function extractDomain($host, $level = 2, $ignoreWWW = FALSE) { //  Уровень означает уровень домена - site.ru - 2х-уровневый
    $parts = explode(".", $host);
    if($ignoreWWW and $parts[0] == 'www') unset($parts[0]);
    $parts = array_slice($parts, -$level);
    return implode(".", $parts);
}

// Функция выделения поддомена сайта 
function getSubDomain($host, $level = -2, $ignoreWWW = TRUE) { //  Уровень означает уровень домена - level.site.ru при $level = 3 - результат: level. + Игнорировать www при получении поддомена
	$parts = explode('.', $host);
    if($ignoreWWW and $parts[0] == 'www') unset($parts[0]);
	$parts = array_slice($parts, 0, $level);
	return implode(".", $parts);
}

// CURL проверка доступности ресурса
function isDomainAvailible($domain)
{
	//проверка на валидность урла
	if(!filter_var($domain, FILTER_VALIDATE_URL)){
		return false;
	}
	//инициализация curl
	$curlInit = curl_init($domain);
	curl_setopt($curlInit,CURLOPT_CONNECTTIMEOUT,10);
	curl_setopt($curlInit,CURLOPT_HEADER,true);
	curl_setopt($curlInit,CURLOPT_NOBODY,true);
	curl_setopt($curlInit,CURLOPT_RETURNTRANSFER,true);
	//получение ответа
	$response = curl_exec($curlInit);
	curl_close($curlInit);
	if ($response && strpos($response,'200') > 0) return true;
	return false;
}

// Функция склонение города с проверкой на доступность сервиса
function morpher_inflect($text, $padeg)
{
	if (isDomainAvailible('http://morpher.ru/')){
		$credentials = array('Username'=>'test', 
							 'Password'=>'test');
		$header = new SOAPHeader('http://morpher.ru/', 
								 'Credentials', $credentials);        
		$url = 'http://morpher.ru/WebService.asmx?WSDL';
		$client = new SoapClient($url); 
		$client->__setSoapHeaders($header);
		$params = array('parameters'=>array('s'=>$text));
		$result = (array) $client->__soapCall('GetXml', $params); 
		$singular = (array) $result['GetXmlResult']; 
		return $singular[$padeg];
	} else {
		return false;
	}
}

// Склонение города
if (isset($city_name) && isDomainAvailible('http://morpher.ru/')){
	$_COOKIE['current_city_r'] = morpher_inflect($city_name, 'Р');
	$_COOKIE['current_city_p'] = morpher_inflect($city_name, 'П');
} else {
	$_COOKIE['current_city_r'] = 'Москвы'; // Дефолтные города Морфер недоступен
	$_COOKIE['current_city_p'] = 'Москве';	
}

// Указывает, если был произведен переход на конкретный региональный поддомен, то там стоит выводить только конкретную мету
if(getSubDomain($_SERVER['HTTP_HOST'], -3) != ''){
	switch (getSubDomain($_SERVER['HTTP_HOST'], -3)) {
		case 'bryansk' :
			$_COOKIE['current_city_r'] = morpher_inflect('Брянск', 'Р');
			$_COOKIE['current_city_p'] = morpher_inflect('Брянск', 'П');
			$_COOKIE['current_city'] = 'Брянск';
			break; 

		case 'spb' :
			$_COOKIE['current_city_r'] = morpher_inflect('Санкт-Петербург', 'Р');
			$_COOKIE['current_city_p'] = morpher_inflect('Санкт-Петербург', 'П');
			$_COOKIE['current_city'] = 'Санкт-Петербург';
			break; 
	}
}

// Постановка rel="canonical" для посетителя с региона на корневом домене. Учитывает проверку на наличие ссылок в стоп-листе
function putRelCanonical($city_name){
	if($_SERVER['HTTP_HOST'] == 'www.'.extractDomain ($_SERVER['HTTP_HOST'], 2) && checkRestricted()){ 
		switch ($city_name) {
			case 'Брянск' :
				echo "<link rel='canonical' href='http://www.bryansk.". extractDomain ($_SERVER['HTTP_HOST'], 3) . $_SERVER['REQUEST_URI'] ."' /> \n";
				break; 

			case 'Санкт-Петербург' :
				echo "<link rel='canonical' href='http://www.spb.". extractDomain ($_SERVER['HTTP_HOST'], 3) . $_SERVER['REQUEST_URI'] ."' /> \n";
				break; 
		}
	}
}

// Функция редиректа на целевой домен: проверяет, если регион соответствует кукам или GeoIP и если пользователь находится не на домене, на который надо производить редирект, то выполняет переход по указанному адресу
function changeDomain($region){
	if(($region == 'Брянск') && ('http://'.$_SERVER['HTTP_HOST'] != 'http://bryansk.' . extractDomain ($_SERVER['HTTP_HOST'], 3)))
	{	// Редирект на указанный поддомен для региона
		header('HTTP/1.1 200 OK');
		header('Location: http://bryansk.' . extractDomain ($_SERVER['HTTP_HOST'], 3) . $_SERVER['REQUEST_URI'], true, 301); 
		exit();		
	} 
	else if(($region == 'Санкт-Петербург') && ('http://'.$_SERVER['HTTP_HOST'] != 'http://spb.' . extractDomain ($_SERVER['HTTP_HOST'], 3))) 
	{	// Редирект на указанный поддомен для региона
		header('HTTP/1.1 200 OK');
		header('Location: http://spb.' . extractDomain ($_SERVER['HTTP_HOST'], 3) . $_SERVER['REQUEST_URI'], true, 301); 
		exit();
	} 
	else if(($region == 'Москва') && ('http://'.$_SERVER['HTTP_HOST'] != 'http://' . extractDomain ($_SERVER['HTTP_HOST'], 3))) 
	{	// Редирект на корневой домен для Москвы
		header('HTTP/1.1 200 OK');
		header('Location: http://' . extractDomain ($_SERVER['HTTP_HOST'], 3) . $_SERVER['REQUEST_URI'], true, 301); 
		exit();
	}
	else if(($region != '') && ('http://'.$_SERVER['HTTP_HOST'] == 'http://' . extractDomain ($_SERVER['HTTP_HOST'], 2)))  
	{	// Перезагрузка страницы, если находимся на домене, который соответствует региону по-умолчанию и хотим переключиться на регион по-умолчанию, будучи на другом регионе.
		// Пример: Есть домен site.ru его регион по-умолчанию Москва. Но мы сейчас определены как Брянск. И хотим переключить регион на Москва
		header('HTTP/1.1 200 OK');
		header('Location: http://' . extractDomain ($_SERVER['HTTP_HOST'], 3) . $_SERVER['REQUEST_URI'], true, 301); 
		exit();
	}
}

// Проверка, что страница не состоит в списке на невывод меты
function checkRestricted() {
	$found = 0;
	if (file_exists("links.txt")){
		$linksFile = array();
		$linksFile = file("links.txt", FILE_IGNORE_NEW_LINES);
		if ($linksFile) {
			foreach($linksFile as $row) {
				if (strpos($_SERVER['REQUEST_URI'], $row) > 0){
					$found = 1;
					break;
				}
			}
		}
	}
	if($found == 0)
		return true;
	else 
		return false;
}

// Автозамена города для мета-данных
function keyReplace($string, $city_nameP, $city_nameR, $city_name){
	$string = str_replace("*Городе*", $city_nameP, $string);
	$string = str_replace("*Города*", $city_nameR, $string);
	$string = str_replace("*Город*", $city_name, $string);
	echo $string;
} 

// Редирект при выборе города из выпадающего списка в форме
if (isset($_POST['city_choose'])){
	changeDomain($choosenCity);
}

// Если у пользователя установлены специальные куки и домен пользователя совпадает с корневым доменом, то выполняем редирект
if(($_SERVER['HTTP_HOST'] == extractDomain($_SERVER['HTTP_HOST'], 2)) && isset($_COOKIE['user_current_city'])){ 
	changeDomain($_COOKIE['user_current_city']);
} 

// С версии 2.0.2 в данном участке используется другой код.
// Если домен пользователя не совпадает с главной страницей и у пользователя установлены специальные куки, то выполняем редирект
//if(($_SERVER['HTTP_HOST'] != extractDomain($_SERVER['HTTP_HOST'], 2)) && isset($_COOKIE['user_current_city'])){ 
	// changeDomain($_COOKIE['user_current_city']);
// }
