/*
шаблон страницы
$(function () {
		if ($('...-page').length > 0) {
		}
});
*/

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// Все страницы
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// ------------------------------------------
// Работа с версткой footer'а
// ------------------------------------------
$(window).resize(function () {
    var rightside_column = $('.footer__rightside');
    var other_column = $('.footer__links-column');

    switch (true) {
        case (rightside_column.position().top != other_column.position().top && rightside_column.hasClass('right')):
            rightside_column.removeClass('right');
            break;
        case (rightside_column.position().top == other_column.position().top && !rightside_column.hasClass('right')):
            rightside_column.addClass('right');
            break;
    }
});

// Меню
$(function () {
    // ======================================================
    // Основа
    // ======================================================
    // -------------------------------
    // Переменные для работы
    // -------------------------------
    var $menu_source = $('.mobile-menu-source');
    var page_catalog_id = $menu_source.attr('data-catalog_id_page');

    var $menu = $('.mobile-menu');
    var menu_saved_left = parseInt($menu.css('left'));

    var $menu_source_container = $menu_source.find('.mobile-menu-source__container[data-catalog_id=' + page_catalog_id + ']');
    var $menu_inner = $('.mobile-menu__items');

    // -------------------------------
    // Добавление кнопок
    // -------------------------------
    set_btns_to_menu($menu_source_container);

    // ======================================================
    // Обработчики
    // ======================================================
    // -------------------------------
    // Закрытие меню по щелчку кнопки
    // -------------------------------
    $('.header__mobile-menu-btn, .mobile-menu__close-btn').on('click', function () {
        if (media >= media_md) {
            return false;
        }

        var to_left;

        var speed = parseInt($menu.css('left')) * -1;
        if (speed > 0 && speed < menu_saved_left * -1) {
            speed = (menu_saved_left * -1) - speed;
        } else {
            speed = menu_saved_left * -1;
        }

        if ($menu.hasClass('mobile-menu_is-hidden')) {
            to_left = 0;
        } else {
            to_left = menu_saved_left;
        }

        $menu.toggleClass('mobile-menu_is-hidden');

        if ($menu.is(':animated')) {
            $menu.stop();
        }

        $menu.animate({
            left: to_left,
        }, speed, function () {
            if ($menu.hasClass('mobile-menu_is-hidden')) {
                reset_items();
            }
        });
    });

    // -------------------------------
    // Закрытие меню при изменении экрана
    // -------------------------------
    $(window).on('resize', function () {
        if (media >= media_md) {
            if (!$menu.is('.mobile-menu_is-hidden')) {
                close_mobile_menu();
            }
        }
    });

    // ======================================================
    // Функции
    // ======================================================
    // -------------------------------
    // Добавление пункта меню
    // -------------------------------
    function add_menu_item($item_source) {
        var $item = $('<a class="mobile-menu__item">' + $item_source.children('.mobile-menu-source__item-title').text() + '</a>').appendTo($menu_inner);

        if ($item_source.hasClass('mobile-menu-source__item_with-children')) {
            var catalog_id = $item_source.find('.mobile-menu-source__container').attr('data-catalog_id');
            $item.addClass('mobile-menu__item_type_forward');
            $item.attr('data-catalog_id', catalog_id);
            $item.on('click', function (e) {
                var $container = $menu_source.find('.mobile-menu-source__container[data-catalog_id=' + catalog_id + ']');
                set_btns_to_menu($container);
            });
        } else {
            $item.attr('href', $item_source.attr('data-href'));
        }
    }

    // -------------------------------
    // Добавление кнопок в меню
    // -------------------------------
    function set_btns_to_menu($container) {
        var catalog_id = $container.attr('data-catalog_id');
        var $children = $container.children('.mobile-menu-source__item');

        $menu_inner.children().each(function () {
            $(this).remove();
        });

        if (catalog_id != 0) {
            var href = $container.closest('.mobile-menu-source__item').attr('data-href');
            // Кнопка для поднятия вверх по menu_source
            var $back_btn = $('<a class="mobile-menu__item mobile-menu__item_type_back">' + $container.attr('data-catalog_title') + '</a>').appendTo($menu_inner);
            $back_btn.on('click', function () {
                var $parent_container = $container.closest('.mobile-menu-source__item').closest('.mobile-menu-source__container');
                set_btns_to_menu($parent_container);
            });
            // кнопка "Смотреть все"
            $('<a href="' + href + '" class="mobile-menu__item mobile-menu__item_type_see-all">Смотреть все</a>').appendTo($menu_inner);
        }

        $children.each(function (i, e) {
            add_menu_item($(this));
        });
    }

    // -------------------------------
    // Закрытие меню
    // -------------------------------
    function close_mobile_menu() {
        $menu.addClass('mobile-menu_is-hidden');
        $menu.css('left', menu_saved_left);
        reset_items();
    }

    // -------------------------------
    // Восстановление первоначального вида меню
    // -------------------------------
    function reset_items() {
        set_btns_to_menu($menu_source_container);
    }
});

