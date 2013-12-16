var animQueue = {
    queue : [],
    timer : null,
    queue : [],
    animQueue : true,
    wrapFunction : function(fn, context, params) {
        return function() {
            fn.apply(context, params);
        }
    },
    deQueue : function() {
        if(this.queue.length) {
            if(!this.animQueue) {
                this.toggleQueue();
            } else {
                (this.queue.shift())();
            }
        } else {
            clearInterval(this.interValAmt);
            this.animQueue = this._defQueue;
            this.current = this._defCurrent;
        }
    },
    toggleQueue : function() {
        if(this.current % 2) {
            (this.queue.shift())()
            this.current +=1;
        } else {
            (this.queue.pop())();
            this.current +=1;
        }
    },
    _defQueue : true,
    _defCurrent : 0,
    fps : 13,
    slow : 2000,
    fast : 1000,
    animate : function(params, duration) {
        if(!params) {
            return this;
        } else {
            this.current = this._defCurrent;
            this.animQueue = this._defQueue;
            var time = (duration) ? duration : this.slow;
            var target;
            for(var index in params) {
                if(index == 'queue') {
                    if(params[index]){
                        this.animQueue = true;
                    } else {
                        this.animQueue = false;
                    }
                } else {
                    var current = parseFloat(this.getStyle(index));
                    if(current < params[index]) {
                        target = params[index] - current;
                        animDirection = '+';
                    } else {
                        target = current - params[index];
                        animDirection = '-';
                    }
                    totalMovement = (target / time) * this.fps;
                    animObj = {
                        type : index,
                        target : target,
                        move : totalMovement,
                        direction : animDirection,
                        duration : time
                    };
                    this.setAnimQueue(animObj);
                }
            }
        }
        this.setTheTimeout();
        return this;
    },
    setAnimQueue : function(obj) {
        var that = this;
        for(var i = 0, amt = (obj.duration / this.fps); i < amt; i++) {
            var fun = this.wrapFunction(that.doAnim, this, [obj.type, obj.move, obj.direction]);
            this.queue.push(fun);
        }
    },
    setTheTimeout : function() {
        var that = this;
        this.interValAmt = setInterval(function() {
            that.deQueue()
        }, that.fps);
    },
    doAnim : function(type, amount, direction) {
        var totalAmount = eval(parseFloat(this[type]()) + direction + amount);
        if(this.elem.style[this.toCamelCase(type)]) {
            this[type](totalAmount);
        } else {
            this[type](totalAmount)
        }
        return this;
    }
}