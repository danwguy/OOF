
<div><h2><strong><?php echo $category->name; ?></strong>
</div>

<?php if (isset($subcategories) && !empty($subcategories)) { ?>
    <div><h2>Please select a sub-category</h2>
        <?php foreach ($subcategories as $subcategory) { ?>
            <div class="category">

                <?php
                    echo $html->link(
                        $subcategory->name, 'categories/view/' . $subcategory->id . '/' . $subcategory->name
                    );
                ?>

            </div>
        <?php
        } //end foreach
        ?>
    </div>
<?php
} else {
    ?>
    <div class='category'>
        <a href=''>Sorry no subcategories exist for category: <?php echo $category->name; ?></a>
    </div>
<?php
}
?>
<?php if (isset($product) && !empty($product)) { ?>
    <div><h2>Please select a product</h2>
        <?php foreach ($product as $product_obj) { ?>
            <div class="category">

                <?php echo $html->link(
                    $product_obj->name, 'products/view/' . $product_obj->id . '/' . $product_obj->name
                ) ?>

            </div>
        <?php
        } //endforeach
        ?>
    </div>
<?php
} //endif
?>