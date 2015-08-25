<?php

/* 商品数据模型 */
class GoodsModel extends BaseModel
{
    var $table  = 'goods';
    var $prikey = 'goods_id';
    var $alias  = 'g';
    var $_name  = 'goods';
    var $temp; // 临时变量
    var $_relation = array(
        // 一个商品对应一条商品统计记录
        'has_goodsstatistics' => array(
            'model'         => 'goodsstatistics',
            'type'          => HAS_ONE,
            'foreign_key'   => 'goods_id',
            'dependent'     => true
        ),
        // 一个商品对应多个规格
        'has_goodsspec' => array(
            'model'         => 'goodsspec',
            'type'          => HAS_MANY,
            'foreign_key'   => 'goods_id',
            'dependent'     => true
        ),
        // 一个商品对应多个文件
        'has_uploadedfile' => array(
            'model'         => 'uploadedfile',
            'type'          => HAS_MANY,
            'foreign_key'   => 'item_id',
            'ext_limit'     => array('belong' => BELONG_GOODS),
            'dependent'     => true
        ),
        // 一个商品对应一个默认规格
        'has_default_spec' => array(
            'model'         => 'goodsspec',
            'type'          => HAS_ONE,
            'refer_key'     => 'default_spec',
            'foreign_key'   => 'spec_id',
        ),
        // 一个商品对应多个属性
        'has_goodsattr' => array(
            'model'         => 'goodsattr',
            'type'          => HAS_MANY,
            'foreign_key'   => 'goods_id',
            'dependent'     => true
        ),
        // 一个商品对应多个图片
        'has_goodsimage' => array(
            'model'         => 'goodsimage',
            'type'          => HAS_MANY,
            'foreign_key'   => 'goods_id',
            'dependent'     => true
        ),
        // 一个商品只能属于一个店铺
        'belongs_to_store' => array(
            'model'         => 'store',
            'type'          => BELONGS_TO,
            'foreign_key'   => 'store_id',
            'reverse'       => 'has_goods',
        ),
        // 商品和分类是多对多的关系
        'belongs_to_gcategory' => array(
            'model'         => 'gcategory',
            'type'          => HAS_AND_BELONGS_TO_MANY,
            'middle_table'  => 'category_goods',
            'foreign_key'   => 'goods_id',
            'reverse'       => 'has_goods',
        ),
        // 商品和会员是多对多的关系（会员收藏商品）
        'be_collect' => array(
            'model'         => 'member',
            'type'          => HAS_AND_BELONGS_TO_MANY,
            'middle_table'  => 'collect',
            'foreign_key'   => 'item_id',
            'ext_limit'     => array('type' => 'goods'),
            'reverse'       => 'collect_goods',
        ),
        // 商品和推荐类型是多对多的关系 todo
        'be_recommend' => array(
            'model'         => 'recommend',
            'type'          => HAS_AND_BELONGS_TO_MANY,
            'middle_table'  => 'recommended_goods',
            'foreign_key'   => 'goods_id',
            'reverse'       => 'recommend_goods',
        ),
            //商品和商品咨询是一对多关系
        'be_questioned' => array(
            'model' => 'goodsqa',
            'type' => HAS_MANY,
            'foreign_key' => 'goods_id',
            'dependent'   => true, // 依赖
        ),
    );

    var $_autov = array(
        'goods_name' => array(
            'required'  => true,
            'filter'    => 'trim',
        ),
    );

