<?php

    echo $html->doctype();
    $this->rendered_page = 'Home';
?>
<head>
    <title>Robert Mason - Home</title>
    <?php
        echo $html->includeJs(
            array(
                 'jquery',
                 'jquery-ui.min',
                 'page2.0',
                 'animator',
                 'simon',
                 'popup',
                 'prettify',
                 'run_prettify'
            )
        );
        echo $html->includeCss(
            array(
                 'base',
                 'pretty',
                 'sunburst'
            )
        );
        echo $html->setupJsVars();
    ?>
    <script type='text/javascript'>
        <?php
        if(isset($themes)) {
            echo "var themes = ".json_encode($themes).";";
        } else {
            echo "var themes = {};";
        }
        ?>
        $(document).ready(function () {
            themeController.holder().hide();
            $('.pull-arrow').click(function () {
                themeController.toggleSelector();
            });
            menu.liElements().click(function () {
                var requestPage = $(this).attr('page');
                menu.handleClick($(this));
                if (requestPage == 'resume') {
                    page.completeAnimationFn = resume.fillLevels;
                }
                page.toggleContent(page.getPage({data: { page: requestPage}, dataType: 'html'}));
            });
            if (page.pages.home.content === null) {
                page.setPageContent('home', $('.content').html());
            }
            $('#themes').change(function () {
                var theme = $(this).val();
                $.each(themes, function () {
                    console.log(this.css_class);
                    $('body').removeClass(this.css_class);
                });
                $('body').addClass(theme);
            })
        })
    </script>
</head>
<body class='city city-wall'>
    <div class='wrap'>
        <div class='logo'>
            <?php
            echo $html->img('img/admin_logo.png');
            ?>
        </div>
        <div class='search'>
            <input type='text' placeholder="Search here..." class='rounded-input'/>
        </div>
        <div class='smaller-menu'>
            <ul>
                <?php
                    if (isset($menu)) {
                        foreach ($menu as $item) {
                            echo "<li page='" . $item->link . "'";
                            echo ($this->rendered_page == $item->title)
                                ? 'class="selected ' . $item->link . '"'
                                : 'class="' . $item->link . '"';
                            echo ">" . $item->title . "</li>";
                        }
                    }
                ?>
            </ul>
        </div>
        <div class='content'>