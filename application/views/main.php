<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $seo_data['title'] ?></title>
  <meta name="keywords" content="<?= $seo_data['keywords'] ?>">
  <meta name="description" content="<?= $seo_data['description'] ?>">
  <meta property="og:title" content="<?= $seo_data['title'] ?>">
  <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">

    <?php
    switch ($seo_data['id']) {
        case Model_Seo::ID_MAIN:
            echo '<meta property="og:image" content="' . $billboard_main_img . '">';
            break;
        case Model_Seo::ID_ITEM_ARTICLE:
        case Model_Seo::ID_ITEM_LITERATURE:
        case Model_Seo::ID_ITEM_NEWS:
            // проверка isset нужна, так как переменная $seo_data['id'] может быть равна ID_ITEM_ARTICLE, но материал скрыт, пользователю выводится сообщение об этом. а переменная $photo не установлена.
            if (isset($photo)) {
                echo '<meta property="og:image" content="' . $photo . '">';
            }
            break;
    }
    ?>

  <!-- styles -->
  <link rel="stylesheet" href="/assets/libs/jquery.autocomplete.css" type="text/css">
  <link rel="stylesheet" href="/assets/libs/bootstrap-tagsinput/dist/bootstrap-tagsinput.css" type="text/css">
  <link rel="stylesheet" href="/assets/libs/slick-1.6.0/slick/slick.css" type="text/css">
  <link rel="stylesheet" href="/assets/libs/fontello/css/fontello.css" type="text/css">
  <link rel="stylesheet" href="/assets/libs/jqcloud/dist/jqcloud.css">
  <link rel="stylesheet" href="/assets/libs/jquery-ui/css/jquery-ui-custom.min.css" type="text/css">
  <link rel="stylesheet" href="/assets/libs/photo_sizepack_uploader/css/style.css" type="text/css">
  <link rel="stylesheet" href="/assets/css/main.css" type="text/css">
  <!-- scripts -->
  <script src="/assets/libs/jquery-3.2.1.min.js"></script>
  <script src="/assets/libs/jquery-ui/jquery-ui.min.js"></script>
  <script src="/assets/libs/jquery.ui.datepicker-ru.js"></script>
  <script src="/assets/libs/touch-punch/touch-punch.min.js"></script>
  <script src='https://www.google.com/recaptcha/api.js?hl=ru&onload=grecaptcha_init&render=explicit'></script>
  <script src="/assets/libs/grecaptcha/grecaptcha.js"></script>
  <script src="/assets/libs/adaptive_layout_script/adaptive_layout_script.js"></script>
  <script src="/assets/libs/jquery-nice-select-1.1.0/js/jquery.nice-select.js"></script>
  <script src="/assets/libs/slick-1.6.0/slick/slick.min.js"></script>
  <script src="/assets/libs/bootstrap-tagsinput/dist/bootstrap-tagsinput.js"></script>
  <script src="/assets/libs/jqcloud/src/jqcloud.js"></script>
  <script src="/assets/libs/ckeditor/ckeditor.js"></script>
  <script src="/assets/libs/photo_sizepack_uploader/photo_sizepack_uploader.js"></script>
  <script src="/assets/libs/custom_ajax/custom_ajax.js"></script>
  <script src="/assets/libs/custom_uploader/custom_uploader.js"></script>
  <script src="/assets/libs/ajax_select/ajax_select.js"></script>
  <script src="/assets/libs/ajax_checkbox/ajax_checkbox.js"></script>
  <script src="/assets/libs/euv_custom_popup/js/euv_custom_popup.js"></script>
  <script src="/assets/js/functions.js"></script>
  <script src="/assets/js/pages.js"></script>
  <script src="/assets/libs/comments/comments.js"></script>
  <script src="https://vk.com/js/api/share.js?93"></script>
</head>

<body>

<?php // Код для кнопки "Поделиться" facebook ?>
<div id="fb-root"></div>
<script>(function (d, s, id) {
        var js,
            fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) {
            return;
        }
        js = d.createElement(s);
        js.id = id;
        js.src = 'https://connect.facebook.net/ru_RU/sdk.js#xfbml=1&version=v3.2';
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));</script>
<?php // ----------------------------------- ?>

