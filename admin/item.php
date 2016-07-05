<?php
/**
 * hbData
 * --------------------------------------------------------------------------------------------------
 * 版权所有 2016-
 * 网站地址:
 * --------------------------------------------------------------------------------------------------
 * Author: AlanJager
 * Release Date: 2016-7-4
 */
define('IN_HBDATA', true);

require (dirname(__FILE__).'/include/init.php');

$module_name = $_REQUEST['module'];

//rec 操作项的初始化
$rec = $check->is_rec($_REQUEST['rec']) ? $_REQUEST['rec'] : 'default';

//图片上传
include_once (ROOT_PATH.'include/upload.class.php');

//文件上传路径，以 '/' 结尾
$images_dir = 'images/' . $module_name . '/';

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
$smarty->assign('cur', $module_name);

/**
 * 文章列表
 */
if ($rec == 'default') {

    $smarty->assign('ur_here', $_LANG['article']);
    $smarty->assign('action_link', array (
        'text' => $_LANG['article_add'],
        'href' => $module_name . '.php?rec=add&module=' . $module_name
    ));

    // 获取参数
    $cat_id = $check->is_number($_REQUEST['cat_id']) ? $_REQUEST['cat_id'] : 0;
    $keyword = isset($_REQUEST['keyword']) ? trim($_REQUEST['keyword']) : '';

    // 筛选条件
    $where = ' WHERE cat_id IN (' . $cat_id . $hbdata->hbdata_child_id($hbdata->table($module_name . '_category'), $cat_id) . ')';
    if ($keyword) {
        $where = $where . " AND title LIKE '%$keyword%'";
        $get = '&keyword=' . $keyword;
    }

    // 分页
    $page = $check->is_number($_REQUEST['page']) ? $_REQUEST['page'] : 1;
    $page_url = $module_name . '.php' . ($cat_id ? '?cat_id=' . $cat_id : '');
    $limit = $hbdata->pager($module_name, 15, $page, $page_url, $where, $get);

    $sql = "SELECT id, title, cat_id, image, add_time FROM " . $hbdata->table($module_name) . $where . " ORDER BY id DESC" . $limit;

    $query = $hbdata->query($sql);

    echo "<pre>";
    echo var_dump($hbdata->field_exist($hbdata->table($module_name), 'image'));
    echo "</pre>";
    if($hbdata->field_exist($hbdata->table($module_name), 'image')){
        while ($row = $hbdata->fetch_array($query)) {
            $cat_name = $hbdata->get_one("SELECT cat_name FROM " . $hbdata->table($module_name . '_category') . " WHERE cat_id = '$row[cat_id]'");
            $add_time = date("Y-m-d", $row['add_time']);

            $item_list[] = array (
                "id" => $row['id'],
                "cat_id" => $row['cat_id'],
                "cat_name" => $cat_name,
                "title" => $row['title'],
                "image" => $row['image'],
                "add_time" => $add_time
            );
        }
    } else {
        while ($row = $hbdata->fetch_array($query)) {
            $cat_name = $hbdata->get_one("SELECT cat_name FROM " . $hbdata->table($module_name . '_category') . " WHERE cat_id = '$row[cat_id]'");

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


    // 首页显示文章数量限制框
    //TODO
    for($i = 1; $i <= $_CFG['home_display_article']; $i++) {
        $sort_bg .= "<li><em></em></li>";
    }

    // 赋值给模板
    $smarty->assign('if_sort', $_SESSION['if_sort']);
    $smarty->assign('sort', get_sort($module_name));
    $smarty->assign('sort_bg', $sort_bg);
    $smarty->assign('cat_id', $cat_id);
    $smarty->assign('keyword', $keyword);
    $smarty->assign('module_category', $hbdata->get_category_nolevel($module_name . '_category'));
    $smarty->assign('item_list', $item_list);
    $smarty->assign('module_name', $module_name);

    $smarty->display('item.htm');

}

/**
 *文章添加
 */
if ($rec == 'add') {

    $smarty->assign('ur_here', $_LANG['article_add']);
    $smarty->assign('action_link', array (
        'text' => $_LANG['article'],
        'href' => 'item.php?module=' . $module_name
    ));

    // 格式化自定义参数，并存到数组$article，文章编辑页面中调用文章详情也是用数组$article，
    if ($_DEFINED['article']) {
        $defined = explode(',', $_DEFINED['article']);
        foreach ($defined as $row) {
            $defined_article .= $row . "：\n";
        }
        $item['defined'] = trim($defined_article);
        $item['defined_count'] = count(explode("\n", $article['defined'])) * 2;
    }

    // CSRF防御令牌生成
    $smarty->assign('token', $firewall->set_token($module_name . '_add'));

    // 赋值给模板
    $smarty->assign('form_action', 'insert');
    $smarty->assign('module_category', $hbdata->get_category_nolevel($module_name . '_category'));
    $smarty->assign('item', $item);

    $smarty->display('item.htm');

}

/**
 *文章插入
 */
if ($rec == 'insert') {

    if (empty($_POST['title']))
        $hbdata->hbdata_msg($_LANG['article_name'] . $_LANG['is_empty']);

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
    $firewall->check_token($_POST['token'], $module_name . '_add');

    $sql = "INSERT INTO " . $hbdata->table('article') . " (id, cat_id, title, defined, content, image ,keywords, add_time, description)" . " VALUES (NULL, '$_POST[cat_id]', '$_POST[title]', '$_POST[defined]', '$_POST[content]', '$file', '$_POST[keywords]', '$add_time', '$_POST[description]')";
    $hbdata->query($sql);

    $hbdata->create_admin_log($_LANG['article_add'] . ': ' . $_POST['title']);
    $hbdata->hbdata_msg($_LANG['article_add_succes'], 'item.php?module=' . $module_name);

}

/**
 *文章编辑
 */
if ($rec == 'edit') {

    $smarty->assign('ur_here', $_LANG['article_edit']);
    $smarty->assign('action_link', array (
        'text' => $_LANG['article'],
        'href' => 'article.php'
    ));

    // 验证并获取合法的ID
    $id = $check->is_number($_REQUEST['id']) ? $_REQUEST['id'] : '';

    $query = $hbdata->select($hbdata->table('article'), '*', '`id` = \'' . $id . '\'');
    $item = $hbdata->fetch_array($query);

    // 格式化自定义参数
    if ($_DEFINED['article'] || $article['defined']) {
        $defined = explode(',', $_DEFINED['article']);
        foreach ($defined as $row) {
            $defined_article .= $row . "：\n";
        }
        // 如果文章中已经写入自定义参数则调用已有的
        $item['defined'] = $article['defined'] ? str_replace(",", "\n", $article['defined']) : trim($defined_article);
        $item['defined_count'] = count(explode("\n", $article['defined'])) * 2;
    }

    // CSRF防御令牌生成
    $smarty->assign('token', $firewall->set_token('article_edit'));

    // 赋值给模板
    $smarty->assign('form_action', 'update');
    $smarty->assign('article_category', $hbdata->get_category_nolevel('article_category'));
    $smarty->assign('item', $item);

    $smarty->display('article.htm');

}

/**
 * 文章更新
 */
if ($rec == 'update') {

    if (empty($_POST['title']))
        $hbdata->hbdata_msg($_LANG['article_name'] . $_LANG['is_empty']);

    // 上传图片生成
    if ($_FILES['image']['name'] != "") {
        // 获取图片文件名
        $basename = addslashes(basename($_POST['image']));
        $file_name = substr($basename, 0, strrpos($basename, '.'));

        $upfile = $img->upload_image('image', "$file_name"); // 上传的文件域
        $file = $images_dir . $upfile;
        // $img->make_thumb($upfile, 100, 100); // 生成缩略图

        $up_file = ", image='$file'";
    }

    // 格式化自定义参数
    $_POST['defined'] = str_replace("\r\n", ',', $_POST['defined']);

    // CSRF防御令牌验证
    $firewall->check_token($_POST['token'], 'article_edit');

    $sql = "UPDATE " . $hbdata->table('article') . " SET cat_id = '$_POST[cat_id]', title = '$_POST[title]', defined = '$_POST[defined]' ,content = '$_POST[content]'" . $up_file . ", keywords = '$_POST[keywords]', description = '$_POST[description]' WHERE id = '$_POST[id]'";
    $hbdata->query($sql);

    $hbdata->create_admin_log($_LANG['article_edit'] . ': ' . $_POST['title']);
    $hbdata->hbdata_msg($_LANG['article_edit_succes'], 'article.php');

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
    $id = $check->is_number($_REQUEST['id']) ? $_REQUEST['id'] : $hbdata->hbdata_msg($_LANG['illegal'], 'article.php');

    $max_sort = $hbdata->get_one("SELECT sort FROM " . $hbdata->table('article') . " ORDER BY sort DESC");
    $new_sort = $max_sort + 1;
    $hbdata->query("UPDATE " . $hbdata->table('article') . " SET sort = '$new_sort' WHERE id = '$id'");

    $hbdata->hbdata_header($_SERVER['HTTP_REFERER']);

}

/**
 *取消首页显示商品
 */
if ($rec == 'del_sort') {

    // 验证并获取合法的ID
    $id = $check->is_number($_REQUEST['id']) ? $_REQUEST['id'] : $hbdata->hbdata_msg($_LANG['illegal'], 'article.php');

    $hbdata->query("UPDATE " . $hbdata->table('article') . " SET sort = '' WHERE id = '$id'");

    $hbdata->hbdata_header($_SERVER['HTTP_REFERER']);

}

/**
 *文章删除
 */
if ($rec == 'del') {

    // 验证并获取合法的ID
    $id = $check->is_number($_REQUEST['id']) ? $_REQUEST['id'] : $hbdata->hbdata_msg($_LANG['illegal'], 'article.php');
    $title = $hbdata->get_one("SELECT title FROM " . $hbdata->table('article') . " WHERE id = '$id'");

    if (isset($_POST['confirm']) ? $_POST['confirm'] : '') {
        // 删除相应商品图片
        $image = $hbdata->get_one("SELECT image FROM " . $hbdata->table('article') . " WHERE id = '$id'");
        $hbdata->del_image($image);

        $hbdata->create_admin_log($_LANG['article_del'] . ': ' . $title);
        $hbdata->delete($hbdata->table('article'), "id = $id", 'article.php');
    } else {
        $_LANG['del_check'] = preg_replace('/d%/Ums', $title, $_LANG['del_check']);
        $hbdata->hbdata_msg($_LANG['del_check'], 'article.php', '', '30', "article.php?rec=del&id=$id");
    }

}

/**
 *批量选择操作
 */
if ($rec == 'action') {

    if (is_array($_POST['checkbox'])) {
        if ($_POST['action'] == 'del_all') {
            // 批量文章删除
            $hbdata->del_all('article', $_POST['checkbox'], 'id', 'image');
        } elseif ($_POST['action'] == 'category_move') {
            // 批量移动分类
            $hbdata->category_move('article', $_POST['checkbox'], $_POST['new_cat_id']);
        } else {
            $hbdata->hbdata_msg($_LANG['select_empty']);
        }
    } else {
        $hbdata->hbdata_msg($_LANG['article_select_empty']);
    }

}

/**
 * get details shows at first page
 * @return array
 */
function get_sort($module_name)
{
    $limit = $GLOBALS['_DISPLAY']['home_article'] ? ' LIMIT ' . $GLOBALS['_DISPLAY']['home_article'] : '';
    $sql = "SELECT id, title FROM " . $GLOBALS['hbdata']->table($module_name) . " WHERE sort > 0 ORDER BY sort DESC" . $limit;
    $query = $GLOBALS['hbdata']->query($sql);
    while ($row = $GLOBALS['hbdata']->fetch_array($query)) {
        $sort[] = array (
            "id" => $row['id'],
            "title" => $row['title']
        );
    }

    return $sort;
}

?>