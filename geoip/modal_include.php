<?
	if(isset($_COOKIE['current_city'])) $city_name = $_COOKIE['current_city'];
	if(isset($_COOKIE['current_city_r'])) $city_nameR = $_COOKIE['current_city_r'];
	if(isset($_COOKIE['current_city_p'])) $city_nameP = $_COOKIE['current_city_p'];
?>
<div class="b-geo__popup b-geo__city-confirm">
	<div class="b-geo__header">
		Ваш город: <?=$city_name;?>
	</div>
	<form action="<?=$_SERVER['REQUEST_URI'];?>" method="POST">
		<button class="b-geo__input b_geo__city-apply" type="submit">Да</button>
		<button class="b-geo__input b_geo__city-change">Нет</button>
		<input name="city_choose" type="hidden" value="<?=$city_name;?>" />
	</form>
</div>
<div class="b-geo__popup b-geo__city-list">
	<div class="b-geo__header">
		Выберите Ваш город:
	</div>
	<form action="<?=$_SERVER['REQUEST_URI'];?>" method="POST">
		<button name="city_choose" class="b-geo__input b_geo__city-list__input" type="submit" value="Брянск">Брянск</button>
		<button name="city_choose" class="b-geo__input b_geo__city-list__input" type="submit" value="Москва">Москва</button>
		<button name="city_choose" class="b-geo__input b_geo__city-list__input" type="submit" value="Псков">Псков</button>
		<button name="city_choose" class="b-geo__input b_geo__city-list__input" type="submit" value="Киров">Киров</button>
		<button name="city_choose" class="b-geo__input b_geo__city-list__input" type="submit" value="Сыктывкар">Сыктывкар</button>
		<button name="city_choose" class="b-geo__input b_geo__city-list__input" type="submit" value="Другой город">Другой город</button>
	</form>
</div>
<div class="b-geo__overlay"></div>
<link rel="stylesheet" type="text/css" href="/geoip/styles.css">
<script>
<? if(!isset($_COOKIE['user_current_city'])): ?>
if ($('.b-geo__city-confirm').length != 0) {
	$('.b-geo__city-confirm').fadeIn("fast");
	var Mtop = -($('.b-geo__city-confirm').outerHeight() / 2) + 'px';
	var Mleft = -($('.b-geo__city-confirm').outerWidth() / 2) + 'px';
	$('.b-geo__city-confirm').css({
		'margin-top': Mtop,
		'margin-left': Mleft,
		'left': '50%',
		'top': '50%'
	});
	$('.b-geo__overlay').fadeIn("fast");
}
<?endif;?>
if ($('.b-geo__city-list').length != 0) {
	$('body').find('.b_geo__city-change, .b-geo__city-name_header').on('click', function (e) {
		e.preventDefault();
		$('.b-geo__city-confirm').fadeOut("fast");
		$(this).parents('body').find('.b-geo__city-list').fadeIn("fast");
		var Mtop = -($('.b-geo__city-list').outerHeight() / 2) + 'px';
		var Mleft = -($('.b-geo__city-list').outerWidth() / 2) + 'px';
		$('.b-geo__city-list').css({
			'margin-top': Mtop,
			'margin-left': Mleft,
			'left': '50%',
			'top': '50%'
		});
		$('.b-geo__overlay').fadeIn("fast");
	});
}
</script>

