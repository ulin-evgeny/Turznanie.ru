/*
Плагин для отправки данных через AJAX и вывода сообщений в euv_custom_popup.
Нужен, чтобы сократить количество кода.
Инициализируется по кнопке отправки. Если при инициализации указваются данные, то плагин будет отправлять их. В противном случае найдет ближайшую форму и отправит ее. Если не найдет форму, то ничего не отправит и сообщит об этом.
Берет функции из файла functions.
*/

// ===========================================
// Инициализация стандартной вариации плагина
// ===========================================
$(function () {
    $(document).on('submit', '.js-custom-ajax-form', function (e) {
        e.preventDefault();

        var func;
        var $this = $(this);
        var settings = {};

        switch (true) {
            case $this.hasClass('js-custom-ajax-form_success_message'):
                func = custom_ajax__message;
                break;
            case $this.hasClass('js-custom-ajax-form_success_notification'):
                func = custom_ajax__notification;
                break;
        }

        settings = $.extend({
            success: func
        }, settings);

        $this.custom_ajax(settings);
    });

    $(document).on('click', '.js-custom-ajax-form-submit-btn', function (e) {
        // класс назван не по БЭМ (js-custom-ajax-form__submit-btn) - потому что не обязательно форма должна иметь js-custom-ajax-form - возможно, есть какие-то предваритеьные действия, а потом вызов custom_ajax вручную.
        e.preventDefault();
        $(this).closest('.js-custom-ajax-form').trigger('submit');
    });

    $(document).on('click', '.js-custom-ajax-send', function (e) {
        e.preventDefault();
        $(this).custom_ajax();
    });

    $('.js-custom-ajax-notification').css('display', 'none');
});

// -------------------------------------------
// Стандартные success-функции data-func.
// На странице, где используются эти функции, должны быть элементы с селекторами из этих функций.
// -------------------------------------------
function custom_ajax__notification(data) {
    $('.js-custom-ajax-notification').removeAttr('style');
}

function custom_ajax__message(data) {
    $('.js-page-content').html(data.message);
    $(window).scrollTop(0);
}

