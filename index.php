<?php
/**
 * hbData
 * --------------------------------------------------------------------------------------------------
 * 版权所有 2016-
 * 网站地址:
 * --------------------------------------------------------------------------------------------------
 * Author: 昊
 * Release Date: 2016-7-4
 */
define('IN_HBDATA', true);

// 强制在移动端中显示PC版
if (isset($_REQUEST['mobile'])) {
    setcookie('client', 'pc');
    if ($_COOKIE['client'] != 'pc') $_COOKIE['client'] = 'pc';
}

require (dirname(__FILE__) . '/include/init.php');

// 如果存在搜索词则转入搜索页面
if ($_REQUEST['s']) {
    if ($check->is_search_keyword($keyword = trim($_REQUEST['s']))) {
        require (ROOT_PATH . 'include/search.inc.php');
    } else {
        $hbdata->hbdata_msg($_LANG['search_keyword_wrong']);
    }
}

// 获取关于我们信息
$sql = "SELECT * FROM " . $hbdata->table('page') . " WHERE id = '1'";
$query = $hbdata->query($sql);
$about = $hbdata->fetch_array($query);

// 写入到index数组
$index['about_name'] = $about['page_name'];
$index['about_content'] = $about['description'] ? $about['description'] : $hbdata->hbdata_substr($about['content'], 300, false); // 这里的300数值不能设置得过大，否则会造成程序卡死
$index['about_link'] = $hbdata->rewrite_url('page', '1');
$index['cur'] = true;

// 赋值给模板-meta和title信息
$smarty->assign('page_title', $hbdata->page_title());
$smarty->assign('keywords', $_CFG['site_keywords']);
$smarty->assign('description', $_CFG['site_description']);

// 赋值给模板-导航栏
$smarty->assign('nav_top_list', $hbdata->get_nav('top'));
$smarty->assign('nav_middle_list', $hbdata->get_nav('middle'));
$smarty->assign('nav_bottom_list', $hbdata->get_nav('bottom'));

// 赋值给模板-数据
$smarty->assign('show_list', $hbdata->get_show_list());
$smarty->assign('link', get_link_list());
$smarty->assign('index', $index);
$smarty->assign('recommend_product', $hbdata->get_list('product', 'ALL', $_DISPLAY['home_product'], 'sort DESC'));
$smarty->assign('recommend_article', $hbdata->get_list('article', 'ALL', $_DISPLAY['home_article'], 'sort DESC'));

$smarty->display('index.dwt');

/**
 * 获取友情链接
 * @return array
 */
function get_link_list() {
    $sql = "SELECT * FROM " . $GLOBALS['hbdata']->table('link') . " ORDER BY sort ASC, id ASC";
    $query = $GLOBALS['hbdata']->query($sql);
    while ($row = $GLOBALS['hbdata']->fetch_array($query)) {
        $link_list[] = array (
            "id" => $row['id'],
            "link_name" => $row['link_name'],
            "link_url" => $row['link_url'],
            "sort" => $row['sort']
        );
    }

    return $link_list;
}
?>