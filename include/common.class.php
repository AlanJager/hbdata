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

if (!defined('IN_HBDATA')) {
    die('Accident occured, please try again.');
}
/**
 * 系统通用操作
 * @name Common
 * @version v1.0
 * @author AlanJager
 */
class Common extends DbMysql
{
    /**
     * check dir status
     * @param $dir
     * @return string
     */
    function dir_status($dir) {
        // dir exists?
        if (file_exists($dir)) {
            // could write dir?
            if ($fp = @fopen("$dir/test.txt", 'w')) {
                @fclose($fp);
                @unlink("$dir/test.txt");
                $status = 'write';
            } else {
                $status = 'exist';
            }
        } else {
            $status = 'no_exist';
        }
        return $status;
    }

    /**
     * get child module belong to cur module
     * @param $table
     * @param int $parent_id
     * @param string $child
     * @param string $module
     * @return $child_id
     */
    function hbdata_child_id($table, $module = '', $parent_id = 0, &$child_id = '')
    {
        if ($module != '') {
            $data = $this->fetch_array_all($this->table($table), 'sort ASC', 'category=\'' . $module . '\'');
        } else {
            $data = $this->fetch_array_all($this->table($table), 'sort ASC');
        } 
        
//        return $data;
        foreach ((array) $data as $value) {
            if ($value['parent_id'] == $parent_id) {
                $child_id .= ',' . $value['cat_id'];
                $this->hbdata_child_id($table, $module, $value['cat_id'], $child_id);
            }
        }
        return $child_id;
    }

    /**
     * redirect user to the aimed url
     * @param string $url
     */
    function hbdata_header($url)
    {
        header("Location: " . $url);
        exit();
    }

    /**
     * system module
     */
    function hbdata_module()
    {
        $setting = $this->read_system();
        $module['column'] = array_filter($setting['column_module']);
        $module['single'] = array_filter($setting['single_module']);
        $module['all'] = array_merge($module['column'], $module['single']);

        //read language file
        $lang_path = ROOT_PATH . 'languages/' . (defined('IS_ADMIN') ? 'zh_cn/admin/' : $GLOBALS['_CFG']['language'] . '/');
        $lang_list = glob($lang_path . '*.lang.php');
        if (is_array($lang_list))
            foreach ($lang_list as $lang)
                $module['lang'][] = $lang;

        // init sys
        $init_list = glob(ROOT_PATH . 'include/' . '*.init.php');
        if (is_array($init_list))
            foreach ($init_list as $init)
                $module['init'][] = $init;

        // get module to start status
        foreach ((array) $module['all'] as $module_id) {
            $_OPEN[$module_id] = true;
        }
        $module['open'] = $_OPEN;

        return $module;
    }

    /**
     * generate qq client info
     * @param $infos
     * @return array $imlist
     */
    function hbdata_qq($infos)
    {
        $infos_explode = explode(',', $infos);
        $im_list = array();
        foreach ((array) $infos_explode as $value) {
            if (strpos($value, '/') !== false) {
                $arr = explode('/', $value);
                $list['number'] = $arr['0'];
                $list['nickname'] = $arr['1'];
                $im_list[] = $list;
            } else {
                $im_list[] = $value;
            }
        }

        return $im_list;
    }


