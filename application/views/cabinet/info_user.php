<div class="page-cabinet__column page-cabinet__avatar user-avatar-wrap user-avatar-wrap_size_sm">
	<img src="<?= $avatar ?>">
</div>

<div class="clearfix custom-elems__section">
	<div class="custom-elems__string">
		<span class="page-cabinet__label page-cabinet__label_length_short custom-elems__label">Логин:</span>
		<span><?= $person->username ?></span>
	</div>
	<?php if (CurrentUser::get_user()->is_admin()) { ?>
		<div class="custom-elems__string">
			<span class="page-cabinet__label page-cabinet__label_length_short custom-elems__label">Email:</span>
			<span><?= $person->email ?></span>
		</div>
	<?php } ?>
	<div class="custom-elems__string">
		<span class="page-cabinet__label page-cabinet__label_length_short custom-elems__label">Пол:</span>
		<span><?= Cabinet::SEX_TYPES[$person->sex] ?></span>
	</div>
	<div class="custom-elems__string">
		<span class="page-cabinet__label page-cabinet__label_length_medium custom-elems__label">Дата рождения:</span>
		<span><?= $person->get_birthday() ?></span>
	</div>
	<div class="custom-elems__string">
		<span class="page-cabinet__label page-cabinet__label_length_medium custom-elems__label">На проекте с:</span>
		<span><?= date('d.m.Y', strtotime($person->date)) ?></span>
	</div>
</div>

<div class="custom-elems__section">
	<h2 class="custom-elems__h2">
		<label>О себе</label>
	</h2>
	<div class="custom-elems__string page-cabinet__about-section">
		<?= $person->get_about() ?>
	</div>
</div>