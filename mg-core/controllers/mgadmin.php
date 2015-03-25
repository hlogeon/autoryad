<?php

/**
 * Контроллер: Mgadmin
 *
 * Класс Controllers_Mgadmin предназначен для открытия панели администрирования.
 * - Формирует панель управления;
 * - Проверяет наличие обновлений движка на сервере;
 * - Обрабатывает запросы на получение выгрузок каталога.
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Controller
 */
class Controllers_Mgadmin extends BaseController {

  function __construct() {
    MG::disableTemplate();
    $model = new Models_Order;
    MG::addInformer(array('count' => $model->getNewOrdersCount(), 'class' => 'message-wrap', 'classIcon' => 'product-small-icon', 'isPlugin' => false, 'section' => 'orders', 'priority' => 80));
    if ('1' == User::getThis()->role) {
      MG::addInformer(array('count' => '', 'class' => 'message-wrap', 'classIcon' => 'statistic-icon', 'isPlugin' => false, 'section' => 'statistics', 'priority' => 10));
    }

    if (URL::get('csv')) {
      $model = new Models_Catalog;
      $model->exportToCsv();
    }
    if (URL::get('examplecsv')) {
      $model = new Models_Catalog;
      $model->getExampleCSV();
    }
    if (URL::get('examplecsvupdate')) {
      $model = new Models_Catalog;
      $model->getExampleCsvUpdate();
    }

    if (URL::get('yml')) {
      if (LIBXML_VERSION && extension_loaded('xmlwriter')) {
        $model = new YML;      
        if(URL::get('filename')){
          if(!$model->downloadYml(URL::get('filename'))){
              $response = array(
                'data' => array(),
                'status' => 'error',
                'msg' => 'Отсутствует запрашиваемый файл',
              );
              echo json_encode($response);            
          };
        }else{    
          $model->exportToYml();        
        }
      } else {
        $response = array(
          'data' => array(),
          'status' => 'error',
          'msg' => 'Отсутствует необходимое PHP расширение: xmlwriter',
        );
        echo json_encode($response);
      }
    }

    if ($orderId = URL::get('getOrderPdf')) {
      $model = new Models_Order;
      $model->getPdfOrder($orderId);
    }

    if ($orderId = URL::get('getExportCSV')) {
      $model = new Models_Order;
      $model->getExportCSV($orderId);
    }

    $this->data = array(
      'staticMenu' => MG::getSetting('staticMenu'),
      'themeBackground' => MG::getSetting('themeBackground'),
      'themeColor' => MG::getSetting('themeColor'),
      'languageLocale' => MG::getSetting('languageLocale'),
      'informerPanel' => MG::createInformerPanel(),
    );

    $this->pluginsList = PM::getPluginsInfo();
    $this->lang = MG::get('lang');

    if (!$checkLibs = MG::libExists()) {
      $newVer = Updata::checkUpdata(false, true);
      $this->newVersion = $newVer['lastVersion'];
    }
  }

}