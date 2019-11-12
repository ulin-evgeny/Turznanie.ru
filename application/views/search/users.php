<?php
foreach ($items as $user) {
	echo '<div><a class="black custom-elems__link custom-elems__link_type_underline-solid" href="' . $user->get_url() . '">' . $user->username . '</a></div>';
}