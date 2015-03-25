<?php

error_reporting(0);	
define('SITE', 'http://'.$_SERVER['SERVER_NAME'].URL::getCutSection());
$prefix = empty($_REQUEST['prefix']) ? 'mg_' : $_REQUEST['prefix'];
$_SESSION = array();

 

if ($_REQUEST['siteName']) {
  $siteName = clearData($_REQUEST['siteName']);
} else {
  $siteName = $_SERVER['SERVER_NAME'];
}

$aLogin = 'Администратор';
$aPass = clearData($_REQUEST['pass']);
$adminEmail = clearData($_REQUEST['email']);


if ($_REQUEST['step1']) {
  $step = 1;
}

if ($_REQUEST['step2']) {
  $step = 0;
  if ('ok'==$_REQUEST['agree']) {
    $step = 2;
  }
  
  if($checkLibs = libExists()){
    $libError = true;
	$msg .= '<div class="wrapper-error">';
    $msg .= '<div class="error-system-install">Установка системы невозможна!</div>';
    foreach ($checkLibs as $message){
        $msg .= '<span class="error-lib">'.$message.'</span><br>';
    }
    $msg .= '</div>';
  }
  
  if($_SERVER['HTTP_HOST']=='localhost'){
    $host = 'localhost';
    $user = 'root';
    $password = '';
    $nameDB = 'BASE_NAME';
  };
	
}

if ($_REQUEST['step3']) {
  
  $host = clearData($_REQUEST['host']);
  $user = clearData($_REQUEST['user']);
  $password = clearData($_REQUEST['password']);
  $nameDB = clearData($_REQUEST['nameDB']);
  $engineType = clearData($_REQUEST['engineType']);
  $step = 3;
  if(!empty($engineType)){
    $checkedTest = 'checked=checked'; //отметка "по умолчанию" типа магазина -тестовый
  }
  
  //Тестирование введенных пользоватем параметров.
  try {

    if (!mysql_connect($host, $user, $password)) {
      throw new Exception('<span class="no-bd">Невозможно установить соединение.</span>');
    }

    if (!mysql_select_db($nameDB)) {
      throw new Exception('<span class="error-db">Ошибка! Невозможно выбрать указанную базу.</span>');
    }
  } catch (Exception $e) {
    //Выведет либо сообщение об ошибке подключения, либо об ошибке выбора.
    $msg = '<div class="msgError">'.$e->getMessage().'</div>';
	$step = 2;
  }
}

