
<div><h2><strong><?php echo $product->name?></strong>
</div>

<div><h2>Price: $<?php echo $product->price?></h2>


<?php if (isset($tag)):?>

<h2>Tags:</h2>

<?php foreach ($tag as $tags):?>
<div class="tag">
<?php echo $tags->name?>
</div>
<?php endforeach?>
</div>
<?php endif?>