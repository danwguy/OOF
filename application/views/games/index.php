<script type='text/javascript'>
    $(document).ready(function() {
        $('.game-list-button').click(function() {
            var gameId = $(this).attr('game_id');
            page.toggleContent(page.getPage({data : { page : 'games/play/' + gameId}, dataType : 'html'}));
        })

    })
</script>
<div clas='game-holder'>
    <?php
        if(isset($games)) {
            $i = 0;
            foreach($games as $game) {

    ?>
    <div class='game paginate' page='<?php echo ($i == 0) ? 1 : $i; ?>'>
        <div class='game-title'>
            <h1><?php echo $game->name; ?></h1>
        </div>
        <div class='game-description'>
            <?php
                echo $game->description;
            ?>
        </div>
        <div class='game-image'>
            <?php
                $img_array = array('src' => 'img/game_images/'.$game->image, 'class' => 'game-list-image');
                echo $html->img($img_array);
            ?>
        </div>
        <div class='game-list-controls'>
            <button class='game-list-button' game_id='<?php echo $game->id; ?>'>Play</button>
        </div>
    </div>
    <?php
        }
        }
    ?>
</div>