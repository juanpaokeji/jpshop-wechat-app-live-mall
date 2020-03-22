/**
 * Created by UICUT.com on 2016/12/31.
 * Contact QQ: 215611388
 */
$(function () {
  // 通用：弹框关闭
  $(document).on('click', '.alert .btn-close,.alert .over-close,.alert .js_btn-cancle', function (event) {
    event.preventDefault()
    $(this).parents(".alert").fadeOut('300', function () {
      $(this).removeClass('show')
    })
  }) // 删除选中

  $("body").on('click', '.btn-delete', function (event) {
    event.preventDefault()

    if (!$(".item-active").hasClass('item-line') && !$(".item-active").hasClass('item-table')) {
      var cls = '.' + $(".item-active").attr("data-name")
      $(".group " + cls).removeClass('on')
    }

    if ($(".item-active").hasClass('item-table')) {
      $(".group .name-table").removeClass('on')
      tableTitle.splice(0, tableTitle.length - 1)
    }
    $(".item-active").remove()
  })
  $('.edit-box .item').l_zoom('auto').l_drag()
  $("body").on('mousedown', '.edit-box .item', function (event) {
    var _this = $(this)

    _this.addClass('item-active').siblings().removeClass('item-active')

    if ($(this).hasClass('item-line')) {
      $('.setFont').css("display", "none")
      $('.setLine').css("display", "block")
    } else {
      $('.setFont').css("display", "block")
      $('.setLine').css("display", "none")
    }
    resetOperate()
  })
  $("body").on('click', ".edit-over", function (event) {
    $(".item-active").removeClass('item-active')
    $('.setFont').css("display", "none")
    $('.setLine').css("display", "none")
  })

  function resetOperate() {
    var active = $(".item-active")
    if (!active.hasClass('item-line') && !active.hasClass('item-img')) {
      $(".setFont .font").eq(0).val(active.css("fontFamily"))
      $(".setFont .font")[1][parseInt(active.css("fontSize")) - 12].selected = true
      $(".setFont .font")[2][parseInt(active.css("letterSpacing"))].selected = true
    } else {
      $(".setFont .font").val("")
      $(".setLine .line").val("")
    }
  } // 添加元素

  function addElement(name, text, isImg,id) {
    var str = ''
		if (isImg) {
      str = '<div class="item item-img '+ name +' item-active" id="item-active" data-name="'+ name +'"><img src="images/code.jpg" alt=""><div class="img-over"></div></div>'
    } else {
			if(!id){
				str = '<div class="item '+ name +' item-active" id="item-active" data-name="'+ name +'"><span>'+ text +'</span></div>'
			}else{
				str = '<div class="item '+ name +' item-active" data-id="'+ id +'" id="item-active" data-name="'+ name +'"><span>'+ text +'</span></div>'
			}
    }
    $(".item-active").removeClass('item-active')
    editBox.append(str)
    $('.item-active').l_zoom('auto').l_drag($('.item-active'),$('.edit-box'))
    $('.setFont').css("display", "block")
    $('.setLine').css("display", "none")
    resetOperate()
  }

  function addElementLine(direction, type, isImg) {
    var width = '4px'
    var height = '4px'
    if (direction == 'w') {
      width = "80%"
      var str = "<div class=\"item item-line " + name + " item-active\" data-direction=\"w\" style=\"width:" + width + ";height:1px;border-width: 8px 0px 0px;border-style: " + type + ";\">\n\t\t\t\t<span></span>\n\t\t\t\t</div>"
    }
    if (direction == 'h') {
      height = "80%"
      var str = "<div class=\"item item-line " + name + " item-active\" data-direction=\"h\" style=\"width:1px;height:" + height + ";border-width: 0px 0px 0px 8px;border-style: " + type + ";\">\n\t\t\t\t<span></span>\n\t\t\t\t</div>"
    }
    $(".item-active").removeClass('item-active')
    editBox.append(str)
    $('.item-active').l_drag($('.item-active'),$('.edit-box'))
    $('.setFont').css("display", "none")
    $('.setLine').css("display", "block")
    resetOperate()
  }

  var tableTitle = []

  function addElementTable() {
    var x = $(".item-table").css("left") || 0
    var y = $(".item-table").css("top") || 0
    $(".item-table").remove()

    if (tableTitle.length > 0) {
      var ths = ''

      for (var i = 0; i < tableTitle.length; i++) {
        ths += "<th>" + tableTitle[i] + "</th>"
      }

      var str = "\n\t\t\t<div class=\"item item-table item-active\" data-name=\"name-table\" style=\"left:" + x + ";top:" + y + ";\">\n\t\t\t\t<table>\n\t\t\t\t\t<tr>\n\t\t\t\t\t\t" + ths + "\n\t\t\t\t\t</tr>\n\t\t\t\t</table>\n\t\t\t</div>\n\t\t\t"
      $(".item-active").removeClass('item-active')
      editBox.append(str)
      $('.item-active').l_zoom('auto').l_drag()
      $('.setFont').css("display", "none")
      $('.setLine').css("display", "none")
    }
  }

  var editBox = $(".edit-box");
  $("body").on('change', '.editBoxWidth', function (event) {
    event.preventDefault()
    var val = $(this).val()
    editBox.width(val)
  })
  $("body").on('change', '.editBoxHeight', function (event) {
    event.preventDefault()
    var val = $(this).val()
    editBox.height(val)
  })
  $("body").on('change', '.font', function (event) {
    event.preventDefault()
    var val = $(this).val()
    var type = $(this).attr("data-type")
    if (type == "fontFamily") {
      $(".item-active").css("fontFamily", val)
    } else if (type == "fontSize") {
      $(".item-active").css("fontSize", val + 'px')
    } else if (type == "letterSpacing") {
      $(".item-active").css("letterSpacing", val + 'px')
    }
  })
  $("body").on('change', '.setLine .line', function (event) {
    event.preventDefault()
    var val = $(this).val()
    var type = $(this).attr("data-type")
    var direction = $(".item-active").attr("data-direction")

    if (type == "line-type") {
      $(".item-active").css("borderStyle", val)
    } else if (type == "line-width") {
      if (direction == 'w') {
        $(".item-active").css("width", val + 'px')
      } else {
        $(".item-active").css("borderWidth", '0px 0px 0px ' + val + 'px')
      }
    } else if (type == "line-height") {
      if (direction == 'w') {
        $(".item-active").css("borderWidth", val + 'px 0px 0px')
      } else {
        $(".item-active").css("height", val + 'px')
      }
    }
  })
  $("body").on('click', '.checkbox', function (event) {
    event.preventDefault()
    var name = $(this).attr("data-name")
    var text = $(this).text()
    var isImg = $(this).hasClass('add-img')
		var id = $(this).parent().prev().attr('id')

    if ($(this).hasClass('on')) {
      $(".edit-box ." + name).remove()
    } else {
      addElement(name, text, isImg,id)
    }
    $(this).toggleClass('on')
  })
$("body").on('click', '.checkbox-table', function (event) {
    event.preventDefault()
    var name = $(this).attr("data-name")
    var text = $(this).text()

    if ($(this).hasClass('on')) {
      tableTitle.splice(tableTitle.findIndex(function (item) {
        return item === text
      }), 1)
    } else {
      tableTitle.push(text)
    }

    addElementTable()
    $(this).toggleClass('on')
  })
  $("body").on('click', '.group .add', function (event) {
    event.preventDefault()
    var direction = $(this).attr("data-direction")
    var type = $(this).attr("data-type")
    var text = $(this).text()
    addElementLine(direction, type)
  })
})