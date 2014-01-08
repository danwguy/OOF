<?php

class HighlighterController extends Controller {

    //Test inline comment for parser

    /**
     * block comment in php for the parser
     */
    public function index() {
        $file_path = APP_PATH . 'controllers/HighlighterController.php';
        $lines = file($file_path);
        if($lines) {
            $str = '';
            foreach($lines as $line) {
                $str .= $line;
            }
        }
        $html = Loader::load('HTML');
        $highlighted = $html->highlight($str, 'php');
        $this->set('highlighted', $highlighted);
        $this->load->view('highlight/index');
    }

} 