<?php


class ProductsController extends Controller {

    public function beforeAction() {}

    public function view($id = null) {
        $this->Product->id = $id;
        $this->Product->showHasOne();
        $this->Product->showHMABTM();
        $product = $this->Product->search();
        if(is_array($product) && !empty($product)) {
            $this->set('product', array_shift($product['Product']));
            if(isset($product['Tag']) && !empty($product['Tag'])) {
                $this->set('tag', $product['Tag']);
            }
        } else {
            $this->set('product', $product);
        }
		$this->load->view('products/view');

    }

    public function afterAction(){}

}