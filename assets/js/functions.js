//===============================================
// open_page_in_window
//===============================================
function open_page_in_window(url) {
    if (page_loading) {
        return;
    }
    page_loading = true;
    $.ajax({
        type: "GET",
        url: url,
        success: function (res) {
            custom_popup_open(res, {btn_1: BTN_OK_TEXT}, function ($popup, $btn_1) {
                $btn_1.on('click', function () {
                    $popup.euv_custom_popup('close');
                });
            });

            page_loading = false;
        },
        error: function (res) {
            $('body').html(res.responseText);
        }
    });
    return false;
}


//===============================================
// url_query и все, что его касается
//===============================================
var page_loading;
var global_last_url = window.location.href;

$(window).on('hashchange', function () {
    global_last_url = window.location.href;
});

function url_query(url, scroll, dont_change_history) {
    if (page_loading) {
        return false;
    }

    page_loading = true;
    show_load(true);

    global_last_url = window.location.href;

    if (!dont_change_history) {
        window.history.pushState({url: url, scroll: scroll}, document.title, url);
    }

    $.ajax({
        type: "GET",
        url: url,
        success: function (res) {
            $('#middle').html(res);

            update_js_functions();

            if (typeof scroll !== 'undefined') {
                $(window).scrollTop(scroll);
            }

            hide_load();
            page_loading = false;
        }
    });
}

// Кнопки браузера "Назад" и "Вперед"
$(window).on('popstate', function (event) {
    var state = event.originalEvent.state;
    var scroll;
    if (state) {
        scroll = state.scroll;
    } else {
        scroll = 0;
    }
    if (!window.location.hash && global_last_url.indexOf("#") === -1) {
        url_query(window.location.hash, scroll, true);
    }
});

function show_load(white) {
    if (white) {
        $('body').append('<div class="custom-popup-bg custom-popup-bg_color_white"></div>');
    } else {
        $('body').append('<div class="custom-popup-bg"></div>');
    }
}

function hide_load() {
    $('.custom-popup-bg').remove();
}

//===============================================
// Функционал для $euv_custom_popup
//===============================================
var $euv_custom_popup;

const BTN_OK_TEXT = 'ОК';
const BTN_NO_TEXT = 'Нет';
const BTN_YES_TEXT = 'Да';

function custom_popup_open(html, btns, before_open_function) {
    $euv_custom_popup.euv_custom_popup('open', function ($popup) {
        $popup.find('.custom-popup-window__close').on('click', function () {
            $popup.euv_custom_popup('close');
        });

        if (btns && (btns.btn_1 || btns.btn_2)) {
            $popup.find('.custom-popup-window__message').html(html);
            let $btns_wrap = $popup.find('.custom-popup-window__btns-wrap');
            let $btn_1, $btn_2;

            if (btns.btn_1) {
                $btn_1 = $('<a class="button">' + btns.btn_1 + '</a>');
                $btn_1.appendTo($btns_wrap);
            }

            if (btns.btn_2) {
                $btn_2 = $('<a class="button">' + btns.btn_2 + '</a>');
                $btn_2.appendTo($btns_wrap);
            }

            if (typeof before_open_function === 'function') {
                before_open_function($popup, $btn_1, $btn_2);
            }
        } else {
            $popup.find('.custom-popup-window__inner').html(html);

            if (typeof before_open_function === 'function') {
                before_open_function($popup);
            }
        }
    });
}


//===============================================
// Функция, которая при произведении шагов по истории (кнопки браузера "Вперед" и "Назад") берет из url GET-параметры и заносит их в переменную url_params - в виде ассоциативного массива.
//===============================================
var url_params;
(window.onpopstate = function () {
    var match,
        pl = /\+/g,  // Regex for replacing addition symbol with a space
        search = /([^&=]+)=?([^&]*)/g,
        decode = function (s) {
            return decodeURIComponent(s.replace(pl, " "));
        },
        query = window.location.search.substring(1);

    url_params = {};
    while (match = search.exec(query)) {
        url_params[decode(match[1])] = decode(match[2]);
    }
})();


