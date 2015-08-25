<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<base href="<?php echo $this->_var['site_url']; ?>/" />

<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7 charset=<?php echo $this->_var['charset']; ?>" />
<meta http-equiv="Content-Type" content="text/html;charset=<?php echo $this->_var['charset']; ?>" />
<title><?php echo $this->_var['page_title']; ?></title>
<meta name="author" content="ecmall.shopex.cn" />
<meta name="copyright" content="ShopEx Inc. All Rights Reserved" />
<link href="<?php echo $this->res_base . "/" . 'shop.css'; ?>" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="index.php?act=jslang"></script>
<script type="text/javascript" src="<?php echo $this->lib_base . "/" . 'jquery.js'; ?>" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo $this->lib_base . "/" . 'ecmall.js'; ?>" charset="utf-8"></script>

<script type="text/javascript">
//<!CDATA[
var SITE_URL = "<?php echo $this->_var['site_url']; ?>";
var PRICE_FORMAT = '<?php echo $this->_var['price_format']; ?>';
//]]>
</script>
<?php echo $this->_var['_head_tags']; ?>
</head>

<body>

<div id="head">
    <h1 title="<?php echo $this->_var['site_title']; ?>"><a href="index.php"><img src="<?php echo $this->_var['site_logo']; ?>" alt="ECMall" /></a></h1>

    <div id="subnav">
        <p>
        您好,<?php echo $this->_var['visitor']['user_name']; ?>
        <?php if (! $this->_var['visitor']['user_id']): ?>
        [<a href="index.php?app=member&amp;act=login&amp;ret_url=<?php echo $this->_var['ret_url']; ?>">登录</a>]
        [<a href="index.php?app=member&amp;act=register&amp;ret_url=<?php echo $this->_var['ret_url']; ?>">注册</a>]
        <?php else: ?>
        [<a href="index.php?act=logout">退出</a>]
        <?php endif; ?>
        </p>
        <p>
        <a class="shopping" href="index.php?app=cart">购物车</a> <span>|</span>
        <a class="favorite" href="index.php?app=my_favorite">收藏夹</a> <span>|</span>
        <a class="note" href="index.php?app=message&amp;act=inbox">站内消息<?php if ($this->_var['new_message']['total']): ?>(<?php echo $this->_var['new_message']['total']; ?>)<?php endif; ?></a> <span>|</span>
        <a class="help" href="index.php?app=article&amp;code=help">帮助中心</a>
        <?php $_from = $this->_var['navs']['header']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'nav');if (count($_from)):
    foreach ($_from AS $this->_var['nav']):
?>
        <span>|</span> <a class="user_defined" href="<?php echo $this->_var['nav']['link']; ?>"<?php if ($this->_var['nav']['open_new']): ?> target="_blank"<?php endif; ?>><?php echo htmlspecialchars($this->_var['nav']['title']); ?></a>
        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
        </p>
    </div>

    <div id="topbtn">
        <div class="topbtn1"></div>
        <div class="topbtn2"></div>
        <a href="index.php?app=member" class="user">用户中心</a> <span>|</span>
        <a href="index.php?app=category">我要买</a> <span>|</span>
        <a href="index.php?app=my_goods&amp;act=add">我要卖</a>
    </div>
    
    <div id="path">
        当前位置:
        <?php $_from = $this->_var['_curlocal']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'lnk');if (count($_from)):
    foreach ($_from AS $this->_var['lnk']):
?>
        <?php if ($this->_var['lnk']['url']): ?>
        <a href="<?php echo $this->_var['lnk']['url']; ?>"><?php echo $this->_var['lnk']['text']; ?></a> <span>&#8250;</span>
        <?php else: ?>
        <?php echo $this->_var['lnk']['text']; ?>
        <?php endif; ?>
        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
    </div>

    <div id="search">
        <form id="" name="" method="get" action="index.php">
            <div class="input">
                <div class="input1"></div>
                <div class="input2"></div>
                <select name="act" class="select1">
                <option value="index">搜索商品</option>
                <option value="store">搜索店铺</option>
                </select>
                <input type="hidden" name="app" value="search" />
                <input type="text" class="search334" name="keyword" />
            </div>
            <input class="search_btn" type="submit" value="" />
        </form>
    </div>
</div>