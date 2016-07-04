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
 * 生成验证码
 * @name Captcha
 * @version v1.0
 * @author AlanJager
 */
class Captcha
{
    var $captcha_width = 70;
    var $captcha_height = 25;

    /**
     * Captcha constructor.
     * @param $captcha_width
     * @param $captcha_height
     * @return Captcha
     */
    function Captcha($captcha_width, $captcha_height) {
        $this->captcha_width = $captcha_width;
        $this->captcha_height = $captcha_height;
    }

    /**
     * create string for captcha
     * @return string
     */
    function create_word() {
        // set random char between the range
        $chars = "23456789ABCDEFGHJKLMNPQRSTUVWXYZ";
        $word = '';
        for($i = 0; $i < 4; $i++)
            $word .= $chars[mt_rand(0, strlen($chars) - 1)];

        return $word;
    }
}