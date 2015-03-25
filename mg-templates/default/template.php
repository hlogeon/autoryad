<?php
/**
 * Файл template.php является каркасом шаблона, содержит основную верстку шаблона.
 * 
 *   
 *   Получить подробную информацию о доступных данных в массиве $data, можно вставив следующую строку кода в верстку файла.
 *   <code>     
 *    <?php viewData($data); ?>  
 *   </code>
 * 
 *   Также доступны вставки, для вывода верстки из папки layout    
 *   <code> 
 *      <?php layout('cart'); ?>      // корзина
 *      <?php layout('auth'); ?>      // личный кабинет
 *      <?php layout('widget'); ?>    // виджиеы и коды счетчиков
 *      <?php layout('compare'); ?>   // информер товаров для сравнения
 *      <?php layout('content'); ?>   // содержание открытой страницы
 *      <?php layout('leftmenu'); ?>  // левое меню с категориями
 *      <?php layout('topmenu'); ?>   // верхнее горизонтаьное меню
 *      <?php layout('contacts'); ?>  // контакты в шапке
 *      <?php layout('search'); ?>    // форма для поиска
 *      <?php layout('content'); ?>   // вывод контента сгенерированного движком
 *   </code>
 *   @author Авдеев Марк <mark-avdeev@mail.ru>
 *   @package moguta.cms
 *   @subpackage Views
 */
?>

<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width">
        <?php mgMeta(); ?>
         <link href="<?php echo PATH_SITE_TEMPLATE ?>/css/mobile.css" rel="stylesheet" type="text/css" />
        <script type="text/javascript" src="<?php echo PATH_SITE_TEMPLATE ?>/js/script.js"></script>
               
        <!--[if IE 9]>
          <link href="<?php echo PATH_SITE_TEMPLATE ?>/css/ie9.css" rel="stylesheet" type="text/css" />
        <![endif]-->
        <!--[if IE 8]>
          <link href="<?php echo PATH_SITE_TEMPLATE ?>/css/ie8.css" rel="stylesheet" type="text/css" />
        <![endif]-->
        <!--[if IE 7]>
          <link href="<?php echo PATH_SITE_TEMPLATE ?>/css/ie7.css" rel="stylesheet" type="text/css" />
        <![endif]-->
   
    </head>
    <body <?php backgroundSite();?>>
<div class="mg-layer" style="display: none"></div>

    <div class="wrapper">
        <div class="container">    
            <!--Шапка сайта-->
            <div class="header">
                <!--Вывод корзины-->
                <?php layout('cart'); ?>
                <!--/Вывод корзины-->

                <!--Вывод авторизации-->
                <div class="top-auth-block">
                    <?php layout('auth'); ?>
                </div>
                <!--/Вывод авторизации-->

                <!--Вывод логотипа сайта-->
                <div class="logo-block">
                    <a href="<?php echo SITE ?>">
                        <?php echo mgLogo(); ?>
                    </a>
                </div>
                <!--/Вывод логотипа сайта-->

                <!--Вывод реквизитов сайта-->
                <?php layout('contacts'); ?>
                <!--/Вывод реквизитов сайта-->

                <!--Вывод аякс поиска-->
                <?php layout('search'); ?>
                <!--/Вывод аякс поиска-->

                <div class="clear">&nbsp;</div>

                <!--Вывод верхнего меню-->
                <div class="top-menu">
                    <?php layout('topmenu'); ?>
                </div>
                <!--/Вывод верхнего меню-->
            </div>            
            <!--/Шапка сайта-->

            <!--Панель для мобильных устройств-->
            <div class="mobile-top-panel">
                <a href="javascript:void(0);" class="show-menu-toggle"></a>
                <div class="mobile-top-menu">
                    <?php layout('topmenu'); ?>
                </div>
                <div class="mobile-cart">
                    <a href="<?php echo SITE ?>/cart">
                        <div class="cart small-cart-icon">
                            <div class="cart-inner">
                                <ul class="cart-list">
                                    <li class="cart-qty">
                                        <span class="countsht">
                                            <?php echo $data['cartCount'] ? $data['cartCount'] : 0 ?>
                                        </span> шт.
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            <!--/Панель для мобильных устройств-->

        <!--Категории для мобильных устройств-->
        <div class="mobile-categories">
            <h2>Категории товаров <span class="mobile-white-arrow"></span></h2>
            <?php layout('leftmenu');  ?>
        </div>
        <!--/Категории для мобильных устройств--> 
	
		<!--Вывод горизонтального меню-->
			<?php
			if(MG::getSetting('horizontMenu')=="true"){
			   layout('horizontmenu');			  
			   if(MG::get('controller')=="controllers_catalog"): ?>
			   <div class="left-block">
				 <?php filterCatalog();?>
			   </div>
			   <?php endif; ?>
			<?php } else{
			?>
			<!--/Вывод левого меню-->
			<?php if((!URL::isSection(null)||(MG::getOption('catalogIndex')=='true')) && !URL::isSection('compare')): ?>
			<div class="left-block">
				<div class="menu-block">
				<h2 class="cat-title">Категории</h2>
				<!-- Вывод левого меню-->
					<?php layout('leftmenu'); ?>
				<!--/Вывод левого меню-->
				
				</div>	
				<?php filterCatalog();?> 
			</div>
			<?php endif; ?>
			<?php } ?>

			<!--Центральная часть сайта-->
			<div class="center">
				<?php layout('content'); ?>
			</div>
			<!--/Центральная часть сайта-->
			<div class="clear">&nbsp;</div>
		</div>
		<div class="h-footer"></div>

        <!--Индикатор сравнения товаров-->
        <?php layout('compare'); ?>
        <!--/Индикатор сравнения товаров-->
    </div>
    <!--Подвал сайта-->
    <div class="footer">
        <div class="copyright"> <?php echo date('Y') ?>  год. Все права защищены.</div>
      <?php copyrightMoguta();?>
    </div>
    <!--/Подвал сайта-->

    <!--Коды счетчиков-->
    <?php layout('widget'); ?>
    <!--/Коды счетчиков-->    
    
</body>
</html>