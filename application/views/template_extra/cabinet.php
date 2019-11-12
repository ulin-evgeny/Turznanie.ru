
<div class="page-cabinet">
	<aside class="left-side">
		<?php
		if ($cabinet_type == Model_Seo::ID_USER) {
			$url_part = array(
				Menu::KEY_URL_PART_POS => 2,
				Menu::KEY_URL_PART_VAL => $person->username
			);
		} else {
			$url_part = array();
		}
		echo Menu::render_menu($menu_items, $header, array());
		?>
	</aside>
	<div class="right-side">

		<h1 class="custom-elems__h1">
			<span><?= $seo_data['h1'] ?></span>

			<?php
			if (Request::initial()->controller() == Controller_Cabinet::get_controller_name()) { ?>
				<span class="page-cabinet__success js-custom-ajax-notification">(изменения сохранены)</span>
			<?php } ?>
		</h1>

		<?php include(Controller::get_include($template)); ?>

	</div>
</div>
