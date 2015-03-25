<?php
$damp = array(
  "DROP TABLE IF EXISTS `".$prefix."category`, `".$prefix."category_user_property`, `".$prefix."delivery`, `".$prefix."delivery_payment_compare`, `".$prefix."order`, `".$prefix."page`, `".$prefix."payment`, `".$prefix."plugins`, `".$prefix."product`, `".$prefix."product_user_property`, `".$prefix."product_variant`, `".$prefix."property`, `".$prefix."setting`, `".$prefix."user`",
  "SET names utf8",
  
  "CREATE TABLE IF NOT EXISTS `".$prefix."cache` (
  `date_add` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `lifetime` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `value` longtext NOT NULL,
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;",


"CREATE TABLE IF NOT EXISTS `".$prefix."category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `parent` int(11) NOT NULL,
  `parent_url` varchar(255) NOT NULL,
  `sort` int(11) NOT NULL,
  `html_content` longtext NOT NULL,
  `meta_title` varchar(255) NOT NULL,
  `meta_keywords` varchar(512) NOT NULL,
  `meta_desc` text NOT NULL,
  `invisible` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Не выводить в меню',
  `1c_id` varchar(255) NOT NULL,
  `image_url` text NOT NULL,
  `rate` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `1c_id` (`1c_id`),
  KEY `url` (`url`),
  KEY `parent_url` (`parent_url`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=19",


"CREATE TABLE IF NOT EXISTS `".$prefix."category_user_property` (
  `category_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8",


"CREATE TABLE IF NOT EXISTS `".$prefix."delivery` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `cost` double NOT NULL,
  `description` text DEFAULT NULL,
  `activity` int(1) NOT NULL DEFAULT '0',
  `free` double NOT NULL COMMENT 'Бесплатно от',
  `date` int(1) NOT NULL,
  `sort` int(11) NOT NULL,
  `ymarket` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='таблица способов доставки товара' AUTO_INCREMENT=4",
  "INSERT INTO `".$prefix."delivery` (`id`, `name`, `cost`, `description`, `activity`, `free`, `date`, `sort`, `ymarket`) VALUES  
    (1, 'Курьер', 700, 'Курьерская служба', 1, 0, 1, 1, 1),
    (2, 'Почта', 200, 'Почта России', 1, 0, 0, 2, 0),
    (3, 'Без доставки', 0, 'Самовывоз', 1, 0, 0, 3, 0)",

"CREATE TABLE IF NOT EXISTS `".$prefix."delivery_payment_compare` (
  `payment_id` int(10) DEFAULT NULL,
  `delivery_id` int(10) DEFAULT NULL,
  `compare` int(1) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8",


"INSERT INTO `".$prefix."delivery_payment_compare` (`payment_id`, `delivery_id`, `compare`) VALUES
(1, 1, 1),
(5, 1, 1),
(2, 2, 1),
(3, 1, 1),
(1, 2, 1),
(2, 1, 1),
(3, 2, 1),
(4, 2, 1),
(4, 3, 1),
(3, 3, 1),
(2, 3, 1),
(1, 3, 1),
(4, 1, 1),
(5, 2, 1),
(6, 1, 1),
(6, 2, 1),
(6, 3, 1),
(5, 3, 1),
(7, 1, 1),
(7, 2, 1),
(7, 3, 1),
(8, 1, 1),
(8, 2, 1),
(8, 3, 1),
(9, 1, 1),
(9, 2, 1),
(9, 3, 1),
(10, 1, 1),
(10, 2, 1),
(10, 3, 1)",

"CREATE TABLE IF NOT EXISTS `".$prefix."order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `updata_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `add_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `close_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user_email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `address` text,
  `summ` varchar(255) DEFAULT NULL COMMENT 'Общая сумма товаров в заказе ',
  `order_content` longtext,
  `delivery_id` int(11) unsigned DEFAULT NULL,
  `delivery_cost` double DEFAULT NULL COMMENT 'Стоимость доставки',
  `payment_id` int(11) DEFAULT NULL,
  `paided` int(1) NOT NULL DEFAULT '0',
  `status_id` int(11) DEFAULT NULL,
  `comment` text,
  `confirmation` varchar(255) DEFAULT NULL,
  `yur_info` text NOT NULL,
  `name_buyer` text NOT NULL,
  `date_delivery` text,
  `ip` text NOT NULL,
  `number` varchar(32) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1",

"CREATE TABLE IF NOT EXISTS `".$prefix."page` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_url` varchar(255) NOT NULL,
  `parent` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `html_content` longtext NOT NULL,
  `meta_title` varchar(255) NOT NULL,
  `meta_keywords` varchar(1024) NOT NULL,
  `meta_desc` text NOT NULL,
  `sort` int(11) NOT NULL,
  `print_in_menu` tinyint(4) NOT NULL DEFAULT '0',
  `invisible` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Не выводить в меню',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6",


"INSERT INTO `".$prefix."page` (`id`, `parent_url`, `parent`, `title`, `url`, `html_content`, `meta_title`, `meta_keywords`, `meta_desc`, `sort`, `print_in_menu`, `invisible`) VALUES
(1, '', 0, 'Главная', 'index', '<h3 style=\"text-align: center;\">Добро пожаловать в наш интернет-магазин!</h3><div><p>Мы стабильная и надежная компания, с каждым днем наращиваем свой потенциал. Имеем огромный опыт в сфере корпоративных продаж, наши менеджеры готовы предложить Вам высокий уровень сервиса, грамотную консультацию, выгодные условия работы и широкий спектр цветовых решений. В число наших постоянных клиентов входят крупные компании.</p><p>Наши товары производятся только из самых качественных материалов!</p><p>Отдел корпоративных продаж готов предложить Вам персонального менеджера, грамотную консультацию, доставку на следующий день после оплаты, сертификаты на всю продукцию, индивидуальный метод работы.</p><p>Отдельным направлением является работа с частными лицами с оперативной доставкой, низкими ценами и высоким качеством обслуживания.</p><p>Главное для нас — своевременно удовлетворять потребности наших клиентов всеми силами и доступными нам средствами. Работая с нами, Вы гарантированно приобретаете только оригинальный товар подлинного качества.</p><p>Мы работаем по всем видам оплат. Только приобретая товар у официального дилера, Вы застрахованы от подделок. Будем рады нашему долгосрочному сотрудничеству.</p><p>** Информация представленная на сайте является демонстрационной для ознакомления с Moguta.CMS. <a data-cke-saved-href=\"http://moguta.ru/\" href=\"http://moguta.ru/\">Moguta.CMS - простая cms для интернет-магазина.</a></p></div>', 'Главная', 'Главная', '', 5, 0, 1),
(2, '', 0, 'Доставка и оплата', 'dostavka', '<div><h1 class=\"new-products-title\">Доставка и оплата</h1><p><strong>Курьером по Москве</strong></p><p>Доставка осуществляется по Москве бесплатно, если сумма заказа составляет свыше 3000 руб.  Стоимость доставки меньше чем на сумму 3000 <span style=\"font-size: 12px;\">руб</span>. Составляет 700 руб. Данный способ доставки дает вам возможность получить товар прямо в руки, курьером по <span style=\"font-size: 12px;\">Москве </span>. Срок доставки до 24 часов с момента заказа товара в интернет - магазине.</p><p><strong>Доставка по России</strong></p><p>Доставка по <span style=\"font-size: 12px;\">России</span>осуществляется с помощью почтово – курьерских служб во все регионы <span style=\"font-size: 12px;\">России</span>. Стоимость доставки зависит от региона и параметров товара. Рассчитать стоимость доставки Вы сможете на официальном сайте почтово – курьерской службы Почта-<span style=\"font-size: 12px;\">России</span> и т.д. Сроки доставки составляет до 3-х дней с момента заказа товара в интернет – магазине.</p><h2>Способы оплаты:</h2><p><strong>Наличными: </strong>Оплатить заказ товара Вы сможете непосредственно курьеру в руки при получение товара. </p><p><strong>Наложенным платежом:</strong> Оплатить заказ товара Вы сможете наложенным платежом при получение товара на складе. С данным видом оплаты Вы оплачиваете комиссию за пересылку денежных средств. </p><p><strong>Электронными деньгами:</strong> VISA, Master Card, Yandex.Деньги, Webmoney, Qiwi и др.</p></div><div></div><div></div><div></div>', 'Доставка', 'Доставка', 'Доставка осуществляется по Москве бесплатно, если сумма заказа составляет свыше 3000 руб.  Стоимость доставки меньше чем на сумму 3000 руб. Составляет 700 руб.', 2, 1, 0),
(3, '', 0, 'Обратная связь', 'feedback', '<p>Свяжитесь с нами, по средствам формы обратной связи представленной ниже. Вы можете задать любой вопрос, и после отправки сообщения наш менеджер свяжется с вами.</p>', 'Обратная связь', 'Обратная связь', 'Свяжитесь с нами, по средствам формы обратной связи представленной ниже. Вы можете задать любой вопрос, и после отправки сообщения наш менеджер свяжется с вами.', 3, 1, 0),
(4, '', 0, 'Контакты', 'contacts', '<h1 class=\"new-products-title\">Контакты</h1><p><strong>Наш адрес </strong>г. Санкт-Петербург Невский проспект, дом 3</p><p><strong>Телефон отдела продаж </strong>8 (555) 555-55-55 </p><p>Пн-Пт 9.00 - 19.00</p><p>Электронный ящик: <span style=\"line-height: 1.6em;\">info@sale.ru</span></p><p><strong>Мы в социальных сетях</strong></p><p></p><p style=\"line-height: 20.7999992370605px;\"><strong>Мы в youtoube</strong></p><p style=\"line-height: 20.7999992370605px;\"></p>', 'Контакты', 'Контакты', 'Мы в социальных сетях  Мы в youtoube ', 4, 1, 0),
(5, '', 0, 'Каталог', 'catalog', '<p>В каталоге нашего магазина вы найдете не только качественные и полезные вещи, но и абсолютно уникальные новинки из мира цифровой индустрии. </p>', 'Каталог', 'Каталог', '', 1, 1, 0)
",


"CREATE TABLE IF NOT EXISTS `".$prefix."payment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(1024) NOT NULL,
  `activity` int(1) NOT NULL DEFAULT '0',
  `paramArray` text DEFAULT NULL,
  `urlArray` varchar(1024) DEFAULT NULL,
  `sort` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=11",


"INSERT INTO `".$prefix."payment` (`id`, `name`, `activity`, `paramArray`, `urlArray`, `sort`) VALUES
(1, 'WebMoney', 1, '{\"Номер кошелька\":\"\",\"Секретный ключ\":\"\"}', '{\"result URL:\":\"/payment?id=1&pay=result\",\"success URL:\":\"/payment?id=1&pay=success\",\"fail URL:\":\"/payment?id=1&pay=fail\"}', 1),
(2, 'Яндекс.Деньги', 1, '{\"Номер счета\":\"\",\"Секретный ключ\":\"\"}', '{\"result URL:\":\"/payment?id=2&pay=result\",\"success URL:\":\"/payment?id=2&pay=success\",\"fail URL:\":\"/payment?id=2&pay=fail\"}', 2),
(3, 'Наложенный платеж', 1, '{\"Примечание\":\"\"}', '', 3),
(4, 'Наличные (курьеру)', 1, '{\"Примечание\":\"\"}', '', 4),
(5, 'ROBOKASSA', 1, '{\"Логин\":\"\",\"пароль 1\":\"\",\"пароль 2\":\"\"}', '{\"result URL:\":\"/payment?id=5&pay=result\",\"success URL:\":\"/payment?id=5&pay=success\",\"fail URL:\":\"/payment?id=5&pay=fail\"}', 5),
(6, 'QIWI', 1, '{\"Логин в Qiwi\":\"\",\"Пароль в Qiwi\":\"\"}', '{\"result URL:\":\"/payment?id=6&pay=result\",\"success URL:\":\"/payment?id=6&pay=success\",\"fail URL:\":\"/payment?id=6&pay=fail\"}', 6),
(7, 'Оплата по реквизитам', 1, '{\"Юридическое лицо\":\"\", \"ИНН\":\"\",\"КПП\":\"\", \"Адрес\":\"\", \"Банк получателя\":\"\", \"БИК\":\"\",\"Расчетный счет\":\"\",\"Кор. счет\":\"\"}', '', 7),
(8, 'Интеркасса', 1, '{\"Идентификатор кассы\":\"\",\"Секретный ключ\":\"\"}', '{\"result URL:\":\"/payment?id=8&pay=result\",\"success URL:\":\"/payment?id=8&pay=success\",\"fail URL:\":\"/payment?id=8&pay=fail\"}', 8),
(9, 'PayAnyWay', 1, '{\"Номер расширенного счета\":\"\",\"Код проверки целостности данных\":\"\"}', '{\"result URL:\":\"/payment?id=9&pay=result\",\"success URL:\":\"/payment?id=9&pay=success\",\"fail URL:\":\"/payment?id=9&pay=fail\"}', 9),
(10, 'PayMaster', 1, '{\"ID магазина\":\"\",\"Секретный ключ\":\"\"}', '{\"result URL:\":\"/payment?id=10&pay=result\",\"success URL:\":\"/payment?id=10&pay=success\",\"fail URL:\":\"/payment?id=10&pay=fail\"}', 10),
(11, 'AlfaBank', '1',  '{\"Логин\":\"\",\"Пароль\":\"\"}',  '{\"result URL:\":\"/payment?id=11&pay=result\",\"success URL:\":\"/payment?id=11&pay=success\",\"fail URL:\":\"/payment?id=11&pay=fail\"}' , 11),
(12, 'Другой способ оплаты', 1, '{\"Примечание\":\"\"}', '', 12),
(13, 'Другой способ оплаты', 1, '{\"Примечание\":\"\"}', '', 13)",

"CREATE TABLE IF NOT EXISTS `".$prefix."plugins` (
  `folderName` varchar(255) NOT NULL,
  `active` tinyint(1) NOT NULL,
  UNIQUE KEY `name` (`folderName`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8",
  
"INSERT INTO `".$prefix."plugins` (`folderName`, `active`) VALUES
('breadcrumbs', 1)",

"CREATE TABLE IF NOT EXISTS `".$prefix."product` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sort` int(11) NOT NULL,
  `cat_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `price` double NOT NULL,
  `url` varchar(255) NOT NULL,
  `image_url` TEXT NOT NULL,
  `code` varchar(255) NOT NULL,
  `count` int(11) NOT NULL DEFAULT '0',
  `activity` tinyint(1) NOT NULL,
  `meta_title` varchar(255) NOT NULL,
  `meta_keywords` varchar(1024) NOT NULL,
  `meta_desc` text NOT NULL,
  `old_price` varchar(255) NOT NULL,
  `recommend` tinyint(4) NOT NULL DEFAULT '0',
  `new` tinyint(4) NOT NULL DEFAULT '0',
  `related` text NOT NULL,
  `inside_cat` text NOT NULL,
  `1c_id` varchar(255) NOT NULL,
  `weight` double NOT NULL,
  `link_electro` varchar(1024) NOT NULL,
  `currency_iso` varchar(255) NOT NULL,
  `price_course` double NOT NULL,
  `image_title` text NOT NULL,
  `image_alt` text NOT NULL,
  `yml_sales_notes` text NOT NULL,
  `count_buy` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `1c_id` (`1c_id`),
  FULLTEXT KEY `SEARCHPROD` (`title`,`description`,`code`,`meta_title`,`meta_keywords`,`meta_desc`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=60 ",


"CREATE TABLE IF NOT EXISTS `".$prefix."product_user_property` (
  `product_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `value` text NOT NULL,
  `product_margin` text NOT NULL COMMENT 'наценка продукта',
  `type_view` enum('checkbox','select','radiobutton','') NOT NULL DEFAULT 'select',
  KEY `product_id` (`product_id`),
  KEY `property_id` (`property_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Таблица пользовательских свойств продуктов'",


"CREATE TABLE IF NOT EXISTS `".$prefix."product_variant` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `title_variant` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL,
  `sort` int(11) NOT NULL,
  `price` double NOT NULL,
  `old_price` varchar(255) NOT NULL,
  `count` int(11) NOT NULL,
  `code` varchar(255) NOT NULL,
  `activity` tinyint(1) NOT NULL,
  `weight` double NOT NULL,
  `currency_iso` varchar(255) NOT NULL,
  `price_course` double NOT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  FULLTEXT KEY `title_variant` (`title_variant`),
  FULLTEXT KEY `code` (`code`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=21",


"CREATE TABLE IF NOT EXISTS `".$prefix."property` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `default` text NOT NULL,
  `data` text NOT NULL,
  `all_category` tinyint(1) NOT NULL,
  `activity` int(1) NOT NULL DEFAULT '0',
  `sort` int(11) NOT NULL,
  `filter` tinyint(1) NOT NULL DEFAULT '0',
  `description` TEXT NOT NULL, 
  `type_filter` VARCHAR(32) NULL,
  `1c_id` VARCHAR(255),
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=51",


"CREATE TABLE IF NOT EXISTS `".$prefix."setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `option` varchar(255) NOT NULL,
  `value` longtext NOT NULL,
  `active` varchar(1) NOT NULL DEFAULT 'N',
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=70",

"INSERT INTO `".$prefix."setting` (`id`, `option`, `value`, `active`, `name`) VALUES
(1, 'sitename', 'localhost', 'Y', 'SITE_NAME'),
(2, 'adminEmail', '', 'Y', 'EMAIL_ADMIN'),
(3, 'templateName', 'default', 'Y', 'SITE_TEMPLATE'),
(4, 'countСatalogProduct', '6', 'Y', 'CATALOG_COUNT_PAGE'),
(5, 'currency', 'руб.', 'Y', 'SETTING_CURRENCY'),
(6, 'staticMenu', 'true', 'N', 'SETTING_STATICMENU'),
(7, 'orderMessage', 'Оформлен заказ № #ORDER# на сайте #SITE#', 'Y', 'TPL_EMAIL_ORDER'),
(8, 'downtime', 'false', 'N', 'DOWNTIME_SITE'),
(9, 'currentVersion', '', 'N', 'INFO_CUR_VERSION'),
(10, 'timeLastUpdata', '', 'N', 'LASTTIME_UPDATE'),
(11, 'title', ' Лучший магазин | Moguta.CMS', 'N', 'SETTING_PAGE_TITLE'),
(12, 'countPrintRowsProduct', '20', 'Y', 'ADMIN_COUNT_PROD'),
(13, 'languageLocale', 'ru_RU', 'N', 'ADMIN_LANG_LOCALE'),
(14, 'countPrintRowsPage', '10', 'Y', 'ADMIN_COUNT_PAGE'),
(15, 'themeColor', '.default', 'N', 'ADMIN_THEM_COLOR'),
(16, 'themeBackground', 'bg_7', 'N', 'ADMIN_THEM_BG'),
(17, 'countPrintRowsOrder', '20', 'N', 'ADMIN_COUNT_ORDER'),
(18, 'countPrintRowsUser', '30', 'N', 'ADMIN_COUNT_USER'),
(19, 'licenceKey', '', 'N', 'LICENCE_KEY'),
(20, 'mainPageIsCatalog', 'true', 'Y', 'SETTING_CAT_ON_INDEX'),
(21, 'countNewProduct', '5', 'Y', 'COUNT_NEW_PROD'),
(22, 'countRecomProduct', '5', 'Y', 'COUNT_RECOM_PROD'),
(23, 'countSaleProduct', '5', 'Y', 'COUNT_SALE_PROD'),
(24, 'actionInCatalog', 'true', 'Y', 'VIEW_OR_BUY'),
(25, 'printProdNullRem', 'true', 'Y', 'PRINT_PROD_NULL_REM'),
(26, 'printRemInfo', 'true', 'Y', 'PRINT_REM_INFO'),
(27, 'heightPreview', '225', 'Y', 'PREVIEW_HEIGHT'),
(28, 'widthPreview', '300', 'Y', 'PREVIEW_WIDTH'),
(29, 'heightSmallPreview', '50', 'Y', 'PREVIEW_HEIGHT_2'),
(30, 'widthSmallPreview', '80', 'Y', 'PREVIEW_WIDTH_2'),
(31, 'waterMark', 'true', 'Y', 'WATERMARK'),
(32, 'widgetCode', '<!-- В это поле необходимо прописать код счетчика посещаемости Вашего сайта. Например, Яндекс.Метрика или Google analytics -->', 'Y', 'WIDGETCODE'),
(33, 'noReplyEmail', 'noreply@sitename.ru', 'Y', 'NOREPLY_EMAIL'),
(34, 'smtp', 'false', 'Y', 'SMTP'),
(35, 'smtpHost', '', 'Y', 'SMTP_HOST'),
(36, 'smtpLogin', '', 'Y', 'SMTP_LOGIN'),
(37, 'smtpPass', '', 'Y', 'SMTP_PASS'),
(38, 'smtpPort', '', 'Y', 'SMTP_PORT'),
(39, 'shopPhone', '8 (555) 555-55-55', 'Y', 'SHOP_PHONE'),
(40, 'shopAddress', 'г. Москва, ул. Тверская, 1. ', 'Y', 'SHOP_ADDERSS'),
(41, 'shopName', 'Бытовой', 'Y', 'SHOP_NAME'),
(42, 'shopLogo', '', 'Y', 'SHOP_LOGO'),
(43, 'phoneMask', '+7 (999) 999-99-99', 'Y', 'PHONE_MASK'),
(44, 'printStrProp', 'true', 'Y', 'PROP_STR_PRINT'),
(45, 'noneSupportOldTemplate', 'false', 'Y', 'OLD_TEMPLATE'),
(46, 'printCompareButton', 'true', 'Y', 'BUTTON_COMPARE'),
(47, 'currencyShopIso', 'RUR', 'Y', 'CUR_SHOP_ISO'),
(48, 'currencyRate', 'a:4:{s:3:\"RUR\";s:1:\"1\";s:3:\"UAH\";s:3:\"2.7\";s:3:\"USD\";s:4:\"39.5\";s:3:\"EUR\";s:4:\"49.8\";}', 'Y', 'CUR_SHOP_RATE'),
(49, 'currencyShort', 'a:4:{s:3:\"RUR\";s:7:\"руб.\";s:3:\"UAH\";s:7:\"грн.\";s:3:\"USD\";s:1:\"$\";s:3:\"EUR\";s:3:\"€\";}', 'Y', 'CUR_SHOP_SHORT'),
(50, 'cacheObject', 'true', 'Y', 'CACHE_OBJECT'),
(51, 'cacheMode', 'DB', 'Y', 'CACHE_MODE'),
(52, 'cacheTime', '18000', 'Y', 'CACHE_TIME'),
(53, 'cacheHost', '', 'Y', 'CACHE_HOST'),
(54, 'cachePort', '', 'Y', 'CACHE_PORT'),
(56, 'priceFormat', '1 234,56', 'Y', 'PRICE_FORMAT'),
(57, 'horizontMenu', 'false', 'Y', 'HORIZONT_MENU'),
(58, 'buttonBuyName', 'В корзину', 'Y', 'BUTTON_BUY_NAME'),
(59, 'buttonCompareName', 'Сравнить', 'Y', 'BUTTON_COMPARE_NAME'),
(60, 'randomProdBlock', 'false', 'Y', 'RANDOM_PROD_BLOCK'),
(61, 'timeStartEngine', '1413809336', 'N', 'TIME_START_ENGINE'),
(62, 'timeFirstUpdate', '', 'N', 'TIME_START_ENGINE'),
(63, 'buttonMoreName', 'Подробнее', 'Y', 'BUTTON_MORE_NAME'),
(64, 'compareCategory', 'true', 'Y', 'COMPARE_CATEGORY'),
(65, 'colorScheme', '', 'Y', 'COLOR_SCHEME'),
(66, 'useCaptcha', '', 'Y', 'USE_CAPTCHA'),
(67, 'autoRegister', 'true', 'Y', 'AUTO_REGISTER'),
(68, 'printFilterResult', 'true', 'Y', 'FILTER_RESULT'),
(69, 'dateActivateKey ', '0000-00-00 00:00:00', 'N', ''),
(70, 'propertyOrder', 'a:16:{s:7:\\\"nameyur\\\";s:40:\\\"ООО \\\"Бытовой\\\"\\\";s:6:\\\"adress\\\";s:48:\\\"г.Москва ул. Тверская, дом 1\\\";s:3:\\\"inn\\\";s:10:\\\"8805614058\\\";s:3:\\\"kpp\\\";s:9:\\\"980501000\\\";s:4:\\\"ogrn\\\";s:13:\\\"7137847078193\\\";s:4:\\\"bank\\\";s:16:\\\"Сбербанк\\\";s:3:\\\"bik\\\";s:9:\\\"041012721\\\";s:2:\\\"ks\\\";s:20:\\\"40702810032030000834\\\";s:2:\\\"rs\\\";s:20:\\\"30101810600000000957\\\";s:7:\\\"general\\\";s:48:\\\"Михаил Васильевич Могутов\\\";s:4:\\\"sing\\\";s:0:\\\"\\\";s:5:\\\"stamp\\\";s:0:\\\"\\\";s:3:\\\"nds\\\";s:2:\\\"18\\\";s:8:\\\"usedsing\\\";s:4:\\\"true\\\";s:6:\\\"prefix\\\";s:3:\\\"MG_\\\";s:8:\\\"currency\\\";s:34:\\\"рубль,рубля,рублей\\\";}', 'N', ''),
(71, 'enabledSiteEditor', 'false', 'N', ''),
(72, 'lockAuthorization', 'true', 'Y','LOCK_AUTH'),
(73, 'orderNumber', 'true','Y', 'ORDER_NUMBER'),
(74, 'popupCart', 'false', 'Y', 'POPUP_CART'),
(75, 'catalogIndex', 'false', 'Y', 'CATALOG_INDEX'),
(76, 'productInSubcat', 'true', 'Y', 'PRODUCT_IN_SUBCAT'),
(77, 'copyrightMoguta', 'true', 'Y', 'COPYRIGHT_MOGUTA'),
(78, 'picturesCategory', 'true', 'Y', 'PICTURES_CATEGORY'),
(79, 'requiredFields', 'true', 'Y', 'REQUIRED_FIELDS'),
(80, 'backgroundSite', '', 'Y', 'BACKGROUND_SITE'),
(81, 'waterMarkVariants', 'false', 'Y', 'WATERMARK_VARIANTS')",

 


"CREATE TABLE IF NOT EXISTS `".$prefix."user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) DEFAULT NULL,
  `pass` varchar(255) DEFAULT NULL,
  `role` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `sname` varchar(255) DEFAULT NULL,
  `address` text,
  `phone` varchar(255) DEFAULT NULL,
  `date_add` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `blocked` int(1) NOT NULL DEFAULT '0',
  `restore` varchar(255) DEFAULT NULL,
  `activity` int(1) DEFAULT '0',
  `inn` text NOT NULL,
  `kpp` text NOT NULL,
  `nameyur` text NOT NULL,
  `adress` text NOT NULL,
  `bank` text NOT NULL,
  `bik` text NOT NULL,
  `ks` text NOT NULL,
  `rs` text NOT NULL,
  `birthday` DATE DEFAULT NULL,
  `ip` TEXT NOT NULL,
  PRIMARY KEY (`id`),
  KEY `email` (`email`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1"



);