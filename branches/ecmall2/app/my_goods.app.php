<?php

define('THUMB_WIDTH', 300);
define('THUMB_HEIGHT', 300);
define('THUMB_QUALITY', 85);

/* 淘宝助理CSV字段编号 */
define('FIELD_GOODS_NAME',      0); // 商品名称
define('FIELD_PRICE',              7); // 商品价格
define('FIELD_STOCK',              9); // 库存
define('FIELD_IF_SHOW',        20); // 是否上架
define('FIELD_RECOMMENDED', 21); // 推荐
define('FIELD_ADD_TIME',       22); // 发布时间
define('FIELD_DESCRIPTION', 24); // 商品描述
define('FIELD_LAST_UPDATE', 31); // 更新时间
define('FIELD_GOODS_IMAGE', 35); // 商品图片
define('FIELD_GOODS_ATTR',  26); // 商品属性
define('FIELD_SALE_ATTR',      36); // 销售属性（规格）
define('FIELD_CID',                   1); // 商品类目cid


/* 商品管理控制器 */
class My_goodsApp extends StoreadminbaseApp
{
    var $_goods_mod;
    var $_spec_mod;
    var $_image_mod;
    var $_uploadedfile_mod;
    var $_store_id;

    /* 构造函数 */
    function __construct()
    {
         $this->My_goodsApp();
    }

    function My_goodsApp()
    {
        parent::__construct();

        $this->_store_id  = intval($this->visitor->get('manage_store'));
        $this->_goods_mod =& bm('goods', array('_store_id' => $this->_store_id));
        $this->_spec_mod  =& m('goodsspec');
        $this->_image_mod =& m('goodsimage');
        $this->_uploadedfile_mod =& m('uploadedfile');
    }

    function index()
    {
        /* 取得店铺商品分类 */
        $this->assign('sgcategories', $this->_get_sgcategory_options());

        /* 搜索条件 */
        $conditions = "1 = 1";
        if (trim($_GET['keyword']))
        {
            $str = "LIKE '%" . trim($_GET['keyword']) . "%'";
            //搜索货号
            //$conditions .= " AND (goods_name {$str} OR brand {$str} OR cate_name {$str})";
            $conditions .= " AND (goods_name {$str} OR brand {$str} OR cate_name {$str} OR sku {$str})";
        }
        if ($_GET['character'])
        {
            switch ($_GET['character'])
            {
                case 'show':
                    $conditions .= " AND if_show = 1";
                    break;
                case 'hide':
                    $conditions .= " AND if_show = 0";
                    break;
                case 'closed':
                    $conditions .= " AND closed = 1";
                    break;
                case 'recommended':
                    $conditions .= " AND g.recommended = 1";
                    break;
            }
        }
        if (intval($_GET['sgcate_id']) > 0)
        {
            $cate_mod =& bm('gcategory', array('_store_id' => $this->_store_id));
            $cate_ids = $cate_mod->get_descendant(intval($_GET['sgcate_id']));
        }
        else
        {
            $cate_ids = 0;
        }

        // 标识有没有过滤条件
        if ($conditions != '1 = 1')
        {
            $this->assign('filtered', 1);
        }

        //更新排序
        if (isset($_GET['sort']) && isset($_GET['order']))
        {
            $sort  = strtolower(trim($_GET['sort']));
            $order = strtolower(trim($_GET['order']));
            if (!in_array($order,array('asc','desc')))
            {
                $sort  = 'goods_id';
                $order = 'desc';
            }
        }
        else
        {
            $sort  = 'goods_id';
            $order = 'desc';
        }

        /* 取得商品列表 */
        $page = $this->_get_page();
        $goods_list = $this->_goods_mod->get_list(array(
            'conditions' => $conditions,
            'count' => true,
            'order' => "$sort $order",
            'limit' => $page['limit'],
        ), $cate_ids);
        //LLL my_goods商品列表语言处理
        $goods_list=chg_array2_string($goods_list, 'goods_name', get_lang2(), true);
        foreach ($goods_list as $key => $goods)
        {
            $goods_list[$key]['cate_name'] = $this->_goods_mod->format_cate_name($goods['cate_name']);
        }
        $this->assign('goods_list', $goods_list);

        $page['item_count'] = $this->_goods_mod->getCount();

        $this->_format_page($page);
        $this->assign('page_info', $page);
        $this->assign('order', $order);
        $this->assign('sort', $sort);
        $this->import_resource(array(
            'script' => array(
                array(
                    'path' => 'dialog/dialog.js',
                    'attr' => 'id="dialog_js" charset="utf-8"',
                ),
                array(
                    'path' => 'jquery.ui/jquery.ui.js',
                    'attr' => 'charset="utf-8"',
                ),
                array(
                    'path' => 'jquery.plugins/jquery.validate.js',
                    'attr' => 'charset="utf-8"',
                ),
                array(
                    'path' => 'utils.js',
                    'attr' => 'charset="utf-8"',
                ),
            ),
            'style' =>  'jquery.ui/themes/ui-lightness/jquery.ui.css',
      ));
        /* 当前页面信息 */
        $this->_curlocal(LANG::get('member_center'), 'index.php?app=member',
                         LANG::get('my_goods'), 'index.php?app=my_goods',
                         LANG::get('goods_list'));
        $this->_curitem('my_goods');
        $this->_curmenu('goods_list');
        //$this->import_resource(array('script' => 'utils.js'));
        $this->assign('page_title', Lang::get('member_center') . ' - ' . Lang::get('my_goods'));

        $this->display('my_goods.index.html');
    }

