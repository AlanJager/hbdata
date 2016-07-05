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

    /**
     * generate graphs use string
     * @return bool
     */
    function create_captcha() {
        $word = $this->create_word();

        //get captcha into session
        $_SESSION['captcha'] = md5($word . HBDATA_SHELL);

        //draw basic background
        $im = imagecreatetruecolor($this->captcha_width, $this->captcha_height);
        $bg_color = imagecolorallocate($im, 235, 236, 237);
        imagefilledrectangle($im, 0, 0, $this->captcha_width, $this->captcha_height, $bg_color);
        $border_color = imagecolorallocate($im, 118, 151, 199);
        imagerectangle($im, 0, 0, $this->captcha_width - 1, $this->captcha_height - 1, $border_color);

        //add disturb
        for($i = 0; $i < 5; $i++) {
            $rand_color = imagecolorallocate($im, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
            imagearc($im, mt_rand(-$this->captcha_width, $this->captcha_width), mt_rand(-$this->captcha_height, $this->captcha_height), mt_rand(30, $this->captcha_width * 2), mt_rand(20, $this->captcha_height * 2), mt_rand(0, 360), mt_rand(0, 360), $rand_color);
        }
        for($i = 0; $i < 50; $i++) {
            $rand_color = imagecolorallocate($im, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
            imagesetpixel($im, mt_rand(0, $this->captcha_width), mt_rand(0, $this->captcha_height), $rand_color);
        }

        //generate captcha graph
        $text_color = imagecolorallocate($im, mt_rand(0, 200), mt_rand(0, 120), mt_rand(0, 120));
        imagestring($im, 6, 18, 5, $word, $text_color);

        //header
        header("Cache-Control: max-age=1, s-maxage=1, no-cache, must-revalidate");
        header("Content-type: image/png;charset=utf-8");

        //finish
        imagepng($im);
        imagedestroy($im);

        return true;
    }
}