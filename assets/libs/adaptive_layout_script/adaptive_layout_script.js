// ------------------------------------------------
// Функции для адаптивной верстки (breakpoint'ы).
//
// Этот скрипт должен подключаться ПЕРЕД файлами, где он используется - чтобы сначала установились переменные, а потом (в других файлах) можно было использовать значения из этих переменных.
// ------------------------------------------------

var win_w = window.innerWidth;
var win_h = window.innerHeight;
var bp_xl = 1200,
		bp_lg = 992,
		bp_md = 768,
		bp_sm = 480;
var media_xl = 4,
		media_lg = 3,
		media_md = 2,
		media_sm = 1,
		media_xs = 0;

var media = -1;
var lastMedia = -1;
var afterChangeMedia = false;

function set_media() {
	if (win_w > bp_xl) {
		media = media_xl;
	}
	else if ((win_w <= bp_xl) && (win_w > bp_lg)) {
		media = media_lg;
	}
	else if ((win_w <= bp_lg) && (win_w > bp_md)) {
		media = media_md;
	}
	else if ((win_w <= bp_md) && (win_w > bp_sm)) {
		media = media_sm;
	}
	else if (win_w <= bp_sm) {
		media = media_xs;
	}
}

$(window).resize(function () {
	win_w = window.innerWidth;
	win_h = window.innerHeight;
	set_media();
	if (lastMedia != media) {
		lastMedia = media;
		afterChangeMedia = true;
	} else if (afterChangeMedia == true) {
		afterChangeMedia = false;
	}
});

$(function () {
	$(window).resize();
})

/*
$(window).on('resize', function () {
	var media_text;
	switch (media) {
		case 0:
			media_text = 'xs';
			break;
		case 1:
			media_text = 'sm';
			break;
		case 2:
			media_text = 'md';
			break;
		case 3:
			media_text = 'lg';
			break;
		case 4:
			media_text = 'xl';
			break;
	}
	console.log('media = ' + media_text);
});
*/