<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7 charset=<?php echo $this->_var['charset']; ?>" />
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $this->_var['charset']; ?>" />
<title><?php echo $this->_var['page_title']; ?></title>
<meta name="description" content="<?php echo $this->_var['page_description']; ?>" />
<meta name="keywords" content="<?php echo $this->_var['page_keywords']; ?>" />
<meta name="author" content="ecmall.shopex.cn" />
<meta name="copyright" content="ShopEx Inc. All Rights Reserved" />

<link href="<?php echo $this->res_base . "/" . 'css/ecmall.css'; ?>" rel="stylesheet" type="text/css" />
<script type="text/javascript">
//<!CDATA[
var SITE_URL = "<?php echo $this->_var['site_url']; ?>";
var PRICE_FORMAT = '<?php echo $this->_var['price_format']; ?>';
//]]>
</script>
<script type="text/javascript" src="index.php?act=jslang"></script>
<script type="text/javascript" src="<?php echo $this->lib_base . "/" . 'jquery.js'; ?>" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo $this->lib_base . "/" . 'ecmall.js'; ?>" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo $this->res_base . "/" . 'js/nav.js'; ?>" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo $this->res_base . "/" . 'js/select.js'; ?>" charset="utf-8"></script>

<?php echo $this->_var['_head_tags']; ?>
<!--<editmode></editmode>-->
</head>

<body>

<div id="head">
    <h1 title="<?php echo $this->_var['site_title']; ?>"><a href="index.php"><img alt="<?php echo $this->_var['site_title']; ?>" src="<?php echo $this->_var['site_logo']; ?>" /></a></h1>
    <div class="menu">
        <p class="link1">
            Benvenuto,<?php echo $this->_var['visitor']['user_name']; ?>
            <?php if (! $this->_var['visitor']['user_id']): ?>
            [<a href="index.php?app=member&amp;act=login&amp;ret_url=<?php echo $this->_var['ret_url']; ?>">Accedi</a>]
            [<a href="index.php?app=member&amp;act=register&amp;ret_url=<?php echo $this->_var['ret_url']; ?>">Registrati</a>]
            <?php else: ?>
            [<a href="index.php?app=member&amp;act=logout">Esci</a>]
            <?php endif; ?>
        </p>
        <p class="link2">
            <a href="index.php?app=member" class="ico">Centro di utenti</a>
            <span>|</span>
            <a href="index.php?app=message&act=inbox">Messagi nel sito<?php if ($this->_var['new_message']['total']): ?>(<?php echo $this->_var['new_message']['total']; ?>)<?php endif; ?></a>
            <span>|</span>
            <a href="index.php?app=article&amp;code=<?php echo $this->_var['acc_help']; ?>">Aiuto</a>
            <?php $_from = $this->_var['navs']['header']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'nav');if (count($_from)):
    foreach ($_from AS $this->_var['nav']):
?>
            <span>|</span>
            <a href="<?php echo $this->_var['nav']['link']; ?>"<?php if ($this->_var['nav']['open_new']): ?> target="_blank"<?php endif; ?>><?php echo htmlspecialchars($this->_var['nav']['title']); ?></a>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
        </p>
    </div>
</div>

<ul id="nav">
    <div class="nav1"></div>
    <div class="nav2"></div>
    <li><a class="<?php if ($this->_var['index']): ?>link<?php else: ?>hover<?php endif; ?>" href="index.php"><span>Home</span></a></li>
    <?php $_from = $this->_var['navs']['middle']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'nav');if (count($_from)):
    foreach ($_from AS $this->_var['nav']):
?>
    <li><a class="<?php if (! $this->_var['index'] && $this->_var['nav']['link'] == $this->_var['current_url']): ?>link<?php else: ?>hover<?php endif; ?>" href="<?php echo $this->_var['nav']['link']; ?>"<?php if ($this->_var['nav']['open_new']): ?> target="_blank"<?php endif; ?>><span><?php echo htmlspecialchars($this->_var['nav']['title']); ?></span></a></li>
    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
</ul>

<div class="search">
    <div class="search1"></div>
    <div class="search2"></div>
    <div class="wrap">
        <form method="GET">
            <div class="border">
                <div class="select_js">
                    <p>Ricerca oggetti</p>
                    <div class="ico"></div>
                    <ul>
                        <li ectype="index">Ricerca oggetti</li>
                        <li ectype="store">Ricerca negozio</li>
                    </ul>
                    <input type="hidden" name="act" value="index" />
                </div>
                <input type="text" name="keyword" class="text2" />
            </div>
            <input type="hidden" name="app" value="search" />
            <input type="submit" name="Submit" value="Cerca" class="btn" />
        </form>
        <p><a href="index.php?app=category">Categorie di oggetti</a><br /><a href="index.php?app=category&amp;act=store">Categorie di negozio</a></p>
    </div>
    <div class="nav">
        <div class="nav1"></div>
        <div class="nav2"></div>
        <a href="index.php?app=cart" class="buy">Carrello <strong id="cart_goods_kinds"><?php echo $this->_var['cart_goods_kinds']; ?></strong> tipi di oggetto</a>
        <a href="index.php?app=my_favorite" class="buyline">Preferiti</a>
        <a href="index.php?app=buyer_order" class="buyline">I miei ordini</a>
    </div>
</div>