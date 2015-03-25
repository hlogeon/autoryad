/**
 * Подключается в карточке товара
 */
$(document).ready(function() {
    //Выбирает текущий тумбнейл
    $('.slides-inner a').click(function() {
        $(this).each(function() {
            $('.slides-inner a').removeClass('active-item');
            $(this).addClass('active-item');
        });
    });

    //Инициализация fancybox
    $(".close-order, a.fancy-modal").fancybox({
        'overlayShow': false,
        tpl: {
            next: '<a title="Вперед" class="fancybox-nav fancybox-next" href="javascript:;"><span></span></a>',
            prev: '<a title="Назад" class="fancybox-nav fancybox-prev" href="javascript:;"><span></span></a>'
        }
    });

 //Слайдер картинок в карточке товаров
  $('.main-product-slide').bxSlider({
    pagerCustom: '.slides-inner',
    controls: false,
    mode: 'fade',
    useCSS: false
  });

    //Слайдер тумбнейлов
    $('.slides-inner').bxSlider({
        minSlides: 3,
        maxSlides: 3,
        slideWidth: 75,
        pager: false,
        slideMargin: 10,
        useCSS: false
    });
    
    //при наведении на фото, появляется лупа для увеличения 
 /* $('a.fancy-modal').hover(
    function() {
      $('.zoom').stop().fadeTo(200, 1.0);
    },
    function() {
      $('.zoom').stop().fadeTo(200, 0.0);
    }
  );
    */
 });  