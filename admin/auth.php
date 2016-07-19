<?php
/**
 * hbData
 * --------------------------------------------------------------------------------------------------
 * 版权所有 2016-
 * 网站地址:
 * --------------------------------------------------------------------------------------------------
 * Author: Fiery
 * Release Date: 2016-7-13
 */

//获取用户id
$user_id = $_USER['user_id'];
//获取REQUEST_URL
$request_url = $_SERVER['REQUEST_URI'];
//获取权限名称
if (!empty($_REQUEST['module'])){
    $url_name = $_REQUEST['module'];
}
elseif(!empty($_REQUEST['name'])){
    $url_name = $_REQUEST['name'];
}
else{
    $url_name = '';
}
$perm_title = $hbdata->get_permission_title($request_url, $url_name, $_REQUEST['page']);
//检查是否具有权限
try{
    $auth = $rbac->check($perm_title, $user_id);
}catch (Exception $e){
    echo $e->getMessage();
}



if(!$auth){
    $hbdata->hbdata_msg($_LANG['auth_failed'], 'index.php', '', 2);
}
?>