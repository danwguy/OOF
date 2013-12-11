<?php
/**
 * Created by JetBrains PhpStorm.
 * User: install
 * Date: 9/20/13
 * Time: 4:16 PM
 * To change this template use File | Settings | File Templates.
 */

class GamesController extends Controller {

    public function beforeAction() {}

    public function index() {
        $this->renderHeader = false;
        $games = Game::retrieve_objects("");
        $this->set('games', $games);
	    $this->load->view('games/index');
    }

    public function play($id) {
        $game = new Game($id);
        $this->set('game', $game);
        $this->renderHeader = false;
	    $this->load->view($game->play_page);
//        if($game->play_page) {
//            $this->override_view($game->play_page);
//        }
    }

    public function afterAction() {}

}