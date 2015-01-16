/*
 * Copyright 2013 International Institute of Social History
 *
 *    Licensed under the Apache License, Version 2.0 (the "License");
 *    you may not use this file except in compliance with the License.
 *    You may obtain a copy of the License at
 *
 *        http://www.apache.org/licenses/LICENSE-2.0
 *
 *    Unless required by applicable law or agreed to in writing, software
 *    distributed under the License is distributed on an "AS IS" BASIS,
 *    WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *    See the License for the specific language governing permissions and
 *    limitations under the License.
 */

/*
 * Local properties object  
 */
var DeliveryProps = (function() {
    var host             = "localhost/delivery";
    var language         = "en";
    var max_items        = 3;
    var url_search       = "http://hdl.handle.net";
    var cart_div         = null;
    var showhide_buttons = true;
    var onLoad = function() {};
    var onUpdate = function() {};
    var cartStyle = "table";

    return {
        setProperties: function(props) {
            if (!!props)
            {
                if (!!props.showhide_buttons) showhide_buttons = props.showhide_buttons;
                if (!!props.host)             host             = props.host;
                if (!!props.language)         language         = props.language;
                if (!!props.max_items)        max_items        = props.max_items;
                if (!!props.url_search)       url_search       = props.url_search;
                if (!!props.cart_div)         cart_div         = props.cart_div;
                if (!!props.cartStyle)        cartStyle        = props.cartStyle;
                if (!!props.onLoad && typeof(props.onLoad) === 'function')        onLoad = props.onLoad;
                if (!!props.onUpdate && typeof(props.onUpdate) === 'function')        onUpdate = props.onUpdate;
            }
        },
        getDeliveryHost: function() {
            return(host);
        },
        getLanguage: function() {
            return(language);
        },
        getMaxItems: function() {
            return(max_items);
        },
        getSearchURL: function() {
            return(url_search);
        },
        getShoppingCartDiv: function() {
            return(cart_div);
        },
        getShowHideButtons: function() {
            return(showhide_buttons);
        },
        getLoadFunction: function() {
            return onLoad;
        },
        getUpdateFunction: function() {
            return onUpdate;
        },
        getCartStyle: function() {
            return cartStyle;
        }
        
        
    };
})();

/*
 * Localization resources object  
 */
var Rsrc = (function() {
    var language  = 'en';
    var str_table = null;
    
    return {
        setLanguage: function(lang) {
            /*
            var url = "resources/js/delivery.locale." + lang + ".js";
            $.getScript(url, function(data, textStatus, jqxhr) {
                if (jqxhr.status === 200)
                {
                    eval(data);
                    str_table = string_table;
                    language  = lang;
                }              
                syslog("Load was performed with "  + textStatus + " " + jqxhr.status);
            });
            */
           switch (lang)
           {
               case 'nl':
                   str_table = string_table_nl;
                   break;
               case 'en':
               default:
                   str_table = string_table_en;
                   break;
           }
        },
        getString: function(key, par)
        {   
            var str;
            
            if (!!str_table)
            {
                str = str_table[key];
                if (!!par) str = str.replace("{0}", par);
                return(str);
            }
            return("TBS");
        }
    };
})();


// Public functions

/**
 * Initialize the Delivery shopping cart API
 * 
 * An object with the following properties can be passed:
 * { 
 *      host:             "localhost/delivery",    // The delivery host and context
 *      language:         "en",                    // The language
 *      max_items:        3,                       // The maximum entries in the shopping cart
 *      cart_div:         "#delivery_cart",        // The html div where the shopping cart is displayed
 *      url_search:       "http://hdl.handle.net", // The url (host) to the holding information  
 *      showhide_buttons: true                     // Flag if the cart buttons should be hidden with empty cart
 * }
 *
 * @param {object} props     the delivery properties
 * @returns {undefined}
 */
