<?php
/**
 * hbData
 * --------------------------------------------------------------------------------------------------
 * 版权所有 2016-
 * 网站地址:
 * --------------------------------------------------------------------------------------------------
 * Author: AlanJager
 * Release Date: 2016-7-12
 */
define('IN_HBDATA', true);

require (dirname(__FILE__).'/include/init.php');

//权限判断
require ('auth.php');

$module = $_REQUEST['module'];

//检测是否为正确的module。
//如果错误，则返回404
if (!$check->is_module($module, $hbdata->read_system())){
    $hbdata->hbdata_msg($_LANG['no_module'], 'index.php', '', 2);
}

//rec 操作项的初始化
$rec = $check->is_rec($_REQUEST['rec']) ? $_REQUEST['rec'] : 'default';

//图片上传
include_once (ROOT_PATH.'include/upload.class.php');

//文件上传路径，以 '/' 结尾
$images_dir = 'images/' . $module . '/';

//缩略图路径，以 '/' 结尾,留空则跟$images_dir相同
$thumb_dir = '';

//实例化类文件
$img = new Upload(ROOT_PATH.$images_dir, $thumb_dir);

//如果文件上传路径不存在，则创建
if (!file_exists(ROOT_PATH.$images_dir)) {
    mkdir(ROOT_PATH.$images_dir, 0777);
}

// 赋值给模板
$smarty->assign('rec', $rec);
$smarty->assign('cur', $module);
$smarty->assign('module_name', $_LANG['top_add_' . $module]);

/**
 * item列表
 */
if ($rec == 'default') {

    $smarty->assign('ur_here', $_LANG[$module]);
    $smarty->assign('action_link', array (
        'text' => $_LANG[$module . '_add'],
        'href' => 'item.php?module=' . $module . '&rec=add'
    ));

    // 获取参数
    $cat_id = $check->is_number($_REQUEST['cat_id']) ? $_REQUEST['cat_id'] : 0;
    $keyword = isset($_REQUEST['keyword']) ? trim($_REQUEST['keyword']) : '';

    // 筛选条件
    $where = ' WHERE cat_id IN (' . $cat_id . $hbdata->hbdata_child_id('category', $module, $cat_id) . ')';
    if ($keyword) {
        $where = $where . " AND title LIKE '%$keyword%'";
        $get = '&keyword=' . $keyword;
    }

    // 分页
    $page = $check->is_number($_REQUEST['page']) ? $_REQUEST['page'] : 1;
    $page_url = 'item.php?module=' . $module . ($cat_id ? '?cat_id=' . $cat_id : '');
    $limit = $hbdata->pager($module, 15, $page, $page_url, $where, $get);


    //如果查询的是产品
    if($module == 'product'){
        $sql = "SELECT id, name, cat_id, add_time, show_price FROM " . $hbdata->table($module) . $where . " ORDER BY id DESC" . $limit;
        $query = $hbdata->query($sql);
        while ($row = $hbdata->fetch_array($query)) {

            $cat_name = $hbdata->get_one("SELECT cat_name FROM " . $hbdata->table('category') . " WHERE cat_id = '$row[cat_id]'");
            $add_time = date("Y-m-d", $row['add_time']);
            if($row['show_price'] == true){
                $showprice = '是';
            }
            else{
                $showprice = '否';
            }

            $item_list[] = array (
                "id" => $row['id'],
                "cat_id" => $row['cat_id'],
                "cat_name" => $cat_name,
                "name" => $row['name'],
                "add_time" => $add_time,
                "show_price" => $showprice
            );
        }
    } else {
        $sql = "SELECT id, title, cat_id, image, add_time , sort FROM " . $hbdata->table($module) . $where . " ORDER BY id DESC" . $limit;
        $query = $hbdata->query($sql);

        if($hbdata->field_exist($hbdata->table($module), "image")){
            while ($row = $hbdata->fetch_array($query)) {

                $cat_name = $hbdata->get_one("SELECT cat_name FROM " . $hbdata->table('category') . " WHERE cat_id = '$row[cat_id]'");
                $add_time = date("Y-m-d", $row['add_time']);

                $item_list[] = array (
                    "id" => $row['id'],
                    "cat_id" => $row['cat_id'],
                    "cat_name" => $cat_name,
                    "title" => $row['title'],
                    "image" => $row['image'],
                    "add_time" => $add_time,
                    "sort" =>$row['sort']
                );
            }
        } else {
            while ($row = $hbdata->fetch_array($query)) {

                $cat_name = $hbdata->get_one("SELECT cat_name FROM " . $hbdata->table('category') . " WHERE cat_id = '$row[cat_id]'");

                $add_time = date("Y-m-d", $row['add_time']);

                $item_list[] = array (
                    "id" => $row['id'],
                    "cat_id" => $row['cat_id'],
                    "cat_name" => $cat_name,
                    "title" => $row['title'],
                    "add_time" => $add_time
                );
            }
        }
    }



    // 首页显示文章数量限制框
    //TODO
    for($i = 1; $i <= $_CFG['home_display_' . $module]; $i++) {
        $sort_bg .= "<li><em></em></li>";
    }

    // 赋值给模板
    $smarty->assign('if_sort', $_SESSION['if_sort']);
    $smarty->assign('sort', get_sort($module));
    $smarty->assign('sort_bg', $sort_bg);
    $smarty->assign('cat_id', $cat_id);
    $smarty->assign('keyword', $keyword);
    $smarty->assign('item_category', $hbdata->get_category_nolevel($module , 'category'));
    $smarty->assign('item_list', $item_list);

    $smarty->display('item.htm');

}

