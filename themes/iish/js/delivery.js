(function ($) {
    var openCloseWrapper = function () {
        var deliveryCartWrapper = $('#delivery_cart_wrapper');
        if ((reservationCart.cart.quantity() > 0) || (reproductionCart.cart.quantity() > 0)) {
            deliveryCartWrapper.show();

            if (reservationCart.cart.quantity() > 0) {
                deliveryCartWrapper.find('.reservationCart').show();
            }
            else {
                deliveryCartWrapper.find('.reservationCart').hide();
            }

            if (reproductionCart.cart.quantity() > 0) {
                deliveryCartWrapper.find('.reproductionCart').show();
            }
            else {
                deliveryCartWrapper.find('.reproductionCart').hide();
            }
        }
        else {
            deliveryCartWrapper.hide();
        }
    };

    var determineHoldingButtons = function () {
        $('#holdings .state').each(function () {
            var holdingState = $(this);
            holdingState.determineButtons(
                holdingState.data('label'),
                holdingState.data('pid'),
                holdingState.data('signature'),
                false,
                holdingState.data('show-reservation'),
                holdingState.data('show-reproduction')
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

        determineHoldingButtons();
        $('ul.recordTabs a').on('shown.bs.tab', function (e) {
            determineHoldingButtons();
        });

		if ($('#holdings').hasClass('online-content-available')) {
			reproductionCart.cart.bind('afterAdd', function (item) {
				var itemElem = $(document.getElementById('cartItem_' + item.id()));

				itemElem.popover({
					content: delivery.warningOnlineContent,
					placement: 'left',
					trigger: 'manual'
				});
				itemElem.popover('show');

				setTimeout(function () {
					itemElem.popover('hide');
				}, 8000);
			});
		}
    });
})($);