    /**
     * 取得商品列表
     *
     * @param array $params     这个参数跟find函数的参数相同
     * @param int   $scate_ids  店铺商品分类id
     * @param bool  $desc       是否查描述
     * @param bool  $no_picture 没有图片时是否使用no_picture作为默认图片
     * @return array
     */
    function get_list($params = array(), $scate_ids = array(), $desc = false, $no_picture = true)
    {
        is_int($scate_ids) && $scate_ids > 0 && $scate_ids = array($scate_ids);

        extract($this->_initFindParams($params));

        $gs_mod    =& m('goodsspec');
        $store_mod =& m('store');
        $gstat_mod =& m('goodsstatistics');
        $cg_table  = DB_PREFIX . 'category_goods';

        $fields = "g.goods_id, g.store_id, g.type, g.goods_name, g.cate_id, g.cate_name, g.brand, g.spec_qty, g.spec_name_1, g.spec_name_2, g.if_show, g.closed, g.add_time, g.recommended, g.default_image, " .
                "gs.spec_id, gs.spec_1, gs.spec_2, gs.color_rgb, gs.price, gs.stock, " .
                "s.store_name, s.region_id, s.region_name, s.credit_value, s.sgrade, " .
                "gst.views, gst.sales, gst.comments";
        $desc && $fields .= ", g.description";
        $scate_ids && $fields .= ", scg.cate_id AS sgcate_id";
        $tables = "{$this->table} g " .
                "LEFT JOIN {$gs_mod->table} gs ON g.default_spec = gs.spec_id " .
                "LEFT JOIN {$store_mod->table} s ON g.store_id = s.store_id " .
                "LEFT JOIN {$gstat_mod->table} gst ON g.goods_id = gst.goods_id ";
        $scate_ids && $tables .= "LEFT JOIN {$cg_table} scg ON g.goods_id = scg.goods_id AND scg.cate_id " . db_create_in($scate_ids);

        /* 条件(WHERE) */
        $conditions = $this->_getConditions($conditions, true);
        $conditions .= " AND gs.spec_id IS NOT NULL AND s.store_id IS NOT NULL ";
        $scate_ids && $conditions .= " AND scg.cate_id IS NOT NULL ";

        /* 排序(ORDER BY) */
        if ($order)
        {
            $order = ' ORDER BY ' . $this->getRealFields($order) . ', s.sort_order ';
        }
        else
        {
            ' ORDER BY s.sort_order ';
        }

        /* 分页(LIMIT) */
        $limit && $limit = ' LIMIT ' . $limit;
        if ($count)
        {
            $this->_updateLastQueryCount("SELECT COUNT(*) as c FROM {$tables}{$conditions}");
        }

        /* 完整的SQL */
        $this->temp = $tables . $conditions;
        $sql = "SELECT {$fields} FROM {$tables}{$conditions}{$order}{$limit}";

        $goods_list = $index_key ? $this->db->getAllWithIndex($sql, $index_key) : $this->db->getAll($sql);

        // 用no_picture替换商品图片
        if ($no_picture)
        {
            foreach ($goods_list as $key => $goods)
            {
                $goods['default_image'] || $goods_list[$key]['default_image'] = Conf::get('default_goods_image');
            }
        }

        return $goods_list;
    }

    /**
     * 统计品牌
     */
    function count_brand($num = 0)
    {
        $sql = "SELECT brand, COUNT(*) AS count FROM {$this->temp} GROUP BY brand HAVING brand <> '' ORDER BY count DESC";

        $num = intval($num);
        if ($num > 1)
        {
            $sql .= " LIMIT $num";
        }

        return $this->db->getAll($sql);
    }

    /**
     * 统计价格
     */
    function count_price($num = 5)
    {
        $sql = "SELECT MIN(price) AS min, MAX(price) AS max FROM {$this->temp}";
        $row = $this->db->getRow($sql);
        $min = $row['min'];
        $max = $row['max'];
        $step = ceil(($max - $min) / $num);
        $sql = "SELECT FLOOR((price - '$min') / '$step') AS i, count(*) AS count FROM {$this->temp} GROUP BY i ORDER BY i";
        $rows = $this->db->getAll($sql);
        foreach ($rows as $key => $row)
        {
            $rows[$key]['min'] = $min + $row[i] * $step;
            $rows[$key]['max'] = $min + ($row[i] + 1) * $step;
        }
        return $rows;
    }

    /**
     * 统计地区
     */
    function count_region($num = 0)
    {
        $sql = "SELECT region_id, region_name, COUNT(*) AS count FROM {$this->temp} GROUP BY region_id HAVING region_id > 0 ORDER BY count DESC";

        $num = intval($num);
        if ($num > 1)
        {
            $sql .= " LIMIT $num";
        }

        return $this->db->getAll($sql);
    }

    /**
     * 取得商品信息
     *
     * @param int $id 商品id
     * @return array
     */
    function get_info($id)
    {
        $goods = $this->get(array(
            'conditions' => "goods_id = '$id'",
            'join'       => 'belongs_to_store',
            'fields'     => 'this.*, store.state'
        ));
        if ($goods)
        {
            /* 商品规格 */
            $spec_mod =& m('goodsspec');
            $goods['_specs'] = array_values($spec_mod->find(array(
                'conditions' => "goods_id = '$id'",
                'order' => 'spec_id',
            )));

            /* 商品图片 */
            $image_mod =& m('goodsimage');
            $goods['_images'] = array_values($image_mod->find(array(
                'conditions' => "goods_id = '$id'",
                'order' => 'sort_order',
            )));

            /* 店铺分类 */
            $goods['_scates'] = array_values($this->getRelatedData('belongs_to_gcategory', $id, array(
                'fields' => 'category_goods.cate_id',
            )));

            /* 统计情况 */
            $stat_mod =& m('goodsstatistics');
            $goods = array_merge($goods, $stat_mod->get_info($id));
        }

        return $goods;
    }

