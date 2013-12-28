jsDebug = {
    expanded : false,
    startMinimized : null,
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
        this.element().show();
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
        this.miniElement().show('bounce', {direction: 'left'});
        this.element()
            .css({
                position : 'absolute',
                bottom : '0px',
                right : '0px',
                overflow : 'hidden'
            })
            .animate({height: '10px', width: '10px'}, 2500, function() {
                $(this).effect("transfer", {to: ".cloud"}, 5500, function() {
                    $(this).hide();
                })
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
            jsDebug.miniElement().show();
            jsDebug.miniSlide('hide');
            jsDebug.element().hide();
        });
        this.miniElement().on('click', '.hide-all', function() {
            jsDebug.element().hide();
            jsDebug.miniElement().hide();
        });
    }
}