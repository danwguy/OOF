<?php


class HomeController extends Controller {

    public function beforeAction() {}

    public function index() {
        $live_and_active_where = " WHERE mode = ? AND status = ?";
        $params = array('live', 'active');
        $menu_items = Menu::retrieve_objects($live_and_active_where, $params);
        $content = Page::retrieve_object(
            " WHERE mode = ? AND status = ? AND linked_menu = ?",
            array('live', 'active', 1)
        );
        $themes = Theme::retrieve_objects($live_and_active_where, $params);
	    $vars = array(
		    'menu' => $menu_items,
		    'content' => $content,
		    'themes' => $themes
	    );
        echo $vars['non-existent'];
        $this->set($vars);
        $this->_template->render();
    }

    public function afterAction() {}


}
