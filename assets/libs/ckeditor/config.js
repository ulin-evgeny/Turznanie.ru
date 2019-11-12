/**
 * @license Copyright (c) 2003-2018, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

CKEDITOR.editorConfig = function (config) {
	config.toolbarGroups = [
		{name: 'document', groups: ['document', 'mode', 'doctools']},
		{name: 'clipboard', groups: ['clipboard', 'undo']},
		{name: 'editing', groups: ['find', 'selection', 'spellchecker', 'editing']},
		{name: 'forms', groups: ['forms']},
		{name: 'basicstyles', groups: ['basicstyles', 'cleanup']},
		{name: 'paragraph', groups: ['indent', 'blocks', 'align', 'bidi', 'paragraph']},
		{name: 'links', groups: ['links']},
		{name: 'insert', groups: ['insert']},
		'/',
		{name: 'styles', groups: ['styles']},
		{name: 'colors', groups: ['colors']},
		{name: 'tools', groups: ['tools']},
		{name: 'others', groups: ['others']},
		{name: 'about', groups: ['about']}
	];

	config.contentsCss = [
		'/assets/css/main.css'
	];

	config.dialog_noConfirmCancel = true;

	config.removePlugins = 'resize,elementspath,magicline';
	config.removeButtons = 'Save,NewPage,Preview,Print,Source,Templates,PasteText,PasteFromWord,Find,Replace,Scayt,Form,Checkbox,Radio,TextField,Textarea,Select,Button,ImageButton,HiddenField,Indent,Outdent,BidiLtr,BidiRtl,Language,Anchor,Image,Flash,Table,HorizontalRule,PageBreak,Iframe,Styles,Format,Font,FontSize,BGColor,TextColor,Maximize,ShowBlocks,About,CreateDiv,Paste,Copy,Cut,Subscript,Superscript';

	config.linkShowAdvancedTab = false;
	config.linkShowTargetTab = false;

	config.extraPlugins = 'blockquote';
	config.extraPlugins = 'justify';

	// Это нужно, чтобы ckeditor отправлял текст таким же, как обычный textarea. Не менял <> на &gt;&lt;
	config.basicEntities = false;

	// Важно заметить, что с BR считать количество символов было бы проще. Да и обрабатывать результирующую строку. Но если сделать CKEDITOR.ENTER_BR, то CKEditor добавляет пустые пробелы после <br/>. Убрать их - целая проблема, ведь нужно знать, какие пробелы поставил пользователь, а какие - ckeditor.
	config.enterMode = CKEDITOR.ENTER_P;

	config.fillEmptyBlocks = false; // если не добавить ту опцию, то Enter (new line) будет равен двум символам

	// TODO: Если в пустом CKEditor написать один пробел и вызвать getData. Или если даже у textarea получить val(), то будет пустая строка. Такой баг. Я не знаю, как его исправить. Но он не сильно мешает.
};

CKEDITOR.on('instanceReady', function ($this) {
	var editor = $this.editor.container.$;
	var iframe = $(editor).find('iframe')[0];
	$(iframe).contents().find('html').addClass('ckeditor-html');
});


// https://stackoverflow.com/questions/12676023/ckeditor-link-dialog-removing-protocol
CKEDITOR.on('dialogDefinition', function (ev) {
	var dialogName = ev.data.name;
	var dialogDefinition = ev.data.definition;

	if (dialogName === 'link') {
		var infoTab = dialogDefinition.getContents('info');

		dialogDefinition.minHeight = 30;

		infoTab.get('linkType').style = 'display: none';
		infoTab.remove('protocol');

		var url = infoTab.get('url');
		url.onKeyUp = function () {
		};
		url.setup = function (data) {
			this.allowOnChange = false;
			if (data.url) {
				var value = '';
				if (data.url.protocol) {
					value += data.url.protocol;
				}
				if (data.url.url) {
					value += data.url.url;
				}
				this.setValue(value);
			}
			this.allowOnChange = true;
		};
		url.commit = function (data) {
			data.url = {protocol: '', url: this.getValue()};
		};
	}
});