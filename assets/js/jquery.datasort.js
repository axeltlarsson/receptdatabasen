/*jslint browser: true, plusplus: true, vars: true, forin: true */
/*global base*/
(function ($) {
    "use strict";
    $.fn.datasort = function (options) {

        var defaults = {
                datatype: 'alpha',
                sortElement: false,
                sortAttr: false,
                reverse: false
            },
            settings = $.extend({}, defaults, options),
            datatypes = {
                alpha: function (a, b) {
                    var o = base.extract(a, b);
                    return base.alpha(o.a, o.b);
                },
                number: function (a, b) {
                    var o = base.extract(a, b);
                    for (var e in o) {
                        o[e] = o[e].replace(/[$]?(-?\d+.?\d+)/, '\$1');
                    }
                    return base.number(o.a, o.b);
                },
                date: function (a, b) {
                    var o = base.extract(a, b);
                    for (var e in o) {
                        o[e] = o[e].replace(/-/g, '')
                            .replace(/january|jan/i, '01')
                            .replace(/february|feb/i, '02')
                            .replace(/march|mar/i, '03')
                            .replace(/april|apr/i, '04')
                            .replace(/may/i, '05')
                            .replace(/june|jun/i, '06')
                            .replace(/july|jul/i, '07')
                            .replace(/august|aug/i, '08')
                            .replace(/september|sept|sep/i, '09')
                            .replace(/october|oct/i, '10')
                            .replace(/november|nov/i, '11')
                            .replace(/december|dec/i, '12')
                            .replace(/(\d{2}) (\d{2}), (\d{4})/, '\$3\$1\$2')
                            .replace(/(\d{2})\/(\d{2})\/(\d{4})/, '\$3\$2\$1');
                    }
                    return base.number(o.a, o.b);
                },
                time: function (a, b) {
                    var o = base.extract(a, b),
                        afternoon = /^(.+) PM$/i;
                    for (var e in o) {
                        o[e] = o[e].split(':');
                        var last = o[e].length - 1;

                        if (afternoon.test(o[e][last])) {
                            o[e][0] = (parseInt(o[e][0]) + 12).toString();
                            o[e][last] = o[e][last].replace(afternoon, '\$1');
                        }
                        if (parseInt(o[e][0]) < 10 && o[e][0].length === 1) {
                            o[e][0] = '0' + o[e][0];
                        }
                        o[e][last] = o[e][last].replace(/^(.+) AM$/i, '\$1');

                        o[e] = o[e].join('');
                    }
                    return base.alpha(o.a, o.b);
                }
            },
            base = {
                alpha: function (a, b) {
                    a = a.toUpperCase();
                    b = b.toUpperCase();
                    return (a < b) ? -1 : (a > b) ? 1 : 0;
                },
                number: function (a, b) {
                    a = parseFloat(a);
                    b - parseFloat(b);
                    return a - b;
                },
                extract: function (a, b) {

                    var get = function (i) {
                        var o = $(i);
                        if (settings.sortElement) {
                            o = o.children(settings.sortElement);
                        }
                        if (settings.sortAttr) {
                            o = o.attr(settings.sortAttr);
                        } else {
                            o = o.text();
                        }
                        return o;
                    };

                    return {
                        a: get(a),
                        b: get(b)
                    };
                }
            },
            that = this;


        if (typeof settings.datatype === 'string') {
            that.sort(datatypes[settings.datatype]);
        }
        if (typeof settings.datatype === 'function') {
            that.sort(settings.datatype);
        }
        if (settings.reverse) {
            that = $($.makeArray(that).reverse());
        }

        $.each(that, function (index, element) {
            that.parent().append(element);
        });

    };
})(jQuery);