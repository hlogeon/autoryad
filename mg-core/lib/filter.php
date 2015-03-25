<?php

/**
 * Класс Filter - конструктор для фильтров в адинке.
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Libraries
 */
class Filter {

  // Массив категрорий.
  private $categories;
  private $property;

  public function __construct($property) {

    $this->property = $property;
  }

  /**
   * Получает примерно такой массив
   *  $array = array(
   *    'category' => '2',
   *    'price'=>array(10,100),
   *    'code'=> 'ABC',
   *    'rows'=> 20,
   *  );
   * @param type $data - массив параметров по фильтрам
   * @param type $sorter - массви содержищий поле, и направление сортировки
   *              $sorter = array('id', 'asc' );
   * по которому следует отсортировать выборку например ID и направление сортировки
   * 
   * @param bool $insideCat - учитывать вложенные категории или нет
   * @return string - часть запроса  WHERE
   */
  public function getFilterSql($data, $sorter = array(), $insideCat = true) {
    
    // удаляем возможный мусор от движка
    unset($data['mguniqueurl']);
    unset($data['mguniquetype']);

    $where = "[START]";

    // начинаем формировать условие
    foreach ($data as $k => $v) {

      // значение фильтра обязательно должно быть не пустым
      if ((!empty($v) && $v != 'null') || $v === 0 || $v === '0') {

        // если значением элемента передана часть запроса
        // 'rule1' => 'sql код'
        // 'rule2' => 'sql код'
        if(substr(mb_strtolower($k),0,4) ==  'rule'){
          $where .= " AND (".$v.")";
          continue;
        }
        
        $devide = ' = ';
        
        
        // если в значении передан масив двух значений, значит будет моледироваться оператор BETWEEN
        if (is_array($v) && count($v) >= 2) {

          //минимальное и максимальное значение обязательно должны быть заполнены
          if (empty($v[0]) || empty($v[1])) {
            continue;
          }

          $devide = ' BETWEEN ';
          if (!empty($v[2]) && $v[2] == 'date') {          
            $v = DB::quote(date('Y-m-d 00:00:00', strtotime($v[0])))." AND ".DB::quote(date('Y-m-d 23:59:59', strtotime($v[1])));
          } else {
            // экранируем данные
            $v = DB::quote($v[0])." AND ".DB::quote($v[1]+1);
          }
        } else {
          $v = DB::quote($v);
        }
     
        if ($k != 'cat_id') {
          $where.=" AND (".$k.$devide.$v.")";
        }
   
      }
    }

    // удаляем первый AND
    $where = str_replace("[START] AND", " ", $where);
    if ($where == "[START]") {
      $where = '';
    }

    //сортировка по полю
    if (!empty($sorter)) {
      if (!empty($sorter[0])) {
        if ($sorter[1] > 0) {
          $sorter[1] = 'asc';
        } else {
          $sorter[1] = 'desc';
        }
        if (empty($where)) {
          $where = " 1 = 1 ";
        }
        $where .= " ORDER BY ".$sorter[0]." ".$sorter[1];
      }
    }

    return $where;
  }

  /**
   * 
   *
   * @param array $array  массив с даннми категории.
   * @return int|bool id новой категории.
   */
  public function getHtmlFilter($submit = false) {
    $html = ''; 
    $lang = MG::get('lang');

    $html .= '<div class="mg-filter-head">';  
    $html .= '<div class="filter-preview"><div class="loader-search"></div><span></span></div>';
    foreach ($this->property as $name => $prop) {
      switch ($prop['type']) {
        case 'select': {

            $html .= '<div class="wrapper-field"><div class="filter-select"><div class="select"><span class="label-field">'.$prop['label'].':</span><select name="'.$name.'" class="last-items-dropdown">';
            foreach ($prop['option'] as $value => $text) {
              $selected = ($prop['selected'] === $value."") ? 'selected="selected"' : '';
              $html .= '<option value="'.$value.'" '.$selected.'>'.$text.'</option>';
            }
            $html .= '</select></div>';
            if ($name == 'cat_id') {
              $checked = '';
              if ($_POST['insideCat']) {
                $checked = 'checked=checked';
              }
              $html .= '<div class="checkbox">'.$lang['FILTR_PRICE7'].'<input type="checkbox"  name="insideCat" '.$checked.' /></div>';
            }
            $html .= '</div></div>';
            break;
          }

        case 'beetwen': {
            if ($prop['special'] == 'date') {
              $html .= '
             <div class="wrapper-field">
             <ul class="period-date">
               <li><span class="label-field">'.$prop['label1'].'</span> <input class="from-'.$prop['class'].'" type="text" name="'.$name.'[]" value="'.date('d.m.Y', strtotime($prop['min'])).'"></li>
               <li><span class="label-field">'.$prop['label2'].'</span> <input class="to-'.$prop['class'].'" type="text" name="'.$name.'[]" value="'.date('d.m.Y', strtotime($prop['max'])).'"></li>
             </ul>
             </div>
           ';

            } else {
              $html .= '<div class="wrapper-field">
                <div class="price-slider-wrapper">
                <ul class="price-slider-list">
                 <li><span class="label-field">'.$prop['label1'].'</span><input type="text" id="minCost" class="price-input start-'.$prop['class'].'  price-input" data-fact-min="'.$prop['factMin'].'" name="'.$name.'[]" value="'.$prop['min'].'" /></li>
                 <li><span class="label-field">'.$prop['label2'].'</span><input type="text" id="maxCost" class="price-input end-'.$prop['class'].'  price-input" data-fact-max="'.$prop['factMax'].'" name="'.$name.'[]" value="'.$prop['max'].'" /><span>'.MG::getSetting('currency').'</span></li>
                </ul>
                <div class="clear"></div>
                <div id="price-slider"></div>
              </div>
              </div>';
            }

            if (!empty($prop['special'])) {
              $html .= '<input type="hidden"  name="'.$name.'[]" value="'.$prop['special'].'" />';
            }
            break;
          }

        case 'hidden': {
            $html .= ' <input type="hidden" name="'.$name.'" value="'.$prop['value'].'" class="price-input"/>';
            break;
          }

        case 'text': {
            $html .= '<div class="wrapper-field"><span class="label-field">'.$prop['label'].':</span> <input type="text" name="'.$name.'" value="'.$prop['value'].'" class="price-input"/></div>';
            break;
          }


        default:
          break;
      }
    }
    $html .= '</div>';
    
    if(MG::get('controller')=='controllers_catalog' || $_REQUEST['mguniqueurl'] == 'catalog.php'){
      $html .= '<div class="mg-filter-body">';
        
      $html .= $this->getHtmlPropertyFilter();

      $html .= '</div>';
    }
    if(MG::get('controller')=='controllers_users' || $_REQUEST['mguniqueurl'] == 'users.php') {
      $html .= '<div class="mg-filter-body">';
     
      $html .= '</div>';
    }
   
    $html .= '<div class="wrapper-field filter-buttons">';
	if($submit){
	  $html.='<input type="submit" value="'.$lang['FILTR_PRICE8'].'" class="filter-btn">';
	  $html.='<a href="'.SITE.URL::getClearUri().'" class="refreshFilter"><span>'.$lang['CLEAR'].'</span></a>'; 
	}else{
      $html.='<a class="filter-now"><span>'.$lang['FILTR_PRICE8'].'</span></a>';
	  $html.='<a href="javascript:void(0);" class="refreshFilter"><span>'.$lang['CLEAR'].'</span></a>'; 
	}
    
    $html .= '</div>';

    return '<form name="filter" class="filter-form" data-print-res="'.MG::getSetting('printFilterResult').'">'.$html.'</form>';
  }



