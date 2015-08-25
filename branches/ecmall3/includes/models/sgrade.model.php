<?php

/* 店铺等级 sgrade */
class SgradeModel extends BaseModel
{
    var $table  = 'sgrade';
    var $prikey = 'grade_id';
    var $_name  = 'sgrade';
    var $_relation  =   array(
        // 一个店铺等级有多个店铺
        'has_store' => array(
            'model'         => 'store',
            'type'          => HAS_MANY,
            'foreign_key' => 'sgrade',
        ),
    );

    var $_autov = array(
        'grade_name' => array(
            'required'  => true,
            'filter'    => 'trim',
        ),
    );

    /*
     * 判断名称是否唯一
     */
    function unique($grade_name, $grade_id = 0)
    {
        $conditions = "grade_name = '" . $grade_name . "'";
        $grade_id && $conditions .= " AND grade_id <> '" . $grade_id . "'";
        return count($this->find(array('conditions' => $conditions))) == 0;
    }

    function get_options()
    {
        $options = array();
        $grades = $this->find();
        foreach ($grades as $sgrade)
        {
            $options[$sgrade['grade_id']] = $sgrade['grade_name'];
        }
        return $options;
    }
}

?>