//$('[name="keywords"]')
//    .typeahead({
//        name: 'results',
//        remote: {
//            url: baseUrl + '/app/typeahead/remote?term=%QUERY',
//            dataType: 'jsonp',
//            filter: function (data) {
//                var retval = [];
//                for (var i = 0; i < data.length; i++) {
//                    retval.push({
//                        value: data[i].value,
//                        tokens: [data[i].tokens],
//                        categoryId: data[i].categoryId,
//                        categoryName: data[i].categoryName,
//                        template: '<p>{{value}} in {{categoryName}}</p>'
//                    });
//                }
//                return retval;
//            }
//        },
//        minLength: 3
//    })
//    .on('typeahead:selected', function (e) {
//        e.target.form.submit();
//    });
//

// TODO: fix error with the css display..

$('#searchfield')
    .typeahead({
        name: 'search',
        remote: {
            url: 'http://suggestqueries.google.com/complete/search?client=chrome&q=%QUERY',
            dataType: 'jsonp',
            cache: false,
            filter: function (parsedResponse) {
                return (parsedResponse.length > 1) ? parsedResponse[1] : [];
            }
        }
    })
    .on('typeahead:selected', function () {
        var form = $(this).closest('form');
        form.submit();
    });