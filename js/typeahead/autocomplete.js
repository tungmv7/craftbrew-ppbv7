/**
 * @version 7.1
 */

if ($('#basic-search')) {
    $('#basic-search')
        .autocomplete({
            source: function (request, response) {
                $.post(
                    paths.typeahead,
                    {
                        term: request.term
                    },
                    function (data) {
                        //map the data into a response that will be understood by the autocomplete widget
                        response($.map(data, function (item) {
                            return {
                                value: item.value,
                                label: item.label,
                                category_id: item.category_id,
                                category_name: item.category_name,
                                listing_id: item.listing_id,
                                no_link: item.no_link
                            }
                        }));

                    },
                    'json'
                );
            },
            minLength: 3,
            //when you have selected something
            select: function (event, ui) {
                var srcForm = $(this).closest('form');
                if (ui.item.category_id > 0) {
                    srcForm.find("[name='parent_id']")
                        .append('<option value="' + ui.item.category_id + '" selected="selected">' + ui.item.category_name + '</option>');
                }

                srcForm.find("[name='keywords']").val(ui.item.value);

                // IMPORTANT: if the id of the submit button is "submit", then the below call wont work!!
                srcForm.submit();

                //close the drop down
                this.close;
            },
            //show the drop down
            open: function () {
                $(this).removeClass("ui-corner-all").addClass("ui-corner-top");
            },
            //close the drop down
            close: function () {
                $(this).removeClass("ui-corner-top").addClass("ui-corner-all");
            }
        })
        .data("ui-autocomplete")._renderItem = function (ul, item) {

        var selector = $("<li></li>")
            .data("item.ui-autocomplete", item);

        if (item.no_link == true) {
            selector.append(item.label)
        }
        else {
            selector.append('<a>' + item.label + '</a>');
        }

        selector.appendTo(ul);

        return selector;
    };
}
