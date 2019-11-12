<?php
// default values
if (!isset($btn_text)) {
	$btn_text = 'На главную';
}
if (!isset($btn_href)) {
	$btn_href = '/';
}
if (!isset($text)) {
	$text = 'Ошибка';
}
if (!isset($without_btn)) {
	$without_btn = false;
}
?>

<div class="page-message">
	<div class="page-message__text"><?= $text ?></div>
	<?php if (!$without_btn) { ?>
		<div class="page-message__btn-wrap">
			<a href="<?= $btn_href ?>" class="page-message__btn button"><?= $btn_text ?></a>
		</div>
	<?php } ?>
</div>