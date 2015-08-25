<?php

/*分类控制器*/
class CategoryApp extends MallbaseApp
{
    /* 商品分类 */
    function index()
    {
        /* 取得导航 */
        $this->assign('navs', $this->_get_navs());

        /* 取得商品分类 */
        $gcategorys = $this->_list_gcategory();

        /* 当前位置 */
        $_curlocal=array(
            array(
                'text'  => Lang::get('index'),
                'url'   => 'index.php',
            ),
            array(
                'text'  => Lang::get('gcategory'),
                'url'   => '',
            ),
        );
        $this->assign('_curlocal',$_curlocal);
        $this->assign('gcategorys', $gcategorys);

        $this->assign('page_title', Lang::get('goods_category') . ' - '. Conf::get('site_title'));
        $this->display('category.goods.html');
    }

        /* 店铺分类 */
    function store()
    {
        /* 取得导航 */
        $this->assign('navs', $this->_get_navs());
        /* 取得商品分类 */
        $scategorys = $this->_list_scategory();
        /* 取得最新店铺 */
        $new_stores = $this->_new_stores(5);
        /* 取得推荐店铺 */
        $recommended_stores = $this->_recommended_stores(5);
        /* 当前位置 */
        $_curlocal=array(
            array(
                'text'  => Lang::get('index'),
                'url'   => 'index.php',
            ),
            array(
                'text'  => Lang::get('scategory'),
                'url'   => '',
            ),
        );
        $this->assign('_curlocal',$_curlocal);
        $this->assign('new_stores', $new_stores);
        $this->assign('recommended_stores', $recommended_stores);
        $this->assign('scategorys', $scategorys);

        $this->assign('page_title', Lang::get('store_category') . ' - '. Conf::get('site_title'));
        $this->display('category.store.html');
    }

        /* 取得商品分类 */
    function _list_gcategory()
    {
        $gcategory_mod =& bm('gcategory', array('_store_id' => 0));
        $gcategories = $gcategory_mod->get_list(-1,true);
        import('tree.lib');
        $tree = new Tree();
        $lang2=get_lang2();//LLL 获取语言2个字母的代称，如fr, sc, en
        $gcategories=chg_array2_string($gcategories, 'cate_name',$lang2);//保留数组第二维字段字符串中指定语言的部分
        $tree->setTree($gcategories, 'cate_id', 'parent_id', 'cate_name');
        return $tree->getArrayList(0);
    }

            /* 取得店铺分类 */
    function _list_scategory()
    {
        $scategory_mod =& m('scategory');
        $scategories = $scategory_mod->get_list(-1,true);

        import('tree.lib');
        $tree = new Tree();
        $lang2=get_lang2();//LLL 获取语言2个字母的代称，如fr, sc, en
        $scategories=chg_array2_string($scategories, 'cate_name',$lang2);//保留数组第二维字段字符串中指定语言的部分
        $tree->setTree($scategories, 'cate_id', 'parent_id','cate_name');
        return $tree->getArrayList(0);
    }

            /* 取得最新店铺 */
    function _new_stores($num)
    {
        $store_mod =& m('store');
        $goods_mod =& m('goods');
        $stores = $store_mod->find(array(
            'conditions' => 'state = 1',
            'order' => 'add_time DESC',
            'join'  => 'belongs_to_user',
            'limit' => '0,' . $num,
        ));
        foreach ($stores as $key => $store){
            empty($store['store_logo']) && $stores[$key]['store_logo'] = Conf::get('default_store_logo');
            $stores[$key]['goods_count'] = $goods_mod->get_count_of_store($store['store_id']);
        }

        return $stores;
    }

          /* 取得推荐店铺 */
    function _recommended_stores($num)
    {
        $store_mod =& m('store');
        $goods_mod =& m('goods');
        $stores = $store_mod->find(array(
            'conditions'    => 'recommended=1 AND state = 1',
            'order'         => 'sort_order DESC',
            'join'          => 'belongs_to_user',
            'limit'         => '0,' . $num,
        ));
        foreach ($stores as $key => $store){
            empty($store['store_logo']) && $stores[$key]['store_logo'] = Conf::get('default_store_logo');
            $stores[$key]['goods_count'] = $goods_mod->get_count_of_store($store['store_id']);
        }
        return $stores;
    }
}

?>