function initDelivery(props) 
{
    var html;

    if (!!props) DeliveryProps.setProperties(props);
    syslog("Use delivery host: " + DeliveryProps.getDeliveryHost());
    Rsrc.setLanguage(DeliveryProps.getLanguage());

    if (DeliveryProps.getShoppingCartDiv() !== null)
    {
        simpleCart({
            // array representing the format and columns of the cart
            cartColumns: [
                { attr: "name",   label: Rsrc.getString('cart_title')},
                { attr: "pid",    label: Rsrc.getString('cart_pid')},
                { view: "remove", label: Rsrc.getString('cart_remove'), text: Rsrc.getString('cart_button_remove')}
            ],
            // "div" or "table"
            cartStyle: DeliveryProps.getCartStyle(), 
            // how simpleCart should checkout
            checkout: { 
                type: "SendForm", 
                url:  "javascript:sendReservation();" 
            },
            load: function() {
                show_hide_cart_buttons();
                // Call custom load function.
                DeliveryProps.getLoadFunction()();
                
            },
            update: function() {
                show_hide_cart_buttons();
                // Call Custom update function.
                DeliveryProps.getUpdateFunction()();
            },
            currency: "EUR"
        });

        html  = "<div class=\"simpleCart_items\"></div>";
        html += "<div id=\"deliveryCartButtons\">";
        html += "<button type=\"submit\" class=\"simpleCart_checkout\" value=\"";
        html += Rsrc.getString('cart_button_reserve');
        html += "\" name=\"Reserve\" onclick=\"javascript:;\" >";
        html += Rsrc.getString('cart_button_reserve');
        html += "<\/button>";
        html += "&nbsp;";
        html += "<button type=\"submit\" class=\"simpleCart_empty\" value=\"";
        html += Rsrc.getString('cart_button_empty');
        html += "\" name=\"Empty\" onclick=\"javascript:;\" >";
        html += Rsrc.getString('cart_button_empty');
        html += "<\/button>";
        html += "</div>";
        $(DeliveryProps.getShoppingCartDiv()).html(html);
    }
} /* initDelivery */

(function($) {
    /**
     * Function displays the reservation button or status text for the holding
     * 
     * @param {string}  label       the label tobe displayed uin shopping cart, if null record title is displayed
     * @param {string}  pid         the holding pid
     * @param {string}  signature   the holding signature (call number)
     * @param {boolean} directflag  if true the button jumps direct to the reservation page and skips shopping cart 
     */
    $.fn.determineReservationButton = function(label, pid, signature, directflag) {
        var pars = { 
            label:     label, 
            pid:       $.trim(pid), 
            signature: $.trim(signature), 
            direct:    directflag, 
            field:     $(this), 
            result:    button_callback 
        };
        get_json_data("GET", "record/" + encodeURIComponent(pars.pid), pars);
    }; /* determineReservationButton */
    
    /**
     * Empty the shopping cart
     */
    $.fn.emptyShoppingCart = function() {
        simpleCart.empty();
    }; /* emptyShoppingCart */

    /**
     * Show the delivery host and selected language
     * For debugging only!
     */
    $.fn.getDeliveryInfo = function() {
        var html;

        html  = "<i>";
        html += "host=" + DeliveryProps.getDeliveryHost();
        html += " ";
        html += "lang=" + DeliveryProps.getLanguage();
        html += "</i>";
        $(this).html(html);
    }; /* getDeliveryInfo */

    /**
     * Show the record response from the delivery REST API
     * For debugging only!
     *  
     * @param {string} pid          the holding pid
     * @param {string} signature    the holding signature
     */
    $.fn.getRecordInfo = function(pid, signature) {
        var pars = {
            pid:       $.trim(pid),
            signature: $.trim(signature),
            field:     $(this),
            result:    record_callback
        };
        get_json_data("GET", "record/" + encodeURIComponent(pars.pid), pars);
        $(this).html("<i>Request: pid=" + pars.pid + " signature=" + pars.signature + "</i>");
    }; /* getRecordInfo */
})(jQuery);

/**
 * Add the reservation to the shopping cart or jump to reservation page if direct request
 * This function is called by the "Request Item" button
 * 
 * @param {string} label        text (link) displayed in shopping card
 * @param {string} pid          the holding pid
 * @param {string} signature    the holding signature
 * @param {string} direct       the direct flag
 * @returns {undefined}
 */
function requestReservation(label, pid, signature, direct)
{
    var item = pid + "^" + signature;
    
    if (direct === true)
    {
        show_delivery_page(item);
    }
    else
    {
        if (DeliveryProps.getShoppingCartDiv() !== null)
        {
            if (simpleCart.find({pid: item}).length === 0)
            {
                if (simpleCart.quantity() >= DeliveryProps.getMaxItems())
                {
                    alert(Rsrc.getString('alert_max', DeliveryProps.getMaxItems()));
                }
                else
                {   
                    label = "<a href=\"" + DeliveryProps.getSearchURL() + "/" + encodeURIComponent(pid) + "\">" + label + "</a>";
                    simpleCart.add({ 
                        name:     label,
                        pid:      item,
                        quantity: 1
                    });
                }
            }
        }
        else
        {
            show_delivery_page(item);
        }
    }
} /* requestReservation */

