<?php

/*
  Plugin Name: Хлебные крошки
  Description: Выводит навигационную цепочку в каталоге товаров. Для вывода в файлах темы views/catalog.php и views/product.php необходимо вставить шорт код [brcr]
  Author: Дмитрий Гринчевский, Авдеев Марк
  Version: 2.1
 */
new BreadCrumbs;

class BreadCrumbs {

  public function __construct() {
    mgAddShortcode('brcr', array(__CLASS__, 'breadcrumbs'));
  }

  static function breadcrumbs() {
    $breadcrumbs = Storage::get(md5('breadcrumbs'.URL::getUrl()));
    if ($breadcrumbs == null) {
      $sections = URL::getSections();
      array_splice($sections, 0, 1);
      $breadcrumbs = '<a href="'.SITE.'/catalog">Каталог</a>';
      $max = count($sections);
      $i = 0;
      $par = '';
      foreach ($sections as $section) {
        $url = $section;
        $cat = 'title';

        $data = self::checkURLname('*', 'category', $section, 'url', $par);

        $url = $data[0]['parent_url'].$section;
        $res = $data[0]['title'];
        $par = $data[0]['id'];

        if (!$data[0]['title']) {
          $cat = 'name';
          $n = '';
          $result = self::checkURLname('*', 'product', $section, 'url', $n);
          $url = $data[0]['parent_url'].$sections[1].'/'.$sections[2];
          $categoryRes = self::checkURLname('url', 'category', $result[0]['cat_id'], 'id');
          $categoryRes1 = self::checkURLname('parent_url', 'category', $result[0]['cat_id'], 'id');
          $url = $categoryRes1[0]['parent_url'].$categoryRes[0]['url'].'/'.$result[0]['url'];
          $res = $result[0]['title'];
        }

        $i++;
        if ($max == $i) {
          $breadcrumbs .= ' » <span class="last-crumb">'.$res.'</span>';
        } else {
          $breadcrumbs .= ' » <a href="'.SITE.'/'.$url.'">'.$res.'</a>';
        }
      }
      $breadcrumbs = "<div class='bread-crumbs'>".$breadcrumbs."</div>";
      //сохраняем объект в кэш
      Storage::save(md5('breadcrumbs'.URL::getUrl()), $breadcrumbs);
    }
    return $breadcrumbs;
  }

  /**
   * Метод работает с БД, получая значение по передаваемым параметрам.
   *
   * @param string $col что.
   * @param string $table от куда.
   * @param string $name условие соответствие.
   * @return array массив с результатом.
   */
  static function checkURLname($col, $table, $name, $where1, $parent_id = '') {
    if ($parent_id) {
      $where2 = 'parent';
      $sql = 'SELECT %s FROM '.PREFIX.'%s   WHERE %s="%s"  AND %s="%s"';
      $result = DB::query($sql, $col, $table, $where1, $name, $where2, $parent_id);
    } else {
      $sql = 'SELECT %s FROM '.PREFIX.'%s   WHERE %s="%s "  ';
      $result = DB::query($sql, $col, $table, $where1, $name);
    }
    while ($row = DB::fetchArray($result)) {
      $categories[] = $row;
    }
    if ($result) {
      return $categories;
    }
  }

}