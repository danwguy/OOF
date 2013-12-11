<?php
/**
 * Created by JetBrains PhpStorm.
 * User: install
 * Date: 9/21/13
 * Time: 2:57 PM
 * To change this template use File | Settings | File Templates.
 */

class TipsTricksController extends Controller {

    public function beforeAction() {}

    public function index() {
        $posts = array();
        $languages = TipsTrick::getLanguages();
        if($languages) {
            foreach($languages as $language) {
                $posts[$language->name] = TipsTrick::retrieve_objects(
                    " WHERE mode = ? AND status = ? AND language = ?",
                    array('live', 'active', $language->id)
                );
            }
        }
	    $this->set(array('posts' => $posts, 'languages' => $languages));
//        $this->set('posts', $posts); //The way you should do it
//        $this->set('languages', $languages);
        $this->renderHeader = false;
	    $this->load->view('tips_tricks/index');
    }

    public function view($id) {
        $post = new TipsTrick($id);
        $this->renderHeader = false;
        $this->set('post', $post);
	    $this->load->view('tips_tricks/view');
    }



    public function afterAction() {}

}