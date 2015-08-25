<?php

/**
 * 最新成交挂件
 *
 * @param   int     $num    数量
 * @return  array   $data
 */
class Latest_soldWidget extends BaseWidget
{
    var $_name = 'latest_sold';
    var $_ttl  = 1;

    function _get_data()
    {
        if (empty($this->options['num']) || intval($this->options['num']) <= 0)
        {
            $this->options['num'] = 5;
        }

        $cache_server =& cache_server();
        $key = $this->_get_cache_id();
        $data = $cache_server->get($key);
        if($data === false)
        {
            $order_goods_mod =& m('ordergoods');
            $data = $order_goods_mod->find(array(
                'conditions' => "status = '" . ORDER_FINISHED . "'",
                'order' => 'finished_time desc',
                'fields' => 'goods_id, goods_name, price, goods_image',
                'join' => 'belongs_to_order',
                'limit' => $this->options['num'],
            ));
            //LLL latest sold最新成交多语言
            $data=chg_array2_string($data, 'goods_name', get_lang2());
            foreach ($data as $key => $goods)
            {
                empty($goods['goods_image']) && $data[$key]['goods_image'] = Conf::get('default_goods_image');
            }
            $cache_server->set($key, $data, $this->_ttl);
        }

        return $data;
    }
}

?>