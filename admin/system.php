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
include_once (ROOT_PATH . 'include/upload.class.php');

//权限判断
require ('auth.php');

// rec操作项的初始化
$rec = $check->is_rec($_REQUEST['rec']) ? $_REQUEST['rec'] : 'default';

// 赋值给模板
$smarty->assign('cur', 'system');

/**
 * 系统设置
 */
if ($rec == 'default') {
    // CSRF防御令牌生成
    $smarty->assign('token', $firewall->set_token('system'));

    // 赋值给模板
    $smarty->assign('ur_here', $_LANG['system']);
    $smarty->assign('cfg_list_main', get_cfg_list());
    $smarty->assign('cfg_list_display', get_cfg_list('display'));
    $smarty->assign('cfg_list_defined', get_cfg_list('defined'));
    if (file_exists(ROOT_PATH . "include/mail.class.php")) // 判断是否存在邮件模块
        $smarty->assign('cfg_list_mail', get_cfg_list('mail'));

    $smarty->display('system.htm');
}

/**
 * 系统设置数据更新
 */
if ($rec == 'update') {
    // 验证系统语言选择
    if (!preg_match("/^[a-z_]+$/", $_POST['language']))
        $hbdata->hbdata_msg($_LANG['language_wrong'], 'system.php');

    // 上传图片生成
    if ($_FILES['site_logo']['name'] != "") {
        $logo_dir = ROOT_PATH . "theme/" . $_CFG['site_theme'] . "/images/"; // logo上传路径,结尾加斜杠
        $logo = new Upload($logo_dir, ''); // 实例化类文件
        $upfile = $logo->upload_image('site_logo', 'logo'); // 上传的文件域
        $_POST['site_logo'] = $upfile;
    }

    // CSRF防御令牌验证
    $firewall->check_token($_POST['token'], 'system');

    foreach ($_POST as $name => $value) {
        if (is_array($value)) $value = serialize($value);
        $sql = "UPDATE " . $hbdata->table('config') . " SET value = '$value' WHERE name = '$name'";
        $hbdata->query($sql);
    }

    $hbdata->create_admin_log($_LANG['system'] . ': ' . $_LANG['edit_succes']);
    $hbdata->hbdata_msg($_LANG['edit_succes'], 'system.php');
}

/**
 * 获取系统设置列表
 */
function get_cfg_list($tab = 'main') {
    $sql = "SELECT * FROM " . $GLOBALS['hbdata']->table('config') . " WHERE type != 'hidden' AND tab = '$tab' ORDER BY sort ASC";
    $query = $GLOBALS['hbdata']->query($sql);
    while ($row = $GLOBALS['hbdata']->fetch_array($query)) {
        // 预设选项
        if ($row['box'])
            $box = explode(",", $row['box']);

        if ($row['name'] == 'site_logo')
            $row['value'] = $row['value'] ? "theme/" . $GLOBALS['_CFG']['site_theme'] . "/images/" . $row['value'] : '';

        if ($row['name'] == 'language')
            $box = $GLOBALS['hbdata']->get_subdirs(ROOT_PATH . 'languages');

        $cue = $GLOBALS['_LANG'][$row['name'] . '_cue'];

        if ($row['name'] == 'rewrite') {
            // 根据 Web 服务器信息 判断伪静态文件
            if (stristr($_SERVER['SERVER_SOFTWARE'], "Apache")) {
                $rewrite_file = ".htaccess";
            } elseif (stristr($_SERVER['SERVER_SOFTWARE'], "IIS")) {
                $iis_exp = explode("/", $_SERVER['SERVER_SOFTWARE']);
                $iis_ver = $iis_exp['1'];

                if ($iis_ver >= 7.0) {
                    $rewrite_file = "web.config";
                } else {
                    $rewrite_file = "httpd.ini";
                }
            }

            if (stristr($_SERVER['SERVER_SOFTWARE'], "nginx")) {
                $cue = $GLOBALS['_LANG'][$row['name'] . '_cue_nginx'];
            } elseif ($rewrite_file) {
                $cue = preg_replace('/d%/Ums', $rewrite_file, $cue);
            } else {
                $cue = $GLOBALS['_LANG'][$row['name'] . '_cue_other'];
            }
        }

        // 数组类型的设置选项
        if ($row['type'] == 'array') {
            $arr = unserialize($row['value']);
            foreach ((array)$arr as $key=>$v) {
                $value_array[] = array (
                    "value" => $v,
                    "name" => $row['name'] . '[' . $key . ']',
                    "lang" => $GLOBALS['_LANG'][$row['name'] . '_' . $key],
                    "cue" => $GLOBALS['_LANG'][$row['name'] . '_' . $key . '_cue']
                );
            }
        }

        $cfg_list[] = array (
            "value" => $value_array ? $value_array : $row['value'],
            "name" => $row['name'],
            "type" => $row['type'],
            "box" => $box,
            "lang" => $GLOBALS['_LANG'][$row['name']],
            "cue" => $cue
        );
    }

    return $cfg_list;
}
?>