//===============================================
// Рандомное число в диапазоне
//===============================================
function random_int_from_interval(min, max) {
    return Math.floor(Math.random() * (max - min + 1) + min);
}


//===============================================
// is html node
//===============================================
function is_node(o) {
    return (
        typeof Node === "object" ? o instanceof Node :
            o && typeof o === "object" && typeof o.nodeType === "number" && typeof o.nodeName === "string"
    );
}


//===============================================
// PHP's is_numeric in JavaScript
//===============================================
function is_numeric(mixedVar) {
    // discuss at: http://locutus.io/php/is_numeric/
    var whitespace = [
        ' ',
        '\n',
        '\r',
        '\t',
        '\f',
        '\x0b',
        '\xa0',
        '\u2000',
        '\u2001',
        '\u2002',
        '\u2003',
        '\u2004',
        '\u2005',
        '\u2006',
        '\u2007',
        '\u2008',
        '\u2009',
        '\u200a',
        '\u200b',
        '\u2028',
        '\u2029',
        '\u3000'
    ].join('')

    return (typeof mixedVar === 'number' ||
        (typeof mixedVar === 'string' &&
            whitespace.indexOf(mixedVar.slice(-1)) === -1)) &&
        mixedVar !== '' &&
        !isNaN(mixedVar)
}


//===============================================
// PHP's end in JavaScript
//===============================================
function end(arr) {
    // discuss at: http://locutus.io/php/end/
    var $global = (typeof window !== 'undefined' ? window : global)
    $global.$locutus = $global.$locutus || {}
    var $locutus = $global.$locutus
    $locutus.php = $locutus.php || {}
    $locutus.php.pointers = $locutus.php.pointers || []
    var pointers = $locutus.php.pointers

    var indexOf = function (value) {
        for (var i = 0, length = this.length; i < length; i++) {
            if (this[i] === value) {
                return i
            }
        }
        return -1
    }

    if (!pointers.indexOf) {
        pointers.indexOf = indexOf
    }
    if (pointers.indexOf(arr) === -1) {
        pointers.push(arr, 0)
    }
    var arrpos = pointers.indexOf(arr)
    if (Object.prototype.toString.call(arr) !== '[object Array]') {
        var ct = 0
        var val
        for (var k in arr) {
            ct++
            val = arr[k]
        }
        if (ct === 0) {
            // Empty
            return false
        }
        pointers[arrpos + 1] = ct - 1
        return val
    }
    if (arr.length === 0) {
        return false
    }
    pointers[arrpos + 1] = arr.length - 1
    return arr[pointers[arrpos + 1]]
}


//===============================================
// Аккуратное удаление и добавление элемента из строки / в строку
//===============================================
function remove_elem_from_string(string, elem, delimiter) {
    delimiter = delimiter || ' ';
    var elems = string.split(delimiter);
    $(elems).each(function (i, v) {
        if (elem == v) {
            elems.splice(i, 1)
        }
    });
    elems = elems.join(delimiter);
    return elems;
}

function add_elem_to_string(string, elem, delimiter) {
    delimiter = delimiter || ' ';
    if (string) {
        string += delimiter + elem;
    } else {
        string = elem;
    }
    return string;
}


//===============================================
// Получение расширения файла
//===============================================
function get_extension_of_file(input_file) {
    var splitted = input_file.files[0].name.split('.');
    return splitted[splitted.length - 1];
}


//===============================================
// Получить адрес без GET параметров
//===============================================
function get_url_address() {
    return location.protocol + '//' + location.host + location.pathname;
}


//===============================================
// Споилер
//===============================================
$(document).on('click', '.js-spoiler__btn', function (e) {
    e.preventDefault();
    $(this).closest('.js-spoiler').toggleClass('active');
});


//===============================================
// Календарь
//===============================================
function set_placeholder_to_val($datepicker) {
    if (!$datepicker.val().match(/\d\d\.\d\d\.\d\d\d\d/)) {
        $datepicker.val($datepicker.attr('data-placeholder'));
    }
}

