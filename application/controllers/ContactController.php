<?php


class ContactController extends Controller {

    public $noModel = true;

    public function beforeAction() {}

    public function index() {
        $this->renderHeader = false;
        $this->noModel = true;
	    $this->load->view('contact/index');
    }

    public function afterAction() {}

}