<div class="wrapper custom-elems">
    <?php
    if ($user->loaded() && !$user->is_approved()) {
        echo '<div class="top-message">
						<p>Внимание! У вас не подтвержден Email, из-за чего неполноценная учетная запись. Пройдите по ссылке, которую мы отправили вам, чтобы подтвердить Email.</p>
						<p>Потеряли письмо? Мы можем <a data-url="/users/request_approve_mail" class="send-approve-mail-btn black custom-elems__link custom-elems__link_type_underline-solid custom-elems__link_active-color_white">отправить еще раз!</a></p>
					</div>';
    }
    ?>

  <div class="header">
    <div class="container clearfix">

      <div class="header__logo">
        <a class="header__mobile-menu-btn icon-menu screen-mobile"></a>
        <a href="/" class="header__logo-img-wrap">
          <img class="header__logo-img screen-pc" src="/assets/images/logo-pc.png" alt>
          <img class="header__logo-img screen-mobile" src="/assets/images/logo-mobile.png" alt>
        </a>
        <div class="header__logo-text"><?= $GLOBALS['SITE_INFO']['description'] ?></div>
      </div>
      <div class="header__cabinet clearfix">
          <?php
          if ($user->loaded()) {
              $href_1 = '/cabinet';
              $href_2 = '/logout';
              ?>
            <div class="header__cabinet-btn-wrap">
              <a href="<?= $href_1 ?>" class="black icon-user header__cabinet-mobile-btn"></a>
              <div class="header__cabinet-pc-btn">
                <span class="icon-user"></span>
                <a href="<?= $href_1 ?>" class="header__cabinet-text header__cabinet-link custom-elems__link custom-elems__link_type_underline-solid">Личный кабинет</a>
              </div>
            </div>
            <div class="header__cabinet-btn-wrap">
              <a href="<?= $href_2 ?>" class="black header__cabinet-mobile-btn icon-logout-1"></a>
              <div class="header__cabinet-pc-btn">
                <span class="header__cabinet-text"><?= $user->username ?> (</span>
                <a style="margin-right: 6px;" href="<?= $href_2 ?>" class="header__cabinet-text header__cabinet-link custom-elems__link custom-elems__link_type_underline-solid">выйти</a>
                <span class="icon-logout-1"></span>
                <span class="header__cabinet-text">)</span>
              </div>
            </div>
          <?php } else {
              $href = '/registration';
              ?>
            <div class="header__cabinet-btn-wrap">
              <a class="js-ajax-login black header__cabinet-mobile-btn icon-login-1"></a>
              <div class="header__cabinet-pc-btn">
                <span class="icon-login-1"></span>
                <a class="js-ajax-login header__cabinet-text header__cabinet-link custom-elems__link custom-elems__link_type_underline-solid">Войти</a>
              </div>
            </div>
            <div class="header__cabinet-btn-wrap">
              <a href="<?= $href ?>" class="black header__cabinet-mobile-btn icon-user-add"></a>
              <div class="header__cabinet-pc-btn">
                <span class="icon-user-add"></span>
                <a href="<?= $href ?>" class="header__cabinet-text header__cabinet-link custom-elems__link custom-elems__link_type_underline-solid">Зарегистрироваться</a>
              </div>
            </div>
          <?php } ?>
      </div>

      <form class="header__main-search main-search" method="GET" action="/search">
        <input placeholder="Поиск" class="main-search__input" name="text">
      </form>

      <div class="nice-line nice-line_device_mobile nice-line_pos_bottom"></div>

    </div>
  </div>

  <div class="navigation">
    <div class="container">
      <div class="navigation__items-wrap navigation__items-wrap_amount-btns_3 clearfix">
          <?php
          $nav_items_1 = ORM::factory('Catalog')->where('parent_id', '=', 0)->find_all()->as_array();
          foreach ($nav_items_1 as $nav_item) {
              echo '<a href="' . $nav_item->get_url() . '" class="navigation__item">' . $nav_item->title . '</a>';
          } ?>
      </div>
        <?php
        if ($user->is_admin()) { ?>
          <div class="navigation__items-wrap navigation__items-wrap_amount-btns_5 navigation__items-wrap_with-border-top clearfix">
              <?php
              $nav_items_2 = ORM::factory('Seo')->where('parent_id', '=', Model_Seo::ID_ADMIN_PANEL)->find_all()->as_array();
              foreach ($nav_items_2 as $nav_item) {
                  echo '<a href="' . $nav_item->get_url() . '" class="navigation__item">' . $nav_item->title_menu . '</a>';
              }
              ?>
          </div>
        <?php } ?>
    </div>
  </div>

  <div class="middle-panel">

      <?= Navigation::render_mobile_menu_source() ?>

    <div class="mobile-menu mobile-menu_is-hidden">
      <a class="mobile-menu__close-btn icon-cancel"></a>
      <div class="mobile-menu__content">
        <div class="mobile-menu__items"></div>
        <form class="mobile-menu__main-search main-search" method="GET" action="/search">
          <input placeholder="Поиск" class="main-search__input" name="text">
        </form>
      </div>
    </div>

      <?php
      if (!$fullpage) {
          echo '<div class="container js-page-content">';
      }

      include(Controller::get_include($template, $template_extra));

      if (!$fullpage) {
          echo '</div>';
      }
      ?>
  </div>

  <div class="footer">
    <div class="container">
      <div class="nice-line nice-line_device_mobile nice-line_pos_top"></div>
      <div class="footer__links clearfix">
        <div class="footer__links-column">
          <div class="footer__links-column-header">Разделы</div>
          <div class="footer__links-body">
              <?php
              foreach ($nav_items_1 as $nav_item) {
                  echo '<a href="' . $nav_item->get_url() . '" class="footer__links-item">' . $nav_item->title . '</a>';
              }
              ?>
          </div>
        </div>
          <?php if (CurrentUser::get_user()->is_admin()) { ?>
            <div class="footer__links-column">
              <div class="footer__links-column-header">Администратор</div>
              <div class="footer__links-body">
                  <?php
                  foreach ($nav_items_2 as $nav_item) {
                      echo '<a href="' . $nav_item->get_url() . '" class="footer__links-item">' . $nav_item->title . '</a>';
                  }
                  ?>
              </div>
            </div>
          <?php } ?>
        <div class="footer__links-column">
          <div class="footer__links-column-header"><?= $GLOBALS['SITE_INFO']['title'] ?></div>
          <div class="footer__links-body">
            <a href="/<?= $GLOBALS['aliases']['about'] ?>" class="footer__links-item">О нас</a>
            <a href="/<?= $GLOBALS['aliases']['partnership'] ?>" class="footer__links-item">Сотрудничество</a>
            <a href="/<?= $GLOBALS['aliases']['agreement'] ?>" class="footer__links-item">Пользовательское соглашение</a>
              <?php
              //<a href="#" class="footer__links-item">Поддержать проект</a>
              ?>
          </div>
        </div>

        <div class="footer__links-column footer__rightside">
          <div class="footer__links-column-header">Будьте с нами</div>
          <div class="footer__links-body">
            <a rel="nofollow" target="_blank" href="https://vk.com/"
              class="footer__links-item"><span class="icon-vk" style="padding-right: 8px"></span>Вконтакте</a>
            <a rel="nofollow" target="_blank" href="https://www.facebook.com"
              class="footer__links-item"><span class="icon-facebook-rect" style="padding-right: 8px"></span>Facebook</a>
          </div>
        </div>
      </div>

      <p class="footer__message">
        <span>Нашли баг? Опечатку? У вас есть предложение?</span>
        <a class="black custom-elems__link custom-elems__link_type_underline-solid" href="/<?= $GLOBALS['aliases']['contact_us'] ?>">Сообщите нам!</a>
      </p>
      <div class="footer__copyright">
        <div class="nice-line nice-line_pos_top"></div>
          <?php echo Helper::get_site_info_date_range($GLOBALS['SITE_INFO']['year']) . ' ' . $GLOBALS['SITE_INFO']['title'] . ' - ' . $GLOBALS['SITE_INFO']['description'] . ' ©' ?>
      </div>
    </div>
  </div>

  <div class="custom-popup-window">
    <div class="custom-popup-window__inner">
      <div class="custom-popup-window__message">
      </div>
      <div class="custom-popup-window__btns-wrap">
      </div>
    </div>
    <a class="custom-popup-window__close icon-cancel"></a>
  </div>

</div>

</body>
</html>