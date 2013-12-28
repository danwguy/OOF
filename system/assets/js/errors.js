jsDebug = {
    expanded : false,
    startMinimized : null,
    elementSize : {},
    element : function() {
        return $('.debug');
    },
    miniElement : function() {
        return $('.error-small');
    },
    init : function() {
        this.element().before(jsDebug.miniElement());
        if(this.startMinimized) {
            this.minimize();
        } else {
            this.maximize();
        }
        this.elementSize.width = this.element().width();
        this.elementSize.height = this.element().height();
        this.addClicks();
    },
    minimize : function() {
        this.element().hide();
        this.miniElement().show();
    },
    maximize : function() {
        this.miniElement().hide();
        this.element().show();
    },
    minimizeSlide : function() {
        this.element().hide('slide', {direction: 'up'}, function() {
            jsDebug.miniElement().show('slide', {direction: 'left'})
        });
    },
    reveal : function() {
        if(this.element().width() != this.elementSize.width) {
            this.element()
                .width(this.elementSize.width)
                .height(this.elementSize.height)
                .css('left', 0);
        }
        this.element().show('slide', {direction : 'left'});
        this.miniElement().hide();
    },
    revealSlide : function() {
        this.element().show('slide', {direction: 'up'}, function() {
            jsDebug.miniElement().hide('slide', {direction: 'left'});
        });
    },
    miniSlide : function(show_hide) {
        if(show_hide == 'show') {
            this.miniElement().animate({
                left: '100px'
            })
        } else {
            this.miniElement().animate({
                left : '-101px'
            })
        }
    },
    switchToSmall : function() {
        this.element()
            .css({
                overflow: 'hidden'
            })
            .animate({
                height: '10px',
                width: '10px',
                left : '-' + (jsDebug.elementSize.width / 2) + 'px',
                top: 0
            }, 1000, function() {
                $(this).hide();
                jsDebug.miniElement().show('bounce', {direction: 'left'}, 750);
            });
    },
    remove : function() {
        this.element().hide();
        this.miniElement().hide();
    },
    addClicks : function() {
        var that = this;
        this.miniElement().on('click', '.cloud', function() {
            jsDebug.miniSlide((!that.expanded) ? 'show' : 'hide');
            that.expanded = !that.expanded;
        });
        this.miniElement().on('click', '.show-more', function() {
            jsDebug.reveal();
        });
        this.element().on('click', '.hide', function() {
            jsDebug.switchToSmall();
            that.expanded = !that.expanded;
        });
        this.miniElement().on('click', '.hide-all', function() {
            jsDebug.element().hide();
            jsDebug.miniElement().hide();
        });
    }
}