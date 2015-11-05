$(document).ready(function () {
    $('.iish-about-link').click(function () {
        return Lightbox.get('IISH', 'About');
    });

    $('.iish-databases-link').click(function () {
        return Lightbox.get('IISH', 'Databases');
    });

    $('#order-record').click(function () {
        return Lightbox.get('Order', 'Home', {id: $('.hiddenId')[0].value});
    });

    $('.homeFacets .facetList').slimScroll({
        height: '',
        touchScrollStep: 50
    });

    $(document).on('submit', '#fulltextSearchForm', function (e) {
        e.stopImmediatePropagation();
        e.preventDefault();

        $('#fulltextSearchResults').html('<div><i class="fa fa-spinner fa-spin"></i> '+vufindString.loading+'...</div>');
        ajaxFullTextResults();

        return false;
    });

    onSearchTypeChange();
    $('.searchForm_type').change(onSearchTypeChange);

    responsiveTabs();
    $(window).resize(responsiveTabs);
});

function responsiveTabs() {
    $('.nav-tabs').each(function () {
        var tabs = $(this);
        var responsiveDropdown = tabs.find('.responsive-dropdown');
        if (responsiveDropdown.length > 0) {
            var tabsHeight = tabs.innerHeight();
            var dropdownMenu = responsiveDropdown.find('.dropdown-menu');

            if (tabsHeight >= 50) {
                while (tabsHeight >= 50) {
                    var children = tabs.children('li:not(:last-child)');
                    var count = children.size();
                    $(children[count-1]).prependTo(dropdownMenu);
                    responsiveDropdown.show();
                    tabsHeight = tabs.innerHeight();
                }
            }
            else {
                var collapsed = dropdownMenu.children('li');
                while ((tabsHeight < 50) && (collapsed.size() > 0)) {
                    collapsed.eq(0).insertBefore(tabs.children('li:last-child'));
                    tabsHeight = tabs.innerHeight();
                    collapsed = dropdownMenu.children('li');
                }
                responsiveDropdown.hide();

                if (tabsHeight >= 50) {
                    responsiveTabs();
                }
            }
        }
    });
}

function onSearchTypeChange() {
    if ($('.searchForm_type:visible:first').val() === 'AllFields') {
        $('.searchFormFullText:visible:first')
            .removeAttr('disabled')
            .closest('label')
            .removeClass('text-muted');
    }
    else {
        $('.searchFormFullText:visible:first')
            .removeAttr('checked')
            .attr('disabled', 'disabled')
            .closest('label')
            .addClass('text-muted');
    }
}

function ajaxFullTextResults() {
    var id = $('.hiddenId')[0].value;
    var fullTextSearch = $('#fulltextSearchForm');
    var fullTextSearchResults = $('#fulltextSearchResults');

    // Grab the part of the url that is the Controller and Record ID
    var urlroot = document.URL.match(new RegExp('/[^/]+/'+id));
    $.ajax({
        url: path + urlroot[0] + '/Search',
        type: 'POST',
        data: {lookfor: fullTextSearch.find('input[name=lookfor]').val()},
        success: function (data) {
            fullTextSearchResults.html(data);
        }
    });
}