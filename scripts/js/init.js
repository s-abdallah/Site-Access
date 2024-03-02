$(document).ready(function() {
    var config = {
        ".neochosen": {
            inherit_select_classes: true,
            width: "100%",
            disable_search_threshold: 10
        },
        ".neochosen-deselect": {
            inherit_select_classes: true,
            allow_single_deselect: true
        },
        ".neochosen-no-single": {
            allow_single_deselect: true,
            disable_search_threshold: 10
        }
    };
    for (var selector in config) {
        $(selector).chosen(config[selector]);
    }
    $(".neochosen").find(".chosen-results").niceScroll({
        cursorcolor: "#1786ab",
        cursorwidth: "8px",
        background: "#fbfbfb",
        cursorborder: "1px solid #ededec",
        cursorborderradius: 0,
        cursoropacitymin: 1
    });
});