<ul class="mailing">
	<li class="mailing__item-wrap">
		<input class="mailing__item-checkbox" type="checkbox" id="material_status"<?= $user_notifications->material_status ? ' checked' : '' ?>>
		<label class="mailing__item-label custom-elems__link custom-elems__link_type_underline-solid" for="material_status">Изменение статуса вашей публикации</label>
	</li>
	<li class="mailing__item-wrap">
		<input class="mailing__item-checkbox" type="checkbox" id="comment_status"<?= $user_notifications->comment_status ? ' checked' : '' ?>>
		<label class="mailing__item-label custom-elems__link custom-elems__link_type_underline-solid" for="comment_status">Изменение статуса вашего комментария</label>
	</li>
	<li class="mailing__item-wrap">
		<input class="mailing__item-checkbox" type="checkbox" id="comment_add"<?= $user_notifications->comment_add ? ' checked' : '' ?>>
		<label class="mailing__item-label custom-elems__link custom-elems__link_type_underline-solid" for="comment_add">Вашу публикацию прокомментировали</label>
	</li>
	<li class="mailing__item-wrap">
		<input class="mailing__item-checkbox" type="checkbox" id="mention_in_comment"<?= $user_notifications->mention_in_comment ? ' checked' : '' ?>>
		<label class="mailing__item-label custom-elems__link custom-elems__link_type_underline-solid" for="mention_in_comment">Вас упомянули в комментарии</label>
	</li>
</ul>