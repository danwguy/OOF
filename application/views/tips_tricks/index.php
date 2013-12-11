<script type="text/javascript">




    $.fn.infiniteCarousel = function () {

        function repeat(str, num) {
            console.log('THE FUCKING NUMBER IS:');
            console.log(num);
            console.log('AND THE FUCKING STRING IS:');
            console.log(str);
            return new Array( num + 1 ).join( str );
        }

        return this.each(function () {
            var $wrapper = $('> div', this).css('overflow', 'hidden'), // .list
                $slider = $wrapper.find('> ul'), // .item-holder
                $items = $slider.find('> li'), // .items
                $single = $items.filter(':first'),

                singleWidth = $single.outerWidth(),
                visible = Math.ceil($wrapper.innerWidth() / singleWidth), // note: doesn't include padding or border
                currentPage = 1,
                pages = Math.ceil($items.length / visible);


            // 1. Pad so that 'visible' number will always be seen, otherwise create empty items
            if (($items.length % visible) != 0) {
                $slider.append(repeat('<li class="empty" />', visible - ($items.length % visible)));
                $items = $slider.find('> li');
            }

            // 2. Top and tail the list with 'visible' number of items, top has the last section, and tail has the first
            $items.filter(':first').before($items.slice(- visible).clone().addClass('cloned'));
            $items.filter(':last').after($items.slice(0, visible).clone().addClass('cloned'));
            $items = $slider.find('> li'); // reselect

            // 3. Set the left position to the first 'real' item
            $wrapper.scrollLeft(singleWidth * visible);

            // 4. paging function
            function gotoPage(page) {
                var dir = page < currentPage ? -1 : 1,
                    n = Math.abs(currentPage - page),
                    left = singleWidth * dir * visible * n;

                $wrapper.filter(':not(:animated)').animate({
                    scrollLeft : '+=' + left
                }, 500, function () {
                    if (page == 0) {
                        $wrapper.scrollLeft(singleWidth * visible * pages);
                        page = pages;
                    } else if (page > pages) {
                        $wrapper.scrollLeft(singleWidth * visible);
                        // reset back to start position
                        page = 1;
                    }

                    currentPage = page;
                });

                return false;
            }

            $wrapper.after('<a class="arrow back">&lt;</a><a class="arrow forward">&gt;</a>');

            // 5. Bind to the forward and back buttons
            $('a.back', this).click(function () {
                return gotoPage(currentPage - 1);
            });

            $('a.forward', this).click(function () {
                return gotoPage(currentPage + 1);
            });

            // create a public interface to move to a specific page
            $(this).bind('goto', function (event, page) {
                gotoPage(page);
            });
        });
    };

    $(document).ready(function () {
        $('.carousel').first().infiniteCarousel();
        $('.carousel').eq(1).infiniteCarousel();
        $('.carousel').last().infiniteCarousel();
    });

    $(document).ready(function () {
//        prettyPrint();
        $('.item').click(function () {
            var itemID = $(this).attr('item_id');
            page.toggleContent(page.getPage({data: { page: 'tips_tricks/view/' + itemID, post_id: itemID}, dataType: 'html'}));
        })
    })
</script>
<div class='list'>
    <?php
        foreach($languages as $language) {
            $i = 1;
            echo "<div class='item-holder carousel'>";
            echo "<div class='wrapper'>";
            echo "<ul>";
            foreach($posts[$language->name] as $post) {
                echo "<li>";
                echo "<div class='item paginate' page='";
                echo (ceil($i / 3) == 0) ? 1 : ceil($i/3);
                echo "' item_id='".$post->id."'>";
                echo "<div class='language-identifier ".$post->category->name."'>".$post->category->name."</div>";
                echo "<div class='item-title' language='".$post->category->name."' post_id='".$post->id."'>".substr($post->title, 0, 55)."&hellip;</div>";
                echo "<div class='item-preview'>".$html->clean(substr($post->content, 0, 200))."&hellip;</p></div>";
                echo "</div>";
                echo "</li>";
                $i++;
            }
            echo "</ul>";
            echo "</div>";
            echo "</div>";
        }
//        echo "HERE IS THE LANGUAGES:<br />";
//        echo "<pre>";
//        print_r($languages);
//        echo "</pre>";
//        echo "<br />AND HERE IS THE POSTS:<br />";
//        echo "<pre>";
//        print_r($posts);
//        echo "</pre>";

    ?>
</div>