    function batch_edit()
    {
        if (!IS_POST)
        {
             /* 取得商品分类 */
             $this->assign('mgcategories', $this->_get_mgcategory_options(0)); // 商城分类第一级
             $this->assign('sgcategories', $this->_get_sgcategory_options());  // 店铺分类

             /* 当前页面信息 */
             $this->_curlocal(LANG::get('member_center'), 'index.php?app=member',
                              LANG::get('my_goods'), 'index.php?app=my_goods',
                              LANG::get('goods_add'));
             $this->_curitem('my_goods');
             $this->_curmenu('batch_edit');
             $this->assign('page_title', Lang::get('member_center') . Lang::get('my_goods'));

             $this->headtag('<script type="text/javascript" src="{lib file=mlselection.js}"></script>');
             $this->import_resource(array(
                 'script' => array(
                     array(
                         'path' => 'mlselection.js',
                         'attr' => 'charset="utf-8"',
                     ),
                     array(
                         'path' => 'my_goods.js',
                         'attr' => 'charset="utf-8"',
                     ),
                 ),
             ));
             $this->display('my_goods.batch.html');
        }
        else
        {
             $id = isset($_POST['id']) ? trim($_POST['id']) : '';
             if (!$id)
             {
                 $this->show_warning('Hacking Attempt');
                 return;
             }

             $ids = explode(',', $id);
             $ids = $this->_goods_mod->get_filtered_ids($ids); // 过滤掉非本店goods_id

             // edit goods
             $data = array();
             if ($_POST['cate_id'] > 0)
             {
                 $data['cate_id'] = $_POST['cate_id'];
                 $data['cate_name'] = $_POST['cate_name'];
             }
             if (trim($_POST['brand']))
             {
                 $data['brand'] = trim($_POST['brand']);
             }
             if ($_POST['if_show'] >= 0)
             {
                 $data['if_show'] = $_POST['if_show'] ? 1 : 0;
             }
             if ($_POST['recommended'] >= 0)
             {
                 $data['recommended'] = $_POST['recommended'] ? 1 : 0;
             }
             if ($data)
             {
                 $this->_goods_mod->edit($ids, $data);
             }

             // edit category_goods
             $cate_ids = array();
             foreach ($_POST['sgcate_id'] as $cate_id)
             {
                 if ($cate_id)
                 {
                     $cate_ids[] = intval($cate_id);
                 }
             }
             $cate_ids = array_unique($cate_ids);
             foreach ($ids as $goods_id)
             {
                 $this->_goods_mod->unlinkRelation('belongs_to_gcategory', $goods_id);
                 $this->_goods_mod->createRelation('belongs_to_gcategory', $goods_id, $cate_ids);
             }

             // edit goods_spec
             $sql = "";
             if ($_POST['price_change'])
             {
                 switch ($_POST['price_change'])
                 {
                     case 'change_to':
                         $sql .= "price = '" . floatval($_POST['price']) . "'";
                         break;
                     case 'inc_by':
                         $sql .= "price = price + '" . floatval($_POST['price']) . "'";
                         break;
                     case 'dec_by':
                         $sql .= "price = price - '" . floatval($_POST['price']) . "'";
                         break;
                 }
             }
             //改变市场价
             if ($_POST['price2_change'])
             {
                 switch ($_POST['price2_change'])
                 {
                     case 'change_to':
                         $sql .= "price2 = '" . floatval($_POST['price2']) . "'";
                         break;
                     case 'inc_by':
                         $sql .= "price2 = price2 + '" . floatval($_POST['price2']) . "'";
                         break;
                     case 'dec_by':
                         $sql .= "price2 = price2 - '" . floatval($_POST['price2']) . "'";
                         break;
                 }
             }

             if ($sql)
             {
                 $this->_spec_mod->edit("goods_id" . db_create_in($ids), $sql);
             }

             $sql = "";
             if ($_POST['stock_change'])
             {
                 switch ($_POST['stock_change'])
                 {
                     case 'change_to':
                         $sql .= "stock = '" . floatval($_POST['stock']) . "'";
                         break;
                     case 'inc_by':
                         $sql .= "stock = stock + '" . floatval($_POST['stock']) . "'";
                         break;
                     case 'dec_by':
                         $sql .= "stock = stock - '" . floatval($_POST['stock']) . "'";
                         break;
                 }
             }
             if ($sql)
             {
                 $this->_spec_mod->edit("goods_id" . db_create_in($ids), $sql);
             }

             $this->show_message('edit_ok',
                 'back_list', 'index.php?app=my_goods');
        }
    }

    function add()
    {
        /* LLL 屏蔽 检测支付方式、配送方式、商品数量等 */
        if(0)// (!$this->_addible())
        {
            return;
        }

        if (!IS_POST)
        {
             /* 添加传给iframe空的id,belong*/
             $this->assign("id", 0);
             $this->assign("belong", BELONG_GOODS);

             $this->assign('goods', $this->_get_goods_info(0));

             /* 取得游离状的图片 */
             $goods_images =array();
             $desc_images =array();
             $uploadfiles = $this->_uploadedfile_mod->find(array(
                 'join' => 'belongs_to_goodsimage',
                 'conditions' => "belong=".BELONG_GOODS." AND item_id=0 AND store_id=".$this->_store_id,
                 'order' => 'add_time ASC'
             ));
             foreach ($uploadfiles as $key => $uploadfile)
             {
                 if ($uploadfile['goods_id'] == null)
                 {
                     $desc_images[$key] = $uploadfile;
                 }
                 else
                 {
                     $goods_images[$key] = $uploadfile;
                 }
             }

             $this->assign('goods_images', $goods_images);
             $this->assign('desc_images', $desc_images);
             /* 取得商品分类 */
             $this->assign('mgcategories', $this->_get_mgcategory_options(0)); // 商城分类第一级
             $this->assign('sgcategories', $this->_get_sgcategory_options());  // 店铺分类

             /* 当前页面信息 */
             $this->_curlocal(LANG::get('member_center'), 'index.php?app=member',
                              LANG::get('my_goods'), 'index.php?app=my_goods',
                              LANG::get('goods_add'));
             $this->_curitem('my_goods');
             $this->_curmenu('goods_add');
             $this->assign('page_title', Lang::get('member_center') . ' - ' . Lang::get('goods_add'));

             /* 商品图片批量上传器 */
             $this->assign('images_upload', $this->_build_upload(array(
                 'obj' => 'GOODS_SWFU',
                 'belong' => BELONG_GOODS,
                 'item_id' => 0,
                 'button_text' => Lang::get('bat_upload'),
                 'progress_id' => 'goods_upload_progress',
                 'upload_url' => 'index.php?app=swfupload&instance=goods_image',
                 'if_multirow' => 1,
             )));

             /* 编辑器图片批量上传器 */
             $this->assign('editor_upload', $this->_build_upload(array(
                 'obj' => 'EDITOR_SWFU',
                 'belong' => BELONG_GOODS,
                 'item_id' => 0,
                 'button_text' => Lang::get('bat_upload'),
                 'button_id' => 'editor_upload_button',
                 'progress_id' => 'editor_upload_progress',
                 'upload_url' => 'index.php?app=swfupload&instance=desc_image',
                 'if_multirow' => 1,
                 'ext_js' => false,
                 'ext_css' => false,
             )));

             $this->import_resource(array(
                 'script' => array(
                     array(
                         'path' => 'mlselection.js',
                         'attr' => 'charset="utf-8"',
                     ),
                     array(
                         'path' => 'jquery.plugins/jquery.validate.js',
                         'attr' => 'charset="utf-8"',
                     ),
                     array(
                         'path' => 'jquery.ui/jquery.ui.js',
                         'attr' => 'charset="utf-8"',
                     ),
                     array(
                         'path' => 'my_goods.js',
                         'attr' => 'charset="utf-8"',
                     ),
                     array(
                         'attr' => 'id="dialog_js" charset="utf-8"',
                         'path' => 'dialog/dialog.js',
                     ),
                 ),
                 'style' =>  'jquery.ui/themes/ui-lightness/jquery.ui.css',
             ));
             /* 所见即所得编辑器 */
             $this->assign('build_editor', $this->_build_editor(array('name' => 'description')));
             $this->display('my_goods.form.html');
        }
        else
        {
            /* 取得数据 */
            $data = $this->_get_post_data(0);
            /* 检查数据 */
            if (!$this->_check_post_data($data, 0))
            {
                $this->show_warning($this->get_error());
                return;
            }
            /* 保存数据 */
            if (!$this->_save_post_data($data, 0))
            {
                $this->show_warning($this->get_error());
                return;
            }

            $this->show_message('add_ok',
                'back_list', 'index.php?app=my_goods',
                'continue_add', 'index.php?app=my_goods&amp;act=add'
            );
        }
    }

