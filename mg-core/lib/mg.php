<?php

/**
 * Класс MG - предназначен для  доступа к функционалу системы,
 * из любой точки программы.
 * Реализован в виде синглтона, что исключает его дублирование.
 * Имеет в себе реестр для хранения любых объектов.
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Libraries
 */
class MG {

  static private $_instance = null;
  private $_registry = array();


  /**
   * Конструктор выполняет следующие действия
   * - Старт сессии
   * - Включение пользовательского шаблона
   * - инициализация библиотеки для работы с категориями
   */
  private function __construct() {
 
   
// Старт сессии
    session_start();

// Включение пользовательского шаблона
    self::enableTemplate();
    define('SITE', PROTOCOL.'://'.$_SERVER['SERVER_NAME'].URL::getCutSection());    
    define('SCRIPT', PROTOCOL.'://'.$_SERVER['SERVER_NAME'].URL::getCutSection().'/'.CORE_JS); 
    define('PREVIEW_TEMPLATE', false); // если указать в true, то можно просмотривать шаблоны сайта с помощью GET параметра tpl=.default
    /**
     * Резервируем переменную staticPage в реестре ,
     * в ней будет хранится массив соответствий.
     * [название таблицы] => [поле содержащей статические страницы]
     * В такой таблице обязательно должнно присутствовать поле url и title.
     * Это требуется для расширения источников статического контента (новости, блог...
     * )
     */
    $this->_registry['staticPage'] = array('page' => 'html_content');
  
// инициализация библиотеки для работы с категориями.
// далее в любом  месте движка можно будет работать с категориями через реестр.
    $category = new Category();
    
    $this->_registry['category'] = $category;
    $page = new Page();
    $this->_registry['pages'] = $page;
  }

  private function __clone() {
    
  }

  private function __wakeup() {
    
  }

  /**
   * Метод addAction добавляет обработчик для заданного хука.
   * пример 1:
   * <code>
   * //Произвольная пользовательская функция в одном из плагинов
   * function userfunc($color, $text){
   *   echo '<span style = "color:'.$color.'">'.$text.'</span>';
   * }
   *
   * // на хук с именем 'printHeader'
   * // вешается обработчик в виде пользовательской функция 'userPrintHeader'
   * // функция ждет два параметра, поэтому хук должен их задавать
   * MG::addAction('printHeader', 'userfunc', 2);
   *
   * // как должен выглядеть хук
   * MG::createHook('printHeader', 'gray', 'text');
   * </code>
   *
   * Варианты вызова данного метода.
   * <code>
   * 1. MG::addAction([имя хука], [имя пользовательской функции]) - назначает пользовательскую функцию в качестве обработчика для хука.
   * 2. MG::addAction([имя хука], [имя пользовательской функции], [количество параметров для пользовательской функции]) - назначает пользовательскую функцию в качестве обработчика для хука, при этом указывается какое количество параметров функция ожидает от хука.
   * 3. MG::addAction([имя хука], [имя пользовательской функции], [количество параметров для пользовательской функции], [приоритет выполнения]) - назначает пользовательскую функцию в качестве обработчика для хука, при этом указывается какое количество параметров функция ожидает от хука и какой приоритет ее выполнения.
   * </code>
   * @param  $hookName имя хука на который вешается обработчик.
   * @param  $userFunction пользовательская функци, которая сработает при объявлении хука.
   * @param  $countArg количество аргументов, которое ждет пользовательская функция.
   * @param  $priority приоритет обработки.
   */
  public static function addAction($hookName, $userFunction, $countArg = 0, $priority = 10) {
    PM::registration(new EventHook($hookName, $userFunction, $countArg, $priority * 10));
  }

  /**
   * Создает shortcode и определяет пользовательскую функцию для его обработки.
   * @param $hookName - название шорткода.
   * @param $userFunction - название пользовательской функции, обработчика.
   */
  public static function addShortcode($hookName, $userFunction, $priority = 10) {
    $hookName = "shortcode_".$hookName;
    self::addAction($hookName, $userFunction, 1, $priority * 10);
  }

  /**
   * Добавляет обработчик для страницы плагина.
   * Назначенная в качестве обработчика пользовательская функция.
   * будет, отрисовывать страницу настроек плагина.
   *
   * @param  $plugin название папки в которой лежит плагин.
   * @param  $userFunction пользовательская функци,
   *         которая сработает при открытии страницы настроек данного плагина.
   */
  public static function pageThisPlugin($plugin, $userFunction) {
    self::addAction($plugin, $userFunction);
  }

  /**
   * Добавляет обработчик для активации плагина,
   * пользовательская функция будет срабатывать тогда когда
   * в панели администрирования будет активирован плагин.
   *
   * Является не обязательным атрибутом плагина, при отсутствии этого
   * обработчика плагин тоже будет работать.
   *
   * Функция обрабатывающя событие
   * не должна производить вывод (echo, print, print_r, var_dump), это нарушит
   * логику работы AJAX.
   *
   * @param  $dirPlugin директория в которой хранится плагин.
   * @param  $userFunction пользовательская функци, которая сработает при объявлении хука.
   */
  public static function activateThisPlugin($dirPlugin, $userFunction) {
    $dirPlugin = PM::getFolderPlugin($dirPlugin);
    $hookName = "activate_".$dirPlugin;
    PM::registration(new EventHook($hookName, $userFunction));
  }

  /**
   * Добавляет обработчик для ДЕактивации плагина,
   * пользовательская функция будет срабатывать тогда когда
   * в панели администрирования будет выключен  плагин.
   *
   * >Является не обязательным атрибутом плагина, при отсутствии этого
   * обработчика плагин тоже будет работать.
   *
   * Функция обрабатывающя событие
   * не должна производить вывод (echo, print, print_r, var_dump), это нарушит
   * логику работы AJAX.
   *
   * @param  $dirPlugin директория в которой хранится плагин.
   * @param  $userFunction пользовательская функци, которая сработает при объявлении хука.
   */
  public static function deactivateThisPlugin($dirPlugin, $userFunction) {
    $dirPlugin = PM::getFolderPlugin($dirPlugin);
    $hookName = "deactivate_".$dirPlugin;
    PM::registration(new EventHook($hookName, $userFunction));
  }

