<?php
 /**
 *  Файл представления Index - выводит сгенерированную движком информацию на главной странице магазина.
 *  В этом файле доступны следующие данные:
 *   <code>     
 *    $data['active'] => состояние активации пользователя.
 *    $data['msg'] => сообщение.
 *    $data['step'] => стадия оформления заказа.
 *    $data['delivery'] => массив способов доставки.
 *    $data['paymentArray'] => массив способов оплаты.
 *    $data['paramArray'] => массив способов оплаты.
 *    $data['id'] => id заказа.
 *    $data['orderNumber'] => номер заказа.
 *    $data['summ'] => сумма заказа.
 *    $data['pay'] => оплата. 
 *    $data['payMentView'] => файл представления дляспособа оплаты.
 *    $data['currency'] => валюта магазина
 *    $data['userInfo'] => информация о пользователе,
 *    $data['orderInfo'] => информация о заказе,
 *    $data['meta_title'] => 'Значение meta тега для страницы order'
 *    $data['meta_keywords'] => 'Значение meta_keywords тега для страницы order'
 *    $data['meta_desc'] => 'Значение meta_desc тега для страницы order'
 *   </code>
 *   
 *   Получить подробную информацию о каждом элементе массива $data, можно вставив следующую строку кода в верстку файла.
 *   <code>     
 *    <php viewData($data['saleProducts']); ?>  
 *   </code>
 * 
 *   Вывести содержание элементов массива $data, можно вставив следующую строку кода в верстку файла.
 *   <code>     
 *    <php echo $data['saleProducts']; ?>  
 *   </code>
 * 
 *   <b>Внимание!</b> Файл предназначен только для форматированного вывода данных на страницу магазина. Категорически не рекомендуется выполнять в нем запросы к БД сайта или реализовывать сложую программную логику логику.
 *   @author Авдеев Марк <mark-avdeev@mail.ru>
 *   @package moguta.cms
 *   @subpackage Views
 */

?>
<?php mgAddMeta('<script type="text/javascript" src="'.SCRIPT.'jquery.maskedinput.min.js"></script>'); ?>
<?php mgAddMeta('<script type="text/javascript" src="'.SCRIPT.'standard/js/order.js"></script>'); ?>
<?php mgAddMeta('<link href="'.SCRIPT.'standard/css/datepicker.css" rel="stylesheet" type="text/css">'); ?>
<?php

