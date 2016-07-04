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
define('EXIT_INIT', true);

require (dirname(__FILE__) . '/include/init.php');
require (ROOT_PATH . 'include/captcha.class.php');

// 开启SESSION
session_start();

// 实例化验证码
$captcha = new Captcha(70, 25);

// 清除之前出现的多余输入
@ob_end_clean();

$captcha->create_captcha();

?>