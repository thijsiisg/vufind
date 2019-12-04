/*global path, delivery, vufindString */

(function ($) {
    var inViewport = function (elem) {
        var w = $(window);

        var top = w.scrollTop();
        var left = w.scrollLeft();
        var bottom = top + w.height();
        var right = left + w.width();

        var elemTop = elem.offset().top;
        var elemLeft = elem.offset().left;
        var elemBottom = elemTop + elem.height();
        var elemRight = elemLeft + elem.width();

        return (top < elemTop) && (left < elemLeft) && (bottom > elemBottom) && (right > elemRight);
    };

    var loadDigital = function () {
        $('.digital.loading:visible').not('.busy').each(function () {
            var element = $(this);
            if (inViewport(element)) {
                element.addClass('busy');

                var record = element.data('record');
                var item = element.data('item');

                $.ajax({
                    dataType: 'json',
                    url: path + '/Record/' + record + '/Digital',
                    data: {item: item},
                    success: function (message) {
                        var iiifArchive = null;
                        if ((message.iiifArchive !== undefined) && (message.iiifArchive !== null)) {
                            iiifArchive = getIIIFArchive(message, record, item);
                        }

                        var iiif = null;
                        if ((message.iiif !== undefined) && (message.iiif !== null)) {
                            iiif = getIIIF(message);
                        }

                        var pdf = null;
                        if ((message.pdf !== undefined) && (message.pdf !== null)) {
                            pdf = getPdf(message);
                        }

                        var view = null;
                        if ((message.view !== undefined) && (message.view !== null)) {
                            testAccess(message.view, function (access) {
                                if (access) {
                                    view = getView(message, pdf);
                                }
                                else {
                                    view = $('<span class="text-danger">' + vufindString.availableReadingRoom + '</span>');
                                    if (pdf !== null) {
                                        view.prepend(document.createTextNode(' | '));
                                    }
                                }
                                setDigitalHtml(element, iiifArchive, iiif, pdf, view);
                            });
                        }
                        else {
                            setDigitalHtml(element, iiifArchive, iiif, pdf, view);
                        }
                    },
                    error: function () {
                        element.html('').removeClass('loading busy');
                    }
                });
            }
        });
    };

    var getAvUrl = function (item, internal) {
        if (internal) {
            return item.url
                .replace('http://hdl.handle.net/', '/AV/')
                .replace('https://hdl.handle.net/', '/AV/')
                .replace('?locatt=view:level1', '');
        }
        return item.url.replace('http://', 'https://');
    };

    var testAccess = function (view, callback) {
        if (!$.isArray(view.items)) {
            callback(true);
            return;
        }

        test(getAvUrl(view.items[0], true), callback);
    };

    var test = function (url, callback) {
        $.ajax({
            type: 'HEAD',
            cache: false,
            url: url,
            success: function () {
                callback(true);
            },
            error: function () {
                callback(false);
            }
        });
    };

    var getIIIFArchive = function (message, record, item) {
        var view = $('<span>');

        view.append(
            $('<a href="#"></a>')
                .text(vufindString.iiif)
                .click(function (event) {
                    event.preventDefault();
                    window.open(message.iiifArchive, record + '.' + item);
                })
        );

        view.append(document.createTextNode(' | '));

        var url = 'https://' + delivery.url + '/permission/createform/10622%2F' + record + '.' + item
            + '?locale=' + delivery.lang;
        view.append(
            $('<a target="_blank"></a>')
                .attr('href', url)
                .text(delivery.requestAccess)
        );

        return view;
    };

    var getIIIF = function (message) {
        var view = $('<span>');

        view.append(
            $('<a href="#"></a>')
                .text(vufindString.iiif)
                .click(function (event) {
                    event.preventDefault();
                    window.open(message.iiif, message.iiif);
                })
        );

        return view;
    };

    var getPdf = function (message) {
        return $('<a target="_blank"></a>')
            .attr('href', message.pdf)
            .text(vufindString.pdf);
    };

    var getView = function (message, pdf) {
        var view = $('<span>');
        if (pdf !== null) {
            view.append(document.createTextNode(' | '));
        }

        var linkText = vufindString.view;
        if ($.isArray(message.view.items)) {
            if (message.view.items[0].contentType.indexOf('audio') === 0) {
                linkText = vufindString.audio;
            }
            else {
                linkText = vufindString.video;
            }
        }
        else {
            view.addClass('hidden-xs hidden-ms');
        }

        $('<a>')
            .attr('href', message.view.mets)
            .text(linkText)
            .appendTo(view)
            .click(function (event) {
                event.preventDefault();

                var parent = $(this).closest('.vfile, .vitem, .info');
                var div = parent.next();

                if (div.hasClass('mets-embedded') || div.hasClass('mets-players')) {
                    div.remove();
                }
                else {
                    $('.mets-embedded, .mets-players').remove();

                    if ($.isArray(message.view.items)) {
                        setPlayers(parent, message.view.items, message.internal);
                    }
                    else {
                        setMetsViewer(parent, message.view.mets);
                    }
                }
            });

        return view;
    };

    var setMetsViewer = function (parent, metsId) {
        $('<div class="mets-embedded hidden-print"><div class="mets-container mets-hide"></div></div>')
            .insertAfter(parent)
            .find('>:first-child')
            .mets2Viewer({
                template: visualMets.url + '/template.handler.html?callback=?',
                layout: 'thumbnailIISG',
                layoutConfig: {
                    toFullScreen: {
                        'thumbnailIISG': 'pageFullScreen',
                        'page': 'pageFullScreen'
                    },
                    toDefaultScreen: {
                        'thumbnailFullScreen': 'thumbnailIISG',
                        'pageFullScreen': 'page'
                    },
                    toStart: {
                        fullScreen: {
                            'pageFullScreen': 'thumbnailFullScreen'
                        },
                        defaultScreen: {
                            'page': 'thumbnailIISG'
                        }
                    }
                },
                initialize: {
                    'metsId': metsId,
                    'defaults': true,
                    'height': '550px',
                    'url': visualMets.url + '/document?',
                    'pager': {
                        'start': 0,
                        'rows': visualMets.rows
                    }
                }
            });
    };

    var setPlayers = function (parent, items, internal) {
        var container = $('<div class="mets-players hidden-print"></div>');
        $.each(items, function (idx, item) {
            var isAudio = (item.contentType.indexOf('audio') === 0);
            var isVideo = (item.contentType.indexOf('video') === 0);

            if (isAudio || isVideo) {
                var avContainer = $('<div class="av-container iish-mejs"></div>');
                var avElem = null;

                if (isAudio) {
                    avElem = $('<audio controls preload="metadata"></audio>');
                    $('<source/>')
                        .attr('src', getAvUrl(item, internal))
                        .attr('type', (item.contentType === 'audio/mpeg3') ? 'audio/mpeg' : item.contentType)
                        .appendTo(avElem);
                    $('<span>No audio playback capabilities</span>')
                        .appendTo(avElem);

                    add();
                }
                else {
                    if (item.stillsUrl) {
                        item.stillsUrl = item.stillsUrl.replace('http://', 'https://');
                    }

                    test(item.stillsUrl, function (hasStills) {
                        avElem = $('<video controls preload="metadata" width="100%" height="100%"></video>');
                        if (hasStills) {
                            avElem.attr('poster', item.stillsUrl);
                        }

                        $('<source/>')
                            .attr('src', getAvUrl(item, internal))
                            .attr('type', item.contentType)
                            .appendTo(avElem);

                        if (hasStills) {
                            $('<img title="No video playback capabilities"/>')
                                .attr('src', item.stillsUrl)
                                .appendTo(avElem);
                        }

                        add();
                    });
                }

                function add() {
                    avContainer.hide().appendTo(container);
                    avElem.appendTo(avContainer).mediaelementplayer({
                        videoWidth: '100%',
                        audioWidth: '100%',
                        enableAutosize: true,
                        success: function () {
                            avContainer.show();
                        }
                    });
                }
            }
        });
        container.insertAfter(parent);
    };

    var setDigitalHtml = function (element, iiifArchive, iiif, pdf, view) {
        element.html('');
        if ((iiifArchive !== null) || (iiif !== null) || (pdf !== null) || (view !== null)) {
            element.append(document.createTextNode('[ '));
            if (iiifArchive !== null) {
                element.append(iiifArchive);
            }
            if (iiif !== null) {
                element.append(iiif);
            }
            if (pdf !== null) {
                element.append(pdf);
            }
            if (view !== null) {
                element.append(view);
            }
            element.append(document.createTextNode(' ]'));
        }
        element.removeClass('loading busy');
    };

    $(document).ready(function () {
        loadDigital();
        $('ul.recordTabs a').on('shown.bs.tab', function () {
            loadDigital();
        });
    });

    var scrollTimeout;
    $(window).scroll(function () {
        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(function () {
            loadDigital();
        }, 250);
    });
})(jQuery);
