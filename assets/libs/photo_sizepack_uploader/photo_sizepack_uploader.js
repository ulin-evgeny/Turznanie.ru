const MB = 1048576;

(function ($) {

    var noop = function () {
        return undefined
    };

    var methods = {
        init: function (settings) {
            return this.each(function () {
                // Создаём настройки по-умолчанию, расширяя их с помощью параметров, которые были переданы
                settings = $.extend({
                    text_btn_remove: 'Удалить',
                    text_btn_change: 'Изменить',
                    text_btn_upload: 'Загрузить',
                    btn_upload: false,
                    btn_remove: false,
                    types: 'jpeg jpg gif png',
                    maxsize: MB,
                    text_types: 'true',
                    text_maxsize: 'true',
                    text_div: false,
                    photo_default: '',
                    photo_current: '',
                    width: 138,
                    height: 138,
                    on_init: noop,
                    on_remove_photo: noop
                }, settings);

                // -----------------------------------------------
                // Установка переменных и data значений
                //------------------------------------------------
                var $this = $(this);
                $this.data('settings', settings);
                $this.data('has_photo', settings.photo_default != settings.photo_current);
                var has_photo = $this.data('has_photo');

                // -----------------------------------------------
                // Добавление необходимых для работы элементов
                //------------------------------------------------
                $this.addClass('photo-sizepack-uploader__input-file');
                var $parent = $this.wrap('<div class="photo-sizepack-uploader"></div>').parent();

                // photo-changed input
                var $photo_changed = $('<input name="photo_changed" type="hidden" class="photo-sizepack-uploader__photo_changed" value="0"/>').appendTo($parent);

                // img с выводом фотки и его wrap'ера
                var $img_and_btns = $('<div class="photo-sizepack-uploader__img-and-btns"></div>').appendTo($parent);
                var $img_wrap = $('<div class="photo-sizepack-uploader__img-wrap"></div>').appendTo($img_and_btns);
                var photo_current_value = settings.photo_current;
                var photo_default_value = settings.photo_default;

                var $img = $('<img src="' + photo_current_value + '" data-photo-default="' + photo_default_value + '" data-types="' + settings.types + '" data-max-size="' + settings.maxsize + '" class="photo-sizepack-uploader__img img">').appendTo($img_wrap);

                $this.data('div_img', $img);

                // Кнопка загрузки / изменения
                var $btn_upload;
                if (!settings.btn_upload) {
                    $btn_upload = $('<div><a class="photo-sizepack-uploader__btn-upload black custom-elems__link custom-elems__link_type_underline-solid" data-message="' + (has_photo == 1 ? settings.text_btn_upload : settings.text_btn_change) + '">' + (has_photo == 1 ? settings.text_btn_change : settings.text_btn_upload) + '</a></div>').appendTo($img_and_btns).find('a');
                } else {
                    $btn_upload = settings.btn_upload;
                }

                // Кнопка удаления
                var $btn_remove;
                if (!settings.btn_remove) {
                    $btn_remove = $('<div><a class="photo-sizepack-uploader__btn-remove black custom-elems__link custom-elems__link_type_underline-solid"' + (has_photo == 1 ? '' : ' style="display: none;"') + '>' + settings.text_btn_remove + '</a></div>').appendTo($img_and_btns).find('a');
                } else {
                    $btn_remove = settings.btn_upload.addClass('photo-sizepack-uploader__btn-upload');
                }

                // Текст - возможные типы и максимальный размер
                if (settings.text_types || settings.text_maxsize) {
                    var $text_div;
                    if (settings.text_div) {
                        $text_div = settings.text_div;
                    } else {
                        $text_div = $('<div class="photo-sizepack-uploader__text"></div>').appendTo($parent);
                    }
                    if (settings.text_types) {
                        $('<span>Для загрузки изображения допустимы следующие форматы: ' + settings.types.replace(/ /g, ', ') + '. </span>').appendTo($text_div);
                    }
                    if (settings.text_maxsize) {
                        $('<span>Максимальный размер файла - ' + settings.maxsize / MB + 'MB.</span>').appendTo($text_div);
                    }
                }

                settings.on_init();

                // -----------------------------------------------
                // Действия
                //------------------------------------------------
                // Добавление обработчиков
                // Загрузка
                $btn_upload.on('click', function (e) {
                    e.preventDefault();
                    $this.trigger('click');
                });
                // Удаление
                $btn_remove.on('click', function (e) {
                    e.preventDefault();
                    if ($(this).css('display') != 'none') {
                        $photo_changed.val(1);
                        toggle_text($btn_upload, 'data-message');
                        resetFormElement($this);
                        $(this).css('display', 'none');
                        $img.attr('src', settings.photo_default);
                        $this.data('has_photo', false);
                        settings.on_remove_photo();
                    }
                });

                // Загрузка файла
                $this.change(function () {
                    // валидация
                    if (!file_valid_existence_types_maxsize(this, $img.attr('data-types'), $img.attr('data-max-size'))) {
                        return false;
                    }
                    // действия после загрузки файла
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        if ($btn_remove.css('display') == 'none') {
                            toggle_text($btn_upload, 'data-message');
                            $btn_remove.removeAttr('style');
                        }
                        $img.attr('src', e.target.result);
                        $this.data('has_photo', true);
                        $photo_changed.val(1);
                    };
                    if ($this[0].files[0]) { // проверка нужна, чтобы не было ошибки, когда пользователь отменил выбор файла
                        reader.readAsDataURL($this[0].files[0]);
                    }
                });
            });
        },
        change_default_photo: function (path) {
            return this.each(function () {
                var $this = $(this);
                $this.data('settings').photo_default = path;
                if (!$this.data('has_photo')) {
                    $this.data('div_img').attr('src', path);
                }
            });
        }
    }

    // -----------------------------------------------
    // Логика вызова методов
    //------------------------------------------------
    $.fn.photo_sizepack_uploader = function (method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Метод с именем ' + method + ' не существует для jQuery.photo_sizepack_uploader');
        }
    }

    // -----------------------------------------------
    // Разные функции, которые использует плагин
    //------------------------------------------------
    function toggle_text(elem, attr) {
        var message = elem.attr(attr);
        elem.attr(attr, elem.text());
        elem.text(message);
    }

    function resetFormElement(e) {
        $(e).wrap('<form>').closest('form').get(0).reset();
        $(e).unwrap();
    }

    // Проверяет наличие (загрузился ли файл), тип, максимальный размер
    function file_valid_existence_types_maxsize(input_file, photo_types, max_size) {
        var valid_error = false;
        if (input_file.files[0]) {
            // тип файла
            var ext = get_extension_of_file(input_file);
            if (photo_types.split(' ').indexOf(ext.toLowerCase()) == -1) {
                custom_popup_open('Файл имеет неподходящий формат', {btn_1: BTN_OK_TEXT}, function ($popup, $btn_1) {
                    $btn_1.on('click', function () {
                        $popup.euv_custom_popup('close');
                    });
                });
                valid_error = true;
            }
            // размер изображения
            else if (input_file.files[0].size > max_size) {
                custom_popup_open('Превышен максимальный размер файла', {btn_1: BTN_OK_TEXT}, function ($popup, $btn_1) {
                    $btn_1.on('click', function () {
                        $popup.euv_custom_popup('close');
                    });
                });
                valid_error = true;
            }
            // действия при ошибки валидации
            if (valid_error == true) {
                resetFormElement(input_file); // если этого не сделать, то при повторном выборе файла с ошибкой сообщения об ошибке не будет
                return false;
            }
        } else {
            return false;
        }
        return true;
    }

})(jQuery);