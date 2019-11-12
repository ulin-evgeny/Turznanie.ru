<?php

class Widget_Share {

	static public function render($params) { ?>
		<div class="custom-elems__h2">Поделиться</div>
		<div class="widget-share">
			<script type="text/javascript">
				document.write(VK.Share.button(false, {
					type: 'custom',
					text: '<img class="item-share-vk" src=\"https://vk.com/images/share_32.png\" width=\"22\" height=\"22\" />'
				}));
			</script>
			<a class="item-share-fb icon-facebook-rect" target="_blank" onclick="return !window.open(this.href, 'Facebook', 'width=640,height=300')" href="https://facebook.com/sharer/sharer.php?u=<?= Helper::get_site_url() . $params['url'] ?>"></a>
		</div>
	<?php }

}