if(!empty($data['fileToOrder'])){?>
<h1 class="new-products-title"><?php echo $data['fileToOrder']['infoMsg']?></h1>
<?php if (!empty($data['fileToOrder']['electroInfo'])){?>
<ul>
<?php
foreach ($data['fileToOrder']['electroInfo'] as $item){?>
<li>Скачать: <a href="<?php echo $item['link'] ?>"><?php echo $item['title'] ?></a></li>
<?php }?>
</ul>
<?php
}

}else{
switch ($data['step']) {
  case 1:
    mgSEO($data);
    ?>
    <h1 class="new-products-title">Оформление заказа</h1>

    <div class="checkout-form-wrapper">
      <?php if ($data['msg']): ?>
        <div class="mg-error">
          <?php echo $data['msg'] ?>
        </div>   
      <?php endif; ?>
      <div class="payment-option">
        <p class="custom-text">Для того чтобы совершить покупку, корректно заполните форму ниже.</p>
        <form action="<?php echo SITE ?>/order?creation=1" method="post">
          <ul class="form-list">
            <li>Email:<span class="red-star">*</span></li>
            <li><input type="text" name="email" value="<?php echo $_POST['email'] ?>"></li>
            <li>Телефон:<span class="red-star">*</span>				
            </li>
            <li>
              <input type="text" name="phone" value="<?php echo $_POST['phone'] ?>">  
            </li>
            <li>Ф.И.О.:</li>
            <li><input type="text" name="fio" value="<?php echo $_POST['fio'] ?>"></li>
            <li>Адрес доставки:</li>
            <li><textarea class="address-area" name="address"><?php echo $_POST['address'] ?></textarea></li>
            <li>Комментарий к заказу:</li>
            <li><textarea class="address-area" name="info"><?php echo $_POST['info'] ?></textarea></li>
            <li>Плательщик:</li>
            <li>
              <select name="customer">
                <?php $selected = $_POST['customer']=="yur"?'selected':'';?>
                <option value="fiz">Физическое лицо</option>
                <option value="yur" <?php echo $selected?>>Юридическое лицо</option>
              </select>
            </li>            
          </ul>
          
          <?php if($_POST['customer']!="yur") {$style = 'style="display:none"';}?>
            <ul class="form-list yur-field" <?php echo $style?>>
                <li>Юр. лицо:</li>
                <li><input type="text" name="yur_info[nameyur]" value="<?php echo $_POST['yur_info']['nameyur'] ?>"></li>
                <li>Юр. адрес:</li>
                <li><input type="text" name="yur_info[adress]" value="<?php echo $_POST['yur_info']['adress'] ?>"></li>
                <li>ИНН:</li>
                <li><input type="text" name="yur_info[inn]" value="<?php echo $_POST['yur_info']['inn'] ?>"></li>
                <li>КПП:</li>
                <li><input type="text" name="yur_info[kpp]" value="<?php echo $_POST['yur_info']['kpp'] ?>"></li>
                <li>Банк:</li>
                <li><input type="text" name="yur_info[bank]" value="<?php echo $_POST['yur_info']['bank'] ?>"></li>
                <li>БИК:</li>
                <li><input type="text" name="yur_info[bik]" value="<?php echo $_POST['yur_info']['bik'] ?>"></li>
                <li>К/Сч:</li>
                <li><input type="text" name="yur_info[ks]" value="<?php echo $_POST['yur_info']['ks'] ?>"></li>
                <li>Р/Сч:</li>
                <li><input type="text" name="yur_info[rs]" value="<?php echo $_POST['yur_info']['rs'] ?>"></li>
            </ul>
          <div class="delivery-vs-payment">
            <?php if (''!=$data['delivery']): ?>
              <p class="delivery-text">Доставка</p>
              <ul class="delivery-details-list">
                <?php foreach ($data['delivery'] as $delivery): ?>
                  <li <?php echo ($delivery['checked']) ? 'class = "active"' : 'class = "noneactive"' ?>>
                    <label  data-delivery-date = "<?php echo $delivery['date'];?>">
                      <input type="radio" name="delivery" <?php if ($delivery['checked']) echo 'checked' ?>  value="<?php echo $delivery['id'] ?>">
                        <?php echo $delivery['description'] ?> <?php echo MG::numberFormat($delivery['cost']); ?> <?php echo '&nbsp;'.$data['currency']; ?>
                    </label>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>
              <div class="delivery-date" style="display:none; margin-bottom: 5px;">
                    <p class="delivery-text">Дата доставки:</p>
                    <input type='text' name='date_delivery' placeholder='Дата доставки' value="<?php echo $_POST['date_delivery'] ?>" >
              </div>
              
            <p class="delivery-text">Способ оплаты</p>
            <ul class="payment-details-list">
              <?php if (count($data['delivery'])>1&&!$_POST['payment']): ?>
                <li>Укажите способ доставки, чтобы выбрать способ оплаты</li>
                  <?php elseif (''!=$data['paymentArray']):?>       
                  <?php echo $data['paymentArray'] ?>             
                  <?php
              else: ?> 
                <li>Нет доступных способов оплаты</li>
              <?php endif; ?>
            </ul> 
            <div class="summ-info" style="margin-bottom: 5px;">
              <span class="delivery-text">Сумма заказа:</span>
              <span class="order-summ"><?php echo $data['summOrder']?></span>
              <span class="delivery-summ"></span>
            </div>
          </div>
           <?php if ($data['captcha']) { ?>   
            <div class="checkCapcha" style="display:inline-block">
              <img style="margin-top: 5px; border: 1px solid gray;" src = "captcha.html" width="140" height="36">
            <div>Введите текст с картинки:<span class="red-star">*</span> </div>
            <input type="text" name="capcha" class="captcha"></div>
           <?php }; ?>
          <div class="clear"></div>
          <input type="submit" name="toOrder" class="checkout-btn default-btn" value="Оформить заказ" disabled>
        </form>
        <div class="clear">&nbsp;</div>
      </div>
    </div>
    <?php
    break;
  case 2:
    $data['meta_title'] = 'Оплата заказа';
    mgSEO($data);
    if ($data['msg']):
      ?>
      <div class="errorSend">
      <?php echo $data['msg'] ?>
      </div>   
    <?php endif; ?>

    <h1 class="new-products-title">Оплата заказа</h1>
    <?php if (!$data['pay']&&$data['payment']=='fail'): ?>
      <div class="payment-form-block"><span style="color:red"><?php echo $data['message']; ?></span></div>
    <?php else: ?>
      <div class="payment-form-block">
          <div class="text-success">Ваша заявка <strong>№ <?php echo $data['orderNumber'] ?></strong> принята!</div>
        <br>На Ваш электронный адрес выслано письмо для подтверждения заказа.
        <hr>
        <p>Оплатить заказ <b>№ <?php echo $data['orderNumber'] ?> </b> на сумму <b><?php echo MG::numberFormat($data['summ']) ?></b>  <?php echo $data['currency']; ?> </p></div>
    <?php
    endif;
    
    if($data['payMentView']){
      include($data['payMentView']);     
    }  
    break;
  case 3:
    $data['meta_title'] = 'Подтверждение заказа';
    mgSEO($data);
    if ($data['msg']):
      ?>
      <div class="errorSend">
      <?php echo $data['msg'] ?>
      </div>   
    <?php endif;

    if ($data['id']):
      ?>
      <h1 class="new-products-title">Подтверждение заказа</h1>
      <p class="auth-text">Заказ №<?php echo $data['orderNumber'] ?> подтвержден</p>
    <?php
    endif;
     //если пользователь не активизирован, то показываем форму задания пароля
    if ($data['active']):
      ?>
      <br><span style="color:green">Вы успешно зарегистрировались на сайте <?php echo SITE ?> и можете отслеживать заказ в личном кабинете.</span>
      <br><br>Ваш логин <strong><?php echo $data['active'] ?></strong>.
      <br><br>Задайте пароль для доступа в личный кабинет.
      <form action = "<?php echo SITE ?>/forgotpass" method = "POST">
        <table>
          <tr>
            <td>Новый пароль (не менее 5 символов)</td>
            <td><input type="password" name="newPass"></td>
          </tr>
          <tr>
            <td>Подтвердите новый пароль</td>
            <td><input type="password" name="pass2"></td>
          </tr>
        </table>
        <input type = "submit" class="enter-btn ie7-fix" name="chengePass" value = "Сохранить" />
      </form>
      <?php
    endif;
    break;

  case 4:
    ?>

    <h1 class="new-products-title">Оплатите заказ № <?php echo $data['orderNumber'] ?> на сумму <?php echo MG::numberFormat($data['summ']) ?> <?php echo $data['currency'] ?></h1>

    <?php
    if($data['payMentView']){
      include($data['payMentView']);
    }else{
    ?>
        <span>  Ваш способ не предусматривает оплату электронными деньгами</span><br><span> Вы должны оплатить заказ в соответствии с указанным способом оплаты! </span>
    <?php 
    }
}
}
?>