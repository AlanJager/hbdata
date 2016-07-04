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

require (dirname(__FILE__) . '/include/init.php');

// 验证并获取合法的ID，如果不合法将其设定为-1
$id = $firewall->get_legal_id('page', $_REQUEST['id'], $_REQUEST['unique_id']);
if ($id == -1)
    $hbdata->hbdata_msg($GLOBALS['_LANG']['page_wrong'], ROOT_URL);
    
    // 获取单页面信息
$page = get_page_info($id);
$top_id = $page['parent_id'] == 0 ? $id : $page['parent_id'];

// 赋值给模板-meta和title信息
$smarty->assign('page_title', $hbdata->page_title('page', '', $page['page_name']));
$smarty->assign('keywords', $page['keywords']);
$smarty->assign('description', $page['description']);

// 赋值给模板-导航栏
$smarty->assign('nav_top_list', $hbdata->get_nav('top'));
$smarty->assign('nav_middle_list', $hbdata->get_nav('middle', '0', 'page', $id));
$smarty->assign('nav_bottom_list', $hbdata->get_nav('bottom'));

// 赋值给模板-数据
$smarty->assign('ur_here', $hbdata->ur_here('page', '', $page['page_name']));
$smarty->assign('page_list', $hbdata->get_page_list($top_id, $id));
$smarty->assign('top', get_page_info($top_id));
$smarty->assign('page', $page);
if ($top_id == $id)
    $smarty->assign("top_cur", 'top_cur');

$smarty->display('page.dwt');

/**
 * 获取单页面信息
 * @param int $id
 * @return mixed
 */
function get_page_info($id = 0) {
    $query = $GLOBALS['hbdata']->select($GLOBALS['hbdata']->table('page'), '*', '`id` = \'' . $id . '\'');
    $page = $GLOBALS['hbdata']->fetch_array($query);

    if ($page) {
        $page['url'] = $GLOBALS['hbdata']->rewrite_url('page', $page['id']);
    }

    return $page;
}
?>