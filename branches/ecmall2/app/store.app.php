<?php

class StoreApp extends StorebaseApp
{
    function index()
    {
        /* 店铺信息 */
        $_GET['act'] = 'index';
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!$id)
        {
            $this->show_warning('Hacking Attempt');
            return;
        }
        $this->set_store($id);
        $store = $this->get_store_data();
        $this->assign('store', $store);

        /* 取得友情链接 */
        $this->assign('partners', $this->_get_partners($id));

        /* 取得推荐商品 */
        $this->assign('recommended_goods', $this->_get_recommended_goods($id));

        /* 取得最新商品 */
        $this->assign('new_goods', $this->_get_new_goods($id));

        /* 当前位置 */
        $this->_curlocal(LANG::get('all_stores'), 'index.php?app=search&amp;act=store', $store['store_name']);

        $this->assign('page_title', $store['store_name'] . ' - ' . Conf::get('site_title'));
        $this->display('store.index.html');
    }

    function search()
    {
        /* 店铺信息 */
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!$id)
        {
            $this->show_warning('Hacking Attempt');
            return;
        }
        $this->set_store($id);
        $store = $this->get_store_data();
        $this->assign('store', $store);

        /* 搜索到的商品 */
        $this->_assign_searched_goods($id);

        /* 当前位置 */
        $this->_curlocal(LANG::get('all_stores'), 'index.php?app=search&amp;act=store',
            $store['store_name'], 'index.php?app=store&amp;id=' . $store['store_id'],
            LANG::get('goods_list')
        );

        $this->assign('page_title', Lang::get('goods_list') . ' - ' . $store['store_name']);
        $this->display('store.search.html');
    }

    function article()
    {
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!$id)
        {
            $this->show_warning('Hacking Attempt');
            return;
        }
        $article = $this->_get_article($id);
        if (!$article)
        {
            $this->show_warning('Hacking Attempt');
            return;
        }
        $this->assign('article', $article);

        /* 店铺信息 */
        $this->set_store($article['store_id']);
        $store = $this->get_store_data();
        $this->assign('store', $store);

        /* 当前位置 */
        $this->_curlocal(LANG::get('all_stores'), 'index.php?app=search&amp;act=store',
            $store['store_name'], 'index.php?app=store&amp;id=' . $store['store_id'],
            $article['title']
        );

        $this->assign('page_title', $article['title'] . ' - ' . $store['store_name']);
        $this->display('store.article.html');
    }

    /* 信用评价 */
    function credit()
    {
        /* 店铺信息 */
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!$id)
        {
            $this->show_warning('Hacking Attempt');
            return;
        }
        $this->set_store($id);
        $store = $this->get_store_data();
        $this->assign('store', $store);
        /* 取得评价过的商品 */
        if (!empty($_GET['eval']) && in_array($_GET['eval'], array(1,2,3)))
        {
            $conditions = "AND evaluation = '{$_GET['eval']}'";
        }
        else
        {
            $conditions = "";
            $_GET['eval'] = '';
        }
        $page = $this->_get_page(10);
        $order_goods_mod =& m('ordergoods');
        $goods_list = $order_goods_mod->find(array(
            'conditions' => "seller_id = '$id' AND evaluation_status = 1 AND is_valid = 1 " . $conditions,
            'join'       => 'belongs_to_order',
            'fields'     => 'buyer_id, buyer_name, anonymous, evaluation_time, goods_id, goods_name, specification, price, quantity, goods_image, evaluation, comment',
            'order'      => 'evaluation_time desc',
            'limit'      => $page['limit'],
            'count'      => true,
        ));
        $this->assign('goods_list', $goods_list);

        $page['item_count'] = $order_goods_mod->getCount();
        $this->_format_page($page);
        $this->assign('page_info', $page);

        /* 按时间统计 */
        $stats = array();
        for ($i = 0; $i <= 3; $i++)
        {
            $stats[$i]['in_a_week']        = 0;
            $stats[$i]['in_a_month']       = 0;
            $stats[$i]['in_six_month']     = 0;
            $stats[$i]['six_month_before'] = 0;
            $stats[$i]['total']            = 0;
        }

        $goods_list = $order_goods_mod->find(array(
            'conditions' => "seller_id = '$id' AND evaluation_status = 1 AND is_valid = 1 ",
            'join'       => 'belongs_to_order',
            'fields'     => 'evaluation_time, evaluation',
        ));
        foreach ($goods_list as $goods)
        {
            $eval = $goods['evaluation'];
            $stats[$eval]['total']++;
            $stats[0]['total']++;

            $days = (gmtime() - $goods['evaluation_time']) / (24 * 3600);
            if ($days <= 7)
            {
                $stats[$eval]['in_a_week']++;
                $stats[0]['in_a_week']++;
            }
            if ($days <= 30)
            {
                $stats[$eval]['in_a_month']++;
                $stats[0]['in_a_month']++;
            }
            if ($days <= 180)
            {
                $stats[$eval]['in_six_month']++;
                $stats[0]['in_six_month']++;
            }
            if ($days > 180)
            {
                $stats[$eval]['six_month_before']++;
                $stats[0]['six_month_before']++;
            }
        }
        $this->assign('stats', $stats);

        /* 当前位置 */
        $this->_curlocal(LANG::get('all_stores'), 'index.php?app=search&amp;act=store',
            $store['store_name'], 'index.php?app=store&amp;id=' . $store['store_id'],
            LANG::get('credit_evaluation')
        );

        $this->assign('page_title', Lang::get('credit_evaluation') . ' - ' . $store['store_name']);
        $this->display('store.credit.html');
    }

    /* 取得友情链接 */
    function _get_partners($id)
    {
        $partner_mod =& m('partner');
        return $partner_mod->find(array(
            'conditions' => "store_id = '$id'",
            'order' => 'sort_order',
        ));
    }

    /* 取得推荐商品 */
    function _get_recommended_goods($id, $num = 12)
    {
        $goods_mod =& bm('goods', array('_store_id' => $id));
        $goods_list = $goods_mod->find(array(
            'conditions' => "closed = 0 AND if_show = 1 AND recommended = 1",
            'fields'     => 'goods_name, default_image, price',
            'join'       => 'has_default_spec',
            'limit'      => $num,
        ));
        foreach ($goods_list as $key => $goods)
        {
            empty($goods['default_image']) && $goods_list[$key]['default_image'] = Conf::get('default_goods_image');
        }

        return $goods_list;
    }

    /* 取得最新商品 */
    function _get_new_goods($id, $num = 12)
    {
        $goods_mod =& bm('goods', array('_store_id' => $id));
        $goods_list = $goods_mod->find(array(
            'conditions' => "closed = 0 AND if_show = 1",
            'fields'     => 'goods_name, default_image, price',
            'join'       => 'has_default_spec',
            'order'      => 'add_time desc',
            'limit'      => $num,
        ));
        foreach ($goods_list as $key => $goods)
        {
            empty($goods['default_image']) && $goods_list[$key]['default_image'] = Conf::get('default_goods_image');
        }

        return $goods_list;
    }

    /* 搜索到的结果 */
    function _assign_searched_goods($id)
    {
        $goods_mod =& bm('goods', array('_store_id' => $id));
        $search_name = LANG::get('all_goods');

        $conditions = $this->_get_query_conditions(array(
            array(
                'field' => 'goods_name',
                'name'  => 'keyword',
                'equal' => 'like',
            ),
        ));
        if ($conditions)
        {
            $search_name = sprintf(LANG::get('goods_include'), $_GET['keyword']);
            $sgcate_id   = 0;
        }
        else
        {
            $sgcate_id = empty($_GET['cate_id']) ? 0 : intval($_GET['cate_id']);
        }

        if ($sgcate_id > 0)
        {
            $gcategory_mod =& m('gcategory');
            $sgcate = $gcategory_mod->get_info($sgcate_id);
            $search_name = $sgcate['cate_name'];
            //LLL 提取语言对应字符
            $search_name=get_part_string($search_name, get_lang2());

            $sgcate_ids = $gcategory_mod->get_descendant($sgcate_id);
        }
        else
        {
            $sgcate_ids = array();
        }

        /* 排序方式 */
        $orders = array(
            'add_time desc' => LANG::get('add_time_desc'),
            'price asc' => LANG::get('price_asc'),
            'price desc' => LANG::get('price_desc'),
        );
        $this->assign('orders', $orders);

        $page = $this->_get_page(16);
        $goods_list = $goods_mod->get_list(array(
            'conditions' => 'closed = 0 AND if_show = 1' . $conditions,
            'count' => true,
            'order' => empty($_GET['order']) || !isset($orders[$_GET['order']]) ? 'add_time desc' : $_GET['order'],
            'limit' => $page['limit'],
        ), $sgcate_ids);
        foreach ($goods_list as $key => $goods)
        {
            empty($goods['default_image']) && $goods_list[$key]['default_image'] = Conf::get('default_goods_image');
        }
        $this->assign('searched_goods', $goods_list);

        $page['item_count'] = $goods_mod->getCount();
        $this->_format_page($page);
        $this->assign('page_info', $page);

        $this->assign('search_name', $search_name);
    }

    /**
     * 取得文章信息
     */
    function _get_article($id)
    {
        $article_mod =& m('article');
        return $article_mod->get_info($id);
    }
}

?>