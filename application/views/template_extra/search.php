<div class="page-search">
	<aside class="left-side">
		<?= Menu::render_menu($menu_items, $header, $menu_params); ?>
	</aside>
	<div class="right-side">

		<h1 class="custom-elems__h1">
			<span><?= $seo_data['h1'] . ' по фразе "' . $search_text . '"' ?></span>
		</h1>

		<?php
		if ($not_found) {
			echo 'Ничего не найдено';
		} else {
			include(Controller::get_include($template));
		}

		if (!isset($search_main_page)) {
			echo $pagination->render(2);
		}
		?>

	</div>
</div>