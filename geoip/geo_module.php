<?php
define('PHPMORPHY_DIR', $_SERVER['DOCUMENT_ROOT'].'/geoip/morpher/src'); 
define('DEFAULT_CITY', 'Смоленск'); // Дефолтный город
require_once(PHPMORPHY_DIR . '/common.php');  // Библиотека склонятора
require_once($_SERVER['DOCUMENT_ROOT'].'/geoip/geo.php'); // Класс определения города
require_once($_SERVER['DOCUMENT_ROOT'].'/geoip/city_list.php'); // Список городов
// Экземпляр класса геоопределения
$o = array(); 
$o['charset'] = 'utf-8'; 
$geo = new Geo($o); 
// Экземпляр класса склонятора
$opts = array(
	'storage' => PHPMORPHY_STORAGE_FILE,
	'with_gramtab' => false,
	'predict_by_suffix' => true, 
	'predict_by_db' => true
);
$morph_dicts_dir = PHPMORPHY_DIR . '/dicts';
$dict_bundle = new phpMorphy_FilesBundle($morph_dicts_dir, 'rus');
$morphy = new phpMorphy($dict_bundle, $opts);

function cast_predicate_r($form, $partOfSpeech, $grammems, $formNo) {
    return in_array('РД', $grammems); 
}
function cast_predicate_p($form, $partOfSpeech, $grammems, $formNo) {
    return in_array('ПР', $grammems); 
}
// Функция склонения города
function getDecForm($source_word, $form, $morphy) {
	$source_word = mb_strtoupper($source_word, "UTF-8"); // Склонятору нужен город в верхнем регистре
	if($form == 'П') $declension_word = $morphy->castFormByGramInfo($source_word, null, null, true, 'cast_predicate_p');
	if($form == 'Р') $declension_word = $morphy->castFormByGramInfo($source_word, null, null, true, 'cast_predicate_r');
	$declension_word = mb_convert_case(mb_strtolower($declension_word[0], 'UTF-8'), MB_CASE_TITLE, "UTF-8");
	return $declension_word;
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

// Если получен $_POST с названием города - записываем его в куки
if (isset($_POST['city_choose'])){
	$choosenCity = addslashes($_POST['city_choose']);
	if($choosenCity == 'Другой город') $choosenCity = DEFAULT_CITY; // Если пользователем выбран "Другой город", то устанавливается регион по-умолчанию
	setcookie('current_city', $choosenCity, time() + 3600 * 24 * 7, '/', '.'.extractDomain ($_SERVER['HTTP_HOST'], 2)); // Установка куки на доменное имя
	setcookie('user_current_city', $choosenCity, time() + 3600 * 24 * 7, '/', '.'.extractDomain ($_SERVER['HTTP_HOST'], 2)); // Историческая кука
	setcookie('current_city_p', getDecForm($choosenCity, 'П', $morphy), time() + 3600 * 24 * 7, '/', '.'.extractDomain ($_SERVER['HTTP_HOST'], 2)); 
	setcookie('current_city_r', getDecForm($choosenCity, 'Р', $morphy), time() + 3600 * 24 * 7, '/', '.'.extractDomain ($_SERVER['HTTP_HOST'], 2)); 
	// Пишем куки еще раз, чтобы редирект сработал сразу после отправки запроса
	$_COOKIE['current_city'] = $choosenCity; 
	$_COOKIE['user_current_city'] = $choosenCity; 
	$_COOKIE['current_city_p'] = getDecForm($choosenCity, 'П', $morphy);
	$_COOKIE['current_city_r'] = getDecForm($choosenCity, 'Р', $morphy);
	
	changeDomain($choosenCity); // Перенаправление на нужный поддомен
} 

// Блок кода, отвечающий за присвоение города переменной $city_name для последующей работы с ней
if (isset($_COOKIE['user_current_city']) && isset($_COOKIE['current_city_p']) && isset($_COOKIE['current_city_r'])){
	$city_name = $_COOKIE['user_current_city']; 
}else{
	if ($geo->get_value('country') == 'RU')
	{ 
		// Если кук нет, показываем, что получил GeoIP
		require_once($_SERVER['DOCUMENT_ROOT'].'/geoip/cities_double_list.php'); // Список двойных городов
		$oGeoData = $geo->get_value('city', true); // Получение города по IP
		$city_name = $oGeoData;
		// Сравниваем полученный по GeoIP город со списком двойных городов
		foreach($aDoubleCity as $doubleCity){
			if($doubleCity[0] == $city_name){
				$arCityForms['normal'] = $doubleCity[0];
				$arCityForms['p'] = $doubleCity[2];
				$arCityForms['r'] = $doubleCity[1];
			}
		}
		// Если город является двойным, то пишем в куки склонения из файла
		if(isset($arCityForms)){
			$_COOKIE['current_city'] = $arCityForms['normal'];
			$_COOKIE['current_city_p'] = $arCityForms['p'];
			$_COOKIE['current_city_r'] = $arCityForms['r'];
		} else {
			$_COOKIE['current_city'] = $oGeoData;
			$_COOKIE['current_city_p'] = getDecForm($oGeoData, 'П', $morphy);
			$_COOKIE['current_city_r'] = getDecForm($oGeoData, 'Р', $morphy);			
		}
	}else{
		$city_name = DEFAULT_CITY; 
		$_COOKIE['current_city'] = DEFAULT_CITY; // Если данных с GeoIP нет - выводим дефолтный город
		$_COOKIE['current_city_p'] = getDecForm(DEFAULT_CITY, 'П', $morphy);
		$_COOKIE['current_city_r'] = getDecForm(DEFAULT_CITY, 'Р', $morphy);
	}
}

// Указывает, если был произведен переход на конкретный региональный поддомен, то там стоит выводить только конкретную мету, а не мету геоопределения
if(getSubDomain($_SERVER['HTTP_HOST'], -2) != ''){
	foreach($aCityList as $city)
	{
		if((getSubDomain($_SERVER['HTTP_HOST'], -2) == $city['subdomain']))
		{
			if(isset($city['city_p']) && isset($city['city_r']))
			{
				// Проверка на добавление склонения городов из файла
				$_COOKIE['current_city_p'] = $city['city_p'];
				$_COOKIE['current_city_r'] = $city['city_r'];
				$_COOKIE['current_city'] = $city['city'];
			} else {
				$_COOKIE['current_city_p'] = getDecForm($city['city'], 'П', $morphy);
				$_COOKIE['current_city_r'] = getDecForm($city['city'], 'Р', $morphy);
				$_COOKIE['current_city'] = $city['city'];
			}			
		} 
	}
}

// Постановка rel="canonical" для посетителя с региона на корневом домене. Учитывает проверку на наличие ссылок в стоп-листе
function putRelCanonical($city_name){
	global $aCityList;
	if($_SERVER['HTTP_HOST'] == extractDomain ($_SERVER['HTTP_HOST'], 2) && checkRestricted()){ 
		foreach($aCityList as $city)
		{
			if(!empty($city['subdomain'])) $city['subdomain'] = $city['subdomain'].'.';
			if(($city_name == $city['city']))
			{
				echo "<link rel='canonical' href='http://". $city['subdomain'] . extractDomain ($_SERVER['HTTP_HOST'], 2) . $_SERVER['REQUEST_URI'] ."' /> \n";	
			} 
		}
	}
}

// Функция редиректа на целевой домен: проверяет, если регион соответствует кукам или GeoIP и если пользователь находится не на домене, на который надо производить редирект, то выполняет переход по указанному адресу
function changeDomain($region){
	global $aCityList;
	foreach($aCityList as $city)
	{
		if(!empty($city['subdomain'])) $city['subdomain'] = $city['subdomain'].'.';
		if(($region == $city['city']) && ('http://'.$_SERVER['HTTP_HOST'] != 'http://'. $city['subdomain'] . extractDomain ($_SERVER['HTTP_HOST'], 2)))
		{
			header('HTTP/1.1 200 OK');
			header('Location: http://'. $city['subdomain'] . extractDomain ($_SERVER['HTTP_HOST'], 2) . $_SERVER['REQUEST_URI'], true, 301); 
			exit();		
		} 
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

// Если пользователь находится на основном домене, а его специальные куки говорят, что он должен находиться на поддомене - редиректим на поддомен
// if(($_SERVER['HTTP_HOST'] == extractDomain($_SERVER['HTTP_HOST'], 2)) && isset($_COOKIE['user_current_city'])){ 
// //	changeDomain($_COOKIE['user_current_city']);
// } 

// Если пользователь находится на поддомене, а его специальные куки говорят, что он должен находиться на основном домене - редиректим на основной домен
if(($_SERVER['HTTP_HOST'] != extractDomain ($_SERVER['HTTP_HOST'], 2)) && isset($_COOKIE['user_current_city'])){ 
	changeDomain($_COOKIE['user_current_city']);
}
// Возможно одну из функций редиректа надо закомментить - у сеошников всегда меняются требования. Но пока так. 