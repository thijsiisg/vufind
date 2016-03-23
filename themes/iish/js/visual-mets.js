(function ($) {
    var setMetsViewers = function () {
        $('.m').each(function () {
            var metsId = $(this).attr('title');
            var a = $('<a href="' + $(this).attr('title') + '">' + $(this).text() + '<\/a>');

            $(a).click(function (event) {
                event.preventDefault();

                var parent = $(this).parents('.vfile, .vitem, .holding');
                var div = $(parent).next();

                if ($(div).hasClass('mets-embedded')) {
                    $(div).remove();
                }
                else {
                    $('div .mets-embedded').remove();
                    $('<div class="mets-embedded hidden-print"><div class="mets-container mets-hide"></div></div>')
                        .insertAfter(parent)
                        .find('>:first-child')
                        .mets2Viewer({
                            template    : visualMets.url + '/template.handler.html?callback=?',
                            layout      : 'thumbnailIISG',
                            layoutConfig: {
                                toFullScreen   : {
                                    'thumbnailIISG': 'pageFullScreen',
                                    'page'         : 'pageFullScreen'
                                },
                                toDefaultScreen: {
                                    'thumbnailFullScreen': 'thumbnailIISG',
                                    'pageFullScreen'     : 'page'
                                },
                                toStart        : {
                                    fullScreen   : {
                                        'pageFullScreen': 'thumbnailFullScreen'
                                    },
                                    defaultScreen: {
                                        'page': 'thumbnailIISG'
                                    }
                                }
                            },
                            initialize  : {
                                'metsId'  : metsId,
                                'defaults': true,
                                'height'  : '550px',
                                'url'     : visualMets.url + '/document?',
                                'pager'   : {
                                    'start': 0,
                                    'rows' : visualMets.rows
                                }
                            }
                        });
                }
            });

            $(a).insertAfter(this);
            $(this).remove();
        });
    };

    $(document).ready(function () {
        setMetsViewers();
        $('ul.recordTabs a').on('shown.bs.tab', function (e) {
            setMetsViewers();
        });
    });
})($);
