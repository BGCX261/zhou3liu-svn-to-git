<?php

class MessageApp extends MemberbaseApp
{
    /**
     *    收件箱
     *
     *    @author    Hyber
     *    @return    void
     */
    function inbox()
    {
        /* 当前位置 */
        $this->_curlocal(LANG::get('member_center'),   'index.php?app=member',
                         LANG::get('message'),         'index.php?app=message&amp;act=inbox',
                         LANG::get('inbox')
                         );

        /* 当前所处子菜单 */
        $this->_curmenu('inbox');
        /* 当前用户中心菜单 */
        $this->_curitem('message');
        $this->import_resource(array(
            'script' => array(
                array(
                    'path' => 'dialog/dialog.js',
                    'attr' => 'id="dialog_js"',
                ),
                array(
                    'path' => 'jquery.ui/jquery.ui.js',
                    'attr' => '',
                ),
                array(
                    'path' => 'jquery.plugins/jquery.validate.js',
                    'attr' => '',
                ),
            ),
            'style' =>  'jquery.ui/themes/ui-lightness/jquery.ui.css',
        ));
        $this->assign('messages', $this->_list_message('inbox', $this->visitor->get('user_id')));
        $this->assign('page_title', Lang::get('member_center') . ' - ' . Lang::get('inbox'));
        $this->display('message.box.html');
    }

    /**
     *    发件箱
     *
     *    @author    Hyber
     *    @return    void
     */
    function outbox()
    {
        /* 当前位置 */
        $this->_curlocal(LANG::get('member_center'),   'index.php?app=member',
                         LANG::get('message'),         'index.php?app=message&amp;act=inbox',
                         LANG::get('outbox')
                         );
        /* 当前所处子菜单 */
        $this->_curmenu('outbox');
        $this->import_resource(array(
            'script' => array(
                array(
                    'path' => 'dialog/dialog.js',
                    'attr' => 'id="dialog_js"',
                ),
                array(
                    'path' => 'jquery.ui/jquery.ui.js',
                    'attr' => '',
                ),
                array(
                    'path' => 'jquery.plugins/jquery.validate.js',
                    'attr' => '',
                ),
            ),
            'style' =>  'jquery.ui/themes/ui-lightness/jquery.ui.css',
        ));
        /* 当前用户中心菜单 */
        $this->_curitem('message');
        $this->assign('messages', $this->_list_message('outbox', $this->visitor->get('user_id')));
        $this->assign('page_title', Lang::get('user_center') . ' - ' . Lang::get('outbox'));
        $this->display('message.box.html');
    }
    /**
     *    发送短消息
     *
     *    @author    Hyber
     *    @return    void
     */
    function send()
    {

        if (!IS_POST){
            /* 当前位置 */
            $this->_curlocal(LANG::get('member_center'),   'index.php?app=member',
                             LANG::get('message'),         'index.php?app=message&amp;act=outbox',
                             LANG::get('send_message')
                             );
            /* 当前所处子菜单 */
            $this->_curmenu('send_message');
            /* 当前用户中心菜单 */
            $this->_curitem('message');
            $to_ids = array(); //防止foreach报错
            $to_id = trim($_GET['to_id']); //获取url中的to_id
            $to_id && $to_ids = explode(',',$to_id); //转换成数组
            $mod_member = &m('member');
            foreach ($to_ids as $key => $to_id)
            {
                /* 如果用户存在 存入$to_user_name数组中 */
                $user_name = $mod_member->get_info(intval($to_id));
                $user_name && $to_user_name[] = $user_name['user_name'];
            }
            /* 如果用户名存在，赋值给$_GET,方便模板获取 */
            isset($to_user_name) && $_GET['to_user_name'] = implode(',', $to_user_name);

            header('Content-Type:text/html;charset=' . CHARSET);
            /* 好友 */
            $friends = $this->_list_friend();
            $this->assign('friends',        $friends);
            $this->assign('friend_num',    count($friends));

            //引入jquery表单插件
            $this->import_resource(array(
                'script' => 'jquery.plugins/jquery.validate.js',
            ));
            $this->assign('page_title', Lang::get('user_center') . ' - ' . Lang::get('send_message'));
            $this->display('message.send.html');
        }
        else
        {
            $to_user_name = str_replace(Lang::get('comma'), ',', trim($_POST['to_user_name'])); //替换中文格式的逗号
            if (!$to_user_name)
            {
                $this->show_warning('no_to_user_name'); //没有填写用户名
                return;
            }
            $to_user_names = explode(',', $to_user_name); //将逗号分割的用户名转换成数组
            $mod_member = &m('member');
            $members = $mod_member->find('user_name ' . db_create_in($to_user_names));
            $to_ids = array();
            foreach ($members as $_user)
            {
                $_user['user_id'] && $to_ids[] = $_user['user_id'];
            }
            if (!$to_ids)
            {
                $this->show_warning('no_such_user'); //没有该用户名
                return;
            }

            /* 连接用户系统 */
            $ms =& ms();
            $msg_id = $ms->pm->send($this->visitor->get('user_id'), $to_ids, $_POST['title'], $_POST['msg_content']);
            if (!$msg_id)
            {
                //$this->show_warning($ms->pm->get_error());
                $rs = $ms->pm->get_error();
                $msg = current($rs);
                $this->show_warning($msg['msg']);
                return;
            }
            $this->show_message('send_message_successed', 'go_back', 'index.php?app=message&act=outbox');
        }
    }

