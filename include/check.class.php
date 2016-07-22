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
 * @name Check
 * @version v1.0
 * @author AlanJager
 */

class Check
{
    /**
     * 判断是否为rec操作项
     * @param $rec
     * @return bool
     */
    function is_rec($rec) {
        if (preg_match("/^[a-z_]+$/", $rec)) {
            return true;
        }
    }

    /**
     * 判断是否为数字
     * @param $number
     * @return bool
     */
    function is_number($number) {
        if (preg_match("/^[0-9]+$/", $number)) {
            return true;
        }
    }

    /**
     * 判断是否为字母
     * @param $letter
     * @return bool
     */
    function is_letter($letter) {
        if (preg_match("/^[a-z]+$/", $letter)) {
            return true;
        }
    }

    /**
     * 判断验证码是否规范
     * @param $captcha
     * @return bool
     */
    function is_captcha($captcha) {
        if (preg_match("/^[A-Za-z0-9]{4}$/", $captcha)) {
            return true;
        }
    }

    /**
     * 判断是否为邮件地址
     * @param $email
     * @return bool
     */
    function is_email($email) {
        if (preg_match("/^[\w-]+(\.[\w-]+)*@[\w-]+(\.[\w-]+)+$/", $email)) {
            return true;
        }
    }

    /**
     * 判断是否为手机号码
     * @param $mobile
     * @return bool
     */
    function is_telphone($mobile) {
        if (preg_match("/^(\(\d{3,4}\)|\d{3,4}-|\s)?\d{7,14}$/", $mobile)) {
            return true;
        }
    }

    /**
     * 判断是否为QQ号码
     * @param $qq
     * @return bool
     */
    function is_qq($qq) {
        if (preg_match("/^[1-9]*[1-9][0-9]*$/", $qq)) {
            return true;
        }
    }

    /**
     * 判断邮编是否规范/国际通用
     * @param $postcode
     * @return bool
     */
    function is_postcode($postcode) {
        if (preg_match("/^[A-Za-z0-9_-\s]*$/", $postcode)) {
            return true;
        }
    }

    /**
     * 判断价格是否规范
     * @param $price
     * @return bool
     */
    function is_price($price) {
        if (preg_match("/^[0-9.]+$/", $price)) {
            return true;
        }
    }

    /**
     * 判断extend_id是否规范
     * @param $extend_id
     * @return bool
     */
    function is_extend_id($extend_id) {
        if (preg_match("/^[A-Za-z0-9-_.]+$/", $extend_id)) {
            return true;
        }
    }

    /**
     * 判断别名是否规范
     * @param $unique
     * @return bool
     */
    function is_unique_id($unique) {
        if (preg_match("/^[a-zA-Z0-9-]+$/", $unique)) {
            return true;
        }
    }

    /**
     * 检查别名是否能转换为表名
     * @param $unique
     * @return bool
     */
    function is_unique_id_transform_table($unique){
        if(preg_match("/^[a-z]+$/", $unique)){
            return true;
        }
    }

    /**
     * 检查分类别名是否存在
     * @param $unique
     * @return bool
     */
    function unique_id_exist($unique, $module){
        foreach($module as $unique_id){
            if($unique_id == $unique)
            return true;
        }
        return false;
    }
    
    /**
     * 判断搜素关键字是否合法：字母、中文、数字
     * @param $search_keyword
     * @return bool
     */
    function is_search_keyword($search_keyword) {
        if (preg_match("/^[\x{4e00}-\x{9fa5}0-9a-zA-Z_]*$/u", $search_keyword)) {
            return true;
        }
    }

    /**
     * 判断用户名是否规范
     * @param $username
     * @return bool
     */
    function is_username($username) {
        if (preg_match("/^[a-zA-Z]{1}([0-9a-zA-Z]|[._]){3,19}$/", $username)) {
            return true;
        }
    }

    /**
     * 限制密码长度为6-32位
     * @param $password
     * @return bool
     */
    function is_password($password) {
        if (preg_match("/^.{6,}$/", $password)) {
            return true;
        }
    }

    /**
     * 判断价格是否规范
     * @param $char
     * @return bool
     */
    function is_illegal_char($char) {
        if (preg_match("/[\\\~@$%^&=+{};'\"<>\/]/", $char)) {
            return true;
        }
    }

    /**
     * 检查是否包含中文字符，防止垃圾信息
     * @param $value
     * @return bool
     */
    function if_include_chinese($value) {
        if (preg_match("/[\x{4e00}-\x{9fa5}]+/u", $value)) {
            return true;
        }
    }

    /**
     * 验证是否输入和输入长度
     * @param $value
     * @param $length
     * @return bool
     */
    function ch_length($value, $length) {
        if (strlen($value) > 0 && strlen($value) <= $length) {
            return true;
        }
    }

    /**
     * 验证是否为系统开启的模块
     * @param $module
     * @param $setting
     * @return bool
     */
    function is_module($module, $setting){
        if(in_array($module, $setting['column_module'])){
            return true;
        }
        return false;
    }
}