// ===========================================
// Код самого плагина
// ===========================================
(function ($) {

    // это те же самые константы, что и в PHP - в функции render_ajax
    const STATUS_SUCCESS = 1;
    const STATUS_UNSUCCESS = 0;
    const STATUS_REDIRECT = 2;
    const STATUS_NEED_CAPTCHA = 3;

    var noop = function () {
        return undefined
    };

    var methods = {
        init: function (settings) {
            return this.each(function () {
                    var $this = $(this);
                    if ($this.hasClass('js-custom-ajax_is-sending')) {
                        return false;
                    }

                    // Создаём настройки по-умолчанию, расширяя их с помощью параметров, которые были переданы
                    settings = $.extend({
                        url: null,
                        data: null,
                        sending_text: 'Отправка данных',
                        change_btn_on_sending: true,
                        success: noop,
                        unsuccess: noop,
                        finish: noop,
                        dont_revert: false
                    }, settings);

                    // Кнопка, текст которой будет меняться
                    var $btn;
                    var is_form = false;
                    if ($this.is('form')) {
                        $btn = $this.find('.js-custom-ajax-form-submit-btn');
                        is_form = true;
                    } else {
                        $btn = $this;
                    }
                    settings.$btn = $btn;

                    // Установка data (данных для отправки на сервер)
                    var data;
                    if (!is_form) {
                        data = new FormData();
                        $.each(settings.data, function (key, val) {
                            data.append(key, val);
                        });
                    } else {
                        data = new FormData($this[0]);
                        $.each(settings.data, function (key, val) {
                            data.append(key, val);
                        });
                    }
                    settings.data = data;

                    // Проверка - есть ли url
                    var exception_not_url = 'Ошибка! Нет url для отправки через custom_ajax.';
                    if (!settings.url && !is_form) {
                        throw exception_not_url;
                    } else if (is_form) {
                        if (!settings.url) {
                            if (!$this.attr('action')) {
                                throw exception_not_url;
                            } else {
                                settings.url = $this.attr('action');
                            }
                        }
                    }

                    $this.data('settings', settings);
                    main_sending_function($this);
                }
            );
        }
    }

    // Изменение кнопки и отправка данных (самая главня функция отправки - main)
    function main_sending_function($this) {
        var settings = $this.data('settings');
        $this.trigger('custom-ajax-sending');
        $this.addClass("js-custom-ajax_is-sending");
        if ($this.data('settings').change_btn_on_sending) {
            var $btn = $this.data('settings').$btn;
            switch (true) {
                case $btn.is('a'):
                    $btn.attr("data-custom-ajax-btn-value", $btn.html());
                    $btn.html($this.data('settings').sending_text);
                    break;
                case $btn.is('input[type="submit"]'):
                    $btn.attr("data-custom-ajax-btn-value", $btn.val());
                    $btn.val($this.data('settings').sending_text);
                    break;
            }
        }

        send_ajax($this).done(function (data) {
            after_send_ajax(data, $this);
        });
    }

    function after_send_ajax(data, $this) {
        // Проверка нужна, так как в send_ajax может быть смена контента страницы - custom_ajax__message, например.
        if (!$.contains(document, $this[0]) || $this.data('settings').dont_revert) {
            return;
        }

        switch (data.status) {
            case STATUS_SUCCESS:
                $this.trigger('custom-ajax-success', data);
                $this.data('settings').success(data);
                break;
            case STATUS_UNSUCCESS:
                $this.trigger('custom-ajax-unsuccess', data);
                $this.data('settings').unsuccess(data);
                break;
        }

        // Так как в success функции может быть удаление $this, проверяем его наличие снова
        if (!$.contains(document, $this[0])) {
            return;
        }
        $this.removeClass("js-custom-ajax_is-sending");
        if ($this.data('settings').change_btn_on_sending) {
            var $btn = $this.data('settings').$btn;
            switch (true) {
                case $btn.is('a'):
                    $btn.html($btn.attr("data-custom-ajax-btn-value"));
                    break;
                case $btn.is('input[type="submit"]'):
                    $btn.val($btn.attr("data-custom-ajax-btn-value"));
                    break;
            }
            $btn.removeAttr("data-custom-ajax-btn-value");
        }
        $this.trigger('custom-ajax-finish', data);
        $this.data('settings').finish(data);
    }

    // -----------------------------------------------
    // Логика вызова методов
    //------------------------------------------------
    $.fn.custom_ajax = function (method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Метод с именем ' + method + ' не существует для jQuery.custom_ajax');
        }
    }

    // -----------------------------------------------
    // Разные функции, которые использует плагин
    // -----------------------------------------------
    function send_ajax($this) {
        return $.ajax({
            contentType: false,	// если data создана через new FormData, то без этого параметра на сервер данные придут в виде строк, а не массивов по input'ам.
            processData: false,	// если data создана через new FormData, то без этого параметра будет ошибка: Uncaught TypeError: Illegal invocation.
            type: "POST",
            url: $this.data('settings').url,
            data: $this.data('settings').data,
            dataType: "json",
            success: function (data) {
                switch (data.status) {
                    case STATUS_UNSUCCESS:
                        // get_deepest нужен, так как ORM_Validation_Exception заворачивает свое сообщение в еще одно свойство message (а то и в несколько - у user _external, например).
                        custom_popup_open(get_deepest(data.message), {btn_1: BTN_OK_TEXT}, function ($popup, $btn_1) {
                            $btn_1.on('click', function () {
                                $popup.euv_custom_popup('close');
                            });
                        });

                        break;
                    case STATUS_REDIRECT:
                        var settings = $this.data('settings');
                        settings.dont_revert = true;
                        $this.data('settings', settings);

                        if (data.message.data) {
                            var form_string = '<form class="hidden" action="' + data.message.url + '" method="POST">';
                            $.each(data.message.data, function (key, val) {
                                form_string += '<input type="hidden" name="' + key + '" value="' + val + '">';
                            });
                            form_string += '</form>';
                            var $form = $(form_string).appendTo('body');
                            $form.submit();
                        } else {
                            window.location = data.message.url;
                        }

                        break;
                    case STATUS_NEED_CAPTCHA:
                        $this.addClass(last_grecaptcha_target_class);
                        // Эта строчка нужна, так как если после вывода капчи (которая в попапе) нажать Enter, то форма отправится снова и появится еще один попап со своей капчей
                        document.activeElement.blur();

                        custom_popup_open(data.message, {btn_1: 'Отмена'}, function ($popup, $btn_1) {
                            var $captcha = $popup.find('.g-recaptcha');
                            render_grecaptcha($captcha);

                            // Действия при разгадывании капчи
                            $captcha.on('grecaptcha-success', function (e, val) {
                                var name = $captcha.attr('data-field-name');
                                $this.data('settings').data.append(name, val);
                                // Закрываем попап
                                $euv_custom_popup.euv_custom_popup('close');
                                // Отправляем форму
                                main_sending_function($this);
                            });

                            $btn_1.on('click', function () {
                                $popup.euv_custom_popup('close');
                            });
                        });

                        break;
                }
            },
            error: function (data) {
                $('body').html(data.responseText);
            }
        });
    }

    function get_deepest(e) {
        if (typeof(e) === 'object') {
            e = e[Object.keys(e)[0]];
            return get_deepest(e);
        } else {
            return e;
        }
    }

})(jQuery);