// Без категории
$(function () {
    // -------------------------------
    // Инициализация euv_custom_popup
    // -------------------------------
    $euv_custom_popup = $('.custom-popup-window');
    $euv_custom_popup.euv_custom_popup({
        reset_on_close: 'html'
    });

    // -------------------------------
    // Без категории
    // -------------------------------
    $('.send-approve-mail-btn').on('click', function (e) {
        e.preventDefault();
        var $this = $(this);
        $this.custom_ajax({
            url: $this.attr('data-url'),
            sending_text: 'ожидайте...'
        });
    });

    $('.nice-select').niceSelect();

    var $datepicker = $('.js-datepicker');
    $datepicker.each(function (i, e) {
        datepicker_init($('.js-datepicker'));
    });

    // -------------------------------
    // Попап для входа на сайт
    // -------------------------------
    var page_login_context = 0;
    $('.js-ajax-login').on('click', function () {
        if (page_loading) {
            return;
        }
        page_login_context++;
        page_loading = true;
        $.ajax({
            type: "GET",
            url: '/ajax/login',
            dataType: "json",
            data: {
                context: page_login_context
            },
            success: function (data) {
                if (data.status) {
                    custom_popup_open(data.message);
                } else {
                    location.reload();
                }
                page_loading = false
            }
        });
        return false;
    });
});
$(document).on('click', '.js-submit', function (e) {
    e.preventDefault();
    var $closest_form = $(this).closest('form');
    $closest_form.trigger('submit');
    $closest_form.submit();
});

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// page-index
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$(function () {
    if ($('.page-index').length) {
        // -------------------------------
        // Слайдер
        // -------------------------------
        var $slider = $('.billboard__slider').slick({
            dots: true,
            customPaging: function () {
                return '<a class="custom-dot"></a>';
            },
            prevArrow: false,
            nextArrow: false,
            autoplay: true,
            autoplaySpeed: 4000
        });

        // -------------------------------
        // jQCloud
        // -------------------------------
        // Облако тегов не будет выводиться, если не указана высота
        var $main_slide_body = $('.billboard__slide_is-main .billboard__slide-body'),
            $tags_cloud = $('.billboard__tags-cloud'),
            $tags_cloud_body = $tags_cloud.closest('.billboard__slide-body');

        set_tags_cloud_body_dimensions($tags_cloud_body, $main_slide_body, $slider);
        $tags_cloud.jQCloud(JSON.parse($tags_cloud.attr('data-tags')), {
            fontSize: {
                from: 0.08,
                to: 0.04
            },
            autoResize: true,
            encodeURI: false
        });

        $(window).resize(function () {
            set_tags_cloud_body_dimensions($tags_cloud_body, $main_slide_body, $slider);
        });

        function set_tags_cloud_body_dimensions($tags_cloud_body, $main_slide_body, $slider) {
            // Размеры слайдов обновляются не сразу. И если не обновить их вручную, то они будут старые. А нам это не нужно.
            $slider[0].slick.refresh()

            $tags_cloud_body.css('height', $main_slide_body.height());
            $tags_cloud_body.css('width', $main_slide_body.width());
        }
    }
});


