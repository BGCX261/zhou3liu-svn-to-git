<?php

include (ROOT_PATH . '/uc_client/client.php');

/**
 *    UCenter连接接口
 *
 *    @author    Garbin
 *    @usage    none
 */
class UcPassport extends BasePassport
{
    var $_name = 'uc';
}

/**
 *    UCenter的用户操作
 *
 *    @author    Garbin
 *    @usage    none
 */
class UcPassportUser extends BasePassportUser
{
    /* 注册 */
    function register($user_name, $password, $email, $local_data = array())
    {
        /* 到UCenter注册 */
        $user_id = outer_call('uc_user_register', array($user_name, $password, $email));
        if ($user_id < 0)
        {
            switch ($user_id)
            {
                case -1:
                    $this->_error('invalid_user_name');
                break;
                case -2:
                    $this->_error('blocked_user_name');
                break;
                case -3:
                    $this->_error('user_exists');
                break;
                case -4:
                    $this->_error('email_error');
                break;
                case -5:
                    $this->_error('blocked_email');
                break;
                case -6:
                    $this->_error('email_exists');
                break;
            }

            return false;
        }

        /* 同步到本地 */
        $local_data['user_name']    = $user_name;
        $local_data['password']     = md5(time() . rand(100000, 999999));
        $local_data['email']        = $email;
        $local_data['reg_time']     = gmtime();
        $local_data['user_id']      = $user_id;

        /* 添加到用户系统 */
        $this->_local_add($local_data);

        return $user_id;
    }

    /* 编辑用户数据 */
    function edit($user_id, $old_password, $items, $force = false)
    {
        $new_pwd = $new_email = '';
        if (isset($items['password']))
        {
            $new_pwd  = $items['password'];
        }
        if (isset($items['email']))
        {
            $new_email = $items['email'];
        }
        $info = $this->get($user_id);
        if (empty($info))
        {
            $this->_error('no_such_user');

            return false;
        }

        /* 先到UCenter修改 */
        $result = outer_call('uc_user_edit', array($info['user_name'], $old_password, $new_pwd, $new_email, $force));
        if ($result != 1)
        {
            switch ($result)
            {
                case 0:
                case -7:
                    return true;
                break;
                case -1:
                    $this->_error('auth_failed');

                    return false;
                break;
                case -4:
                    $this->_error('email_error');

                    return false;
                break;
                case -5:
                    $this->_error('blocked_email');

                    return false;
                break;
                case -6:
                    $this->_error('email_exists');

                    return false;
                break;
                case -8:
                    $this->_error('user_protected');

                    return false;
                break;
                default:
                    $this->_error('unknow_error');

                    return false;
                break;
            }
        }

        /* 成功后编辑本地数据 */
        $local_data = array();
        if ($new_pwd)
        {
            $local_data['password'] = md5(time() .  rand(100000, 999999));
        }
        if ($new_email)
        {
            $local_data['email']    = $new_email;
        }

        //编辑本地数据
        $this->_local_edit($user_id, $local_data);

        return true;
    }

    /* 删除用户 */
    function drop($user_id)
    {
        if (empty($user_id))
        {
            $this->_error('no_such_user');

            return false;
        }

        /* 先到UCenter中删除 */
        $result = outer_call('uc_user_delete', array($user_id));
        outer_call('uc_user_deleteavatar', array($user_id));
        if (!$result)
        {
            $this->_error('uc_drop_user_failed');

            return false;
        }

        /* 再删除本地的 */
        return $this->_local_drop($user_id);
    }

    /* 获取用户信息 */
    function get($flag, $is_name = false)
    {
        /* 至UCenter取用户 */
        $user_info = outer_call('uc_get_user', array($flag, !$is_name));
        if (empty($user_info))
        {
            $this->_error('no_such_user');

            return false;
        }
        list($user_id, $user_name, $email) = $user_info;

        /* 同步至本地 */
        $this->_local_sync($user_id, $user_name, $email);

        return array(
            'user_id'   =>  $user_id,
            'user_name' =>  $user_name,
            'email'     =>  $email,
            'portrait'  =>  portrait($user_id, '')
        );
    }

    /**
     *    验证用户登录
     *
     *    @author    Garbin
     *    @param     $string $user_name
     *    @param     $string $password
     *    @return    int    用户ID
     */
    function auth($user_name, $password)
    {
        $result = outer_call('uc_user_login', array($user_name, $password));
        if ($result[0] < 0)
        {
            switch ($result[0])
            {
                case -1:
                    $this->_error('no_such_user');
                break;
                case -2:
                    $this->_error('password_error');
                break;
                case -3:
                    $this->_error('answer_error');
                break;
                default:
                    $this->_error('unknow_error');
                break;
            }

            return false;
        }

        /* 同步到本地 */
        $this->_local_sync($result[0], $result[1], $result[3]);

        /* 返回用户ID */
        return $result[0];
    }

    /**
     *    同步登录
     *
     *    @author    Garbin
     *    @param     int $user_id
     *    @return    string
     */
    function synlogin($user_id)
    {
        return outer_call('uc_user_synlogin', array($user_id));
    }

