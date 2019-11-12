<?php

class Widget_Breadcrumbs {

	static public function render($breadcrumbs) {
		$res = '';
		$i = 0;
		foreach ($breadcrumbs as $bc) {
			$res .= '<li class="breadcrumbs__link-wrap"><a class="black" href="' . $bc['url'] . '"><span>' . $bc['title'] . '</span></a>';
			$i++;
			if ($i != count($breadcrumbs)) {
				$res .= '<span class="breadcrumbs__divider"></span>';
			}
			$res .= '</li>';
		}
		$res = '<div class="clearfix"><ul class="breadcrumbs">' . $res . '</ul></div>';
		return $res;
	}

}
