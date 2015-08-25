<?php

/* 商品分类 gcategory */
class GcategoryModel extends BaseModel
{
    var $table  = 'gcategory';
    var $prikey = 'cate_id';
    var $_name  = 'gcategory';
    var $_relation  = array(
        // 一个分类只能属于一个店铺
        'belongs_to_store' => array(
            'model'         => 'model',
            'type'          => BELONGS_TO,
            'foreign_key'   => 'store_id',
            'reverse'       => 'has_gcategory',
        ),
        // 一个分类有多个子分类
        'has_gcategory' => array(
            'model'         => 'gcategory',
            'type'          => HAS_MANY,
            'foreign_key'   => 'parent_id',
            'dependent'     => true
        ),
        // 分类和商品是多对多的关系
        'has_goods' => array(
            'model'         => 'goods',
            'type'          => HAS_AND_BELONGS_TO_MANY,
            'middle_table'  => 'category_goods',
            'foreign_key'   => 'cate_id',
            'reverse'       => 'belongs_to_gcategory',
        ),
    );

    var $_autov = array(
        'cate_name' => array(
            'required'  => true,
            'filter'    => 'trim',
        ),
        'parent_id' => array(
        ),
        'sort_order' => array(
            'filter'    => 'intval',
        ),
        'if_show' => array(
        ),
    );

    /**
     * 取得分类列表
     *
     * @param int $parent_id 大于等于0表示取某分类的下级分类，小于0表示取所有分类
     * @param bool $shown 只取要显示的分类
     * @return array
     */
    function get_list($parent_id = -1, $shown = false)
    {
        $conditions = "1 = 1";
        $parent_id >= 0 && $conditions .= " AND parent_id = '$parent_id'";
        $shown && $conditions .= " AND if_show = 1";

        return $this->find(array(
            'conditions' => $conditions,
            'order' => 'sort_order, cate_id',
        ));
    }

    function get_options($parent_id = -1, $shown = false)
    {
        $options = array();
        $rows = $this->get_list($parent_id, $shown);
        foreach ($rows as $row)
        {
            $options[$row['cate_id']] = $row['cate_name'];
        }

        return $options;
    }

    /**
     * 把某分类及其上级分类加到数组前
     */
    function get_parents(&$parents, $id)
    {
        $data = $this->get(intval($id));
        array_unshift($parents, array('cate_id' => $data['cate_id'], 'cate_name' => $data['cate_name']));
        if ($data['parent_id'] > 0)
        {
            $this->get_parents($parents, $data['parent_id']);
        }
    }

    /**
     * 取得某分类的所有子孙分类id
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
            'fields' => 'cate_id',
            'conditions' => "parent_id " . db_create_in($ids)
        ));
        $ids_total = array_merge($ids_total, $ids);
        $ids = array();
        foreach ($childs as $child)
        {
            $ids[] = $child['cate_id'];
        }
        if (empty($ids))
        {
            return ;
        }
        $this->_get_descendant($ids, $ids_total);
    }
}

/* 商品分类业务模型 */
class GcategoryBModel extends GcategoryModel
{
    var $_store_id = 0;

    /*
     * 判断名称是否唯一
     */
    function unique($cate_name, $parent_id, $cate_id = 0)
    {
        $conditions = "parent_id = '$parent_id' AND cate_name = '$cate_name'";
        $cate_id && $conditions .= " AND cate_id <> '" . $cate_id . "'";
        return count($this->find(array('conditions' => $conditions))) == 0;
    }

    /* 覆盖基类方法 */
    function add($data, $compatible = false)
    {
        $data['store_id'] = $this->_store_id;
        return parent::add($data, $compatible);
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
}

?>