if ($_REQUEST['step4']) {
  $step = 4;


  $host = clearData($_REQUEST['host']);
  $user = clearData($_REQUEST['user']);
  $password = clearData($_REQUEST['password']);
  $nameDB = clearData($_REQUEST['nameDB']);
  $engineType = clearData($_REQUEST['engineType']);
  
  if(!empty($engineType)){
    $checkedTest = 'checked=checked'; //отметка "по умолчанию" типа магазина -тестовый
  }
  
  if (!$_REQUEST['existDB']) {
     
    // Проверка адреса сайта.
    if (''==$siteName) {
      $msg .= '<div class="msgError">Ошибка!
        Не заполнено имя сайта</div>';
    }
    // Проверка электронного адреса.
    if (!preg_match(
        '/^[-._a-zA-Z0-9]+@(?:[a-zA-Z0-9][-a-zA-Z0-9]{0,61}+\.)+[a-zA-Z]{2,6}$/', $adminEmail)
    ) {
      $msg .= '<span class="error-email">Ошибка!
        Неверно заполнено email администратора</span>';
    }
      
    // Пароль должен быть больше 5-ти символов.
    if (strlen($aPass)<5) {
      $msg .= '<span class="error-pass-count">Ошибка!
        Пароль менее 5 символов</span>';
      // Иначе, если не отмечено что пароль видимый.
    } elseif (!$_REQUEST['showPass']) {
      $rePass = clearData($_REQUEST['rePass']);

      // Проверяем равенство введенных паролей.
      if ($rePass!=$aPass) {
        $msg .= '<span class="error-pass">Ошибка!
          Введенные пароли не совпадают</span>';
      }
    }
     
  
  
    // Если ошибок нет
    if (!$msg) {

	  //Тестирование введенных пользоватем параметров.
	  try {

		if (!mysql_connect($host, $user, $password)) {
		  throw new Exception('<span class="no-bd">Невозможно установить соединение.</span>');
		}

		if (!mysql_select_db($nameDB)) {
		  throw new Exception('<span class="error-db">Ошибка! Невозможно выбрать указанную базу.</span>');
		}
	  } catch (Exception $e) {
		//Выведет либо сообщение об ошибке подключения, либо об ошибке выбора.
		$msg = '<div class="msgError">'.$e->getMessage().'</div>';
	  }
    
      if (file_exists('install/dbDump.php')) { //подгружаем основной дамп БД
      
        require_once ('install/dbDump.php');
   
        if(is_array($damp)){
         
          if (file_exists('install/dbDumpTestShop.php') && $checkedTest == 'checked=checked') { //если указано, что устанавливать тестовый магазин,
          //  то подгружаем дамп тестового магазина
            require_once ('install/dbDumpTestShop.php');
            
            if(is_array($dampTestShop)){
              $damp = array_merge($damp, $dampTestShop);
            }
            
            $imageFile = downloadTestImage('http://moguta.ru/uploads/install/upTest.zip');
            
            if($imageFile) extractZip($imageFile);
          }

          foreach ($damp as $sql) {
            mysql_query($sql) or die  ("Ошибка выполнения запроса:".mysql_error()."<br/>".$sql);
          }
        }
      }else{
	    echo "Внимание! Файл install/dbDump.php - не существует, не удалось установить движок! ";
	    exit();
	  }

      $cryptAPass = crypt($aPass);
      $sql = "
        INSERT INTO `".$prefix."user` 
          (`email`, `pass`,`name`,`role`,`activity`)
        VALUES ('".$adminEmail."','".$cryptAPass."', '".$aLogin."', 1, 1)
      ";
      mysql_query($sql);
      $sql = "
        UPDATE `".$prefix."setting`
        SET `value` = '".$siteName."'
        WHERE `option` = 'sitename'
      ";
      mysql_query($sql);
      $sql = "
        UPDATE `".$prefix."setting`
        SET `value` = '".$adminEmail."'
        WHERE `option` = 'adminEmail'
      ";
      mysql_query($sql);
      $sql = '
        SELECT *
        FROM `'.$prefix.'user`
        WHERE `email` = "'.$adminEmail.'"';
    
      $res = mysql_query($sql);    
      session_start();

      if ($row = mysql_fetch_object($res)) {        
        $_SESSION['user'] = $row;
      }
  
    }else{
      $step = 3;
    }
  } else {

    $sql = '
    SELECT id
    FROM `'.$prefix.'user`
    WHERE `role` = 1';

    $res = mysql_query($sql);

    if (!mysql_fetch_assoc($res))
      $msg .= '<div class="error-email">Ошибка! Недостаточно данных для установки системы. Не найден аккаунт с правами администратора</div>';
  }
  if (!$msg) {
    $step = 4;
    // Запись введенных данных в файл параметров config.ini
    $str = "[DB]\r\n";
    $str .="HOST = \"".$host."\"\r\n";
    $str .="USER = \"".$user."\"\r\n";
    $str .="PASSWORD = \"".$password."\"\r\n";
    $str .="NAME_BD = \"".$nameDB."\"\r\n";
    $str .="TABLE_PREFIX = \"".$prefix."\"\r\n";
    
    $str .="\r\n";
    $str .="[SETTINGS]\r\n";
    $str .=";Консоль выполненных sql запросов, для генерации страницы\r\n";
    $str .="DEBUG_SQL = 0\r\n";
    
    $str .="\r\n";
    $str .="; Протокол обмена данными с сайтом,(http или https)\r\n";
    $str .="PROTOCOL = \"http\"\r\n";

    $str .="\r\n";
    $str .="; Максимальное количество наименований товаров в одном заказе\r\n";
    $str .="MAX_COUNT_CART = 50\r\n";

    $str .="\r\n";
    $str .="; Переключение на полнотекстовый поиск, ускорит поиск в объемных\r\n"; 
    $str .="; каталогах (требует настроек на сервере, чтобы искать слова со\r\n"; 
    $str .="; знаком минуса/дефиса, а также коротких слов от 3 символов)\r\n"; 
    $str .="SEARCH_FULLTEXT = 0\r\n";
    
    $str .="\r\n";
    $str .="; Позволяет использовать объемные запросы на хостинге\r\n";
    $str .="SQL_BIG_SELECTS = 0\r\n";
    	
	  $str .="\r\n";
	  $str .= "; Выводит все характеристики если = 1,\r\n"; 
    $str .= "; выводит ссылку - \"показать все\" если = 0,\r\n";
    $str .= "FILTER_MODE = 1\r\n";    
    
    $str .="\r\n";
	  $str .= "; Выводит характеристики в фильтрах включая все вложенные подкатегории если = 1, \r\n"; 
    $str .= "; Выводит характеристи в фильтрах только используемые в открытой категории, если = 0\r\n";
    $str .= "FILTER_SUBCATGORY = 1\r\n";
    
    $str .="\r\n";
    $str .= "; Выводит заданное количетсво характеристик в фильтрах, остальные можно вывести только по клику на 'показать все', \r\n"; 
    $str .= "FILTER_COUNT_PROP = 3\r\n";    
        
    $str .= "\r\n";    
    $str .= "; Устанавливает сортировку товаров в каталоге. Пример price_course|desc, sort|asc \r\n";
    $str .= "FILTER_SORT = \"price_course|asc\"";
    
    $str .= "\r\n";
    $str .= "; Устанавливает количество попыток авторизации, после превышения данного значения возможность авторизации блокируется на 15 минут, \r\n"; 
    $str .= "LOGIN_ATTEMPT = 5\r\n";
        
    $str .= "\r\n";
    $str .= "; Префикс для номера заказа \r\n";
    $str .= "PREFIX_ORDER = \"M-010\" \r\n";
    
    $str .= "\r\n";
    $str .= "; При импорте каталога из 1с или commerceML удаляет содержимое каталога магазина \r\n";
    $str .= "CLEAR_1С_CATALOG = 1\r\n";
    
    $str .= "\r\n";
    $str .= "; Добавление капчи при оформлении заказа \r\n";
    $str .= "CAPTCHA_ORDER = 0\r\n";


    file_put_contents('config.ini', $str);

    $robots ="User-agent: Yandex
Allow: /uploads/
Disallow: /install/
Disallow: /mg-admin/
Disallow: /mg-core/
Disallow: /mg-pages/
Disallow: /mg-plugins/
Disallow: /mg-templates/
Disallow: /mg-admin 
Disallow: /personal
Disallow: /enter
Disallow: /forgotpass
Disallow: /registration
Host: ".$_SERVER['SERVER_NAME']."

User-agent: *
Disallow: /install/
Disallow: /mg-admin/
Disallow: /mg-core/
Disallow: /mg-pages/
Disallow: /mg-plugins/
Disallow: /mg-templates/
Disallow: /mg-admin 
Disallow: /personal
Disallow: /enter
Disallow: /forgotpass
Disallow: /registration
Host: ".$_SERVER['SERVER_NAME']."

Sitemap: http://".$_SERVER['SERVER_NAME']."/sitemap.xml";
    
    file_put_contents('robots.txt', $robots);
     
    $tables = array(
      'category',
      'category_user_property',
      'delivery',
      'order',
      'page',
      'payment',
      'plugins',
      'product',
      'product_user_property',
      'property',
      'setting',
      'user',
    );
  }
}

