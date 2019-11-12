<?php

class Cabinet {

	const ACTION_SHOW_COUNT = 1;
	const ACTION_SHOW_VISIBLE = 2;
	const ACTION_SHOW_HIDDEN = 3;

	const SEX_TYPES = array(
		0 => Model_User::NOT_SPECIFIED_PLACEHOLDER,
		1 => 'мужской',
		2 => 'женский'
	);

	static public function render_menu($menu, $header) { ?>
		<div class="aside-menu">
			<div class="h1 aside-menu__header"><?= $header ?></div>
			<menu class="aside-menu__inner">
				<ul class="aside-menu__item-list">
					<?php foreach ($menu as $v) { ?>
						<li class="aside-menu__link-wrap<?= Request::current()->url() == $v['url'] ? ' active' : ''; ?>">
							<a class="aside-menu__link" href="<?= $v['url'] ?>"><?= $v['title'] ?></a>
						</li>
					<?php } ?>
				</ul>
			</menu>
		</div>
	<?php }

}