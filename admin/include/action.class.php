<?php
/**
 * hbData
 * --------------------------------------------------------------------------------------------------
 * 版权所有 2016-
 * 网站地址:
 * --------------------------------------------------------------------------------------------------
 * Author: Firery
 * Release Date: 2016-7-4
 */

if(!defined('IN_HBDATA')){
    die('Hacking attempt');
}

/**
 *
 * Class Action
 */
class Action extends Common
{
    /**
     * 初始化工作空间
     * @return mixed
     */
    function hbdata_workspace()
    {
        $menu_list = $this->get_menu_list();
        $workspace['menu_column'] = $menu_list['column'];
        $workspace['menu_single'] = $menu_list['single'];
        $workspace['menu_simple'] = $this->get_menu_page();

        // 可更新数量
        $number = unserialize($GLOBALS['_CFG']['update_number']);
        $number['system'] = $number['update'] + $number['patch'];
        $GLOBALS['smarty']->assign('unum', $number);

        // 计算工作空间高度
        if ($GLOBALS['_MODULE']['all']) {
            $height = (count($menu_list['single']) * 43) + (count($menu_list['column']) * 86) + 280;
        } else {
            $record_count = mysqli_num_rows($this->query("SELECT * FROM " . $this->table('page')));
            $height = $record_count * 43 + 280;
        }
        $height = $height < 550 ? 550 : $height;
        $workspace['height'] = 'height:auto!important;height:'.$height.'px;min-height:'.$height.'px;';

        return $workspace;
    }

    /**
     * 本地站点信息，在线安装时使用
     * @param string $type 类型
     * @return string
     */
    function hbdata_localsite($type = '')
    {
        if ($type) {
            $update_date = unserialize($GLOBALS['_CFG']['update_date']);
            $localsite = $update_date[$type];
        } else {
            $localsite = unserialize($GLOBALS['_CFG']['update_date']);
        }

        $cloud_account = unserialize($GLOBALS['_CFG']['cloud_account']);
        $localsite['cloud_account'] = array('user' => $cloud_account['user'], 'password' => $cloud_account['password']);
        $localsite['url'] = ROOT_URL;
        return urlencode(serialize($localsite));
    }

    /**
     * 用户权限判断
     * @param $user_id
     * @param $shell
     * @param $action_list
     * @return array
     */
    function admin_check($user_id, $shell)
    {
        if (!defined('NO_CHECK')) {
            if ($row = $this->admin_state($user_id, $shell)) {
                $this->admin_ontime(10800);
                if (is_array($row)) {
                    $user = array (
                        'user_id' => $row['user_id'],
                        'user_name' => $row['user_name'],
                        'email' => $row['email'],
                        'action_list' => $row['action_list']
                    );

                    return $user;
                }
            } else {
                $this->hbdata_header(ROOT_URL . ADMIN_PATH . '/login.php');
            }
        }
    }

    /**
     * 用户状态
     * @param $user_id
     * @param $shell
     * @return array|null
     */
    function admin_state($user_id, $shell) {
        $query = $this->select($this->table('admin'), '*', '`user_id` = \'' . $user_id . '\'');
        $user = $this->fetch_array($query);

        // 如果$user则开始比对$shell值
        $check_shell = is_array($user) ? $shell == md5($user['user_name'] . $user['password'] . HBDATA_SHELL) : FALSE;

        // 如果比对$shell吻合，则返回会员信息，否则返回空
        return $check_shell ? $user : NULL;
    }

    /**
     * 登录超时默认为3小时(10800秒)
     * @param string $timeout
     */
    function admin_ontime($timeout = '10800') {
        $ontime = $_SESSION[HBDATA_ID]['ontime'];
        $cur_time = time();
        if ($cur_time - $ontime > $timeout) {
            unset($_SESSION[HBDATA_ID]);
        } else {
            $_SESSION[HBDATA_ID]['ontime'] = time();
        }
    }

    /**
     * 找回密码验证
     * @param $user_id      会员ID
     * @param $code         密码找回码
     * @param int $timeout  默认为24小时(86400秒)
     * @return bool
     */
    function check_password_reset($user_id, $code, $timeout = 86400) {
        if ($GLOBALS['hbdata']->value_exist('admin', 'user_id', $user_id)) {
            $user = $GLOBALS['hbdata']->fetch_array($GLOBALS['hbdata']->select($GLOBALS['hbdata']->table('admin'), '*', "user_id = '$user_id'"));

            // 初始化
            $get_code = substr($code , 0 , 16);
            $get_time = substr($code , 16 , 26);
            $code = substr(md5($user['user_name'] . $user['email'] . $user['password'] . $get_time . $user['last_login'] . HBDATA_SHELL) , 0 , 16);

            // 验证链接有效性
            if (time() - $get_time < $timeout && $code == $get_code) return true;
        }
    }

