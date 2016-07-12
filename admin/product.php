<?php
/**
 * hbData
 * --------------------------------------------------------------------------------------------------
 * 版权所有 2016-
 * 网站地址:
 * --------------------------------------------------------------------------------------------------
 * Author: Anewnoob
 * Release Date: 2016-7-4
 */
define('IN_HBDATA', true);

require (dirname(__FILE__) . '/include/init.php');

// rec操作项的初始化
$rec = $check->is_rec($_REQUEST['rec']) ? $_REQUEST['rec'] : 'default';

// 图片上传
include_once (ROOT_PATH . 'include/upload.class.php');

// 文件上传路径，结尾加斜杠
$images_dir = 'images/product/';

// 缩略图路径（相对于$images_dir） 结尾加斜杠，留空则跟$images_dir相同
$thumb_dir = '';
$img = new Upload(ROOT_PATH . $images_dir, $thumb_dir); // 实例化类文件
if (!file_exists(ROOT_PATH . $images_dir)) {
    mkdir(ROOT_PATH . $images_dir, 0777);
}

// 赋值给模板
$smarty->assign('rec', $rec);
$smarty->assign('cur', 'product');

/**
 * 产品列表
 */
if ($rec == 'default') {
    $smarty->assign('ur_here', $_LANG['product']);
    $smarty->assign('action_link', array (
        'text' => $_LANG['product_add'],
        'href' => 'product.php?rec=add'
    ));

    // 获取参数
    $cat_id = $check->is_number($_REQUEST['cat_id']) ? $_REQUEST['cat_id'] : 0;
    $keyword = isset($_REQUEST['keyword']) ? trim($_REQUEST['keyword']) : '';

    // 筛选条件
    $where = ' WHERE cat_id IN (' . $cat_id . $hbdata->hbdata_child_id($hbdata->table('product_category'), $cat_id) . ')';
    if ($keyword) {
        $where = $where . " AND name LIKE '%$keyword%'";
        $get = '&keyword=' . $keyword;
    }

    // 分页
    $page = $check->is_number($_REQUEST['page']) ? $_REQUEST['page'] : 1;
    $page_url = 'product.php' . ($cat_id ? '?cat_id=' . $cat_id : '');
    $limit = $hbdata->pager('product', 15, $page, $page_url, $where, $get);

    $sql = "SELECT id, name, cat_id, add_time, show_price FROM " . $hbdata->table('product') . $where . " ORDER BY id DESC" . $limit;
    $query = $hbdata->query($sql);
    while ($row = $hbdata->fetch_array($query)) {
        $cat_name = $hbdata->get_one("SELECT cat_name FROM " . $hbdata->table('product_category') . " WHERE cat_id = '$row[cat_id]'");
        $add_time = date("Y-m-d", $row['add_time']);
        if($row['show_price'] == true){
            $showprice = '是';
        }
        else{
            $showprice = '否';
        }

        $product_list[] = array (
            "id" => $row['id'],
            "cat_id" => $row['cat_id'],
            "cat_name" => $cat_name,
            "name" => $row['name'],
            "add_time" => $add_time,
            "show_price" => $showprice
        );
    }

    // 首页显示商品数量限制框
    for($i = 1; $i <= $_CFG['home_display_product']; $i++) {
        $sort_bg .= "<li><em></em></li>";
    }

    // 赋值给模板
    $smarty->assign('if_sort', $_SESSION['if_sort']);
    $smarty->assign('sort', get_sort_product());
    $smarty->assign('sort_bg', $sort_bg);
    $smarty->assign('cat_id', $cat_id);
    $smarty->assign('keyword', $keyword);
    $smarty->assign('product_category', $hbdata->get_category_nolevel('product_category'));
    $smarty->assign('product_list', $product_list);

    $smarty->display('product.htm');
}

/**
 * 产品添加
 */
elseif ($rec == 'add') {
    $smarty->assign('ur_here', $_LANG['product_add']);
    $smarty->assign('action_link', array (
        'text' => $_LANG['product'],
        'href' => 'product.php'
    ));

    // 格式化自定义参数，并存到数组$product，商品编辑页面中调用商品详情也是用数组$product，
    if ($_DEFINED['product']) {
        $defined = explode(',', $_DEFINED['product']);
        foreach ($defined as $row) {
            $defined_product .= $row . "：\n";
        }
        $product['defined'] = trim($defined_product);
        $product['defined_count'] = count(explode("\n", $product['defined'])) * 2;
    }

    // CSRF防御令牌生成
    $smarty->assign('token', $firewall->set_token('product_add'));

    // 赋值给模板
    $smarty->assign('form_action', 'insert');
    $smarty->assign('product_category', $hbdata->get_category_nolevel('product_category'));
    $smarty->assign('product', $product);

    $smarty->display('product.htm');
}
/**
 * 产品插入
 */