function datepicker_init($elem, min_value, max_value) {
    // одна секунда - это 1000 миллисекунд. Date.now() возвращает время в миллисекундах, поэтому нужно делить на 1000.
    if (!min_value || !max_value) {
        // максимальная дата - сегодняшний день
        max_value = dateFromUnixToString(Date.now() / 1000);
        // минимальная дата - 100 лет назад от сегоднешнего дня
        min_value = dateFromUnixToString(new Date(new Date().setFullYear(new Date().getFullYear() - 100)).getTime() / 1000);
    }

    $elem.datepicker({
        firstDay: 1,
        dateFormat: "dd.mm.yy",
        changeMonth: true,
        changeYear: true,
        minDate: min_value,
        maxDate: max_value,
        onClose: function () {
            set_placeholder_to_val($elem);
        }
    });

    // Установка year_range (если это требуется - например, если min_value и max_value не были указаны и диапазон равен 100 лет - явно больше 20)
    var min_year = dateFromStringToArray(min_value)['year'];
    var max_year = dateFromStringToArray(max_value)['year'];
    var year_range = max_year - min_year;
    if (year_range > 20) { // 20 - по умолчанию
        $elem.datepicker("option", "yearRange", min_year + ":" + max_year);
    }

    set_placeholder_to_val($elem);
}

/*
function datepicker_validate_range(input_from, input_to) {
	$(input_from).add(input_to).on('change', function () {
		if (dateFromStringToUnix(input_from.val()) > dateFromStringToUnix(input_to.val())) {
			if ($(this).is(input_from)) {
				input_from.val(input_to.val());
			} else if ($(this).is(input_to)) {
				input_to.val(input_from.val());
			}
		}
	});
}

function datepicker_validate_date(value, min, max) {
	var m = value.match(/^(\d{2})\.(\d{2})\.(\d{4})$/);
	if (m) {
		var new_arr = dateFromStringToArray(value);
		var min_arr = dateFromStringToArray(min);
		var max_arr = dateFromStringToArray(max);
		var composedDate = dateFromArrayToStandart(new_arr);
		if (
				composedDate.getDate() == new_arr['day'] && (composedDate.getMonth() + 1) == new_arr['month'] && composedDate.getFullYear() == new_arr['year']
				&& composedDate <= dateFromArrayToStandart(max_arr) && composedDate >= dateFromArrayToStandart(min_arr)
		) {
			return new_arr['day'] + '.' + new_arr['month'] + '.' + new_arr['year'];
		}
	}
	return false;
}
*/


//===============================================
// Работа с датой
//===============================================
function dateFromUnixToString(unixDate) {
    var date = new Date(unixDate * 1000);
    var curr_date = ('0' + date.getDate()).slice(-2);
    var curr_month = ('0' + (date.getMonth() + 1)).slice(-2);
    var curr_year = date.getFullYear();
    return curr_date + '.' + curr_month + '.' + curr_year;
}

function dateFromStringToUnix(stringDate) {
    var splitted = stringDate.split('.');
    var day = splitted[0];
    var month = splitted[1];
    var year = splitted[2];
    var date = year + '/' + month + '/' + day + ' 00:00:00';
    return new Date(date).getTime() / 1000;
}

function dateFromStringToArray(value) {
    var splitted = value.split('.');
    var day = ('0' + splitted[0]).slice(-2);
    var month = ('0' + splitted[1]).slice(-2);
    var year = splitted[2];
    return {
        day: day,
        month: month,
        year: year
    }
}

function dateFromArrayToStandart(date_arr) {
    return new Date(date_arr['year'], date_arr['month'] - 1, date_arr['day']);
}


//===============================================
// Функция для сброса <input>
//===============================================
function resetFormElement(e) {
    $(e).wrap('<form>').closest('form').get(0).reset();
    $(e).unwrap();
}


//===============================================
// Функции для плагина JQuery - nice select
//===============================================
function nice_select__init(selector) {
    var active_value = $(selector).attr('data-value');
    var active_elem = $(selector + ' .option').filter(function () {
        return $(this).text().toLowerCase() === active_value;
    });
    $('div' + selector).find('.current').html(active_elem.text());
}

