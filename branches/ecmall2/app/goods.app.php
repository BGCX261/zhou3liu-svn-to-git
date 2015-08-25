<?php

/* 商品 */
class GoodsApp extends StorebaseApp
{
    var $_goods_mod;
    function __construct()
    {
        $this->GoodsApp();
    }
    function GoodsApp()
    {
        parent::__construct();
        $this->_goods_mod =& m('goods');
    }

    function index()
    {
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!$id)
        {
            $this->show_warning('Hacking Attempt');
            return;
        }

        if (!$this->_assign_common_info($id))
        {
            return;
        }

        /* 赋值商品评论 */
        $this->_assign_goods_comment($id, 3);

        /* 赋值销售记录 */
        $this->_assign_sales_log($id, 3);

        /* 更新浏览次数 */
        $this->_update_views($id);

        //是否开启验证码
        if (Conf::get('captcha_status.goodsqa'))
        {
            $this->assign('captcha', 1);
        }
        $this->_assign_goods_qa($id, 3);
        $this->display('goods.index.html');
    }

    /* 商品评论 */
    function comments()
    {
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!$id)
        {
            $this->show_warning('Hacking Attempt');
            return;
        }

        if (!$this->_assign_common_info($id))
        {
            return;
        }

        /* 赋值商品评论 */
        $this->_assign_goods_comment($id, 15);

        $this->display('goods.comments.html');
    }

    /* 销售记录 */
    function saleslog()
    {
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!$id)
        {
            $this->show_warning('Hacking Attempt');
            return;
        }

        if (!$this->_assign_common_info($id))
        {
            return;
        }

        /* 赋值销售记录 */
        $this->_assign_sales_log($id, 15);

        $this->display('goods.saleslog.html');
    }
    function qa()
    {
        $goods_qa = & m('goodsqa');
        if(!IS_POST)
        {
            //如果是用户第一次查看则将是否为最新设为否
            $new = empty($_GET['new']) ? '' : trim($_GET['new']);
            $update_data = array(
                    'if_new' => '0',
                );
            $question_id = empty($_GET['question_id']) ? '' : intval($_GET['question_id']);
            if ($question_id != '' && $new == 'yes')
            {
                $goods_qa->edit($question_id,$update_data);
            }
            $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
            $ques_id = empty($_GET['ques_id']) ? 0 :intval($_GET['ques_id']);
            if (!$id)
            {
                $this->show_warning('Hacking Attempt');
                return;
            }
            if ($ques_id != 0)
            {
                //查出要回复主题的用户名和id
                $answer = $goods_qa->get(array(
                    'join' => 'belongs_to_user',
                    'fields' => 'member.user_id,user_name',
                    'conditions' => '1 = 1 AND ques_id = '.$ques_id,
                ));
                 $this->assign('answer',$answer);
            }
            if (!$this->_assign_common_info($id))
            {
                return;
            }
            $this->_assign_goods_qa($id,10);
            //是否开启验证码
            if (Conf::get('captcha_status.goodsqa'))
            {
                $this->assign('captcha', 1);
            }
            
            /*赋值产品咨询*/
            $this->display('goods.qa.html');
        }
        else
        {
            $content = (isset($_POST['content']) && $_POST['content'] != '') ? $_POST['content'] : '';
            $type = (isset($_POST['type']) && $_POST['type'] !='') ? $_POST['type'] : '';
            $email = (isset($_POST['email']) && $_POST['email'] != '') ? $_POST['email'] : '';
            $hide_name = (isset($_POST['hide_name']) && $_POST['hide_name'] != '') ? $_POST['hide_name'] : '';
            //对验证码和邮件进行判断
            if ($type == 'ask')
            {
                if (Conf::get('captcha_status.goodsqa'))
                {
                    if (base64_decode($_SESSION['captcha']) != strtolower($_POST['captcha']))
                    {
                        $this->show_warning('captcha_failed');
                        return;
                    }
                }
               if(trim($email) != '')
                {
                    if(!is_email($email))
                    {
                        $this->show_warning('email_not_correct');
                        return;
                    }
                }
                if (trim($hide_name) != '')
                {
                    $user_id = 0;
                }
                else
                {
                    $user_id = $_SESSION['user_info']['user_id'];
                }
            }
            if (trim($content) == '')
            {
                $this->show_warning('content_not_null');
                return;
            }
            $id = empty($_POST['goods_id']) ? 0 : intval($_POST['goods_id']);
            if (!$id)
            {
                $this->show_warning('Hacking Attempt');
                return;
            }
            $conditions = 'and g.goods_id ='.$id;
            $goods_mod = & m('goods');
            $ids = $goods_mod->get(array(
                'fields' => 'store_id,goods_name',
                'conditions' => '1=1 ' . $conditions
            ));
            extract($ids);
            if ($type == 'ask')
            {
                $data = array(
                    'question_content' => $content,
                    'goods_id' => $id,
                    'store_id' => $store_id,
                    'email' => $email,
                    'user_id' => $user_id,
                    'time_post' => gmtime(),
                );
                if ($goods_qa->add($data))
                {
                    $this->show_message('question_successful');
                    return;
                }
            }
            else
            {
                $ques_id = (isset($_POST['ques_id']) && $_POST['ques_id'] !='') ? $_POST['ques_id'] : '';
                            //检查此咨询是否被回复，如果回复则提示
                $if_replied = $goods_qa->get(array(
                    'fields' => 'reply_content',
                    'conditions' => '1 = 1 AND ques_id='.$ques_id,
                    ));
                if (trim($if_replied['reply_content']) != '')
                {
                    $this->show_warning('already_replied',
                        'back',"index.php?app=goods&amp;id={$id}");
                    return;
                }
                if($_SESSION['user_info']['user_id'] != $store_id)
                {
                    $this->show_warning('permission_denied',
                        'back',"index.php?app=goods&amp;id={$id}");
                    return;
                }
                $user_info = $goods_qa->get(array(
                    'conditions' => '1 = 1 AND ques_id = '.$ques_id,
                    'fields' => 'user_id,email'));
                extract($user_info);
                $data = array(
                    'reply_content' => $content,
                    'time_reply' => gmtime(),
                    'if_new' => 1,
                    );
                if ($goods_qa->edit($ques_id,$data))
                {
                    $mail = get_mail('tobuyer_question_replied', array('id' => $id, 'ques_id' => $ques_id, 'goods_name' => $goods_name));
                    $this->_mailto($email, addslashes($mail['subject']), addslashes($mail['message']));
                    $this->show_message('reply_successful',
                        'back',"index.php?app=goods&amp;id={$id}");
                }
                else
                {
                    $this->show_message('reply_failed');
                    return;
                }
            }
        }
    }

    /* 赋值公共信息 */
    function _assign_common_info($id)
    {
        /* 取得商品信息 */
        $goods = $this->_goods_mod->get_info($id);
        //LLL 商品名称显示的多语言
        $goods['goods_name']=get_part_string($goods['goods_name'], get_lang2());
        
        if (!$goods || $goods['if_show'] == 0 || $goods['closed'] == 1 || $goods['state'] != 1)
        {
            $this->show_warning('goods_not_exist');
            return false;
        }
        $this->assign('goods', $goods);
        $this->assign('sales_info', sprintf(LANG::get('sales'), $goods['sales'] ? $goods['sales'] : 0));
        $this->assign('comments', sprintf(LANG::get('comments'), $goods['comments'] ? $goods['comments'] : 0));

        /* 设置店铺id */
        if (!$goods['store_id'])
        {
            $this->show_warning('store of goods is empty');
            return false;
        }
        $this->set_store($goods['store_id']);
        /* 赋值店铺信息 */
        $this->assign('store', $this->get_store_data());

        /* 取得浏览历史 */
        $this->assign('goods_history', $this->_get_goods_history($id));

        /* 默认图片 */
        $this->assign('default_image', Conf::get('default_goods_image'));

        /* 当前位置 */
        $this->_curlocal($this->_get_curlocal($goods['cate_id']));

        $this->assign('page_title', $goods['goods_name'] . ' - ' . Conf::get('site_title'));

        $this->import_resource(array(
            'script' => 'jquery.jqzoom.js',
            'style' => 'res:jqzoom.css'
        ));
        return true;
    }

    /* 取得浏览历史 */
    function _get_goods_history($id, $num = 9)
    {
        $goods_list = array();
        $goods_ids  = ecm_getcookie('goodsBrowseHistory');
        $goods_ids  = $goods_ids ? explode(',', $goods_ids) : array();
        if ($goods_ids)
        {
            $rows = $this->_goods_mod->find(array(
                'conditions' => $goods_ids,
                'fields'     => 'goods_name,default_image',
            ));
            foreach ($goods_ids as $goods_id)
            {
                if (isset($rows[$goods_id]))
                {
                    empty($rows[$goods_id]['default_image']) && $rows[$goods_id]['default_image'] = Conf::get('default_goods_image');
                    $goods_list[] = $rows[$goods_id];
                }
            }
        }
        $goods_ids[] = $id;
        if (count($goods_ids) > $num)
        {
            unset($goods_ids[0]);
        }
        ecm_setcookie('goodsBrowseHistory', join(',', array_unique($goods_ids)));

        return $goods_list;
    }

    /* 赋值销售记录 */
    function _assign_sales_log($goods_id, $num_per_page)
    {
        $page = $this->_get_page($num_per_page);
        $order_goods_mod =& m('ordergoods');
        $sales_list = $order_goods_mod->find(array(
            'conditions' => "goods_id = '$goods_id' AND status = '" . ORDER_FINISHED . "'",
            'join'  => 'belongs_to_order',
            'fields'=> 'buyer_id, buyer_name, add_time, anonymous, goods_id, specification, price, quantity, evaluation',
            'count' => true,
            'order' => 'add_time desc',
            'limit' => $page['limit'],
        ));
        $this->assign('sales_list', $sales_list);

        $page['item_count'] = $order_goods_mod->getCount();
        $this->_format_page($page);
        $this->assign('page_info', $page);
        $this->assign('more_sales', $page['item_count'] > $num_per_page);
    }

    /* 赋值商品评论 */
    function _assign_goods_comment($goods_id, $num_per_page)
    {
        $page = $this->_get_page($num_per_page);
        $order_goods_mod =& m('ordergoods');
        $comments = $order_goods_mod->find(array(
            'conditions' => "goods_id = '$goods_id' AND evaluation_status = '1'",
            'join'  => 'belongs_to_order',
            'fields'=> 'buyer_id, buyer_name, anonymous, evaluation_time, comment, evaluation',
            'count' => true,
            'order' => 'evaluation_time desc',
            'limit' => $page['limit'],
        ));
        $this->assign('goods_comments', $comments);
        $page['item_count'] = $order_goods_mod->getCount();
        $this->_format_page($page);
        $this->assign('page_info', $page);
        $this->assign('more_comments', $page['item_count'] > $num_per_page);
    }
    /*赋值商品咨询*/
    function _assign_goods_qa($goods_id,$num_per_page)
    {
        $goods = $this->_goods_mod->get_info($goods_id);
        $page = $this->_get_page($num_per_page);
        $goods_qa = & m('goodsqa');
        $qa_info = $goods_qa->find(array(
            'join' => 'belongs_to_user',
            'fields' => 'member.user_name,question_content,reply_content,time_post',
            'conditions' => '1 = 1 AND goods_id = '.$goods_id,
            'limit' => $page['limit'],
            'order' =>'time_post desc',
            'count' => true
            ));
        $page['item_count'] = $goods_qa->getCount();
        $this->_format_page($page);
        //如果登陆，则查出email
        if (!empty($_SESSION['user_info']))
        {
            $user_mod = & m('member');
            $user_info = $user_mod->get(array(
                'fields' => 'email',
                'conditions' => '1=1 AND user_id = '.$_SESSION['user_info']['user_id']
            ));
            extract($user_info);
        }
        if ($goods['store_id'] == $user_id)
        {
            $admin = true;
            $this->assign('admin',$admin);
        }
        $this->assign('email',$email);
        $this->assign('page_info',$page);
        $this->assign('qa_info',$qa_info);
    }
    /* 更新浏览次数 */
    function _update_views($id)
    {
        $goodsstat_mod =& m('goodsstatistics');
        $info = $goodsstat_mod->get_info($id);
        if ($info)
        {
            $goodsstat_mod->edit($id, "views = views + 1");
        }
        else
        {
            $goodsstat_mod->add(array(
                'goods_id' => $id,
                'views'    => 1,
            ));
        }
    }

    /**
     * 取得当前位置
     *
     * @param int $cate_id 分类id
     */
    function _get_curlocal($cate_id)
    {
        $parents = array();
        if ($cate_id)
        {
            $gcategory_mod =& m('gcategory');
            $gcategory_mod->get_parents($parents, $cate_id);
        }

        $curlocal = array(
            array('text' => LANG::get('all_categories'), 'url' => 'index.php?app=category'),
        );
        //LLL $parents里面的cate_name部分仅保留对应语言的字符 goods page中导航栏
        $parents=chg_array2_string($parents, 'cate_name', get_lang2());
        foreach ($parents as $category)
        {
            $curlocal[] = array('text' => $category['cate_name'], 'url' => 'index.php?app=search&amp;cate_id=' . $category['cate_id']);
        }
        $curlocal[] = array('text' => LANG::get('goods_detail'));

        return $curlocal;
    }
}

?>