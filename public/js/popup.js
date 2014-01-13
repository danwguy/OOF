var popUp = {
	logErrors:     false,
	failMessage:   false,
	failTitle:     'An Error Occured',
	addClick:      true,
	contentOptions:{},
	content:       null,
	title:         null,
    subTitle: null,
	clickFunctions : false,
    activeTheme : null,
    defaultTheme : 'apple',
    showBothButtons : true,
    buttons : {},
    buttonsHTML : null,
    bodyHTML : null,
    themes : ['apple', 'modern', 'transparent', 'clean', 'glass'],
    styleLoaded : false,
    setTheme : function(theme) {
        this.activeTheme = theme;
    },
    getHTML : function(buttons) {
        if(!this.bodyHTML) {
            this.bodyHTML = "<div class='cover'>" +
                            "<div class='modal " + this.activeTheme + "'>" +
                               "<div class='inner-modal'>" +
                                    "<div class='title'>" + this.title + "</div>" +
                                    "<div class='sub-title'>" + this.subTitle + "</div>" +
                                    "<div class='game-info'>" +
                                        "<p>" + this.content + "</p>" +
                                    "</div>" +
                                "</div>" +
                                "<div class='buttons'>";
            if(typeof buttons != 'undefined') {
                for(var i = 0, len = buttons.length; i < len; i++) {
                    this.bodyHTML += "<div class='game-button'><button>" + buttons[i] + "</button></div>";
                }
            }  else {
                this.bodyHTML += this.getButtons();
            }
             this.bodyHTML += "</div>" +
                     "<div class='modal-shine'></div>" +
                     "</div>" +
                     "</div>";
        }
        return this.bodyHTML;
    },
    clear : function() {
        this.bodyHTML = null;
        this.activeTheme = null;
        this.buttons.length = 0;
        this.buttonsHTML = null;
    },
    toType : function(obj) {
        return ({}).toString.call(obj).match(/\s([a-zA-Z]+)/)[1].toLowerCase();
    },
    addButton : function(button) {
        this.buttons[button] = '<div class="game-button" name="' + button + '"><button>' + button + '</button></div>'
//        this.buttons.push({
//            name : button,
//            element: '<div class="game-button"><button>' + button + '</button></div>'
//        });
    },
    getButtons : function() {
        if(!this.buttonsHTML) {
            this.buttonsHTML = '';
            for(var index in this.buttons) {
                this.buttonsHTML += this.buttons[index];
            }
//            for(var i = 0, len = this.buttons.length; i < len; i++) {
//                this.buttonsHTML += this.buttons[i].element;
//            }
            return this.buttonsHTML;
        } else {
            return this.buttonsHTML;
        }
    },
    showThemed : function() {
        if(!this.styleLoaded) {
            $('head').append('<link rel="stylesheet" href="system/assets/css/popup.css" />');
            this.styleLoaded = true;
        }
        var that = this;
        $('body').append(that.getHTML());
        $('.cover').click(function() {
            that.hide();
        })

    },
    addEvent : function(button, event, callback) {
        if(typeof this.buttons[button] != 'undefined') {
            var that = this;
            $('div [name="' + button + '"]').on(event, 'button',  callback);
        }
    },
	add_styles:    function (key, value) {
		var modalBox = $('.paulund_modal_box');
		if (key !== undefined) {
			modalBox.css({
				key:value
			});
		}
		modalBox.css({
			'position':             'fixed',
			'left':                 '30%',
			'top':                  '10%',
			'height':               '600px',
			'width':                '800px',
			'padding':              '10px',
			'border':               '1px solid #fff',
			'box-shadow':           '0px 2px 7px #292929',
			'font-family':          'Arial',
			'-moz-box-shadow':      '0px 2px 7px #292929',
			'-webkit-box-shadow':   '0px 2px 7px #292929',
			'border-radius':        '6px',
			'-moz-border-radius':   '6px',
			'-webkit-border-radius':'6px',
			'z-index':              '100',
			'background':           '#ccc'
		});
		$('.paulund_modal_close').css({
			'position':  'relative',
			'top':       '-20px',
			'left':      '10px',
			'float':     'right',
			'display':   'block',
			'height':    '20px',
			'width':     '20px',
			'background':'url(images/close_small.png) no-repeat'
		});
		/*Block page overlay*/
		var pageHeight = $(document).height();
		var pageWidth = $(window).width();

		$('.paulund_block_page').css({
			'position':        'fixed',
			'top':             '0',
			'left':            '0',
			'background-color':'rgba(0,0,0,0.6)',
			'height':          pageHeight,
			'width':           pageWidth,
			'z-index':         '10',
			'font-face':       'Arial'
		});
		$('.paulund_inner_modal_box').css({
			'background-color':     '#fff',
			'position':             'relative',
			'height':               '550px',
			'width':                '775px',
			'padding':              '5px',
			'margin':               '8px',
			'border-radius':        '6px',
			'-moz-border-radius':   '6px',
			'-webkit-border-radius':'6px',
			'font-face':            'Arial',
			'color':                'black',
			'overflow':             'auto'
		});
		$('.p_header').css({
			'text-align':'center',
			'text-decoration':'underline'
		})
	},
	add_block_page:function () {
		var block_page = $('<div class="paulund_block_page"></div>')
		$(block_page).appendTo('body');
	},
	add_popup_box: function (title, description) {
		var pop_up = $('<div class="paulund_modal_box"><a href="" class="paulund_modal_close"></a><div class="paulund_inner_modal_box"><div class="p_header">' + title + '</div><p class="p_body">' + description + '</p></div></div>');
		$(pop_up).appendTo('.paulund_block_page');

		$('.paulund_modal_close').click(function (e) {
			e.preventDefault();
			$(this).parent().fadeOut().remove();
			$('.paulund_block_page').fadeOut().remove();
			if(typeof popUp.onClose != 'undefined') {
				popUp.onClose();
				delete popUp.onClose;
			}
		});
	},
    themed : function(opts) {
        console.log(opts);
        if(opts.theme) {
            console.log('theme was passed so setting active theme to passed theme')
            this.activeTheme = opts.theme;
        } else if(!this.activeTheme) {
            this.activeTheme = this.defaultTheme;
        }
        this.showThemed();
    },
	show:          function (opts) {
		for (var index in opts) {
			this[index] = opts[index];
		}
        if(typeof opts.type != 'undefined') {
            this[opts.type](opts);
        } else {
            this.themed(opts);
        }
		if(this.clickFunctions) {
			this.clickFunctions();
			this.clickFunctions = false;
		}
	},
    mini : function(opts) {
        miniPopUp.show(opts);
    },
    large : function(opts) {
        this.add_block_page();
        var title = opts.title || this.title;
        var content = opts.content || this.content;
        this.add_popup_box(title, content);
        this.add_styles();
    },
	hide:          function (wipe) {
		$('.paulund_modal_box').fadeOut().remove();
		$('.paulund_block_page').fadeOut().remove();
        $('.cover').fadeOut().remove();
        $('.modal').fadeOut().remove();
        if(typeof wipe != 'undefined' && wipe) {
            this.clear();
        }
	},
	findElements:  function () {
		return $('.paulund_inner_modal_box').find('input, select');
	},
	makeMessage:   function (msg) {
		var output = '';
		if (msg != undefined) {
			if (typeof msg == 'string') {
				output = msg;
			} else {
				for (var index in msg) {
					output += index + ' Failed Because ' + msg[index] + '<br />';
				}
			}
		} else {
			output = 'NONE';
		}
		return output;
	},
	logError:      function (content) {
		var loggerURL = 'controllers/javascript_logger.php',
			params = '?description=' + encodeURIComponent(content) +
				'&page=' + encodeURIComponent(document.location.href) +
				'&log_to=manager_script_creator';
		new Image().src = loggerURL + params;
	},
	getPage : function(obj) {
		var data = (typeof obj.data != 'undefined') ? obj.data : '',
			dataType = (typeof obj.dataType != 'undefined') ? obj.dataType : 'html',
            reply;
		$.ajax({
			async: false,
			type: "POST",
			url: obj.page,
			data : data,
			dataType : dataType,
			success: function(msg) {
				reply = msg;
			}
		})
		return reply;
	},
	getForm:       function (options) {
		var form = $("<form action='" + options.url + "' method='post'></form>");

		for (var index in options) {
			if (index != 'url') {
				form.append("<input type='" + options[index].type + "' name='" + index + "' value='" + options[index].value + "' />");
			}
		}
		return form;
	}
};

