(function ($) {
    var navigationObj = function (container, navigation, loading) {
        return {
            container : container,
            navigation: navigation,
            loading   : loading,

            resize: function () {
                var spaceBetweenElements = 20;
                var availableSpaceWidth = this.container.offset().left;
                var availableSpaceHeight = $(window).height() - this.container.offset().top;

                this.navigation.css({
                    'width'     : (availableSpaceWidth - spaceBetweenElements) + 'px',
                    'max-height': (availableSpaceHeight - spaceBetweenElements) + 'px'
                });
            },

            load: function (activeTab) {
                var tabname = activeTab.attr('id');
                var navigationElem = this.navigation.find('.' + tabname);

                if (navigationElem.length === 0) {
                    var navigationData = activeTab.data('navigation');
                    if (navigationData !== undefined) {
                        var thisObj = this;
                        this.showLoading();
                        navigationElem = $('<div class="' + tabname + ' hidden"></div>').appendTo(this.navigation);
                        $.ajax({
                            type   : 'GET',
                            url    : navigationData,
                            success: function (data) {
                                navigationElem.html(data);
                                thisObj.loadSuccess(navigationElem);
                            },
                            error  : function () {
                                navigationElem.remove();
                                thisObj.loadFailure();
                            }
                        });
                    }
                    else {
                        this.loadFailure();
                    }
                }
                else {
                    this.loadSuccess(navigationElem);
                }
            },

            showLoading: function () {
                this.navigation.children().addClass('hidden');
                this.loading.removeClass('hidden');

                this.navigation.addClass('visible-lg');
                this.navigation.removeClass('hidden invisible');
            },

            loadSuccess: function (navigationElem) {
                this.navigation.children().addClass('hidden');
                navigationElem.removeClass('hidden');

                this.navigation.addClass('visible-lg');
                this.navigation.removeClass('hidden invisible');
            },

            loadFailure: function () {
                this.navigation.children().addClass('hidden');

                this.navigation.addClass('hidden');
                this.navigation.removeClass('visible-lg invisible');
            }
        };
    };

    $(document).ready(function () {
        var container = $('.main .container');
        var navigation = $('#navigation');
        var loading = navigation.find('.loading');

        navigation.affix({
            offset: {
                top: container.offset().top
            }
        });

        var obj = navigationObj(container, navigation, loading);

        obj.resize();
        $(window).resize(function () {
            obj.resize();
        });

        obj.load($('ul.recordTabs li.active a'));
        $('ul.recordTabs a').click(function (e) {
            obj.load($(this));
        });
    });
})($);