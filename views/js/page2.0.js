var menu = {
    home: function () {
        return $('.home');
    },
    resume: function () {
        return $('.resume');
    },
    games: function () {
        return $('.games');
    },
    tipsAndTricks: function () {
        return $('.tips_and_tricks');
    },
    contact: function () {
        return $('.contact');
    },
    ulElement: function () {
        return $('.smaller-menu ul');
    },
    liElements: function () {
        return $('.smaller-menu ul').find('li');
    },
    handleClick: function (item) {
        this.ulElement().children('li').each(function () {
            $(this).removeClass('selected');
        });
        item.addClass('selected');
    }

};

var themeController = {
    CLOSED : 920,
    OPEN : 1220,
    HIDDEN : 800,
    container : function() {
        return $('.theme-container');
    },
    arrow : function() {
        return $('.pull-arrow');
    },
    holder : function() {
        return $('.theme-holder');
    },
    showSelector : function() {
        /*THIS WORKS!!!!!!!*/
        var that = this;
        this.holder().show(
            'slide',
            {
                direction: 'left',
                easing: 'easeInExpo'
            },
            500,
            function() {
                that.container().animate({
                    'left' : that.OPEN
                }, 1500, 'easeOutBounce', function() {
                    $('.pull-arrow').html("<p class='pull-arrow-p' title='Theme Chooser'><</p>");
                })
            }
        )

        //testing a new way
//        var that = this;
//        this.container().animate({
//            width: 350
//        }, 500, 'easeOutBounce', function() {
//            that.arrow().html('<-');
//        })
//        this.holder().show();
    },
    hideSelector : function() {
        /*THIS WORKS!!!!!!!!!!!!*/
        var that = this;
        this.container().animate({
            'left' : that.CLOSED
        }, 1500, 'easeInExpo', function() {
            $('.pull-arrow').html("<p class='pull-arrow-p' title='Theme Chooser'>></p>");
            that.holder().hide('slide', {direction: 'left'}, 500);
        });

        //testing a new way
//        var that = this;
//        this.holder().hide();
//        this.container().animate({
//            width: 0
//        }, 1500, 'easeOutBounce', function() {
//            that.arrow().html('->');
//        })

    },
    toggleSelector : function() {
//        if(this.container().width() == 0) {
//            this.showSelector();
//        } else {
//            this.hideSelector();
//        }
        if(this.container().position().left == this.CLOSED) {
            this.showSelector();
        } else {
            this.hideSelector();
        }
    }
}

var resume = {
    currentPage: null,
    elementsToFill: [],
    pageListElement : function() {
        return $('.page-id');
    },
    previous : function() {
        return $('.prev');
    },
    next : function() {
        return $('.next');
    },
    pages: function () {
        return $('.pagination');
    },
    inners: function () {
        return $('.skills-inner');
    },
    header: function () {
        return $('.resume-header');
    },
    fill: function (ele) {
        var height = $(ele).attr('fill');
        $(ele).animate({
            'height': height + '%'
        });
    },
    more: function (i) {
        return i <= this.inners().length;
    },
    fillLevels: function () {
        var that = this;
        for (var i = 0, len = resume.inners().length; i < len; i++) {
            if (resume.more(i)) {
                var level = $(resume.inners()[i]).attr('fill');
                $(resume.inners()[i]).animate({
                    'height': level + '%'
                })
            }
        }
    },
    findPage: function (page) {
        return $('.pagination[page="' + page + '"]');
    },
    showPage: function (page) {
        this.pages().each(function () {
            $(this).hide();
        });
        this.currentPage = page;
        if(page > 1) {
            this.previous().removeClass('disabled');
            this.next().addClass('disabled');
        } else {
            this.previous().addClass('disabled');
            this.next().removeClass('disabled');
        }
        this.pageListElement().html('Page ' + page + ' of 2');
        $('.pagination[page="' + resume.currentPage + '"]').show();
    }
};

var page = {
    reply: null,
    animEffect: null,
    animDir: null,
    completeAnimationFn: null,
    pages: {
        home: {
            content: null
        },
        resume: {
            content: {
                page1: null,
                page2: null
            }
        },
        games: {
            content: null
        },
        tipsAndTricks: {
            content: null
        },
        contact: {
            content: null
        }
    },
    newHeight: '200px',
    effects: [
        'blind',
        'clip',
        'drop',
        'fold',
        'slide'
    ],
    directions: [
        'up',
        'down',
        'left',
        'right'
    ],
    contentDiv: function () {
        return $('.content');
    },
    themeDiv : function() {
        return $('.theme-holder');
    },
    getPage: function (obj) {
        var possible = this.getPageContent(obj.data.page);
        if (possible) {
            return possible;
        }
        var url = (typeof obj.url != 'undefined') ? obj.url : 'controller/controller.php',
            that = this;
        $.ajax({
            async: false,
            type: "POST",
            url: url,
            data: obj.data,
            dataType: (typeof obj.dataType != 'undefined') ? obj.dataType : 'json',
            success: function (msg) {
                that.reply = msg;
            }
        });
        if(typeof this.pages[obj.data.page] != 'undefined') {
            this.pages[obj.data.page].content = this.reply;
        }
        return this.reply;
    },
    getRandomEffect: function () {
        return this.effects[Math.floor(Math.random() * this.effects.length)];
    },
    getRandomDir: function () {
        return this.directions[Math.floor(Math.random() * this.directions.length)];
    },
    toggleContent: function (content) {
        var that = this;
        this.animEffect = this.getRandomEffect();
        this.animDir = this.getRandomDir();
//        themeController.container().hide(
//            'slide',
//            {
//                direction: 'left',
//                easing: 'easeInOutExpo'
//            }
//        );
        themeController.container().animate({
            left: themeController.HIDDEN
        }, 500, 'easeOutBounce').hide();
        this.contentDiv()
            .hide(that.animEffect, {direction: that.animDir}, 750, function () {
                that.fillContent(content);
                that.showDiv();
            });
    },
    fillContent: function (content) {
        this.contentDiv().html(content);
    },
    showDiv: function () {
        var that = this;
        this.animEffect = this.getRandomEffect();
        this.animDir = this.getRandomDir();
//        this.contentDiv().css({ height : '2387px'});
        this.contentDiv().show(that.animEffect, {direction: that.animDir}, 750, function () {

            if (that.completeAnimationFn) {
                that.completeAnimationFn();
                that.completeAnimationFn = null;
            }
//            themeController.container().show(
//                'slide',
//                {
//                    direction: 'left',
//                    easing: 'easeOutBounce'
//                },
//                1500
//            )
            themeController.container()
                .show()
                .animate({
                left : themeController.CLOSED
            }, 1500, 'easeOutBounce')
//            that.themeDiv().show('bounce', {direction:'left'});
        });
    },
    setPageContent: function (what, content) {
        this.pages[what].content = content;
    },
    getPageContent: function (what) {
        if(typeof this.pages[what] != 'undefined') {
            if (this.pages[what].content === null) {
                return false;
            } else if (typeof this.pages[what].content == 'string') {
                return this.pages[what].content;
            } else {
                if (typeof this.pages[what].content == 'object') {
                    return
                    (this.pages[what].content['page-' + this.currentPage] === null)
                        ? false
                        : this.pages[what].content['page-' + this.currentPage];
                }
            }
        } else {
            return false;
        }
//        return ((typeof this.pages[what].content != 'undefined') && this.pages[what].content !== null)
//            ? this.pages[what].content
//            : false;
    }
};