    /**
     * 获取导航菜单
     * @param string $type          导航类型
     * @param int $parent_id        默认获取一级导航
     * @param int $level            无限极分类层次
     * @param string $current_id    当前页面栏目ID
     * @param array $nav
     * @param string $mark          无限极分类标记
     * @return array
     */
    function get_nav($type = 'middle', $parent_id = 0, $level = 0, $current_id = '', &$nav = array(), $mark = '-') {
        $data = $this->fetch_array_all($this->table('nav'), 'sort ASC');
        foreach ((array) $data as $value) {
            if ($value['parent_id'] == $parent_id && $value['type'] == $type && $value['id'] != $current_id) {
                if ($value['module'] != 'nav') {
                    $value['guide'] = $this->rewrite_url($value['module'], $value['guide']);
                }

                $value['mark'] = str_repeat($mark, $level);
                $nav[] = $value;
                $this->get_nav($type, $value['id'], $level + 1, $current_id, $nav);
            }
        }
        return $nav;
    }

    /**
     * 生成模块后台菜单
     * @return mixed
     */
    function get_menu_list() {
        foreach ((array) $GLOBALS['_MODULE']['column'] as $value) {
            $menu_list['column'][] = array (
                'name_category' => $value . '_category',
                'lang_category' => $GLOBALS['_LANG'][$value . '_category'],
                'name' => $value,
                'lang' => $GLOBALS['_LANG'][$value]
            );
        }

        foreach ((array) $GLOBALS['_MODULE']['single'] as $value) {
            $menu_list['single'][] = array (
                'name' => $value,
                'lang' => $GLOBALS['_LANG'][$value]
            );
        }

        return $menu_list;
    }

    /**
     * 获取有层次的栏目分类，有几层分类就创建几维数组
     * @param int $parent_id        默认获取一级导航
     * @param string $current_id    当前页面栏目ID
     * @return array
     */
    function get_menu_page($parent_id = 0, $current_id = '') {
        $menu_page = array ();
        $query = $this->query("SELECT id, unique_id, parent_id, page_name FROM " . $this->table('page') . " ORDER BY id ASC");
        while ($row = $this->fetch_assoc($query)) {
            $data[] = $row;
        }
        foreach ((array) $data as $value) {
            // $parent_id将在嵌套函数中随之变化
            if ($value['parent_id'] == $parent_id) {
                $value['cur'] = $value['id'] == $current_id ? true : false;

                foreach ($data as $child) {
                    // 筛选下级导航
                    if ($child['parent_id'] == $value['id']) {
                        // 嵌套函数获取子分类
                        $value['child'] = $this->get_menu_page($value['id'], $current_id);
                        break;
                    }
                }
                $menu_page[] = $value;
            }
        }

        return $menu_page;
    }

    /**
     * 获取整站目录数据
     * @param string $module
     * @param string $id
     * @return array
     */
    function get_catalog($module = '', $id = '') {
        // 单页面列表
        foreach ((array) $this->get_page_nolevel() as $row) {
            $catalog[] = array (
                "name" => $row['page_name'],
                "module" => 'page',
                "guide" => $row['id'],
                "cur" => ($module == 'page' && $id == $row['id']) ? true : false,
                "mark" => '-' . $row['mark']
            );
        }

        // 栏目模块
        foreach ((array) $GLOBALS['_MODULE']['column'] as $module_id) {
            $catalog[] = array (
                "name" => $GLOBALS['_LANG']['nav_' . $module_id],
                "module" => $module_id . '_category',
                "cur" => ($module == $module_id . '_category' && $id == 0) ? true : false,
                "guide" => 0
            );
            foreach ($this->get_category_nolevel($module_id , 'category') as $row) {
                $catalog[] = array (
                    "name" => $row['cat_name'],
                    "module" => $module_id . '_category',
                    "guide" => $row['cat_id'],
                    "cur" => ($module == $module_id . '_category' && $id == $row['cat_id']) ? true : false,
                    "mark" => '-' . $row['mark']
                );
            }
        }

        // 简单模块
        foreach ((array) $GLOBALS['_MODULE']['single'] as $module_id) {
            // 不显示的模块
            $no_show = 'plugin';
            if (!in_array($module_id, explode('|', $no_show))) {
                $catalog[] = array (
                    "name" => $GLOBALS['_LANG'][$module_id],
                    "module" => $module_id,
                    "cur" => ($module == $module_id && $id == 0) ? true : false,
                    "guide" => 0
                );
            }
        }

        return $catalog;
    }

