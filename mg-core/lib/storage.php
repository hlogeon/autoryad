<?php

/**
 * Класс Storage - предназначен для сохранения сериализованных массивов в базе.
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Libraries
 */

class Storage {

  static private $_instance = null;
  static private $cacheTime = null;
  static public $noCache = null;
  static public $сacheMode = null;
  static public $memcache_obj = null;
 
  private function __construct() {
  
    $result = DB::query("
      SELECT `option`, `value`
      FROM `".PREFIX."setting`
      WHERE `option` IN ('cacheObject','cacheMode','cacheTime','cacheHost','cachePort')
      ");
    $settings = array();

    while ($row = DB::fetchAssoc($result)) {
      $settings[$row['option']] = $row['value'];
    }    
    
    if($settings['cacheObject']=='true'){
       define(CACHE, true);
    }else{
       define(CACHE, false);
    }
    if($cacheMode = $settings['cacheMode']){
      define(CACHE_MODE, $cacheMode);
    };
    if($cacheTime = $settings['cacheTime']){
      define(CACHE_TIME, $cacheTime);
    };
    if($cacheHost =  $settings['cacheHost']){
      define(CACHE_HOST, $cacheHost);
    };
    if($cachePort =  $settings['cachePort']){
      define(CACHE_PORT, $cachePort);
    };
    
    self::$noCache = !CACHE;
    self::$сacheMode = CACHE_MODE; // DB or FILE or MEMCACHE
    self::$cacheTime = CACHE_TIME;


    if(self::$сacheMode=='MEMCACHE'){   
      if(class_exists('Memcache')){
          self::$memcache_obj = new Memcache;
          self::$memcache_obj->connect(CACHE_HOST, CACHE_PORT) or die('Ошибка подключения к серверу MEMCACHE');
      }
    }
    
  }

  private function __clone() {
    
  }

  private function __wakeup() {
    
  }

  /**
   * Возвращет единственный экземпляр данного класса.
   * @return object - объект класса Storage
   */
  static public function getInstance() {
    if (is_null(self::$_instance)) {
      self::$_instance = new self;
    }
    return self::$_instance;
  }

  /**
   * Инициализирует единственный объект данного класса.
   * @return object - объект класса Storage
   */
  public static function init() {
    self::getInstance();
  }

  
  /**
   * Сохраняет данные в таблицу
   * @return object - объект класса Storage
   */
  public static function save($name,$value) {    
    if(self::$noCache){
      return false;
    }    
   
    if(is_array($value)|| is_string($value)){

      if(self::$сacheMode=='FILE'){
        if (function_exists('apc_add')) {
          apc_add(self::$dirCache.$name, addslashes(serialize($value)));     
          return true;
        } 
      }

     if(self::$сacheMode=='DB'){ 
        $cacheArray = array(
          'date_add' => time(), // 20 минут 
          'lifetime' => time() + self::$cacheTime, // 20 минут 
          'name' => $name,
          'value' => addslashes(serialize($value)),
          );      

        DB::query('
          INSERT INTO `'.PREFIX.'cache` SET '.DB::buildPartQuery($cacheArray).'
          ON DUPLICATE KEY UPDATE 
            lifetime = '.$cacheArray['lifetime'].',
            value = "'.$cacheArray['value'].'"');  
      }

      if(self::$сacheMode=='MEMCACHE'){
        if(class_exists('Memcache')){
          self::$memcache_obj->set($name, $value, MEMCACHE_COMPRESSED, self::$cacheTime);   
        }
      }

    } else{
      //echo 'Ошибка: невозможно создать кэш объекта!';
      return false;
    }
     
    return true;
  }

  /**
   * Возвращает сохраненный ранее массив 
   * @return object - объект класса Storage
   */
  public static function get($name) {
    
    if(self::$noCache){
      return null;
    }    

    if(self::$сacheMode=='FILE'){ 
     
 	  if (function_exists('apc_fetch')) {      
      apc_fetch($name);       
      return  apc_fetch($name);   
	  }
    }    
 
    if(self::$сacheMode=='MEMCACHE'){        
      if(class_exists('Memcache')){
        return  self::$memcache_obj->get($name);
	  }
    }
    
    if(self::$сacheMode=='DB'){   
      $result = DB::query('
        SELECT `value` 
        FROM `'.PREFIX.'cache`
        WHERE name='.DB::quote($name). "
        AND `lifetime` >= ".time());

      if($row = DB::fetchAssoc($result)){
        $res = unserialize(stripslashes($row['value']));
        return  $res;   
      }
    }
    return null; 
  }
  
   /**
   * Очищает кэш для всех объектов
   * @param $name - наименование объекта
   */
  public static function clear() {
 
    if(self::$сacheMode=='FILE'){ 
      if (function_exists('apc_clear_cache')) {      
         apc_clear_cache();
      }
    }    
 
    if(self::$сacheMode=='MEMCACHE'){
      if(class_exists('Memcache')){
        self::$memcache_obj->flush();
      }
    }
    
    if(self::$сacheMode=='DB'){   
      $result = DB::query("
      UPDATE  `".PREFIX."cache`
      SET  `value` =  '' ");
    }
         
    return true; 
  }
  
  /**
   * закрывает соединение с сервером
   * @param $name - наименование объекта
   */
  public static function close() {
  
    if(self::$сacheMode=='MEMCACHE'){
      self::$memcache_obj->close();
    }     
    return true; 
  }

}