//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// page-cabinet
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$(function () {
    if ($('.page-cabinet').length) {
        // ------------------------------------------------
        // Функции
        // ------------------------------------------------
        function get_photo_default_path_by_sex(sex, $server_values) {
            if (sex == $server_values.attr('data-sex-woman')) {
                photo_default = $server_values.attr('data-avatar-default-woman');
            } else {
                photo_default = $server_values.attr('data-avatar-default-man');
            }
            return photo_default;
        }

        // ------------------------------------------------
        // photo-sizepack-uploader
        // ------------------------------------------------
        var $server_values = $('.js-server-values');
        var $input_file = $('.page-cabinet__photo');

        // Получение фотки по-умолчаию
        var photo_default = get_photo_default_path_by_sex($server_values.attr('data-sex'), $server_values);

        $input_file.photo_sizepack_uploader({
            photo_current: $server_values.attr('data-avatar-current'),
            photo_default: photo_default,
            text_div: $('page-cabinet__photo-text'),
            types: $server_values.attr('data-image-types'),
            maxsize: $server_values.attr('data-maxsize'),
            width: 122,
            height: 122
        });

        $('.page-cabinet__change-sex').on('change', function () {
            var path = get_photo_default_path_by_sex($(this).val(), $server_values);
            $input_file.photo_sizepack_uploader('change_default_photo', path);
        });
        // ------------------------------------------------

        $('.mailing__item-checkbox').each(function () {
            var $this = $(this);
            $this.ajax_checkbox({
                url: '/cabinet/mailing',
                data: {
                    name: $this.attr('id')
                }
            });
        });
    }
});


// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// page-catalog
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$(function () {
    if ($('.page-catalog').length) {

        $(window).resize(function () {
            if (afterChangeMedia) {
                if (media == media_md || media == media_lg) {
                    catalog__compare_btns_height_and_add_classes();
                } else {
                    catalog__remove_classes_from_btns();
                }
            }
        });

        $('#middle').on('click', '.sorter__btn, .pagination a, .sorter__on-page a, .fpanel__btn', function (e) {
            e.preventDefault();
            var scroll;
            if ($(this).is('.pagination a')) {
                scroll = $(".sorter").offset().top;
                if (media > media_sm) {
                    scroll -= 8;
                } else {
                    scroll -= ($('.header').innerHeight() - 1);
                }
            }
            url_query($(this).attr('href'), scroll);
            return false;
        });
        update_js_functions();
    }
});

function update_catalog_functions() {
    // -------------------------------
    // Фильтры для телефонов
    // -------------------------------
    let $catalog_filters = $('.js-catalog-filters');
    // Не стал писать clone(true, true), т.к. datepicker добавляет id, а элемент с определенным id может быть только один на странице.
    let $catalog_filters_mobile = $catalog_filters.clone();
    $catalog_filters_mobile.appendTo('#middle');
    $catalog_filters_mobile.euv_custom_popup();
    $('.choosing-panel__btn_type_filter').on('click', function () {
        $catalog_filters_mobile.euv_custom_popup('open');
    });
    // -------------------------------

    $('.page-catalog__nice-select').niceSelect();
    var nice_select__selector = '.on-page';
    nice_select__init(nice_select__selector);
    nice_select__option_to_a(nice_select__selector);
    input_auto_width_by_selector__init('.page-catalog__input-auto-width');

    var server_values = $('.js-server-values');
    $catalog_filters.add($catalog_filters_mobile).each(function () {
        catalog__filter_date__init(server_values, $(this));
        catalog__filter_pages__init(server_values, $(this));
    });

    $('.js-filter').each(function () {
        $(this).find('.ls-btn').click(function (e) {
            e.preventDefault();
            url_query($(this).attr('href'));
        });
    });

    $('.js-tag, .js-ajax-query').on('click', function (e) {
        e.preventDefault();
        url_query($(this).attr('href'));
    });
}

