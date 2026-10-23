jQuery(function ($) {
    // Ctrl + Enter 提交评论表单。
    $('#comment_form').on('keydown', function (event) {
        if (event.ctrlKey && (event.key === 'Enter' || event.which === 13)) {
            $(this).find('#comment_submit').trigger('click');
        }
    });

    // 代码可编辑。
    $('pre, code').attr({ spellcheck: 'false', contenteditable: 'true' });

    // 两种标题共用同一套伸缩框逻辑。
    $('.slide-box-title, .slide-box-title-withouttime').on('click', function () {
        var title = $(this);
        var body = title.parent().siblings('.slide-box-body');
        var icon = title.prev().find('i');
        var display = body.css('display');

        if (display === 'none') {
            body.show('slow');
            icon.removeClass('fa-chevron-right').addClass('fa-chevron-up');
        } else if (display === 'block') {
            body.hide('slow');
            icon.removeClass('fa-chevron-up').addClass('fa-chevron-right');
        }
    });
});

var clrs_setCookie = function (name, value) {
    var days = 3;
    var expires = new Date();
    expires.setTime(expires.getTime() + days * 24 * 60 * 60 * 1000);
    document.cookie = name + '=' + escape(value) + ';path=/;expires=' + expires.toUTCString();
};

function clrs_pagebg(id) {
    clrs_setCookie('clrs_opbg_des', id);
    document.body.style.backgroundImage = "url('" + resourceUIR + '/wp-content/uploads/background' + id + ".jpg')";
}