    /**
     * 批量移动分类
     * @param $module       模块名称及数据表名
     * @param $checkbox     要批量处理的ID合集
     * @param $new_cate_id  要移动到哪个分类
     */
    function category_move($module, $checkbox, $new_cate_id) {
        $sql_in = $this->create_sql_in($_POST['checkbox']);

        // 移动分类操作
        $this->query("UPDATE " . $this->table($module) . " SET cat_id = '$new_cate_id' WHERE id " . $sql_in);

        $this->create_admin_log($GLOBALS['_LANG']['category_move_batch'] . ': ' . strtoupper($module) . ' ' . addslashes($sql_in));
        $this->hbdata_msg($GLOBALS['_LANG']['category_move_batch_succes'], $module . '.php');
    }

    /**
     * 批量删除
     * @param $module
     * @param $checkbox
     * @param $field_filter
     * @param string $field_image
     */
    function del_all($module, $checkbox, $field_filter, $field_image = '') {
        $sql_in = $this->create_sql_in($_POST['checkbox']);

        // 删除相应图片
        if ($field_image) {
            $sql = "SELECT " . $field_image . " FROM " . $this->table($module) . " WHERE " . $field_filter . " " . $sql_in;
            $query = $this->query($sql);
            while ($row = $this->fetch_array($query)) {
                $this->del_image($row[$field_image]);
            }
        }

        // 从数据库中删除所选内容
        $this->query("DELETE FROM " . $this->table($module) . " WHERE " . $field_filter . " " . $sql_in);

        $this->create_admin_log($GLOBALS['_LANG']['del_all'] . ': ' . strtoupper($module) . ' ' . addslashes($sql_in));
        $this->hbdata_msg($GLOBALS['_LANG']['del_succes'], 'item.php?module=' . $module);
    }

    /**
     * 删除图片
     * @param $image
     */
    function del_image($image) {
        $no_ext = explode(".", $image);
        $image_thumb = $no_ext[0] . '_thumb' . '.' . $no_ext[1];
        @unlink(ROOT_PATH . $image);
        @unlink(ROOT_PATH . $image_thumb);
    }

    /**
     * 获取管理员日志
     * @param $action   管理员操作的内容
     */
    function create_admin_log($action) {
        $create_time = time();
        $ip = $this->get_ip();

        $sql = "INSERT INTO " . $this->table('admin_log') . " (id, create_time, user_id, action ,ip)" . " VALUES (NULL, '$create_time', " .
            $_SESSION[HBDATA_ID]['user_id'] . ", '$action', '$ip')";
        $this->query($sql);
    }

    /**
     * 获取管理员日志
     * @param string $user_id   管理员ID
     * @param string $num       调用日志数量
     * @return array
     */
    function get_admin_log($user_id = '', $num = '') {
        if ($user_id) {
            $where = " WHERE user_id = '$user_id'";
        }
        if ($num) {
            $limit = " LIMIT $num";
        }

        $sql = "SELECT * FROM " . $this->table('admin_log') . $where . " ORDER BY id DESC" . $limit;
        $query = $this->query($sql);
        while ($row = $this->fetch_array($query)) {
            $create_time = date("Y-m-d H:i:s", $row['create_time']);
            $user_name = $this->get_one("SELECT user_name FROM " . $this->table('admin') . " WHERE user_id = " . $row['user_id']);

            $log_list[] = array (
                "id" => $row['id'],
                "create_time" => $create_time,
                "user_name" => $user_name,
                "action" => $row['action'],
                "ip" => $row['ip']
            );
        }

        return $log_list;
    }

    /**
     * 获取当前目录子文件夹
     * @param $dir  要检索的目录
     * @return array
     */
    function get_subdirs($dir) {
        $subdirs = array();
        if (!$handle  = @opendir($dir)) return $subdirs;

        while ($file = @readdir($handle )) {
            if ($file == '.' || $file == '..') continue; // 排除掉当前目录和上一个目录
            $subdirs[] = $file;
        }
        return $subdirs;
    }

