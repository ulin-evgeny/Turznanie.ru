// -----------------------------------------------------------------------------------
// Этот скрипт использует функции из functions.js, custom_ajax.js и ajax_select.js, поэтому подключаться должен после них.
// -----------------------------------------------------------------------------------

$(function () {
    var $comment_cancel_change = $('.comments__cancel-change');
    var $textarea = $('.comments__textarea');
    var $notice = $('.comments__notice');
    var $submit_btn = $('.comments__submit-btn');
    var $form = $('.comments__form');
    var $mentioned_wrap = $('.comments__mentioned-wrap');
    var ckeditor = CKEDITOR.instances[$textarea.attr('id')];

    function success_func_comment_add(data) {
        var $new_comment = $(data.message).appendTo('.custom-ajax-comments-list');
        comment_add_js($new_comment);
        after_success($new_comment);
    }

    function success_func_comment_edit(data, is_change_status) {
        var $editable_comment = $('.comment[data-id=' + data.message.comment_id + ']');
        var $new_comment = $(data.message.comment).insertAfter($editable_comment);
        $editable_comment.remove();
        comment_add_js($new_comment);
        if (!is_change_status) {
            after_success($new_comment);
        }
    }

    function after_success($comment) {
        ckeditor_set_data_by_textarea($('.comments__textarea'), '');
        if ($form.attr('action') == $form.attr('data-edit-url-action')) {
            form_add_or_edit_toggle();
        }
        $mentioned_wrap.find('.comments__mentioned-user').remove();
        check_and_toggle_mentioned_wrap();
        $comment.find('.comment__link-btn')[0].click();
    }

    $submit_btn.on('click', function (e) {
        e.preventDefault();
        var $this = $(this);
        $this.closest('.comments__form').submit();
    });

    $form.on('submit', function (e) {
        e.preventDefault();
        var $this = $(this);

        var success_func;
        switch ($this.attr('action')) {
            case $this.attr('data-add-url-action'):
                success_func = success_func_comment_add;
                break;
            case $this.attr('data-edit-url-action'):
                success_func = success_func_comment_edit;
                break;
        }

        $(this).custom_ajax({
            finish: function (data) {
                if (data.status == 1) {
                    success_func(data);
                }
            }
        });
    });

    // ----------------------------------------------
    // Добавление комментариям обработчиков.
    // Делается через функцию comment_add_js, а не при загрузке страницы, так как комментарий может быть добавлен после загрузки.
    // ----------------------------------------------
    $('.comment').each(function () {
        comment_add_js($(this));
    });

    function comment_add_js($comment) {
        var common_url = $comment.closest('.comments').attr('data-url');
        var id = $comment.attr('data-id');

        add_handler_change_status($comment, common_url, id);
        add_handler_delete($comment, common_url, id);
        add_handler_edit($comment);
        add_handler_mention($comment);
    }

    // ----------------------------------------------
    // Кнопка для отмены редактирования и ее функции
    // ----------------------------------------------
    $comment_cancel_change.on('click', function () {
        if ($form.hasClass('js-custom-ajax_is-sending')) {
            return;
        }
        ckeditor.setData('');
        ckeditor.updateElement();
        var $editable_comment = $('.comment_is_editable');
        comment_add_or_edit_toggle($editable_comment);
        form_add_or_edit_toggle();
        $form.attr('action', $form.attr('data-add-url-action'));
        $('.js-comment-id').remove();

        $mentioned_wrap.find('.comments__mentioned-user').remove();
        check_and_toggle_mentioned_wrap();
    });

    function comment_add_or_edit_toggle($comment) {
        var toggle_classes = 'custom-elems__link custom-elems__link_type_underline-solid disabled';
        var $comment_edit = $comment.find('.comment__edit');
        $comment_edit.toggleClass(toggle_classes);
        toggle_text($comment_edit, 'data-text');
        $comment.toggleClass('comment_is_editable');
        var $delete_btn = $comment.find('.comment__delete');
        $delete_btn.toggleClass(toggle_classes);
        $('.js-comment-id').val($comment.attr('data-id'));
    }

    function form_add_or_edit_toggle() {
        var $notice = $('.comments__notice');
        toggle_text($notice, 'data-text');
        toggle_text($submit_btn, 'data-text');
        $comment_cancel_change.toggleClass('hidden');
        if ($form.attr('action') == $form.attr('data-add-url-action')) {
            $form.attr('action', $form.attr('data-edit-url-action'));
        } else {
            $form.attr('action', $form.attr('data-add-url-action'));
        }
    }

    function check_and_toggle_mentioned_wrap() {
        if ($mentioned_wrap.find('.comments__mentioned-user').length) {
            $mentioned_wrap.removeClass('hidden');
        } else {
            $mentioned_wrap.addClass('hidden');
        }
    }

    // ---------------------------------------------
    // Функции добавления обработчиков комментариям
    // ---------------------------------------------
    function add_handler_change_status($comment, common_url, id) {
        var $change_status = $comment.find('.comment-change-status');
        $change_status.ajax_select({
            url: common_url + '/comment_change_status',
            id: id
        });
        $change_status.on('success', function (e, data) {
            success_func_comment_edit(data, true)
        });
        $change_status.addClass('nice-select nice-select-style-1')
        $change_status.niceSelect();
    }

    function add_handler_delete($comment, common_url, id) {
        var $this = $comment.find('.comment__delete');
        $this.on('click', function () {
            if ($comment.hasClass('comment_is_editable')) {
                return;
            }
            custom_popup_open('Вы действительно хотите удалить этот комментарий?', {
                btn_1: BTN_YES_TEXT, btn_2: BTN_NO_TEXT
            }, function ($popup, $btn_1, $btn_2) {
                $btn_1.on('click', function () {
                    $popup.euv_custom_popup('close');
                    $this.custom_ajax({
                        url: common_url + '/comment_delete',
                        data: {'id': id},
                        success: function () {
                            $comment.remove();
                        }
                    });
                });
                $btn_2.on('click', function () {
                    $popup.euv_custom_popup('close');
                });
            });
        });
    }

    function add_handler_edit($comment) {
        $comment.find('.comment__edit').on('click', function () {
            if ($form.hasClass('js-custom-ajax_is-sending')) {
                return;
            }

            var $closest_comment = $(this).closest('.comment');
            if ($closest_comment.hasClass('comment_is_editable')) {
                return;
            }

            var comment_html = $closest_comment.find('.comment__text').html();
            scroll_to($notice);
            ckeditor.setData(comment_html);
            ckeditor.updateElement();

            $mentioned_wrap.find('.comments__mentioned-user').remove();

            // Добавление упомянутых пользователей
            var $mentioned_elems = $comment.find('.comment__mentioned-user');
            if ($mentioned_elems.length) {
                $mentioned_elems.each(function (i, e) {
                    add_mentioned_elems_to_mentioned_wrap($(e).html());
                })
            }

            var amount_of_editable_comments = $('.comment_is_editable').length;
            if (amount_of_editable_comments == 0) {
                form_add_or_edit_toggle();
                $form.attr('action', $form.attr('data-edit-url-action'));
                $form.append('<input class="js-comment-id" type="hidden" name="id" value="' + $closest_comment.attr('data-id') + '">');
                $form.removeClass('js-custom-ajax-form_success-func_comment_add');
                $form.addClass('js-custom-ajax-form_success-func_comment_edit');
                // добавляем новый комментарий editable
                comment_add_or_edit_toggle($closest_comment);
            } else {
                // отменяем изменения у предыдущего editable комментария
                comment_add_or_edit_toggle($('.comment_is_editable'));
                // добавляем новый комментарий editable
                comment_add_or_edit_toggle($closest_comment);
            }
        });
    }

    function add_handler_mention($comment) {
        $comment.find('.comment-mention-btn').on('click', function () {
            var $this = $(this);
            scroll_to($notice);

            var username = $($this.closest('.comment').find('.comment__username')).html();
            if (!mentioned_text_check_username(username)) {
                add_mentioned_elems_to_mentioned_wrap(username)
            }
        });

        function mentioned_text_check_username(username) {
            var username_exists = false;
            $mentioned_wrap.find('.comment__mentioned-username').each(function (i, e) {
                if ($(e).html() === username) {
                    username_exists = true;
                    return false;
                }
            });
            return username_exists;
        }
    }
    // ---------------------------------------------

    function add_mentioned_elems_to_mentioned_wrap(username) {
        var mentioned_elem = $('<span class="comments__mentioned-user"><span class="comment__mentioned-username">' + username + '</span> <a class="comments__mentioned-cancel js-comment-mention-user-cancel icon-cancel"></a><input type="hidden" name="mentioned_user_usernames[]" value="' + username + '"></span>').appendTo($mentioned_wrap);

        mentioned_elem.find('.js-comment-mention-user-cancel').on('click', function () {
            mentioned_elem.remove();
            check_and_toggle_mentioned_wrap();
        });

        check_and_toggle_mentioned_wrap();
    }
});






