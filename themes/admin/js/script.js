/**
 * @version 7.5
 */
$(document).ready(function () {
    /* ADMIN SEARCH AUTOCOMPLETE BOX */
    $("[name='admin_quick_nav']").autocomplete({
        source: function (request, response) {

            $.ajax({
                url: paths.quickNavigation,
                dataType: "json",
                data: {
                    input: request.term
                },
                success: function (data) {
                    //map the data into a response that will be understood by the autocomplete widget
                    response($.map(data, function (item) {
                        return {
                            label: item.label,
                            path: item.path
                        }
                    }));
                    $(".ui-helper-hidden-accessible").hide();
                }
            });
        },
        //start looking at 2 characters
        minLength: 2,
        //when you have selected something
        select: function (event, ui) {
            if (ui.item.path != '') {
                window.location.href = ui.item.path;
            }
        },
        //show the drop down
        open: function () {
            $(this).removeClass("ui-corner-all").addClass("ui-corner-top");
        },
        //close the drop down
        close: function () {
            $(this).removeClass("ui-corner-top").addClass("ui-corner-all");
        }
    });

    $("[name='init_category_counters']").on('click', function (e) {
        e.preventDefault();

        var total = $('#category-total-listings').text(); // need to get this from somewhere.

        var limit = 100;
        var offset = (-1) * limit;
        var button = $(this);
        var buttonValue = button.html();
        var progress = 0;
        button.html('Please wait..').attr('disabled', true);

        function countListingsByCategory() {
            offset += limit;
            if (offset >= total) {
                button.attr('disabled', false).html(buttonValue);
                $('#category-counters-progress').html(total + ' listings counted.');
                return;
            }

            $.ajax({
                url: paths.initCategoryCounters,
                dataType: "json",
                data: {
                    limit: limit,
                    offset: offset
                },
                cache: false,
                success: function (data) {
                    progress += data.counter;
                    $('#category-counters-progress').html(progress + '/' + total + ' listings counted.');
                    countListingsByCategory();
                }
            });
        }

        countListingsByCategory();

    });
});
