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
 * 上传文件
 * @name Upload
 * @version v1.0
 * @author AlanJager
 */

class Upload {
    var $images_dir;
    var $thumb_dir;
    var $upfile_type; // 上传的类型，默认为：jpg gif png rar zip
    var $upfile_size_max; // 上传大小限制，单位是“KB”，默认为：2048KB
    var $to_file = true; // $this->to_file设定为false时将以原图文件名创建缩略图

    /**
     * Upload constructor.
     * @param string $images_dir
     * @param string $thumb_dir
     * @param string $upfile_type
     * @param string $upfile_size_max
     * @return Upload
     */
    function Upload($images_dir = '../upload/', $thumb_dir = 'thumb/', $upfile_type = 'jpg,gif,png', $upfile_size_max = '2048') {
        $this->images_dir = $images_dir; // upload dir
        $this->thumb_dir = $thumb_dir; // thumb dir
        $this->upfile_type = $upfile_type;
        $this->upfile_size_max = $upfile_size_max;
    }

    /**
     * execute graph uplaod
     * @param $upfile
     * @param string $image_name
     * @return string
     */
    function upload_image($upfile, $image_name = '') {
        if ($GLOBALS['hbdata']->dir_status($this->images_dir) != 'write') {
            $GLOBALS['hbdata']->hbdata_msg($GLOBALS['_LANG']['upload_dir_wrong']);
        }

        //if do not have name rule, set time as file name
        if (empty($image_name)) {
            $image_name = time(); //set cur time as filename
        }

        if (@ empty($_FILES[$upfile]['name'])) {
            $GLOBALS['hbdata']->hbdata_msg($GLOBALS['_LANG']['upload_image_empty']);
        }
        $name = explode(".", $_FILES[$upfile]["name"]); //get file type
        $img_count = count($name); //split string num
        $img_type = $name[$img_count - 1]; //get file type
        if (stripos($this->upfile_type, $img_type) === false) {
            $GLOBALS['hbdata']->hbdata_msg($GLOBALS['_LANG']['upload_file_support'] . $this->upfile_type . $GLOBALS['_LANG']['upload_file_support_no'] . $img_type);
        }
        $photo = $image_name . "." . $img_type; //db filename
        $upfile_name = $this->images_dir . $photo; //upload file name
        $upfile_ok = move_uploaded_file($_FILES[$upfile]["tmp_name"], $upfile_name);
        if ($upfile_ok) {
            $img_size = $_FILES[$upfile]["size"];
            $img_size_kb = round($img_size / 1024);
            if ($img_size_kb > $this->upfile_size_max) {
                @unlink($upfile_name);
                $GLOBALS['hbdata']->hbdata_msg($GLOBALS['_LANG']['upload_out_size'] . $this->upfile_size_max . "KB");
            }
        } else {
            $GLOBALS['_LANG']['upload_wrong'] = preg_replace('/d%/Ums', $this->upfile_size_max, $GLOBALS['_LANG']['upload_wrong']);
            $GLOBALS['hbdata']->hbdata_msg($GLOBALS['_LANG']['upload_wrong']);
        }
        return $photo;
    }

    /**
     * upload graphs
     * @param $upfile
     * @param $images_url
     * @param string $image_name
     */
    function upload_gallery($upfile, $images_url, $image_name = '') {
        if ($GLOBALS['hbdata']->dir_status($this->images_dir) != 'write') {
            echo $GLOBALS['_LANG']['upload_dir_wrong'];
            exit;
        }

        //if do not have name rule, set time as file name
        if (empty($image_name)) {
            $image_name = time(); //set cur time as filename
        }

        if (@ empty($_FILES[$upfile]['name'])) {
            echo $GLOBALS['_LANG']['upload_image_empty'];
            exit;
        }
        $name = explode(".", $_FILES[$upfile]["name"]);
        $img_count = count($name);
        $img_type = $name[$img_count - 1];
        if (stripos($this->upfile_type, $img_type) === false) {
            echo $GLOBALS['_LANG']['upload_file_support'] . $this->upfile_type . $GLOBALS['_LANG']['upload_file_support_no'] . $img_type;
            exit;
        }
        $photo = $image_name . "." . $img_type;
        $upfile_name = $this->images_dir . $photo;
        $upfile_ok = move_uploaded_file($_FILES[$upfile]["tmp_name"], $upfile_name);
        if ($upfile_ok) {
            $img_size = $_FILES[$upfile]["size"];
            $img_size_kb = round($img_size / 1024);
            if ($img_size_kb > $this->upfile_size_max) {
                @unlink($upfile_name);
                echo $GLOBALS['_LANG']['upload_out_size'] . $this->upfile_size_max . "KB";
            } else {
                echo '<li><img src="' . ROOT_URL . $images_url . $photo . '" id="' . $photo . '" class="del">';
                echo '<input type="hidden" name="gallery[]" value="' . $images_url . $photo . '" />';
                echo '<span id="' . $photo . '" class="del">X</span></li>';
            }
        } else {
            $GLOBALS['_LANG']['upload_wrong'] = preg_replace('/d%/Ums', $this->upfile_size_max, $GLOBALS['_LANG']['upload_wrong']);
            echo $GLOBALS['_LANG']['upload_wrong'];
        }
    }

    /**
     * get file information
     * @param $photo
     * @return mixed
     */
    function get_img_info($photo) {
        $photo = $this->images_dir . $photo;
        $image_size = getimagesize($photo);
        $img_info["width"] = $image_size[0];
        $img_info["height"] = $image_size[1];
        $img_info["type"] = $image_size[2];
        $img_info["name"] = basename($photo);
        $img_info["ext"] = pathinfo($photo, PATHINFO_EXTENSION);
        return $img_info;
    }

    /**
     * create thumb graph
     * @param $photo
     * @param string $width
     * @param string $height
     * @param string $quality
     * @return bool|string
     */
    function make_thumb($photo, $width = '128', $height = '128', $quality = '90') {
        $img_info = $this->get_img_info($photo);
        $photo = $this->images_dir . $photo; // 获得图片源
        $thumb_name = substr($img_info["name"], 0, strrpos($img_info["name"], ".")) . "_thumb." . $img_info["ext"]; // 缩略图名称
        if ($img_info["type"] == 1) {
            $img = imagecreatefromgif($photo);
        } elseif ($img_info["type"] == 2) {
            $img = imagecreatefromjpeg($photo);
        } elseif ($img_info["type"] == 3) {
            $img = imagecreatefrompng($photo);
        } else {
            $img = "";
        }

        if (empty($img)) {
            return False;
        }

        if (function_exists("imagecreatetruecolor")) {
            $new_thumb = imagecreatetruecolor($width, $height);
            ImageCopyResampled($new_thumb, $img, 0, 0, 0, 0, $width, $height, $img_info["width"], $img_info["height"]);
        } else {
            $new_thumb = imagecreate($width, $height);
            ImageCopyResized($new_thumb, $img, 0, 0, 0, 0, $width, $height, $img_info["width"], $img_info["height"]);
        }

        // $this->to_file设定为false时将以原图文件名创建缩略图
        if ($this->to_file) {
            if (file_exists($this->images_dir . $this->thumb_dir . $thumb_name))
                @ unlink($this->images_dir . $this->thumb_dir . $thumb_name);
            ImageJPEG($new_thumb, $this->images_dir . $this->thumb_dir . $thumb_name, $quality);
            return $this->images_dir . $this->thumb_dir . $thumb_name;
        } else {
            ImageJPEG($new_thumb, '', $quality);
        }
        ImageDestroy($new_thumb);
        ImageDestroy($img);
        return $thumb_name;
    }
}