 /**
   * Строить HTML верстку для фильтра по характеристикам
   *
   * @param array $array  массив с даннми категории.
   * @return int|bool id новой категории.
   */
  public function getHtmlPropertyFilter($submit=false) { 
     return '';
  }
  
  
   /**
   * Строит sql запрос для поиска всех id  товаров удовлетворяющих фильтру по характеристикам
   *
   * @param array $properties  массив с ключами переданных массивов с характеристиками
   * @return array массив id товаров.
   */
  public function getProductIdByFilter($properties) {
    $result = array();
    
  
    $sql = '
			SELECT p.id
			FROM `'.PREFIX.'product` as p';
   
   // viewData($properties);
    
    foreach ($properties as $id => $property) {
     
      if(empty($id)){
        continue;
      }	   
      
      // если указан параметр по умолчанию из выпадающего спи ска "не выбрано"
      if(count($property)===1 && empty($property[0])){
        continue;
      }
      
      $sql .= ' JOIN `'.PREFIX.'product_user_property` as pup'.$id.' ON ';
       
      foreach ($property as $cnt=>$value) {
        
        //Если мы уже составляли условие для слайдера, то пропускаем шаг. #ДОБАВЛЕНО
        if($property[0] == "slider" && $cnt > 0){
          continue;
        }
        
        $value = str_replace('+', '[[.plus-sign.]]', $value);
        $value = str_replace('*', '[[.asterisk.]]', $value);   
        $value = str_replace('(', '[[.left-parenthesis.]]', $value);   
        $value = str_replace(')', '[[.right-parenthesis.]]', $value); 
        $value = str_replace('?', '[[.question-mark.]]', $value);        	
    
        $sql .= '(pup'.$id.'.product_id = p.id AND ';
        $sql .= '((pup'.$id.'.property_id = '.DB::quote($id).') AND ';
        
        //Проверяем, выводится ли тип характеристика слайдером. #ДОБАВЛЕНО
        if($property[0] == "slider"){
          $sql .= 'pup'.$id.'.value BETWEEN '.$property[1].' AND '.$property[2].')) OR ';
          continue;
        }
        
        $sql .= '(LCASE(concat("|",pup'.$id.'.value,"|"))  REGEXP LCASE("[[.vertical-line.]]'.DB::quote(htmlspecialchars_decode($value),true).'(#.*#)?[[.vertical-line.]]")))) OR ';
        
      }
      
      $sql = substr($sql, 0, -4);    
      
    }
    
    $res = DB::query($sql);    
    while ($row = DB::fetchAssoc($res)){
      $result[] = $row['id'];
    }
    
    $pIds = implode(',',$result);
    if(!empty($pIds)){      
      $sql = '
        SELECT  pup.property_id, pup.value
        FROM `'.PREFIX.'product_user_property` as pup
        WHERE pup.product_id IN ('.DB::quote($pIds,1).')
       ';

      $res = DB::query($sql);    
      while ($row = DB::fetchAssoc($res)){
        if(strpos($row['value'], '|')===false){
          $this->accessValues[$row['property_id']][] = $row['value'];        
        }
      }
    }
    
    return $result;
  }
  
  
}