/*
Загрузчик файлов на сервер. Вешается на обычный div (или другой тег) и сам создает нужные для себя элементы.
*/

// ===========================================
// Инициализация
// ===========================================
(function ($) {

    var methods = {
        init: function (settings) {
            return this.each(function () {
                    var $uploader = $(this);

                    // ------------------------------------
                    // Создаём настройки по-умолчанию, расширяя их с помощью параметров, которые были переданы
                    // ------------------------------------
                    settings = $.extend({
                        exts: [],
                        max_size: '',
                        upload_btn_text: 'Добавить файл',
                        btn_text: '<span class="custom-uploader__icon-cancel icon-cancel"></span>',
                        default_values: [],
                        only_one_ext: false
                    }, settings);

                    // ------------------------------------
                    // Создаем необходимые для работы плагина элементы
                    // ------------------------------------
                    $uploader.addClass('custom-uploader');
                    var $template = $('<div class="custom-uploader__file custom-uploader__template hidden">' +
                        '<input name="books[]" class="hidden custom-uploader__input" type="file">' +
                        '<span class="custom-uploader__text"></span><span class="custom-uploader__size"></span>' +
                        '<a class="custom-uploader__upload-btn black custom-elems__link custom-elems__link_type_underline-solid">' + settings.upload_btn_text + '</a>' +
                        '<a class="custom-uploader__remove-btn hidden">' + settings.btn_text + '</a>' +
                        '</div>').appendTo($uploader);
                    var $custom_uploader_has_been_change = $('<input name="custom_uploader_has_been_change" type="checkbox" class="hidden">').appendTo($uploader);
                    $('<p>Для загрузки файла допустимы следующие форматы: ' + (settings.exts).join(', ') + ' (по одному файлу на каждый формат). Максимальный размер файла - ' + settings.max_size / 1024 / 1024 + ' Mb.</p>').appendTo($uploader);

                    var $to_delete_values = $('<input class="custom-uploader__to-delete hidden" name="custom_uploader_to_delete">').appendTo($uploader);

                    if (Array.isArray(settings.default_values) || settings.default_values.length) {
                        $(settings.default_values).each(function (i, e) {
                            // проверка - если это не пустой элемент
                            if (e.length) {
                                add_file($uploader, e);
                            }
                        });
                    }

                    add_file($uploader);

                    // ------------------------------------
                    // Обработчики
                    // ------------------------------------
                    // Вызвать загрузку файлов
                    $(document).on('click', '.custom-uploader__upload-btn', function () {
                        $(this).closest('.custom-uploader__file').find('.custom-uploader__input').trigger('click');
                    });

                    // Что будет после загрузки файла
                    $(document).on('change', '.custom-uploader__input', function () {
                        if (settings.exts !== null) {
                            if (!check_ext(this, settings.exts)) {
                                return false;
                            }
                            if (settings.only_one_ext) {
                                var exists_exts = [];
                                $uploader.find('.custom-uploader__file').each(function (i, e) {
                                    var text = $(e).find('.custom-uploader__text').html();
                                    if (text.length) {
                                        var ext = get_extension(text);
                                        exists_exts.push(ext);
                                    }
                                });
                                var ext = get_extension(this.files[0]['name']);
                                if (exists_exts.includes(ext)) {
                                    custom_popup_open('Нельзя загрузить два файла с одинаковым расширением (у вас повторяется расширение ' + ext + ')', {btn_1: BTN_OK_TEXT}, function ($popup, $btn_1) {
                                        $btn_1.on('click', function () {
                                            $popup.euv_custom_popup('close');
                                        });
                                    });

                                    // Если этого не сделать, то при повторном выборе файла с ошибкой сообщения об ошибке не будет
                                    resetFormElement(this);
                                    return false;
                                }
                            }
                        }

                        if (settings.max_size !== null) {
                            if (!check_maxsize($(this)[0], settings.max_size)) {
                                return false;
                            }
                        }

                        var $file = $(this).closest('.custom-uploader__file');
                        var $text = $file.find('.custom-uploader__text');
                        var $size = $file.find('.custom-uploader__size');
                        $file.find('.custom-uploader__upload-btn').addClass('hidden');
                        $file.find('.custom-uploader__remove-btn').removeClass('hidden');

                        $text.html(this.files[0].name);
                        $size.html(' (' + (this.files[0].size / 1048576).toFixed(2) + 'MB)');

                        add_file($uploader);
                        $custom_uploader_has_been_change.attr('checked', true)
                        $file.find('.custom-uploader__upload-btn').remove();
                    });

                    // Удаление загруженного файла
                    $(document).on('click', '.custom-uploader__remove-btn', function () {
                        var $this = $(this);
                        var $file = $this.closest('.custom-uploader__file');
                        if ($file.hasClass('custom-uploader__file_to-delete')) {
                            var string = $to_delete_values.val();
                            if (string.length) {
                                string += ';';
                            }
                            string += $file.find('.custom-uploader__text').html();
                            $to_delete_values.val(string);
                        }
                        $(this).closest('.custom-uploader__file').remove();
                        $custom_uploader_has_been_change.attr('checked', true)
                    });
                }
            );
        }
    }

    // ===========================================
    // Логика вызова методов
    // ===========================================
    $.fn.custom_uploader = function (method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Метод с именем ' + method + ' не существует для jQuery.custom_ajax');
        }
    }

    // ===========================================
    // Разные функции, которые использует плагин
    // ===========================================
    // ----------------------------------------------
    // Добавление файла
    // ----------------------------------------------
    function add_file($uploader, name_to_delete) {
        name_to_delete = name_to_delete || false;

        var $template = $('.custom-uploader__template');
        var $file = $template.clone();
        var $text = $file.find('.custom-uploader__text');

        $file.appendTo($uploader);
        $file.removeClass('custom-uploader__template');
        $file.removeClass('hidden');
        if (name_to_delete) {
            $file.addClass('custom-uploader__file_to-delete');
            $file.find('.custom-uploader__upload-btn').remove();
            $file.find('.custom-uploader__input').remove();
            $file.find('.custom-uploader__remove-btn').removeClass('hidden');
            $text.html(name_to_delete);
            $text.removeClass('hidden');
        }
    }

    // ----------------------------------------------
    // Функция для сброса <input>
    // ----------------------------------------------
    function resetFormElement(e) {
        $(e).wrap('<form>').closest('form').get(0).reset();
        $(e).unwrap();
    }

    // ----------------------------------------------
    // Получение расширения файла
    // ----------------------------------------------
    function get_extension_of_file(input) {
        var splitted = input.files[0].name.split('.');
        return splitted[splitted.length - 1];
    }

    // ----------------------------------------------
    // Валидация на тип
    // ----------------------------------------------
    function check_ext(input, accessible_exts) {
        var ext = get_extension_of_file(input);
        if (accessible_exts.indexOf(ext.toLowerCase()) === -1) {
            custom_popup_open('Файл имеет неподходящий формат', {btn_1: BTN_OK_TEXT}, function ($popup, $btn_1) {
                $btn_1.on('click', function () {
                    $popup.euv_custom_popup('close');
                });
            });

            // Если этого не сделать, то при повторном выборе файла с ошибкой сообщения об ошибке не будет
            resetFormElement(input);
            return false;
        }
        return true;
    }

    // ----------------------------------------------
    // Валидация на размер файла
    // ----------------------------------------------
    function check_maxsize(input, max_size) {
        if (input.files[0].size > max_size) {
            custom_popup_open('Превышен максимальный размер файла', {btn_1: BTN_OK_TEXT}, function ($popup, $btn_1) {
                $btn_1.on('click', function () {
                    $popup.euv_custom_popup('close');
                });
            });

            // Если этого не сделать, то при повторном выборе файла с ошибкой сообщения об ошибке не будет
            resetFormElement(input);
            return false;
        }
        return true;
    }

    function get_extension(string) {
        var parts = string.split('.');
        return parts[parts.length - 1];
    }


})(jQuery);