var miniPopUp = {
    add_styles:    function (key, value) {
        var modalBox = $('.mini-popup');
        if (key !== undefined) {
            modalBox.css({
                key:value
            });
        }
        modalBox.css({
            'position':             'absolute',
            'height':               '153px',
            'width':                '390px',
            'padding':              '10px',
            'font-family':          'Custom',
            'background':           'url(assets/img/mini_popup.png)',
            'z-index':              9999999999
        });
        $('.mini-popup-close').css({
            'position':  'relative',
            'top':       '-20px',
            'left':      '10px',
            'float':     'right',
            'display':   'block',
            'height':    '20px',
            'width':     '20px',
            'background':'url(assets/images/close_small.png) no-repeat'
        });
        $('.p_header').css({
            'text-align':'center',
            'text-decoration':'underline'
        })
    },
    add_popup_box: function (title, description) {
        var pop_up = $('<div class="mini-popup"><a href="" class="mini-popup-close"></a><div class="p_header">' + title + '</div><p class="p_body">' + description + '</p></div>');
        $(pop_up).appendTo('body');

        $('.mini-popup-close').click(function (e) {
            e.preventDefault();
            $(this).parent().fadeOut().remove();
            if(typeof popUp.onClose != 'undefined') {
                popUp.onClose();
                delete popUp.onClose;
            }
        });
    },
    show:          function (opts) {
        for (var index in opts) {
            this[index] = opts[index];
        }
        this.add_popup_box(this.title, this.content);
        this.add_styles();
        if (this.addClick) {
            this.addModalClick();
        }
        if(this.clickFunctions) {
            this.clickFunctions();
            this.clickFunctions = false;
        }
    },
    hide:          function () {
        $('.mini-popup').fadeOut().remove();
    },
    addModalClick: function () {
        var that = this,
            elems = $('.mini-popup').find('input, select'),
            postData = {};
        $(".submit").click(function () {
            elems.each(function() {
                postData[$(this).attr('name')] = $(this).val();
            });
            postData.url = 'admin/controller/controller.php';
            var content = page.getPage(postData);
            admin.add(content);
            admin.toggle();
//            var market = $("#market").val();
//            var product = $("#product").val();
//            var widgetSet = 4;
//            var campaign = $("#campaign").val();
//            var user = page.user;
//            var url = document.location.href;
//            var form = that.getForm({
//                market:   market,
//                campaign: campaign,
//                widgetSet:widgetSet,
//                user:     user,
//                product:  product,
//                url:      url,
//                intent:   'view_script'
//            });
//            form.submit();
        });
    },
    getForm:       function (options) {
        var form = $("<form action='" + options.url + "' method='post'></form>");

        for (var index in options) {
            if (index != 'url') {
                form.append("<input type='hidden' name='" + index + "' value='" + options[index] + "' />");
            }
        }
        return form;
    }
};