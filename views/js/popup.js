var popUp = {
	logErrors:     false,
	failMessage:   false,
	failTitle:     'An Error Occured',
	addClick:      true,
	contentOptions:{},
	content:       null,
	title:         null,
	clickFunctions : false,
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
	show:          function (opts) {
		for (var index in opts) {
			this[index] = opts[index];
		}
		if (!this.content) {
			this.contentOptions = this.type[opts.getOptions]();
			this.content = this.contentOptions.content;
			this.title = this.contentOptions.title;
		}
		this.add_block_page();
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
		$('.paulund_modal_box').fadeOut().remove();
		$('.paulund_block_page').fadeOut().remove();
	},
	findElements:  function () {
		return $('.paulund_inner_modal_box').find('input, select');
	},
	type:          {
		script_options:function () {
			return {
				title:  'Select Script Options',
				content:popUp.marketSelect() + '' +
					        '' + popUp.productSelect() + '' +
					        '' + popUp.campaignSelect() + '' +
					'<button class="submit">Submit</button>'
			}
		},
		success:       function () {
			return {
				title:  'Successful Save',
				content:'Successfully saved'
			}
		},
		fail:          function () {
			if (this.logErrors) {
				this.logError(this.error)
			}
			if (this.failMessage) {
				this.failMessage = this.makeMessage(this.failMessage);
				return {
					title:  this.failTitle,
					content:this.failMessage
				}
			}
			return {
				title:  'An Error Occured',
				content:'There was a problem while processing your request'
			}
		},
		dba_options:   function () {
			return {
				title:  'Select a DBA',
				content:popUp.dbaSelect() + '' +
					'<button class="submit">Submit</button>'
			}
		}
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
			dataType = (typeof obj.dataType != 'undefined') ? obj.dataType : 'html';
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
	addModalClick: function () {
		var that = this;
		$(".submit").click(function () {
			var market = $("#market").val();
			var product = $("#product").val();
			var widgetSet = 4;
			var campaign = $("#campaign").val();
			var user = page.user;
			var url = document.location.href;
			var form = that.getForm({
				market:   market,
				campaign: campaign,
				widgetSet:widgetSet,
				user:     user,
				product:  product,
				url:      url,
				intent:   'view_script'
			});
			form.submit();
		})
	},
	getForm:       function (options) {
		var form = $("<form action='" + options.url + "' method='post'></form>");

		for (var index in options) {
			if (index != 'url') {
				form.append("<input type='hidden' name='" + index + "' value='" + options[index] + "' />");
			}
		}
		return form;
	},
	marketSelect:  function () {
		if (page.marketList) {
			return page.marketList();
		} else {
			return '<label for="market">Market:</label><select name="market" id="market"><option value="null">--none--</option></select> '
		}
	},
	campaignSelect:function () {
		if (page.campaignList) {
			return page.campaignList();
		} else {
			return '<label for="campaign">Campaign:</label><select name="campaign" id="campaign"><option value="null">--none--</option></select> '
		}
	},
	productSelect: function () {
		if (page.productList) {
			return page.productList();
		} else {
			return '<label for="product">Product:</label><select name="product" id="product"><option value="null">--none--</option></select> '
		}
	},
	dbaSelect:     function () {
		if (page.dbaList) {
			return page.dbaList();
		} else {
			return '<label for="dba">DBA:</label><select name="dba" id="dba"><option value="null">--none--</option></select> '
		}
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
            'background':           'url(assets/images/mini_popup.png)',
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