<?php

    echo "<script type='text/javascript' src='system/assets/js/jquery.js'></script>
          <script type='text/javascript' src='system/assets/js/prettify.js'></script>
          <script type='text/javascript' src='system/assets/js/errors.js'></script>
          <link rel='stylesheet' href='system/assets/css/errors.css' />";
?>
<script type="text/javascript">
    $(document).ready(function() {
        prettyPrint();
        $('.error-line')
            .removeClass('error-line')
            .parent('li')
            .addClass('error-line');
    })
</script>
<div class="animated error-small hinge bounceInDown">
    <div class="cloud animated hinge bounceOut">
        <div id="background"></div>
        <div id="icon"><img src="system/assets/img/error.png" alt="icon" /></div>
        <div id="title">A PHP Error was encountered</div>
        <div id="text"><?php echo $message; ?></div>
        <div id="controls">
            <span class="show-more">Show All</span>
            <span class="hide-all"> Hide All</span>
        </div>
    </div>
</div>
<div class="php-error-wrapper">
    <div class="error-title">
        <h4>A PHP Error was encountered</h4>
        <span class="reason"><?php echo $message; ?></span>
        <span class="hide">Hide debug</span>
    </div>
    <div class="error-trace">
        <?php echo $trace; ?>
    </div>
    <div class="php-file">
        <?php  echo $file_contents; ?>
    </div>





</div>