    function edit()
    {
        import('image.func');
        import('uploader.lib');
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!IS_POST)
        {
            /* 传给iframe id */
            $this->assign('id', $id);
            $this->assign('belong', BELONG_GOODS);
            if(!$id || !($goods = $this->_get_goods_info($id)))
            {
                $this->show_warning('no_such_goods');
                return;
            }
            //XXX 还只解决两种语言，多语言有待修改（最好是用javascript)
            $goods['cate_name']=get_part_string($goods['cate_name'],'', true);
            $goods['goods_name_it']=get_part_string($goods['goods_name'],'it', false);
            $goods['goods_name_sc']=get_part_string($goods['goods_name'],'sc', false);

            $this->assign('goods', $goods);
            /* 取到商品关联的图片 */
            $uploadedfiles = $this->_uploadedfile_mod->find(array(
                'fields' => "f.*,goods_image.*",
                'conditions' => "store_id=".$this->_store_id." AND belong=".BELONG_GOODS." AND item_id=".$id,
                'join'       => 'belongs_to_goodsimage',
                'order' => 'add_time ASC'
            ));
            $default_goods_images = array(); // 默认商品图片
            $other_goods_images = array(); // 其他商品图片
            $desc_images = array(); // 描述图片
            /*if (!empty($goods['default_image']))
            {
                   $goods_images
            }*/
            foreach ($uploadedfiles as $key => $uploadedfile)
            {
                if ($uploadedfile['goods_id'] == null)
                {
                    $desc_images[$key] = $uploadedfile;
                }
                else
                {
                    if (!empty($goods['default_image']) && ($uploadedfile['thumbnail'] == $goods['default_image']))
                    {
                        $default_goods_images[$key] = $uploadedfile;
                    }
                    else
                    {
                        $other_goods_images[$key] = $uploadedfile;
                    }
                }
            }

            $this->assign('goods_images', array_merge($default_goods_images, $other_goods_images));
            $this->assign('desc_images', $desc_images);

            /* 取得商品分类 */         
            $this->assign('mgcategories', $this->_get_mgcategory_options(0)); // 商城分类第一级
            $this->assign('sgcategories', $this->_get_sgcategory_options());  // 店铺分类

            /* 当前页面信息 */
            $this->_curlocal(LANG::get('member_center'), 'index.php?app=member',
                             LANG::get('my_goods'), 'index.php?app=my_goods',
                             LANG::get('goods_list'));
            $this->_curitem('my_goods');
            $this->_curmenu('edit_goods');
            $this->assign('page_title', Lang::get('member_center') . ' - ' . Lang::get('edit_goods'));

            $this->import_resource(array(
                'script' => array(
                    array(
                         'path' => 'mlselection.js',
                         'attr' => 'charset="utf-8"',
                    ),
                    array(
                         'path' => 'jquery.plugins/jquery.validate.js',
                         'attr' => 'charset="utf-8"',
                    ),
                    array(
                         'path' => 'jquery.ui/jquery.ui.js',
                         'attr' => 'charset="utf-8"',
                    ),
                    array(
                         'path' => 'my_goods.js',
                         'attr' => 'charset="utf-8"',
                     ),
                    array(
                        'attr' => 'id="dialog_js" charset="utf-8"',
                        'path' => 'dialog/dialog.js',
                    ),
                ),
                'style' =>  'jquery.ui/themes/ui-lightness/jquery.ui.css',
            ));

            /* 商品图片批量上传器 */
            $this->assign('images_upload', $this->_build_upload(array(
                'obj' => 'GOODS_SWFU',
                'belong' => BELONG_GOODS,
                'item_id' => $id,
                'button_text' => Lang::get('bat_upload'),
                'progress_id' => 'goods_upload_progress',
                'upload_url' => 'index.php?app=swfupload&instance=goods_image',
                'if_multirow' => 1,
            )));

            /* 编辑器图片批量上传器 */
            $this->assign('editor_upload', $this->_build_upload(array(
                'obj' => 'EDITOR_SWFU',
                'belong' => BELONG_GOODS,
                'item_id' => $id,
                'button_text' => Lang::get('bat_upload'),
                'button_id' => 'editor_upload_button',
                'progress_id' => 'editor_upload_progress',
                'upload_url' => 'index.php?app=swfupload&instance=desc_image',
                'if_multirow' => 1,
                'ext_js' => false,
                'ext_css' => false,
            )));

            /* 所见即所得编辑器 */
            $this->assign('build_editor', $this->_build_editor(array('name' => 'description')));
            $this->display('my_goods.form.html');
        }
        else
        {
            /* 取得数据 */
            $data = $this->_get_post_data($id);

            /* 检查数据 */
            if (!$this->_check_post_data($data, $id))
            {
                $this->show_warning($this->get_error());
                return;
            }
            /* 保存商品 */
            if (!$this->_save_post_data($data, $id))
            {
                $this->show_warning($this->get_error());
                return;
            }

            $this->show_message('edit_ok',
                'back_list', 'index.php?app=my_goods',
                'edit_again', 'index.php?app=my_goods&amp;act=edit&amp;id=' . $id);
        }
    }

   function spec_edit()
   {
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if (!IS_POST)
        {
            $goods_spec = $this->_goods_mod->findAll(array(
                'fields' => "this.goods_name,this.goods_id,this.spec_name_1,this.spec_name_2",
                'conditions' => "goods_id = $id",
                'include' => array('has_goodsspec' => array('order'=>'spec_id')),
            ));

            header('Content-Type:text/html;charset=' . CHARSET);
            $this->assign('goods', current($goods_spec));
            $this->display("spec_edit.html");
        }
        else
        {
            $data = $this->save_spec($_POST);
            if (empty($data))
            {
                $this->pop_warning('not_data');
            }
            foreach ($data as $key => $val)
            {
                $this->_spec_mod->edit($key, $val);
            }
            $this->pop_warning('ok', 'my_goods_spec_edit');
        }
   }

   function save_spec($spec)
   {
        $data = array();
        if (empty($spec['price']) || empty($spec['stock']) || empty($spec['price2']))
        {
            return $data;
        }
        foreach ($spec['price'] as $key => $val)
        {
            $data[$key]['price'] = $val;
        }
        foreach ($spec['price2'] as $key => $val)
        {
            $data[$key]['price2'] = $val;
        }
        foreach ($spec['stock'] as $key => $val)
        {
            $data[$key]['stock'] = $val;
        }
        return $data;
   }
     //异步修改数据
   function ajax_col()
   {
       $id        = empty($_GET['id']) ? 0 : intval($_GET['id']);
       $column = empty($_GET['column']) ? '' : trim($_GET['column']);
       $value  = isset($_GET['value']) ? trim($_GET['value']) : '';
       $data      = array('goods' => array(),
                          'specs' => array(),
                          'cates' => array());
       if (in_array($column ,array('goods_name','description', 'cate_id', 'cate_name', 'brand', 'spec_qty','if_show','closed','recommended')))
       {
           $data['goods'][$column] = $value;
           if($this->_goods_mod->edit($id, $data['goods']))
           {
               $result = $this->_goods_mod->get_info($id);
               $this->json_result($result[$column]);
           }
       }
       elseif (in_array($column, array('price', 'stock', 'sku','price2')))
       {
           $data['specs'][$column] = $value;
           if($this->_spec_mod->edit("goods_id = $id", $data['specs']))
           {
               $result = $this->_spec_mod->get("goods_id = $id");
               $this->json_result($result[$column]);
           }

       }
       else
       {
           $this->json_error('unallow edit');
           return ;
       }
   }

    function drop()
    {
        $id = isset($_GET['id']) ? trim($_GET['id']) : '';
        if (!$id)
        {
            $this->show_warning('no_goods_to_drop');
            return;
        }

        $ids = explode(',', $id);
        $this->_goods_mod->drop_data($ids);
        $rows = $this->_goods_mod->drop($ids);
        if ($this->_goods_mod->has_error())
        {
            $this->show_warning($this->_goods_mod->get_error());
            return;
        }

        $this->show_message('drop_ok');
    }

    /* 导出数据 */
    function export()
    {
        $goods_ids = isset($_GET['id']) ? trim($_GET['id']) : '';
        if ($goods_ids)
        {
            $condition = 'g.goods_id' . db_create_in(explode(',', $goods_ids));
        }
        else
        {
            $condition =array();
        }

        // 目标编码
        $to_charset = (CHARSET == 'utf-8') ? substr(LANG, 0, 2) == 'sc' ? 'gbk' : 'big5' : '';

        if (!IS_POST)
        {
            if (CHARSET == 'utf-8')
            {
                $this->assign('note_for_export', sprintf(LANG::get('note_for_export'), $to_charset));

                /* 当前页面信息 */
                $this->_curlocal(LANG::get('member_center'), 'index.php?app=member',
                                                   LANG::get('my_goods'), 'index.php?app=my_goods',
                                                   LANG::get('export'));
                $this->_curitem('my_goods');
                $this->_curmenu('export');
                $this->assign('page_title', Lang::get('member_center') . Lang::get('my_goods'));
                $this->display('common.export.html');

                return;
            }
        }
        else
        {
            if (!$_POST['if_convert'])
            {
                 $to_charset = '';
            }
        }

        $rows = array();
        $goods_list = $this->_goods_mod->get_list($condition, array(), true);
        $rows[] = array('goods_name', 'brand', 'price', 'stock', 'sku', 'description', 'default_image','price2');
        foreach ($goods_list as $goods)
        {
            $rows[] = array($goods['goods_name'], $goods['brand'], $goods['price'], $goods['stock'], $goods['sku'], $goods['description'], $goods['default_image'], $goods['price2']);
        }
        $this->export_to_csv($rows, 'goods', $to_charset);
    }

    /* 导入数据 */
    function import()
    {
        if (!IS_POST)
        {
            $this->assign('note_for_import', sprintf(LANG::get('note_for_import'), CHARSET));

            /* 当前页面信息 */
            $this->_curlocal(LANG::get('member_center'), 'index.php?app=member',
                             LANG::get('my_goods'), 'index.php?app=my_goods',
                             LANG::get('import'));
            $this->_curitem('my_goods');
            $this->_curmenu('import');
            $this->assign('page_title', Lang::get('member_center') . Lang::get('my_goods'));
            $this->display('common.import.html');
        }
        else
        {
            $file = $_FILES['csv'];
            if ($file['error'] != UPLOAD_ERR_OK)
            {
                $this->show_warning('select_file');
                return;
            }

            /* 取得还能上传的商品数，false表示不限制 */
            $store_mod =& m('store');
            $settings  = $store_mod->get_settings($this->_store_id);
            $remain       = $settings['goods_limit'] > 0 ? $settings['goods_limit'] - $this->_goods_mod->get_count() : false;

            $data = $this->import_from_csv($file['tmp_name'], true, $_POST['charset'], CHARSET);
            foreach ($data as $row)
            {
                /* 如果商品数超过限制了，中断 */
                if ($remain !== false)
                {
                    if ($remain <= 0)
                    {
                        $this->show_warning('goods_limit_arrived');
                        return;
                    }
                    else
                    {
                        $remain--;
                    }
                }

                $goods_name = trim($row[0]);
                if (!$goods_name || $this->_goods_mod->get("goods_name = '$goods_name'"))
                {
                    // 商品名为空或已存在，不处理
                    continue;
                }
                $image_url = trim($row[6]);
                $thumbnail = trim($row[6]);

                $goods = array(
                    'type'                   => 'material',
                    'spec_qty'            => 0,
                    'if_show'             => 1,
                    'closed'              => 0,
                    'add_time'            => gmtime(),
                    'last_update'      => gmtime(),
                    'recommended'      => 1,
                    'goods_name'       => $goods_name,
                    'brand'                  => trim($row[1]),
                    'description'      => trim($row[5]),
                    'default_image' => $thumbnail,
                );
                $goods_id = $this->_goods_mod->add($goods);
                if ($this->_goods_mod->has_error())
                {
                    $this->show_warning($this->_goods_mod->get_error());
                    return;
                }

                $spec = array(
                    'goods_id' => $goods_id,
                    'price'       => floatval($row[2]),
                    'stock'       => intval($row[3]),
                    'sku'            => $row[4],
                    'price2'  =>floatval($row[7]),
                );
                $spec_id = $this->_spec_mod->add($spec);
                if ($this->_spec_mod->has_error())
                {
                    $this->show_warning($this->_spec_mod->get_error());
                    return;
                }

                if ($image_url)
                {
                    $image = array(
                        'goods_id'      => $goods_id,
                        'image_url'  => $image_url,
                        'thumbnail'  => $thumbnail,
                        'sort_order' => 255,
                    );
                    $this->_image_mod->add($image);
                }

                $this->_goods_mod->edit($goods_id, array('default_spec' => $spec_id));
            }

            $this->show_message('import_ok',
                'back_list', 'index.php?app=my_goods');
        }
    }

    function unicodeToUtf8($str,$order="little")
    {
        $utf8string ="";
        $n=strlen($str);
        for ($i=0;$i<$n ;$i++ )
        {
            if ($order=="little")
            {
                $val = str_pad(dechex(ord($str[$i+1])), 2, 0, STR_PAD_LEFT) .
                       str_pad(dechex(ord($str[$i])),      2, 0, STR_PAD_LEFT);
            }
            else
            {
                $val = str_pad(dechex(ord($str[$i])),      2, 0, STR_PAD_LEFT) .
                       str_pad(dechex(ord($str[$i+1])), 2, 0, STR_PAD_LEFT);
            }
            $val = intval($val,16); // 由于上次的.连接，导致$val变为字符串，这里得转回来。
            $i++; // 两个字节表示一个unicode字符。
            $c = "";
            if($val < 0x7F)
            { // 0000-007F
                $c .= chr($val);
            }
            elseif($val < 0x800)
            { // 0080-07F0
                $c .= chr(0xC0 | ($val / 64));
                $c .= chr(0x80 | ($val % 64));
            }
            else
            { // 0800-FFFF
                $c .= chr(0xE0 | (($val / 64) / 64));
                $c .= chr(0x80 | (($val / 64) % 64));
                $c .= chr(0x80 | ($val % 64));
            }
            $utf8string .= $c;
        }
        /* 去除bom标记 才能使内置的iconv函数正确转换 */
        if (ord(substr($utf8string,0,1)) == 0xEF && ord(substr($utf8string,1,2)) == 0xBB && ord(substr($utf8string,2,1)) == 0xBF)
        {
            $utf8string = substr($utf8string,3);
        }
        return $utf8string;
    }

           /* 导入淘宝助理数据 */
    function import_taobao()
    {
        /* 检测支付方式、配送方式、商品数量等 */
        if (!$this->_addible()) {
            return;
        }
        if (!IS_POST)
        {
            $this->assign('page_title', Lang::get('member_center') . ' - ' . Lang::get('import_taobao'));
            $this->_curlocal(LANG::get('member_center'), 'index.php?app=member',
                             LANG::get('my_goods'), 'index.php?app=my_goods',
                             LANG::get('import_taobao'));
            $this->_curitem('my_goods');
            $this->_curmenu('import_taobao');

            $this->assign('build_upload', $this->_build_upload(array(
                'itme_id'                    => 0,
                'belong'                        => BELONG_GOODS,
                'image_file_type'      => 'gif|jpg|jpeg|png|tbi',
                'upload_url'              => 'index.php?app=swfupload&act=taobao_image',
            ))); // 构建swfupload上传组件
            $this->import_resource(array(
                'script' => array(
                    array(
                        'path' => 'mlselection.js',
                        'attr' => 'charset="utf-8"',
                    ),
                    array(
                        'path' => 'my_goods.js',
                        'attr' => 'charset="utf-8"',
                    ),
                ),
                ));
            /* 取得商品分类 */
            $this->assign('mgcategories', $this->_get_mgcategory_options(0)); // 商城分类第一级
            $this->assign('sgcategories', $this->_get_sgcategory_options());  // 店铺分类
            $this->assign('step', (isset($_GET['step']) && $_GET['step'] == 2) ? 2 : 1);
            $this->display('import.taobao.html');
        }
        else
        {
            $file = $_FILES['csv'];
            if ($file['error'] != UPLOAD_ERR_OK)
            {
                $this->show_warning('select_file');
                return;
            }
            import('uploader.lib'); // 导入上传类
            $uploader = new Uploader();
            $uploader->allowed_type('csv'); // 限制文件类型
            $uploader->allowed_size(SIZE_CSV_TAOBAO); // 限制单个文件大小2M
            $uploader->addFile($file);
            if (!$uploader->file_info())
            {
                $this->show_warning($uploader->get_error());
                return;
            }

            /* 取得还能上传的商品数，false表示不限制 */
            $store_mod =& m('store');
            $settings  = $store_mod->get_settings($this->_store_id);
            $remain       = $settings['goods_limit'] > 0 ? $settings['goods_limit'] - $this->_goods_mod->get_count() : false;

            /* 初始化统计 */
            $num_image = 0; // 需要导入的图片数量
            $num_record = 0; // 成功导入的记录条数

            $csv_string = $this->unicodeToUtf8(file_get_contents($file['tmp_name']));
            if (CHARSET =='big5')
            {
                $csv_string = ecm_iconv('utf-8', 'gbk', $csv_string);//dump($chs);
                $csv_string = ecm_iconv('gbk', 'big5', $csv_string);
            }
            else
            {
                $csv_string = ecm_iconv('utf-8', CHARSET, $csv_string);
            }


            $csv_string = addslashes($csv_string); // 必须在转码后进行引号转义

            $records = $this->_parse_taobao_csv($csv_string);
            foreach ($records as $record)
            {
                // 如果商品名称为空则跳过
                if (!trim($record[FIELD_GOODS_NAME]) || $find_goods)
                {
                    continue;
                }

                if ($remain !== false) // 如果店铺等级有商品数量限制
                {
                    if ($remain <= 0)
                    {
                        if ($num_record == 0) // 还没有导入商品数就超过限制了
                        {
                            $this->show_warning('goods_limit_arrived');
                            return;
                        }
                        else // 导入部分商品时超限
                        {
                            if ($num_image>0) // 需要上传图片
                            {
                                $this->show_message(sprintf(Lang::get('import_part_ok_need_image'), $num_record, $num_image),
                                'upload_taobao_image', 'index.php?app=my_goods&act=import_taobao&step=2');
                            }
                            else // 不需要上传图片
                            {
                                $this->show_message(sprintf(Lang::get('import_part_ok'), $num_record),
                                'back_list', 'index.php?app=my_goods');
                            }
                        }
                        exit();
                    }
                    else
                    {
                        if ($record[FIELD_GOODS_IMAGE])
                        {
                               $num_image++;
                        }
                        $remain--;
                    }
                }
                else
                {
                    if ($record[FIELD_GOODS_IMAGE]) // 店铺等级无商品数量限制
                    {
                        $num_image++;
                    }
                }

                $goods = array(
                    'type'                   => 'material',
                    'brand'                   => '',
                    'cate_id'             => $_POST['cate_id'],
                    'cate_name'        => $_POST['cate_name'],
                    'spec_qty'            => 0,
                    'goods_name'       => $record[FIELD_GOODS_NAME],
                    'store_id'            => $this->_store_id,
                    'description'      => $record[FIELD_DESCRIPTION],
                    'if_show'             => $record[FIELD_IF_SHOW],
                    'add_time'            => $record[FIELD_ADD_TIME],
                    'last_update'      => $record[FIELD_LAST_UPDATE],
                    'recommended'      => $record[FIELD_RECOMMENDED],
                    'default_image' => $record[FIELD_GOODS_IMAGE],
                    'closed'              => 0,
                );
                $goods_id = $this->_goods_mod->add($goods);
                if ($this->_goods_mod->has_error())
                {
                    $this->show_warning($this->_goods_mod->get_error());
                    return;
                }

                /* 商品分类 */
                if ($_POST['sgcate_id'])
                {
                    $this->_goods_mod->createRelation('belongs_to_gcategory', $goods_id, $_POST['sgcate_id']);
                }

                /* 规格 */

                $spec_qty = 0;

                if ($record[FIELD_SALE_ATTR]) // 有规格
                {
                    $spec_info = $this->_parse_tabao_prop($record[FIELD_CID], $record[FIELD_SALE_ATTR] ,$goods_id); //dump($spec_info);
                    //dump($spec_info);
                    if (isset($spec_info['msg']))
                    {
                        $this->show_warning($prop['msg']);
                        return;
                    }
                    if ($spec_info)
                    {
                        $spec_data = $spec_info['item'];
                        $spec_qty  = $spec_info['spec_kind'];
                        $spec_name = $spec_info['spec_name'];
                    }
                    if ($spec_qty > 2 || !$spec_info)
                    { // 有两个以上规格或淘宝接口没有获取到属性，视无规格处理
                        $spec_qty = 0;
                        $spec_data = array();
                        $spec_data[0] = array(
                               'goods_id' => $goods_id,
                               'price'       => floatval($record[FIELD_PRICE]),
                               'stock'       => intval($record[FIELD_STOCK]),
                        );
                        $spec_name =array();
                    }
                }
                else // 没有规格
          {
                 $spec_data[0] = array(
                     'goods_id' => $goods_id,
                     'price'       => floatval($record[FIELD_PRICE]),
                     'stock'       => intval($record[FIELD_STOCK]),
                 );
                 $spec_name =array();
                }

                $default_spec = 0; // 初始化默认规格

                foreach ($spec_data as $spec)
                {
                    $spec['goods_id'] = $goods_id;
                    $spec_id = $this->_spec_mod->add($spec);
                    if (!$spec_id)
                    {
                              $this->_error($this->_spec_mod->get_error());
                              return false;
                    }
                    !$default_spec && $default_spec = $spec_id; // 取第一个规格为默认规格
                }

                if (!$this->_goods_mod->edit($goods_id, array_merge($spec_name, array('default_spec' => $default_spec, 'spec_qty' => $spec_qty))))
                {
                    $this->_error($this->_goods_mod->get_error());
                    return false;
                }
                $num_record ++;
            }

            if ($num_image>0)
            {
                $this->show_message(sprintf(Lang::get('import_ok_need_image'), $num_record, $num_image),
                'upload_taobao_image', 'index.php?app=my_goods&act=import_taobao&step=2');
            }
            else
            {
                $this->show_message(sprintf(Lang::get('import_ok'), $num_record),
                'back_list', 'index.php?app=my_goods');
            }

        }
    }

    /* 解析淘宝助理CSV数据 */
    function _parse_taobao_csv($csv_string)
    {
        $records = explode("\n", trim($csv_string, "\n"));
        array_shift($records); // 去掉标题
        foreach ($records as $key => $record)
        { // dump($records);
            $record = explode("\t", trim($record, "\t")); // 按制表符构建每一行数据
            foreach ($record as $k => $col)
            {
                $col = trim($col); // 去掉数据两端的空格
                $col = trim($col, "\\\""); // 去掉数据两端的\"
                $col = trim($col, "\""); // 去掉数据两端的"

                /* 对字段数据进行分别处理 */
                switch ($k)
                {
                    case FIELD_ADD_TIME :             $record[$k] = ($col== '1980-01-01 00:00:00' || $col== '') ? gmtime() : gmstr2time($col); break;
                    case FIELD_LAST_UPDATE :       $record[$k] = ($col== '1980-01-01 00:00:00' || $col== '') ? $record[FIELD_ADD_TIME] : gmstr2time($col); break;
                    case FIELD_DESCRIPTION :       $record[$k] = str_replace(array("\\\"\\\"", "\"\""), array("\\\"", "\""), $col); break;
                    case FIELD_GOODS_IMAGE :       $record[$k] = substr($col, 0 , strpos($col, ':')); break;
                    case FIELD_IF_SHOW :              $record[$k] = $col == 1 ? 0 : 1; break;
                    case FIELD_GOODS_NAME :        $record[$k] = $col; break;
                    case FIELD_STOCK :                   $record[$k] = $col; break;
                    case FIELD_PRICE :                   $record[$k] = $col; break;
                    case FIELD_RECOMMENDED :       $record[$k] = $col; break;
                    case FIELD_GOODS_ATTR :        $record[$k] = $col; break;
                    case FIELD_SALE_ATTR :            $record[$k] = $col; break;
                    case FIELD_CID :            $record[$k] = $col; break;
                    default:                                      unset($record[$k]);
                }
            }
            $records[$key] = $record;
        }
        return $records;
    }

    /* 解析淘宝的销售属性 返回ECMall规格 */
    function _parse_tabao_prop($cid, $pvs, $goods_id)
    {
        $i = 0; // 规格数量
        $spec_kind = 0; // 规格种类数
        $spec_price_stock = array(); // 价格和库存
        $arr_temp = explode(';', $pvs);
        $pvs = ''; // 淘宝销售属性编码

        /* 分离库存价格与属性编码 */
        foreach ($arr_temp as $k => $v)
        {
            $pos_2 = strpos($v, '::');
            if ($pos_2>0)
            {
                $pos_1 = strpos($v, ':'); //dump($_pos);
                //$price_stock = explode(':', substr($v, 0,))
                $spec_price_stock[$i]['price'] = round(substr($v, 0, $pos_1), 2);
                $spec_price_stock[$i]['stock'] = substr($v, $pos_1 + 1, $pos_2 - $pos_1 - 1);
                $pvs .= substr($v, $pos_2 + 2) . ';';
                $i++;
            }
            else if ($v)
            {
                $pvs .= $v . ';';
            }
        }
        $spec_kind = substr_count($pvs, ';') / count($spec_price_stock);

        /* 根据编码解析销售属性 */
        import('taobaoprop.lib');
        $TaobaoProp = new TaobaoProp($cid, $pvs);
        $prop = $TaobaoProp->get_prop();

        if (!$prop || $TaobaoProp->has_error())
        {
            return array();
        }
        if (CHARSET == 'big5')
        {
            $prop = ecm_iconv_deep('utf-8', 'gbk', $prop);
            $prop = ecm_iconv_deep('gbk', 'big5', $prop);
        }
        else
        {
            $prop = ecm_iconv_deep('utf-8', CHARSET, $prop);
        }

        /* 组合成ECMall规格 */
        $spec = array(); // 规格数据
        foreach ($spec_price_stock as $_k => $_v)
        {
            $spec['item'][$_k] = $_v;
            $spec['item'][$_k]['goods_id'] = $goods_id;
            if ($spec_kind == 2)
            {
                $spec['item'][$_k]['spec_1'] = $prop['prop_value'][2 * $_k]['name'];
                $spec['item'][$_k]['spec_2'] = $prop['prop_value'][2 * $_k + 1]['name'];
                $spec['spec_name'] = array(
                    'spec_name_1' => $prop['prop_value'][0]['prop_name'],
                    'spec_name_2' => $prop['prop_value'][1]['prop_name'],
                );
            }
            else if ($spec_kind = 1)
            {
                $spec['item'][$_k]['spec_1'] = $prop['prop_value'][$_k]['name'];
                $spec['spec_name'] = array(
                    'spec_name_1' => $prop['prop_value'][0]['prop_name'],
                );
            }
        }
        $spec['spec_kind'] = $spec_kind;
        return addslashes_deep($spec); // 因经过转码，必须要重新转义
    }

    function drop_image()
    {
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        $uploadedfile = $this->_uploadedfile_mod->get(array(
                  'conditions' => "f.file_id = '$id' AND f.store_id = '{$this->_store_id}'",
                  'join' => 'belongs_to_goodsimage',
                  'fields' => 'goods_image.image_url, goods_image.thumbnail, goods_image.image_id, f.file_id',
        ));
        if ($uploadedfile)
        {
            $this->_uploadedfile_mod->drop($id);
            if ($this->_image_mod->drop($uploadedfile['image_id']))
            {
                // 删除文件
                if (file_exists(ROOT_PATH . '/' . $uploadedfile['image_url']))
                {
                       @unlink(ROOT_PATH . '/' . $uploadedfile['image_url']);
                }
                if (file_exists(ROOT_PATH . '/' . $uploadedfile['thumbnail']))
                {
                       @unlink(ROOT_PATH . '/' . $uploadedfile['thumbnail']);
                }

                $this->json_result($id);
                return;
            }
            $this->json_result($id);
            return;
        }
        $this->json_error(Lang::get('no_image_droped'));
    }

    function _get_member_submenu()
    {
        if (ACT == 'index')
        {
            $menus = array(
                array(
                    'name' => 'goods_list',
                    'url'  => 'index.php?app=my_goods',
                ),
            );
        }
        else
        {
             $menus = array(
                 array(
                     'name' => 'goods_list',
                     'url'  => 'index.php?app=my_goods',
                 ),
                 array(
                     'name' => 'goods_add',
                     'url'  => 'index.php?app=my_goods&amp;act=add',
                 ),
                 array(
                     'name' => 'import_taobao',
                     'url'  => 'index.php?app=my_goods&amp;act=import_taobao',
                 ),
             );
        }
        if (ACT == 'batch_edit')
        {
            $menus[] = array(
                'name' => 'batch_edit',
                'url'  => '',
            );
        }
        elseif (ACT == 'edit')
        {
            $menus[] = array(
                'name' => 'edit_goods',
                'url'  => '',
            );
        }

        return $menus;
    }

    /* 构造并返回树 */
    function &_tree($gcategories)
    {
        import('tree.lib');
        $tree = new Tree();
        $tree->setTree($gcategories, 'cate_id', 'parent_id', 'cate_name');
        return $tree;
    }

    /* 取得本店所有商品分类 */
    function _get_sgcategory_options()
    {
        $mod =& bm('gcategory', array('_store_id' => $this->_store_id));
        $gcategories = $mod->get_list();
        //LLL 本店所有商品分类多语言
        $gcategories=chg_array2_string($gcategories, 'cate_name', '', true);
        
        import('tree.lib');
        $tree = new Tree();
        $tree->setTree($gcategories, 'cate_id', 'parent_id', 'cate_name');
        return $tree->getOptions();
    }

    /* 取得商城商品分类，指定parent_id */
    function _get_mgcategory_options($parent_id = 0)
    {
        $res = array();
        $mod =& bm('gcategory', array('_store_id' => 0));
        $gcategories = $mod->get_list($parent_id, true);
        foreach ($gcategories as $gcategory)
        {
            //LLL 本店所有商品分类多语言
            $res[$gcategory['cate_id']] =get_part_string($gcategory['cate_name'], '', true);
        }
        return $res;
    }

    /**
     * 上传商品图片
     *
     * @param int $goods_id
     * @return bool
     */
    function _upload_image($goods_id)
    {
        import('image.func');
        import('uploader.lib');
        $uploader = new Uploader();
        $uploader->allowed_type(IMAGE_FILE_TYPE);
        $uploader->allowed_size(SIZE_GOODS_IMAGE); // 400KB

        /* 取得剩余空间（单位：字节），false表示不限制 */
        $store_mod  =& m('store');
        $settings      = $store_mod->get_settings($this->_store_id);
        $upload_mod =& m('uploadedfile');
        $remain        = $settings['space_limit'] > 0 ? $settings['space_limit'] * 1024 * 1024 - $upload_mod->get_file_size($this->_store_id) : false;

        $files = $_FILES['new_file'];
        foreach ($files['error'] as $key => $error)
        {
            if ($error == UPLOAD_ERR_OK)
            {
                /* 处理文件上传 */
                $file = array(
                    'name'            => $files['name'][$key],
                    'type'            => $files['type'][$key],
                    'tmp_name'  => $files['tmp_name'][$key],
                    'size'            => $files['size'][$key],
                    'error'        => $files['error'][$key]
                );
                $uploader->addFile($file);
                if (!$uploader->file_info())
                {
                    $this->_error($uploader->get_error());
                    return false;
                }

                /* 判断能否上传 */
                if ($remain !== false)
                {
                    if ($remain < $file['size'])
                    {
                        $this->_error('space_limit_arrived');
                        return false;
                    }
                    else
                    {
                        $remain -= $file['size'];
                    }
                }

                $uploader->root_dir(ROOT_PATH);
                $dirname      = 'data/files/store_' . $this->_store_id . '/goods_' . (time() % 200);
                $filename  = $uploader->random_filename();
                $file_path = $uploader->save($dirname, $filename);
                $thumbnail = dirname($file_path) . '/small_' . basename($file_path);
                make_thumb(ROOT_PATH . '/' . $file_path, ROOT_PATH . '/' . $thumbnail, THUMB_WIDTH, THUMB_HEIGHT, THUMB_QUALITY);

                /* 处理文件入库 */
                $data = array(
                    'store_id'  => $this->_store_id,
                    'file_type' => $file['type'],
                    'file_size' => $file['size'],
                    'file_name' => $file['name'],
                    'file_path' => $file_path,
                    'add_time'  => gmtime(),
                );
                $uf_mod =& m('uploadedfile');
                $file_id = $uf_mod->add($data);
                if (!$file_id)
                {
                    $this->_error($uf_mod->get_error());
                    return false;
                }

                /* 处理商品图片入库 */
                $data = array(
                    'goods_id'      => $goods_id,
                    'image_url'  => $file_path,
                    'thumbnail'  => $thumbnail,
                    'sort_order' => 255,
                    'file_id'       => $file_id,
                );
                if (!$this->_image_mod->add($data))
                {
                    $this->_error($this->_image_mod->get_error());
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * 检测店铺是否能添加商品
     *
     */
    function _addible()
    {
        $payment_mod =& m('payment');
        $payments = $payment_mod->get_enabled($this->_store_id);
        if (empty($payments))
        {
            $this->show_warning('please_install_payment', 'go_payment', 'index.php?app=my_payment');
                  return false;
        }

        $shipping_mod =& m('shipping');
        $shippings = $shipping_mod->find("store_id = '{$this->_store_id}' AND enabled = 1");
        if (empty($shippings))
        {
                  $this->show_warning('please_install_shipping', 'go_shipping', 'index.php?app=my_shipping');
                  return false;
        }

        /* 判断商品数是否已超过限制 */
        $store_mod =& m('store');
        $settings = $store_mod->get_settings($this->_store_id);
        if ($settings['goods_limit'] > 0)
        {
                  $goods_count = $this->_goods_mod->get_count();
                  if ($goods_count >= $settings['goods_limit'])
                  {
                         $this->show_warning('goods_limit_arrived');
                         return false;
                  }
        }
        return true;
    }
    /**
     * 保存远程图片
     */
    function _add_remote_image($goods_id)
    {
        foreach ($_POST['new_url'] as $image_url)
        {
            if ($image_url && $image_url != 'http://')
            {
                $data = array(
                    'goods_id' => $goods_id,
                    'image_url' => $image_url,
                    'thumbnail' => $image_url, // 远程图片暂时没有小图
                    'sort_order' => 255,
                    'file_id' => 0,
                );
                if (!$this->_image_mod->add($data))
                {
                    $this->_error($this->_image_mod->get_error());
                    return false;
                }
            }
        }

        return true;
    }
    /**
     * 编辑图片
     */
    function _edit_image($goods_id)
    {
        if (isset($_POST['old_order']))
        {
            foreach ($_POST['old_order'] as $image_id => $sort_order)
            {
                $data = array('sort_order' => $sort_order);
                if (isset($_POST['old_url'][$image_id]))
                {
                    $data['image_url'] = $_POST['old_url'][$image_id];
                }
                $this->_image_mod->edit("image_id = '$image_id' AND goods_id = '$goods_id'", $data);
            }
        }

        return true;
    }

    /**
     * 取得商品信息
     */
    function _get_goods_info($id = 0)
    {
        $default_goods_image = Conf::get('default_goods_image'); // 商城默认商品图片
        if ($id > 0)
        {
            $goods_info = $this->_goods_mod->get_info($id);
            if ($goods_info === false)
            {
                return false;
            }
            $goods_info['default_goods_image'] = $default_goods_image;
            if (empty($goods_info['default_image']))
            {
                   $goods_info['default_image'] = $default_goods_image;
            }
        }
        else
        {
            $goods_info = array(
                'cate_id' => 0,
                'if_show' => 1,
                'recommended' => 1,
                'price' => 1,
                'stock' => 1,
                'spec_qty' => 0,
                'spec_name_1' => Lang::get('color'),
                'spec_name_2' => Lang::get('size'),
                'default_goods_image' => $default_goods_image,
                'price2'=> 1,
            );
        }
        $goods_info['spec_json'] = ecm_json_encode(array(
            'spec_qty' => $goods_info['spec_qty'],
            'spec_name_1' => isset($goods_info['spec_name_1']) ? $goods_info['spec_name_1'] : '',
            'spec_name_2' => isset($goods_info['spec_name_2']) ? $goods_info['spec_name_2'] : '',
            'specs' => $goods_info['_specs'],
        ));
        return $goods_info;
    }

    /**
     * 提交的数据
     */
    function _get_post_data($id = 0)
    {
        $goods = array(
            'goods_name'       => $_POST['goods_name'],
            'description'      => $_POST['description'],
            'cate_id'             => $_POST['cate_id'],
            'cate_name'        => $_POST['cate_name'],
            'brand'                  => $_POST['brand'],
            'if_show'             => $_POST['if_show'],
            'last_update'      => gmtime(),
            'recommended'      => $_POST['recommended'],
        );
        $spec_name_1 = !empty($_POST['spec_name_1']) ? $_POST['spec_name_1'] : '';
        $spec_name_2 = !empty($_POST['spec_name_2']) ? $_POST['spec_name_2'] : '';
        if ($spec_name_1 && $spec_name_2)
        {
            $goods['spec_qty'] = 2;
        }
        elseif ($spec_name_1 || $spec_name_2)
        {
            $goods['spec_qty'] = 1;
        }
        else
        {
            $goods['spec_qty'] = 0;
        }

        $goods_file_id = array();
        $desc_file_id =array();
        if (isset($_POST['goods_file_id']))
        {
            $goods_file_id = $_POST['goods_file_id'];
        }
        if (isset($_POST['desc_file_id']))
        {
            $desc_file_id = $_POST['desc_file_id'];
        }
        if ($id <= 0)
        {
            $goods['type'] = 'material';
            $goods['closed'] = 0;
            $goods['add_time'] = gmtime();
        }

        $specs = array();
        switch ($goods['spec_qty'])
        {
            case 0: // 没有规格
                $specs[] = array(
                    'price' => floatval($_POST['price']),
                    'stock' => intval($_POST['stock']),
                    'sku'      => trim($_POST['sku']),
                    'price2'=>floatval($_POST['price2']),
                );
                break;
            case 1: // 一个规格
                $goods['spec_name_1'] = $spec_name_1 ? $spec_name_1 : $spec_name_2;
                $goods['spec_name_2'] = '';
                $spec_data = $spec_name_1 ? $_POST['spec_1'] : $_POST['spec_2'];
                foreach ($spec_data as $key => $spec_1)
                {
                    $spec_1 = trim($spec_1);
                    if ($spec_1)
                    {
                        $specs[$spec_1] = array(
                            'spec_1' => $spec_1,
                            'price'  => floatval($_POST['price'][$key]),
                            'stock'  => intval($_POST['stock'][$key]),
                            'sku'       => trim($_POST['sku'][$key]),
                            'price2'  => floatval($_POST['price2'][$key]),
                        );
                    }
                }
                break;
            case 2: // 二个规格
                $goods['spec_name_1'] = $spec_name_1;
                $goods['spec_name_2'] = $spec_name_2;
                foreach ($_POST['spec_1'] as $key => $spec_1)
                {
                    $spec_1 = trim($spec_1);
                    $spec_2 = trim($_POST['spec_2'][$key]);
                    if ($spec_1 && $spec_2)
                    {
                        $specs[$spec_1 . '!@#$%^&*()' . $spec_2] = array(
                            'spec_1' => $spec_1,
                            'spec_2' => $spec_2,
                            'price'  => floatval($_POST['price'][$key]),
                            'stock'  => intval($_POST['stock'][$key]),
                            'sku'       => trim($_POST['sku'][$key]),
                            'price2'  => floatval($_POST['price2'][$key]),
                        );
                    }
                }
                break;
            default:
                break;
        }

        /* 分类 */
        $cates = array();

        foreach ($_POST['sgcate_id'] as $cate_id)
        {
            if (intval($cate_id) > 0)
            {
                $cates[$cate_id] = array(
                    'cate_id'      => $cate_id,
                );
            }
        }

        return array('goods' => $goods, 'specs' => $specs, 'cates' => $cates, 'goods_file_id' => $goods_file_id, 'desc_file_id' => $desc_file_id);
    }

    /**
     * 检查提交的数据
     */
    function _check_post_data($data, $id = 0)
    {
        if (!$this->_goods_mod->unique(trim($data['goods']['goods_name']), $id))
        {
            $this->_error('name_exist');
            return false;
        }
        if ($data['goods']['spec_qty'] == 1 && empty($data['goods']['spec_name_1'])
                  || $data['goods']['spec_qty'] == 2 && (empty($data['goods']['spec_name_1']) || empty($data['goods']['spec_name_2'])))
        {
            $this->_error('fill_spec_name');
            return false;
        }
        if (empty($data['specs']))
        {
            $this->_error('fill_spec');
            return false;
        }
        return true;
    }

    /**
     * 保存数据
     */
    function _save_post_data($data, $id = 0)
    {
        import('image.func');
        import('uploader.lib');
        /* 保存商品 */
        if ($id > 0)
        {
            // edit
            if (!$this->_goods_mod->edit($id, $data['goods']))
            {
                $this->_error($this->_goods_mod->get_error());
                return false;
            }

            $goods_id = $id;
        }
        else
        {
            // add
            $goods_id = $this->_goods_mod->add($data['goods']);
            if (!$goods_id)
            {
                $this->_error($this->_goods_mod->get_error());
                return false;
            }
            if (($data['goods_file_id'] || $data['desc_file_id'] ))
            {
                $uploadfiles = array_merge($data['goods_file_id'], $data['desc_file_id']);
                $this->_uploadedfile_mod->edit(db_create_in($uploadfiles, 'file_id'), array('item_id' => $goods_id));
            }
            if (!empty($data['goods_file_id']))
            {
                $this->_image_mod->edit(db_create_in($data['goods_file_id'], 'file_id'), array('goods_id' => $goods_id));
            }
        }
        /* 保存规格 */
        if ($id > 0)
        {
            // 删除已有规格
            $this->_spec_mod->drop("goods_id = '$id'");
            if ($this->_spec_mod->has_error())
            {
                $this->_error($this->_spec_mod->get_error());
                return false;
            }
        }

        $default_spec = 0; // 初始化默认规格
        foreach ($data['specs'] as $spec)
        {
            $spec['goods_id'] = $goods_id;
            $spec_id = $this->_spec_mod->add($spec);
            if (!$spec_id)
            {
                $this->_error($this->_spec_mod->get_error());
                return false;
            }
            !$default_spec && $default_spec = $spec_id; // 取第一个规格为默认规格
        }

        /* 更新默认规格 */
        if (!$this->_goods_mod->edit($goods_id, array('default_spec' => $default_spec)))
        {
            $this->_error($this->_goods_mod->get_error());
            return false;
        }

        /* 保存商品分类 */
        $this->_goods_mod->unlinkRelation('belongs_to_gcategory', $goods_id);
        if ($data['cates'])
        {
            $this->_goods_mod->createRelation('belongs_to_gcategory', $goods_id, $data['cates']);
        }

        /* 设置默认图片 */
        if (isset($data['goods_file_id'][0]))
        {
            $default_image = $this->_image_mod->get(array(
                'fields' => 'thumbnail',
                'conditions' => "goods_id = '$goods_id' AND file_id = '{$data[goods_file_id][0]}'",
            ));
            $this->_image_mod->edit("goods_id = $goods_id", array('sort_order' => 255));
            $this->_image_mod->edit("goods_id = $goods_id AND file_id = '{$data[goods_file_id][0]}'", array('sort_order' => 1));
        }

        $this->_goods_mod->edit($goods_id, array(
            'default_image' => $default_image ? $default_image['thumbnail'] : '',
        ));

        return true;
    }
}

?>