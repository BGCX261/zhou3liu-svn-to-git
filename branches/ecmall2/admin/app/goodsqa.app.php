<?php
class GoodsqaApp extends BackendApp
{
    var $goodsqa_mod;
    function __construct()
    {
        $this->goodsqaapp();
    }
    function goodsqaapp()
    {
        $this->goodsqa_mod = & m('goodsqa');
        parent::__construct();
    }
    function index()
    {
        $user_name = (isset($_GET['asker']) && $_GET['asker'] != '') ? trim($_GET['asker']) : '';
        $content = (isset($_GET['content']) && $_GET['content'] != '') ? trim($_GET['content']) : '';
        $conditions = '';
        if (trim($user_name) != '')
        {
            $conditions .= ' AND user_name = "'. $user_name.' " ' ;
        }
        if (trim($content) != '')
        {
            $conditions .= ' AND question_content like "%' . $content . '%"';
        }
        $page = $this->_get_page();
        $list_data = $this->goodsqa_mod->find(array(
            'join' => 'belongs_to_user,belongs_to_store,belongs_to_goods',
            'fields' => 'ques_id,question_content,goods_qa.user_id,goods_qa.store_id,goods_name,goods_qa.goods_id,user_name,store_name,time_post,goods_qa.reply_content',
            'limit' => $page['limit'],
            'order' => 'time_post desc',
            'count' => true,
            'conditions' => '1=1 '.$conditions,
        ));
        $page['item_count'] = $this->goodsqa_mod->getCount();
        $this->_format_page($page);
        $this->assign('page_info', $page);
        $this->assign ('list_data', $list_data);
        $this->display('goodsqa.index.html');
    }
    function delete()
    {
            $ques_id = empty($_GET['id']) ? 0 :trim($_GET['id']);
            $ids = explode(',',$ques_id);
            $conditions = "1 = 1 AND ques_id ".db_create_in($ids);
            $ms =& ms();
            foreach ($ids as $key => $val)
            {
                $title = Lang::get('drop_goodsqa_notice');
                $store = $this->goodsqa_mod->get(array(
                        'conditions' => 'ques_id ='.$val,
                        'join' => 'belongs_to_goods',
                        'fields' => 'goods_qa.store_id,goods_name,question_content',
                ));
                $content = sprintf(Lang::get('admin_drop_your_goodsqa'), LANG::get('goods'), addslashes($store['goods_name']), Lang::get('content_is'), addslashes($store['question_content']));
                $ms->pm->send(MSG_SYSTEM, $store['store_id'], $title, $content);
            }
            if ((!$res = $this->goodsqa_mod->drop($conditions)))
            {
                $this->show_warning('drop_failed');
                return;
            }
            else
            {
                $this->show_warning('drop_successful',
                    'to_qa_list', 'index.php?app=goodsqa');
                return;
            }
    }
}
?>