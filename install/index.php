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

require (dirname(__FILE__) . '/include/init.php');

// rec操作项的初始化
$step = $install->is_rec($_REQUEST['step']) ? $_REQUEST['step'] : 'default';

/* 判断是否安装过 */
if (file_exists($system_file)) {
    $title = $_LANG['hbdataphp'] . " &rsaquo; " . $_LANG['lock'];

    $smarty->assign('title', $title);
    $smarty->display('install_lock.htm');
    exit();
}

/**
 * +----------------------------------------------------------
 * 欢迎页面
 * +----------------------------------------------------------
 */
if ($step == 'default') {
    $title = $_LANG['welcome'];

    $smarty->assign('title', $title);
    $smarty->display('index.htm');
}

/**
 * +----------------------------------------------------------
 * 目录和服务器权限检测
 * +----------------------------------------------------------
 */
elseif ($step == 'check') {
    $title = $_LANG['hbdataphp'] . " &rsaquo; " . $_LANG['check'];

    /* 系统信息 */
    $sys_info['os'] = PHP_OS;
    $sys_info['web_server'] = $_SERVER['SERVER_SOFTWARE'];
    $sys_info['php_ver'] = PHP_VERSION;
    $sys_info['mysql_ver'] = extension_loaded('mysql') ? $_LANG['yes'] : $_LANG['no'];
    $sys_info['zlib'] = function_exists('gzclose') ? $_LANG['yes'] : $_LANG['no'];
    $sys_info['timezone'] = function_exists("date_default_timezone_get") ? date_default_timezone_get() : $_LANG['no_timezone'];
    $sys_info['socket'] = function_exists('fsockopen') ? $_LANG['yes'] : $_LANG['no'];
    $sys_info['gd'] = extension_loaded("gd") ? $_LANG['yes'] : $_LANG['no'];

    /* 检查目录 */
    $check_dirs = array (
        'cache',
        'cache/admin',
        'cache/m',
        'data',
        'data/slide',
        'data/backup',
        'images/article',
        'images/product',
        'images/upload'
    );

    foreach ($check_dirs as $dir) {
        $full_dir = ROOT_PATH . $dir;

        // 如果目录不存在则建立
        if (!file_exists($full_dir))
            mkdir($full_dir, 0777);

        $check_writeable = $install->check_writeable($full_dir);
        if ($check_writeable == '1') {
            $if_write = "<b class='write'>" . $_LANG['write'] . "</b>";
        } elseif ($check_writeable == '0') {
            $if_write = "<b class='noWrite'>" . $_LANG['no_write'] . "</b>";
            $no_write = true;
        } elseif ($check_writeable == '2') {
            $if_write = "<b class='noWrite'>" . $_LANG['not_exist'] . "</b>";
            $no_write = true;
        }

        $writeable[] = array (
            "dir" => $dir,
            "if_write" => $if_write
        );
    }

    // 根据 Web 服务器 信息配置伪静态文件
    if (strpos($sys_info['web_server'], 'Apache') !== false) {
        $rewrite_file = ".htaccess.txt";
    } elseif (strpos($sys_info['web_server'], 'nginx') !== false) {
        $rewrite_file = "nginx.txt";
    } elseif (strpos($sys_info['web_server'], 'IIS') !== false) {
        $iis_exp = explode("/", $sys_info['web_server']);
        $iis_ver = $iis_exp['1'];

        if ($iis_ver >= 7.0) {
            $rewrite_file = "web.config.txt";
        } else {
            $rewrite_file = "httpd.ini.txt";
        }
    }

    // 复制rewrite文件到站点根目录
    if ($rewrite_file) {
        $source = ROOT_PATH . "install/rewrite/" . $rewrite_file;
        $destination = ROOT_PATH . $rewrite_file;
        $m_destination = M_PATH . $rewrite_file;
        @copy($source, $destination);
        if (strpos($sys_info['web_server'], 'nginx') === false)
            @copy($source, $m_destination);
    }

    $smarty->assign('title', $title);
    $smarty->assign('sys_info', $sys_info);
    $smarty->assign('writeable', $writeable);
    $smarty->assign('no_write', $no_write);
    $smarty->display('check.htm');
}

