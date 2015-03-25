<?php

/**
 * Класс Exchange1c - предназначен для обмена данными между "1с - Управление Торговлей" и Moguta.CMS.
 * - Импортирует товары из 1с на сайт.
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Libraries
 */
class Controllers_Exchange1c extends BaseController {

  public $startTime = null;
  public $maxExecTime = null;
  public $mode = null;
  public $type = null;
  public $filename = null;
  public $auth = null;
  public $unlinkFile = false;

  public function __construct() {

    if (empty($_GET['mode'])) {
      MG::redirect('/');
    };

    MG::disableTemplate();
    Storage::$noCache = true;
    $this->unlinkFile = true;
    $this->startTime = microtime(true);
    $this->maxExecTime = min(30, @ini_get("max_execution_time"));
    if (empty($this->maxExecTime)) {
      $this->maxExecTime = 30;
    }

    $mode = (string) $_GET['mode'];
    $this->mode = $mode;
    $this->type = $_GET['type'];
    $this->filename = $_GET['filename'];
    $this->auth = USER::auth($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
    $this->$mode();

    if ($mode && $this->auth) {
      $this->$mode();
    }
  }

  /**
   * 1 шаг - авторизация 1с клиента.
   */
  public function checkauth() {
    echo "success\n";
    echo session_name()."\n";
    echo session_id()."\n";
    exit;
  }

  /**
   * 2 шаг - сообщаем в 1с клиент о поддержке работы с архивами.
   */
  public function init() {
    $zip = extension_loaded('zip')?"yes":"no";
    echo "zip=".$zip."\n";
    echo "file_limit=0\n";
    exit;
  }

  /**
   * 3 шаг - сохраняем файл выгрузки полученный из 1с.
   */
  public function file() {

    $filename = $this->filename;
    if (isset($filename) && ($filename) > 0) {
      $filename = trim(str_replace("\\", "/", trim($filename)), "/");
    }

    if (function_exists("file_get_contents")) {
      $data = file_get_contents("php://input");
      file_put_contents($filename, $data);
      if ($this->extractZip($filename)) {
        $_SESSION['lastCountOffer1cImport'] = 0;
        $_SESSION['lastCountProduct1cImport'] = 0;
        echo "success\n";
      } else {
        echo "failure\n";
      }
    } else {
      echo "failure\n";
    }
    exit;
  }

  /**
   * 4 шаг - запуск процесса импорта файла выгрузки.
   */
  public function import() {
    $this->processImportXml($this->filename);
    echo "success\n";
    echo session_name()."\n";
    echo session_id()."\n";
    exit;
  }

  /**
   * 5 шаг - распаковывает архив с данными по выгрузкам заказов и товаров.
   * @param $file - путь к файлу архива с данными.
   */
  public static function extractZip($file) {

    if (file_exists($file)) {
      $zip = new ZipArchive;
      $res = $zip->open($file, ZIPARCHIVE::CREATE);

      if ($res === TRUE) {
        $sep = DIRECTORY_SEPARATOR;
        $dirname = dirname(__FILE__);
        $realDocumentRoot = str_replace($sep.'mg-core'.$sep.'controllers', '', $dirname);
        $zip->extractTo($realDocumentRoot);
        $zip->close();
        unlink($file);
        return true;
      } else {
        return false;
      }
    }
    return false;
  }

  /**
   * Парсинг XML и импортв БД товаров.
   * @param $filename - путь к файлу архива с данными.
   */
  public function processImportXml($filename) {
    $importOnlyNew = false;
    $sep = DIRECTORY_SEPARATOR;
    $dirname = dirname(__FILE__);
    $realDocumentRoot = str_replace($sep.'mg-core'.$sep.'controllers', '', $dirname);

    $lastPositionProduct = $_SESSION['lastCountProduct1cImport'];
    $lastPositionOffer = $_SESSION['lastCountOffer1cImport'];
    $xml = $this->getImportXml($filename);

    if ($xml && $filename == 'import.xml') {

      foreach ($xml->Каталог->attributes() as $key => $val) {
        if ($key == 'СодержитТолькоИзменения' && $val == "true") {
          $importOnlyNew = true;
        }
      }

      if (isset($xml->Каталог->СодержитТолькоИзменения)) {
        $importOnlyNew = $xml->Каталог->СодержитТолькоИзменения[0] == 'true'?true:false;
      }

      if (empty($lastPositionProduct) && $importOnlyNew == false) {
        // если установлена директива CLEAR_CATALOG = 1 в config.ini, то удаляем товары перед синхронизацией с 1с
        if(CLEAR_1С_CATALOG!='CLEAR_1С_CATALOG' && CLEAR_1С_CATALOG!=0){
          DB::query('DELETE FROM `'.PREFIX.'product` WHERE 1');
          DB::query('DELETE FROM `'.PREFIX.'category` WHERE 1');
        }
      }

      $category = $this->groupsGreate($xml->Классификатор, $category, 0);
      $this->propertyСreate($xml->Классификатор->Свойства);

      $model = new Models_Product;
      $currentPosition = 0;

      foreach ($xml->Каталог->Товары[0] as $item) {
        $currentPosition++;
        if ($currentPosition <= $lastPositionProduct) {
          continue;
        }

        // Добавляем изображение товара в папку uploads 
        $imageUrl = array();
        if (isset($item->Картинка)) {
          foreach ($item->Картинка as $img) {

            $path = $item->Картинка;

            $image = basename($item->Картинка);
            if (!empty($image) && is_file($path)) {
              copy($path, $realDocumentRoot.$sep.'uploads'.$sep.$image);
              $widthPreview = MG::getSetting('widthPreview')?MG::getSetting('widthPreview'):200;
              $widthSmallPreview = MG::getSetting('widthSmallPreview')?MG::getSetting('widthSmallPreview'):50;
              $heightPreview = MG::getSetting('heightPreview')?MG::getSetting('heightPreview'):100;
              $heightSmallPreview = MG::getSetting('heightSmallPreview')?MG::getSetting('heightSmallPreview'):50;
              $upload = new Upload(false);
              $upload->_reSizeImage('70_'.$image, $realDocumentRoot.$sep.'uploads'.$sep.$image, $widthPreview, $heightPreview);
              // миниатюра по размерам из БД (150*100)
              $upload->_reSizeImage('30_'.$image, $realDocumentRoot.$sep.'uploads'.$sep.$image, $widthSmallPreview, $heightSmallPreview);
            }
            $imageUrl[] = $image;
          }
        }

        $imageUrl = implode($imageUrl, "|");
        $id = (string) $item->Группы->Ид[0];
        $name = (string) $item->Наименование[0];
        $description = '';
        if (isset($item->Описание)) {
          $description = (string) $item->Описание[0];
        }

        foreach ($item->ЗначенияРеквизитов->ЗначениеРеквизита as $row) {
          if ($row->Наименование == 'Полное наименование') {
            $name = $row->Значение;
          }
        }

        $code = !empty($item->Артикул[0])?$item->Артикул[0]:$item->ШтрихКод[0];

        $id_1c = (string) $item->Ид[0];
        $dataProd = array(
          'title' => $name,
          'url' => str_replace('\\', '-', URL::prepareUrl(MG::translitIt($name))),
          'code' => $code,
          'price' => 0,
          'description' => $description,
          'old_price' => '',
          'image_url' => $imageUrl,
          'count' => 0,
          'cat_id' => $category[$id]['category_id'],
          'meta_title' => $name,
          'meta_keywords' => $name,
          'meta_desc' => MG::textMore($description, 157),
          'recommend' => 0,
          'activity' => 1,
          'new' => 0,
          'related' => '',
          'inside_cat' => '',
          '1c_id' => $id_1c,
          'weight' => '0',
        );
        
        MG::loger($importOnlyNew);
        MG::loger(print_r($dataProd,1));
        if($importOnlyNew){    
          unset($dataProd['description']);
          unset($dataProd['image_url']);
          unset($dataProd['meta_title']);
          unset($dataProd['meta_keywords']);
          unset($dataProd['recommend']);
          unset($dataProd['activity']);
          unset($dataProd['new']);
          unset($dataProd['related']);
          unset($dataProd['inside_cat']);
          unset($dataProd['weight']);
        }
        MG::loger(print_r($dataProd,1));

        $res = DB::query('SELECT * 
          FROM '.PREFIX.'product WHERE `1c_id`='.DB::quote($id_1c));

        if ($row = DB::fetchAssoc($res)) {
          DB::query('
           UPDATE `'.PREFIX.'product`
           SET '.DB::buildPartQuery($dataProd).'
           WHERE `1c_id`='.DB::quote($id_1c)
          );
        } else {
          $model->addProduct($dataProd);
        }

        // Привязываем свойства.
        if (isset($item->ЗначенияСвойств)) {
          foreach ($item->ЗначенияСвойств->ЗначенияСвойства as $prop) {
            $this->propertyConnect($id_1c, $prop->Ид, $prop->Значение, $category[$id]['category_id']);
          }
        }

        $execTime = microtime(true) - $this->startTime;
        if ($execTime + 1 >= $this->maxExecTime) {
          header("Content-type: text/xml; charset=utf-8");
          echo "\xEF\xBB\xBF";
          echo "progress\r\n";
          echo "Выгружено товаров: $currentPosition";
          $_SESSION['lastCountProduct1cImport'] = $currentPosition;
          exit();
        }
      }

      if ($this->unlinkFile) {
        unlink($filename);
      }

      $_SESSION['lastCountProduct1cImport'] = 0;
    } elseif ($xml && $filename == 'offers.xml') {

      $currentPosition = 0;
      foreach ($xml->ПакетПредложений[0]->Предложения[0] as $item) {

        $currentPosition++;
        if ($currentPosition <= $lastPositionOffer) {
          continue;
        }

        $id = (string) $item->Ид[0];
        $price = (string) $item->Цены->Цена->ЦенаЗаЕдиницу[0];
        $count = (string) $item->Количество[0];
        $code = !empty($item->Артикул[0])?$item->Артикул[0]:$item->ШтрихКод[0];

        $partProd = array(
          'price' => $price,
          'count' => $count,
          'code' => $code,
          'price_course' => $price
        );

        DB::query('
           UPDATE `'.PREFIX.'product`
           SET '.DB::buildPartQuery($partProd).'
           WHERE 1c_id = '.DB::quote($id).'
        ');

        $execTime = microtime(true) - $this->startTime;

        if ($execTime + 1 >= $this->maxExecTime) {
          header("Content-type: text/xml; charset=utf-8");
          echo "\xEF\xBB\xBF";
          echo "progress\r\n";
          echo "Выгружено предложений: $currentPosition";
          $_SESSION['lastCountOffer1cImport'] = $currentPosition;
          exit();
        }
      }

      if ($this->unlinkFile) {
        unlink($filename);
      }

      $_SESSION['lastCountOffer1cImport'] = 0;
      Storage::clear();
    } else {
      echo "Ошибка загрузки XML\n";
      foreach (libxml_get_errors() as $error) {
        echo "\t", $error->message;
        exit;
      }
    }
  }

  /**
   * Обход дерева групп полученных из 1С
   * @param $xml - дерево с данными, 
   * @param $category - категория, 
   * @param $parent - родительская категория.
   */
  function groupsGreate($xml, $category, $parent) {

    if (!$parent) {
      $parent = array('category_id' => 0, 'name' => '');
    }

    if (!isset($xml->Группы)) {
      return $category;
    }

    foreach ($xml->Группы->Группа as $category_data) {
      $name = (string) $category_data->Наименование;
      $cnt = (string) $category_data->Ид;
      $category[$cnt]['1c_id'] = $cnt;
      $category[$cnt]['name'] = $name;
      $category[$cnt]['parent_id'] = $parent['category_id'];
      $category[$cnt]['parentname'] = $parent['name'];
      $category[$cnt]['description'] = "Описание";
      $category[$cnt]['category_id'] = $this->newCategory($category[$cnt]);
      $category = $this->groupsGreate($category_data, $category, $category[$cnt]);
    }

    return $category;
  }

  /**
   * Создание новой категории.
   * @param $category - категория.
   */
  function newCategory($category) {

    $url = URL::prepareUrl(MG::translitIt($category['name'], 1));
    $parent_url = MG::get('category')->getParentUrl($category['parent_id']);
    $parent = URL::prepareUrl(MG::translitIt($category['parentname'], 1));

    $data = array(
      'title' => $category['name'],
      'url' => str_replace(array('/','\\'),'-',$url),
      'parent' => $category['parent_id'],
      'html_content' => $category['description'],
      'meta_title' => $category['name'],
      'meta_keywords' => $category['name'],
      'meta_desc' => MG::textMore($category['description'], 157),
      'invisible' => 0,
      'parent_url' => $parent_url,
      '1c_id' => $category['1c_id'],
    );

    $res = DB::query('SELECT *
      FROM '.PREFIX.'category 
      WHERE `1c_id`='.DB::quote($category['1c_id']));
    if ($row = DB::fetchAssoc($res)) {

      DB::query('
        UPDATE `'.PREFIX.'category`
        SET '.DB::buildPartQuery($data).'
        WHERE `1c_id`='.DB::quote($category['1c_id'])
      );

      return $row['id'];
    } else {
      $data = MG::get('category')->addCategory($data);
      return $data['id'];
    }

    return 0;
  }

  /**
   * Создание свойств для товаров.
   * @param $xml - дерево с данными
   */
  function propertyСreate($xml) {  
    foreach ($xml->Свойство as $property_data) {
      $this->propertyСreateProcess($property_data);     
    }
    foreach ($xml->СвойствоНоменклатуры as $property_data) {
      $this->propertyСreateProcess($property_data);     
    }
  }
  
   /**
   * Процесс создания характеристик
   * @param $xml - дерево с данными
   */
  function propertyСreateProcess($property_data) {
  
      $id = (string) $property_data->Ид;
      $name = (string) $property_data->Наименование;

      $property['1c_id'] = $id;
      $property['name'] = $name;

      $res = DB::query('SELECT * 
        FROM '.PREFIX.'property 
        WHERE `1c_id`='.DB::quote($property['1c_id']));
      if ($row = DB::fetchAssoc($res)) {
        DB::query('
          UPDATE `'.PREFIX.'property`
          SET `name` ='.DB::quote($property['name']).'
          WHERE `1c_id`='.DB::quote($property['1c_id'])
        );
      } else {
        DB::query("
          INSERT INTO `".PREFIX."property`       
          VALUES ('',".DB::quote($property['name']).",'string','','','1','1','','1','','checkbox',".DB::quote($property['1c_id']).")"
        );
      }
 
  }

  /**
   * Привязка свойств к товару, категории и установка значений
   * @param $productId1c - id товара из 1с в бзе сайта.
   * @param $propId1c - id обрабатываемого товара из 1с.
   * @param $propValue - значение свойства.
   * @param $categoryId - id категории.
   */
  function propertyConnect($productId1c, $propId1c, $propValue, $categoryId) {

    // Получаем реальные id для товара и свойства из базы данных.
    $res = DB::query('SELECT id FROM '.PREFIX.'product WHERE `1c_id`='.DB::quote($productId1c));
    if ($row = DB::fetchAssoc($res)) {
      $productId = $row['id'];
    } else {
      return false;
    }

    $res = DB::query('SELECT id FROM '.PREFIX.'property WHERE `1c_id`='.DB::quote($propId1c));
    if ($row = DB::fetchAssoc($res)) {
      $propertyId = $row['id'];
    } else {
      return false;
    }

    // Проверим, если такой привязки еще нет между категориями и свойствами, то создадим ее для категории.
    $res = DB::query('
      SELECT category_id      
      FROM '.PREFIX.'category_user_property 
      WHERE `property_id`='.DB::quote($propertyId).'
         and `category_id` = '.DB::quote($categoryId));

    if (!DB::numRows($res)) {
      DB::query("
        INSERT INTO `".PREFIX."category_user_property` (`category_id`, `property_id`)
        VALUES (".DB::quote($categoryId).", ".DB::quote($propertyId).")");
    }

    // Проверим, если такой привязки еще нет между продуктом и свойством ,
    //  то создадим ее для продукта.
    $res = DB::query('
     SELECT product_id
     FROM '.PREFIX.'product_user_property
     WHERE `product_id`='.DB::quote($productId).'
       and `property_id` = '.DB::quote($propertyId));
    if (!DB::numRows($res)) {
      DB::query("
        INSERT INTO `".PREFIX."product_user_property` 
          (`product_id`, `property_id`, `value`, `product_margin`, `type_view`)
        VALUES (".DB::quote($productId).", ".DB::quote($propertyId).", ".DB::quote($propValue).", '', 'select')");
    } else {
      // если привязка есть, то обновим данные
      DB::query('
        UPDATE `'.PREFIX.'product_user_property`
        SET `value` ='.DB::quote($propValue).'
        WHERE `product_id`='.DB::quote($productId).' and `property_id` = '.DB::quote($propertyId)
      );
    }

    return true;
  }

  /**
   * Парсинг XML.
   * @param $filename - исходный файл.
   */
  public function getImportXml($filename) { 
    $xml = simplexml_load_file($filename);
    return $xml;
  }

}