    /**
     *    同步退出
     *
     *    @author    Garbin
     *    @return    string
     */
    function synlogout()
    {
        return outer_call('uc_user_synlogout');
    }

    /**
     *    检查电子邮件是否唯一
     *
     *    @author    Garbin
     *    @param     string $email
     *    @return    bool
     */
    function check_email($email)
    {
        $result = outer_call('uc_user_checkemail', array($email));
        if ($result < 0)
        {
            switch ($result)
            {
                case -4:
                    $this->_error('email_error');
                break;
                case -5:
                    $this->_error('blocked_email');
                break;
                case -6:
                    $this->_error('email_exists');
                break;
                default:
                    $this->_error('unknow_error');
                break;
            }

            return false;
        }

        return true;
    }

    /**
     *    检查用户名是否唯一
     *
     *    @author    Garbin
     *    @param     string $user_name
     *    @return    bool
     */
    function check_username($user_name)
    {
        $result = outer_call('uc_user_checkname', array($user_name));
        if ($result < 0)
        {
            switch ($result)
            {
                case -1:
                    $this->_error('invalid_user_name');
                break;
                case -2:
                    $this->_error('blocked_user_name');
                break;
                case -3:
                    $this->_error('user_exists');
                break;
                default:
                    $this->_error('unknow_error');
                break;
            }
            return false;
        }

        return true;
    }

    /**
     *    设置头像
     *
     *    @author    Garbin
     *    @param     int $user_id
     *    @return    string
     */
    function set_avatar($user_id = 0)
    {
        return outer_call('uc_avatar', array($user_id));
    }

    /**
     *    删除头像
     *
     *    @author    Garbin
     *    @param     int $user_id
     *    @return    bool
     */
    function drop_avatar($user_id)
    {
        return outer_call('uc_user_deleteavatar', array($user_id));
    }
}

/**
 *    内置用户中心的短信操作
 *
 *    @author    Garbin
 *    @usage    none
 */
class UcPassportPM extends BasePassportPM
{
    /**
     *    发送短消息
     *
     *    @author    Garbin
     *    @param     int $sender        发送者
     *    @param     array $recipient     接收者
     *    @param     string $subject    标题
     *    @param     string $message    内容
     *    @param     int $replyto       回复主题
     *    @return    false:失败   true:成功
     */
    function send($sender, $recipient, $subject, $message, $replyto = 0)
    {
        $model_message =& m('message');
        $msg_id = $model_message->send($sender, $recipient, $subject, $message, $replyto);
        if (!$msg_id)
        {
            $this->_errors = $model_message->get_error();

            return 0;
        }

        return $msg_id;
    }

    /**
     *    获取短消息内容
     *
     *    @author    Garbin
     *    @param     int  $user_id  拥有者
     *    @param     int  $pm_id    短消息标识
     *    @param     bool $full     是否包括回复 false:不包括 true包括
     *    @return    false:没有消息 array:消息内容
     */
    function get($user_id, $pm_id, $full = false)
    {
        $model_message =& m('message');
        $topic = $model_message->get(array(
            'fields'     => 'this.*,member.user_name, member.portrait',
            'conditions' => 'msg_id=' . $pm_id . ' AND parent_id=0',
            'join'       => 'sent_belongs_to_member',
        ));
        if ($topic['from_id'] == MSG_SYSTEM)
        {
            $topic['user_name'] = Lang::get('system_message');
            $topic['system'] = 1;
        }
        $topic['portrait'] = portrait($topic['from_id'], $topic['portrait']);
        if ($full)
        {
            $replies = $model_message->find(array(
                'fields'     => 'this.*,member.user_name,member.portrait',
                'conditions' => 'parent_id=' . $pm_id,
                'join'       => 'sent_belongs_to_member',
            ));
            foreach ($replies as $key => $value)
            {
                $replies[$key]['portrait'] = portrait($value['from_id'], $value['portrait']);
            }
        }

        return array(
            'topic' => $topic,
            'reply' => $replies
        );
    }

    /**
     *    获取消息列表
     *
     *    @author    Garbin
     *    @param     int    $user_id
     *    @param     string $limit
     *    @param     string $folder 可选值:inbox, outbox
     *    @return    array:消息列表
     */
    function get_list($user_id, $limit = '0, 10', $folder = 'inbox')
    {
        switch ($folder)
        {
            /* 发件箱 */
            case 'outbox':
                $role = 'from_id';
                $status = '2,3';
                $new = '2'; //新消息：2发件方为新消息
                $join_user_name = 'received_belongs_to_member';
            break;
            /* 收件箱 */
            default:
                $role = 'to_id';
                $status = '1,3';
                $new = '1'; //新消息：1收件方为新消息
                $join_user_name = 'sent_belongs_to_member';
            break;
        }
        $model_message =& m('message');
        $messages = $model_message->find(array(
            'fields'        =>'this.*,member.user_name',
            'conditions'    => $role . '=' . $user_id .' AND parent_id=0 AND status IN('. $status .')',
            'count'         => true,
            'limit'         => $limit,
            'order'         => 'last_update DESC',
            'join'          => $join_user_name,
        ));
        if (!empty($messages))
        {
            foreach ($messages as $key => $message)
            {
                $messages[$key]['new'] = $message['new'] == $new ? 1 : 0; //判断是否是新消息
                $message['from_id'] == MSG_SYSTEM && $messages[$key]['user_name'] = Lang::get('system_message'); //判断是否是系统消息
            }
        }

        return array(
            'count' => $model_message->getCount(),
            'items' => $messages
        );
    }

