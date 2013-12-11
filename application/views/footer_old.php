<?php
    $list = array('yellow', 'brown' => array('poop', 'tan', 'diarrhea'), 'gold', 'blue');
    $attr = array('class' => 'my-list', 'id' => 'list-id');
    echo $html->ul($list, $attr);
    echo $html->br(3);
    echo $html->nbs(4);
?>
</body>
</html>