/**
 * Фильтрует введеные пользователем данные
 *
 * @param string $str передаваемая строка
 * @param int $strong строгость проверки
 * @return string отфильтрованная строка
 *
 */
function clearData($str, $strong = 2) {

  switch ($strong) {
    case 1:
      return trim($str);
    case 2:
      return trim(strip_tags($str));
  }
}
/**
 * скачивание архива с изображениями для тестового магазина
 * @param string $imageFile путь к архиву на сервере
 * @return string|boolean в случае успеха путь к архиву в папке инсталятора
 */
function downloadTestImage($imageFile){
    $imageZip = 'install/image.zip';
    $ch = curl_init($imageFile);
    $fp = fopen($imageZip, "w");
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_exec($ch);
    curl_close($ch);
    fclose($fp);
    
    if(file_exists($imageZip)) return $imageZip;
    
    return false;
}

  /**
   * Распаковывает архив.
   * После распаковки удаляет заданый архив.
   *
   * @param $file - название архива, который нужно распаковать
   * @return bool
   */
  function extractZip($file) {
    $imageFolder = SITE_DIR.'uploads/';
      
    if (file_exists($file)) {
      $zip = new ZipArchive;
      $res = $zip->open($file, ZIPARCHIVE::CREATE);

      if ($res === TRUE) {
        $zip->extractTo($imageFolder);
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
   * Функция проверяет наличие установленных библиотек PHP
   * @return boolean|srting сообщение об отсутствии необходимого модуля
   */
  function libExists() {
      
    if(!function_exists('curl_init')){
      $res[] = 'Пакет libcurl не установлен! Библиотека cURL не подключена.';
    }
    
    if(!extension_loaded('zip')){
      $res[] = 'Пакет zip не установлен! Библиотека ZipArchive не подключена.';
    }
    
    file_put_contents('temp.txt', ' ');
    
    if(!file_exists('temp.txt')){
      $res[] = 'Нет прав на создание файла. Загрузка архива с обновлением невозможна';
    }else{
      unlink('temp.txt');
    }
    
   
    if(!filesize('.htaccess')){
	
$htaccess = 'AddType image/x-icon .ico
AddDefaultCharset UTF-8
Options +FollowSymlinks
Options -Indexes

<IfModule mod_rewrite.c>
RewriteEngine on
RewriteCond %{REQUEST_FILENAME} !-f [OR]
RewriteCond %{REQUEST_URI} \.(ini|php)$
RewriteRule ^(.*) index.php [L,QSA]
</IfModule>

<IfModule mod_php5.c> 
php_flag magic_quotes_gpc Off
</IfModule>
';
      file_put_contents('.htaccess',$htaccess);
      if(!file_get_contents('.htaccess')){
        $res[] = 'Невозможно создать файл .htaccess ';
      }
      
      
    }    
   
    
    return $res;
  }

switch ($step) {
  case 0:
    require_once ('step0.php');
    break;
  case 1:
    require_once ('step1.php');
    break;
  case 2:
    require_once ('step2.php');
    break;
  case 3:
    require_once ('step3.php');
	break;
  case 4:
    require_once ('step4.php');
	break;
}
