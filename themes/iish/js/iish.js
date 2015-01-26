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
});