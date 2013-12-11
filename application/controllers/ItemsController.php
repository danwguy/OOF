<?php


class ItemsController extends Controller {

    public function beforeAction() {}

    public function view($id = null, $name = null) {
        $this->set('title', $name . ' - My Todo List App');
        $this->set('todo', $this->Item->select($id));
	    $this->load->view('items/view');
    }

    public function viewAll() {
        $this->set('title', 'All Items - My Todo List App');
        $this->set('todo', $this->Item->selectAll());
	    $this->load->view('items/viewall');
    }

    public function add() {
        $todo = $_POST['todo'];
        $this->set('title', 'Success - My Todo List App');
        $this->set('todo', $this->Item->insert(array('item_name' => mysql_real_escape_string($todo))));
	    $this->load->view('items/add');
    }

    public function delete($id = null) {
        $this->set('title', 'Success - My Todo List App');
        $this->set('todo', $this->Item->remove(mysql_real_escape_string($id)));
	    $this->load->view('items/delete');
    }

    public function index() {
        $this->set('title', 'All Items - My Todo List App');
        $this->set('todo', $this->Item->selectAll());
	    $this->load->view('items/index');
    }

    public function afterAction() {}

}