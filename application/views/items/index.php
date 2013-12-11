<form action="../items/add" method="post">
    <input type="text" value="I have to..." onclick="this.value=''" name="todo"> <input type="submit" value="add">
</form>
<br/><br/>
<?php $number = 0; ?>

<?php foreach ($todo as $todoitem):?>
    <a class="big" href="../items/view/<?php echo $todoitem->id; ?>/<?php echo strtolower(str_replace(" ","-",$todoitem->item_name))?>">
    <span class="item">
    <?php echo ++$number; ?>
    <?php echo $todoitem->item_name; ?>
    </span>
    </a><br/>
<?php endforeach?>