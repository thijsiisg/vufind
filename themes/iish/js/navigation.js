(function ($) {
    var navigationObj = function (navigation, loading) {
        return {
            navigation: navigation,
            loading   : loading,

            resize: function () {
                var spaceBetweenElements = 20;
                var availableSpaceHeight = $(window).height() - this.navigation.parent().offset().top;

                this.navigation.css({
                    'width'     : this.navigation.parent().width(),
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
                                if (data.trim().length > 0) {
                                    navigationElem.html(data);
                                    thisObj.loadSuccess(navigationElem);
                                }
                                else {
                                    navigationElem.remove();
                                    thisObj.loadFailure();
                                }
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

                this.navigation.addClass('invisible');
                this.navigation.removeClass('visible-lg hidden');
            }
        };
    };

    $(document).ready(function () {
        var navigation = $('#navigation');
        var loading = navigation.find('.loading');

        navigation.affix({
            offset: {
                top: navigation.parent().offset().top - 60
            }
        });

        var obj = navigationObj(navigation, loading);

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