function nice_select__option_to_a(selector) {
    var data_href_array = [];
    $('select' + selector).find('option').each(function () {
        data_href_array[data_href_array.length] = $(this).attr('data-href');
    });
    $('div' + selector).find('.option').each(function (i) {
        $(this).replaceWith('<a href="' + data_href_array[i] + '" class="option">' + $(this).html() + '</a>');
    });
}


//===============================================
// Распределитель update_js_functions
//===============================================
function update_js_functions() {
    if (('.page-catalog').length > 0) {
        update_catalog_functions();
        if (('.page-catalog_type_admin').length > 0) {
            update_admin_catalog_functions();
        }
    }
}


//===============================================
// view-more - как споилер, только часть уже видна
//===============================================
$.fn.viewmore = function (h, btn, tw, overlay) {
    var $this = $(this),
        speed = 400;
    var h_default = tw.css('height');
    tw.css('height', h);
    btn.on('click', function () {
        $this.toggleClass('active');
        if ($this.hasClass('active')) {
            tw.animate({height: h_default}, speed);
            overlay.fadeOut(speed, function () {
                toggle_text(btn, 'data-text');
            });
        } else {
            tw.animate({height: h}, speed);
            overlay.fadeIn(speed, function () {
                toggle_text(btn, 'data-text');
            });
        }
    });
};


//===============================================
// Функция переключения атрибутов
//===============================================
function toggle_attributes(elem, attr_1, attr_2) {
    var attr_2_value = elem.attr(attr_2);
    elem.attr(attr_2, elem.attr(attr_1));
    elem.attr(attr_1, attr_2_value);
}


//===============================================
// Функция переключения текста
//===============================================
function toggle_text(elem, attr_1) {
    var text = elem.attr(attr_1);
    elem.attr(attr_1, elem.text());
    elem.text(text);
}


//===============================================
// Подгонка ширины конкретных input под их содержимое
//===============================================
function input_auto_width_by_selector__init(selector) {
    $(selector).each(function () {
        var input = $(this);
        input.on('input change', function () {
            $('body').append('<div style="position:absolute; visibility:hidden; white-space:nowrap;" class="js-input-auto-width-buffer">');
            var buffer = $('.js-input-auto-width-buffer');
            buffer.css({
                'font': input.css('font'),
                'letter-spacing': input.css('letter-spacing')
            });
            buffer.text(input.val());
            input.width(buffer.width());
            buffer.remove();
        });
        input.trigger('input');
    });
}
$(function () {
    input_auto_width_by_selector__init('.js-input-auto-width');
});


//===============================================
// Функция для настройки input'ов диапазона
//===============================================
function range_inputs_settings(input_from, input_to, minimum, maximum) {
    input_from.on('change', function () {
        if (isNaN(parseInt(input_from.val())) || parseInt(input_from.val()) < minimum) {
            input_from.val(minimum).trigger('input');
        }
        if (parseInt(input_from.val()) > parseInt(input_to.val())) {
            input_from.val(parseInt(input_to.val())).trigger('input');
        }
    });

    input_to.on('change', function () {
        if (isNaN(parseInt(input_to.val())) || parseInt(input_to.val()) > maximum) {
            input_to.val(maximum).trigger('input');
        }
        if (parseInt(input_from.val()) > parseInt(input_to.val())) {
            input_from.val(parseInt(input_to.val())).trigger('input');
        }
    });
}


//===============================================
// Проверка - существует ли атрибут
//===============================================
$.fn.hasAttr = function (attr) {
    var attribute = $(this).attr(attr)
    return (typeof attribute !== typeof undefined && attribute !== false)
}


//===============================================
// Плагин тегов
//===============================================
$(function () {
    var e = $('.js-color-tags');
    var tags;
    if (e.hasAttr('data-tags')) {
        tags = JSON.parse(e.attr('data-tags'));
    } else {
        tags = false;
    }
    e.colortags(e.attr('data-url'), tags);
});

