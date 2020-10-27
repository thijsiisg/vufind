(function ($) {
    var openCloseWrapper = function () {
        var reservationItems = $.getShoppingCartItems(DeliveryShoppingCart.RESERVATIONS);
        var reproductionItems = $.getShoppingCartItems(DeliveryShoppingCart.REPRODUCTIONS);
        var deliveryCartWrapper = $('#delivery_cart_wrapper');

        var hasItemWithChildren = false;
        for (var i = 0; i < reservationItems.length; i++) {
            var item = reservationItems[i];
            if (item.children.length > 0) {
                hasItemWithChildren = true;
            }
        }

        var extent = parseFloat($('[data-extent]').data('extent'));

        deliveryCartWrapper.find('.reproductionCart_messages').hide();
        if (hasItemWithChildren) {
            deliveryCartWrapper.find('.reservationCart_messages p')
                .text(delivery.archiveInventoryMessage).parent().show();
        }
        else if (extent && (extent > 1)) {
            deliveryCartWrapper.find('.reservationCart_messages p')
                .text(delivery.archiveNoInventoryMessage).parent().show();
        }
        else {
            deliveryCartWrapper.find('.reservationCart_messages').hide();
        }

        if ((reservationItems.length > 0) || (reproductionItems.length > 0)) {
            deliveryCartWrapper.show();

            if (reservationItems.length > 0) {
                deliveryCartWrapper.find('.reservationCart').show();
            }
            else {
                deliveryCartWrapper.find('.reservationCart').hide();
            }

            if (reproductionItems.length > 0) {
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
        $('.holdings-container.no-children .state').each(function () {
            var holdingState = $(this);
            holdingState.determineButtons(
                holdingState.data('label'),
                holdingState.data('pid'),
                holdingState.data('signature'),
                holdingState.data('showReservation'),
                holdingState.data('showReproduction'),
                holdingState.data('showPermission')
            );
        });

        $('.holdings-container.with-children').each(function () {
            var container = $(this);
            container.determineChildButtons(
                '.state',
                container.data('label'),
                container.data('pid'),
                container.data('signature'),
                container.data('showReservation'),
                container.data('showReproduction'),
                container.data('showPermission')
            );
        });
    };

    var initWrapperState = function () {
        var deliveryCartWrapper = $('#delivery_cart_wrapper');
        var deliveryCart = $('#delivery_cart');

        var state;
        try {
            state = localStorage.getItem('delivery_cart_state');
        }
        catch (e) {
            state = 'closed';
            localStorage.clear();
        }

        if (state !== 'open' && state !== 'closed') state = 'open';
        deliveryCartWrapper.addClass(state);

        if (state === 'open')
            deliveryCart.show();
        else
            deliveryCart.hide();

        deliveryCartWrapper.find('h3').click(function () {
            if (deliveryCartWrapper.hasClass('open')) {
                deliveryCartWrapper.removeClass('open').addClass('closed');
                deliveryCart.slideUp();
            }
            else {
                deliveryCartWrapper.removeClass('closed').addClass('open');
                deliveryCart.slideDown();
            }
        });
    };

    $(document).ready(function () {
        initWrapperState();
        $(document).on('delivery.update', function () {
            openCloseWrapper();
        });

        $.initDelivery({
            host: delivery.url,
            language: delivery.lang,
            max_items: 3,
            max_children: 10,
            cart_div: '#delivery_cart'
        });

        determineHoldingButtons();
        $('ul.recordTabs a').on('shown.bs.tab', function () {
            determineHoldingButtons();
        });

        if ($('.holdings-container.no-children').hasClass('online-content-available')) {
            $(document).on('delivery.add', function (e, shoppingCart, item) {
                if (shoppingCart === DeliveryShoppingCart.REPRODUCTIONS) {
                    var itemElem = $.getShoppingCartItemElem(shoppingCart, item.pid);
                    itemElem.popover({
                        content: delivery.warningOnlineContent,
                        placement: 'left',
                        trigger: 'manual'
                    });
                    itemElem.popover('show');

                    setTimeout(function () {
                        itemElem.popover('hide');
                    }, 8000);
                }
            });
        }
    });

    $(document).on('mouseover', '.holdings-container.no-children .reservationBtn, ' +
        '.holdings-container.no-children .reproductionBtn, ' +
        '.holdings-container.no-children .permissionBtn', function (e) {
        var elem = $(e.target);
        if (!elem.hasClass('reservationBtn') && !elem.hasClass('reproductionBtn') && !elem.hasClass('permissionBtn'))
            elem = elem.closest('.reservationBtn, .reproductionBtn, .permissionBtn');

        if (elem.data('tooltipLoaded') === true) return;

        var title = delivery.reproductionTooltip;
        if (elem.hasClass('reservationBtn'))
            title = delivery.reservationTooltip;
        if (elem.hasClass('permissionBtn'))
            title = delivery.permissionTooltip;
        elem.data('tooltipLoaded', true).tooltip({title: title}).trigger('mouseover');
    });
})($);
