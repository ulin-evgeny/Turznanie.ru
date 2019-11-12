//===============================================
// Если на сервере все окей, то переключение работает. В противном случае - нет.
//===============================================

(function ($) {

	var methods = {
		init: function (settings) {
			return this.each(function () {
						// Создаём настройки по-умолчанию, расширяя их с помощью параметров, которые были переданы
						settings = $.extend({
							url: null,
							data: null
						}, settings);

						// Проверка - есть ли url и data?
						if (!settings.url || !settings.data) {
							if (!settings.url) {
								console.log('Ошибка! Нет url для проверки через ajax_checkbox.');
							}
							if (!settings.data) {
								console.log('Ошибка! Нет data для работы ajax_checkbox.');
							}
							return false;
						}

						var $this = $(this);
						$this.attr('data-old-value', $this.is(":checked"));

						$this.on('change', function () {
							$this.attr('data-new-value', $this.is(":checked"));
							$this.prop('checked', $this.attr('data-old-value') === 'true');
							settings.data = $.extend(settings.data, {'status': $this.attr('data-new-value')});
							$.ajax({
								type: "POST",
								url: settings.url,
								data: settings.data,
								dataType: "json",
								success: function (data) {
									if (data.status) {
										$this.trigger('success', data);
										$this.prop('checked', $this.attr('data-new-value') === 'true');
										$this.attr('data-old-value', $this.is(':checked'))
										$this.removeAttr('data-new-value');
									} else {
										$this.trigger('unsuccess');
										var message = data.message;
										// get_deepest нужен, так как ORM_Validation_Exception заворачивает свое сообщение в еще одно свойство message (а то и в несколько - у user _external, например).
                                        custom_popup_open(get_deepest(message), {btn_1: BTN_OK_TEXT}, function ($popup, $btn_1) {
                                            $btn_1.on('click', function () {
                                                $popup.euv_custom_popup('close');
                                            });
                                        });
									}
								},
								error: function (data) {
									$('body').html(data.responseText);
								}
							});
						});
					}
			);
		}
	}

	// -----------------------------------------------
	// Логика вызова методов
	//------------------------------------------------
	$.fn.ajax_checkbox = function (method) {
		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof method === 'object' || !method) {
			return methods.init.apply(this, arguments);
		} else {
			$.error('Метод с именем ' + method + ' не существует для ajax_checkbox');
		}
	}

	// -----------------------------------------------
	// Разные функции, которые использует плагин
	// -----------------------------------------------
	function get_deepest(e) {
		if (typeof(e) === 'object') {
			e = e[Object.keys(e)[0]];
			return get_deepest(e);
		} else {
			return e;
		}
	}

})(jQuery);