    /**
     *    检查是否有短消息
     *
     *    @author    Garbin
     *    @param     int $user_id
     *    @return    false:无新短消息 ture:有新短消息
     */
    function check_new($user_id)
    {
        $model_message =& m('message');

        return $model_message->check_new($user_id);
    }

    /**
     *    删除短消息
     *
     *    @author    Garbin
     *    @param     int        $user_id 短消息拥有者
     *    @param     array      $pm_ids  欲删除的短消息
     *    @param     string     $foloder    可选值:inbox,outbox
     *    @return    false:失败   true:成功
     */
    function drop($user_id, $pm_ids, $folder = '')
    {
        $model_message =& m('message');
        if (!$model_message->msg_drop($pm_ids, $user_id))
        {
            $this->_errors = $model_message->get_error();

            return false;
        }

        return true;
    }

    /**
     *    标记阅读状态
     *
     *    @author    Garbin
     *    @param     int   $user_id   短消息拥有者
     *    @param     array $pm_ids    欲标记的短消息ID数组
     *    @param     int   $status    标记成的状态，0为已读，1为未读
     *    @return    false:标记失败  true:标记成功
     */
    function mark($user_id, $pm_ids, $status = 0)
    {
        $model_message =& m('message');
        $model_message->edit($pm_ids, array(
            'new'   => $status,
        ));

        return (!$model_message->has_error());
    }
}

/**
 *    内置用户中心的好友操作
 *
 *    @author    Garbin
 *    @usage    none
 */
class UcPassportFriend extends BasePassportFriend
{
    /**
     *    新增一个好友
     *
     *    @author    Garbin
     *    @param     int $user_id       好友拥有者
     *    @param     array $friend_ids    好友
     *    @return    false:失败 true:成功
     */
    function add($user_id, $friend_ids)
    {
        $model_member =& m('member');
        $user_data = array();
        foreach ($friend_ids as $friend_id)
        {
            if ($friend_id == $user_id)
            {
                $this->_error('cannot_add_myself');

                return false;
            }
            $user_data[$friend_id] = array(
                'add_time'  => gmtime()
            );
        }

        return $model_member->createRelation('has_friend', $user_id ,$user_data);
    }

    /**
     *    删除一个好友
     *
     *    @author    Garbin
     *    @param     int $user_id       好友拥有者
     *    @param     array $friend_id     好友
     *    @return    false:失败   true:成功
     */
    function drop($user_id, $friend_ids)
    {
        $model_member =& m('member');

        return $model_member->unlinkRelation('has_friend', $user_id ,$friend_ids);
    }

    /**
     *    获取好友总数
     *
     *    @author    Garbin
     *    @param     int $user_id       好友拥有者
     *    @return    int    好友总数
     */
    function get_count($user_id)
    {
        $model_member =& m('member');

        return count($model_member->getRelatedData('has_friend', array($user_id)));
    }

    /**
     *    获取好友列表
     *
     *    @author    Garbin
     *    @param     int $user_id       好友拥有者
     *    @param     string $limit      条数
     *    @return    array  好友列表
     */
    function get_list($user_id, $limit = '0, 10')
    {
        $model_member =& m('member');
        $friends = $model_member->getRelatedData('has_friend', array($user_id), array(
            'limit' => $limit,
            'order' => 'add_time DESC',
        ));
        if (empty($friends))
        {
            $friends = array();
        }
        else
        {
            foreach ($friends as $_k => $f)
            {
                $friends[$_k]['portrait'] = portrait($f['user_id'], $f['portrait']);
            }
        }

        return $friends;
    }
}

/**
 *    UCenter的事件操作
 *
 *    @author    Garbin
 *    @usage    none
 */
class UcPassportFeed extends BasePassportFeed
{
    /**
     *    添加事件
     *
     *    @author    Garbin
     *    @param     array $feed    事件
     *    @return    false:失败   true:成功
     */
    function add($feed_info)
    {
        return outer_call('uc_feed_add', array($feed_info['icon'], $feed_info['user_id'], $feed_info['user_name'], $feed_info['title']['template'], $feed_info['title']['data'], $feed_info['body']['template'], $feed_info['body']['data'], $feed_info['body_general'], $feed_info['target_ids'], $feed_info['images']));
    }

    /**
     *    获取事件
     *
     *    @author    Garbin
     *    @param     int $limit     条数
     *    @return    array
     */
    function get($limit) {}
}

?>
