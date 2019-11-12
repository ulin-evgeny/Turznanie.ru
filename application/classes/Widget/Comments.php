<?php

class Widget_Comments {

	static public function render_comments($item, $url) {
		$user = CurrentUser::get_user();
		$comments = $item->get_comments();
		$result = '<div data-url="' . $url . '" class="comments">
								<div class="custom-elems__h2">Комментарии</div>
								<div class="custom-ajax-comments-list">';
		foreach ($comments as $comment) {
			if (($comment->status != Status::STATUS_VISIBLE_VALUE && $user->is_admin_or_owner($comment) !== Model_User::USER_IS_NOT_ADMIN_OR_OWNER) || $comment->status == Status::STATUS_VISIBLE_VALUE) {
				$result .= static::render_comment($comment, $user);
			}
		}
		$result .= '</div>';
		if (CurrentUser::get_user()->is_approved()) {
			if ($item->have_access_to_hidden()) {
				$url_add = $url . '/comment_add';
				$url_edit = $url . '/comment_edit';
				$result .= '
				<div class="leave-comment">
					<form class="comments__form" data-edit-url-action="' . $url_edit . '" data-add-url-action="' . $url_add . '" action="' . $url_add . '">
						<div class="custom-elems__section">
							<div class="comments__notice-wrap"><span data-text="Изменение комментария (изменения будут отображены после проверки модератором)"  class="comments__notice">Ваш комментарий' . (!$user->is_admin($user) ? ' (будет отображаться после проверки модератором)' : '') . '</span><a class="comments__cancel-change black hidden custom-elems__link custom-elems__link custom-elems__link_type_underline-solid">Отмена</a></div>
							<div class="comments__mentioned-wrap hidden"><span>Будут упомянуты пользователи:</span></div>
							<input type="hidden" name="item_id" value="' . $item->id . '"/>
							' . HelperCKEditor::render('text', null, 'comments__textarea comment-textarea') . '
						</div>';
				$result .= '<a data-text="Изменить" class="js-custom-ajax-form-submit-btn comments__submit-btn button">Отправить</a>
					</form>
				</div>';
			} else {
				$result .= '<div>Нельзя комментировать скрытые публикации.</div>';
			}
		} elseif (!CurrentUser::get_user()->is_approved()) {
			$result .= '<div>Только подтвержденные пользователи могут оставлять комментарии. </div>';
		} else {
			$result .= '<div>Чтобы оставить комментарий, необходимо
										<a class="js-ajax-login">войти</a> или
										<a href="/registration">зарегистрироваться</a>, если у вас нет учетной записи.
									</div>';
		}
		$result .= '</div>';

		return $result;
	}

	static public function render_comment($comment, $user) {
		$mentioned_usernames = $comment->get_mentioned_usernames();
		$comment_user = ORM::factory('User', $comment->user_id);
		$is_admin_or_owner = $user->is_admin_or_owner($comment);
		$can_edit = $user->can_edit_comment($comment);
		$date = date("d.m.y H:i", strtotime($comment->date));
		$avatar = Helper::const_to_client($comment_user->get_photo_by_size(Model_User::PHOTO_SIZES['xs']));

		$result = '<div id="comment_' . $comment->id . '" data-id="' . $comment->id . '" class="comment clearfix">
									<div class="comment__img-wrap user-avatar-wrap user-avatar-wrap_size_xs">
										<img class="comment__img" src="' . $avatar . '" alt/>
									</div>
									<div class="comment__text-wrap">
										<div class="comment__top-line">
											<a class="comment__top-line-elem comment__username black custom-elems__link custom-elems__link_type_underline-solid" href="' . $comment_user->get_url() . '">' . $comment_user->username . '</a>
											<span class="comment__top-line-elem comment__date">' . $date . '</span>';
		if ($can_edit) {
			$result .= '<a class="comment__top-line-elem comment__delete black custom-elems__link custom-elems__link_type_underline-solid">Удалить</a>';
			$result .= '<a data-text="Изменение" class="comment__top-line-elem comment__edit black custom-elems__link custom-elems__link_type_underline-solid">Изменить</a>';
			$result .= '<span class="comment__top-line-elem comment__change-status-wrap">';
			if ($is_admin_or_owner === Model_User::USER_IS_ADMIN) {
				$result .= '<select class="comment-change-status">';
				foreach (Status::STATUSES_HV as $value => $title) {
					$result .= '<option value="' . $value . '" ' . ($value == $comment->status ? ' selected' : '') . '>' . $title . '</option>';
				}
				$result .= '</select>';
			}
			$result .= '</span>';
		}
		if ($is_admin_or_owner === Model_User::USER_IS_OWNER) {
			$result .= '<span class="comment__top-line-elem">' . Status::STATUSES_HV[$comment->status] . '</span>';
		}
		$result .= '<a title="Ссылка на комментарий" class="comment__top-line-elem comment__link-btn comment__link black custom-elems__link custom-elems__link_type_underline-solid" id="comment_' . $comment->id . '" href="#comment_' . $comment->id . '">#</a>';

		$result .= '</div>
								<div class="comment__text">' . $comment->text . '</div>';

		if (!empty($mentioned_usernames)) {
			$result .= '<div class="comment__bottom-line"><div class="comment__mentioned-wrap">Упомянуты:';

			$count_usernames = count($mentioned_usernames);
			foreach ($mentioned_usernames as $i => $username) {
				$result .= '<span class="comment__mentioned-elem comment__mentioned-user">' . $username . '</span>';
				if ($i < $count_usernames - 1) {
					$result .= ',';
				}
			}
			$result .= '.';

			if (CurrentUser::get_user()->is_admin()) {
				if ($comment->notification_sent_mentioned_users) {
					$result .= '<span class="comment__mentioned-elem">Уведомления отправлены.</span>';
				} else {
					$result .= '<span class="comment__mentioned-elem" title="Чтобы уведомить пользователей о том, что их упомянули, необходимо изменить статус комментария на &#34;' . Status::STATUS_VISIBLE_TITLE . '&#34;">Уведомления не отправлены.</span>';
				}
			}

			$result .= '</div></div>';
		}

		if ($comment->has_been_edit()) {
			$result .= '<div class="comment__last-edit">Последний раз редактировал пользователь ' . ORM::factory('User', $comment->edit_user_id)->username . ' в ' . Helper::date_to_russian_date($comment->edit_date) . '</div>';
		}
		if (CurrentUser::get_user()->is_approved() && $comment->user_id !== $user->id) {
			$result .= '<a class="comment-mention-btn comment__mention-btn black custom-elems__link custom-elems__link_type_underline-solid">Упомянуть</a>';
		}
		$result .= '</div>
							</div>';

		return $result;
	}
}