  /**
   * Создает hook -  крючок, для  пользовательских функций и плагинов.
   * может быть вызван несколькими спообами:
   * 1. createHook('userFunction'); - в любом месте программы выполнится пользовательская функция userFunction() из плагина;
   * 2. createHook('userFunction', $args); - в любом месте программы выполнится пользовательская функция userFunction($args) из плагина с параметрами;
   * 3. return createHook('thisFunctionInUserEnviroment', $result, $args); - хук прописывается перед.
   *  возвращением результата какой либо функции,
   *  в качестве параметров передается результат работы текущей функции,
   *  и начальные параметры которые были переданы ей.
   *
   * @param array $arr параметры, которые надо защитить.
   * @return array $arr теже параметры, но уже безопасные.
   */
  public static function createHook($hookName) {

// Вариант 1. createHook('userFunction');
    $arg = array();
    $result = false;

// Вариант 2. createHook('userFunction', $args);
//  Не удалять, он работает.
//  Для случая:
//    createHook(__CLASS__."_".__FUNCTION__, $title);
//    mgAddAction('mg_titlepage', 'myTitle', 1);
    if (func_num_args() == 2) {
      $arg = func_get_args();
      $arg = $arg[1];
    }

// Вариант 3. return createHook('thisFunctionInUserEnviroment', $result, $args);
    if (func_num_args() == 3) {
      $arg = func_get_args();
      $result = isset($arg[1]) ? true : false;
      if ($result) {
        $argumets = array(
          'result' => $arg[1],
          'args' => $arg[2]
        );
        $arg = $argumets;
      }
    }

    if ($result) {
      return PM::createHook($hookName, $arg, $result);
    }

    PM::createHook($hookName, $arg, $result);
  }

  /**
   * Создает хук activate_$folderName при активации заданного плагина.
   * Предварительно подключает index.php активируемого плагина,
   * для того, чтобы зарегистрировать его обработчики.
   * @param $folderName - название папки содержащей плагин.
   */
  public static function createActivationHook($folderName) {
//подключает функции плагина
    PM::includePluginInFolder($folderName);
    $hookName = "activate_".$folderName;
    self::createHook($hookName);
  }

  /**
   * Создает хук deactivate_$folderName при активации заданного плагина.
   * Предварительно подключает index.php активируемого плагина,
   * для того, чтобы зарегистрировать его обработчики.
   * @param $folderName - название папки содержащей плагин.
   */
  public static function createDeactivationHook($folderName) {
    PM::includePluginInFolder($folderName);
    $hookName = "deactivate_".$folderName;
    self::createHook($hookName);
  }

  /**
   * Вырезает все слеши, аналог функции отключения магических кавычек.
   *
   * @param array массив в котором надо удалить слеши.
   * @return array $arr тот же массив но без слешей.
   */
  public static function stripslashesArray($array) {
    if (is_array($array))
      return array_map(array(__CLASS__, 'stripslashesArray'), $array);
    else
      return stripslashes($array);
  }

  /**
   * Защита от XSS атак полученный массив параметров.
   *
   * @param array $arr параметры, которые надо защитить.
   * @return array $arr теже параметры, но уже безопасные.
   */
  public static function defenderXss($arr, $emulMgOff = false) {

      
    $filter = array('<', '>');
  
    foreach ($arr as $num => $xss) {
      if (is_array($xss)) {
        $arr[$num] = self::defenderXss($xss,$emulMgOff);
      } else{
        if ($emulMgOff) {
          $xss = stripslashes($xss);
        }
        $xss = htmlspecialchars_decode($xss);
        $xss = str_replace('"', '&quot;', $xss);
        $arr[$num] = str_replace($filter,  array('&lt;', '&gt;'),  trim($xss));
      }  
    }
  
    return $arr;
  }

   /**
   * Восстанавливает строку пошедшую защиту от xss атак - defenderXss()
   *
   * @param array $string входящая строка.
   */
  public static function defenderXss_decode($string) {    
    return  str_replace( array('&lt;', '&gt;','&quot;'), array('<', '>','"'), trim($string));
  }
  
  /**
   * Отключает вывод элементов шаблона. Нужен при работе с AJAX.
   */
  public static function disableTemplate() {
    $_SESSION['noTemplate'] = true;
  }

  /**
   * Включает вывод элементов шаблона. Весь контент будет
   * выводиться внутри пользовательской темы оформления.
   */
  public static function enableTemplate() {
    $_SESSION['noTemplate'] = false;
  }

  /**
   * Возвращает переменную из реестра.
   * @param $key - имя перменной.
   */
  static public function get($key) {
    return !empty(self::getInstance()->_registry[$key]) ? self::getInstance()->_registry[$key] : null;
  }

  /**
   * Возвращает запрошенную настройку из памяти.
   * @param $option - имя перменной.
   */
  static public function getSetting($option) {
    return !empty(self::getInstance()->_registry['settings'][$option]) ? self::getInstance()->_registry['settings'][$option] : null;
  }

