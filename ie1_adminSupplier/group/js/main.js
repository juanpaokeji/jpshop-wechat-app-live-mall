/**
 * Created by UICUT.com on 2016/12/31.
 * Contact QQ: 215611388
 */
jQuery(".js_banner").slide({
  mainCell: ".bd ul",
  autoPlay: true
});
jQuery(".js_bannerLeft").slide({
  mainCell: ".bd ul",
  autoPlay: true,
  effect: "leftLoop"
});
jQuery(".js_slideTxtBox").slide();
jQuery(".js_picScrollLeft").slide({
  mainCell: ".bd ul",
  autoPage: true,
  effect: "left",
  autoPlay: true,
  vis: 3,
  trigger: "click"
});
var winH = $(window).height();
$(function () {
  if (winH > 600) {
    $(".fullpage-header-footer").css({
      minHeight: winH - $("header").height() - $("footer").height()
    });
  }

  $(document).on('click', '.alert .btn-close,.alert .js_btn-cancle', function (event) {
    event.preventDefault();
    $(this).parents(".alert").fadeOut('300', function () {
      $(this).removeClass('show');
    });
  }); // 验证码发送

  function timeClock(cls) {
    var _this = cls;

    if (_this.hasClass('disabled')) {
      return false;
    } else {
      var clock = function clock() {
        _this.text("重新发送(" + i + ")");

        i--;

        if (i < 0) {
          _this.removeClass('disabled');

          i = 59;

          _this.text("发送验证码(60)");

          clearInterval(int);
        }
      };

      _this.addClass('disabled');

      var i = 59;
      var int = setInterval(clock, 1000);
      return false;
    }
  } // 发送验证码


  $("body").on('click', '.btn-yzm', function (event) {
    event.preventDefault();
    timeClock($(this));
  });
});
$(window).resize(function () {
  // 窗口变换刷新页面
  if ($("body").hasClass('resizeFresh')) {
    location.reload();
  }
});
$(document).scroll(function (event) {});