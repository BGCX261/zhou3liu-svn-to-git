<?php

/**
 * 销售排行前十挂件
 *
 * @return  array   $goods_list
 */
class Sales_listWidget extends BaseWidget
{
    var $_name = 'sales_list';
    var $_ttl  = 86400;

    function _get_data()
    {
        $cache_server =& cache_server();
        $key = $this->_get_cache_id();
        $data = $cache_server->get($key);
        if($data === false)
        {
            $goods_mod =& m('goods');
            $data = $goods_mod->find(array(
                'conditions' => "if_show = 1 AND closed = 0",
                'order' => 'sales',
                'fields' => 'g.goods_id, g.goods_name',
                'join' => 'has_goodsstatistics',
                'limit' => 10,
            ));
            //LLL sale list top 10 销售排行多语言
            $data=chg_array2_string($data, 'goods_name', get_lang2());
            $cache_server->set($key, $data, $this->_ttl);
        }

        return $data;
    }
}

?>