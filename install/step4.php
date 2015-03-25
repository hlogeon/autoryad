<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8"/>
<meta name="keywords" content="#"/>
<meta name="description" content="#"/>
<link href="<?php echo SITE ?>/install/css/style.css" rel="stylesheet" type="text/css" />
<link href="#" rel="shortcut icon" type="image/x-icon" />
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js"></script>
<title>Moguta.CMS | Установка</title>
</head>
<body>

<div class="install-body">
	<img src="<?php echo SITE ?>/install/images/logo.png" alt="" />
	<div class="center-wrapper step2">
		<div class="widget-table-title">
			<h4 class="product-table-icon">Поздравляем! Установка Moguta.CMS успешно завершена</h4>
		</div>
		<div class="install-text">
			<h2>Вы успешно прошли этап предварительной настройки Moguta.CMS.</h2>
			<p>Для полноценного функционирования магазина Вы должны воспользоваться панелью администрирования и настроить дополнительные параметры.</p>
			<p>Панель администрирования Вашего интернет-магазина всегда доступна по адресу: <?php $url = 'http://'.$_SERVER['SERVER_NAME'].str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']).'/mg-admin'?>
            <a href="<?php echo $url?>"><?php echo $url?></a></p>
			<p> <a class="dell-install" href = "<?php echo SITE?>/ajaxrequest?delInstal=1">Перейти на сайт</a></p>
			<div class="clear"></div>
		</div>
	</div>
</div>


</body>
</html>