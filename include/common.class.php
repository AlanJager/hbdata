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
 * @name DbMysql
 * @version v1.0
 * @author AlanJager
 */
class Common extends DbMysql
{

    /**
     * get child module belong to cur module
     */
    function hbdata_child_id()
    {

    }

    /**
     * redirect user to the aimed url
     */
    function hbdata_header()
    {

    }

    /**
     * system module
     */
    function hbdata_module()
    {

    }


    function hbdata_qq()
    {

    }


    function dou_substr($str, $length, $clear_space = true, $charset = DOU_CHARSET) {
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

    }
}