<?php

/**
 *    邮件模板管理控制器
 *
 *    @author    Hyber
 *    @usage    none
 */
class MailtemplateApp extends BackendApp
{

    var $_mailtemplate_mod;

    function __construct()
    {
        $this->MailtemplateApp();
    }

    function MailtemplateApp()
    {
        parent::BackendApp();
        $this->_mailtemplate_mod = &af('mailtemplate');
    }

    /**
     *    邮件模板索引
     *
     *    @author    Hyber
     *    @return    void
     */
    function index()
    {
        $mailtemplates = $this->_mailtemplate_mod->getAll(); //获取所有邮件模板
        $this->assign('mailtemplates', $mailtemplates);
        $this->display('mailtemplate.index.html');
    }
    /**
     *    邮件模板索引
     *
     *    @author    Hyber
     *    @return    void
     */
    function edit()
    {
        $code = isset($_GET['code']) ? trim($_GET['code']) : '';
        if (!$code)
        {
            $this->show_warning('no_such_mailtemplate');
        }
        if (!IS_POST)
        {
            $mailtemplate = $this->_mailtemplate_mod->getOne($code); //获取所有邮件模板
            if (!$mailtemplate)
            {
                $this->show_warning('no_such_mailtemplate');
                return;
            }
            $this->assign('mailtemplate', $mailtemplate);
            $this->assign('build_editor', $this->_build_editor(array('name' => 'content')));
            $this->display('mailtemplate.form.html');
        }
        else
        {
            /* 由于var_export会自动对保存的字符串进行转义，因此为了避免多次转义引起的问题，这里要先去除GPC的转义 */
            $data = stripslashes_deep(array(
                'version'   => $_POST['version'],
                'subject'   => $_POST['subject'],
                'content'   => $_POST['content'],
            ));
            $this->_mailtemplate_mod->_filename = $this->_mailtemplate_mod->_user_dir . $code . '.php';
            $this->_mailtemplate_mod->setAll($data);
            $this->show_message('update_mailtemplate_successed',
                'back_list',        'index.php?app=mailtemplate',
                'edit_again',    'index.php?app=mailtemplate&amp;act=edit&amp;code=' . $code);
        }
    }
}

?>