elseif ($rec == 'insert') {
    if (empty($_POST['name']))
        $hbdata->hbdata_msg($_LANG['product_name'] . $_LANG['is_empty']);
    if (!$check->is_price($_POST['price'] = trim($_POST['price'])))
        $hbdata->hbdata_msg($_LANG['price_wrong']);

    // 判断是否有上传图片/上传图片生成
    if ($_FILES['image']['name'] != '') {
        $upfile = $img->upload_image('image', $hbdata->auto_id('product')); // 上传的文件域
        $file = $images_dir . $upfile;
        $img->make_thumb($upfile, $_CFG['thumb_width'], $_CFG['thumb_height']);
    }
    $add_time = time();

    // 格式化自定义参数
    $_POST['defined'] = str_replace("\r\n", ',', $_POST['defined']);
    $show_price = $_POST['show_price'];


    // CSRF防御令牌验证
    $firewall->check_token($_POST['token'], 'product_add');

    $sql = "INSERT INTO " . $hbdata->table('product') . " (id, cat_id, name, price, defined, content, image ,keywords, add_time, description,show_price)" . " VALUES (NULL, '$_POST[cat_id]', '$_POST[name]', '$_POST[price]', '$_POST[defined]', '$_POST[content]', '$file', '$_POST[keywords]', '$add_time', '$_POST[description]', '$show_price[0]')";
    $hbdata->query($sql);

    $hbdata->create_admin_log($_LANG['product_add'] . ': ' . $_POST['name']);
    $hbdata->hbdata_msg($_LANG['product_add_succes'], 'product.php');
}

/**
 * 产品编辑
 */
if ($rec == 'edit') {
    $smarty->assign('ur_here', $_LANG['product_edit']);
    $smarty->assign('action_link', array (
        'text' => $_LANG['product'],
        'href' => 'product.php'
    ));

    // 验证并获取合法的ID
    $id = $check->is_number($_REQUEST['id']) ? $_REQUEST['id'] : '';

    $query = $hbdata->select($hbdata->table('product'), '*', '`id` = \'' . $id . '\'');
    $product = $hbdata->fetch_array($query);

    // 格式化自定义参数
    if ($_DEFINED['product'] || $product['defined']) {
        $defined = explode(',', $_DEFINED['product']);
        foreach ($defined as $row) {
            $defined_product .= $row . "：\n";
        }
        // 如果商品中已经写入自定义参数则调用已有的
        $product['defined'] = $product['defined'] ? str_replace(",", "\n", $product['defined']) : trim($defined_product);
        $product['defined_count'] = count(explode("\n", $product['defined'])) * 2;
    }

    // CSRF防御令牌生成
    $smarty->assign('token', $firewall->set_token('product_edit'));
    
    //格式化参数
    if($product['show_price'] == true){
        $showprice = "checked";
    }
    else{
        $showprice = "";
    }

    // 赋值给模板
    $smarty->assign('form_action', 'update');
    $smarty->assign('product_category', $hbdata->get_category_nolevel('product_category'));
    $smarty->assign('product', $product);
    $smarty->assign('showprice', $showprice);

    $smarty->display('product.htm');
}

/**
 * 产品更新
 */
elseif ($rec == 'update') {
    if (empty($_POST['name']))
        $hbdata->hbdata_msg($_LANG['product_name'] . $_LANG['is_empty']);
    if (!$check->is_price($_POST['price'] = trim($_POST['price'])))
        $hbdata->hbdata_msg($_LANG['price_wrong']);

    // 上传图片生成
    if ($_FILES['image']['name'] != "") {
        $image = $hbdata->get_one("SELECT image FROM " . $hbdata->table('product') . " WHERE id = '$_POST[id]'");

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
    }

    // 格式化自定义参数
    $_POST['defined'] = str_replace("\r\n", ',', $_POST['defined']);
    $show_price = $_POST['show_price'];
    if($show_price[0] == 1){
        $showprice = 1;
    }
    else{
        $showprice = 0;
    }

    // CSRF防御令牌验证
    $firewall->check_token($_POST['token'], 'product_edit');

    $sql = "update " . $hbdata->table('product') . " SET cat_id = '$_POST[cat_id]', name = '$_POST[name]', price = '$_POST[price]', defined = '$_POST[defined]' ,content = '$_POST[content]'" . $up_file . ", keywords = '$_POST[keywords]', description = '$_POST[description]',show_price = $showprice WHERE id = '$_POST[id]'";
    $hbdata->query($sql);

    $hbdata->create_admin_log($_LANG['product_edit'] . ': ' . $_POST['name']);
    $hbdata->hbdata_msg($_LANG['product_edit_succes'], 'product.php');
}

