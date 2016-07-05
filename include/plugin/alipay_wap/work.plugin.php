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
 * 支付宝插件功能
 * @name Plugin
 * @version v1.0
 * @author AlanJager
 */
class Plugin {
    var $plugin_id = 'alipay_wap'; // 插件唯一ID

    /**
     * +----------------------------------------------------------
     * 构造函数
     * $order_sn 订单编号
     * $order_amount 订单金额
     * +----------------------------------------------------------
     */
    function Plugin($order_sn = '', $order_amount = '') {
        $this->order_sn = $order_sn;
        $this->order_amount = $order_amount;
    }

    /**
     * +----------------------------------------------------------
     * 建立请求
     * +----------------------------------------------------------
     * $session_cart session储存的商品信息
     * +----------------------------------------------------------
     */
    function work() {
        // 建立请求
        require_once(ROOT_PATH . 'include/plugin/' . $this->plugin_id . '/lib/alipay_submit.class.php');
        $alipaySubmit = new AlipaySubmit($this->alipay_config());
        $html_text = $alipaySubmit->buildRequestForm($this->parameter(),"get", "立即付款");
        return $html_text;
    }

    /**
     * +----------------------------------------------------------
     * 配置信息
     * +----------------------------------------------------------
     */
    function alipay_config() {
        // 获取插件配置信息
        $plugin = $GLOBALS['hbdata']->get_plugin($this->plugin_id);

        // 合作身份者id，以2088开头的16位纯数字
        $alipay_config['partner']  = $plugin['config']['partner'];

        //收款支付宝账号，一般情况下收款账号就是签约账号
        $alipay_config['seller_id']	= $alipay_config['partner'];

        //商户的私钥（后缀是.pen）文件相对路径
        $alipay_config['private_key_path']	= ROOT_PATH . 'include/plugin/' . $this->plugin_id . '/key/rsa_private_key.pem';

        //支付宝公钥（后缀是.pen）文件相对路径
        $alipay_config['ali_public_key_path']= ROOT_PATH . 'include/plugin/' . $this->plugin_id . '/key/alipay_public_key.pem';

        // 签名方式 不需修改
        $alipay_config['sign_type']    = strtoupper('MD5');

        // 字符编码格式 目前支持 gbk 或 utf-8
        $alipay_config['input_charset']= strtolower('utf-8');

        // ca证书路径地址，用于curl中ssl校验
        // 请保证cacert.pem文件在当前文件夹目录中
        $alipay_config['cacert']    = ROOT_PATH . 'include/plugin/' . $this->plugin_id . '/cacert.pem';

        // 访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
        $alipay_config['transport']    = 'http';

        return $alipay_config;
    }

    /**
     * +----------------------------------------------------------
     * 请求参数
     * +----------------------------------------------------------
     */
    function parameter() {
        // 获取插件配置信息
        $plugin = $GLOBALS['hbdata']->get_plugin($this->plugin_id);

        $parameter['service'] = "alipay.wap.create.direct.pay.by.user";

        // 合作身份者id，以2088开头的16位纯数字
        $parameter['partner'] = trim($plugin['config']['partner']);

        // 收款支付宝账号
        $parameter['seller_id'] = $parameter['partner'];

        //支付类型，必填，不能修改
        $parameter['payment_type'] = "1";

        //服务器异步通知页面路径，需http://格式的完整路径，不能加?id=123这类自定义参数
        $parameter['notify_url'] = ROOT_URL . 'include/plugin/' . $this->plugin_id . '/notify_url.php';

        //页面跳转同步通知页面路径，需http://格式的完整路径，不能加?id=123这类自定义参数，不能写成http://localhost/
        $parameter['return_url'] = ROOT_URL . 'include/plugin/' . $this->plugin_id . '/return_url.php';

        //商户订单号，商户网站订单系统中唯一订单号，必填
        $parameter['out_trade_no'] = $this->order_sn;

        //订单名称，必填
        $parameter['subject'] = 'Order Sn : ' . $this->order_sn . ' (' . $GLOBALS['_CFG']['site_name'] . ')';

        //付款金额，必填
        $parameter['total_fee'] = $this->order_amount;

        //商品展示地址，需以http://开头的完整路径，例如：http://www.商户网址.com/myorder.html
        $parameter['show_url'] = "";

        //订单描述
        $parameter['body'] = "";

        //超时时间
        $parameter['it_b_pay'] = "";

        //钱包token
        $parameter['extern_token'] = "";

        // 字符编码格式 目前支持 gbk 或 utf-8
        $parameter['_input_charset'] = trim(strtolower(strtolower('utf-8')));

        return $parameter;
    }
}
