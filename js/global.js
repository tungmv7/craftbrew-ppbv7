/**
 * @version 7.6
 */

(function ($) {
    // postage calculator jquery plugin
    $.fn.calculatePostage = function (data) {

        // the data that will be used by the function to output the postage options
        var settings = $.extend({
            selector: null,
            btn: null,
            postUrl: null,
            ids: null,
            quantity: null,
            locationId: null,
            postCode: null,
            postageId: null,
            enableSelection: null
        }, data);

        if (settings.btn != null) {
            settings.btn.button('loading');
        }

        // shortcut method
        if (settings.selector != null) {
            // no overrides for the settings
            if (settings.ids == null) {
                settings.ids = $(settings.selector).find('.ids').map(function () {
                    return $(this).val();
                }).get();
            }
            if (settings.quantity == null) {
                settings.quantity = $(settings.selector).find('.qty').map(function () {
                    return $(this).val();
                }).get();
            }

            if (settings.locationId == null) {
                settings.locationId = $(settings.selector).find('select[name="locationId"]').val();
            }
            if (settings.postCode == null) {
                settings.postCode = $(settings.selector).find('input[name="postCode"]').val();
            }
            if (settings.enableSelection == null) {
                settings.enableSelection = $(settings.selector).find('input[name="enableSelection"]').val();
            }
            if (settings.postageId == null) {
                settings.postageId = $(settings.selector).find('input[name="postage_id"]').val();
            }
        }

        var selector = this;

        $.post(
            settings.postUrl,
            {
                ids: settings.ids,
                quantity: settings.quantity,
                locationId: settings.locationId,
                postCode: settings.postCode,
                enableSelection: settings.enableSelection,
                postageId: settings.postageId
            },
            function (data) {
                if (settings.btn != null) {
                    setTimeout(function () {
                        settings.btn.button('reset')
                    }, 500);
                }

                return selector.each(function () {
                    selector.html(data);
                });

            }
        );
    }
})(jQuery);