/**
 *添加
 */
if ($rec == 'add') {

    $smarty->assign('ur_here', $_LANG[$module . '_add']);
    $smarty->assign('action_link', array (
        'text' => $_LANG[$module],
        'href' => 'item.php?module=' . $module
    ));

    // 格式化自定义参数，并存到数组$item，文章编辑页面中调用文章详情也是用数组$item，
    if ($_DEFINED[$module]) {
        $defined = explode(',', $_DEFINED[$module]);
        foreach ($defined as $row) {
            $defined_item .= $row . "：\n";
        }
        $item['defined'] = trim($defined_item);
        $item['defined_count'] = count(explode("\n", $item['defined'])) * 2;
    }

    // CSRF防御令牌生成
    $smarty->assign('token', $firewall->set_token($module . '_add'));

    // 赋值给模板
    $smarty->assign('form_action', 'insert');
    $smarty->assign('item_category', $hbdata->get_category_nolevel($module , 'category'));
    $smarty->assign('item', $item);

    $smarty->display('item.htm');

}

/**
 *插入
 */
if ($rec == 'insert') {

    //如果插入的是商品
    if ($module == 'product') {
        if (empty($_POST['name']))
            $hbdata->hbdata_msg($_LANG[$module . '_name'] . $_LANG['is_empty']);
        if (!$check->is_price($_POST['price'] = trim($_POST['price'])))
            $hbdata->hbdata_msg($_LANG['price_wrong']);

        // 判断是否有上传图片/上传图片生成
        if ($_FILES['image']['name'] != '') {
            $upfile = $img->upload_image('image', $hbdata->auto_id($module)); // 上传的文件域
            $file = $images_dir . $upfile;
            $img->make_thumb($upfile, $_CFG['thumb_width'], $_CFG['thumb_height']);
        }
        $add_time = time();

        // 格式化自定义参数
        $_POST['defined'] = str_replace("\r\n", ',', $_POST['defined']);
        $show_price = $_POST['show_price'];


        // CSRF防御令牌验证
        $firewall->check_token($_POST['token'], $module . '_add');

        $sql = "INSERT INTO " . $hbdata->table($module) . " (id, cat_id, name, price, defined, content, image ,keywords, add_time, description,show_price)" . " VALUES (NULL, '$_POST[cat_id]', '$_POST[name]', '$_POST[price]', '$_POST[defined]', '$_POST[content]', '$file', '$_POST[keywords]', '$add_time', '$_POST[description]', '$show_price[0]')";
        $hbdata->query($sql);
    } else {
        if (empty($_POST['title']))
            $hbdata->hbdata_msg($_LANG[$module . '_name'] . $_LANG['is_empty']);

        // 判断是否有上传图片/上传图片生成
        if ($_FILES['image']['name'] != "") {
            // 生成图片文件名
            $file_name = date('Ymd');
            for($i = 0; $i < 6; $i++) {
                $file_name .= chr(mt_rand(97, 122));
            }

            // 其中image指的是上传的文本域名称，$file_name指的是生成的图片文件名
            $upfile = $img->upload_image('image', $file_name);
            $file = $images_dir . $upfile;
            // $img->make_thumb($upfile, 100, 100); // 生成缩略图
        }

        $add_time = time();

        // 格式化自定义参数
        $_POST['defined'] = str_replace("\r\n", ',', $_POST['defined']);

        // CSRF防御令牌验证
        $firewall->check_token($_POST['token'], $module . '_add');

        $sql = "INSERT INTO " . $hbdata->table($module) . " (id, cat_id, title, defined, content, image ,keywords, add_time, description, sort)" . " VALUES (NULL, '$_POST[cat_id]', '$_POST[title]', '$_POST[defined]', '$_POST[content]', '$file', '$_POST[keywords]', '$add_time', '$_POST[description]', '$_POST[sort]')";
        $hbdata->query($sql);
    }

    $hbdata->create_admin_log($_LANG[$module . '_add'] . ': ' . $_POST['title']);
    $hbdata->hbdata_msg($_LANG[$module . '_add_succes'], 'item.php?module=' . $module);

}

