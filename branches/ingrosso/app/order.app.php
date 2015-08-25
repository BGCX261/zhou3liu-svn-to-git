<?php

/**
 *    售货员控制器，其扮演实际交易中柜台售货员的角色，你可以这么理解她：你告诉我（售货员）要买什么东西，我会询问你你要的收货地址是什么之类的问题
 ＊        并根据你的回答来生成一张单子，这张单子就是“订单”
 *
 *    @author    Garbin
 *    @param    none
 *    @return    void
 */
class OrderApp extends ShoppingbaseApp
{
    /**
     *    填写收货人信息，选择配送，支付方式。
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function index()
    {
        if (!IS_POST)
        {
            $goods_info = $this->_get_goods_info();

            if ($goods_info === false)
            {
                /* 购物车是空的 */
                $this->show_warning('goods_empty');

                return;
            }

            /* 根据商品类型获取对应订单类型 */
            $goods_type     =&  gt($goods_info['type']);
            $order_type     =&  ot($goods_type->get_order_type());

            /* 显示订单表单 */
            $form = $order_type->get_order_form($goods_info['store_id']);
            if ($form === false)
            {
                $this->show_warning($order_type->get_error());

                return;
            }
            $this->_curlocal(
                LANG::get('create_order')
            );
            $this->assign('page_title', Lang::get('confirm_order_info') . ' - ' . Conf::get('site_title'));
            $this->assign('goods_info', $goods_info);
            $this->assign($form['data']);
            $this->display($form['template']);
        }
        else
        {
           /* 在此获取生成订单的两个基本要素：用户提交的数据（POST），商品信息（包含商品列表，商品总价，商品总数量，类型），所属店铺 */
            $goods_info = $this->_get_goods_info();
            if ($goods_info === false)
            {
                /* 购物车是空的 */
                $this->show_warning('goods_empty');

                return;
            }

            /* 根据商品类型获取对应的订单类型 */
            $goods_type =& gt($goods_info['type']);
            $order_type =& ot($goods_type->get_order_type());

            /* 将这些信息传递给订单类型处理类生成订单(你根据我提供的信息生成一张订单) */
            $order_id = $order_type->submit_order(array(
                'goods_info'    =>  $goods_info,      //商品信息（包括列表，总价，总量，所属店铺，类型）,可靠的!
                'post'          =>  $_POST,           //用户填写的订单信息
            ));


            if (!$order_id)
            {
                $this->show_warning($order_type->get_error());

                return;
            }

            /*  检查是否添加收货人地址  */
            if (isset($_POST['save_address']) && (intval(trim($_POST['save_address'])) == 1))
            {
                 $data = array(
                    'user_id'       => $this->visitor->get('user_id'),
                    'consignee'     => trim($_POST['consignee']),
                    'region_id'     => $_POST['region_id'],
                    'region_name'   => $_POST['region_name'],
                    'address'       => trim($_POST['address']),
                    'zipcode'       => trim($_POST['zipcode']),
                    'phone_tel'     => trim($_POST['phone_tel']),
                    'phone_mob'     => trim($_POST['phone_mob']),
                );
                $model_address =& m('address');
                $model_address->add($data);
            }
            /* 下单完成后清理商品，如清空购物车，或将团购拍卖的状态转为已下单之类的 */
            $this->_clear_goods();

            /* 发送邮件 */
            $model_order =& m('order');

            /* 减去商品库存 */
            $model_order->change_stock('-', $order_id);

            /* 获取订单信息 */
            $order_info = $model_order->get($order_id);

            /* 发送事件 */
            $feed = array(
                'icon'  => 'goods',
                'user_id'  => $this->visitor->get('user_id'),
                'user_name'  => addslashes($this->visitor->get('user_name')),
                'title'  => array(
                    'template'  => Lang::get('feed_bought_goods_title'),
                    'data'      => array(
                        'store'    => '<a href="' . SITE_URL . '/index.php?app=store&id=' . $order_info['seller_id'] . '">' . $order_info['seller_name'] . '</a>',
                    ),
                ),
                'body'  => array(
                    'template'  => Lang::get('feed_bought_goods_body'),
                ),
            );
            $ms =& ms();
            $ms->feed->add($feed);

            $buyer_address = $this->visitor->get('email');
            $model_member =& m('member');
            $member_info  = $model_member->get($goods_info['store_id']);
            $seller_address= $member_info['email'];

            /* 发送给买家下单通知 */
            $buyer_mail = get_mail('tobuyer_new_order_notify', array('order' => $order_info));
            $this->_mailto($buyer_address, addslashes($buyer_mail['subject']), addslashes($buyer_mail['message']));

            /* 发送给卖家新订单通知 */
            $seller_mail = get_mail('toseller_new_order_notify', array('order' => $order_info));
            $this->_mailto($seller_address, addslashes($seller_mail['subject']), addslashes($seller_mail['message']));

            /* 更新下单次数 */
            $model_goodsstatistics =& m('goodsstatistics');
            $goods_ids = array();
            foreach ($goods_info['items'] as $goods)
            {
                $goods_ids[] = $goods['goods_id'];
            }
            $model_goodsstatistics->edit($goods_ids, 'orders=orders+1');

            /* 到收银台付款 */
            header('Location:index.php?app=cashier&order_id=' . $order_id);
        }
    }

    /**
     *    获取外部传递过来的商品
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function _get_goods_info()
    {
        $return = array(
            'items'     =>  array(),    //商品列表
            'quantity'  =>  0,          //商品总量
            'amount'    =>  0,          //商品总价
            'store_id'  =>  0,          //所属店铺
            'store_name'=>  '',         //店铺名称
            'type'      =>  null,       //商品类型
        );
        switch ($_GET['goods'])
        {
            case 'groupbuy':
                /* 团购的商品 */
            break;
            default:
                /* 从购物车中取商品 */
                $_GET['store_id'] = isset($_GET['store_id']) ? intval($_GET['store_id']) : 0;
                $store_id = $_GET['store_id'];
                if (!$store_id)
                {
                    return false;
                }


                $cart_model =& m('cart');

                $cart_items      =  $cart_model->find("user_id = " . $this->visitor->get('user_id') . " AND store_id = {$store_id} AND session_id='" . SESS_ID . "'");
                if (empty($cart_items))
                {
                    return false;
                }

                $store_model =& m('store');
                $store_info = $store_model->get($store_id);

                foreach ($cart_items as $rec_id => $goods)
                {
                    $return['quantity'] += $goods['quantity'];                      //商品总量
                    $return['amount']   += $goods['quantity'] * $goods['price'];    //商品总价
                    $cart_items[$rec_id]['subtotal']    =   $goods['quantity'] * $goods['price'];   //小计
                    empty($goods['goods_image']) && $cart_items[$rec_id]['goods_image'] = Conf::get('default_goods_image');
                }

                $return['items']        =   $cart_items;
                $return['store_id']     =   $store_id;
                $return['store_name']   =   $store_info['store_name'];
                $return['type']         =   'material';
            break;
        }

        return $return;
    }

    /**
     *    下单完成后清理商品
     *
     *    @author    Garbin
     *    @return    void
     */
    function _clear_goods()
    {
        switch ($_GET['goods'])
        {
            case 'groupbuy':
                /* 团购的商品 */
            break;
            default://购物车中的商品
                /* 订单下完后清空指定购物车 */
                $_GET['store_id'] = isset($_GET['store_id']) ? intval($_GET['store_id']) : 0;
                $store_id = $_GET['store_id'];
                if (!$store_id)
                {
                    return false;
                }
                $model_cart =& m('cart');
                $model_cart->drop("store_id = {$store_id} AND session_id='" . SESS_ID . "'");
            break;
        }
    }
}
?>