  /**
   * Получает настройки для доступа к БД, из конфигурационного файла config.ini.
   * @return boolean.
   */
  public static function getConfigIni() {
  
    if (file_exists('config.ini')) {
      $config = parse_ini_file('config.ini', true);
      define('HOST', $config['DB']['HOST']);
      define('USER', $config['DB']['USER']);
      define('PASSWORD', $config['DB']['PASSWORD']);
      define('NAME_BD', $config['DB']['NAME_BD']);
      define('PREFIX', $config['DB']['TABLE_PREFIX']);
      
      // создание констант из настроек
      foreach($config['SETTINGS'] as $key => $value){
        define($key, $value);
      }  
      
      //Убираем "/" в конце адреса, для всех страниц, кроме админки и главной
      if(!strpos($_SERVER['REQUEST_URI'], "mg-admin") && 
        substr($_SERVER['REDIRECT_URL'], -1)=='/' &&         
        str_replace('?'.$_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI'])!=str_replace('index.php', '', $_SERVER['SCRIPT_NAME'])){         
        $strLocation = PROTOCOL.'://'.$_SERVER['HTTP_HOST'].'/'.trim($_SERVER['REDIRECT_URL'], '/');
        $strLocation .= (strlen($_SERVER['QUERY_STRING'])>0)?'?'.$_SERVER['QUERY_STRING']:'';
        header('HTTP/1.1 301 Moved Permanently');
        header('Location: '.$strLocation);
      }

      return true;
    }else{
      self::instalMoguta();
    }
    return false;
  }

  /**
   * Получает контент статической HTML страницы из БД.  
   * @return string|boolean - возвращает либо HTML либо false.
   */
  public static function getHtmlContent() {
    $result = false;
    $sections = URL::getSections();

    $arrayStaticPage = self::get('staticPage');
    $url = URL::parsePageUrl();
    $url = $url ? $url : 'index';
    $parentUrl = URL::parseParentUrl();
    $parentUrl = $parentUrl != '/' ? $parentUrl : '';
    foreach ($arrayStaticPage as $table => $content) {
      $res = DB::query('
          SELECT *
          FROM '.PREFIX.$table.'
          WHERE (parent_url='.DB::quote($parentUrl).' AND url='.DB::quote($url.".html").')
            OR (parent_url='.DB::quote($parentUrl).' AND url='.DB::quote($url).' )
        ');

      if ($html = DB::fetchAssoc($res)) {
        $result = self::inlineEditor(PREFIX.$table, 'html_content', $html['id'], $html[$content]);

        self::titlePage($html['title']);
        self::seoMeta($html);
      }
    }

//если HTML файл не найден в БД сайта, возможно такой файл есть в 'mg-pages/'
    if (!$result) {
      if (file_exists(PAGE_DIR.URL::getUri()) && !is_dir(PAGE_DIR.URL::getUri())) {
        MG::disableTemplate();
        $result = file_get_contents(PAGE_DIR.URL::getUri());
      }
    }

    if ($result) {
      MG::set('isStaticPage', true);
    }

    $args = func_get_args();
    return self::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Оборачивает публичную часть в специальный див для инлайн редактирования.
   * Необходимо  задать параметры редактируемого поля в таблице БД.
   * @param string $table - таблица в базе например mg_page
   * @param string $field - поле в таблице , например html_content
   * @param int $id - номер записи в таблице
   * @param string $content - исходное содержание
   * @return string
   */
  public static function inlineEditor($table, $field, $id, $content) {

    if ($content === '') {
      $content = '&nbsp;';
    }
    if (USER::isAuth() && ('1' == USER::getThis()->role || '4' == USER::getThis()->role) && MG::getSetting("enabledSiteEditor") == "true") {
      $result = "<div id='".$table."' contenteditable='true' class='fastEdit' data-item-id=".$id." data-table='".$table."' data-field='".$field."'>".$content.'</div>';
    } else {
      $result = $content;
    }
    return $result;
  }

  /**
   * Вызывает модалку для редактирования объекта прямо на сайте.
   * 
   * @param type $content - содержание блока
   * @param type $type - тип модального окна
   * @param type $id - сущность
   * @return type
   */
  public static function modalEditor($section, $content, $type, $id) {
    if (USER::isAuth() && ('1' == USER::getThis()->role || '4' == USER::getThis()->role) && MG::getSetting("enabledSiteEditor") == "true") {
      $result = "<div class='modalOpen' data-section='$section' data-param=\"['".$type."',".$id."]\">".$content.'</div>';
    } else {
      $result = $content;
    }
    return $result;
  }

  /**
   * Строит блок выпадающий по наведению на категории в меню.
   * 
   * @param type $section - секция для вызова модалки 
   * @param type $content - содержание блока
   * @param type $id - сущность
   * @param type $component - меню категорий, либо меню страниц.
   * @return type
   */
  public static function contextEditor($section, $content, $id, $component) {
    if (USER::isAuth() && ('1' == USER::getThis()->role || '4' == USER::getThis()->role) && MG::getSetting("enabledSiteEditor") == "true") {
      $lang = MG::get('lang');
      
      if ($component == "category") {
        $result .= "<div class='exist-admin-context'>$content<div class='admin-context'>";
        $result .= "<div class='modalOpen' data-section='$section' data-param=\"['edit',".$id."]\">".$lang['PUBLIC_BAR_4']." <span class='admin-edit-icon'></span></div>";
        $result .= "<div class='modalOpen' data-section='$section' data-param=\"['add',".$id."]\">".$lang['PUBLIC_BAR_5']." <span class='admin-add-icon'></span></div>";
        $result .= "<div class='modalOpen' data-section='catalog' data-param=\"['add',".$id."]\">".$lang['PUBLIC_BAR_6']." <span class='admin-add-icon'></span></div>";
        $result .= "</div></div>";
      } else {
        $result .= "<div class='exist-admin-context'>$content<div class='admin-context'>";
        $result .= "<div style='width:120px;' class='modalOpen' data-section='$section' data-param=\"['edit',".$id."]\">".$lang['PUBLIC_BAR_7']." <span class='admin-edit-icon'></span></div>";
        $result .= "<div class='modalOpen' data-section='$section' data-param=\"['add',".$id."]\">".$lang['PUBLIC_BAR_8']."<span class='admin-add-icon'></span></div>";
        $result .= "</div></div>";
      }
    } else {
      $result = $content;
    }
    return $result;
  }

  /**
   * Добавляет в реестр движка информацию,
   * о новой таблице, в которой можно искать статический контент.
   * @param string $table наименование новой таблицы.
   * @param string $table наименование поля в таблице с контентом.
   */
  public static function newTableContent($table, $contentField) {
    $newTablePage = MG::get('staticPage');
    $newTablePage[$table] = $contentField;
    self::set('staticPage', $newTablePage);
  }

  /**
   * Возвращет единственный экземпляр данного класса.
   * @return object - объект класса MG.
   */
  static public function getInstance() {
    if (is_null(self::$_instance)) {
      self::$_instance = new self;
    }
    return self::$_instance;
  }

  /**
   * Получить меню в HTML виде.
   * @return object - объект класса Menu.
   */
  public static function getMenu($type = false) {
    if ($type)
      return Menu::getMenuFull($type);
    return Menu::getMenu();
  }

  /**
   * Получить путь до пользовательского файла, создающего контент страницы.
   * Файл должен находиться в папке mg-pages.
   * @return string - путь к php файлу.
   */
  public static function getPhpContent() {
    $result = false;
    $sections = URL::getSections();
//проверем наличие скрипта в папке 'mg-pages', при этом нельзя напрямую обратьиться через 'mg-pages' в адресной строке
    if (count($sections) >= 1 && $sections[1] != 'mg-pages') {

// если запрошен файл из корня 'mg-pages'
      if (file_exists(PAGE_DIR.URL::getRoute().'.php')) {
        $result = PAGE_DIR.URL::getRoute().'.php';
      } elseif (file_exists(PAGE_DIR.URL::getRoute().'/index.php') && ('/' == substr(URL::getURI(), -1))) {
// если запрошена субдиректория из корня 'mg-pages' то пытаемся открыть в ней index.php
        $result = PAGE_DIR.URL::getRoute().'/index.php';
      } elseif (file_exists(PAGE_DIR.URL::getRoute().'/index.html') && ('/' == substr(URL::getURI(), -1))) {
// если запрошена субдиректория из корня 'mg-pages' в ней index.html
        $result = PAGE_DIR.URL::getRoute().'/index.html';
      } elseif (file_exists(PAGE_DIR.URL::getUri()) && !is_dir(PAGE_DIR.URL::getUri())) {
// если запрошен существующий файл и он не является директорией, а является файлом с расширением отличным от phph и HTML
        $result = PAGE_DIR.URL::getUri();
      }
    }

    $args = func_get_args();
    return self::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Получить параметры маленькой корзины.
   * @return object - объект класса SmalCart.
   */
  public static function getSmalCart() {
    return SmalCart::getCartData();
  }

  /**
   * Инициализация настроек сайта из таблицы settings в БД.
   * Записывает в реестр все настройки из таблицы,
   * в последствии к ним осуществляется доступ из любой точки программы
   * @return void
   */
  public static function init() {
   
    $result = DB::query("
      SELECT `option`, `value`
      FROM `".PREFIX."setting`  
      ");
    $settings = array();

    while ($row = DB::fetchAssoc($result)) {
      $settings[$row['option']] = $row['value'];
    }    
       
    $settings['currencyRate'] = unserialize(stripslashes($settings['currencyRate']));
    $settings['currencyShort'] = unserialize(stripslashes($settings['currencyShort']));
    $settings['currency'] = $settings['currencyShort'][$settings['currencyShopIso']];
  
    self::set('settings', $settings); // инициализируем объект MG попутно заполняя реестр натроек
         
    include('mg-admin/locales/'.$settings['languageLocale'].'.php');
    self::set('lang', $lang);
    /**
     * Подключает файл адаптирующий вызовы статических методов, в обыкновенные функции.
     */
    require_once 'metodadapter.php';


// Определяет константу PATH_TEMPLATE с путем до шаблона сайта.
    self::setDifinePathTemplate($settings['templateName']);

// Подключает файл с функциями шаблона, если таковой существует.
    if (file_exists(PATH_TEMPLATE.'/functions.php')) {
      require_once PATH_TEMPLATE.'/functions.php';
    }
  }

  /**
   * Запускает инсталятор CMS.
   * @return void
   */
  public static function instalMoguta() {

      if (file_exists('install/install.php')) {
        require_once 'install/install.php';
        exit;
      } else {
        echo '<span>ВНИМАНИЕ!! Файл конфигурации недоступен!!
                Повторите процедуру инсталяции</span>';
        exit;
      }
    
  }

  /**
   * Функция Downtime (временное отключение работоспособности сайта).
   * @return boolean
   */
  public static function isDowntime() {
    $route = URL::getRoute();

    $role = USER::isAuth() ? USER::getThis()->role : '';
    $settings = self::get('settings');
    if ('mgadmin' != $route &&
      'ajax' != $route &&
      'enter' != $route &&
      'forgotpass' != $route &&
      $role != 1 &&
      'true' == $settings['downtime']) {
      return true;
    }
    return false;
  }

  /**
   * Функция проверяет наличие установленных библиотек PHP.
   * @param type $mode тип проверки уставовленных модулей.
   * @return boolean|srting сообщение об отсутствии необходимого модуля.
   */
  public static function libExists($mode = 0) {

    if (!function_exists('curl_init')) {
      $res[] = 'Пакет libcurl не установлен! Библиотека cURL не подключена.';
    }

    if (!extension_loaded('zip')) {
      $res[] = 'Пакет zip не установлен! Библиотека ZipArchive не подключена.';
    }

    file_put_contents('temp.txt', ' ');

    if (!file_exists('temp.txt')) {
      $res[] = 'Нет прав на создание файла. Загрузка архива с обновлением невозможна';
    } else {
      unlink('temp.txt');
    }
    return $res;
  }

  /**
   * Полезная при отладке функция, создает лог в корне сайта.
   * @param string $text текст лога.
   * @param string $mode режим записи.
   * @return void
   */
  public static function loger($text, $mode = 'a+') {
    $date = date('Y_m_d');
    $fileName = 'log_'.$date.'.txt';
    $string = date('d.m.Y H:i:s').' =>'.$text."\r\n";
    $f = fopen($fileName, $mode);
    fwrite($f, $string);
    fclose($f);
  }

  /**
   * Возвращает созданую движком HTML страницу, для вывода на экран.
   * Имеет четыре типа вывода:
   * - представление из MVC;
   * - пользовательский php Файл;
   * - статическая HTML страница из БД;
   * - страница 404 ошибки, из пользовательского шаблона.
   *
   * @param mixed $data - массив с данными для вывода контента.
   * @return string - сгенерированный HTML код.
   */
  public static function printGui($data) {

    switch ($data['type']) {
      case 'view': {
          return self::getBuffer($data['view'], false, $data['variables']);
          break;
        }
      case 'php': {
          return self::getBuffer($data['data'], false);
          break;
        }
      case 'html': {
          self::set('isPage', true); // флаг статической страницы, если его нет, то страница формируется иным обазом
          return self::getBuffer($data['data'], true);
          break;
        }
      case '404': {
          header('HTTP/1.0 404 Not Found');
          self::titlePage('Ошибка 404');
          $path404 = PATH_TEMPLATE.'/404.php';

          if (!file_exists($path404)) {
            $path404 = 'mg-templates/default/404.php';
          }
          return self::getBuffer($path404);
          break;
        }
    }

    return false;
  }

  /**
   * Устанавливает meta данные страницы.
   * @param string|bool $title заголовок страницы.
   * @return void.
   */
  public static function meta() {
    $jQuery = true;
    $metaTitle = self::get('metaTitle');
    $metaKeywords = self::get('metaKeywords');
    $metaDescription = self::get('metaDescription');

    $title = $metaTitle ? $metaTitle : self::get('title');

    $meta = '
    <!--Заголовки определенные движком-->
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>'.$title.'</title>
    <meta name="keywords" content="'.$metaKeywords.'" />
    <meta name="description" content="'.$metaDescription.'" />';

    $meta .= '
    <script type="text/javascript" src="'.SITE.'/mg-core/script/jquery-1.10.2.min.js"></script>
    <script type="text/javascript" src="'.SITE.'/mg-core/script/jquery-ui-1.10.3.custom.min.js"></script>
    <script type="text/javascript" src="'.SITE.'/mg-core/script/jquery.form.js"></script>
    <script>var phoneMask = "'.MG::getSetting('phoneMask').'";</script>';

    $meta .= '
    <script type="text/javascript" src="'.SITE.'/mg-core/script/engine-script.js?protocol='.PROTOCOL.'&amp;mgBaseDir='.SITE.'&amp;currency='.MG::getSetting('currency').'"></script>
    ';
    
    // поддержка js для старых шаблонов
    if(MG::getSetting('noneSupportOldTemplate')=='true'){
      $meta .= '
    <script type="text/javascript" src="'.SITE.'/mg-core/script/old-engine-script.js"></script>
    ';
    }
    
    $meta .= '
    <!--/Заголовки определенные движком-->
    ';

    $meta .= '
    <!--Обязательный файл стилей для каждого шаблона-->
    <link rel="stylesheet" href="'.PATH_SITE_TEMPLATE.'/css/style.css" type="text/css" />
    <!--/Обязательный файл стилей для каждого шаблона-->
';
   $colorScheme = MG::getSetting('colorScheme');
    if (PREVIEW_TEMPLATE) {
		if (!empty($_GET['color'])) {
		  SetCookie('color', $_GET['color'], time() + 3600 * 24 * 365);
		  $colorScheme = $_GET['color'];		
		} else {		
		  $colorScheme = $_COOKIE['color'];		
		}	
	}
	
    if($colorScheme){
    $meta .= '
    <!--Цветовая схема шаблона-->
    <link href="'.PATH_SITE_TEMPLATE.'/css/color-scheme/color_'.$colorScheme.'.css" rel="stylesheet" type="text/css" />
    <!--/Цветовая схема шаблона-->
';
    }
    
    if (USER::isAuth() && ('1' == USER::getThis()->role || USER::getThis()->role == '3' || '4' == USER::getThis()->role)) {
      $meta .= '
    <link rel="stylesheet" href="'.SITE.'/mg-admin/design/css/adminbar.css" type="text/css" />
    <link rel="stylesheet" href="'.SITE.'/mg-admin/design/css/style.css" type="text/css" />  
    <script type="text/javascript" src="'.SITE.'/mg-core/script/admin/admin.js?mgBaseDir='.SITE.'&currency='.MG::getSetting('currency').'" type="text/css" /></script>  
    <script>  $(document).ready(function(){admin.publicAdmin();}); var lang = '.json_encode(MG::get('lang')).';</script>
    ';
    }
    
 
    $userMeta = MG::get('register');      
    //MG::loger(print_r($userMeta,true));
    $currentController = MG::get('controller');
    $meta .= '
    <!--Реестр определенных заголовков в плагинах движка-->
    ';
    if(!empty($userMeta)){
      foreach ($userMeta as $key => $headers) {
        //выводим мета заголовок, только если соответствует контролер или его нужно выводить всегда
        if($key == 'all' || $key == $currentController){
         foreach ($headers as $value) {
           $meta .= $value.'
      ';           
         }  
        }
      }
    }
    $meta .= '<!--/Реестр определенных заголовков в плагинах движка-->
    ';
   // $meta .= MG::get('userMeta');
    $args = func_get_args();
    return self::createHook(__CLASS__."_".__FUNCTION__, $meta, $args);
  }

  /**
   * Создает переменную в реестре, в последствии доступна из любой точки программы.
   * @param $key - имя перменной.
   * @param $object - значение переменной.
   */
  static public function set($key, $object) {
    self::getInstance()->_registry[$key] = $object;
  }

  /**
   * Устанавливает константу пути, до папки с шаблоном.
   * @param $template - папка с шаблоном в mg-templates/.
   */
  public static function setDifinePathTemplate($template = 'default') {

    $pathTemplate = 'mg-templates/'.$template;


    if (!empty($_GET['tpl']) && PREVIEW_TEMPLATE) {
      SetCookie('tpl', $_GET['tpl'], time() + 3600 * 24 * 365);
      $pathTemplate = 'mg-templates/'.$_GET['tpl'];
    } else {
      if (PREVIEW_TEMPLATE) {
        $pathTemplate = 'mg-templates/'.$_COOKIE['tpl'];
      }
    }

    $path = $pathTemplate.'/css/style.css';

	// для админки всегда использовать дефолтный шаб
    if (!file_exists($path) || URL::isSection('mg-admin')) {
      $pathTemplate = 'mg-templates/default';
    }
   
	define('PATH_TEMPLATE', $pathTemplate);
    define('PATH_SITE_TEMPLATE', SITE.'/'.$pathTemplate);

// Дописываем в includePath путь до шаблона,
// для того чтобы дать влзможность изменять логику MVC локально, не трогая ядро.
// Теперь  модель, вид и контролер в первую очередь
// будут браться из пользовательского шаблона, при условии что они существуют.
// папки views, models и controlers - могут вовсе отсутствовать в шаблоне.
    set_include_path(PATH_TEMPLATE."/".PATH_SEPARATOR.get_include_path());
  }

  /**
   * Собирает массив данных, доступных в последствии из шаблоне через масиив $data.
   * @return void
   */
  public static function templateData($content) {
    $cart = self::getSmalCart();
    $settings = self::get('settings');
    $data = array(
      'data' => array(
        'cartCount' => $cart['cart_count'],
        'cartPrice' => $cart['cart_price'],
        'currency' => $settings['currency'],
        'cartData' => $cart,
        //'categoryList' => (MG::getSetting('horizontMenu')=="true")?self::get('category')->getCategoriesHorHTML():self::get('category')->getCategoriesHTML(),
        'content' => $content,
        'menu' => self::getMenu(),
        'thisUser' => User::getThis()
      )
    );
    MG::set('templateData', $data['data']);
    return $data;
  }

  /**
   * Подключает пользовательский подвал сайта из выбранного шаблона.
   * Если футер в текущем шаблоне отсутствует поставляется стандартный код из шаблона .default;  
   * @return void
   */
  public static function templateFooter($data = null) {
    $footerPath = PATH_TEMPLATE.'/footer.php';

    if (!file_exists($footerPath)) {
      $footerPath = 'mg-templates/default/footer.php';
    }

    if (!$_SESSION['noTemplate']) {
      require_once $footerPath;
    }
  }

  /**
   * Подключает пользовательскую шапку сайта из темы.
   * Если шапки в текущем шаблоне поставляется стандартный код из шаблона .default; 
   * @return void
   */
  public static function templateHeader($data = null) {

// делаем доступным массив $data в шаблоне.
    extract(self::templateData());
    include PATH_TEMPLATE.'/template.php';


    if (!$_SESSION['noTemplate']) {

// Подключение админ панели.
      if ('1' == User::getThis()->role || '3' == User::getThis()->role || '4' == User::getThis()->role) {
        require_once ADMIN_DIR.'/adminbar.php';
      }

// Подключение файла шапки.
      require_once $headerPath;
    }
  }

  /**
   * Возвращает буфер, который содержит весь,
   * полученый в ходе работы движка, контент.
   * @param $include - путь для полкючаемого файла (вид или пользовательский файл).
   * @param $html - флаг, вывода html контента.
   * @param $variables - массив переменных, которые должны быть доступны в файле вида.
   */
  public static function getBuffer($include, $html = false, $variables = false) {

    if (!empty($variables)) {
      extract($variables);
    }
    ob_start();

    if ($html) {
// выводим контент, предварительно заменив все шорткоды, результатами их обработки.
      echo $include;
    } else {
// не подключается вид если view = _NONE_, например, открывается страница плагина.
      if ($include != '_NONE_') {
        include $include;
      }
    }

    $content = ob_get_contents();
    ob_end_clean();

    ob_start();
// делаем доступным массив $data в шаблоне.
    if (!$_SESSION['noTemplate']) {
      self::printTemplate($content);
    }
    $buffer = ob_get_contents();
    ob_end_clean();

// делаем доступным массив $data в шаблоне.
    if ($_SESSION['noTemplate']) {
      $buffer = $content;
    }

    $args = func_get_args();
    return self::createHook(__CLASS__."_".__FUNCTION__, $buffer, $args);
  }

  /**
   * Подключает пользовательскую шапку сайта из темы.
   * Если шапки в текущем шаблоне поставляется стандартный код из шаблона .default;
   * @return void
   */
  public static function printTemplate($content) {

    extract(self::templateData($content));
    include PATH_TEMPLATE.'/template.php';
// Подключение админ панели.
    if ('1' == User::getThis()->role || '3' == User::getThis()->role || '4' == User::getThis()->role) {
      require_once ADMIN_DIR.'/adminbar.php';
    }
  }

  /**
   * Задает заголовок страницы.
   * @return void
   */
  public static function titlePage($title) {
    self::set('title', $title);
// Инициализирует событие mg_titlePage.
    self::createHook(__CLASS__."_".__FUNCTION__);
// Чтобы обработать его пользховательской функцией нужно добавить обработчик:
// mgAddAction('mg_titlepage', 'userFunctionName');
  }

  public static function seoMeta($data) {
    $data['meta_title'] = !empty($data['meta_title']) ? $data['meta_title'] : '';
    $data['meta_keywords'] = !empty($data['meta_keywords']) ? $data['meta_keywords'] : '';
    $data['meta_desc'] = !empty($data['meta_desc']) ? $data['meta_desc'] : '';

    self::set('metaTitle', $data['meta_title']);
    self::set('metaKeywords', $data['meta_keywords']);
    self::set('metaDescription', $data['meta_desc']);
// Инициализирует событие mg_seoMeta.
    $args = func_get_args();
    self::createHook(__CLASS__."_".__FUNCTION__, $args);
  }

  /**
   * Перевдит кирилицу в латиницу.
   * @param string $str переводимая строка.
   * @return string
   */
  public static function translitIt($str, $mode = 0) {
    $simb = '-';
    if ($mode == 1) {
      $simb = '/';
    }
    $tr = array(
      'А' => 'a', 
      'Б' => 'b', 
      'В' => 'v', 
      'Г' => 'g',
      'Д' => 'd',
      'Е' => 'e',
      'Ё' => 'yo',
      'Ж' => 'j', 
      'З' => 'z', 
      'И' => 'i',
      'Й' => 'y',
      'К' => 'k',
      'Л' => 'l',
      'М' => 'm',
      'Н' => 'n',
      'О' => 'o',
      'П' => 'p', 
      'Р' => 'r',
      'С' => 's',
      'Т' => 't',
      'У' => 'u', 
      'Ф' => 'f', 
      'Х' => 'h',
      'Ц' => 'ts',
      'Ч' => 'ch',
      'Ш' => 'sh',
      'Щ' => 'sch',
      'Ъ' => '',
      'Ы' => 'y',
      'Ь' => '',
      'Э' => 'e',
      'Ю' => 'yu',
      'Я' => 'ya', 
      'а' => 'a', 
      'б' => 'b',
      'в' => 'v', 
      'г' => 'g',
      'д' => 'd',
      'е' => 'e',
      'ё' => 'yo', 
      'ж' => 'j',
      'з' => 'z', 
      'и' => 'i', 
      'й' => 'y', 
      'к' => 'k',
      'л' => 'l',
      'м' => 'm', 
      'н' => 'n', 
      'о' => 'o', 
      'п' => 'p', 
      'р' => 'r',
      'с' => 's', 
      'т' => 't', 
      'у' => 'u', 
      'ф' => 'f',
      'х' => 'h',
      'ц' => 'ts', 
      'ч' => 'ch',
      'ш' => 'sh', 
      'щ' => 'sch', 
      'ъ' => '',
      'ы' => 'y', 
      'ь' => '', 
      'э' => 'e',
      'ю' => 'yu', 
      'я' => 'ya',
      '/' => $simb, 
      '1' => '1',
      '2' => '2',
      '3' => '3', 
      '4' => '4',
      '5' => '5',
      '6' => '6',
      '7' => '7', 
      '8' => '8', 
      '9' => '9', 
      '0' => '0',
      'І' => 'i',
      'Ї' => 'i',
      'Є' => 'e', 
      'Ґ' => 'g',
      'і' => 'i', 
      'ї' => 'i', 
      'є' => 'e', 
      'ґ' => 'g', 
      ' '=> '-'
    );
        
    return strtr($str, $tr);
  }

  /**
   * Перенаправляет на другую страницу сайта.
   * @return void
   */
  public static function redirect($location, $redirect = '') {
    header('Location: '.$redirect.' '.SITE.$location);
    exit;
  }

  /**
   * Устанавливает значение для опции (настройки).
   * @param array $data -  может содержать значения для полей таблицы.
   * <code>
   * $data = array(
   *   option => 'идентификатор опции например: sitename'
   *   value  => 'значение опции например: moguta.ru'
   *   active => 'в будущем будет отвечать за автоподгрузку опций в кеш Y/N'
   *   name => 'Метка для опции например: Имя сайта'
   *   desc => 'Описание опции: Настройа задает имя для сайта'
   * )
   * </code>
   * @return void
   */
  public static function setOption($data) {
// Если функция вызвана вот так: setOption('option', 'value');
    if (func_num_args() == 2) {
      $arg = func_get_args();
      $data = array();
      $data['option'] = $arg[0];
      $data['value'] = $arg[1];
    }

    $result = DB::query("
    SELECT *
    FROM `".PREFIX."setting`
    WHERE `option` = ".DB::quote($data['option'])
    );


    if (!DB::numRows($result)) {
      $result = DB::query("
      INSERT INTO `".PREFIX."setting`
      VALUES ('',".DB::quote($data['option']).",'','N','')"
      );
    }

    $result = DB::query("
    UPDATE `".PREFIX."setting`
    SET ".DB::buildPartQuery($data)."
    WHERE `option` = ".DB::quote($data['option'])    
    );
  }

  /**
   * Возвращает значение для запрошенной опции (настройки).
   * имеет два режима:
   * <code>
   * 1. getOption('optionName') - вернет только значение;
   * 2. getOption('optionName' , true) - вернет всю информацию об опции в
   * виде массива.
   * </code>
   * <code>
   * $data = array(
   *   option => 'идентификатор опции например: sitename'
   *   value  => 'значение опции например: moguta.ru'
   *   active => 'в будущем будет отвечать за автоподгрузку опций в кеш Y/N'
   *   name => 'Метка для опции например: Имя сайта'
   *   desc => 'Описание опции: Настройа задает имя для сайта'
   * )
   * </code>
   * @return void
   */
  public static function getOption($option, $data = false) {

// Если функция вызвана вот так: getOption('option', true);
    if ($data) {
      $result = DB::query("
      SELECT *
      FROM `".PREFIX."setting`
      WHERE `option` = ".DB::quote($option)
      );
      if ($option = DB::fetchAssoc($result)) {
        return $option;
      }
    }

    $result = DB::query("
      SELECT value
      FROM `".PREFIX."setting`
      WHERE `option` = ".DB::quote($option)
    );

    if ($option = DB::fetchAssoc($result)) {
      return $option['value'];
    }
  }

  /**
   *  Склонение числительных
   *  пример echo 'Найдено '.declensionNum(5, array('иностранный язык', 'иностранных языка', 'иностранных языков'));
   * @param type $number
   * @param type $titles
   * @return type
   */
  public static function declensionNum($number, $titles) {
    $cases = array(2, 0, 1, 1, 1, 2);
    return $number." ".$titles[($number % 100 > 4 && $number % 100 < 20) ? 2 : $cases[min($number % 10, 5)]];
  }

  /**
   * Формирует массив информеров для панели администрирования 
   * Примеры вызова
   * <code>
   * MG::addInformer(array('count'=>$model->getCount(),'class'=>'comment-wrap','classIcon'=>'comment-small-icon', 'plugin'=>'comments', 'priority'=>80));
   * MG::addInformer(array('count'=>$model->getCount(),'class'=>'count-wrap','classIcon'=>'message-icon', 'plugin'=>'comments', 'priority'=>70));
   * MG::addInformer(array('count'=>$model->getCount(),'class'=>'message-wrap','classIcon'=>'product-small-icon', 'plugin'=>'comments', 'priority'=>80));
   * </code>
   * @param array $data - массив с входящими параметрами для инициализации информера
   */
  public static function addInformer($data) {
    if (URL::isSection('mg-admin')) {
      self::getInstance()->_registry['informerPanel'][] = $data;
    }
    return true;
  }

  /**
   * Формирует верстку для панели информеров в админке
   * Метод создан специально, для вохможности добавления пользовательских информеров.
   * @param type $items
   * @return type
   */
  public static function createInformerPanel() {
    $items = self::get('informerPanel');
    $html = '';
// Сортировка в порядке приоритетов.
    if (!empty($items)) {
      usort($items, array(__CLASS__, "prioritet"));
      foreach ($items as $item) {
        $display = ($item['count'] == 0) ? 'none' : 'block';
        $html .= '<li><span class="'.$item['class'].'" style="display:'.$display.'">'.$item['count'].'</span><a href="#" rel="'.$item['section'].'" class="'.($item['isPlugin'] ? "isPlugin" : "notPlugin").'" ><span class="'.$item['classIcon'].'"></span></a></li>';
      }
      $html .= '';
    }


    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $html, $args);
  }

// Сортировка по приоритетам массивов.
  public static function prioritet($a, $b) {
    return $a['priority'] - $b['priority'];
  }

  /**
   * Отрезает часть строки дополняя ее многоточием, вырезает шорткоды.
   * @param string $text входящая строка
   * @param int $length количество символов
   * @return string
   */
  public static function textMore($text, $length = 240) {
    $text = strip_tags(PM::stripShortcodes($text));
    $more = "...";
    if (strlen($text) <= $length) {
      $more = '';
    }
    return mb_substr($text, 0, $length, 'utf-8').$more;
  }

  /**
   * Меняет местами параметры сортировки двух записей из заданой таблицы. Таблица обязательно должна иметь поля id и sort.
   * @param type $tablename - название таблицы в базе
   * @param type $oneId - id первой строки
   * @param type $twoId - id второй строки
   * @return boolean 
   */
  public static function changeRowsTable($tablename, $oneId, $twoId) {
    $row1 = null;
    $row2 = null;
    $res = DB::query('
      SELECT *
      FROM `'.PREFIX.$tablename.'`
      WHERE id = '.DB::quote($oneId));

    if (!empty($res)) {
      if ($row = DB::fetchAssoc($res)) {
        $row1 = $row;
      }
    }

    $res = DB::query('
      SELECT *
      FROM `'.PREFIX.$tablename.'`
      WHERE id = '.DB::quote($twoId)
     );

    if (!empty($res)) {
      if ($row = DB::fetchAssoc($res)) {
        $row2 = $row;
      }
    }


    if (!empty($row1) && !empty($row2)) {

      $res = DB::query('
       UPDATE `'.PREFIX.$tablename.'` 
       SET  `sort` = '.DB::quote($row1['sort']).'  
       WHERE  `id` ='.DB::quote($row2['id']).'
     ');

      $res = DB::query('
       UPDATE `'.PREFIX.$tablename.'` 
       SET  `sort` = '.DB::quote($row2['sort']).'  
       WHERE  `id` ='.DB::quote($row1['id']).'
     ');
      return true;
    }
    return false;
  }

  /**
   * Добавляет информацию текущем посетителе.
   *  - с какой рекламной площадки пришел впервые,
   *  - с какой рекламной площадки пришел и совершил покупку.
   * @return boolean 
   */
  public static function logReffererInfo() {
	
    $url = $_SERVER["HTTP_REFERER"];
    $urlInfo = parse_url($_SERVER["HTTP_REFERER"]);
    $referrer = $_SERVER["HTTP_REFERER"];
    $param = '';
    $sitename = $urlInfo['host'];
	
    if (strpos($referrer, SITE) === FALSE && strlen($referrer) > 0) {	  
      if (empty($_COOKIE['firstvisit'])) {
        $firstVisit = $sitename;
        SetCookie('firstvisit', $sitename, time() + 3600 * 24 * 365);
      } else {
        $firstVisit = $_COOKIE['firstvisit'];
      }
      $_SESSION['firstvisit'] = $firstVisit;
      $_SESSION['lastvisit'] = $sitename;
    }

    if (empty($_COOKIE['ad'])) {
      $advert = $_GET['ad'];
      SetCookie('ad', $advert, time() + 3600 * 24 * 365);
    } else {
      $advert = $_COOKIE['ad'];
    }
    $_SESSION['advert_info'] = $advert;
	  

    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, true, $args);
  }

  /**
   * Конвертирует дату из числового представления в строковое с названием месяца.
   * @param type $date - дата в любом формате.
   * @return boolean
   */
  public static function dateConvert($date, $year = false) {
    $date = date("d.m.Y", strtotime($date));
    $date = explode(".", $date);
    $day = $date[0];
    $month = $date[1];
    $year = $date[2];
    if ($month > 12 || $month < 1)
      return FALSE;
    $aMonth = array('января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря');
    if (!$year) {
      $year = '';
    }
    return ($day * 1).' '.$aMonth[$month - 1].' '.$year;
  }
  
 

   /**
   * Возвращает наименование мобильного устройства, с которого происходит просмотр страниц.
   * @return string - наименование устройства.
   */
  function isMobileDevice() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $device['ipod'] = strpos($user_agent, "iPod");
    $device['iphone'] = strpos($user_agent, "iPhone");
    $device['android'] = strpos($user_agent, "Android");
    $device['symb'] = strpos($user_agent, "Symbian");
    $device['winphone'] = strpos($user_agent, "WindowsPhone");
    $device['wp7'] = strpos($user_agent, "WP7");
    $device['wp8'] = strpos($user_agent, "WP8");
    $device['operam'] = strpos($user_agent, "Opera M");
    $device['palm'] = strpos($user_agent, "webOS");
    $device['berry'] = strpos($user_agent, "BlackBerry");
    $device['mobile'] = strpos($user_agent, "Mobile");
    $device['htc'] = strpos($user_agent, "HTC_");
    $device['fennec'] = strpos($user_agent, "Fennec/");

    foreach ($device as $key => $isMobile) {
      if($isMobile){
        return $key;
      } 
    }
    
    return false;    
  }
  
  
  /**
   * Метод управляющий подключением верстки для писем и HTML блоков движка. 
   * @param string $layout - название файла с версткой 
   * @param string $param - массив переменных, которые будут доступны в layout
   * @return array
   */
  public static function layoutManager($layout, $data) {
    $content = ""; 
    $path = '';
	$layoutPath = PATH_TEMPLATE.'/layout/';
	
	// если идет обращение к лэйауту из админки, например отправка письма
	// то вместо PATH_TEMPLATE в котором для админки всегда хранится путь к дефолтному шаблону
	// берем путь до указанного в настройках шаблона, чтобы использовались нужные лайауты
	if(URL::isSection('mg-admin')){
	 $selectedTemplate = self::getSetting('templateName');
	 $layoutPath = 'mg-templates/'.$selectedTemplate.'/layout/';
	}
   
    if(file_exists($layoutPath.$layout.'.php')){ 
      $path = $layoutPath;
    }
    elseif(file_exists(CORE_DIR.'layout/'.$layout.'.php')){
      $path = CORE_DIR.'/layout/';
    }
    
    if($path){   
      //extract($param);
      ob_start();
      include $path.$layout.'.php';
      $content = ob_get_contents();
      ob_end_clean();    

     
      // если в layout для писем не отменен вывод в общем шаблоне.
      // чтобы отключить вывод в шаблоне, можно прям в layout определить $data['noTemplate'] = true;
      if(empty($data['noTemplate']) && strpos($layout,'email_') === 0){
        ob_start();
        include $path.'email_template.php';
        $content = ob_get_contents();
        ob_end_clean();    
      }
    }
    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $content, $args);    
  }

  /**
   * Форматирует цену в читаемый вид
   * @param type $type - тип вывода
   * @param type $data
   */
  public static function numberFormat($str, $type=null) {
    $result = $str;
    $priceFormat = MG::getSetting('priceFormat');
    if($type){
	  $priceFormat = $type;
	}
	
    //без форматирования
    if($priceFormat=='1234.56'){ 
      $result = $str;       
     
    } else

    //разделять тысячи пробелами, а копейки запятыми
    if($priceFormat==='1 234,56'){ 
      $result = number_format( $str, 2, ',', ' ' );  
    } else
  
    //разделять тысячи запятыми, а копейки точками
    if($priceFormat==='1,234.56'){ 
      $result = number_format( $str, 2, '.', ',' );
    } else 
    
    //без копеек, без форматирования
    if($priceFormat=='1234'){  
      $result = round($str);
    } else
    
    //без копеек, разделять тысячи пробелами, а копейки запятыми
    if($priceFormat=='1 234'){ 
      $result = number_format( round($str), 0, ',', ' ' );
    } else
  
    //без копеек, разделять тысячи запятыми, а копейки точками
    if($priceFormat=='1,234'){ 
      $result = number_format( round($str), 0, '.', ',' );
    }  else{
      $result = number_format( round($str), 0, ',', ' ' );        
    }
    
    $cent = substr($result, -3);
    
    if($cent==='.00'||$cent===',00'){   
      $result =substr($result, 0, -3);
    }
  
    return $result;
  }
  
   /**
   * Деформатирует цену в читаемый вид
   * @param type $type - тип вывода
   * @param type $data
   */
  public static function numberDeFormat($str) {

    $result = $str;      
   
    $cent = false;
    $thousand = false;
  
    $existpoint = strrpos($str,'.');
    $existcomma = strrpos($str,',');
    
    // 1,320.50
    if($existpoint && $existcomma){
      $result = str_replace(' ','',$str);     
      $result = str_replace(',','.',$str);  
      $firstpoint = stripos($result,'.');
      $lastpoint = strrpos($result,'.');
        if($firstpoint!=$lastpoint){
          $str1 = substr($result, 0, $lastpoint);
          $str2 = substr($result, $lastpoint);
          $str1 = str_replace('.','',$str1);    
          $result = $str1.$str2;
        }    
      return $result;
    }    
    
    // 1,234 или 1 234,56
    if(!$existpoint && $existcomma){     
      //определяем, что отделяется запятой, тысячи или копейки 
      $str2 = substr($str, $existcomma);     
      if(strlen($str2)-1 == 2){
        $cent = true;
      }else{
        $thousand = true;
      }
    }     
   
    if($thousand){ 
      $result = str_replace(',','',$str);     
    }
    
    if($cent){
      $result = str_replace(',','.',$str);       
      $firstpoint = stripos($result,'.');
      $lastpoint = strrpos($result,'.');
      if($firstpoint!=$lastpoint){
        $str1 = substr($result, 0, $lastpoint);
        $str2 = substr($result, $lastpoint);
        $str1 = str_replace('.','',$str1);    
        $result = $str1.$str2;          
      }
    }
   
    $result = str_replace(' ','',$result);  
    return $result;
  }
  

  public static function priceCourse($price, $format = true, $useFloat = null ) {    
   // $useFloat = true;
    
    if($useFloat===false){
      $price = round($price);
    }
    
    if($format){
      $price = self::numberFormat($price);    
    }
    
    return $price;
  }
}