/**
 *编辑
 */
if ($rec == 'edit') {

    $smarty->assign('ur_here', $_LANG[$module . '_edit']);
    $smarty->assign('action_link', array (
        'text' => $_LANG[$module],
        'href' => 'item.php?module=' . $module
    ));

    // 验证并获取合法的ID
    $id = $check->is_number($_REQUEST['id']) ? $_REQUEST['id'] : '';

    $query = $hbdata->select($hbdata->table($module), '*', '`id` = \'' . $id . '\'');
    $item = $hbdata->fetch_array($query);

    // 格式化自定义参数
    if ($_DEFINED[$module] || $item['defined']) {
        $defined = explode(',', $_DEFINED[$module]);
        foreach ($defined as $row) {
            $defined_item .= $row . "：\n";
        }
        // 如果文章中已经写入自定义参数则调用已有的
        $item['defined'] = $item['defined'] ? str_replace(",", "\n", $item['defined']) : trim($defined_item);
        $item['defined_count'] = count(explode("\n", $item['defined'])) * 2;
    }

    //如果为产品页面，则还需要输出是否显示价格
    if ($module == 'product') {
        $show_price = ($item['show_price'] == true) ? "checked" : "";
        $smarty->assign('showprice', $show_price);
    }

    // CSRF防御令牌生成
    $smarty->assign('token', $firewall->set_token($module . '_edit'));

    // 赋值给模板
    $smarty->assign('form_action', 'update');
    $smarty->assign('item_category', $hbdata->get_category_nolevel($module , 'category'));
    $smarty->assign('item', $item);

    $smarty->display('item.htm');

}

/**
 * 更新
 */
