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
 * @name Firewall
 * @version v1.0
 * @author AlanJager
 */

class Firewall
{
    /**
     * hbdata firewall
     */
    function hbdata_firewall() {
        //execute escape character change operation
        $this->hbdata_magic_quotes();
    }

    /**
     * user input safe
     * @param $value
     * @return array|string
     */
    function hbdata_foreground($value) {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = htmlspecialchars($v, ENT_QUOTES);
            }
        } else {
            $value = htmlspecialchars($value, ENT_QUOTES);
        }

        return $value;
    }

    /**
     * escape character change operation
     * before use addslashes should make sure magic_quotes_gpc is close, if not the input special words will include
     * '/' due to the two times operation to escape characters
     * if server open magic magic_quotes_gpc defaultly post, get , cookie will add more escape characters
     */
    function hbdata_magic_quotes() {
        if (!@ get_magic_quotes_gpc()) {
            $_GET = $_GET ? $this->addslashes_deep($_GET) : '';
            $_POST = $_POST ? $this->addslashes_deep($_POST) : '';
            $_COOKIE = $this->addslashes_deep($_COOKIE);
            $_REQUEST = $this->addslashes_deep($_REQUEST);
        }
    }

    /**
     * use recursion to make special words into escape characters
     * use addslashes will add '\' for input value, before store into database delete it
     * @param $value
     * @return array|string
     */
    function addslashes_deep($value) {
        if (empty($value)) {
            return $value;
        }

        if (is_array($value)) {
            foreach ((array) $value as $k => $v) {
                unset($value[$k]);
                $k = addslashes($k);
                if (is_array($v)) {
                    $value[$k] = $this->addslashes_deep($v);
                } else {
                    $value[$k] = addslashes($v);
                }
            }
        } else {
            $value = addslashes($value);
        }

        return $value;
    }

    /**
     * use recursion to make special words into escape characters
     * @param $value
     * @return array|string
     */
    function stripslashes_deep($value) {
        if (empty($value)) {
            return $value;
        }

        if (is_array($value)) {
            foreach ((array) $value as $k => $v) {
                unset($value[$k]);
                $k = stripslashes($k);
                if (is_array($v)) {
                    $value[$k] = $this->stripslashes_deep($v);
                } else {
                    $value[$k] = stripslashes($v);
                }
            }
        } else {
            $value = stripslashes($value);
        }
        return $value;
    }
    
    /**
     * set token
     * @param $id
     * @return string
     */
    function set_token($id) {
        $token = md5(uniqid(rand(), true));
        $n = rand(1, 24);
        return $_SESSION[DOU_ID]['token'][$id] = substr($token, $n, 8);
    }

    /**
     * +----------------------------------------------------------
     * 验证令牌
     * +----------------------------------------------------------
     * $token 一次性令牌
     * $id 令牌ID
     * $boolean 是否直接返回布尔值
     * +----------------------------------------------------------
     */
    function check_token($token, $id, $boolean = false) {
        if (isset($_SESSION[HBDATA_ID]['token'][$id]) && $token == $_SESSION[HBDATA_ID]['token'][$id]) {
            unset($_SESSION[HBDATA_ID]['token'][$id]);
            return true;
        } else {
            unset($_SESSION[HBDATA_ID]['token'][$id]);
            if ($boolean) return false; // 是否直接返回布尔值
            if (strpos(HBDATA_ID, 'hbdata_') !== false) {
                $GLOBALS['hbdata']->hbdata_msg($GLOBALS['_LANG']['illegal'], ROOT_URL);
            } elseif (strpos(DOU_ID, 'mobile_')) {
                $GLOBALS['hbdata']->hbdata_msg($GLOBALS['_LANG']['illegal'], M_URL);
            } else {
                header('Content-type: text/html; charset=' . HBDATA_CHARSET);
                echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">" . $GLOBALS['_LANG']['illegal'];
                exit();
            }
        }
    }

    /**
     * +----------------------------------------------------------
     * 获取合法的分类ID或者栏目ID
     * +----------------------------------------------------------
     * $module 模块名称及数据表名
     * $id 分类ID或者栏目ID
     * $unique_id 伪静态别名
     * +----------------------------------------------------------
     */
    function get_legal_id($module, $id = '', $unique_id = '') {
        // 如果有设置则验证合法性，验证通过的情况包括为空和赋值合法，分类页允许ID为空，详细页（包括单页面）不允许ID为空
        if ((isset($id) && !$GLOBALS['check']->is_number($id)) || (isset($unique_id) && !$GLOBALS['check']->is_unique_id($unique_id)))
            return -1;

        if (isset($unique_id)) {
            if ($module == 'page') {
                $get_id = $GLOBALS['hbdata']->get_one("SELECT id FROM " . $GLOBALS['hbdata']->table($module) . " WHERE unique_id = '$unique_id'");
            } else {
                if (isset($id)) {
                    if ($id === '0') return 0; // 分类页允许ID为0
                    $system_unique_id = $GLOBALS['hbdata']->get_one("SELECT c.unique_id FROM " . $GLOBALS['hbdata']->table($module . '_category') .  " AS c LEFT JOIN " . $GLOBALS['hbdata']->table($module) . " AS i ON id = '$id' WHERE c.cat_id = i.cat_id");
                    $get_id = $system_unique_id == $unique_id ? $id : '';
                } else {
                    $get_id = $GLOBALS['hbdata']->get_one("SELECT cat_id FROM " . $GLOBALS['hbdata']->table($module) . " WHERE unique_id = '$unique_id'");
                }
            }
        } else {
            if (isset($id)) {
                if (strpos($module, 'category')) {
                    if ($id === '0') return 0; // 分类页允许ID为0
                    $get_id = $GLOBALS['hbdata']->get_one("SELECT cat_id FROM " . $GLOBALS['hbdata']->table($module) . " WHERE cat_id = '$id'");
                } else {
                    $get_id = $GLOBALS['hbdata']->get_one("SELECT id FROM " . $GLOBALS['hbdata']->table($module) . " WHERE id = '$id'");
                }
            } else {
                // $unique_id和$id都没设置只可能为分类主页或者是详细页没有输入id
                return strpos($module, 'category') ? 0 : -1;
            }
        }

        $legal_id = $get_id ? $get_id : -1;

        return $legal_id;
    }
}