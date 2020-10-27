/*global jQuery*/
var DeliveryShoppingCart = {RESERVATIONS: 0, REPRODUCTIONS: 1};

(function ($) {
    "use strict";

    var DeliveryProps = (function () {
        var host = "localhost/delivery";
        var language = "en";
        var max_items = 3;
        var max_children = 10;
        var url_search = window.location.protocol + "//hdl.handle.net";
        var cart_div = null;

        return {
            setProperties: function (props) {
                if (!!props) {
                    if (!!props.host) host = props.host;
                    if (!!props.language) language = props.language;
                    if (!!props.max_items) max_items = props.max_items;
                    if (!!props.max_children) max_children = props.max_children;
                    if (!!props.url_search) url_search = props.url_search;
                    if (!!props.cart_div) cart_div = props.cart_div;
                }
            },
            getDeliveryHost: function () {
                return (host);
            },
            getLanguage: function () {
                return (language);
            },
            getMaxItems: function () {
                return (max_items);
            },
            getMaxChildren: function () {
                return (max_children);
            },
            getSearchURL: function () {
                return (url_search);
            },
            getShoppingCartDiv: function () {
                return (cart_div);
            }
        };
    })();

    var Rsrc = (function () {
        var str_table = null;
        return {
            setLanguage: function (lang) {
                switch (lang) {
                    case 'nl':
                        str_table = string_table_nl;
                        break;
                    case 'en':
                    default:
                        str_table = string_table_en;
                        break;
                }
            },
            getString: function (key, par) {
                var str;

                if (!!str_table) {
                    str = str_table[key];
                    if (!!par) str = str.replace("{0}", par);
                    return (str);
                }
                return ("TBS");
            }
        };
    })();

    var reservationItems = [], reproductionItems = [];
    var reservationChildrenByPid = {}, reproductionChildrenByPid = {};

    $.initDelivery = function (props) {
        if (!!props) DeliveryProps.setProperties(props);
        Rsrc.setLanguage(DeliveryProps.getLanguage());

        loadShoppingCarts();
        loadChildrenByPid();

        var inits = [
            {classStart: 'reservation', shoppingCart: DeliveryShoppingCart.RESERVATIONS},
            {classStart: 'reproduction', shoppingCart: DeliveryShoppingCart.REPRODUCTIONS}
        ];

        var shoppingCartDiv = $(DeliveryProps.getShoppingCartDiv());
        if (!shoppingCartDiv.hasClass('delivery-init')) {
            inits.forEach(function (init) {
                shoppingCartDiv.append(
                    $('<div>').addClass(init.classStart + "Cart")
                        .append(
                            $('<div>').addClass(init.classStart + "Cart_header").append(
                                $('<h5>').text(Rsrc.getString(init.classStart + '_header'))
                            )
                        )
                        .append(
                            $('<div>').addClass(init.classStart + "Cart_messages").append($('<p>'))
                        )
                        .append(
                            $('<div>').addClass(init.classStart + "Cart_items").append(
                                $('<div>').addClass("header itemRow")
                                    .append(
                                        $('<div>').addClass("itemCol").text(Rsrc.getString('cart_title'))
                                    )
                                    .append(
                                        $('<div>').addClass("itemCol item-remove").text(Rsrc.getString('cart_remove'))
                                    )
                            )
                        )
                        .append(
                            $('<div>').addClass("deliveryCartButtons")
                                .append(
                                    $('<button>')
                                        .addClass(init.classStart + "Cart_checkout")
                                        .val(Rsrc.getString('cart_button_request'))
                                        .text(Rsrc.getString('cart_button_request'))
                                )
                                .append(
                                    $('<button>')
                                        .addClass(init.classStart + "Cart_empty")
                                        .val(Rsrc.getString('cart_button_empty'))
                                        .text(Rsrc.getString('cart_button_empty'))
                                )
                        )
                );
            });
            shoppingCartDiv.addClass('delivery-init');
        }

        inits.forEach(function (init) {
            getItems(init.shoppingCart).forEach(function (item) {
                update(init.shoppingCart, item, 'init');
            });

            $(document)
                .on('click', '.' + init.classStart + 'Cart_checkout', function (e) {
                    e.preventDefault();
                    sendRequest(init.shoppingCart);
                })
                .on('click', '.' + init.classStart + 'Cart_empty', function (e) {
                    e.preventDefault();
                    emptyRequest(init.shoppingCart);
                })
                .on('click', '.' + init.classStart + 'Cart_remove', function (e) {
                    e.preventDefault();
                    var btn = $(this);
                    if (btn.data('children')) {
                        btn.data('children').split(',').forEach(function (child) {
                            removeRequest(init.shoppingCart, btn.data('pid'), child);
                        });
                    }
                    else {
                        removeRequest(init.shoppingCart, btn.data('pid'), btn.data('child'));
                    }
                })
                .on('click', 'button.' + init.classStart + 'Btn.deliveryReserveButton', function (e) {
                    e.preventDefault();
                    var btn = $(this);
                    makeRequest(init.shoppingCart, btn.data('label'),
                        btn.data('pid'), btn.data('signature'), btn.data('child'));
                })
                .on('change', 'label.' + init.classStart + 'Btn.deliveryReserveButton', function (e) {
                    e.preventDefault();
                    var btn = $(this);
                    makeRequest(init.shoppingCart, btn.data('label'),
                        btn.data('pid'), btn.data('signature'), btn.data('child'));
                });
        });

        $(document).on('click', 'button.permissionBtn.deliveryReserveButton', function (e) {
            e.preventDefault();
            var btn = $(this);
            showDeliveryPermissionPage(btn.data('pid'));
        })
    };

    $.getShoppingCartItems = function (shoppingCart) {
        return getItems(shoppingCart);
    };

    $.getShoppingCartItemElem = function (shoppingCart, pid) {
        return getItemElem(shoppingCart, pid);
    };

    $.fn.determineButtons = function (label, pid, signature, show_reservation, show_reproduction, show_permission) {
        var pars = {
            label: label,
            pid: $.trim(pid),
            signature: $.trim(signature),
            field: $(this),
            show_reservation: (show_reservation || (show_reservation === undefined)),
            show_reproduction: (show_reproduction || (show_reproduction === undefined)),
            show_permission: (show_permission || (show_permission === undefined)),
            result: buttonCallback
        };
        getJSONData("GET", "record/" + encodeURIComponent(pars.pid), pars);
    };

    $.fn.determineChildButtons = function (field_selector, label, pid, signature,
                                           show_reservation, show_reproduction, show_permission) {
        var pars = {
            container: $(this),
            field_selector: field_selector,
            label: label,
            pid: $.trim(pid),
            signature: $.trim(signature),
            show_reservation: (show_reservation || (show_reservation === undefined)),
            show_reproduction: (show_reproduction || (show_reproduction === undefined)),
            show_permission: (show_permission || (show_permission === undefined)),
            result: parentRecordCallback
        };
        getJSONData("GET", "record/" + encodeURIComponent(pars.pid), pars);
    };

    function onShoppingCart(shoppingCart, ifReservation, ifReproduction) {
        if (shoppingCart === DeliveryShoppingCart.RESERVATIONS)
            return ($.isFunction(ifReservation)) ? ifReservation() : ifReservation;
        else if (shoppingCart === DeliveryShoppingCart.REPRODUCTIONS)
            return ($.isFunction(ifReproduction)) ? ifReproduction() : ifReproduction;
        return null;
    }

    function getItems(shoppingCart) {
        if (shoppingCart === DeliveryShoppingCart.RESERVATIONS)
            return reservationItems;
        if (shoppingCart === DeliveryShoppingCart.REPRODUCTIONS)
            return reproductionItems;
        return null;
    }

    function getItemByPid(items, pid) {
        var foundItem = null;
        items.forEach(function (item) {
            if (item.pid === pid) foundItem = item;
        });
        return foundItem;
    }

    function loadShoppingCarts() {
        reservationItems = loadFromLocalStorage("delivery_reservations") || [];
        reproductionItems = loadFromLocalStorage("delivery_reproductions") || [];
    }

    function saveShoppingCart(shoppingCart) {
        if (shoppingCart === DeliveryShoppingCart.RESERVATIONS)
            localStorage.setItem("delivery_reservations", JSON.stringify(reservationItems));
        if (shoppingCart === DeliveryShoppingCart.REPRODUCTIONS)
            localStorage.setItem("delivery_reproductions", JSON.stringify(reproductionItems));
    }

    function loadChildrenByPid() {
        reservationChildrenByPid = loadFromLocalStorage("delivery_reservation_children_pid") || {};
        reproductionChildrenByPid = loadFromLocalStorage("delivery_reproduction_children_pid") || {};
    }

    function saveChildrenByPid() {
        localStorage.setItem("delivery_reservation_children_pid", JSON.stringify(reservationChildrenByPid));
        localStorage.setItem("delivery_reproduction_children_pid", JSON.stringify(reproductionChildrenByPid));
    }

    function loadFromLocalStorage(key) {
        try {
            return JSON.parse(localStorage.getItem(key));
        }
        catch (err) {
            if (err.name === 'NS_ERROR_FILE_CORRUPTED')
                localStorage.clear();
            return null;
        }
    }

    function getItemElem(shoppingCart, pid) {
        var classStart = onShoppingCart(shoppingCart, 'reservationCart', 'reproductionCart');
        var itemsContainer = $(DeliveryProps.getShoppingCartDiv()).find('.' + classStart + '_items');
        return itemsContainer
            .find('.itemRow.item')
            .filter(function () {
                return $(this).data('pid') === pid;
            });
    }

    function makeRequest(shoppingCart, label, pid, signature, child) {
        var longPid = signature ? pid + "^" + signature : pid;
        var items = getItems(shoppingCart);
        var item = getItemByPid(items, longPid);

        if (!item) {
            if ((shoppingCart === DeliveryShoppingCart.RESERVATIONS) &&
                (items.length >= DeliveryProps.getMaxItems())) {
                alert(Rsrc.getString('alert_max', DeliveryProps.getMaxItems()));
            }
            else {
                item = {
                    name: label,
                    handle: DeliveryProps.getSearchURL() + "/" + encodeURIComponent(pid),
                    pid: longPid,
                    children: child ? [child] : []
                };
                items.push(item);
            }
        }
        else if (child) {
            if (item.children.indexOf(child) < 0) {
                if (item.children.length >= DeliveryProps.getMaxChildren()) {
                    alert(Rsrc.getString('alert_max_children', DeliveryProps.getMaxChildren()));
                }
                else {
                    item.children.push(child);
                }
            }
            else {
                removeRequest(shoppingCart, pid, child);
            }
        }

        if (item) {
            update(shoppingCart, item, 'add');
        }
    }

    function removeRequest(shoppingCart, pid, child) {
        var items = getItems(shoppingCart);
        var item = getItemByPid(items, pid);

        if (item) {
            if (child && (item.children.indexOf(child) >= 0)) {
                item.children.splice(item.children.indexOf(child), 1);
                if (item.children.length === 0) {
                    items.splice(items.indexOf(item), 1);
                }
            }
            else {
                items.splice(items.indexOf(item), 1);
            }
        }

        update(shoppingCart, item, 'remove');
    }

    function update(shoppingCart, item, type) {
        saveShoppingCart(shoppingCart);
        updateItemHtml(shoppingCart, item);
        updateButtonHtml(shoppingCart, item);

        $(document)
            .trigger('delivery.update', [shoppingCart, item])
            .trigger('delivery.' + type, [shoppingCart, item]);
    }

    function emptyRequest(shoppingCart) {
        while (getItems(shoppingCart).length > 0) {
            var item = getItems(shoppingCart)[0];
            removeRequest(shoppingCart, item.pid);
        }
    }

    function sendRequest(shoppingCart) {
        var items = getItems(shoppingCart);
        if (items.length > 0) {
            var pids = "";
            items.forEach(function (item) {
                var pid = (item.children.length > 0) ? "" : item.pid;
                item.children.forEach(function (child, i) {
                    if (i > 0) pid += ",";
                    pid += item.pid + "." + child;
                });

                if (pids.length > 0) pids += ",";
                pids += pid;
            });

            showDeliveryPage(shoppingCart, pids);
            emptyRequest(shoppingCart);
        }
        else {
            alert(Rsrc.getString('alert_noitems'));
        }
    }

    function parentRecordCallback(parsParent, data, holding) {
        $(parsParent.container).find(parsParent.field_selector).each(function () {
            var field = $(this);
            var pars = {
                label: parsParent.label,
                pid: parsParent.pid,
                signature: parsParent.signature,
                child: field.data('child') ? field.data('child').toString() : null,
                field: field,
                show_reservation: parsParent.show_reservation,
                show_reproduction: parsParent.show_reproduction,
                show_permission: parsParent.show_permission
            };

            var newData = null;
            if (data !== null) {
                var childAvailable = data.reservedChilds.indexOf(pars.child) < 0;
                newData = {
                    pid: pars.pid + "." + pars.child,
                    title: pars.label,
                    restrictionType: data.restrictionType,
                    publicationStatus: data.publicationStatus,
                    openForReproduction: data.openForReproduction,
                    holdings: [{
                        signature: pars.signature,
                        status: childAvailable ? 'AVAILABLE' : 'RESERVED',
                        usageRestriction: holding.usageRestriction
                    }]
                };
            }

            buttonCallback(pars, newData, newData.holdings[0]);
        });

        reservationChildrenByPid[parsParent.pid] = getChildren(DeliveryShoppingCart.RESERVATIONS, parsParent.container);
        reproductionChildrenByPid[parsParent.pid] = getChildren(DeliveryShoppingCart.REPRODUCTIONS, parsParent.container);
        saveChildrenByPid();
    }

    function buttonCallback(pars, data, holding) {
        var html = [];

        if (data === null) {
            // Holding is not found in Delivery
            data = {
                pid: pars.pid,
                title: pars.pid,
                restriction: 'OPEN',
                publicationStatus: 'CLOSED',
                openForReproduction: false,
                holdings: [{
                    signature: pars.signature,
                    status: 'AVAILABLE',
                    usageRestriction: 'OPEN'
                }]
            };
            holding = data.holdings[0];
        }

        if (!!pars.error) {
            html.push(
                $('<span>')
                    .addClass("deliveryResponseText deliveryResponseError")
                    .text(Rsrc.getString('stat_notfound'))
            );
        }
        else if (pars.show_reservation || pars.show_reproduction || pars.show_permission) {
            if (data.restriction !== 'CLOSED') {
                if (holding.usageRestriction === 'OPEN') {
                    if (pars.show_reservation) {
                        if (holding.status === 'AVAILABLE') {
                            html.push(createButtonHtml(DeliveryShoppingCart.RESERVATIONS, pars, data));
                        }
                        else if (pars.child) {
                            html.push(
                                $('<span>')
                                    .addClass("deliveryResponseText deliveryStatReserved")
                                    .text(Rsrc.getString('stat_open_reserved_child'))
                            );
                        }
                        else {
                            html.push(
                                $('<span>')
                                    .addClass("deliveryResponseText deliveryStatReserved")
                                    .text(Rsrc.getString('stat_open_reserved') + ' ')
                                    .append(
                                        $('<a>')
                                            .attr("href", "mailto:" + Rsrc.getString('email_office'))
                                            .text(Rsrc.getString('email_office'))
                                    )
                            );
                        }
                    }

                    if (pars.show_reproduction) {
                        if (data.openForReproduction) {
                            html.push(createButtonHtml(DeliveryShoppingCart.REPRODUCTIONS, pars, data));
                        }
                        else {
                            html.push(
                                $('<span>')
                                    .addClass("deliveryResponseText deliveryStatPublicationStatus")
                                    .text(Rsrc.getString('stat_open_publication_status'))
                            );
                        }
                    }

                    if (pars.show_permission) {
                        html.push(createPermissionButtonHtml(pars, data));
                    }
                }
                else {
                    html.push(
                        $('<span>')
                            .addClass("deliveryResponseText deliveryStatUsageRestriction")
                            .text(Rsrc.getString('stat_open_restricted'))
                    );
                }
            }
        }

        var field = $(pars.field).html('');
        html.forEach(function (elem) {
            field.append(elem);
        });
    }

    function getChildren(shoppingCart, container) {
        var btnClass = onShoppingCart(shoppingCart, 'reservationBtn', 'reproductionBtn');
        return $(container)
            .find('.deliveryReserveButton.' + btnClass)
            .map(function (i, c) { return $(c).data('child'); })
            .toArray();
    }

    function createButtonHtml(shoppingCart, pars, data) {
        var html;
        var btnClass = onShoppingCart(shoppingCart, 'reservationBtn', 'reproductionBtn');
        var btnText = onShoppingCart(shoppingCart,
            Rsrc.getString('button_request_reservation'), Rsrc.getString('button_request_reproduction'));

        if (pars.child) {
            var item = getItemByPid(getItems(shoppingCart), pars.pid);

            html = $('<label>')
                .addClass('deliveryReserveButton')
                .addClass(btnClass)
                .data('label', pars.label || data.title)
                .data('pid', pars.pid)
                .data('signature', pars.signature)
                .data('child', pars.child)
                .append(
                    $('<input>')
                        .attr('type', 'checkbox')
                        .prop('checked', item && (item.children.indexOf(pars.child) >= 0))
                )
                .append(
                    $('<span>').text(btnText)
                );
        }
        else {
            html = $('<button>')
                .addClass('deliveryReserveButton')
                .addClass(btnClass)
                .data('label', pars.label || data.title)
                .data('pid', pars.pid)
                .data('signature', pars.signature)
                .val(btnText)
                .text(btnText);
        }

        return html;
    }

    function createPermissionButtonHtml(pars, data) {
        return $('<button>')
            .addClass('deliveryReserveButton')
            .addClass('permissionBtn')
            .data('label', pars.label || data.title)
            .data('pid', pars.pid)
            .data('signature', pars.signature)
            .val(Rsrc.getString('button_request_permission'))
            .text(Rsrc.getString('button_request_permission'));
    }

    function updateItemHtml(shoppingCart, item) {
        var classStart = onShoppingCart(shoppingCart, 'reservationCart', 'reproductionCart');
        var itemsContainer = $(DeliveryProps.getShoppingCartDiv()).find('.' + classStart + '_items');
        var itemDiv = getItemElem(shoppingCart, item.pid);
        var itemAdded = !!getItemByPid(getItems(shoppingCart), item.pid);

        if (!itemAdded && (itemDiv.length > 0))
            itemDiv.remove();

        if (itemAdded) {
            if (itemDiv.length === 0) {
                itemDiv = $('<div>')
                    .addClass('itemRow item')
                    .data('pid', item.pid)
                    .append(
                        $('<div>')
                            .addClass('itemCol')
                            .append(
                                $('<a>').attr("href", item.handle).text(item.name)
                            )
                            .append(
                                $('<div>').addClass("children")
                            )
                    )
                    .append(
                        $('<div>')
                            .addClass('itemCol item-remove')
                            .append(
                                $('<a>')
                                    .addClass(classStart + "_remove")
                                    .attr("href", "javascript:;")
                                    .data('pid', item.pid)
                                    .text(Rsrc.getString('cart_button_remove'))
                            )
                    );
                itemsContainer.append(itemDiv);
            }

            updateItemChildrenHtml(shoppingCart, item, itemDiv);
        }
    }

    function updateItemChildrenHtml(shoppingCart, item, itemDiv) {
        var allChildren = onShoppingCart(shoppingCart,
            reservationChildrenByPid[item.pid], reproductionChildrenByPid[item.pid]);
        var children = item.children.sort(function (a, b) { return allChildren.indexOf(a) - allChildren.indexOf(b); });
        var childrenDiv = itemDiv.find('.children');

        var rangeDivIdx = 0, rangeStart, lastNumber;
        children.forEach(function (child) {
            if (!rangeStart) {
                lastNumber = child;
                rangeStart = child;
                return;
            }

            if (allChildren.indexOf(child) === (allChildren.indexOf(lastNumber) + 1)) {
                lastNumber = child;
            }
            else {
                createUpdateChildrenRangeHtml(rangeDivIdx++, rangeStart, lastNumber);
                lastNumber = child;
                rangeStart = child;
            }
        });

        if (rangeStart && lastNumber)
            createUpdateChildrenRangeHtml(rangeDivIdx++, rangeStart, lastNumber);

        childrenDiv.find('.range').slice(rangeDivIdx).remove();

        function createUpdateChildrenRangeHtml(idx, from, until) {
            var rangesDivs = childrenDiv.find('.range');
            if (idx < rangesDivs.length) {
                var rangeDiv = rangesDivs.eq(idx);
                rangeDiv.find('input.from').val(from);
                rangeDiv.find('input.until').val(until);
                rangeDiv.find('a').data('children', item.children.slice(
                    item.children.indexOf(from), item.children.indexOf(until) + 1
                ).join(','))
            }
            else {
                var classStart = onShoppingCart(shoppingCart, 'reservationCart', 'reproductionCart');
                childrenDiv.append(
                    $('<div>')
                        .addClass('range')
                        .append($('<span>').text(Rsrc.getString('cart_children')))
                        .append($('<input>').attr('type', 'text').prop('disabled', true).addClass('from').val(from))
                        .append($('<span>').text('-').addClass('divider'))
                        .append($('<input>').attr('type', 'text').prop('disabled', true).addClass('until').val(until))
                        .append(
                            $('<a>')
                                .addClass(classStart + "_remove")
                                .attr("href", "javascript:;")
                                .data('pid', item.pid)
                                .data('children', item.children.slice(
                                    item.children.indexOf(from), item.children.indexOf(until) + 1
                                ).join(','))
                                .text(Rsrc.getString('cart_button_remove'))
                        )
                )
            }

            var dividerAndUntil = childrenDiv.find('.range').eq(idx).find('.divider, .until');
            (from === until) ? dividerAndUntil.hide() : dividerAndUntil.show();
        }
    }

    function updateButtonHtml(shoppingCart, item) {
        var classBtn = onShoppingCart(shoppingCart, 'reservationBtn', 'reproductionBtn');

        $('.deliveryReserveButton.' + classBtn + ' input:checked')
            .filter(function () { return $(this).closest('label').data('pid') === item.pid; })
            .prop('checked', false);

        if (getItemByPid(getItems(shoppingCart), item.pid)) {
            $('.deliveryReserveButton.' + classBtn)
                .filter(function () {
                    var isPid = $(this).data('pid') === item.pid;
                    var isChild = item.children.indexOf($(this).data('child')) >= 0;
                    return isPid && isChild;
                })
                .find('input')
                .prop('checked', true);
        }
    }

    function showDeliveryPage(shoppingCart, pids) {
        var url = window.location.protocol + "//" + DeliveryProps.getDeliveryHost();
        url += (shoppingCart === DeliveryShoppingCart.RESERVATIONS)
            ? "/reservation/createform/" : "/reproduction/createform/";
        url += encodeURIComponent(pids);
        url += "?locale=" + DeliveryProps.getLanguage();
        window.open(url);
    }

    function showDeliveryPermissionPage(pid) {
        var url = window.location.protocol + "//" + DeliveryProps.getDeliveryHost()
            + "/permission/createform/" + encodeURIComponent(pid)
            + "?locale=" + DeliveryProps.getLanguage();
        window.open(url);
    }

    function getJSONData(reqtype, url, pars) {
        url = DeliveryProps.getDeliveryHost() + "/" + url;
        $.ajax({
            type: reqtype,
            dataType: 'jsonp',
            url: window.location.protocol + "//" + url,
            cache: true,
            timeout: 10000,
            success: function (data, stat, xhr) {
                handleComplete(data, stat, xhr, pars);
            },
            error: function (xhr, stat, err) {
                handleError(xhr, stat, err, pars);
            }
        });
    }

    function handleComplete(data, stat, xhr, pars) {
        for (var hld in data[0].holdings) {
            var pidEquals = (pars.pid === data[0].pid);
            var signatureEquals =
                (!pars.signature || (pars.signature === '') || (pars.signature === data[0].holdings[hld].signature));
            if (pidEquals && signatureEquals) {
                pars.result(pars, data[0], data[0].holdings[hld]);
            }
        }
    }

    function handleError(xhr, stat, err, pars) {
        var msg = stat;
        if (err !== "") msg += ": " + err;
        if (xhr.status !== 0) msg += " (" + xhr.status + ")";
        console.log("handleError: msg=" + msg);
        console.log("handleError: pid=" + pars.pid);
        pars.error = stat;
        pars.result(pars, null, null);
    }
})(jQuery);
