/**
 +----------------------------------------------------------
 * 页面加载时运行
 +----------------------------------------------------------
 */
$(function() {
    // 下来菜单
    $('.M').hover(function() {
        $(this).addClass('active');
    },
    function() {
        $(this).removeClass('active');
    });
});

/**
 +----------------------------------------------------------
 * 刷新验证码
 +----------------------------------------------------------
 */
function refreshimage() {
    var cap = document.getElementById('vcode');
    cap.src = cap.src + '?';
}

/**
 +----------------------------------------------------------
 * 无组件刷新局部内容
 +----------------------------------------------------------
 */
function hbdata_callback(page, name, value, target) {
    $.ajax({
        type: 'GET',
        url: page,
        data: name + '=' + value,
        dataType: "html",
        success: function(html) {
            $('#' + target).html(html);
        }
    });
}

/**
 +----------------------------------------------------------
 * 表单全选
 +----------------------------------------------------------
 */
function selectcheckbox(form) {
    for (var i = 0; i < form.elements.length; i++) {
        var e = form.elements[i];
        if (e.name != 'chkall' && e.disabled != true) e.checked = form.chkall.checked;
    }
}







/**
 +----------------------------------------------------------
 * 弹出窗口
 +----------------------------------------------------------
 */
function hbdataFrame(name, frame, url ) {
    $.ajax({
        type: 'POST',
        url: url,
        data: {'name':name, 'frame':frame},
        dataType: 'html',
        success: function(html) {
            $(document.body).append(html);
        }
    });
}

/**
 +----------------------------------------------------------
 * 显示和隐藏
 +----------------------------------------------------------
 */
function hbdataDisplay(target, action) {
    var traget = document.getElementById(target);
    if (action == 'show') {
        traget.style.display = 'block';
    } else {
        traget.style.display = 'none';
    }
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
 * 无刷新自定义导航名称
 +----------------------------------------------------------
 */
function change(id, choose) {
    document.getElementById(id).value = choose.options[choose.selectedIndex].title;
}

/**
 +----------------------------------------------------------
 * 角色名称检验
 +----------------------------------------------------------
 */
function checkRoleName() {
    var textLists = document.getElementsByClassName('roleName');
    var cues = document.getElementsByClassName('cue');
    var btns = document.getElementsByClassName('btnManager');
    var reg1 = /^[a-zA-Z][a-zA-Z0-9_]*$/; //角色名称正则
    for(var i = 0; i < textLists.length; i++) {
        if(!reg1.test(textLists[i].value)) {
            cues[i].style.display = "inline-block";
            cues[i].innerHTML = "用户名不能为空，且只能以英文字母、数字、下划线组成（字母开头）";
            btns[i].disabled = true;
            btns[i].style.opacity = '.5';
        } else {
            cues[i].style.display = "none";
            btns[i].disabled = false;
            btns[i].style.opacity = '1';
        }
    }
}

/**
 +----------------------------------------------------------
 * 别名检验
 +----------------------------------------------------------
 */
function checkUniqueId() {
    var textLists = document.getElementsByClassName('uniqueId');
    var cues = document.getElementsByClassName('cue');
    var btns = document.getElementsByClassName('btnCategory');
    var reg = /^[a-z0-9\-]+$/; //别名正则
    for(var i = 0; i < textLists.length; i++) {
        if(!reg.test(textLists[i].value)) {
            cues[i].style.display = "inline-block";
            cues[i].innerHTML = '别名不能为空，且只能以小写英文字母、数字、和"-"组成';
            btns[i].disabled = true;
            btns[i].style.opacity = '.5';
        } else {
            cues[i].style.display = "none";
            btns[i].disabled = false;
            btns[i].style.opacity = '1';
            checkSort();
        }
    }
}

/**
 +----------------------------------------------------------
 * 分类管理别名检验
 +----------------------------------------------------------
 */
function checkCateId() {
    var textLists = document.getElementsByClassName('cateId');
    var cues = document.getElementsByClassName('cue');
    var btns = document.getElementsByClassName('btnCategory');
    var reg1 = /^[a-zA-Z]+$/; //分类管理别名正则
    for(var i = 0; i < textLists.length; i++) {
        if(!reg1.test(textLists[i].value)) {
            cues[i].style.display = "inline-block";
            cues[i].innerHTML = '别名不能为空，且只能以英文字母组成';
            btns[i].disabled = true;
            btns[i].style.opacity = '.5';
        } else {
            cues[i].style.display = "none";
            btns[i].disabled = false;
            btns[i].style.opacity = '1';
        }
    }
}

/**
 +----------------------------------------------------------
 * 排序输入检验
 +----------------------------------------------------------
 */
function checkSort() {
    var textLists = document.getElementsByClassName('sortFomt');
    var cues = document.getElementsByClassName('cue2');
    var btns = document.getElementsByClassName('btnCategory');
    var reg = /^[1-9]\d*$/; //排序正则
    for(var i = 0; i < textLists.length; i++) {
        if(!reg.test(textLists[i].value)) {
            cues[i].style.display = "inline-block";
            cues[i].innerHTML = '排序不能为空，且以正整数组成';
            btns[i].disabled = true;
            btns[i].style.opacity = '.5';
        } else {
            cues[i].style.display = "none";
            btns[i].disabled = false;
            btns[i].style.opacity = '1';
            checkUniqueId();
        }
    }
}