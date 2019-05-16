(function( $ ){

    let Widget = {
        $body: null,
        $template: null,
        $languages: '',

        $options: {
            include_to: 'body',
            header: '',
            lang: 'ru',
            cabinet_data: null
        },

        $widget_templates: {
            main_template: 'widget/templates/chat.html'
        },

        cacheDom: function(){
            this.$body     = $('body');

        },

        events: function() {
            this.$body.on('click', '.msg_send_btn', this.cabinet.sendMessage.bind(this));

            if(this.$options.type === 'admin') {
                this.$body.on('click', '#start_search', this.cabinet_admin.startSearch.bind(this));
            }


        },

        cabinet_client: {

        },

        cabinet_admin: {
            startSearch: function (e) {
                alert('Реализовать поиск на тестовом JSON');

                let search_str = this.$options.cabinet_data.webhook_search;

                return false;
            }
        },

        cabinet: {
            sendMessage: function (e) {

                if (this.$options.cabinet_data.hasOwnProperty('callback_after_send')) {
                    this.$options.cabinet_data.callback_after_send();
                }

                return false;
            }
        },

        functions: {
            loadCSS: function(href) {
                let cssLink = $("<link>");
                $("head").append(cssLink); //IE hack: append before setting href

                cssLink.attr({rel:  "stylesheet", type: "text/css", href: href});
            },

            prepareWidget: function () {
                let _that = this;
                var content = $(_that.parent.$options.include_to).find('.container');
                // set header for widget
                if (_that.parent.$options.header === "") {
                    content.find('.widget_header').hide();
                } else {
                    content.find('.widget_header').html(_that.parent.$options.header);
                }

                let langs = _that.parent.$languages[_that.parent.$options.lang];
                $.each(langs, function (index, element) {
                    $(_that.parent.$options.include_to).html($(_that.parent.$options.include_to).html().replace('{' + element.key + '}', element.value));
                });

                // перезагрузим контент.
                content = $(_that.parent.$options.include_to).find('.container');

                this.checkHasPayment(function (is_pay) {
                    if (is_pay) {
                        content.find('.widget_copyright').hide();
                    }
                });

                if(_that.parent.$options.type === 'cabinet') {
                    content.find('.inbox_people').remove();
                    content.find('.mesgs').addClass('mesgs_full');
                } else if (_that.parent.$options.type === 'admin') {
                    // Если нет вебхука для поиска, то скрываем поиск
                    if (!this.parent.$options.cabinet_data.hasOwnProperty('webhook_search')) {
                        content.find('.srch_bar').hide();
                    }
                }

                $(_that.parent.$options.include_to).find('.container').show();
            },

            /**
             * Проверим, что лицензия на виджет оплачена.
             */
            checkHasPayment: function (callback) {
                callback(true);
            }
        },

        init: function ($settings) {
            var _that = this;

            _that.$options = $.extend(_that.$options, $settings);
            _that.functions.parent = _that;
            _that.cabinet.parent = _that;
            _that.cabinet_admin.parent = _that;
            _that.cabinet_client.parent = _that;
            
            _that.functions.loadCSS('widget/css/widget_main.css?sadasd');

            // загрузка языков.
            $.get("widget/lang.json").then( function(languages) {
                _that.$languages = languages;

                // загрузка чата
                $(_that.$options.include_to).load('widget/templates/chat.html', function() {
                    _that.functions.prepareWidget();
                    _that.cacheDom();
                    _that.events();
                });
            });
        }
    };

    $.extend({
        messenger_widget: function (settings) {
            Widget.init(settings);
        }
    });

})(jQuery);