jQuery(document).ready(function ($) {
    $('.alert-box').on('blur', function (e) {
        e.preventDefault();

        var message = $(this).attr('data-message');
        bootbox.alert(message);
    });

    $('.dialog-box').on('click', function (e) {
        e.preventDefault();

        var href = $(this).attr('href');
        var title = $(this).attr('title');

        $.get(href, function (data) {
            bootbox.dialog({
                title: title,
                message: data,
                buttons: {
                    main: {
                        label: msgs.close,
                        className: "btn-default"
                    }
                }
            });
        });
    });

    $('.confirm-box').on('click', function (e) {
        e.preventDefault();

        var href = $(this).attr('href');
        var message = msgs.confirmThisAction;
        if ($(this).attr('data-message')) {
            message = $(this).attr('data-message');
        }

        bootbox.confirm({
            buttons: {
                confirm: {
                    label: msgs.ok,
                    className: "btn-primary"
                },
                cancel: {
                    label: msgs.cancel,
                    className: "btn-default"
                }
            },
            message: message,
            callback: function (result) {
                if (result) {
                    window.location.replace(href);
                }
            }
//            title: "You can also add a title"
        });
    });

    $('.confirm-form').on('click', function (e) {
        e.preventDefault();

        var message = msgs.confirmThisAction;
        if ($(this).attr('data-message')) {
            message = $(this).attr('data-message');
        }
        var option = $(this).val();
        var form = $(this).closest('form');

        bootbox.confirm({
            buttons: {
                confirm: {
                    label: msgs.ok,
                    className: "btn-primary"
                },
                cancel: {
                    label: msgs.cancel,
                    className: "btn-default"
                }
            },
            message: message,
            callback: function (result) {
                if (result) {
                    form.find('[name="option"]').val(option);
                    form.submit();
                }
            }
//            title: "You can also add a title"
        });
    });

    // checkboxes - select all/none
    $('[name="selectAll"]').click(function () {
        var checked = $(this).prop('checked');
        $('.select-all').prop('checked', checked);
    });

    // postage calculator from listing details page
    $('#calculate-postage').click(function () {
        $('#postage-calculator').find('.result').calculatePostage({
            selector: '#postage-calculator',
            postUrl: paths.calculatePostage,
            btn: $(this)
        });
    });

    $('.masonry').masonry({
        itemSelector: '.item'
    });

    // list grid cookie
    var cc = $.cookie('list_grid');
    if (cc == 'g') {
        $('#browse').find('.listings').find('.list').removeClass('list').addClass('grid col-sm-3 col-xs-6');
    } else {
        $('#browse').find('.listings').find('.grid').removeClass('grid col-sm-3 col-xs-6').addClass('list');
    }

    // list grid toggle
    $('#grid').click(function () {
        $('#browse').find('.listings').fadeOut(400, function () {
            $(this).find('.list').removeClass('list').addClass('grid col-sm-3 col-xs-6');
            $(this).fadeIn(400);
            $.cookie('list_grid', 'g', {path: baseUrl, expires: 30});
        });
        return false;
    });
    $('#list').click(function () {
        $('#browse').find('.listings').fadeOut(400, function () {
            $(this).find('.grid').removeClass('grid col-sm-3 col-xs-6').addClass('list');
            $(this).fadeIn(400);
            $.cookie('list_grid', null, {path: baseUrl});
        });
        return false;
    });

    /* sidebar nav offcanvas toggle */
    $('[data-toggle=offcanvas]').click(function () {
        $('.row-offcanvas').toggleClass('active', 400);
        $('footer').toggle(); // workaround for footer overlapping
    });

    /* attach loading modal behavior to button */
    $('.btn-loading-modal').on('click', function () {
        $('body').addClass('loading');
    });

    if (!modRewrite) {
        // workaround for posting get forms when mod rewrite is not available
        $('form').submit(function (e) {
            if ($(this).attr('method').toLowerCase() == 'get') {
                e.preventDefault();
                $(this).attr('method', 'post');
                $(this).submit();
            }
        });
    }

    $('pre').each(function () {
        $(this).text($(this).html()); //makes the html into plaintext
    });

    /* jquery table rows filter */
    $('.table-filter').keyup(function () {
        var rex = new RegExp($(this).val(), 'i');
        var table = $(this).closest('table');
        table.find('.searchable tr').hide();
        table.find('.searchable tr').filter(function () {
            return rex.test($(this).text());
        }).show();
    });

    // @version 7.6

    // open form in a bootbox modal
    $('.jq-popup-form').on('click', function (e) {
        e.preventDefault();

        $('body').addClass('loading');

        var title = $(this).attr('title');
        var onCloseRedirect = $(this).attr('data-close-redirect');
        var url = null;
        var method = 'GET';
        var data = null;

        if ($(this).is(':submit')) {
            var form = $(this).closest('form');

            var formMethod = form.attr('method');
            if (formMethod != '' && $.type(formMethod) !== 'undefined') {
                method = formMethod;
            }

            url = $(this).attr('formaction');
            if (url == '' || $.type(url) === 'undefined') {
                url = form.attr('action');
            }

            data = form.serialize();
        }
        else {
            url = $(this).attr('href');
        }

        $.ajax({
            url: url,
            data: data,
            method: method,
            success: function (data) {
                bootbox.dialog({
                    title: title,
                    message: data,
                    closeButton: false,
                    buttons: {
                        main: {
                            label: msgs.close,
                            className: "btn-default",
                            callback: function() {
                                if (onCloseRedirect != '' && $.type(onCloseRedirect) !== 'undefined') {
                                    $('body').addClass('loading');
                                    window.location.reload(onCloseRedirect);
                                }
                            }
                        }
                    }
                });

                $('body').removeClass('loading');
            }
        });
    });

    // full size images gallery
    $('.jq-gallery').magnificPopup({
        type: 'image',
        gallery: {
            enabled: true
        }
    });

    // ajax load pages in popup - use href as ajax load destination
    $('.ajax-popup').magnificPopup({
        type: 'ajax'
    });

    // slick carousel implementation for the listing details page main image and thumbnails
    $('#jq-mainImage')
        .slick({
            slidesToShow: 1,
            slidesToScroll: 1,
            arrows: false,
            asNavFor: '#jq-thumbnails'
        })
        .find('img').on('click', function () {
            var thumbId = $(this).attr('data-gallery-id');
            $('.jq-gallery').eq(thumbId).trigger('click');
        });

    $('#jq-thumbnails').slick({
        slidesToShow: 4,
        slidesToScroll: 4,
        asNavFor: '#jq-mainImage',
        dots: true,
        arrows: false,
        focusOnSelect: true
    });

    // slick carousel implementation for home page slider
    $('.jq-slider').slick({
        dots: true,
        arrows: false,
        autoplay: true
    });
});