/**
 * Send the holdings in the shopping cart to delivery
 * This function is called by the cart "Reserve" button
 * 
 * @returns {undefined}
 */
function sendReservation()
{
    var pids = "";

    if (simpleCart.quantity() > 0)
    {
        simpleCart.each(function(item, x) {
            if (pids.length > 0) pids += ",";
            pids += item.get('pid');
        });
        show_delivery_page(pids);
        simpleCart.empty();
    }
    else
    {
        alert(Rsrc.getString('alert_noitems'));
    }
} /* sendReservation */

/**
 * Goto the delivery request permission page
 * This function is called by the "Request Permission" button
 * 
 * @param {string} pid          the holding pid
 * @param {string} signature    the holding signature
 * @returns {undefined}
 */
function requestPermission(pid, signature)
{
    var item = pid + "^" + signature;
    
    show_permission_page(item);
} /* requestPermission */

// Local functions

function button_callback(pars, data, holding)
{
    var html;
    
    syslog("button_callback: field=" + pars.field + " data=" + data);
    if (data === null)
    {
        // Holding is not found in Delivery
        data = {
            pid:             pars.pid,
            title:           pars.pid,
            // embargo:         "2013-03-20",
            restrictionType: 'OPEN',
            holdings: [
                {
                    signature:        pars.signature,
                    status:           'AVAILABLE',
                    usageRestriction: 'OPEN'
                }  
            ]
        };
        holding = data.holdings[0];
    }
    if (!!pars.error)
    {
        html = "<span class=\"deliveryResponseError\">";
        html += Rsrc.getString('stat_notfound');
        html += "</span>";
    }
    else
    {
        if (data.restrictionType === 'OPEN')
        {
            if (holding.usageRestriction === 'OPEN')
            {
                if (holding.status === 'AVAILABLE')
                {
                    html  = "<button type=\"submit\" class=\"deliveryReserveButton\" value=\"";
                    html += Rsrc.getString('button_request');
                    html += "\" name=\"RequestItem\" onclick=\"requestReservation('";
                    if (pars.label === null)
                    {
                        html += data.title;
                    }
                    else
                    {
                        html += pars.label;
                    }
                    html += "', '";
                    html += pars.pid;
                    html += "', '";
                    html += pars.signature;
                    html += "', ";
                    html += pars.direct;
                    html += ");\" >";
                    html += Rsrc.getString('button_request');
                    html += "<\/button>";
                }
                else
                {
                    html  = "<span class=\"deliveryResponseText deliveryStatReserved\">";
                    html += Rsrc.getString('stat_open_reserved');
                    html += " ";
                    html += "<a href=\"mailto:";
                    html += Rsrc.getString('email_office');
                    html += "\">";
                    html += Rsrc.getString('email_office');
                    html += "</a>";
                    html += ".</span>";
                }
            }
            else
            {
                html  = "<span class=\"deliveryResponseText deliveryStatUsageRestriction\">";
                html += Rsrc.getString('stat_open_restricted');
                html += ".</span>";
            }
        }
        else if (data.restrictionType === 'RESTRICTED')
        {
            html  = "<span class=\"deliveryResponseText deliveryStatRestricted\">";
            html += Rsrc.getString('stat_restricted');
            html += " ";
            html += " <button type=\"submit\" class=\"deliveryPermissionButton\" value=\"";
            html += Rsrc.getString('button_permission');
            html += "\" name=\"RequestItem\" onclick=\"requestReservation('";
            html += pars.pid;
            html += "', '";
            html += pars.signature;
            html += ");\" >";
            html += Rsrc.getString('button_request');
            html += "<\/button>";
            html += ".</span>";
        }
        else // CLOSED
        {
            html = "<span class=\"deliveryResponseText deliveryStatClosed\">";
            if (!!data.embargo)
            {
                html += Rsrc.getString('stat_closed_embargo', formatted_date(data.embargo));
            }
            else
            {
                html += Rsrc.getString('stat_closed');
            }
            html += ".</span>";
        }
    }
    $(pars.field).html(html);
} /* button_callback */