    /**
     *    查看短消息
     *
     *    @author    Hyber
     *    @return    void
     */
    function view()
    {
        $msg_id = isset($_GET['msg_id']) ? intval($_GET['msg_id']) : 0;
        if (!$msg_id)
        {
            $this->show_warning('no_such_message');
            return;
        }
        $ms =& ms();
        if (!IS_POST)
        {
            /* 当前位置 */
            $this->_curlocal(LANG::get('member_center'),   'index.php?app=member',
                             LANG::get('message'),         'index.php?app=message&amp;act=outbox',
                             LANG::get('view_message')
                             );
            /* 当前所处子菜单 */
            $this->_curmenu('view_message');
            /* 当前用户中心菜单 */
            $this->_curitem('message');

            $message = $ms->pm->get($this->visitor->get('user_id'), $msg_id, true);
            if ($this->visitor->get('user_id') == $message['topic']['to_id'] && in_array($message['topic']['status'],array(1,3)))
            {
                $box='inbox'; //当前位置是收件箱
                $message['topic']['new']==1 && $new = 0; //如果为新消息
            }
            elseif ($this->visitor->get('user_id') == $message['topic']['from_id'] && in_array($message['topic']['status'], array(2,3)))
            {
                $box='outbox'; //当前位置是发件箱
                $message['topic']['new']==2 && $new = 0; //如果为新消息
            }
            else
            {
                $this->show_warning('no_such_message');

                return;
            };
            isset($new) && $ms->pm->mark($this->visitor->get('user_id'), array($msg_id), $new); //标示已读
            $this->assign('box', $box);
            $this->assign('message', $message['topic']);
            $this->assign('replies', $message['reply']);
            $this->display('message.view.html');
        }
        else
        {
            $message = $ms->pm->get($this->visitor->get('user_id'), $msg_id);

            if ($this->visitor->get('user_id') == $message['topic']['to_id'] && in_array($message['topic']['status'],array(1,3)))
            {
                $reply_to_id = $message['topic']['from_id']; //回复对方的user_id
            }
            elseif ($this->visitor->get('user_id') == $message['topic']['from_id'] && in_array($message['topic']['status'], array(2,3)))
            {
                $reply_to_id = $message['topic']['to_id']; //回复对方的user_id
            }
            else
            {
                $this->show_warning('no_such_message');

                return;
            }

            if ($reply_to_id == MSG_SYSTEM)
            {
                $this->show_warning('cannot_replay_system_message');
                return;
            }

            $mod_member = &m('member');
            if (!$mod_member->get_info($reply_to_id))
            {
                $this->show_warning('no_such_user');
                return;
            }
            if (!$msg_id = $ms->pm->send($this->visitor->get('user_id'), $reply_to_id, '' , $_POST['msg_content'] , $msg_id))  //获取msg_id
            {
                $this->show_warning($ms->pm->get_error());

                return;
            }
            $this->show_message('send_message_successed');
        }
    }

    /**
     *    删除短消息
     *
     *    @author    Hyber
     *    @return    void
     */
    function drop()
    {
        $msg_ids = isset($_GET['msg_id']) ? trim($_GET['msg_id']) : '';
        if(in_array($_GET['back'],array('inbox', 'outbox')))
        {
            $folder = trim($_GET['back']);
        }
        if (!$msg_ids)
        {
            $this->show_warning('no_such_message');
            return;
        }
        $msg_ids = explode(',',$msg_ids);
        if (!$msg_ids)
        {
            $this->show_warning('no_such_message');
            return;
        }
        $ms =& ms();
        if (!$ms->pm->drop($this->visitor->get('user_id'), $msg_ids, $folder))    //删除
        {
            $this->show_warning($ms->pm->get_error());

            return;
        }

        /* 删除成功返回 */
        if (in_array($_GET['back'],array('inbox', 'outbox')))
        {
            $this->show_message('drop_message_successed',
                'back_' . $_GET['back'] ,'index.php?app=message&amp;act=' . $_GET['back']);
        }
        else
        {
            $this->show_message('drop_message_successed');
        }
    }

