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
							item_id: null
						}, settings);

						// Проверка - есть ли url и id?
						if (!settings.url || !settings.id) {
							if (!settings.url) {
								console.log('Ошибка! Нет url для проверки через ajax_select.');
							}
							if (!settings.id) {
								console.log('Ошибка! Нет id для работы ajax_select.');
							}
							return false;
						}

						var $this = $(this);

						$this.attr('data-old-value', $this.val());

						$this.on('change', function () {
							$this.attr('data-new-value', $this.val())
							$this.val($this.attr('data-old-value'));
							$this.niceSelect('update'); // чтобы отменить изменение значения у nice-select (сбросить на старое)

							$.ajax({
								type: "POST",
								url: settings.url,
								data: {
									'id': settings.id,
									'status': $this.attr('data-new-value')
								},
								dataType: "json",
								success: function (data) {
									if (data.status) {
										$this.trigger('success', data);
										$this.val($this.attr('data-new-value'));
										$this.attr('data-old-value', $this.val())
										$this.removeAttr('data-new-value');
										$this.niceSelect('update');
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
	$.fn.ajax_select = function (method) {
		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof method === 'object' || !method) {
			return methods.init.apply(this, arguments);
		} else {
			$.error('Метод с именем ' + method + ' не существует для ajax_select');
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