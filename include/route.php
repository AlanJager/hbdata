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

define('ROUTE', true);

//get ReWrite URL
$route = $_REQUEST['route'];

$site_path = str_replace('include/route.php', '', str_replace('\\', '/', __FILE__));
$root_url = str_replace('include', '', dirname('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']));

//format URL
$mark = seo_url($route);

if (is_array($mark)) {
    // 将关键数据赋值到$_REQUEST
    foreach ($mark as $key => $value) {
        if ($value)
            $_REQUEST[$key] = $value;
    }
    // 根据URL加载对应的PHP文件
    require ($site_path . $mark['module'] . '.php');
} else {
    header("Location: " . $root_url);
    exit;
}

/**
 * format URL
 * @param $route ReWrite URL
 * @return mixed
 */
function seo_url($route) {
    //explode URL
    $parts = explode('/', $route);
    $parts[1] = isset($parts[1]) ? $parts[1] : '';
    $parts[2] = isset($parts[2]) ? $parts[2] : '';

    //URL news maps article module
    $parts[0] = $parts[0] == 'news' ? 'article' : $parts[0];

    //get module information
    $module = module();

    //format detail page URL
    if (preg_match("/^([a-z0-9-]+)\.html$/", $parts[0])) {
        $mark['module'] = 'page';
        $mark['unique_id'] = str_replace('.html', '', $parts[0]);
    } elseif (in_array($parts[0], $module['column'])) { //format category or module URL
        if (strpos($route, '.html')) {
            $mark['module'] = $parts[0];
            $mark['unique_id'] = !preg_match("/^([0-9]+)\.html$/", $parts[1]) ? $parts[1] : '';
            $mark['id'] = str_replace('.html', '', basename($route));
        } else {
            $mark['module'] = $parts[0] . '_category';
            if (preg_match("/^o([0-9]+)$/", $parts[1])) {
                $mark['page'] = str_replace('o', '', $parts[1]);
            } else {
                $mark['unique_id'] = $parts[1];
                if (preg_match("/^o([0-9]+)$/", $parts[2])) {
                    $mark['page'] = str_replace('o', '', $parts[2]);
                }
            }
        }
    } elseif (in_array($parts[0], $module['single'])) { // 单一模块URL格式化
        $mark['module'] = $parts[0];
        if (preg_match("/^o([0-9]+)$/", $parts[1])) {
            $mark['page'] = str_replace('o', '', $parts[1]);
        } else {
            $mark['rec'] = $parts[1];
            if (preg_match("/^o([0-9]+)$/", $parts[2]))
                $mark['page'] = str_replace('o', '', $parts[2]);
        }
    }

    return $mark;
}

/**
 * system module
 * @return mixed
 */
function module() {
    global $site_path;
    //read sys file
    $content = file($site_path . 'data/system.hbdata');
    foreach ($content as $line) {
        $line = trim($line);
        if (strpos($line, '//') !== 0) {
            $arr = explode(':', $line);
            $setting[$arr[0]] = explode(',', $arr[1]);
        }
    }

    $module['column'] = $setting['column_module'];
    $module['single'] = $setting['single_module'];

    return $module;
}