    /**
     * 清除缓存及已编译的模板
     * @param $dir  要删除的目录
     * @return int
     */
    function hbdata_clear_cache($dir) {
        $dir = realpath($dir);
        if (!$dir || !@is_dir($dir))
            return 0;
        $handle = @opendir($dir);
        if ($dir[strlen($dir) - 1] != DIRECTORY_SEPARATOR)
            $dir .= DIRECTORY_SEPARATOR;
        while ($file = @readdir($handle)) {
            if ($file != '.' && $file != '..') {
                if (@is_dir($dir . $file) && !is_link($dir . $file))
                    $this->hbdata_clear_cache($dir . $file);
                else
                    @unlink($dir . $file);
            }
        }
        closedir($handle);
    }

    /**
     * 删除目录及目录下所有的子目录和文件
     * @param $dir          要删除的目录
     * @param bool $sub_dir 只删除子目录
     */
    function del_dir($dir, $sub_dir = false) {
        if ($handle = @opendir($dir)) {
            // 删除目录下子目录和文件
            while (false !== ($item = @readdir($handle))) {
                if ($item != '.' && $item != '..') {
                    if (is_dir( "$dir/$item")) {
                        $this->del_dir("$dir/$item");
                    } else {
                        @unlink("$dir/$item");
                    }
                }
            }
            closedir($handle);

            // 删除目录本身
            if (!$sub_dir) @rmdir($dir);
        }
    }

    /**
     * 获取模板信息
     * @param $unique_id        模板ID
     * @param bool $is_mobile   是否是手机版
     * @return mixed
     */
    function get_theme_info($unique_id, $is_mobile = false) {
        $theme_url = $is_mobile ? M_PATH.'/theme/' : 'theme/';
        $content = file(ROOT_PATH . $theme_url . $unique_id . '/style.css');
        foreach ((array) $content as $line) {
            if (strpos($line, '/*') !== false) continue;
            if (strpos($line, '*/') !== false) break;

            $line = preg_replace('/:/', '#', $line, 1); // 使用'#'作为分隔符，避免把网址中的':'也给分割了
            $arr = explode('#', trim($line));
            $key = str_replace(' ', '_', strtolower($arr[0]));
            $info[$key] = trim($arr[1]);
        }
        $info['unique_id'] = $unique_id;
        $info['image'] = ROOT_URL . $theme_url . $unique_id . '/images/screenshot.png';

        return $info;
    }

    /**
     * 给URL自动上http://
     * @param $url  网址
     * @return string
     */
    function auto_http($url) {
        if (strpos($url, 'http://') !== false || strpos($url, 'https://') !== false) {
            $url = trim($url);
        } else {
            $url = 'http://' . trim($url);
        }
        return $url;
    }

    /**
     * 创建IN查询如"IN('1','2')";
     * @param $arr  要处理成IN查询的数组
     * @return string
     */
    function create_sql_in($arr) {
        foreach ((array) $arr as $list) {
            $sql_in .= $sql_in ? ",'$list'" : "'$list'";
        }
        return "IN ($sql_in)";
    }

    /**
     * 后台通用信息提示
     * @param $text         提示的内容
     * @param string $url   提示后要跳转的网址
     * @param string $out   管理员登录操作时的提示页面
     * @param int $time     多久时间跳转
     * @param string $check 删除确认按钮的链接
     */
    function hbdata_msg($text, $url = '', $out = '', $time = 3, $check = '') {
        if (!$text) {
            $text = $GLOBALS['_LANG']['hbdata_msg_success'];
        }

        $GLOBALS['smarty']->assign('ur_here', $GLOBALS['_LANG']['hbdata_msg']);
        $GLOBALS['smarty']->assign('text', $text);
        $GLOBALS['smarty']->assign('url', $url);
        $GLOBALS['smarty']->assign('out', $out);
        $GLOBALS['smarty']->assign('time', $time);
        $GLOBALS['smarty']->assign('check', $check);

        // 根据跳转时间生成跳转提示
        $cue = preg_replace('/d%/Ums', $time, $GLOBALS['_LANG']['hbdata_msg_cue']);
        $GLOBALS['smarty']->assign('cue', $cue);

        $GLOBALS['smarty']->display('hbdata_msg.htm');
        exit();
    }

