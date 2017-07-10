<?
if(isset($_COOKIE['current_city'])) $city_name = $_COOKIE['current_city'];
if(isset($_COOKIE['current_city_r'])) $city_nameR = $_COOKIE['current_city_r'];
if(isset($_COOKIE['current_city_p'])) $city_nameP = $_COOKIE['current_city_p'];
?>

<!DOCTYPE HTML>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>

		<title><?php keyReplace(Core_Page::instance()->title, $city_nameP, $city_nameR, $city_name); ?></title>
		<meta name="description" content="<?php keyReplace(Core_Page::instance()->description, $city_nameP, $city_nameR, $city_name); ?>" />
		<meta name="keywords" content="<?php keyReplace(Core_Page::instance()->keywords, $city_nameP, $city_nameR, $city_name); ?>" />

		<meta content="text/html; charset=<?php echo SITE_CODING?>" http-equiv="Content-Type" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge" />
		<meta name="viewport" content="width=device-width, initial-scale=1">
	</head>
	<body>
		<header>   
			<div class="phone_top">
				<?
					if($city_name == "Брянск"){
				?>
				<p id='Label1'>
					НОМЕР ТЕЛЕФОНА
				</p>
				<?
					} elseif($city_name == "Москва"){ 
				?>
				<p id='Label2'>
					НОМЕР ТЕЛЕФОНА
				</p>
				<?
					}
				?>
			</div>
			
			<form action="./" method="POST">
				<select size="1" name="city_choose" onchange="$(this).parent('form').submit();">
					<?
						echo "<option selected value='$city_name'>Текущий город: $city_name</option>";
					?>
					<option value="Брянск">Брянск</option>
					<option value="Москва">Москва</option>
				</select>
			</form>
		</header> 
	</body>
</html>