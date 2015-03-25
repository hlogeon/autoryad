$(document).ready(function() {

  var slider_width = $('.menu-block').width() + 2;
  var deviceWidth = $(window).width();

  /*Mobile menu*/
  $(".mobile-categories h2").on("click", function() {
    $(this).toggleClass("open-menu");
    $(this).next(".cat-list").slideToggle("fast");
  });

  $(".cat-list li .slider_btn, .mobile-top-panel .top-menu-list li .slider_btn").on("click", function() {
    $(this).toggleClass("opened");
    $(this).parent().find(".sub_menu:first").slideToggle("fast");
  });

  $(".show-menu-toggle").on("click", function() {
    $(this).parent().find(".mobile-top-menu").slideToggle("fast");
    ;
  });

  /*Fix mobile top menu position if login admin*/
  if ($("body").hasClass("admin-on-site")) {
    $("body").find(".mobile-top-panel").addClass("position-fix");
  }

 /*
  $(function() { // Когда страница загрузится
    $('.top-menu-list li a').each(function() { // получаем все нужные нам ссылки
      var location = window.location.href; // получаем адрес страницы
      var link = this.href;                // получаем адрес ссылки
      if (location == link) {               // при совпадении адреса ссылки и адреса окна
        $(this).addClass('active-menu-item');  //добавляем класс
      }
    });
  });*/
});