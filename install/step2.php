<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8"/>
<meta name="keywords" content="#"/>
<meta name="description" content="#"/>
<link href="<?php echo SITE ?>/install/css/style.css" rel="stylesheet" type="text/css" />
<link href="#" rel="shortcut icon" type="image/x-icon" />
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js"></script>
<!--[if IE 9]>
  <style type="text/css">
   .error-pass-count::after{left:-253px;}
   .error-email::after{left:-364px;}
  </style>
  <![endif]-->
<title>Moguta.CMS | Установка</title>
<script>
  //Реакция на чекбокс У вас есть существующая рабочая БД?

  $('input[name=existDB]').live("click", function(){
    if($('input[name=existDB]').attr("checked")=="checked"){
      $("#siteParam").hide();
      $('.attention').hide();
    }else{
      $("#siteParam").show();
      $('.attention').show();
    }
  });

  // Реакция на чекбокс Показать пароль
  $('input[name=showPass]').live("click", function(){
    var valPass = $('input[name=pass]').attr("value");

    if($('input[name=showPass]').attr("checked")=="checked"){
      $('#rePass').hide("fast");
      $('input[name=pass]').replaceWith("<input type='text' name='pass' class='product-name-input'></td>");
    }else{
      $('#rePass').show("fast");
      $('input[name=pass]').replaceWith("<input type='password' name='pass' class='product-name-input'></td>");
    }

    $('input[name=pass]').attr("value", valPass);
  });
</script>
</head>
<body>

<div class="install-body">
  <img src="<?php echo SITE ?>/install/images/logo.png" alt="" />
  <div class="center-wrapper step2">
    
    <div class="widget-table-title">
      <h4 class="product-table-icon">Установка Moguta.CMS</h4>
    </div>
    <?php if($msg) echo $msg;
      if(!$libError):?>
    <div class="install-text">
      <form class="add-img-form" method="post" enctype="multipart/form-data" action="" encoding="multipart/form-data" disable>
        <h3 class="bd-title">Настройки подключения к базе данных</h3>
        <div class="clear"></div>
        <h6 style="margin-top:-10px; position:absolute;">Все параметры вы можете уточнить у вашего хостинг-провайдера.</h6>
        <div class="clear"></div>
        <ul class="product-text-inputs">
          <li>
            <label>
              <span class="custom-text">Имя сервера базы<span class="red-star">*</span>:</span>
              <input type="text" name="host" class="product-name-input" value="localhost">
            </label>
          </li>
          <li>
            <label>
              <span class="custom-text">Пользователь базы<span class="red-star">*</span>:</span>
              <input type="text" name="user" class="product-name-input" value="<?php echo $user?>" placeholder="">
            </label>
          </li>
          <li>
            <label>
              <span class="custom-text">Пароль к базе</span>
              <input type="password" name="password" class="product-name-input" value="<?php echo $password?>">
            </label>
          </li>
          <li>
            <label>
              <span class="custom-text">Название базы<span class="red-star">*</span>:</span>
              <input type="text" name="nameDB" class="product-name-input" value="<?php echo $nameDB?>">
            </label>
          </li>
          <li>
            <label>
              <span class="custom-text">Префикc таблиц:</span>
              <input type="text" name="prefix" class="product-name-input" value="<?php echo $prefix?>">
            </label>
          </li>
        </ul>
        <div class="attention"><strong>Внимание:</strong> указанная база данных должна быть пустой!</div>
		<button class="save-settings" type="submit" name="step3" value="go"><span>Далее</span></button>
		<div class="clear"></div>
    </form>	
    </div>
    <?php endif?>
  </div>
</div>


</body>
</html>