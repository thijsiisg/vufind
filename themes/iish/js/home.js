$(document).ready(function () {
    var searchCloud = [];
    $('.search-cloud a').each(function () {
        var self = $(this);
        searchCloud.push({
            text: self.text(),
            weight: self.attr('rel'),
            link: self.attr('href')
        });
    }).remove();

    $('.search-cloud').jQCloud(searchCloud, {
        colors: ['#12538B', '#4074A1', '#6E95B8', '#9CB6CE', '#CAD8E5'],
        fontSize: {
            from: 0.18,
            to: 0.05
        }
    });

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