function record_callback(pars, data, holding)
{
    var rec;
    
    syslog("record_callback: field=" + pars.field);
    rec  = "<i>Response:<br />";
    if (data === null)
    {
        rec += "pid=" + pars.pid + "<br />";
        rec += "signature=" + pars.signature + "<br />";
        rec += "status=NOT_FOUND<br />";
    }
    else
    {
        rec += "pid=" + data.pid + "<br />";
        rec += "signature=" + holding.signature + "<br />";
        rec += "title=" + data.title + "<br />";
        if (!!data.embargo) rec += "embargo=" + data.embargo + "<br />";
        rec += "restrictionType=" + data.restrictionType + "<br />";
        rec += "usageRestriction=" + holding.usageRestriction + "<br />";
        rec += "status=" + holding.status + "<br />";        
    }
    rec += "</i>";
    $(pars.field).html(rec);
} /* record_callback */

function show_delivery_page(pids)
{
    var url;
    
    url  = "http://" + DeliveryProps.getDeliveryHost();
    url += "/reservation/createform/";
    url += encodeURIComponent(pids);
    url += "?locale=" + DeliveryProps.getLanguage();
    // window.location = url;
    window.open(url);
} /* show_delivery_page */

function show_permission_page(pids)
{
    var url;
    
    url  = "http://" + DeliveryProps.getDeliveryHost();
    url += "/permission/createform/";
    url += encodeURIComponent(pids);
    url += "?locale=" + DeliveryProps.getLanguage();
    // window.location = url;
    window.open(url);
} /* show_permission_page */

function show_hide_cart_buttons()
{
    if (DeliveryProps.getShowHideButtons() === true)
    {
        if (simpleCart.quantity() > 0)
        {
            $("#deliveryCartButtons").show();
        }
        else
        {
            $("#deliveryCartButtons").hide();
        }
    }
    else
    {
        $("#deliveryCartButtons").show();
    }
} /* show_hide_cart_buttons */

function get_json_data(reqtype, url, pars)
{
    url = DeliveryProps.getDeliveryHost() + "/" + url;
    syslog("get_json_data: url=" + url + " pars=" + pars);

    if ($.jsonp === undefined)
    {
        $.ajax({
            type:        reqtype,
            url:         "http://" + url,
            dataType:    'jsonp',
            crossDomain: true,
            cache:       true,
            timeout:     10000,
            success:     function(data, stat, xhr) {handle_complete(data, stat, xhr, pars);},
            error:       function(xhr, stat, err) {handle_error(xhr, stat, err, pars);},
            // complete:      function(xhr, stat) {syslog("Complete " + xhr.status);},
            statusCode: {
                404: function(xhr, stat, err) {handle_error(xhr, stat, err, pars);}
            }
        });
    }
    else
    {
        $.jsonp({
            type:              reqtype,
            url:               "http://" + url,
            callbackParameter: "callback",
            cache:             true,
            timeout:           10000,
            success:           function(data, stat, xhr) {handle_complete(data, stat, xhr, pars);},
            error:             function(xhr, stat, err)  {handle_error(xhr, stat, err, pars);}
            // complete:          function(xhr, stat) {syslog("Complete " + xhr + " stat=" + stat);},
        });    
    }
} /* get_json_data */

function handle_complete(data, stat, xhr, pars)
{
    syslog("handle_complete: stat=" + stat);    
    syslog("handle_complete: pid=" + data[0].pid);
    for(var hld in data[0].holdings)
    {
        if (pars.pid === data[0].pid && pars.signature === data[0].holdings[hld].signature)
        {
            pars.result(pars, data[0], data[0].holdings[hld]);
        }
    }
} /* handle_complete */

function handle_error(xhr, stat, err, pars)
{
    syslog("handle_error: stat=" + stat);
    var msg = stat;
    if (err !== "") msg += ": " + err;
    if (xhr.status !== 0) msg += " (" + xhr.status + ")";
    syslog("handle_error: msg=" + msg);    
    syslog("handle_error: pid=" + pars.pid);
    pars.error = stat;
    pars.result(pars, null, null);
} /* handle_error */

function syslog(msg)
{
    if (!!window.console) window.console.log(msg);
} /* syslog */

function formatted_date(dd)
{
    if (!!$.datepicker)
    {
        return($.datepicker.formatDate(Rsrc.getString('date_format'), new Date(dd)));
    }
    return(dd);
} /* formatted_date */
