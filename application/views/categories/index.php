<div><h2>Please select a category</h2>

<?php foreach ($categories['Category'] as $category):?>

<div class="category">

<?php echo $html->link($category->name,'categories/view/'.$category->id.'/'.$category->name)?>

</div>
<?php endforeach?>
</div>