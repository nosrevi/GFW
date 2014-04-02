function load() {
    jQuery.each($(".index_input_text"), function () {
        $(this).focus(function () {
            $(this).css("box-shadow", "0 0 10px rgba(255, 255, 255, 0.5), 0 0 3px #333333 inset");
        });
        $(this).blur(function () {
            $(this).css("box-shadow", "0 0 3px #333333 inset");
        });
    });

    document.getElementById("index_submit").onmouseover = function () {
        $("#index_submit").css("box-shadow", "inset 0 0 10px rgba(255, 255, 255, 0.9)");
    }
    document.getElementById("index_submit").onmouseout = function () {
        $("#index_submit").css("box-shadow", "none");
    }
    document.getElementById("index_submit").onmousedown = function () {
        $("#index_submit").removeClass("button");
        $("#index_submit").addClass("button_on");
    }
    document.getElementById("index_submit").onmouseup = function () {
        $("#index_submit").removeClass("button_on");
        $("#index_submit").addClass("button");
    }
}