if ($rec == 'update') {

    //不同分类检测不同
    if ($module == 'product') {
        if (empty($_POST['name']))
            $hbdata->hbdata_msg($_LANG['product_name'] . $_LANG['is_empty']);
        if (!$check->is_price($_POST['price'] = trim($_POST['price'])))
            $hbdata->hbdata_msg($_LANG['price_wrong']);
    } else {
        if (empty($_POST['title']))
            $hbdata->hbdata_msg($_LANG['article_name'] . $_LANG['is_empty']);
    }


    // 上传图片生成
    if ($_FILES['image']['name'] != "") {

        //如果插入的是产品
        if($module == 'product'){
            $image = $hbdata->get_one("SELECT image FROM " . $hbdata->table($module) . " WHERE id = '$_POST[id]'");

            // 分析商品图片名称
            if ($image) {
                $basename = addslashes(basename($image));
                $file_name = substr($basename, 0, strrpos($basename, '.'));
            } else {
                $file_name = $_POST['id'];
            }

            $upfile = $img->upload_image('image', $file_name); // 上传的文件域
            $file = $images_dir . $upfile;
            $img->make_thumb($upfile, $_CFG['thumb_width'], $_CFG['thumb_height']);

            $up_file = ", image='$file'";
        }else{
            // 获取图片文件名
            $basename = addslashes(basename($_POST['image']));
            $file_name = substr($basename, 0, strrpos($basename, '.'));

            $upfile = $img->upload_image('image', "$file_name"); // 上传的文件域
            $file = $images_dir . $upfile;
            // $img->make_thumb($upfile, 100, 100); // 生成缩略图

            $up_file = ", image='$file'";
        }
    }

    // 格式化自定义参数
    $_POST['defined'] = str_replace("\r\n", ',', $_POST['defined']);

    // CSRF防御令牌验证
    $firewall->check_token($_POST['token'], $module . '_edit');


    //如果更新的是产品
    if ($module == 'product'){
        $show_price = ($_POST['show_price'][0] == 1) ? 1 : 0;
        $sql = "UPDATE " . $hbdata->table($module) . " SET cat_id = '$_POST[cat_id]', name = '$_POST[name]', price = '$_POST[price]', defined = '$_POST[defined]' ,content = '$_POST[content]'" . $up_file . ", keywords = '$_POST[keywords]', description = '$_POST[description]',show_price = $show_price WHERE id = '$_POST[id]'";
    } else {
        $sql = "UPDATE " . $hbdata->table('article') . " SET cat_id = '$_POST[cat_id]', title = '$_POST[title]', defined = '$_POST[defined]' ,content = '$_POST[content]'" . $up_file . ", keywords = '$_POST[keywords]', description = '$_POST[description]', sort = '$_POST[sort]' WHERE id = '$_POST[id]'";
    }
    $hbdata->query($sql);

    $hbdata->create_admin_log($_LANG[$module . '_edit'] . ': ' . $_POST['title']);
    $hbdata->hbdata_msg($_LANG[$module . '_edit_succes'], 'item.php?module=' . $module);

}

/**
 * 重新生成产品图片
 */
if ($rec == 're_thumb') {
    
    //只有产品才能重新生成图片
    if($module == 'product'){
        $smarty->assign('ur_here', $_LANG[$module . '_thumb']);
        $smarty->assign('action_link', array (
            'text' => $_LANG[$module],
            'href' => 'item.php?module=' . $module
        ));

        $sql = "SELECT id, image FROM " . $hbdata->table($module) . "ORDER BY id ASC";
        $count = mysqli_num_rows($query = $hbdata->query($sql));
        $mask['count'] = preg_replace('/d%/Ums', $count, $_LANG[$module . '_thumb_count']);
        $mask_tag = '<i></i>';
        $mask['confirm'] = $_POST['confirm'];

        for($i = 1; $i <= $count; $i++)
            $mask['bg'] .= $mask_tag;

        $smarty->assign('mask', $mask);
        $smarty->display('item.htm');

        if (isset($_POST['confirm'])) {
            echo ' ';
            while ($row = $hbdata->fetch_array($query)) {
                $img->make_thumb(basename($row['image']), $_CFG['thumb_width'], $_CFG['thumb_height']);
                echo "<script type=\"text/javascript\">mask('" . $mask_tag . "');</script>";
                flush();
                ob_flush();
            }
            echo "<script type=\"text/javascript\">success();</script>\n</body>\n</html>";
        }
    }
    
}


/**
 *首页商品筛选
 */
if ($rec == 'sort') {

    $_SESSION['if_sort'] = $_SESSION['if_sort'] ? false : true;

    // 跳转到上一页面
    $hbdata->hbdata_header($_SERVER['HTTP_REFERER']);

}

/**
 *设为首页显示商品
 */
