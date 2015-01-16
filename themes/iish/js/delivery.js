(function ($) {
    var openCloseWrapper = function () {
        if (simpleCart.quantity() > 0) {
            $('#delivery_cart_wrapper').show();
        }
        else {
            $('#delivery_cart_wrapper').hide();
        }
    };

    var determineHoldingReservations = function () {
        $('#holdings .state').each(function () {
            var holdingState = $(this);
            holdingState.determineReservationButton(
                holdingState.data('label'),
                holdingState.data('pid'),
                holdingState.data('signature'),
                false
            );
        });
    };

    $(document).ready(function () {
        initDelivery({
            host     : delivery.url,
            language : delivery.lang,
            max_items: 3,
            cart_div : '#delivery_cart',
            onLoad   : openCloseWrapper,
            onUpdate : openCloseWrapper
        });

        determineHoldingReservations();
        $('ul.recordTabs a').on('shown.bs.tab', function (e) {
            determineHoldingReservations();
        })
    });
})($);