$.fn.colortags = function (url, tags) {
    var colortags = this;

    // инициализация базового плагина
    colortags.tagsinput({
        maxTags: 5,
        confirmKeys: [13, 32, 44],
        allowDuplicates: false,
        cancelConfirmKeysOnEmpty: false
    });

    // добавление существующих тегов и окрашивание их в соответствии с их статусом
    if (tags) {
        for (var i = 0; i < tags.length; i++) {
            colortags.tagsinput('add', tags[i]['name']);
            var e = $('.js-input-tags-wrap .tag:last');
            if (tags[i]['status'] == 1) {
                e.removeClass('label-plain');
                e.addClass('label-success');
            } else if (tags[i]['status'] == -1) {
                e.removeClass('label-plain');
                e.addClass('label-danger');
            }
        }
    }

    // проверка на регистр при добавлении (чтобы избежать дубликатов)
    colortags.on('beforeItemAdd', function (event) {
        if (event.item !== event.item.toLowerCase()) {
            event.cancel = true;
            $(this).tagsinput('add', event.item.toLowerCase());
        }
    });

    // изменение цвета тега
    colortags.on('itemAdded', function (event) {
        var elem = $('.bootstrap-tagsinput .tag:last');
        $.ajax({
            url: url + 'tag_status',
            dataType: "json",
            data:
                {
                    name: event.item.toLowerCase()
                },
            success: function (data) {
                if (data.status) {
                    if (data.message.data == 1) {
                        elem.removeClass('label-plain');
                        elem.addClass('label-success');
                    }
                }
            }
        });
    });

    // подсказки
    $('.bootstrap-tagsinput input').autocomplete({
        source: function (request, response) {
            $.ajax({
                url: url + 'tag_autocomplete',
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
                }
            });
        },
        minLength: 1,
        delay: 1,
        select: function (event, ui) {
            var input = $('.bootstrap-tagsinput input');
            input.val('');
            $('#tags-with-colors').tagsinput('add', ui.item.label);
            return false;
        }
    });

}

//===============================================
// Получить label по id
//===============================================
function get_label_for_by_id(id) {
    return $('label[for="' + id + '"]');
}


//===============================================
// Автоматическое заполнение textarea для CKEDITOR
//===============================================
$(function () {
    if (typeof(CKEDITOR) !== "undefined") {
        for (var i in CKEDITOR.instances) {
            CKEDITOR.instances[i].on('change', function () {
                CKEDITOR.instances[i].updateElement();
                var textarea = CKEDITOR.instances[i].element.$;
                var string = $(textarea).val();

                // CKEditor вместо Enter'ов просто закрывает прошлый <p> и открывает новый. Проблема в том, что <p> без текста не занимает высоты. И Enter'ы не получатся. Эта строчка помогает исправить ситуацию.
                string = string.replace(/<p>\s*<\/p>/g, '&nbsp;');

                $(textarea).val(string)
                $(textarea).trigger('change');
            });
        }
    }
});

//===============================================
// Работа со строками
//===============================================
// Удалить теги
function strip_tags(html) {
    var div = document.createElement("div");
    div.innerHTML = html;
    return div.textContent || div.innerText || "";
}
// Заменить много пробелов на один
function multiple_space_to_single(string) {
    return string.replace(/\s\s+/g, ' ');
}
// Заменить много переносов строк на один
function multiple_newline_to_single(string) {
    return string.replace(/\r*|\n*/g, ' ');
}
// Форма слова
function form_of_word(n, f1, f2, f5) {
    // example form_of_word(count, 'товар', 'товара', 'товаров');
    n = Math.abs(parseInt(n)) % 100;
    if (n > 10 && n < 20) {
        return f5;
    }
    n = n % 10;
    if (n > 1 && n < 5) {
        return f2;
    }
    if (n == 1) {
        return f1;
    }
    return f5;
}

//===============================================
// Функция для плавного скролла к элементу
//===============================================
function scroll_to($element) {
    $('html, body').animate({
        scrollTop: ($element.offset().top - 20) + 'px'
    }, 'fast');
}

//===============================================
// Установить data для CKEditor по textarea
//===============================================
function ckeditor_set_data_by_textarea($textarea, data) {
    var ckeditor = CKEDITOR.instances[$textarea.attr('id')];
    ckeditor.setData(data);
    ckeditor.updateElement();
}