<?php
 /**
 *  Файл представления Index - выводит сгенерированную движком информацию на главной странице магазина.
 *  В этом файле доступны следующие данные:
 *   <code>     
 *    $data['recommendProducts'] => Массив рекомендуемых товаров 
 *    $data['newProducts'] => Массив товаров новинок 
 *    $data['saleProducts'] => Массив товаров распродажи
 *    $data['titeCategory'] => Название категории
 *    $data['cat_desc'] => Описание категории
 *    $data['meta_title'] => Значение meta тега для страницы 
 *    $data['meta_keywords'] => Значение meta_keywords тега для страницы
 *    $data['meta_desc'] => Значение meta_desc тега для страницы 
 *    $data['currency'] => Текущая валюта магазина
 *    $data['actionButton'] => тип кнопки в миникарточке товара
 *   </code>
 *   
 *   Получить подробную информацию о каждом элементе массива $data, можно вставив следующую строку кода в верстку файла.
 *   <code>     
 *    <php viewData($data['saleProducts']); ?>  
 *   </code>
 * 
 *   Вывести содержание элементов массива $data, можно вставив следующую строку кода в верстку файла.
 *   <code>     
 *    <php echo $data['saleProducts']; ?>  
 *   </code>
 * 
 *   <b>Внимание!</b> Файл предназначен только для форматированного вывода данных на страницу магазина. Категорически не рекомендуется выполнять в нем запросы к БД сайта или реализовывать сложую программную логику логику.
 *   @author Авдеев Марк <mark-avdeev@mail.ru>
 *   @package moguta.cms
 *   @subpackage Views
 */

 
  // Установка значений в метатеги title, keywords, description.
  mgSEO($data);
  //viewData($data['newProducts']);
?>

<?php mgAddMeta('<script type="text/javascript" src="'.SCRIPT.'jquery.bxslider.min.js"></script>'); ?>

<script>
$(document).ready(function() {   
  //Слайдер новинок на главной странице
   $('.m-p-products-slider-start').bxSlider({
    minSlides: 4,
    maxSlides: 4,
    slideWidth: 222,
    slideMargin: 15,
    moveSlides: 1,
    pager: false,
    auto: false,
    pause: 6000,
    useCSS: false,
    touchEnabled: false
  }); 
});
</script>

<?php if(!empty($data['newProducts'])): ?>

<div class="m-p-products">
	<h2 class="m-p-new-products-title"><a href="<?php echo SITE; ?>/group?type=latest" class="text-decoration-none">Новинки</a></h2>
	<div class="m-p-products-slider">
		<div class="<?php echo count($data['newProducts'])>3?"m-p-products-slider-start":"" ?>">
			<?php foreach ($data['newProducts'] as $item): ?>
			<div class="product-wrapper">
				<div class="product-image">				
					<a href="<?php echo SITE ?>/<?php echo isset($item["category_url"]) ? $item["category_url"] : 'catalog' ?>/<?php echo htmlspecialchars($item["product_url"]) ?>">
				    <?php echo mgImageProduct($item); ?>
					</a>
				</div>

				<div class="product-name">
					<a href="<?php echo SITE ?>/<?php echo isset($item["category_url"]) ? $item["category_url"] : 'catalog' ?>/<?php echo htmlspecialchars($item["product_url"]) ?>"><?php echo $item["title"] ?></a>
				</div>

				<span class="product-price"><?php echo priceFormat($item["price"]) ?> <?php echo $data['currency']; ?></span>
				<!--Кнопка, кототорая меняет свое значение с "В корзину" на "Подробнее"-->
				<?php echo $item[$data['actionButton']]?>  
        <?php echo $item['actionCompare']?>  
        
			</div>
			<?php endforeach; ?>
		</div>
	</div>
	<div class="clear"></div>
</div>
<?php endif; ?> 

<?php if(!empty($data['recommendProducts'])): ?>
<div class="m-p-recommended-products">
 <h2 class="m-p-recommended-products-title"> <a href="<?php echo SITE; ?>/group?type=recommend" class="text-decoration-none">Рекомендуемые товары</a></h2>
	<div class="m-p-products-slider">
		<div class="<?php echo count($data['recommendProducts'])>3?"m-p-products-slider-start":"" ?>">
			<?php foreach ($data['recommendProducts'] as $item): ?>
        
				<div class="product-wrapper">
					<div class="product-image">					
						<a href="<?php echo SITE ?>/<?php echo isset($item["category_url"]) ? $item["category_url"] : 'catalog' ?>/<?php echo htmlspecialchars($item["product_url"]) ?>">
						<?php echo mgImageProduct($item); ?>
            </a>
					</div>

					<div class="product-name">
						<a href="<?php echo SITE ?>/<?php echo isset($item["category_url"]) ? $item["category_url"] : 'catalog' ?>/<?php echo htmlspecialchars($item["product_url"]) ?>"><?php echo $item["title"] ?></a>
					</div>

					<span class="product-price"><?php echo priceFormat($item["price"]) ?> <?php echo $data['currency']; ?></span>
					<!--Кнопка, кототорая меняет свое значение с "В корзину" на "Подробнее"-->
					<?php echo $item[$data['actionButton']]?>   
          <?php echo $item['actionCompare']?>  
				</div>
			<?php endforeach; ?> 
		</div>
	</div>
<div class="clear"></div>
</div>
<?php endif; ?> 

<?php if(!empty($data['saleProducts'])): ?>
<div class="m-p-products">
  <h2 class="m-p-sale-products-title"><a href="<?php echo SITE; ?>/group?type=sale" class="text-decoration-none">Распродажа</a></h2></a>
	<div class="m-p-products-slider">
		<div class="<?php echo count($data['saleProducts'])>3?"m-p-products-slider-start":"" ?>">
			<?php foreach ($data['saleProducts'] as $item): ?>
				<div class="product-wrapper">
					<div class="product-image">					
						<a href="<?php echo SITE ?>/<?php echo isset($item["category_url"]) ? $item["category_url"] : 'catalog' ?>/<?php echo $item["product_url"] ?>">
						<?php echo mgImageProduct($item); ?>
            </a>
					</div>

					<div class="product-name">
						<a href="<?php echo SITE ?>/<?php echo isset($item["category_url"]) ? $item["category_url"] : 'catalog' ?>/<?php echo $item["product_url"] ?>"><?php echo $item["title"] ?></a>
					</div>

					<span class="product-price"><?php echo priceFormat($item["price"]) ?> <?php echo $data['currency']; ?></span>
					<!--Кнопка, кототорая меняет свое значение с "В корзину" на "Подробнее"-->
					<?php echo $item[$data['actionButton']]?>  
          <?php echo $item['actionCompare']?>  
				</div>
			<?php endforeach; ?>
		</div>
	</div>
<div class="clear"></div>
</div>
<?php endif; ?> 

<div class="cat-desc">
	<?php echo $data['cat_desc'] ?>
</div>

