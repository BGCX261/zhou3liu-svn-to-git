<?php

class SearchApp extends MallbaseApp
{
    /* 搜索商品 */
    function index()
    {
        /* 取得导航 */
        $this->assign('navs', $this->_get_navs());

        /* 取得满足条件的商品 */
        $conditions = "g.if_show = 1 AND g.closed = 0 AND s.state = 1"; // 上架且没有被禁售，店铺是开启状态
        $filters = array(); // 筛选条件

        // 分类
        $cate_id = empty($_GET['cate_id']) ? 0 : intval($_GET['cate_id']);
        if ($cate_id > 0)
        {
            $gcategory_mod =& m('gcategory');
            $cate_ids = $gcategory_mod->get_descendant($cate_id);
            $conditions .= " AND cate_id" . db_create_in($cate_ids);
        }

        // 关键字
        $keyword = trim($_GET['keyword']);
        if (!empty($keyword))
        {
            $conditions .= " AND goods_name LIKE '%{$keyword}%' ";
            $filters['keyword'] = array('key' => 'keyword', 'name' => LANG::get('keyword'), 'value' => $keyword);
        }

        // 品牌
        if (!empty($_GET['brand']))
        {
            $conditions .= " AND brand = '{$_GET['brand']}' ";
            $filters['brand'] = array('key' => 'brand', 'name' => LANG::get('brand'), 'value' => $_GET['brand']);
        }

        // 地区
        if (!empty($_GET['region_id']))
        {
            $conditions .= " AND region_id = '{$_GET['region_id']}' ";
            $region_mod =& m('region');
            $region = $region_mod->get_info($_GET['region_id']);
            $filters['region_id'] = array('key' => 'region_id', 'name' => LANG::get('region'), 'value' => $region['region_name']);
        }

        // 价格区间
        if (!empty($_GET['price']))
        {
            $arr = explode('-', $_GET['price']);
            $min = floatval($arr[0]);
            $max = floatval($arr[1]);
            if ($min != 0 || $max != 0)
            {
                if ($min != 0 && $max != 0)
                {
                    if ($min > $max)
                    {
                        list($min, $max) = array($max, $min);
                    }
                    $conditions .= " AND price >= '$min' AND price <= '$max' ";
                    $filters['price'] = array('key' => 'price', 'name' => LANG::get('price'), 'value' => price_format($min) . ' - ' . price_format($max));
                }
                else
                {
                    if ($min != 0)
                    {
                        $conditions .= " AND price >= '$min' ";
                        $filters['price'] = array('key' => 'price', 'name' => LANG::get('price'), 'value' => LANG::get('ge') . ' ' . price_format($min));
                    }
                    else
                    {
                        $conditions .= " AND price <= '$max' ";
                        $filters['price'] = array('key' => 'price', 'name' => LANG::get('price'), 'value' => LANG::get('le') . ' ' . price_format($max));
                    }
                }
            }
            else
            {
                unset($_GET['price']);
            }
        }

        $page = $this->_get_page(12);
        $goods_mod =& m('goods');
        $store_mod =& m('store');
        $sgrade_mod =& m('sgrade');
        $sgrades = $sgrade_mod->find(array('fields' => 'grade_name'));
        $goods_list = $goods_mod->get_list(array(
            'conditions' => $conditions,
            'count' => true,
            'order' => empty($_GET['order']) || !in_array($_GET['order'], array('sales desc', 'price asc', 'price desc', 'credit_value_desc', 'views desc', 'add_time desc')) ? 'sales desc' : $_GET['order'],
            'limit' => $page['limit'],
        ));
        foreach ($goods_list as $key => $goods)
        {
            //等级图片
            $step = intval(Conf::get('upgrade_required'));
            $step < 1 && $step = 5;
            $goods_list[$key]['credit_image'] = $this->_view->res_base . '/images/' . $store_mod->compute_credit($goods['credit_value'], $step);
            empty($goods['default_image']) && $goods_list[$key]['default_image'] = Conf::get('default_goods_image');
            $goods_list[$key]['grade_name'] = $sgrades[$goods['sgrade']]['grade_name'];
        }
        $this->assign('goods_list', $goods_list);

        $page['item_count'] = $goods_mod->getCount();
        $this->_format_page($page);
        $this->assign('page_info', $page);

        /* 商品展示方式 */
        $display_mode = ecm_getcookie('goodsDisplayMode');
        if (empty($display_mode) || !in_array($display_mode, array('list', 'squares')))
        {
            $display_mode = 'squares'; // 默认格子方式
        }
        $this->assign('display_mode', $display_mode);

        /* 统计品牌 */
        $brands = $goods_mod->count_brand();
        $this->assign('brands', $brands);
        $this->assign('brand_count', count($brands));

        /* 统计价格 */
        $this->assign('price_intervals', $goods_mod->count_price());

        /* 统计地区 */
        $regions = $goods_mod->count_region();
        $this->assign('regions', $regions);
        $this->assign('region_count', count($regions));

        /* 筛选条件 */
        $this->assign('filters', $filters);

        /* 排序方式 */
        $this->assign('orders', array(
            'sales desc' => LANG::get('sales_desc'),
            'credit_value desc' => LANG::get('credit_value_desc'),
            'price asc' => LANG::get('price_asc'),
            'price desc' => LANG::get('price_desc'),
            'views desc' => LANG::get('views_desc'),
            'add_time desc' => LANG::get('add_time_desc'),
        ));

        /* 取得下级分类 */
        $gcategory_mod =& bm('gcategory', array('_store_id' => 0));
        $categories = $gcategory_mod->get_list($cate_id, true);
        $this->assign('categories', $categories);
        $this->assign('category_count', count($categories));

        /* 当前位置 */
        $this->_curlocal($this->_get_goods_curlocal($cate_id));

        $this->assign('page_title', Conf::get('site_title'));
        $this->display('search.goods.html');
    }