/**
 * +----------------------------------------------------------
 * 安装配置
 * +----------------------------------------------------------
 */
elseif ($step == 'setting') {
    $title = $_LANG['hbdataphp'] . " &rsaquo; " . $_LANG['setting'];

    $smarty->assign('title', $title);
    $smarty->display('setting.htm');
}

/**
 * +----------------------------------------------------------
 * 安装配置
 * +----------------------------------------------------------
 */
elseif ($step == 'install') {
    // 生成config文件内容
    $config_str = "<?php\n";
    $config_str .= "/**\n";
    $config_str .= " * hbdata\n";
    $config_str .= " * --------------------------------------------------------------------------------------------------\n";
    $config_str .= " * \n";
    $config_str .= " * 网站地址:\n";
    $config_str .= " * --------------------------------------------------------------------------------------------------\n";
    $config_str .= " * --------------------------------------------------------------------------------------------------\n";
    $config_str .= " * Author: AlanJager\n";
    $config_str .= " * Release Date: 2016-07-05\n";
    $config_str .= " */\n\n";

    $config_str .= "// database host\n";
    $config_str .= '$dbhost   = "' . $_POST['dbhost'] . '";' . "\n\n";

    $config_str .= "// database name\n";
    $config_str .= '$dbname   = "' . $_POST['dbname'] . '";' . "\n\n";

    $config_str .= "// database username\n";
    $config_str .= '$dbuser   = "' . $_POST['dbuser'] . '";' . "\n\n";

    $config_str .= "// database password\n";
    $config_str .= '$dbpass   = "' . $_POST['dbpass'] . '";' . "\n\n";

    $config_str .= "// table prefix\n";
    $config_str .= '$prefix   = "' . $_POST['prefix'] . '";' . "\n\n";

    $config_str .= "// charset\n";
    $config_str .= "define('HBDATA_CHARSET','utf-8');" . "\n\n";

    $config_str .= "// administrator path\n";
    $config_str .= "define('ADMIN_PATH','admin');\n\n";

    $config_str .= "// mobile path\n";
    $config_str .= "define('M_PATH','m');\n\n\n";

    $config_str .= "?>";

    // 生成config.php文件
    file_put_contents($hbdataphp_config, $config_str);

    // 嵌入config配置文件
    include_once ($hbdataphp_config);
    
    //生成RBAC配置文件
    $dbAdapterString = '$adapter="mysqli";';
    $dbNameString = '$dbname="' . $_POST['dbname'] . '";';
    $data =	 '<?php

' . $dbAdapterString . '
$host="' . $_POST['dbhost'] . '";

' . $dbNameString . '
$tablePrefix = "' . $_POST['prefix'] . '";

$user="' . $_POST['dbuser'] . '";
$pass="' . $_POST['dbpass'] . '";

';

    $dbConnFile = ROOT_PATH . 'admin/include/PhpRbac/database/database.config';

    file_put_contents($dbConnFile, $data);

    $currentOS = strtoupper(substr(PHP_OS, 0, 3));

    if ($currentOS != 'WIN') {
        chmod($dbConnFile, 0644);
    }

    // 实例化RBAC
    require_once (ROOT_PATH  . 'admin/include/PhpRbac/autoload.php');
    require (ROOT_PATH . 'admin/include/PhpRbac/src/PhpRbac/Rbac.php');
    $rbac = new PhpRbac\Rbac();

    // execute '$rbac->reset(true);'
    $rbac->reset(true);

    // 检查表单
    if (!@$link = mysql_connect($dbhost, $dbuser, $dbpass)) {
        $cue = $_LANG['cue_connect'];
    } elseif (!$_POST['username']) {
        $cue = $_LANG['cue_username_empty'];
    } elseif (!$install->is_username($_POST['username'])) {
        $cue = $_LANG['cue_username_wrong'];
    } elseif (!$_POST['password']) {
        $cue = $_LANG['cue_password_empty'];
    } elseif (!$install->is_password($_POST['password'])) {
        $cue = $_LANG['cue_password_wrong'];
    } elseif (!$_POST['password_confirm']) {
        $cue = $_LANG['cue_password_confirm_empty'];
    } elseif ($_POST['password'] != $_POST['password_confirm']) {
        $cue = $_LANG['cue_password_confirm_wrong'];
    }

    // AJAX验证表单
    if ($_REQUEST['do'] == 'callback') {
        if ($cue) {
            echo '<p class="cue"><strong>' . $_LANG['wrong'] . '：</strong>' . $cue . '</p>';
        }
        exit;
    }

    // 无错误信息，完成安装
    if (!$cue) {
        mysql_query("CREATE DATABASE IF NOT EXISTS `$dbname` default charset utf8 COLLATE utf8_general_ci");
        mysql_select_db($dbname);

        // 读取SQL文件到一个字符串中
        $sql = file_get_contents(I_PATH . 'data/backup/hbdataphp.sql');

        // 进行安装的常规替换
        $sql = preg_replace('/hbdata_/Ums', "$prefix", $sql);

        // 进行安装的常规替换
        $sql_head = "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n";
        $sql_head .= "SET time_zone = '+00:00';\n\n\n";

        $sql_head .= "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\n";
        $sql_head .= "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\n";
        $sql_head .= "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\n";
        $sql_head .= "/*!40101 SET NAMES utf8 */;\n\n";

        $sql = $sql_head . $sql;

        // 生成管理员
        $username = $_POST['username'];
        $password = md5($_POST['password']);
        $add_time = time();

        // 导入数据
        $install->sql_execute($sql);

        /* 写入 hash_code，做为网站唯一性密钥 */
        $hash_code = md5(md5(time()) . md5(md5(ROOT_URL . $dbhost . $dbname . $dbuser . $dbpass)));

        // 初始化数据
        $table_admin = $prefix . "admin";
        $table_config = $prefix . "config";
        $build_date = time();
        $hbdataphp_version = $install->get_one("SELECT value FROM " . $table_config . " WHERE name = 'hbdataphp_version'");
        $date = substr(trim($hbdataphp_version), -8);
        $update_date = 'a:2:{s:6:"system";a:2:{s:6:"update";i:' . $date . ';s:5:"patch";i:' . $date . ';}s:6:"module";a:2:{s:7:"article";i:' . $date . ';s:7:"product";i:' . $date . ';}}';

        // 初始化管理员账号
        $install->sql_execute("UPDATE $table_admin SET user_name = '$username', password = '$password', email = '', add_time = '$add_time' WHERE user_id = '1'");

        // 初始化系统信息
        $install->sql_execute("UPDATE $table_config SET value = '0' WHERE name = 'rewrite'");
        $install->sql_execute("UPDATE $table_config SET value = '$build_date' WHERE name = 'build_date'");
        $install->sql_execute("UPDATE $table_config SET value = '$hash_code' WHERE name = 'hash_code'");
        $install->sql_execute("UPDATE $table_config SET value = '$update_date' WHERE name = 'update_date'");
        $install->sql_execute("UPDATE $table_config SET value = '' WHERE name = 'cloud_account'");

        $_SESSION['username'] = $_POST['username'];

        header("Location: index.php?step=finish");
        exit;
    }
}

/**
 * +----------------------------------------------------------
 * 完成安装
 * +----------------------------------------------------------
 */
elseif ($step == 'finish') {
    // 生成system.hbdata文件
    $content .= "// hbdata INSTALLED\r\n";
    $content .= "column_module:product,article\r\n";
    $content .= "single_module:\r\n";
    file_put_contents($system_file, $content);

    $title = $_LANG['hbdataphp'] . " &rsaquo; " . $_LANG['finish'];

    $smarty->assign('title', $title);
    $smarty->assign('username', $_SESSION['username']);
    $smarty->display('finish.htm');
}