<?php
/**
 * Класс Delivery - совершает все возможные операции с способами доставки товаров.
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Libraries
 */

class Delivery{

  /**
   * Получает параметры способа доставки по его id.
   *
   * @param string $url запрашиваемой  категории.
   * @return array массив с данными о категории.
   *
   */
  public function getDeliveryById($id){
    $result = array();
    $res = DB::query('
      SELECT *
      FROM `'.PREFIX.'delivery`
      WHERE id = "%s"
    ', $id);

    if(!empty($res)){
      if($deliv = DB::fetchAssoc($res)){
        $result = $deliv;
      }
    }

    $args = func_get_args();
    return MG::createHook( __CLASS__ ."_". __FUNCTION__, $result, $args );
  }

  /**
   * Бесплатная доставка если проходит по условию в найстройках.
   * @param id - id доставки
   * @return numeric
   */
  public function getCostDelivery($id) {
    $delivery = $this->getDeliveryById($id);
    $cart = new Models_Cart;
    $cartSumm = $cart->getTotalSumm();
    return $delivery['cost'];
  }
}