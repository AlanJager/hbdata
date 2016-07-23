# HBDATA
HBDATA官方网站使用说明

一、平台需求
1.Windows 平台：
IIS/Apache/Nginx + PHP 5.3及以上版本 + MySQL5.0及以上

2.Linux/Unix 平台
Apache + PHP 5.3及以上版本 + MySQL5.0及以上

建议使用平台：Linux + Apache2.3 + PHP5.5/PHP5.6 + MySQL5.7

3.PHP必须环境或启用的系统函数：
GD扩展库
MySQLI扩展库
系统函数 —— phpinfo、dir
伪静态 —— mod_rewrite（apache环境）、ISAPI_Rewrite（IIS环境）

4.基本目录结构

hbdata                                      根目录
│  .gitignore                               Git忽略配置文件
│  .htaccess.txt                            去掉.txt开启重定向
│  captcha.php                              验证码模块
│  favicon.ico                              网站icon
│  guestbook.php                            留言板模块
│  index.php                                网站入口文件
│  item.php                                 分类项模块
│  item_category.php                        分类模块
│  page.php                                 分页模块
│  README.md                                使用说明
│  robots.txt                               告诉搜索引擎那些内容不应该被收录
│  sitemap.php
│
├─admin                                     网站后台
│  │  auth.php                              权限认证模块
│  │  backup.php                            备份模块
│  │  category_manage.php                   分类管理模块
│  │  guestbook.php                         留言板模块
│  │  index.php                             后台入口文件
│  │  item.php                              分类项管理模块
│  │  item_category.php                     分类管理模块
│  │  login.php                             登录模块
│  │  manager.php                           用户管理模块
│  │  nav.php                               导航栏模块
│  │  page.php                              分页模块
│  │  role.php                              角色管理模块
│  │  show.php                              幻灯片管理模块
│  │  system.php                            系统设置模块
│  │  theme.php                             设置模板模块
│  │  
│  ├─include                                此文件夹下包含了后台使用的类文件
│  │  │  action.class.php                   负责实现网站操作的类
│  │  │  backup.class.php                   管理备份的类
│  │  │  init.php                           后台初始化的类
│  │  │  pclzip.class.php                   解压缩zip文件的类
│  │  │  
│  │  ├─kindeditor                          此文件夹下包含了一个富文本编辑器
│  │  │  
│  │  └─PhpRbac                             此文件夹下包含了RBAC的库
│  │      │  autoload.php                   符合PSR-4规范的自动加载文件
│  │      │  
│  │      ├─database                        此文件夹下包含了RBAC的配置文件
│  │      │      database.config            RBAC数据库配置文件
│  │      │      readme.md                  关于配置的README
│  │      ├─src                             此文件夹下包含了RBAC的核心实现
│  │      │                      
│  │      └─tests                           此文件夹下包含了RBAC的测试类文件
│  │                          
│  ├─resources                              此文件夹下包含了后台所用的资源
│  │  ├─css                                 此文件夹下包含了css文件
│  │  ├─imgs                                此文件夹下包含了图片文件
│  │  └─js                                  此文件夹下包含了js文件
│  │          
│  └─templates                              此文件夹下包含了后台的模板文件
│          backup.htm                       备份管理模板
│          category_manage.htm              分类管理模板
│          footer.htm                       页脚模板
│          guestbook.htm                    留言板模板
│          hbdata_msg.htm                   弹出消息模板
│          header.htm                       页头模板
│          index.htm                        后台首页模板
│          item.htm                         分类项模板
│          item_category.htm                分类模板
│          javascript.htm                   回掉函数模板
│          link.htm                         友情链接模板
│          login.htm                        登陆模板
│          manager.htm                      用户管理模板
│          menu.htm                         菜单栏模板
│          nav.htm                          导航栏管理模板
│          page.htm                         分页管理模板
│          pager.htm                        分页模板
│          role.htm                         角色管理模板
│          show.htm                         幻灯片管理模板
│          system.htm                       系统设置模板
│          theme.htm                        设置模板模板
│          ur_here.htm                      当前位置模板
│
├─cache                                     此文件夹下包含了系统缓存
│ 
├─data                                      此文件夹下包含了网站配置和备份等
│  │  config.php                            网站配置文件
│  │  system.hbdata                         系统分类配置文件
│  │
│  ├─backup                                 此文件夹下包含了网站的备份文件
│  │
│  └─slide                                  此文件夹下包含了幻灯片图片
│      └─thumb                              此文件夹下包含了幻灯片图片的略缩图
│
├─docs                                      此文件夹下包含了相关文档
│
├─images                                    此文件夹下包含了网站所使用的图片
│  └─upload                                 此文件夹下包含了上传的图片
|
├─include                                   此文件夹下包含了网站通用的类文件
│  │  action.class.php                      前台网站操作类
│  │  actionTestUseCase.php                 前台网站操作类的测试用例
│  │  captcha.class.php                     生成验证码类
│  │  captchaTestUseCase.php                生成验证码类的测试用例
│  │  check.class.php                       验证合法性类
│  │  checkTestUseCase.php                  验证合法性类的测试用例
│  │  common.class.php                      通用类
│  │  commonTestUseCase.php                 通用类的测试用例
│  │  firewall.class.php                    防火墙类
│  │  firewallTestUseCase.php               防火墙类的测试用例
│  │  init.php                              前台初始化类
│  │  mail.class.php                        发送邮件类
│  │  mysql.class.php                       数据库封装类
│  │  mysqlTestUseCase.php                  数据库封装类的测试用例
│  │  route.php                             重定向类
│  │  search.inc.php                        搜索类
│  │  sitemap.class.php                     站点图类
│  │  smtp.class.php                        邮件服务器类
│  │  upload.class.php                      上传类
│  │  util.php                              通用组件类
│  │
│  ├─plugin                                 此文件夹下包含了网站的插件
│  │  ├─alipay                              此文件夹下包含了阿里支付
│  │  ├─alipay_wap 							此文件夹下包含了阿里支付网页版
│  │  └─bankpay								此文件夹下包含了银联支付
│  │
│  └─smarty									此文件夹下包含了SMARTY模板插件
│
├─install									此文件夹下包含了网站安装所需文件
│  │  index.php								网站安装入口
│  │
│  ├─data									网站初始化数据
│  │  ├─backup								备份
│  │  │      hbdataphp.sql					数据库文件
│  │  └─cache								缓存
│  │
│  ├─include								此文件夹下包含了安装所有的类文件
│  │      init.php							安装初始化类
│  │      install.class.php					安装类
│  │      language.class.php				安装语言包
│  │
│  ├─rewrite								此文件夹下包含了重定向所需的文件
│  │
│  └─template								此文件夹下包含了安装时用到的模板
│      │  check.htm							检测系统配置和环境模板
│      │  finish.htm						完成安装模板
│      │  index.htm							安装模板
│      │  install_lock.htm					锁定安装模板
│      │  setting.htm						网站设置模板
│      │
│      └─resources							此文件夹下包含了安装时用到的资源
│          ├─css 							此文件夹下包含了安装时用到的css文件
│          ├─imgs     						此文件夹下包含了安装时用到的图片
│          └─js								此文件夹下包含了安装时用到的js文件
│
├─languages									此文件夹下包含了网站的语言包
│  ├─en_US									此文件夹下包含了英文语言包
│  └─zh_cn									此文件夹下包含了中文语言包
│      │  article.lang.php					文章的语言包
│      │  common.lang.php					通用的语言包
│      │  guestbook.lang.php				留言板的语言包
│      │  product.lang.php					产品的语言包
│      │
│      └─admin								此文件夹下包含了后台用的语言包
│              article.lang.php				文章的语言包
│              auth.lang.php				权限的语言包
│              category_manage.lang.php		分类管理的语言包
│              common.lang.php				通用的语言包
│              guestbook.lang.php			留言板的语言包
│              item.lang.php				分类项的语言包
│              product.lang.php				产品的语言包
│              rbac.lang.php				RBAC的语言包
│              theme.lang.php				模板的语言包
│
└─theme										此文件夹下包含了所有的主题模板				
    └─default								此文件夹下包含了默认的主题模板
        │  guestbook.dwt					前台的留言板模板
        │  hbdata_msg.dwt					前台的弹出消息模板
        │  index.dwt						前台的首页模板
        │  item.dwt							前台的分类项模板
        │  item_category.dwt				前台的分类模板
        │  module.dwt						前台的模块模板
        │  page.dwt							前台的分页模板
        │  search.dwt						前台的搜索模板
        │
        ├─include							此文件夹下包含了小组件
        │      about.tpl					关于我们组件
        │      contact.tpl					联系我们组件
        │      footer.tpl					页尾组件
        │      header.tpl					页头组件
        │      item_tree.tpl				分类树状导航组件
        │      online_service.tpl			在线客服组件
        │      pager.tpl					分页组件
        │      page_tree.tpl				单页树状导航组件
        │      recommend_article.tpl		推荐文章组件
        │      recommend_product.tpl		推荐产品组件
        │      slide_show.tpl				幻灯片组件
        │      ur_here.tpl					当前位置组件
        │
        └─resources							此文件夹下包含了模板所用资源文件
            ├─css 							此文件夹下包含了模板所用的css文件
            ├─imgs 							此文件夹下包含了模板所用的图片文件
            └─js							此文件夹下包含了模板所用的js文件

                    

