<?php /* Smarty version 2.6.26, created on 2016-07-24 00:25:31
         compiled from index.htm */ ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?php echo $this->_tpl_vars['title']; ?>
</title>
    <link href="template/resources/css/style.css" rel="stylesheet" type="text/css" />
    <script language="javascript">
        <?php echo '
        function agree()
        {
            if (document.getElementById(\'btn_license\').checked)
            {
                document.getElementById(\'submit\').disabled=false;
                document.getElementById(\'submit\').className=\'btn\';
            }
            else
            {
                document.getElementById(\'submit\').disabled=\'disabled\';
                document.getElementById(\'submit\').className=\'btnGray\';
            }
        }
        '; ?>

    </script>
</head>
<body>
<div id="wrapper">
    <div class="logo"><a href="" target="_blank"><img src="template/resources/imgs/logo.gif" alt="" title="" /></a></div>
    <div class="license">
        <form action="" method="post">
            <ul>
    <textarea name="request" cols="90" rows="15" readonly="readonly">
        Here in install rules
    </textarea>
                <div class="agree">
                    <label>
                        <input name="confirm" type="checkbox" onclick="agree();" align="absMiddle" id="btn_license"/>
                        <b><?php echo $this->_tpl_vars['lang']['welcome_agree']; ?>
</b></label>
                </div>
            </ul>
            <p class="action">
                <input type="button" class="btnGray" name="submit" value="<?php echo $this->_tpl_vars['lang']['next']; ?>
" disabled="disabled" id="submit" onclick="location.href='index.php?step=check'"/>
            </p>
        </form>
    </div>
</div>
</body>
</html>