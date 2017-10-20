$(document).ready(function () {
    $('.toggle-more').click(function (e) {
        var parent = $(this).closest('.facetList');
        parent.find('.show-minimal').addClass('hidden');
        parent.find('.show-all').removeClass('hidden');

        e.preventDefault();
    });

    $('.toggle-less').click(function () {
        var parent = $(this).closest('.facetList');
        parent.find('.show-all').addClass('hidden');
        parent.find('.show-minimal').removeClass('hidden');

        e.preventDefault();
    });
});