/**
 * 重新生成产品图片
 */
elseif ($rec == 're_thumb') {
    $smarty->assign('ur_here', $_LANG['product_thumb']);
    $smarty->assign('action_link', array (
        'text' => $_LANG['product'],
        'href' => 'product.php'
    ));

    $sql = "SELECT id, image FROM " . $hbdata->table('product') . "ORDER BY id ASC";
    $count = mysql_num_rows($query = $hbdata->query($sql));
    $mask['count'] = preg_replace('/d%/Ums', $count, $_LANG['product_thumb_count']);
    $mask_tag = '<i></i>';
    $mask['confirm'] = $_POST['confirm'];

    for($i = 1; $i <= $count; $i++)
        $mask['bg'] .= $mask_tag;

    $smarty->assign('mask', $mask);
    $smarty->display('product.htm');

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

/**
 * 首页商品筛选
 */
elseif ($rec == 'sort') {
    $_SESSION['if_sort'] = $_SESSION['if_sort'] ? false : true;

    // 跳转到上一页面
    $hbdata->hbdata_header($_SERVER['HTTP_REFERER']);
}

/**
 * 设为首页显示商品
 */
elseif ($rec == 'set_sort') {
    // 验证并获取合法的ID
    $id = $check->is_number($_REQUEST['id']) ? $_REQUEST['id'] : $hbdata->hbdata_msg($_LANG['illegal'], 'product.php');

    $max_sort = $hbdata->get_one("SELECT sort FROM " . $hbdata->table('product') . " ORDER BY sort DESC");
    $new_sort = $max_sort + 1;
    $hbdata->query("UPDATE " . $hbdata->table('product') . " SET sort = '$new_sort' WHERE id = '$id'");

    $hbdata->hbdata_header($_SERVER['HTTP_REFERER']);
}

/**
 * 取消首页显示商品
 */
elseif ($rec == 'del_sort') {
    // 验证并获取合法的ID
    $id = $check->is_number($_REQUEST['id']) ? $_REQUEST['id'] : $hbdata->hbdata_msg($_LANG['illegal'], 'product.php');

    $hbdata->query("UPDATE " . $hbdata->table('product') . " SET sort = '' WHERE id = '$id'");

    $hbdata->hbdata_header($_SERVER['HTTP_REFERER']);
}

/**
 * 产品删除
 */
elseif ($rec == 'del') {
    // 验证并获取合法的ID
    $id = $check->is_number($_REQUEST['id']) ? $_REQUEST['id'] : $hbdata->hbdata_msg($_LANG['illegal'], 'product.php');

    $name = $hbdata->get_one("SELECT name FROM " . $hbdata->table('product') . " WHERE id = '$id'");

    if (isset($_POST['confirm']) ? $_POST['confirm'] : '') {
        // 删除相应商品图片
        $image = $hbdata->get_one("SELECT image FROM " . $hbdata->table('product') . " WHERE id = '$id'");
        $hbdata->del_image($image);

        $hbdata->create_admin_log($_LANG['product_del'] . ': ' . $name);
        $hbdata->delete($hbdata->table('product'), "id = $id", 'product.php');
    } else {
        $_LANG['del_check'] = preg_replace('/d%/Ums', $name, $_LANG['del_check']);
        $hbdata->hbdata_msg($_LANG['del_check'], 'product.php', '', '30', "product.php?rec=del&id=$id");
    }
}

/**
 * 批量操作选择
 */
elseif ($rec == 'action') {
    if (is_array($_POST['checkbox'])) {
        if ($_POST['action'] == 'del_all') {
            // 批量商品删除
            $hbdata->del_all('product', $_POST['checkbox'], 'id', 'image');
        } elseif ($_POST['action'] == 'category_move') {
            // 批量移动分类
            $hbdata->category_move('product', $_POST['checkbox'], $_POST['new_cat_id']);
        } else {
            $hbdata->hbdata_msg($_LANG['select_empty']);
        }
    } else {
        $hbdata->hbdata_msg($_LANG['product_select_empty']);
    }
}

/**
 * 获取首页显示商品
 * @@return sort
 */
function get_sort_product() {
    $limit = $GLOBALS['_DISPLAY']['home_product'] ? ' LIMIT ' . $GLOBALS['_DISPLAY']['home_product'] : '';
    $sql = "SELECT id, name, image FROM " . $GLOBALS['hbdata']->table('product') . " WHERE sort > 0 ORDER BY sort DESC" . $limit;
    $query = $GLOBALS['hbdata']->query($sql);
    while ($row = $GLOBALS['hbdata']->fetch_array($query)) {
        $image = ROOT_URL . $row['image'];

        $sort[] = array (
            "id" => $row['id'],
            "name" => $row['name'],
            "image" => $image
        );
    }

    return $sort;
}
?>