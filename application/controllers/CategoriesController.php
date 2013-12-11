<?php


class CategoriesController extends Controller {

    public function beforeAction() {}

    public function view($categoryId =  null) {
        $this->Category->where('parent_id', $categoryId);
        $this->Category->showHasOne();
        $this->Category->showHasMany();
        $subcategories = $this->Category->search();
        $this->Category->id = $categoryId;
        $this->Category->showHasOne();
        $this->Category->showHasMany();
        $category = $this->Category->search();

        if(is_array($subcategories) && !empty($subcategories)) {
            $this->set('subcategories', $subcategories['Category']);
        } else {
            $this->set('subcategories', $subcategories);
        }
        if(is_array($category) && !empty($category)) {
            $this->set('category', array_shift($category['Category']));
            if(isset($category['Product'])) {
                $this->set('product', $category['Product']);
            }
        } else {
            $this->set('category', $category);
        }
	    $this->load->view('categories/view');
    }

    public function index() {
        $this->Category->orderBy('name', 'ASC');
        $this->Category->showHasMany();
        $this->Category->showHasOne();
        $this->Category->where('parent_id', '0');
        $categories = $this->Category->search();
        $this->set('categories', $categories);
	    $this->load->view('categories/index');
    }

    public function test() {
	    echo "<br />WE ARE TESTING STUFF:<br />";
	    $category = new Category(1);
        echo "<pre>";
        print_r($category);
        echo "</pre>";
    }

    public function afterAction() {}
}