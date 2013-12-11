<?php
    echo $html->link_tag('css/simon.css');
 ?>

<!--<link rel='stylesheet' href='assets/css/simon.css' />-->
<script type='text/javascript'>
    $(document).ready(function() {
        $('.psymon-start').click(function() {
            simon.start();
        });
        $('.psymon-stop').click(function() {
            simon.endGame();
        });
        $('.clickable').click(function(){
            simon.lastClickedElement = $(this);
            simon.handleClick($(this));
        });
        $('.active').hide();
    });
</script>
<div class='game-stats'>
    <h3>Current Stats</h3>
    <div class='highest'>
        <p class='highest-level-title'>Highest Level:</p>
        <p class='highest-level'>1</p>
    </div>
    <div class='current'>
        <p class='current-level-title'>Current Level:</p>
        <p class='current-level'>1</p>
    </div>
</div>
<div class='simon-gameboard'>
    <div class='green-game-piece clickable'>
        <?php
        echo $html->img(array('src' => 'game_images/play_psymon_green.png', 'class' => 'green-piece normal absolute', 'color' => 'green'));
            echo $html->img(array('src' => 'game_images/play_psymon_green_active.png', 'class' => 'green-piece active absolute', 'color' => 'green'));
        ?>
<!--        <img class='green-piece normal absolute' color='green' src='assets/images/game_images/play_psymon_green.png' />-->
<!--        <img class='green-piece active absolute' color='green' src='assets/images/game_images/play_psymon_green_active.png' />-->

    </div>
    <div class='red-game-piece clickable'>
        <?php
            echo $html->img(array('src' => 'game_images/play_psymon_red.png', 'class' => 'red-piece normal absolute', 'color' => 'red'));
            echo $html->img(array('src' => 'game_images/play_psymon_red_active.png', 'class' => 'red-piece active absolute', 'color' => 'red'));
        ?>
<!--        <img class='red-piece normal absolute' color='red' src='assets/images/game_images/play_psymon_red.png' />-->
<!--        <img class='red-piece active absolute' color='red' src='assets/images/game_images/play_psymon_red_active.png' />-->

    </div>
    <div class='blue-game-piece clickable'>
        <?php
            echo $html->img(array('src' => 'game_images/play_psymon_blue.png', 'class' => 'blue-piece normal absolute', 'color' => 'blue'));
            echo $html->img(array('src' => 'game_images/play_psymon_blue_active.png', 'class' => 'blue-piece active absolute', 'color' => 'blue'));
        ?>
<!--        <img class='blue-piece normal absolute' color='blue' src='assets/images/game_images/play_psymon_blue.png' />-->
<!--        <img class='blue-piece active absolute' color='blue' src='assets/images/game_images/play_psymon_blue_active.png' />-->
    </div>
    <div class='yellow-game-piece clickable'>
        <?php
            echo $html->img(array('src' => 'game_images/play_psymon_yellow.png', 'class' => 'yellow-piece normal absolute', 'color' => 'yellow'));
            echo $html->img(array('src' => 'game_images/play_psymon_yellow_active.png', 'class' => 'yellow-piece active absolute', 'color' => 'yellow'));
        ?>
<!--        <img class='yellow-piece normal absolute' color='yellow' src='assets/images/game_images/play_psymon_yellow.png' />-->
<!--        <img class='yellow-piece active absolute' color='yellow' src='assets/images/game_images/play_psymon_yellow_active.png' />-->
    </div>
    <div class='center'>
        <button class='psymon-start'>START</button>
        <button class='psymon-stop'>STOP</button>
    </div>
</div>
<div class='difficulty-wrapper'>
    <label for='difficulty'>
        Difficulty:
        <select name='difficulty' id='difficulty' class='styled-select'>
            <option value='1'>Yawn, that was easy!(Easy)</option>
            <option value='2'>Well now that was tough(medium)</option>
            <option value='3'>Why yes, I am insane(hard)</option>
        </select>
    </label>
    <input type='text' class='clear-input' />
</div>