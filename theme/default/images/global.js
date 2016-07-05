/**
 +----------------------------------------------------------
 * 下拉菜单
 +----------------------------------------------------------
 */
$(function() {
    /* 主导航 */
    $("#mainNav ul li").hover(function() {
        $(this).addClass("hover");
        $('ul:first', this).css('display', 'block');
    },
    function() {
        $(this).removeClass("hover");
        $('ul:first', this).css('display', 'none');
    });
    /* 顶部导航 */
    $("ul.topNav li.parent").hover(function() {
        $(this).addClass("hover");
        $('ul:first', this).css('display', 'block');
    },
    function() {
        $(this).removeClass("hover");
        $('ul:first', this).css('display', 'none');
    });
});

/**
 +----------------------------------------------------------
 * 刷新验证码
 +----------------------------------------------------------
 */
function refreshimage() {
    var cap = document.getElementById("vcode");
    cap.src = cap.src + '?';
}

/**
 +----------------------------------------------------------
 * 搜索框的鼠标交互事件
 +----------------------------------------------------------
 */
function formClick(name, text) {
    var obj = name;
    if (typeof(name) == "string") obj = document.getElementById(id);
    if (obj.value == text) {
        obj.value = "";
    }
    obj.onblur = function() {
        if (obj.value == "") {
            obj.value = text;
        }
    }
}

/**
 +----------------------------------------------------------
 * 更新购物车数量
 +----------------------------------------------------------
 */
function changeNumber(product_id, calculate, root_url) {
    var item_id = document.getElementById("number_" + product_id);
   
    if (calculate == 'add') {
        item_id.value++;
    } else {
        if (item_id.value > 1) {
            item_id.value--;
        }
    }
    
    changePrice(product_id, item_id.value, root_url);
}

/**
 +----------------------------------------------------------
 * 更新购物车价格
 +----------------------------------------------------------
 */
function changePrice(product_id, number, root_url) {
    if (number == 0) {
        document.getElementById("number_" + product_id).value = 1;
        var number = 1;
    }
    $.ajax({
        type: "POST",
        url: root_url + 'order.php?rec=update',
        data: {"product_id":product_id, "number":number},
        dataType: "json",
        success: function(order) {
            $("#subtotal_" + product_id).html(order.subtotal);
            $("#total").html(order.total);
            $("#product_amount").html(order.product_amount);
        }
    });
}

/**
 +----------------------------------------------------------
 * 更新快递费
 +----------------------------------------------------------
 */
function changeShipping(unique_id, root_url) {
    $.ajax({
        type: "POST",
        url: root_url + 'order.php?rec=change_shipping',
        data: {"unique_id":unique_id},
        dataType: "json",
        success: function(order) {
            $("#shipping_fee").html(order.shipping_fee);
            $(".order_amount").html(order.order_amount)
        }
    });
}

/**
 +----------------------------------------------------------
 * 表单提交
 +----------------------------------------------------------
 */
function hbdataSubmit(form_id) {
    var formParam = $("#"+form_id).serialize(); //序列化表格内容为字符串
    
    $.ajax({
        type: "POST",
        url: $("#"+form_id).attr("action")+'&do=callback',
        data: formParam,
        dataType: "json",
        success: function(form) {
            if (!form) {
                $("#"+form_id).submit();
            } else {
                for(var key in form) {
                    $("#"+key).html(form[key]);
                }
            }
        }
    });
}

/**
 +----------------------------------------------------------
 * 弹出窗口
 +----------------------------------------------------------
 */
function hbdataBox(page) {
    $.ajax({
        type: "GET",
        url: page,
        data: "if_check=1",
        dataType: "html",
        success: function(html) {
            $(document.body).append(html);
        }
    });
}

/**
 +----------------------------------------------------------
 * 清空对象内HTML
 +----------------------------------------------------------
 */
function hbdataRemove(target) {
    var obj = document.getElementById(target);
    obj.parentNode.removeChild(obj);
}

/**
 +----------------------------------------------------------
 * 收藏本站
 +----------------------------------------------------------
 */
function AddFavorite(url, title) {
    try {
        window.external.addFavorite(url, title)
    } catch(e) {
        try {
            window.sidebar.addPanel(title, url, "")
        } catch(e) {
            alert("加入收藏失败，请使用Ctrl+D进行添加")
        }
    }
}

/**
 +----------------------------------------------------------
 * 在线客服
 +----------------------------------------------------------
 */
$(document).ready(function(e) {
    // 右侧滚动
    $("#onlineService").css("right", "0px");

    // 弹出窗口
    var button_toggle = true;
    $(".onlineIcon").live("mouseover",
    function() {
        button_toggle = false;
        $("#pop").show();
    }).live("mouseout",
    function() {
        button_toggle = true;
        hideRightTip()
    });
    $("#pop").live("mouseover",
    function() {
        button_toggle = false;
        $(this).show()
    }).live("mouseout",
    function() {
        button_toggle = true;
        hideRightTip()
    });
    function hideRightTip() {
        setTimeout(function() {
            if (button_toggle) $("#pop").hide()
        },
        500)
    }

    // 返回顶部
    $(".goTop").live("click",
    function() {
        var _this = $(this);
        $('html,body').animate({
            scrollTop: 0
        },
        500,
        function() {
            _this.hide()
        })
    });
    $(window).scroll(function() {
        var htmlTop = $(document).scrollTop();
        if (htmlTop > 0) {
            $(".goTop").show()
        } else {
            $(".goTop").hide()
        }
    })
});