    /**
     * 编辑模块
     * @param （array）$file         存放data/system.hbdata内容的数组
     * @param string $fd            打开文件的流
     * @param str $module_name      要修改或添加，删除的模块名
     * @param string $action        对模块的操作行为
     */
    function edit_module($module_name,$action,$module_old = ''){
        if($action == 'del') {
            $file = file(ROOT_PATH . 'data/system.hbdata');
            //分三种情况删除
            //module在第一个
            $file[1] = str_replace(':' . $module_name . ",", ':', $file[1]);
            //module在中间
            $file[1] = str_replace(',' . $module_name . ',', ',', $file[1]);
            //module在末尾
            $file[1] = str_replace(',' . $module_name, '', $file[1]);
            //write into file
            $fd = fopen(ROOT_PATH . 'data/system.hbdata', "w") or die("Unable to open file!");
            foreach ($file as $value) {
                fwrite($fd, $value);
            }
            $this->del_lang_file($module_name);
            //删除category表里对应的模块内容
            $sql="DELETE FROM ".$this->table('category')."WHERE category = '".$module_name."'";
            $this->query($sql);
            //删除表
            $sql="DROP TABLE IF EXISTS".$this->table($module_name);
            $this->query($sql);
            //删除nav
            $sql = "DELETE FROM ".$this->table('nav')."WHERE module = '".$module_name."_category'";
            $this->query($sql);
            //删除权限
            $this->del_module_access($module_name);
            fclose($fd);
        }
        //add modele
        elseif($action == 'add') {
            $file = file(ROOT_PATH . 'data/system.hbdata');
            $file[1] = str_replace(':', ':' . $module_name.',', $file[1]);
            //write into file
            $fd = fopen(ROOT_PATH . 'data/system.hbdata', "w") or die("Unable to open file!");
            foreach ($file as $value) {
                fwrite($fd, $value);
            }
            $this->create_table($module_name);
            fclose($fd);
            
            //创建新表
        }
        //alter modele
        elseif($action == 'alter') {
            $file = file(ROOT_PATH . 'data/system.hbdata');
            $file[1] = str_replace($module_old, $module_name, $file[1]);
            //write into file
            $fd = fopen(ROOT_PATH . 'data/system.hbdata', "w") or die("Unable to open file!");
            foreach ($file as $value) {
                fwrite($fd, $value);
            }
            $this->del_lang_file($module_old);
            $sql="DROP TABLE IF EXISTS".$this->table($module_old);
            $this->query($sql);
            $this->create_table($module_name);
            fclose($fd);
        }
    }

