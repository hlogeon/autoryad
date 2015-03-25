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
	   <h3 class="bd-title">Предварительные настройки магазина</h3>
      
        <p class="checkbox-text">
          <!--<label>
            <input type="checkbox" name ="existDB" >Не заполнять следующий блок настроек
          </label>-->
        </p>
        <div id="siteParam">      
          <ul class="product-text-inputs">
            <li>
              <label>
                <span class="custom-text">Название сайта<span class="red-star">*</span>:</span>
                <input type="text" name="siteName" value="<?php echo $siteName?>" class="product-name-input" >
              </label>
            </li>
			<li>
			<p class="radiobotton-text">
            <label>
              <input type="checkbox" name = "engineType" value = "test" <?php echo $checkedTest ?>>
              Заполнить сайт демострационными данными.
            </label> 
			</li>			
          </p>
          </ul>
          <h3 class="title">Настройки администратора</h3>
          <ul class="product-text-inputs">
            <li>
              <label>
                <span class="custom-text">Электронный адрес<span class="red-star">*</span>:</span>
                <input type="text" name="email" value="<?php echo $adminEmail?>" class="product-name-input" >
              </label>
            </li>
            <li>
              <label>
               <span class="custom-text">Пароль<span class="red-star">*</span>:</span>
               <input type="password" name="pass" class="product-name-input" >
             </label>
             <i>Не менее 5 символов</i>
            </li>
            <li id="rePass">
              <label>
                <span class="custom-text">Повтор пароля<span class="red-star">*</span>:</span>
                <input type="password" name="rePass" class="product-name-input" >
              </label>
            </li>
            <li>
              <label>
                <input type="checkbox" name ="showPass" class="checkit" >
                <span class="custom-text">Показать пароль</span>
              </label>
            </li>
          </ul>
        </div>
        <button class="save-settings" type="submit" name="step4" value="go"><span>Установить</span></button>
        <div class="clear"></div>
		
		  <input type="hidden" name="host" class="product-name-input" value="<?php echo $host?>">
	    <input type="hidden" name="user" class="product-name-input" value="<?php echo $user?>">
      <input type="hidden" name="password" class="product-name-input" value="<?php echo $password?>">
      <input type="hidden" name="nameDB" class="product-name-input" value="<?php echo $nameDB?>">
      <input type="hidden" name="prefix" class="product-name-input" value="<?php echo $prefix?>">
      </form>
    </div>
    <?php endif?>
  </div>
</div>


</body>
</html>