    /* 搜索店铺 */
    function store()
    {
        /* 取得导航 */
        $this->assign('navs', $this->_get_navs());

        /* 取得该分类及子分类cate_id */
        $cate_id = empty($_GET['cate_id']) ? 0 : intval($_GET['cate_id']);
        $cate_ids=array();
        $condition_id='';
        if ($cate_id > 0)
        {
            $scategory_mod =& m('scategory');
            $cate_ids = $scategory_mod->get_descendant($cate_id);
        }

        /* 店铺分类检索条件 */
        $condition_id=implode(',',$cate_ids);
        $condition_id && $condition_id = ' AND cate_id IN(' . $condition_id . ')';

        /* 其他检索条件 */
        $conditions = $this->_get_query_conditions(array(
            array( //店铺名称
                'field' => 'store_name',
                'equal' => 'LIKE',
                'assoc' => 'AND',
                'name'  => 'keyword',
                'type'  => 'string',
            ),
            array( //地区名称
                'field' => 'region_name',
                'equal' => 'LIKE',
                'assoc' => 'AND',
                'name'  => 'region_name',
                'type'  => 'string',
            ),
            array( //地区id
                'field' => 'region_id',
                'equal' => '=',
                'assoc' => 'AND',
                'name'  => 'region_id',
                'type'  => 'string',
            ),
            array( //商家用户名
                'field' => 'user_name',
                'equal' => 'LIKE',
                'assoc' => 'AND',
                'name'  => 'user_name',
                'type'  => 'string',
            ),
        ));

        $model_store =& m('store');
        $regions = $model_store->list_regions();
        $page   =   $this->_get_page(10);   //获取分页信息
        $stores = $model_store->find(array(
            'conditions'  => 'state = 1' . $condition_id . $conditions,
            'limit'   =>$page['limit'],
            'order'   => empty($_GET['order']) || !in_array($_GET['order'], array('credit_value desc')) ? 'sort_order' : $_GET['order'],
            'join'    => 'belongs_to_user,has_scategory',

            'count'   => true   //允许统计
        ));

        $model_goods = &m('goods');

        foreach ($stores as $key => $store)
        {
            //店铺logo
            empty($store['store_logo']) && $stores[$key]['store_logo'] = Conf::get('default_store_logo');

            //商品数量
            $stores[$key]['goods_count'] = $model_goods->get_count_of_store($store['store_id']);

            //等级图片
            $step = intval(Conf::get('upgrade_required'));
            $step < 1 && $step = 5;
            $stores[$key]['credit_image'] = $this->_view->res_base . '/images/' . $model_store->compute_credit($store['credit_value'], $step);

        }
        $page['item_count']=$model_store->getCount();   //获取统计数据
        $this->_format_page($page);

        /* 当前位置 */
        $this->_curlocal($this->_get_store_curlocal($cate_id));
        $scategorys = $this->_list_scategory();
        $this->assign('stores', $stores);
        $this->assign('regions', $regions);
        $this->assign('cate_id', $cate_id);
        $this->assign('scategorys', $scategorys);
        $this->assign('page_info', $page);
        $this->assign('page_title', Conf::get('site_title'));
        $this->display('search.store.html');
    }

                /* 取得店铺分类 */
    function _list_scategory()
    {
        $scategory_mod =& m('scategory');
        $scategories = $scategory_mod->get_list(-1,true);

        import('tree.lib');
        $tree = new Tree();
        $tree->setTree($scategories, 'cate_id', 'parent_id', 'cate_name');
        return $tree->getArrayList(0);
    }

    function _get_goods_curlocal($cate_id)
    {
        $parents = array();
        if ($cate_id)
        {
            $gcategory_mod =& m('gcategory');
            $gcategory_mod->get_parents($parents, $cate_id);
        }

        $curlocal = array(
            array('text' => LANG::get('all_categories'), 'url' => "javascript:dropParam('cate_id')"),
        );
        foreach ($parents as $category)
        {
            $curlocal[] = array('text' => $category['cate_name'], 'url' => "javascript:replaceParam('cate_id', '" . $category['cate_id'] . "')");
        }
        unset($curlocal[count($curlocal) - 1]['url']);

        return $curlocal;
    }

    function _get_store_curlocal($cate_id)
    {
        $parents = array();
        if ($cate_id)
        {
            $scategory_mod =& m('scategory');
            $scategory_mod->get_parents($parents, $cate_id);
        }

        $curlocal = array(
            array('text' => LANG::get('all_categories'), 'url' => 'index.php?app=search&amp;act=store'),
        );
        foreach ($parents as $category)
        {
            $curlocal[] = array('text' => $category['cate_name'], 'url' => 'index.php?app=search&amp;act=store&amp;cate_id=' . $category['cate_id']);
        }
        unset($curlocal[count($curlocal) - 1]['url']);
        return $curlocal;
    }
}

?>