    /**
     * 取得店铺商品数量
     *
     * @param int $store_id
     */
    function get_count_of_store($store_id)
    {
        static $data = array();
        if (!isset($data[$store_id]))
        {
            $cache_server =& cache_server();
            $data = $cache_server->get('goods_count_of_store');
            if($data === false)
            {
                $sql = "SELECT store_id, COUNT(*) AS goods_count FROM {$this->table} WHERE if_show = 1 AND closed = 0 GROUP BY store_id";
                $data = array();
                $res = $this->db->query($sql);
                while ($row = $this->db->fetchRow($res))
                {
                    $data[$row['store_id']] = $row['goods_count'];
                }
                $cache_server->set('goods_count_of_store', $data, 3600);
            }
        }
        return isset($data[$store_id]) ? $data[$store_id] : 0;
    }

    /**
     * 格式化分类名称
     *
     * @param string $cate_name 用tab键隔开的多级分类名称
     * @return 把tab换成换行符，并且分级缩进
     */
    function format_cate_name($cate_name)
    {
        $arr = explode("\t", $cate_name);
        if (count($arr) > 1)
        {
            for ($i = 0; $i < count($arr); $i++)
            {
                $arr[$i] = str_repeat("&nbsp;", $i * 4) . htmlspecialchars($arr[$i]);
            }
            $cate_name = join("\n", $arr);
        }

        return $cate_name;
    }

    /**
     *    更新被收藏次数
     *
     *    @author    Garbin
     *    @param     int $goods_id
     *    @return    void
     */
    function update_collect_count($goods_id)
    {
        $count = $this->db->getOne("SELECT COUNT(*) AS collect_count FROM {$this->_prefix}collect WHERE item_id={$goods_id} AND type='goods'");
        $model_goodsstatistics =& m('goodsstatistics');
        $model_goodsstatistics->edit($goods_id, array('collects' => $count));
    }

    /**
     * 删除商品相关数据：包括商品图片、商品缩略图，要在删除商品之前调用
     *
     * @param   string  $goods_ids  商品id，用逗号隔开
     */
    function drop_data($goods_ids)
    {
        $image_mod =& m('goodsimage');
        $images = $image_mod->find(array(
            'conditions' => 'goods_id' . db_create_in($goods_ids),
            'fields' => 'image_url, thumbnail',
        ));

        foreach ($images as $image)
        {
            if (!empty($image['image_url']) && trim($image['image_url']) && substr($image['image_url'], 0, 4) != 'http' && file_exists(ROOT_PATH . '/' . $image['image_url']))
            {
                @unlink(ROOT_PATH . '/' . $image['image_url']);
            }
            if (!empty($image['thumbnail']) && trim($image['thumbnail']) && substr($image['thumbnail'], 0, 4) != 'http' && file_exists(ROOT_PATH . '/' . $image['thumbnail']))
            {
                @unlink(ROOT_PATH . '/' . $image['thumbnail']);
            }
        }
    }
}

/* 商品业务模型 business model */
class GoodsBModel extends GoodsModel
{
    var $_store_id = 0;

    /*
     * 判断名称是否唯一
     */
    function unique($goods_name, $goods_id = 0)
    {
        return true;
//        $conditions = "goods_name = '$goods_name'";
//        $goods_id && $conditions .= " AND goods_id <> '" . $goods_id . "'";
//        return count($this->find(array('conditions' => $conditions))) == 0;
    }

    /* 覆盖基类方法 */
    function add($data, $compatible = false)
    {
        $data['store_id'] = $this->_store_id;
        $id = parent::add($data, $compatible);
        $stat_mod =& m('goodsstatistics');
        $stat_mod->add(array(
            'goods_id' => $id
        ));

        return $id;
    }

    /* 覆盖基类方法 */
    function _getConditions($conditions, $if_add_alias = false)
    {
        $alias = '';
        if ($if_add_alias)
        {
            $alias = $this->alias . '.';
        }
        $res = parent::_getConditions($conditions, $if_add_alias);
        return $res ? $res . " AND {$alias}store_id = '{$this->_store_id}'" : " WHERE {$alias}store_id = '{$this->_store_id}'";
    }

    /* 过滤掉不是本店的商品id */
    function get_filtered_ids($ids)
    {
        $sql = "SELECT goods_id FROM {$this->table} WHERE store_id = '{$this->_store_id}' AND goods_id " . db_create_in($ids);

        return $this->db->getCol($sql);
    }

    /* 取得商品数 */
    function get_count()
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE store_id = '{$this->_store_id}'";

        return $this->db->getOne($sql);
    }
}

?>