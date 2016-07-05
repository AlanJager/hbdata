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
        return $_SESSION[HBDATA_ID]['token'][$id] = substr($token, $n, 8);
    }

    /**
     * verify token
     * @param $token
     * @param $id
     * @param bool $boolean
     * @return bool
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
            } elseif (strpos(HBDATA_ID, 'mobile_')) {
                $GLOBALS['hbdata']->hbdata_msg($GLOBALS['_LANG']['illegal'], M_URL);
            } else {
                header('Content-type: text/html; charset=' . HBDATA_CHARSET);
                echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">" . $GLOBALS['_LANG']['illegal'];
                exit();
            }
        }
    }

    /**
     * test if category ID id legal for a module
     * @param $module
     * @param string $id
     * @param string $unique_id
     * @return int|string
     */
    function get_legal_id($module, $id = '', $unique_id = '') {
        //if set, should be verify. And empty or legal value is allowed, classification page ID could be empty, single(detail) couldn't be empty
        if ((isset($id) && !$GLOBALS['check']->is_number($id)) || (isset($unique_id) && !$GLOBALS['check']->is_unique_id($unique_id)))
            return -1;

        if (isset($unique_id)) {
            if ($module == 'page') {
                $get_id = $GLOBALS['hbdata']->get_one("SELECT id FROM " . $GLOBALS['hbdata']->table($module) . " WHERE unique_id = '$unique_id'");
            } else {
                if (isset($id)) {
                    if ($id === '0') return 0; // classification page ID could be 0
                    $system_unique_id = $GLOBALS['hbdata']->get_one("SELECT c.unique_id FROM " . $GLOBALS['hbdata']->table($module . '_category') .  " AS c LEFT JOIN " . $GLOBALS['hbdata']->table($module) . " AS i ON id = '$id' WHERE c.cat_id = i.cat_id");
                    $get_id = $system_unique_id == $unique_id ? $id : '';
                } else {
                    $get_id = $GLOBALS['hbdata']->get_one("SELECT cat_id FROM " . $GLOBALS['hbdata']->table($module) . " WHERE unique_id = '$unique_id'");
                }
            }
        } else {
            if (isset($id)) {
                if (strpos($module, 'category')) {
                    if ($id === '0') return 0; // classification page ID could be 0
                    $get_id = $GLOBALS['hbdata']->get_one("SELECT cat_id FROM " . $GLOBALS['hbdata']->table($module) . " WHERE cat_id = '$id'");
                } else {
                    $get_id = $GLOBALS['hbdata']->get_one("SELECT id FROM " . $GLOBALS['hbdata']->table($module) . " WHERE id = '$id'");
                }
            } else {
                // if either unique_id and id not set, means main page or classification main page do not has id
                return strpos($module, 'category') ? 0 : -1;
            }
        }

        $legal_id = $get_id ? $get_id : -1;
        return $legal_id;
    }

    /**
     * test if module name legal
     * @param $get_legal_module_name
     * @return mixed
     */
    function get_legal_module_name($get_legal_module_name)
    {
        return $get_legal_module_name;
    }
}