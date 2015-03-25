<?php

/**
 * Класс Moguta - запускает движок и выполняет роль маршрутизатора, определяет контролер и представление.
 * Если не находит контролер, то подбирает другой доступный вариант
 * вывода информации, такой как вывод страницы из папки mg-pages/ или получение HTML из базы сайта.
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Libraries
 */
class Moguta {

  // Конструктор запускает маршрутизатор и получает запрашиваемый путь.
  public function __construct() {
    $this->getRoute();
  }

  /**
   * Запускает движок системы.
   *
   * @return array массив с результатами содержащий:
   * -тип файла, который надо открыть
   * -данные для этого файла
   * -вид
   */
  public function run() {
    $data = null;
    $view = null;
    $variables = null;
    
    // Если найден контролер.
    if ($controller = $this->getController()) {    
      MG::set('controller', $controller);
      $contr = new $controller;
      $type = 'view';
      $variables = $contr->variables;
      $view = $this->getView();
    } elseif ($data = MG::getPhpContent()) {	
	     // отключаем вывод шаблона для пользовательских скриптов
		  MG::disableTemplate();
 
		  // Если найден пользовательский файл, возвращает его расширение (php||html).
      $ext = explode(".", $data);
		  $type = end($ext);		  
    
		  // если запрошен существуюий файл стилей или js или любое другое расширение кроме html и php,
		  // то редиректрим на его настоящее местоположение минуя проверки движка
		  if($type != 'php' && $type !='html'){	  		
			MG::redirect('/'.$data);
			exit;
		  }
	
	  if($type != 'php'){	  
	    // если файл не исполняемый то считываем его в строку
	    $data = file_get_contents($data);
		$type = 'html';
	  }
	 
    } elseif ($data = MG::getHtmlContent()) {

      // Если найден статический контент в БД.
      $type = 'html';
    }
	
    // Если не существует запрашиваемых данных.
    $type = !empty($type) ? $type : '404';
    $result = array(
      'type' => $type,
      'data' => $data,
      'view' => $view,
      'variables' => $variables
    );
    return $result;
  }

  /**
   * Проверяет, может ли контролер 'Catalog' обработать ЧПУ ссылку.
   * Если ссылка действительно запрашивает какую-то существующую категорию,
   * то метод возвращает в качестве названия контролера строку "catalog".
   * В противном случае именем контролера считается последняя секция в ссылке.
   *
   * @return string название контролера.
   */
  public function convertCpuCatalog() {
    $arraySections = URL::getSections();
    unset($arraySections[0]);
    $url = implode('/', $arraySections);

	if($url=="mg-admin"||$url=="mgadmin"||$url=="ajax"||$url=="mg-admin/ajax"){
	  return URL::getRoute();
	}

    $result = DB::query("
      SELECT  url as category_url, id
      FROM `".PREFIX."category`
      WHERE CONCAT(parent_url,url) = '%s'
    ", $url);

    if ($obj = DB::fetchObject($result)) {
      URL::setQueryParametr('category_id', $obj->id);
      return 'catalog';
    }

    return URL::getRoute();
  }

  /**
   * Проверяет, может ли контролер 'Product' обработать ЧПУ ссылку.
   * Если ссылка действительно запрашивает какой-то существующий продукт
   * в имеющейся категории, то метод возвращает в качестве названия контролера строку "product".
   * В противном случае метод считает, что именем контролера должена являться
   * последняя секция в ссылке.
   *
   * @return string - имя контролера.
   */
  public function convertCpuProduct() {

    // вычисляем url для возможной категории.
    // если запрошен адрес http://[сайт]/templates/free/my/templ2.html
    // то на выходе $categoryUrl будет равно /templates/free/my , а $productUrl=templ2
    $arraySections = URL::getSections();
    unset($arraySections[0]);
    unset($arraySections[count($arraySections)]);

    $categoryUrl = implode('/', $arraySections);
    $productUrl = URL::getLastSection();

    // Получает id продукта по заданной секции.
    $sql = '
      SELECT CONCAT(c.parent_url,c.url) as category_url, p.url as product_url, p.id
      FROM `'.PREFIX.'product` p
      LEFT JOIN `'.PREFIX.'category` c
        ON c.id=p.cat_id
      WHERE p.url = "%s"
    ';

    $result = DB::query($sql, $productUrl);

    if ($obj = DB::fetchObject($result)) {
      // Для товаров без категорий формируется ссылка [site]/catalog/[product].
      $obj->category_url = ($obj->category_url !== NULL) ? $obj->category_url : 'catalog';

      if ($categoryUrl == $obj->category_url) {
        URL::setQueryParametr('id', $obj->id);
        $args = func_get_args();
        return MG::createHook(__CLASS__."_".__FUNCTION__, 'product', $args);
      }
    }

    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $productUrl, $args);
  }

