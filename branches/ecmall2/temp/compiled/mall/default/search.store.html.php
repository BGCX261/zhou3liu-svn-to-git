<?php echo $this->fetch('header.html'); ?>
<?php echo $this->fetch('curlocal.html'); ?>
<script type="text/javascript">
//<!CDATA[
$(function (){
    var order = '<?php echo $_GET['order']; ?>';
    var arrow = '';

    switch (order){
        case 'credit_value desc' : order = '';
        arrow = '↓';
        break;
        default : order = 'credit_value desc';
    }
    $('#credit_grade').html('信用度' + arrow);
    $('#credit_grade').click(function(){query('order', order);});
    $('#region').change(function(){query('region_id', $(this).val());});
}
);
function query(name, value){
    $("input[name='"+name+"']").val(value);
    $('#search').submit();
}

//]]>
</script>

<div class="keyword_foot">
    <div class="keyword_foot_warp">
        <form id="search" method="GET" action="index.php">
            <input type="hidden" name="order" value="<?php echo htmlspecialchars($_GET['order']); ?>"/>
            <input type="hidden" name="app" value="search"/>
            <input type="hidden" name="act" value="store"/>
            <input type="hidden" name="cate_id" value="<?php echo htmlspecialchars($_GET['cate_id']); ?>"/>
            <input type="hidden" name="region_id" value="<?php echo htmlspecialchars($_GET['region_id']); ?>"/>
            <span>店铺名称: </span><input class="text" type="text" name="keyword" value="<?php echo htmlspecialchars($this->_var['query']['keyword']); ?>" />
            <span>店主: </span><input class="text" type="text" name="user_name" value="<?php echo htmlspecialchars($this->_var['query']['user_name']); ?>" />
            <span>所在地: </span><input class="text" type="text" name="region_name" value="<?php echo htmlspecialchars($this->_var['query']['region_name']); ?>" />
            <input class="btn" type="submit" name="Submit" value="搜索" />
            <input class="btn" type="reset" name="reset" value="Reset" />
            <div class="specific_bottom"></div>
        </form>
    </div>

    <div class="keyword_foot_bottom"></div>
</div>

<div class="content">
    <div class="left">
        <div class="module_sidebar">
            <h2><b>Categorie di negozio</b></h2>
            <div class="wrap">
                <div class="wrap_child">
                    <?php $_from = $this->_var['scategorys']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'scategory');if (count($_from)):
    foreach ($_from AS $this->_var['scategory']):
?>
                    <dl class="sidebar_list">
                        <dt class="bg_color1"><a href="index.php?app=search&act=store&cate_id=<?php echo $this->_var['scategory']['id']; ?>"><?php echo htmlspecialchars($this->_var['scategory']['value']); ?></a></dt>
                        <?php $_from = $this->_var['scategory']['children']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'child');if (count($_from)):
    foreach ($_from AS $this->_var['child']):
?>
                        <dd><a href="index.php?app=search&act=store&cate_id=<?php echo $this->_var['child']['id']; ?>"><?php echo htmlspecialchars($this->_var['child']['value']); ?></a></dd>
                        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                    </dl>
                    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="right">
        <div class="shop_list">
            <div class="ornament1"></div>
            <div class="ornament2"></div>
            <table>
                <tr>
                    <th class="align1">店铺</th>
                    <th>商品数量</th>
                    <th class="width2">店主</th>
                    <th><a id="credit_grade" href="###">信用度</a></th>
                    <th>
                        <select id="region">
                            <option value="">所在地</option>
                            <?php echo $this->html_options(array('options'=>$this->_var['regions'],'selected'=>$this->_var['query']['region_id'])); ?>
                        </select>
                    </th>
                </tr>
                <?php $_from = $this->_var['stores']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'store');if (count($_from)):
    foreach ($_from AS $this->_var['store']):
?>
                <tr>
                    <td class="width1">
                        <p class="ware_pic"><a href="index.php?app=store&id=<?php echo $this->_var['store']['store_id']; ?>"><img src="<?php echo $this->_var['store']['store_logo']; ?>" width="65" height="65" /></a></p>
                        <h3>
                            <a href="index.php?app=store&id=<?php echo $this->_var['store']['store_id']; ?>"><?php echo htmlspecialchars($this->_var['store']['store_name']); ?></a>
                        </h3>
                    </td>
                    <td class="align2"><?php echo $this->_var['store']['goods_count']; ?></td>
                    <td class="align2"><p class="name"><?php echo htmlspecialchars($this->_var['store']['user_name']); ?></p><a class="email" href="<?php echo $this->_var['site_url']; ?>/index.php?app=message&amp;act=send&amp;to_id=<?php echo $this->_var['store']['user_id']; ?>">Mandi messagi</a></td>
                    <td class="align2">
                        <?php if ($this->_var['store']['credit_value'] >= 0): ?>
                        <img src="<?php echo $this->_var['store']['credit_image']; ?>" />
                        <?php else: ?>
                        <?php echo $this->_var['store']['credit_value']; ?>
                        <?php endif; ?>
                    </td>
                    <td class="align2"><?php echo htmlspecialchars($this->_var['store']['region_name']); ?></td>
                </tr>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            </table>
        </div>

        <div class="shop_list_page">
            <?php echo $this->fetch('page.bottom.html'); ?>
        </div>
    </div>
</div>

<?php echo $this->fetch('footer.html'); ?>