function update_admin_catalog_functions() {
    function action_delete($this, $item) {
        custom_popup_open('Вы действительно хотите удалить эту запись?', {
            btn_1: BTN_YES_TEXT, btn_2: BTN_NO_TEXT
        }, function ($popup, $btn_1, $btn_2) {
            $btn_1.on('click', function () {
                $popup.euv_custom_popup('close');
                $.ajax({
                    type: "POST",
                    url: $item.attr('data-url') + '/delete_item',
                    data: {
                        'id': $item.find('.js-id').val()
                    },
                    dataType: 'JSON',
                    success: function (res) {
                        if (res.status) {
                            $item.remove();
                        } else {
                            custom_popup_open(res.message, {btn_1: BTN_OK_TEXT}, function ($popup, $btn_1) {
                                $btn_1.on('click', function () {
                                    $popup.euv_custom_popup('close');
                                });
                            });
                        }
                    }
                });
            });
            $btn_2.on('click', function () {
                $popup.euv_custom_popup('close');
            });
        });
    }

    function action_change_title($this, $item) {
        $this.custom_ajax({
            url: $this.attr('data-url'),
            data: {'id': $item.find('.js-id').val(), 'title': $item.find('.item-admin__title').val()},
            success: function (data) {
                $item.find('.item-admin__title').val(data.message);
            }
        });
    }

    function action_init($item) {
        $item.find('.item-admin__delete').on('click', function () {
            action_delete($(this), $item);
        });
        $item.find('.item-admin__change-title').on('click', function () {
            action_change_title($(this), $item);
        });
        $item.find('.item-admin__change-status').ajax_select({
            url: $item.attr('data-url') + '/change_status',
            id: $item.find('.js-id').val()
        });
    }

    function action_create($this, $item) {
        var $item_wrap = $this.closest('.item-admin-wrap');
        $this.custom_ajax({
            url: $item.attr('data-url') + '/create_item',
            data: {
                'title': $item.find('.item-admin__title').val(),
                'status': $item.find('.item-admin__change-status').val()
            },
            change_btn_on_sending: false,
            success: function (data) {
                $item_wrap.empty();
                var $new_item = $(data.message).appendTo($item_wrap);
                $new_item.unwrap();
                $new_item.find('.page-catalog__nice-select').niceSelect();
                action_init($new_item);
            }
        });
    }

    $('.page-catalog__btn-add').on('click', function () {
        var $item_template = $('.item-admin-template');
        var $new_item = $item_template.clone().insertAfter($item_template);
        $new_item.removeClass('item-admin-template');
        $new_item.removeAttr('style');
        var $item = $new_item.find('.js-item');
        $item.find('.item-admin__create').on('click', function () {
            action_create($(this), $item);
        })
    });

    $('.js-item').not('.js-item_is-template').each(function () {
        action_init($(this));
    });
}

//--------------------------------
// Функции для сравнения высоты групп (ls-panel__left-group и ls-panel__right-group) у блоков ls-panel на средних экранах (это когда группы равны по ширине). И добавления соответствующих классов (для соответствующих стилей).
//--------------------------------
function catalog__compare_btns_height_and_add_classes() {
    $('.ls-panel__body').each(function () {
        //если есть левая группа, то будет и правая
        if ($(this).find('.ls-panel__left-group').length) {
            var left_group = $(this).find('.ls-panel__left-group'),
                right_group = $(this).find('.ls-panel__right-group');
            if (left_group.height() == right_group.height()) {
                $(this).addClass('ls-panel__body_groups-eq-height');
            } else {
                $(this).addClass('ls-panel__body_groups-diff-height');
            }
        }
    });
}
function catalog__remove_classes_from_btns() {
    $('.ls-panel__body_groups-eq-height').removeClass('ls-panel__body_groups-eq-height');
    $('.ls-panel__body_groups-diff-height').removeClass('ls-panel__body_groups-diff-height');
}

//--------------------------------
// Функции фильтра по страницам
//--------------------------------
function catalog__filter_pages__init(server_values, $filters_wrap) {
    var min_pages = parseInt(server_values.attr('data-min-pages'));
    var max_pages = parseInt(server_values.attr('data-max-pages'));
    var current_min_pages = server_values.attr('data-pages-from');
    var current_max_pages = server_values.attr('data-pages-to');

    // Округляем max_pages в большую сторону, так как по умолчанию slider делает это в меньшую
    var step = 1;
    max_pages = Math.ceil(max_pages / step) * step;
    current_max_pages = Math.ceil(current_max_pages / step) * step;

    var horizontal_slider = $filters_wrap.find('.filter-pages__slider'),
        input_from = $filters_wrap.find('.filter-pages__input-from'),
        input_to = $filters_wrap.find('.filter-pages__input-to');

    horizontal_slider.slider({
        range: true,
        min: min_pages,
        max: max_pages,
        step: step,
        values: [current_min_pages, current_max_pages],
        slide: function (event, ui) {
            input_from.val(ui.values[0]).trigger('input');
            input_to.val(ui.values[1]).trigger('input');
        },
        change: function (event, ui) {
            if (ui.values[1] == 0) {
                ui.values[1] = max_pages;
            }
            // TODO: по-хорошему здесь нужно сделать проверку - если ползунки не двигались (просто был клик), то ничего не делать. И если значение слайдера равно по умолчанию, то НЕ ПИСАТЬ лишние параметры в url (в качестве GET параметров), а просто удалять их.
            var url = $(this).closest('.js-filter-values').attr('data-href');
            url = url.replace('pages_from=1', 'pages_from=' + ui.values[0]);
            url = url.replace('pages_to=1', 'pages_to=' + ui.values[1]);
            url_query(url);
        }
    });
    input_from.on('change', function () {
        horizontal_slider.slider('values', 0, parseInt($(this).val()));
    });
    input_to.on('change', function () {
        horizontal_slider.slider('values', 1, parseInt($(this).val()));
    });
    // Вызов события input - это чтобы сработала функция input_auto_width_by_selector__init
    input_from.val(horizontal_slider.slider("values", 0)).trigger('input');
    input_to.val(horizontal_slider.slider("values", 1)).trigger('input');

    range_inputs_settings(input_from, input_to, min_pages, max_pages);
}

