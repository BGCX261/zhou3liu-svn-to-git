<?php

/* 地区 region */
class RegionModel extends BaseModel
{
    var $table  = 'region';
    var $prikey = 'region_id';
    var $_name  = 'region';

    var $_relation  = array(
        // 一个地区有多个子地区
        'has_region' => array(
            'model'         => 'region',
            'type'          => HAS_MANY,
            'foreign_key'   => 'parent_id',
            'dependent'     => true
        ),
    );

    var $_autov = array(
        'region_name' => array(
            'required'  => true,
            'filter'    => 'trim',
        ),
        'sort_order'    => array(
            'filter'    => 'intval',
        ),
    );

    /**
     * 取得地区列表
     *
     * @param int $parent_id 大于等于0表示取某个地区的下级地区，小于0表示取所有地区
     * @return array
     */
    function get_list($parent_id = -1)
    {
        if ($parent_id >= 0)
        {
            return $this->find(array(
                'conditions' => "parent_id = '$parent_id'",
                'order' => 'sort_order, region_id',
            ));
        }
        else
        {
            return $this->find(array(
                'order' => 'sort_order, region_id',
            ));
        }
    }

    /*
     * 判断名称是否唯一
     */
    function unique($region_name, $parent_id, $region_id = 0)
    {
        $conditions = "parent_id = '" . $parent_id . "' AND region_name = '" . $region_name . "'";
        $region_id && $conditions .= " AND region_id <> '" . $region_id . "'";
        return count($this->find(array('conditions' => $conditions))) == 0;
    }

    /**
     * 取得options，用于下拉列表
     */
    function get_options($parent_id = 0)
    {
        $res = array();
        $regions = $this->get_list($parent_id);
        foreach ($regions as $region)
        {
            $res[$region['region_id']] = $region['region_name'];
        }
        return $res;
    }

    /**
     * 取得某地区的所有子孙地区id
     */
    function get_descendant($id)
    {
        $ids = array($id);
        $ids_total = array();
        $this->_get_descendant($ids, $ids_total);
        return array_unique($ids_total);
    }
    function _get_descendant($ids, &$ids_total)
    {
        $childs = $this->find(array(
            'fields' => 'region_id',
            'conditions' => "parent_id " . db_create_in($ids)
        ));
        $ids_total = array_merge($ids_total, $ids);
        $ids = array();
        foreach ($childs as $child)
        {
            $ids[] = $child['region_id'];
        }
        if (empty($ids))
        {
            return ;
        }
        $this->_get_descendant($ids, $ids_total);
    }
}

?>