     /**
     *    三级菜单
     *
     *    @author    Hyber
     *    @return    void
     */
    function _get_member_submenu()
    {
        $ms =& ms();
        $new = $ms->pm->check_new($this->visitor->get('user_id'));
        $new['inbox'] && $new_inbox = "(". $new['inbox']. ")";
        $new['outbox'] && $new_outbox = "(". $new['outbox']. ")";
        $menus = array(
                array(
                    'name'  => 'inbox',
                    'url'   => 'index.php?app=message&amp;act=inbox',
                    'text'  => Lang::get('inbox') . $new_inbox,
                ),
                array(
                    'name'  => 'outbox',
                    'url'   => 'index.php?app=message&amp;act=outbox',
                    'text'  => Lang::get('outbox') . $new_outbox,
                ),
        );

        ACT == 'send' && $menus[] = array(
                'name' => 'send_message',
        );

        ACT == 'view' && $menus[] = array(
                'name' => 'view_message',
        );
        return $menus;
    }

    function _list_message($pattern, $user_id)
    {
        /* 连接用户系统 */
        $user_id = intval($user_id);
        if (!$user_id){
            $this->show_warning('no_such_user');

            return;
        }
        switch ($pattern)
        {
            case 'outbox':
                $lang_user_name = Lang::get('to_user_name');
            break;
            default:
                $lang_user_name = Lang::get('from_user_name');
            break;
        }
        $page = $this->_get_page(10);
        $ms =& ms();
        $pms = $ms->pm->get_list($user_id, $page['limit'], $pattern);
        $page['item_count'] = $pms['count'];
        $this->_format_page($page);
        $this->assign('page_info', $page);          //将分页信息传递给视图，用于形成分页条
        $this->assign('lang_user_name', $lang_user_name);

        return $pms['items'];
    }
    function _list_friend()
    {
        $friends = array();
        $ms =& ms();
        $friends = $ms->friend->get_list($this->visitor->get('user_id'), '0, 10000');

        return $friends;
    }
    
   /**
     *   LLL 启动客服守候,由店主触发
     *
     *    @author    ZZH
     *    @return    void
     */
    function talk()
    {
        $user_name = $this->visitor->get('user_name');
        $user_id=$this->visitor->get('user_id');
        $mod_user =& m('member');
        $user_info = $mod_user->get(array(
            'conditions'    => "user_id = '{$user_id}'",
            'join'          => 'has_store',                 //关联查找看看是否有店铺
            'fields'        => 'user_id, user_name, reg_time, last_login, last_ip, store_id',
        ));
        $my_store = empty($user_info['store_id']) ? 0 : $user_info['store_id'];
        $this->assign('username',$user_name);
        $this->assign('store_id',$my_store);
        $this->display('onlinechat.html');
    }


   /**
     * LLL 保存客服守候信息（adobe_id and last_chat_time)
     *
     *    @author    ZZH
     *    @return    void
     */
    function savechat()
    {
        $user_id = $this->visitor->get('user_id');
        if(!isset($_POST['adobe_id'])) return;
        $data = array(
                'adobe_id' => $_POST['adobe_id'],
                'last_chat_time'=>time(),
            );
             $model_user =& m('member');
            $model_user->edit($user_id , $data);
            if ($model_user->has_error())
            {
                $this->assign('result',"error");
                $this->display('saveid.html');
                return;
            }
            $this->assign('result',"ok");
            $this->display('saveid.html');
            return;
    }

   /**
     *   LLL 启动对话,由visitor触发
     *
     *    @author    LT
     *    @return    void
     */
    function starttalk()
    {

        $to_id = trim($_GET['to_id']); //获取url中的to_id
        $mod_member=&m('member');
        $owner=$mod_member->get_info(intval($to_id));
        $owner_name=$owner['user_name'];
        $this->assign('store_id',intval($to_id));
        $this->assign('owner_name',$owner_name);
        $this->assign('visitor_name',$this->visitor->info['user_name']);
        $this->display('user.online_chat.html');
    }

    //LLL get adobe id
    function get_adobe_id()
    {
        $store_id=trim($_REQUEST['store_id']);
        $mod_member=&m('member');
        $owner=$mod_member->get_info(intval($store_id));
        $adobe_id=$owner['adobe_id'];
        !isset($adobe_id) && $adobe_id='';
        $this->assign('adobe_id',$adobe_id);
        $this->display('adobe_id.html');
    }
}
?>