//--------------------------------
// Функции фильтра по дате
//--------------------------------
function catalog__filter_date__init(server_values, $filters_wrap) {
    var min_date = parseInt(server_values.attr('data-min-date')),
        max_date = parseInt(server_values.attr('data-max-date')),
        current_min_date = parseInt(server_values.attr('data-date-from')),
        current_max_date = parseInt(server_values.attr('data-date-to'));

    var $input_from = $filters_wrap.find('.filter-date__input-from'),
        $input_to = $filters_wrap.find('.filter-date__input-to');

    // Проверка на if нужна, так как, возможно, открыт каталог литературы. А там нет фильтрации по дате.
    if ($input_from.length && $input_to.length) {
        $input_from.val(dateFromUnixToString(current_min_date));
        $input_to.val(dateFromUnixToString(current_max_date));

        datepicker_init($input_from, dateFromUnixToString(min_date), dateFromUnixToString(max_date));
        datepicker_init($input_to, dateFromUnixToString(min_date), dateFromUnixToString(max_date));

        $input_from.add($input_to).on('change', function () {
            var from = dateFromStringToUnix($input_from.val());
            var to = dateFromStringToUnix($input_to.val());
            if (to < from) {
                to = from;
            }

            to = new Date(to * 1000);
            to = to.setDate(to.getDate() + 1);
            to = (to - 1000) / 1000;

            var url = $(this).closest('.js-filter-values').attr('data-href');
            url = url.replace('date_from=1', 'date_from=' + from);
            url = url.replace('date_to=1', 'date_to=' + to);
            url_query(url);
        });
    }
}


// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// page-item-filling
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$(function () {
    if ($('.page-item-filling').length) {
        // ----------------------------
        // photo sizepack uploader (для фоток)
        // ----------------------------
        var $server_values = $('.js-server-values');
        var $input_photo = $('.page-item-filling__photo');
        $input_photo.photo_sizepack_uploader({
            photo_default: $server_values.attr('data-photo-default'),
            photo_current: $server_values.attr('data-photo-current'),
            text_div: $('.page-item-filling__photo-text'),
            types: $server_values.attr('data-image-types'),
            maxsize: $server_values.attr('data-maxsize'),
        });

        // ======================================
        // Действия для литературы
        // ======================================
        if ($('.page-item-filling_material-type_literature').length) {
            // ----------------------------
            // uploader (для книжек)
            // ----------------------------
            var $uploader = $('.uploader');
            var exts = ($uploader.attr('data-exts')).split(';');
            var max_size = $uploader.attr('data-max-size');
            var default_values = ($uploader.attr('data-default-values')).split(';');

            $uploader.custom_uploader({
                exts: exts,
                max_size: max_size,
                only_one_ext: true,
                default_values: default_values
            });

            // ----------------------------
            // Инициализация авторов
            // ----------------------------
            var default_values = $('.page-item-filling__authors-wrap').attr('data-values');
            var authors = default_values.split(',');
            $(authors).each(function (i, e) {
                if (e.length) {
                    add_new_author(e);
                }
            });

            // ----------------------------
            // Добавление нового автора
            // ----------------------------
            $('.page-item-filling__author-btn').on('click', function () {
                add_new_author();
            });

            function add_new_author(value) {
                var $new_author = $('.page-item-filling__author-template').clone();
                $new_author.appendTo('.page-item-filling__authors');
                $new_author.removeClass('hidden');
                $new_author.removeClass('page-item-filling__author-template');
                add_author_autocomplete($new_author.find('.custom-elems__input'));
                if (typeof value !== 'undefined') {
                    $new_author.find('.page-item-filling__author-input').val(value);
                }
            }

            $(document).on('click', '.page-item-filling__author-delete-btn', function () {
                $(this).closest('.page-item-filling__author').remove();
            });

            function add_author_autocomplete(input) {
                input.autocomplete({
                    source: function (request, response) {
                        $.ajax({
                            url: input.attr('data-url'),
                            dataType: "json",
                            data:
                                {
                                    max_rows: 6,
                                    starts_with: request.term
                                },
                            success: function (data) {
                                if (data.status) {
                                    response($.map(data.message.data, function (item) {
                                        return {
                                            label: item.name,
                                            value: item.name
                                        };
                                    }));
                                }
                            },
                        });
                    },
                    minLength: 1,
                    delay: 1,
                    select: function (event, ui) {
                        setTimeout(function () {
                            $(event.target).trigger('change');
                        }, 0);
                    }
                });
            }
        }
        // ======================================
    }
});


// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// page-item
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$(function () {
    if ($('.page-item').length) {

        $('.page-item__delete').on('click', function () {
            var $this = $(this);
            var url = $this.closest('.page-item').attr('data-material-type-url');
            var data = {
                'item_id': $this.attr('data-id'),
                'url': url // url нужен для возврата на страницу
            };
            custom_popup_open('Вы действительно хотите удалить материал?', {
                btn_1: BTN_YES_TEXT, btn_2: BTN_NO_TEXT
            }, function ($popup, $btn_1, $btn_2) {
                $btn_1.on('click', function () {
                    $popup.euv_custom_popup('close');
                    $this.custom_ajax({
                        url: url + '/delete',
                        data: data,
                        success: custom_ajax__message
                    });
                });
                $btn_2.on('click', function () {
                    $popup.euv_custom_popup('close');
                });
            });
        });

        $('.item-params__add-favorite').click(function (e) {
            e.preventDefault();
            var $this = $(this);
            var favorite = 1;
            var url = $this.closest('.page-item').attr('data-material-type-url');
            if ($this.hasClass('active')) {
                favorite = 0;
            }
            $this.custom_ajax({
                url: url + '/item_favorite',
                data: {
                    'item_id': $this.attr('data-id'),
                    'favorite': favorite
                },
                finish: function (data) {
                    if (data.status) {
                        if (favorite) {
                            $this.addClass('active');
                            $this.html($this.attr('data-from-favorite'));
                        } else {
                            $this.removeClass('active');
                            $this.html($this.attr('data-to-favorite'));
                        }
                    }
                }
            });
        });

        function add_handler_to_rating_btn() {
            $('.item-params__rating-btn').click(function (e) {
                e.preventDefault();
                var $this = $(this);
                if ($this.hasClass('item-params__rating-btn_is-sending')) {
                    return;
                }
                $this.addClass('item-params__rating-btn_is-sending');

                var rating_btns = $this.closest('.js-rating');
                var rate;
                var url = $this.closest('.page-item').attr('data-material-type-url') + '/rating_change';
                var $item_params = $this.closest('.item-params');

                if ($this.hasClass('js-rating__up') || $this.hasClass('js-rating__down')) {
                    if ($this.hasClass('active')) {
                        rate = 0;
                    } else {
                        if ($this.hasClass('js-rating__up')) {
                            rate = 1;
                        } else if ($this.hasClass('js-rating__down')) {
                            rate = -1;
                        }
                    }
                }

                $this.custom_ajax({
                    url: url,
                    data: {
                        'rate': rate,
                        'item_id': rating_btns.attr('data-id')
                    },
                    change_btn_on_sending: false,
                    success: function (data) {
                        $this.closest('.item-params__rating').remove();
                        $(data.message).appendTo($item_params);
                        add_handler_to_rating_btn();
                    },
                    unsuccess: function () {
                        $this.removeClass('item-params__rating-btn_is-sending');
                    }
                });
            });
        }

        add_handler_to_rating_btn();
    }
});


// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// page-index
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$(function () {
    if ($('.page-index').length) {
        $(function () {
            var e = $('.js-view-more'),
                btn = e.find('.js-view-more__btn'),
                tw = e.find('.js-view-more__tw'),
                overlay = e.find('.js-view-more__overlay');

            e.viewmore(e.attr('data-height'), btn, tw, overlay);
        });
    }
});