if ($rec == 'set_sort') {

    // 验证并获取合法的ID
    $id = $check->is_number($_REQUEST['id']) ? $_REQUEST['id'] : $hbdata->hbdata_msg($_LANG['illegal'], 'item.php?module=' . $module);

    $max_sort = $hbdata->get_one("SELECT sort FROM " . $hbdata->table($module) . " ORDER BY sort DESC");
    $new_sort = $max_sort + 1;
    $hbdata->query("UPDATE " . $hbdata->table($module) . " SET sort = '$new_sort' WHERE id = '$id'");

    $hbdata->hbdata_header($_SERVER['HTTP_REFERER']);

}

/**
 *取消首页显示商品
 */
if ($rec == 'del_sort') {

    // 验证并获取合法的ID
    $id = $check->is_number($_REQUEST['id']) ? $_REQUEST['id'] : $hbdata->hbdata_msg($_LANG['illegal'], 'item.php?module=' . $module);

    $hbdata->query("UPDATE " . $hbdata->table($module) . " SET sort = '' WHERE id = '$id'");

    $hbdata->hbdata_header($_SERVER['HTTP_REFERER']);

}

/**
 *删除
 */
if ($rec == 'del') {

    // 验证并获取合法的ID
    $id = $check->is_number($_REQUEST['id']) ? $_REQUEST['id'] : $hbdata->hbdata_msg($_LANG['illegal'], 'item.php?module=' . $module);

    //如果是删除产品
    if ($module == 'product') {
        $item_name = $hbdata->get_one("SELECT name FROM " . $hbdata->table($module) . " WHERE id = '$id'");
    } else {
        $item_name = $hbdata->get_one("SELECT title FROM " . $hbdata->table($module) . " WHERE id = '$id'");
    }
    
    if (isset($_POST['confirm']) ? $_POST['confirm'] : '') {
        // 删除相应商品图片
        $image = $hbdata->get_one("SELECT image FROM " . $hbdata->table($module) . " WHERE id = '$id'");
        $hbdata->del_image($image);

        
        $hbdata->create_admin_log($_LANG[$module . '_del'] . ': ' . $item_name);
        
        $hbdata->delete($hbdata->table($module), "id = $id", 'item.php?module=' . $module);
    } else {
        $_LANG['del_check'] = preg_replace('/d%/Ums', $item_name, $_LANG['del_check']);
        $hbdata->hbdata_msg($_LANG['del_check'], 'item.php?module=' . $module, '', '30', "item.php?module=$module&rec=del&id=$id");
    }

}

/**
 *批量选择操作
 */
if ($rec == 'action') {

    if (is_array($_POST['checkbox'])) {
        if ($_POST['action'] == 'del_all') {
            // 批量文章删除
            $hbdata->del_all($module, $_POST['checkbox'], 'id', 'image');
        } elseif ($_POST['action'] == 'category_move') {
            // 批量移动分类
            $hbdata->category_move($module, $_POST['checkbox'], $_POST['new_cat_id']);
        } else {
            $hbdata->hbdata_msg($_LANG['select_empty']);
        }
    } else {
        $hbdata->hbdata_msg($_LANG[$module . '_select_empty']);
    }
}

/**
 * 获取首页显示
 * @return array
 */
function get_sort($module)
{
    $limit = $GLOBALS['_DISPLAY']['home_' . $module] ? ' LIMIT ' . $GLOBALS['_DISPLAY']['home_' . $module] : '';
    
    //如果是获取商品
    if ($module == 'product') {
        $sql = "SELECT id, name, image FROM " . $GLOBALS['hbdata']->table($module) . " WHERE sort > 0 ORDER BY sort DESC" . $limit;
        $query = $GLOBALS['hbdata']->query($sql);
        while ($row = $GLOBALS['hbdata']->fetch_array($query)) {
            $image = ROOT_URL . $row['image'];

            $sort[] = array (
                "id" => $row['id'],
                "name" => $row['name'],
                "image" => $image
            );
        }
    } else {
        $sql = "SELECT id, title FROM " . $GLOBALS['hbdata']->table($module) . " WHERE sort > 0 ORDER BY sort DESC" . $limit;
        $query = $GLOBALS['hbdata']->query($sql);
        while ($row = $GLOBALS['hbdata']->fetch_array($query)) {
            $sort[] = array (
                "id" => $row['id'],
                "title" => $row['title']
            );
        }
    }


    return $sort;
}

?>