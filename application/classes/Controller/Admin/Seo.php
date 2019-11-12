<?php

class Controller_Admin_Seo extends AdminController {

	public function action_index() {
		$editable_page = ORM::factory('Seo')->where('id', '=', $this->request->param('page_id'))->where('status', '=', Status::STATUS_VISIBLE_VALUE)->find();

		if (!$editable_page->loaded()) {
			return $this->go_home();
		}

		$editable_seo_page_data = Model_Seo::get_seo_data_by_page($editable_page);

		$catalog_seo_url = Model_Seo::get_alias_by_id(Model_Seo::ID_ADMIN_PANEL) . Model_Seo::get_alias_by_id(Model_Seo::ID_CATALOG_SEO);
		$sending_url = $catalog_seo_url . '/' . $editable_page->id;

		if ($_POST) {
			try {
				$editable_page = ORM::factory('Seo', $editable_seo_page_data['id']);
				$editable_page->title = $_POST['title'];
				$editable_page->description = $_POST['description'];
				$editable_page->keywords = $_POST['keywords'];
				$editable_page->h1 = $_POST['h1'];
				$editable_page->content = $_POST['content'];
				$editable_page->title_menu = $_POST['title_menu'];
				$editable_page->save();
			} catch (Exception $e) {
				return $this->render_ajax($e->getMessage(), Ajax::STATUS_UNSUCCESS);
			}

			return $this->render_ajax(new PageMessage(array(
				'text' => 'Изменения сохранены!',
				'btn_text' => 'К списку страниц',
				'btn_href' => $catalog_seo_url
			)));
		}

		return $this->render('admin/seo', array(
			'editable_seo_page_data' => $editable_seo_page_data,
			'sending_url' => $sending_url
		));
	}

}
