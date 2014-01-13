
var simon = {
    /*Class constants here, these are the max/min time events for difficulty settings*/
    MIN_FLASH_DELAY : 100,
    MIN_FLASH_TIME : 50,
    MIN_TIMEOUT : 2000,
    DEFAULT_GAME_OVER_REASON : 'Bummer, you lost. I know you can do better... you should try again',
    WRONG_CLICK : 'You clicked on the wrong color',
    DELAY_OF_GAME : 'Sorry you took to long to answer, maximum answer time is ' + this.timeOut / 1000 + ' seconds',
    difficulty : null,
    baseSpeed : 1,
    level : 1,
    highestLevel : 1,
    sequence : [],
    levelDelay : 10,
    possibilities: [1, 2, 3, 4],
    levelSequence: [],
    timeOut : 20000,
    flashDelay : 600,
    flashTime : 400,
    flashTimer : null,
    valueTimer : null,
    clickTimer : null,
    flashClickTimer : null,
    redFlashTimer : null,
    blueFlashTimer : null,
    greenFlashTimer : null,
    yellowFlashTimer : null,
    clickNum : 0,
    lastColor: null,
    gameStarted: false,
    flashValues : {
        red : 1,
        blue : 2,
        yellow: 3,
        green : 4
    },
    lastClickedElement: null,
    gameOverReason: null,
    gameOverSubTitle: null,
    gameOver : {
        title: '<h3 class="end-game">Game Over</h3>',
        content: '<p class="game-over-reason">' + this.gameOverReason + '</p>'
    },
    lastFlashed : null,
    red : function() {
        return $('.red-game-piece');
    },
    blue : function() {
        return $('.blue-game-piece');
    },
    yellow : function() {
        return $('.yellow-game-piece');
    },
    green : function() {
        return $('.green-game-piece');
    },
    currentLevelEle : function() {
        return $('.current-level');
    },
    highestLevelEle : function() {
        return $('.highest-level');
    },
    getLastClicked : function() {
        if(this.lastClickedElement == null) {
            return '';
        }
        return this.lastClickedElement.children('.normal').attr('color');
    },
    getColorForClick : function() {
        return this.levelSequence[this.clickNum - 1];
    },
    getLastColor : function() {
        if(this.lastColor == null) {
            return '';
        }
        return this.lastColor;
    },
    moreClicksNeeded : function() {
         return (this.clickNum != this.levelSequence.length)
    },
    handleClick : function(ele) {
        this.flashColor(ele);
        if(this.gameStarted) {
            this.clickNum += 1;
            this.validateClick(ele);
        }
    },
    getFlashColor : function(num) {
        switch(num) {
            case 1:
                return 'red';
            case 2:
                return 'blue';
            case 3:
                return 'yellow';
            case 4:
                return 'green';
            default:
                return 'red'
        }
    },
    getDifficulty : function() {
        return $('#difficulty').val();
    },
    levelUp : function() {
        this.level += 1;
        this.currentLevelEle().html(this.level);
        this.clickNum = 0;
        if(this.level > this.highestLevel) {
            this.highestLevel = this.level;
            this.highestLevelEle().html(this.highestLevel);
        }
        this.difficulty = this.difficulty || this.getDifficulty();
        if(this.difficulty == 2) {
            if(this.flashDelay > this.MIN_FLASH_DELAY) {
                this.flashDelay -= 25;
            }
            if(this.flashTime > this.MIN_FLASH_TIME) {
                this.flashTime -= 17.5;
            }
            if(this.timeOut > this.MIN_TIMEOUT) {
                this.timeOut -= 900;
            }
        } else if(this.difficulty == 3) {
            if(this.flashDelay > this.MIN_FLASH_DELAY) {
                this.flashDelay -= 50;
            }
            if(this.flashTime > this.MIN_FLASH_TIME) {
                this.flashTime -= 35;
            }
            if(this.timeOut > this.MIN_TIMEOUT) {
                this.timeOut -= 1800;
            }
        }
        this.start();
    },
    flashColor : function(color) {
        if(typeof color != 'object') {
            color = this[color]();
        }
        color.children('.active').show();
        color.children('.normal').hide();
        this[color.attr('color') +'FlashTimer'] = setTimeout(function() {
            simon.removeFlash(color);
        }, this.flashTime);
    },
    removeFlash : function(color) {
        clearTimeout(this[color.attr('color') + 'FlashTimer']);
        color.children('.normal').show();
        color.children('.active').hide();
    },
    flashSequence : function() {
        var color,
            that = this;
        for(var i = 0; i < this.levelSequence.length; i++) {
            color = this.getFlashColor(this.levelSequence[i]);
            animQueue.queue.push(animQueue.wrapFunction(that.flashColor, this, [color]))
        }
        animQueue.interValAmt = setInterval(function() {
            animQueue.deQueue();
        }, that.flashDelay);
    },

    startClickTimer: function() {
        if(this.clickTimer != null) {
            clearTimeout(this.clickTimer);
            this.clickTimer = null;
        }
        this.clickTimer = setTimeout(function() {
            simon.endGame();
        }, this.timeOut);
    },
    getRandomTarget : function() {
        this.lastColor = Math.floor(Math.random() * this.possibilities.length) + 1;
        return this.lastColor;
    },
    getLevelSequence : function() {
        this.levelSequence.push(this.getRandomTarget());
    },
    validateClick : function(ele) {
        var clickedColor = ele.children('.normal').attr('color');
        var expectedColor = this.getFlashColor(this.levelSequence[this.clickNum - 1]);
        if(clickedColor == expectedColor) {
            clearTimeout(this.clickTimer);
            if(this.clickNum == this.levelSequence.length) {
                this.levelUp();
            } else {
                this.startClickTimer();
            }
        } else {
            this.gameOverReason = this.WRONG_CLICK;
            this.endGame();
        }
    },
    alertUser : function(msg) {
        popUp.addButton('retry');
        popUp.addButton('close');

        popUp.show({
            title: msg.title,
            subTitle: msg.subTitle,
            content: msg.content,
            theme: 'modern'
        });
        popUp.addEvent('retry', 'click', function() {
            popUp.hide();
            simon.start();
        });
        popUp.addEvent('close', 'click', function() {
            popUp.hide(true);
        })
    },
    start : function() {
        this.gameStarted = true;
        this.getLevelSequence();
        this.flashSequence();
    },
    getGameOverReason : function() {
        if(this.gameOverReason == this.WRONG_CLICK) {
            this.gameOverSubTitle = this.WRONG_CLICK;
            this.gameOverReason = ' You clicked on: ' +
                '' + simon.getLastClicked() + ', but you should have clicked on ' +
                '' + simon.getFlashColor(simon.getColorForClick());
        } else if(this.gameOverReason == this.DELAY_OF_GAME) {
            this.gameOverSubTitle = this.DELAY_OF_GAME;
            this.gameOverReason = ' You gotta click faster than that, maybe you should' +
                ' try an easier game difficulty setting';
        } else {
            this.gameOverSubTitle = "Sorry, but you lost";
            this.gameOverReason = this.DEFAULT_GAME_OVER_REASON;
        }
        return this.gameOverReason;
    },
    endGame : function() {
        this.gameOverReason = this.getGameOverReason();


        clearTimeout(this.clickTimer);
        this.clickTimer = null;
        clearTimeout(this.flashTimer);
        this.flashTimer = null;
        clearTimeout(this.valueTimer);
        this.clickNum = 0;
        this.valueTimer = null;
        this.gameStarted = false;
        this.levelSequence.length = 0;
        this.highestLevel = this.level;
        this.level = 1;

        this.alertUser({
            title: 'Game Over',
            subTitle: this.gameOverSubTitle,
            content: this.gameOverReason
        });
    }
//    wrongClick : 'Sorry you clicked on the wrong color. You clicked on: ' + simon.getLastClicked() + ', but you should have clicked on ' + this.getColorForClick()
};

//simon.WRONG_CLICK = 'Sorry you clicked on the wrong color.';
