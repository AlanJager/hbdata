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
$id = $firewall->get_legal_id('article', $_REQUEST['id'], $_REQUEST['unique_id']);
$cat_id = $hbdata->get_one("SELECT cat_id FROM " . $hbdata->table('article') . " WHERE id = '$id'");
$parent_id = $hbdata->get_one("SELECT parent_id FROM " . $hbdata->table('article_category') . " WHERE cat_id = '" . $cat_id . "'");
if ($id == -1)
    $hbdata->hbdata_msg($GLOBALS['_LANG']['page_wrong'], ROOT_URL);
    
/* 获取详细信息 */
$query = $hbdata->select($hbdata->table('article'), '*', '`id` = \'' . $id . '\'');
$article = $hbdata->fetch_array($query);

// 格式化数据
$article['add_time'] = date("Y-m-d", $article['add_time']);

// 格式化自定义参数
foreach (explode(',', $article['defined']) as $row) {
    $row = explode('：', str_replace(":", "：", $row));

    if ($row['1']) {
        $defined[] = array (
            "arr" => $row['0'],
            "value" => $row['1']
        );
    }
}

// 访问统计
$click = $article['click'] + 1;
$hbdata->query("update " . $hbdata->table('article') . " SET click = '$click' WHERE id = '$id'");

// 赋值给模板-meta和title信息
$smarty->assign('page_title', $hbdata->page_title('article_category', $cat_id, $article['title']));
$smarty->assign('keywords', $article['keywords']);
$smarty->assign('description', $article['description']);

// 赋值给模板-导航栏
$smarty->assign('nav_top_list', $hbdata->get_nav('top'));
$smarty->assign('nav_middle_list', $hbdata->get_nav('middle', '0', 'article_category', $cat_id, $parent_id));
$smarty->assign('nav_bottom_list', $hbdata->get_nav('bottom'));

// 赋值给模板-数据
$smarty->assign('ur_here', $hbdata->ur_here('article_category', $cat_id, $article['title']));
$smarty->assign('article_category', $hbdata->get_category('article_category', 0, $cat_id));
$smarty->assign('lift', $hbdata->lift('article', $id, $cat_id));
$smarty->assign('article', $article);
$smarty->assign('defined', $defined);

$smarty->display('article.dwt');
?>