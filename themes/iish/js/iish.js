$(document).ready(function () {
    $('#searchForm input[name=lookfor]').focus();

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
});

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