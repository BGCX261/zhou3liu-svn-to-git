<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7 charset=<?php echo $this->_var['charset']; ?>" />
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $this->_var['charset']; ?>" />
<title><?php echo $this->_var['page_title']; ?></title>
<meta name="author" content="ecmall.shopex.cn" />
<meta name="copyright" content="ShopEx Inc. All Rights Reserved" />
<link href="<?php echo $this->res_base . "/" . 'css/user.css'; ?>" rel="stylesheet" type="text/css" />
<script type="text/javascript">
//<!CDATA[
var SITE_URL = "<?php echo $this->_var['site_url']; ?>";
var PRICE_FORMAT = '<?php echo $this->_var['price_format']; ?>';
//]]>
</script>
<script type="text/javascript" src="index.php?act=jslang"></script>
<script type="text/javascript" src="<?php echo $this->lib_base . "/" . 'jquery.js'; ?>" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo $this->lib_base . "/" . 'ecmall.js'; ?>" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo $this->lib_base . "/" . 'member.js'; ?>" charset="utf-8"></script>
<script type="text/javascript">
$(function(){
    $('#left h1 span,h2.title a.fold').click(function(){
        if($(this).hasClass('span_close')){
            $(this).removeClass('span_close');
            $(this).addClass('span_open');
            this.title = 'open';
            closeSubmenu($(this).parent());
        }
        else{
            $(this).removeClass('span_open');
            $(this).addClass('span_close');
            this.title = 'close';
            openSubmenu($(this).parent());
        }
    });
});
function closeSubmenu(h1){
    h1.next('ul').css('display', 'none');
}
function openSubmenu(h1){
    h1.next('ul').css('display', '');
}
</script>
<?php echo $this->_var['_head_tags']; ?>
</head>
<body>
<div id="header">
    <h1 title="<?php echo $this->_var['site_title']; ?>"><a href="index.php"><img src="<?php echo $this->_var['site_logo']; ?>" alt="<?php echo $this->_var['site_title']; ?>" /></a></h1>
    <div id="subnav">
        <p>
        Benvenuto,<?php echo $this->_var['visitor']['user_name']; ?>
        <?php if (! $this->_var['visitor']['user_id']): ?>
            [<a href="index.php?app=member&amp;act=login&amp;ret_url=<?php echo $this->_var['ret_url']; ?>">Accedi</a>]
            [<a href="index.php?app=member&amp;act=register&amp;ret_url=<?php echo $this->_var['ret_url']; ?>">Registrati</a>]
            <?php else: ?>
            [<a href="index.php?app=member&amp;act=logout">Esci</a>]
            <?php endif; ?>
        </p>
        <p>
        <a class="shopping" href="index.php?app=cart">Carrello</a> <span>|</span>
        <a class="favorite" href="index.php?app=my_favorite">Preferiti</a> <span>|</span>
        <a class="note" href="index.php?app=message&amp;act=inbox">Messagi nel sito<?php if ($this->_var['new_message']['total']): ?>(<?php echo $this->_var['new_message']['total']; ?>)<?php endif; ?></a> <span>|</span>
        <a class="help" href="index.php?app=article&amp;code=<?php echo $this->_var['acc_help']; ?>">Aiuto</a>
        </p>
    </div>
    <div id="topbtn">
        <div class="topbtn1"></div>
        <div class="topbtn2"></div>
        <a href="index.php?app=member" class="user">Centro di utenti</a> <span>|</span>
        <a href="index.php?app=category">Compra</a> <span>|</span>
        <a href="index.php?app=my_goods&amp;act=add">Vendi</a>
    </div>
    <div id="path">
        Posizione attuale:
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
    <div class="search">
        <form id="" name="" method="get" action="">
            <div class="input">
                <div class="input1"></div>
                <div class="input2"></div>
                <select name="act" class="select1">
                <option value="index">Ricerca oggetti</option>
                <option value="store">Ricerca negozio</option>
                </select>
                <input type="hidden" name="app" value="search" />
                <input type="text" class="text2" name="keyword" />
            </div>
            <input class="btn" type="submit" name="Submit" value=""/>
        </form>
    </div>
</div>