    function hbdata_substr($str, $length, $clear_space = true, $charset = HBDATA_CHARSET)
    {
        $str = trim($str); // 清除字符串两边的空格
        $str = strip_tags($str, ""); // 利用php自带的函数清除html格式
        $str = preg_replace("/\r\n/", "", $str);
        $str = preg_replace("/\r/", "", $str);
        $str = preg_replace("/\n/", "", $str);
        // 判断是否清除空格
        if ($clear_space) {
            $str = preg_replace("/\t/", "", $str);
            $str = preg_replace("/ /", "", $str);
            $str = preg_replace("/&nbsp;/", "", $str); // 匹配html中的空格
        }
        $str = trim($str); // 清除字符串两边的空格

        if (function_exists("mb_substr")) {
            $substr = mb_substr($str, 0, $length, $charset);
        } else {
            $c['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
            $c['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
            preg_match_all($c[$charset], $str, $match);
            $substr = join("", array_slice($match[0], 0, $length));
        }

        return $substr;
    }

    /**
     * all connections of url to module and generate cur module's url
     */
    function hbdata_url()
    {
        $module = $this->hbdata_module();
        foreach ((array) $module['column'] as $module_id)
            $url[$module_id] = $this->rewrite_url($module_id . '_category');
        foreach ((array) $module['single'] as $module_id)
            $url[$module_id] = $this->rewrite_url($module_id);

        // cart url
        $url['cart'] = $this->rewrite_url('order', 'cart');

        // user common url
        foreach (explode('|', 'login|register|logout|order|order_list') as $value)
            $url[$value] = $this->rewrite_url('user', $value);

        // url of cur module's child
        if ($GLOBALS['subbox']['sub']) // use column value to split url
            foreach (explode('|', $GLOBALS['subbox']['sub']) as $value)
                $url[$value] = $this->rewrite_url($GLOBALS['subbox']['module'], $value);

        return $url;
    }

    /**
     * get module category for several dimension array
     * @param $table
     * @param int $parent_id
     * @param string $current_id
     * @param string $module
     * @return array
     */
    function get_category($table, $parent_id = 0, $current_id = '', $module = '') {
        $category = array ();

        if ($module != '') {
            $data = $this->fetch_array_all($this->table($table), 'sort ASC', 'category=\'' . $module . '\'');
        } else {
            $data = $this->fetch_array_all($this->table($table), 'sort ASC');
        }
        
        foreach ((array) $data as $value) {
            // $parent_id将在嵌套函数中随之变化
            if ($value['parent_id'] == $parent_id) {
                $value['url'] = $this->rewrite_url($table, $value['cat_id']);
                $value['cur'] = $value['cat_id'] == $current_id ? true : false;

                foreach ($data as $child) {
                    // filter next category
                    if ($child['parent_id'] == $value['cat_id']) {
                        // get child category sorted
                        $value['child'] = $this->get_category($table, $value['cat_id'], $current_id);
                        break;
                    }
                }
                $category[] = $value;
            }
        }

        return $category;
    }

    /**
     * get goods sorts without layers and save to one dimension array using $mark to diff them
     * @param $table
     * @param $category
     * @param int $parent_id
     * @param int $level
     * @param string $current_id
     * @param array $category_nolevel
     * @param string $mark
     * @return array
     */
    function get_category_nolevel($category, $table, $parent_id = 0, $level = 0, $current_id = '', &$category_nolevel = array(), $mark = '-') {
        $data = $this->fetch_array_all_by_category($this->table($table), $category, 'sort ASC');
        foreach ((array) $data as $value) {
            if ($value['parent_id'] == $parent_id && $value['cat_id'] != $current_id) {
                $value['url'] = $this->rewrite_url($table, $value['cat_id']);
                $value['mark'] = str_repeat($mark, $level);
                $category_nolevel[] = $value;
                $this->get_category_nolevel($category, $table, $value['cat_id'], $level + 1, $current_id, $category_nolevel);
            }
        }

        return $category_nolevel;
    }

    /**
     * get website information
     * @return mixed
     */
    function get_config() {
        $query = $this->select_all($this->table('config'));
        while ($row = $this->fetch_array($query)) {
            $config[$row['name']] = $row['value'];
        }
        if ($config['qq'] && !defined('IS_ADMIN')) {
            $config['qq'] = $this->hbdata_qq($config['qq']);
        }
        $config['root_url'] = ROOT_URL;
        //$config['m_url'] = M_URL;
        return $config;
    }

    /**
     * get log first/last log info
     * @param $log
     * @param bool $desc set true for last record
     * @return mixed
     */
    function get_first_log($log, $desc = false)
    {
        $log_array = explode(',', $log);
        $log = $desc ? ($log_array[1] ? $log_array[1] : $log_array[0]) : $log_array[0];
        return $log;
    }

    /**
     * get real ip address
     * @return string
     */
    function get_ip()
    {
        static $ip;
        if (isset($_SERVER)) {
            if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
                $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
            } else if (isset($_SERVER["HTTP_CLIENT_IP"])) {
                $ip = $_SERVER["HTTP_CLIENT_IP"];
            } else {
                $ip = $_SERVER["REMOTE_ADDR"];
            }
        } else {
            if (getenv("HTTP_X_FORWARDED_FOR")) {
                $ip = getenv("HTTP_X_FORWARDED_FOR");
            } else if (getenv("HTTP_CLIENT_IP")) {
                $ip = getenv("HTTP_CLIENT_IP");
            } else {
                $ip = getenv("REMOTE_ADDR");
            }
        }

        if (preg_match('/^(([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]).){3}([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/', $ip)) {
            return $ip;
        } else {
            return '127.0.0.1';
        }
    }

    /**
     * @param $module
     * @param string $cat_id category id
     * @param string $num invoke num
     * @param string $sort
     * @return array
     */
    function get_list($module, $cat_id = '', $num = '', $sort = '') {
        $where = $cat_id == 'ALL' ? '' : " WHERE cat_id IN (" . $cat_id . $this->hbdata_child_id('category', $module, $cat_id) . ")";
        $sort = $sort ? $sort . ',' : '';
        $limit = $num ? ' LIMIT ' . $num : '';

        $sql = "SELECT * FROM " . $this->table($module) . $where . " ORDER BY " . $sort . "id DESC" . $limit;
        $query = $this->query($sql);
        while ($row = $this->fetch_array($query)) {
            $item['id'] = $row['id'];
            if ($row['title']) $item['title'] = $row['title'];
            if ($row['name']) $item['name'] = $row['name'];
            if (!empty($row['price'])) $item['price'] = $row['price'] > 0 ? $this->price_format($row['price']) : $GLOBALS['_LANG']['price_discuss'];
            if ($row['click']) $item['click'] = $row['click'];

            $item['add_time'] = date("Y-m-d", $row['add_time']);
            $item['add_time_short'] = date("m-d", $row['add_time']);
            $item['description'] = $row['description'] ? $row['description'] : $this->hbdata_substr($row['content'], 320);
            $item['image'] = $row['image'] ? ROOT_URL . $row['image'] : '';
            $image = explode(".", $row['image']);
            $item['thumb'] = ROOT_URL . $image[0] . "_thumb." . $image[1];
            $item['url'] = $this->rewrite_url($module, $row['id']);

            $list[] = $item;
        }

        return $list;
    }

    /**
     * get aimed page info after pager operation
     * @param int $parent_id
     * @param string $current_id
     * @return array
     */
    function get_page_list($parent_id = 0, $current_id = '')
    {
        $page_list = array ();
        $data = $this->fetch_array_all($this->table('page'), 'sort ASC');
        foreach ((array) $data as $value) {
            // $parent_id将在嵌套函数中随之变化
            if ($value['parent_id'] == $parent_id) {
                $value['url'] = $this->rewrite_url('page', $value['id']);
                $value['cur'] = $value['id'] == $current_id ? true : false;

                foreach ($data as $child) {
                    // 筛选下级单页面
                    if ($child['parent_id'] == $value['id']) {
                        // 嵌套函数获取子分类
                        $value['child'] = $this->get_page_list($value['id'], $current_id);
                        break;
                    }
                }
                $page_list[] = $value;
            }
        }

        return $page_list;
    }


    /**
     * get page info without layers
     * @param int $parent_id
     * @param int $level
     * @param string $current_id
     * @param array $page_nolevel
     * @param string $mark
     * @return array
     */
    function get_page_nolevel($parent_id = 0, $level = 0, $current_id = '', &$page_nolevel = array(), $mark = '-') {
        $data = $this->fetch_array_all($this->table('page'));
        foreach ((array) $data as $value) {
            if ($value['parent_id'] == $parent_id && $value['id'] != $current_id) {
                $value['url'] = $this->rewrite_url('page', $value['id']);
                $value['mark'] = str_repeat($mark, $level);
                $value['level'] = $level;
                $page_nolevel[] = $value;
                $this->get_page_nolevel($value['id'], $level + 1, $current_id, $page_nolevel);
            }
        }
        return $page_nolevel;
    }

    /**
     * get plugin id
     * @param $unique_id
     * @return array $plugin
     */
    function get_plugin($unique_id)
    {
        $plugin = $this->fetch_array($this->select($this->table('plugin'), '*', '`unique_id` = \'' . $unique_id . '\''));
        if ($plugin['config'])
            $plugin['config'] = unserialize($plugin['config']);

        return $plugin;
    }

    /**
     * get slider images
     * @param string $type
     * @return array
     */
    function get_show_list($type = 'pc') {
        if ($type) {
            $where = " WHERE type = '$type'";
        }
        $sql = "SELECT * FROM " . $this->table('show') . $where . " ORDER BY sort ASC, id ASC";
        $query = $this->query($sql);
        while ($row = $this->fetch_array($query)) {
            $image = explode('.', basename($row['show_img']));
            $thumb = $GLOBALS['images_dir'] . $GLOBALS['thumb_dir'] . $image['0'] . "_thumb." . $image['1'];

            $show_list[] = array (
                "id" => $row['id'],
                "show_name" => $row['show_name'],
                "show_link" => $row['show_link'],
                "show_img" => ROOT_URL . $row['show_img'],
                "thumb" => ROOT_URL . $thumb,
                "sort" => $row['sort']
            );
        }
        return $show_list;
    }

    /**
     * get alias
     * @param $module
     * @param $id
     * @return bool|string
     */
    function get_unique($module, $id) {
        $filed = $module == 'page' ? id : cat_id;
        $table_module = $module;

        // 非单页面和分类模型下获取分类ID
        if (!strpos($module, 'category') && $module != 'page') {
            $id = $this->get_one("SELECT cat_id FROM " . $this->table($module) . " WHERE id = " . $id);
            $table_module = $module . '_category';
        }

        $unique_id = $this->get_one("SELECT unique_id FROM " . $this->table($table_module) . " WHERE " . $filed . " = " . $id);

        // 把分类页和列表的别名统一
        $module = preg_replace("/\_category/", '', $module);

        // 伪静态时使用的完整别名
        if ($module == 'page') {
            $unique = $unique_id;
        } elseif ($module == 'article') {
            $unique = $unique_id ? '/' . $unique_id : $unique_id;
            $unique = 'news' . $unique;
        } else {
            $unique = $unique_id ? '/' . $unique_id : $unique_id;
            $unique = $module . $unique;
        }

        return $unique;
    }

    /**
     * get previous module and next module for cur module
     * @param $module
     * @param $id
     * @param $cat_id
     * @return mixed
     */
    function lift($module, $id, $cat_id)
    {
        $field = $this->field_exist($module, 'title') ? 'title' : 'name'; // title or name
        $screen = $cat_id ? " AND cat_id = $cat_id" : ''; // if has different type sort

        // previous item
        $lift['previous'] = $this->fetch_assoc($this->query("SELECT id, " . $field . " FROM " . $this->table($module) . " WHERE id > $id" . $screen . " ORDER BY id ASC"));
        if ($lift['previous']) $lift['previous']['url'] = $this->rewrite_url($module, $lift['previous']['id']);
        // next item
        $lift['next'] = $this->fetch_assoc($this->query("SELECT id, " . $field . " FROM " . $this->table($module) . " WHERE id < $id" . $screen . " ORDER BY id DESC"));
        if ($lift['next']) $lift['next']['url'] = $this->rewrite_url($module, $lift['next']['id']);

        return $lift;
    }

    /**
     * get result into limit record for each page
     * @param $table table name
     * @param int $page_size num of each page
     * @param $page cur page num
     * @param string $page_url page url
     * @param string $where search condition
     * @param string $get assignment from url address
     * @param bool $close_rewrite close fake static
     * @return string
     */
    function pager($table, $page_size = 10, $page, $page_url = '', $where = '', $get = '', $close_rewrite = false)
    {
        $sql = "SELECT * FROM " . $this->table($table) . $where;
        $record_count = mysql_num_rows($this->query($sql));

        // set page style sheet
        if (!defined('IS_ADMIN') && $GLOBALS['_CFG']['rewrite'] && !$close_rewrite) {
            $get_page = '/o';
            $get = preg_replace('/&/', '?', $get, 1); // 将起始参数标记改为'?'
            $get = '/' . $get; // 起始参数前加'/'
        } else {
            $get_page = strpos($page_url, '?') !== false ? '&page=' : '?page=';
        }

        $page_count = ceil($record_count / $page_size);
        $first = $page_url . $get_page . '1' . $get;
        $previous = $page_url . $get_page . ($page > 1 ? $page - 1 : 0) . $get;
        $next = $page_url . $get_page . ($page_count > $page ? $page + 1 : 0) . $get;
        $last = $page_url . $get_page . $page_count . $get;

        $pager = array (
            "record_count" => $record_count,
            "page_size" => $page_size,
            "page" => $page,
            "page_count" => $page_count,
            "previous" => $previous,
            "next" => $next,
            "first" => $first,
            "last" => $last
        );

        $start = ($page - 1) * $page_size;
        $limit = " LIMIT $start, $page_size";

        $GLOBALS['smarty']->assign('pager', $pager);

        return $limit;
    }

    /**
     * format goods price
     * @param string $price
     * @return mixed
     */
    function price_format($price = '') {
        $price = number_format($price, $GLOBALS['_CFG']['price_decimal']);
        $price_format = preg_replace('/d%/Ums', $price, $GLOBALS['_LANG']['price_format']);
        return $price_format;
    }

    /**
     * transform system file to array
     * @return mixed
     */
    function read_system() {
        $content = file(ROOT_PATH . 'data/system.hbdata');
        foreach ((array) $content as $line) {
            $line = trim($line);
            if (strpos($line, '//') !== 0) {
                $arr = explode(':', $line);
                $setting[$arr[0]] = explode(',', $arr[1]);
            }
        }
        return $setting;
    }

    /**
     * rewrite url
     * @param $module
     * @param string $value
     * @return string
     */
    function rewrite_url($module, $value = '') {
        // use var type to judge ID or parameter
        if (is_numeric($value)) {
            $id = $value;
        } else {
            $rec = $value;
        }

        if ($GLOBALS['_CFG']['rewrite']) {
            $filename = $module != 'page' ? '/' . $id : '';
            $item = (!strpos($module, 'category') && $id) ? $filename . '.html' : '';
            $url = $this->get_unique($module, $id) . $item . ($rec ? '/' . $rec : '');
        } else {
            $req = $rec ? '?rec=' . $rec : ($id ? '?id=' . $id : '');
            $url = $module . '.php' . $req;
        }

        if ($module == 'mobile') {
            // generate mobile url in PC terminal
            return ROOT_URL . M_PATH;
        } else {
            // different root url for PC and Mobile
            return (defined('IS_MOBILE') ? M_URL : ROOT_URL) . $url;
        }
    }

    /**
     * send mail
     * @param $mailto
     * @param string $subject
     * @param string $body
     * @return bool
     */
    function send_mail($mailto, $subject = '', $body = '')
    {
        if ($GLOBALS['_CFG']['mail_service'] && file_exists(ROOT_PATH . 'include/mail.class.php')) {
            include_once (ROOT_PATH . 'include/mail.class.php');
            include_once (ROOT_PATH . 'include/smtp.class.php');

            $mail = new PHPMailer;                                // 实例化

            $mail->CharSet ="UTF-8";

            //get SMTP ready
            $mail->isSMTP();
            $mail->Host = $GLOBALS['_CFG']['mail_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $GLOBALS['_CFG']['mail_username'];
            $mail->Password = $GLOBALS['_CFG']['mail_password'];
            if ($GLOBALS['_CFG']['mail_ssl'])
                $mail->SMTPSecure = 'ssl';
            $mail->Port = $GLOBALS['_CFG']['mail_port'];

            //user information
            $mail->From = $GLOBALS['_CFG']['mail_username'];
            $mail->FromName = $GLOBALS['_CFG']['site_name'];
            $mail->addAddress($mailto, '');

            $mail->isHTML(true);

            //mail content
            $mail->Subject = $subject;
            $mail->Body    = $body;

            //for device do not support HTML mail
            $mail->AltBody = $GLOBALS['_LANG']['mail_altbody'];

            if($mail->send()) {
                return true;
            }
        } else {
            $subject = "=?UTF-8?B?".base64_encode($subject)."?=";
            //get encode error to be done
            $header  = "From: ".$GLOBALS['_CFG']['site_name']." <".$GLOBALS['_CFG']['email'].">\n";
            $header .= "Return-Path: <".$GLOBALS['_CFG']['email'].">\n";
            //avoid to be treat as trash mail
            $header .= "MIME-Version: 1.0\n";
            $header .= "Content-type: text/html; charset=utf-8\n";
            $header .= "Content-Transfer-Encoding: 8bit\r\n";
            ini_set('sendmail_from', $GLOBALS['_CFG']['email']);
            $body = wordwrap($body, 70);
            //limit words for each line
            if (mail($mailto, $subject, $body, $header))
                return ture;
        }
    }
}