<?php
$avatar_default_man = Helper::const_to_client(Model_User::get_default_photo_by_size_and_sex($avatar_size, Model_User::SEX_MAN));
$avatar_default_woman = Helper::const_to_client(Model_User::get_default_photo_by_size_and_sex($avatar_size, Model_User::SEX_WOMAN));
$types = PhotoSizepackUploader::get_string_types(Model_User::PHOTO_EXTENSIONS);
$maxsize = Model_User::MAX_PHOTO_SIZE;
?>

<form method="POST" action="/cabinet" class="js-custom-ajax-form js-custom-ajax-form_success_notification" enctype="multipart/form-data">
	<input type="hidden" class="js-server-values"
		data-avatar-default-man="<?= $avatar_default_man ?>"
		data-avatar-default-woman="<?= $avatar_default_woman ?>"
		data-avatar-current="<?= $avatar ?>"
		data-image-types="<?= $types ?>"
		data-maxsize="<?= $maxsize ?>"
		data-sex="<?= $person->sex ?>"
		data-sex-man="<?= Model_User::SEX_MAN ?>"
		data-sex-woman="<?= Model_User::SEX_WOMAN ?>"
	>

	<div class="clearfix custom-elems__section">
		<div class="page-cabinet__column page-cabinet__photo-wrap">
			<input type="file" name="photo" class="page-cabinet__photo">
		</div>
		<div class="custom-elems__string">
			<span class="page-cabinet__label page-cabinet__label_length_short custom-elems__label">Логин:</span>
			<input class="custom-elems__input" name="username" value="<?= $person->username ?>"/>
		</div>
		<div class="custom-elems__string">
			<span class="page-cabinet__label page-cabinet__label_length_short custom-elems__label">Пол:</span>
			<select class="custom-elems__input page-cabinet__change-sex" name="sex">
				<?php
				for ($i = 0; $i < 3; $i++) {
					echo '<option value="' . $i . '" ' . ($person->sex == $i ? 'selected' : '') . '>' . Cabinet::SEX_TYPES[$i] . '</option>';
				}
				?>
			</select>
		</div>
		<div class="custom-elems__string">
			<span class="page-cabinet__label page-cabinet__label_length_medium custom-elems__label">Дата рождения:</span>
			<input data-placeholder="<?= Model_User::NOT_SPECIFIED_PLACEHOLDER ?>" class="page-cabinet__birthday custom-elems__input js-datepicker" name="birthday" value="<?= $person->get_birthday() ?>"/>
		</div>
		<div class="custom-elems__string">
			<span class="page-cabinet__label page-cabinet__label_length_medium custom-elems__label">На проекте с:</span>
			<input class="custom-elems__input" readonly value="<?= date('d.m.Y', strtotime($person->date)) ?>">
		</div>
	</div>

	<div class="custom-elems__section">
		<h2 class="custom-elems__h2">
			<label>О себе</label>
		</h2>
		<div class="custom-elems__string">
			<?= HelperCKEditor::render('about', $person->about) ?>
		</div>
	</div>

	<div class="custom-elems__section">
		<div class="page-cabinet__btn-list">
			<a class="button page-cabinet__btn-list-item" href="/cabinet/change_password">Изменить пароль</a>
			<a class="button page-cabinet__btn-list-item" href="/cabinet/change_email">Изменить Email</a>
			<a class="button js-custom-ajax-form-submit-btn page-cabinet__btn-list-item">Сохранить</a>
		</div>
		<p class="page-cabinet__photo-text"></p>
	</div>

</form>