  /**
   * Получает название класса контролера, который будет обрабатывать текущий запрос.
   * @return string название класса нужного контролера.
   */
  private function getController() {
    if ($this->route == 'mg-admin') {
      $this->route = 'mgadmin';
    }

    // если отрывается главная страница, и при этом в настройках указано что нужно вывести каталог на главной
    if ($this->route == 'index' && MG::getSetting('mainPageIsCatalog') != 'true') {
      return false;
    }

    $sections = URL::getSections();

    if (count($sections) == 2 || count($sections) == 1 || $this->route == 'ajax' || $this->route == 'product' || $this->route == 'catalog') {
      if (file_exists(CORE_DIR.'controllers/'.$this->route.'.php') ||
        file_exists(PATH_TEMPLATE.'/controllers/'.$this->route.'.php')) {
        return 'controllers_'.$this->route;
      }
    }

    return false;
  }

  /**
   * Получает маршрут исходя из URL.
   * Интерпретирует ЧПУ ссылку в понятную движку форму.
   *
   * @return string возвращает полученный маршрут.
   */
  private function getRoute() {
    $this->route = URL::getRoute();
    if (empty($this->route) || $this->route == 'index') {
      $this->route = 'index';
     
      if (MG::getOption('catalogIndex')=='true') {
        $this->route = 'catalog';
      }
      return $this->route;
    }


    /**
     * По умолчанию движок поддерживает ЧПУ только для каталога и карточки товара,
     * поэтому проверяем не адресован ли запрос к контролерам catalog или product.
     */
    $this->route = $this->convertCpuCatalog();
    if ($this->route !== 'catalog') {	
      $this->route = $this->convertCpuProduct();
    }

    $this->route = !empty($this->route) ? $this->route : "index";
    $this->route = ($this->route == 'index' && (MG::getOption('catalogIndex')=='true')) ? "catalog" : $this->route;
    /**
     * Если ссылка не может быть обработана
     * ни контролером 'Catalog',
     * ни контролером 'Product', то ищется контролер
     * по последней секции в ссылке
     * <code>/monitoryi/order<code>
     * в этом примере запрос обработает контролер 'Order', если он существует.
     */
    return $this->route;
  }

  /**
   * Получает путь до файла представления, который выведет
   * на страницу полученные из контролера данные.
   * @return string путь до представление.
   */
  public function getView() {
    $route = $this->route;

    /**
     * Если работал контролер аякса, то в реестре переменных должна
     * существовать переменная 'view' содержащая
     * путь до представления в админке mg-admin/section/views/[название файла представления].php.
     */
    $view = URL::get('view');

    // Если запрос не аяксовый, то представление будет
    // взято из папки views/ шаблона сайта расположенного в PATH_TEMPLATE.
    // Также представление может находиться в папке ядра mg-core/views/.
    if (!$view) {
      $pathView = PATH_TEMPLATE.'/views/';
      $view = $pathView.$route.'.php';

      if (!file_exists($view)) {
        $view = 'views/'.$route.'.php';
        ;
      }
    }
    return $view;
  }

}