    /**
     * 创建新模块的表
     * @param string $sql           数据库操作语句
     * @param str $module_name      模块名
     */
    function create_table($module_name){
        //若之前存在此表则删除
        $sql="DROP TABLE IF EXISTS".$this->table($module_name);
        $this->query($sql);

        //创建新表
        $sql="CREATE TABLE".$this->table($module_name)."(
        `id` mediumint(8) unsigned NOT NULL auto_increment,
        `cat_id` smallint(5) NOT NULL default '0',
        `title` varchar(150) NOT NULL default '',
        `defined` text NOT NULL,
        `content` longtext NOT NULL,
        `image` varchar(255) NOT NULL default '',
        `keywords` varchar(255) NOT NULL default '',
        `add_time` int(10) unsigned NOT NULL default '0',
        `click` smallint(6) unsigned NOT NULL default '0',
        `description` varchar(255) NOT NULL default '',
        `sort` tinyint(3) unsigned NOT NULL default '0',
        PRIMARY KEY  (`id`)
      ) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8";
        $this->query($sql);
    }

    /**
     * 增加分类的权限
     * @param $module
     * @param $module_name
     */
    function add_module_access($module, $module_name){
        require (ROOT_PATH  . 'admin/include/PhpRbac/autoload.php');
        $rbac = new PhpRbac\Rbac();
        $id = $rbac->Permissions->titleId('admin/item_category.php');
        $id = $rbac->Permissions->add('admin/item_category.php?module='.$module, $module_name . '分类', $id);
        $rbac->Permissions->add('admin/item_category.php?module='.$module.'&rec=add', '添加' . $module_name . '分类', $id);
        $rbac->Permissions->add('admin/item_category.php?module='.$module.'&rec=insert', '插入' . $module_name . '分类', $id);
        $rbac->Permissions->add('admin/item_category.php?module='.$module.'&rec=update', '更新' . $module_name . '分类', $id);
        $rbac->Permissions->add('admin/item_category.php?module='.$module.'&rec=edit', '编辑' . $module_name . '分类', $id);
        $rbac->Permissions->add('admin/item_category.php?module='.$module.'&rec=del', '删除' . $module_name . '分类', $id);

        $id = $rbac->Permissions->titleId('admin/item.php');
        $id = $rbac->Permissions->add('admin/item.php?module='.$module, $module_name, $id);
        $rbac->Permissions->add('admin/item.php?module='.$module.'&rec=add', '添加' . $module_name, $id);
        $rbac->Permissions->add('admin/item.php?module='.$module.'&rec=insert', '插入' . $module_name, $id);
        $rbac->Permissions->add('admin/item.php?module='.$module.'&rec=update', '更新' . $module_name, $id);
        $rbac->Permissions->add('admin/item.php?module='.$module.'&rec=edit', '编辑' . $module_name, $id);
        $rbac->Permissions->add('admin/item.php?module='.$module.'&rec=del', '删除' . $module_name, $id);
        $rbac->Permissions->add('admin/item.php?module='.$module.'&rec=sort', '筛选' . $module_name, $id);
        $rbac->Permissions->add('admin/item.php?module='.$module.'&rec=set_sort', '设置首页显示' . $module_name, $id);
        $rbac->Permissions->add('admin/item.php?module='.$module.'&rec=del_sort', '取消首页显示' . $module_name, $id);
        $rbac->Permissions->add('admin/item.php?module='.$module.'&rec=action', '批量操作' . $module_name, $id);
        return;
    }

    /**
     * 删除分类的权限
     * @param $module
     */
    function del_module_access($module){
        require (ROOT_PATH  . 'admin/include/PhpRbac/autoload.php');
        $rbac = new PhpRbac\Rbac();
        $id = $rbac->Permissions->titleId('admin/item_category.php?module='.$module);
        $rbac->Permissions->remove($id, true);
        $id = $rbac->Permissions->titleId('admin/item.php?module='.$module);
        $rbac->Permissions->remove($id, true);
    }

    /**
     * 增加单页面的权限
     * @param $parent_id
     * @param $page_name
     */
    function add_page_access($parent_id, $page_name){
        require (ROOT_PATH  . 'admin/include/PhpRbac/autoload.php');
        $rbac = new PhpRbac\Rbac();
        if($parent_id != 0){
            $parent_unique_id = $this->get_one("SELECT unique_id FROM ".$this->table('page')."WHERE id = $parent_id");
            $id = $rbac->Permissions->titleId('admin/page.php?name='.$parent_unique_id);
            $id = $rbac->Permissions->add('admin/page.php?name='.$page_name, '', $id);
        }
        else{
            $id = $rbac->Permissions->titleId('admin/page.php');
            $id = $rbac->Permissions->add('admin/page.php?name='.$page_name, '', $id);
        }
        $rbac->Permissions->add('admin/page.php?name='.$page_name.'&rec=edit', '', $id);
        $rbac->Permissions->add('admin/page.php?name='.$page_name.'&rec=del', '', $id);
    }

    /**
     * 删除页面的权限
     * @param $page_name
     */
    function del_page_access($page_name){
        require (ROOT_PATH  . 'admin/include/PhpRbac/autoload.php');
        $rbac = new PhpRbac\Rbac();
        $id = $rbac->Permissions->titleId('admin/page.php?name='.$page_name);
        $rbac->Permissions->remove($id, true);
    }
    
    /**
     * 为新模块增加语言包
     * @param string $lang          语言包
     * @param str $lang_category     语言包分类
     */
    function add_category_lang($unique_id,$category){

        //后台语言包

        //系统设置
        $system=array(
            "\r\n//系统设置\r\n",
            "\$_LANG['top_add_".$unique_id."'] = '".$category."';\r\n",
            "\$_LANG['display_".$unique_id."'] = '".$category."列表数量';\r\n",
            "\$_LANG['display_home_".$unique_id."'] = '首页展示".$category."数量';\r\n",
            "\$_LANG['defined_".$unique_id."'] = '".$category."自定义属性';\r\n",
            "\$_LANG['defined_".$unique_id."_cue'] = '如\"颜色,尺寸,型号\"中间以英文逗号隔开';\r\n",
            "\$_LANG['sort_".$unique_id."'] = '开始筛选首页".$category."';\r\n",
            "\$_LANG['nav_".$unique_id."'] = '".$category."中心';\r\n",
            "\$_LANG['mobile_display_".$unique_id."'] = '手机版".$category."列表数量';\r\n",
            "\$_LANG['mobile_display_home_".$unique_id."'] = '手机版首页展示".$category."数量';\r\n"
        );

        //**分类
        $cate=array(
            "\r\n//".$category."分类\r\n",
            "\$_LANG['".$unique_id."_category'] = '".$category."分类';\r\n",
            "\$_LANG['".$unique_id."_category_add'] = '添加分类';\r\n",
            "\$_LANG['".$unique_id."_category_edit'] = '编辑".$category."分类';\r\n",
            "\$_LANG['".$unique_id."_category_del'] = '删除".$category."分类';\r\n",
            "\$_LANG['".$unique_id."_category_del_is_parent'] = '\"d%\"不是末级分类或者分类下还存在".$category."，您不能删除';\r\n",
            "\$_LANG['".$unique_id."_category_name'] = '分类名称';\r\n",
            "\$_LANG['".$unique_id."_category_add_succes'] = '添加".$category."分类成功';\r\n",
            "\$_LANG['".$unique_id."_category_edit_succes'] = '编辑".$category."分类成功';\r\n",
        );

        //**中心
        $center=array(
            "\r\n//".$category."中心\r\n",
            "\$_LANG['".$unique_id."'] = '".$category."';\r\n",
            "\$_LANG['".$unique_id."_add'] = '添加".$category."';\r\n",
            "\$_LANG['".$unique_id."_edit'] = '编辑".$category."';\r\n",
            "\$_LANG['".$unique_id."_del'] = '删除".$category."';\r\n",
            "\$_LANG['".$unique_id."_name'] = '".$category."名称';\r\n",
            "\$_LANG['".$unique_id."_defined'] = '自定义属性';\r\n",
            "\$_LANG['".$unique_id."_defined_cue'] = '以英文逗号 , 隔开';\r\n",
            "\$_LANG['".$unique_id."_content'] = '".$category."描述';\r\n",
            "\$_LANG['".$unique_id."_add_succes'] = '添加".$category."成功';\r\n",
            "\$_LANG['".$unique_id."_edit_succes'] = '编辑".$category."成功';\r\n",
            "\$_LANG['".$unique_id."_select_empty'] = '没有选择任何".$category."';\r\n"
        );
        $fd = fopen(ROOT_PATH."/languages/zh_cn/admin/".$unique_id.".lang.php", "w") or die("Unable to open file!");
        fwrite($fd,"<?php\r\n");
        fwrite($fd, "//category\r\n");
        $lang="\$_LANG['".$unique_id."'] = '".$category."';\r\n";
        fwrite($fd, $lang);
        $lang_category="\$_LANG['".$unique_id."_category'] = '".$category."分类"."';\r\n";
        fwrite($fd, $lang_category);

        foreach ($system as $value){
            fwrite($fd, $value);
        }
        foreach ($cate as $value){
            fwrite($fd, $value);
        }
        foreach ($center as $value){
            fwrite($fd, $value);
        }
        fclose($fd);

        //前台语言包
        $lang=array(
            "\$_LANG['".$unique_id."_category'] = '".$category."中心';\r\n",
            "\$_LANG['".$unique_id."_tree'] = '".$category."分类';\r\n",
            "\$_LANG['".$unique_id."_news'] = '".$category."新闻';\r\n",
            "\$_LANG['".$unique_id."_more'] = '查看更多".$category."';\r\n",
            "\$_LANG['".$unique_id."_previous'] = '上一篇';\r\n",
            "\$_LANG['".$unique_id."_next'] = '下一篇';\r\n"
        );
        $fd = fopen(ROOT_PATH."/languages/zh_cn/".$unique_id.".lang.php", "w") or die("Unable to open file!");
        fwrite($fd,"<?php\r\n");
        foreach ($lang as $value){
            fwrite($fd, $value);
        }
        fclose($fd);
    }

    /**
     * 删除模块对应的语言包
     */
    function del_lang_file($unique_id){
        //删除后台
        $file_path_f =ROOT_PATH."/languages/zh_cn/admin/".$unique_id.".lang.php";
        @unlink ($file_path_f);

        //删除前台
        $file_path_b =ROOT_PATH."/languages/zh_cn/".$unique_id.".lang.php";
        @unlink ($file_path_b);
        return;
    }
}

?>