<div id="nav">
    <div class="banner"><a href="index.php?app=store&amp;id=<?php echo $this->_var['store']['store_id']; ?>">
        <?php if ($this->_var['store']['store_banner']): ?>
        <img src="<?php echo $this->_var['store']['store_banner']; ?>" width="1000" height="150" />
        <?php else: ?>
        <img src="<?php echo $this->res_base . "/" . 'images/banner.jpg'; ?>"  />
        <?php endif; ?>
    </a></div>

    <ul>
        <li><a class="<?php if ($_GET['app'] == 'store' && $_GET['act'] == 'index'): ?>active<?php else: ?>normal<?php endif; ?>" href="index.php?app=store&amp;id=<?php echo $this->_var['store']['store_id']; ?>"><span>店铺首页</span></a></li>
        <?php $_from = $this->_var['store']['store_navs']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'store_nav');if (count($_from)):
    foreach ($_from AS $this->_var['store_nav']):
?>
        <li><a class="<?php if ($_GET['app'] == 'store' && $_GET['act'] == 'article' && $_GET['id'] == $this->_var['store_nav']['article_id']): ?>active<?php else: ?>normal<?php endif; ?>" href="index.php?app=store&amp;act=article&amp;id=<?php echo $this->_var['store_nav']['article_id']; ?>"><span><?php echo $this->_var['store_nav']['title']; ?></span></a></li>
        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
        <li><a class="<?php if ($_GET['app'] == 'store' && $_GET['act'] == 'credit'): ?>active<?php else: ?>normal<?php endif; ?>" href="index.php?app=store&amp;act=credit&amp;id=<?php echo $this->_var['store']['store_id']; ?>"><span>信用评价</span></a></li>
        <a class="collection" href="javascript:collect_store(<?php echo $this->_var['store']['store_id']; ?>)">收藏该店铺</a>
    </ul>

    <div class="nav_bg"></div>
</div>