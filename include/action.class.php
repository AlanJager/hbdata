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
 * 系统操作
 * @name Action
 * @version v1.0
 * @author AlanJager
 */
class Action extends Common
{
    /**
     * get navigation bar
     * @param string $type
     * @param int $parent_id
     * @param string $current_module
     * @param string $current_id
     * @param string $current_parent_id
     * @return array
     */
    function get_nav($type = 'middle', $parent_id = 0, $current_module = '', $current_id = '', $current_parent_id = '') {
        $nav = array ();
        $data = $this->fetch_array_all($this->table('nav'), 'sort ASC');
        foreach ((array) $data as $value) {
            // use $parent_id and $type to filter parent navigation bar
            if ($value['parent_id'] == $parent_id && $value['type'] == $type) {
                // customize url use $value['guide'] as url address, if content navigation should use cat_id
                if ($value['module'] == 'nav') {
                    if (strpos($value['guide'], 'http://') === 0 || strpos($value['guide'], 'https://') === 0) {
                        $value['url'] = $value['guide'];
                        // if customize url content http link, open a new window for it
                        $value['target'] = true;
                    } else {
                        $value['url'] = ROOT_URL . $value['guide'];
                        // if customize url content in cur url, highlight it if do not need just annotation it
                        $value['cur'] = strpos($_SERVER['REQUEST_URI'], $value['guide']);
                    }
                } elseif (strpos($value['module'], 'category') != 0) {
                    $module = explode('_', $value['module'])[0];
                    $req = ($value['guide'] == 0) ? '' : '&id='.$value['guide'];
                    $value['url'] = ROOT_URL . 'item_category.php?module=' . $module .$req;
                } else {
                    $value['url'] = $this->rewrite_url($value['module'], $value['guide']);
                    $value['cur'] = $this->hbdata_current($value['module'], $value['guide'], $current_module, $current_id, $current_parent_id);
                }

                foreach ($data as $child) {
                    // filter next navigation bar
                    if ($child['parent_id'] == $value['id']) {
                        $value['child'] = $this->get_nav($type, $value['id']);
                        break;
                    }
                }
                $nav[] = $value;
            }
        }
        return $nav;
    }

    /**
     * highlight cur menu
     * @param $module
     * @param $id
     * @param $current_module
     * @param string $current_id
     * @param string $current_parent_id
     * @return bool
     */
    function hbdata_current($module, $id, $current_module, $current_id = '', $current_parent_id = '') {
        if (($id == $current_id || $id == $current_parent_id) && $module == $current_module)
            return true;
        elseif (!$id && $module == $current_module)
            return true;
    }

    /**
     * cur position of the user
     * @param $module
     * @param string $class
     * @param string $title
     * @return string
     */
    function ur_here($module, $class = '', $title = '') {
        if ($module == 'page') {
            // if single page show the name of it
            $ur_here = $title;
        } elseif (!$class) {
            // module index
            $ur_here = $GLOBALS['_LANG'][$module];
        } else {
            // module name
            $main = '<a href=' . $this->rewrite_category_url('item_category', $module) . '>' . $GLOBALS['_LANG'][$module . '_category'] . '</a>';
            // if different classification exists
            if ($class) {
                $cat_name = is_numeric($class) ? $this->get_one("SELECT cat_name FROM " . $this->table($module) . " WHERE cat_id = '$class'") : $GLOBALS['_LANG'][$class];
                // if has title
                if ($title)
                    $category = '<b>></b><a href=' . $this->rewrite_url($module, $class) . '>' . $cat_name . '</a>';
                else
                    $category = "<b>></b>$cat_name";
            }

            // if has title
            if ($title)
                $title = '<b>></b>' . $title;

            $ur_here = $main . $category . $title;
        }
        return $ur_here;
    }

    /**
     * page title
     * @param $module
     * @param string $class
     * @param string $title
     * @return string
     */
    function page_title($module, $class = '', $title = '') {
        // if single page execute this
        if ($module == 'page') {
            $titles = $title . ' | ';
        } elseif ($module) {
            // module name
            $main = $GLOBALS['_LANG'][$module] . ' | ';
            // if different classification exists
            if ($class) {
                $cat_name = is_numeric($class) ? $this->get_one("SELECT cat_name FROM " . $this->table($module) . " WHERE cat_id = '$class'") : $GLOBALS['_LANG'][$class];
                $cat_name = $cat_name . ' | ';
            }
            // if has title
            if ($title)
                $title = $title . ' | ';
            $titles = $title . $cat_name . $main;
        }
        $page_title = ($titles ? $titles . $GLOBALS['_CFG']['site_name'] : $GLOBALS['_CFG']['site_title']) . ' - Powered by hbdata';

        return $page_title;
    }

    /**
     * terminal type
     * @return bool
     */
    function is_mobile() {
        static $is_mobile;
        $user_agent = $_SERVER['HTTP_USER_AGENT'];

        if (isset($is_mobile))
            return $is_mobile;

        if (empty($user_agent)) {
            $is_mobile = false;
        } else {
            // mobile user access
            $mobile_agents = array (
                'Mobile',
                'Android',
                'Silk/',
                'Kindle',
                'BlackBerry',
                'Opera Mini',
                'Opera Mobi'
            );
            $is_mobile = false;

            foreach ($mobile_agents as $device) {
                if (strpos($user_agent, $device) !== false) {
                    $is_mobile = true;
                    break;
                }
            }
        }
        return $is_mobile;
    }

    /**
     * msg notification
     * @param $text content information
     * @param string $url redirect url
     * @param int $time delay time
     */
    function hbdata_msg($text, $url = '', $time = 3) {
        if (!$text) {
            $text = $GLOBALS['_LANG']['hbdata_msg_success'];
        }

        //get meta and title information
        $GLOBALS['smarty']->assign('page_title', $GLOBALS['_CFG']['site_title']);
        $GLOBALS['smarty']->assign('keywords', $GLOBALS['_CFG']['site_keywords']);
        $GLOBALS['smarty']->assign('description', $GLOBALS['_CFG']['site_description']);

        //initialize navigation bar
        $data = $this->fetch_array_all($this->table('nav'), 'sort ASC');
        $GLOBALS['smarty']->assign('nav_top_list', $this->get_nav('top'));
        $GLOBALS['smarty']->assign('nav_middle_list', $this->get_nav('middle'));
        $GLOBALS['smarty']->assign('nav_bottom_list', $this->get_nav('bottom'));

        //initialize data
        $GLOBALS['smarty']->assign('ur_here', $GLOBALS['_LANG']['hbdata_msg']);
        $GLOBALS['smarty']->assign('text', $text);
        $GLOBALS['smarty']->assign('url', $url);
        $GLOBALS['smarty']->assign('time', $time);

        //generate msg notification base on $time
        $cue = preg_replace('/d%/Ums', $time, $GLOBALS['_LANG']['hbdata_msg_cue']);
        $GLOBALS['smarty']->assign('cue', $cue);

        $GLOBALS['smarty']->display('hbdata_msg.dwt');

        exit();
    }
}