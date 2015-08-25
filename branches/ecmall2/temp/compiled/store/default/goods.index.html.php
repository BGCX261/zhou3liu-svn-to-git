<?php echo $this->fetch('header.html'); ?>

<?php echo $this->fetch('top.html'); ?>

<div id="content">
    <div id="left">
        <?php echo $this->fetch('left.html'); ?>
    </div>
    
    <div id="right">
        <?php echo $this->fetch('goodsinfo.html'); ?>
        
        <ul class="user_menu">
            <div class="ornament1"></div>
            <div class="ornament2"></div>
            <li><a class="active" href="#"><span>Descrizione </span></a></li>
            <li><a class="normal" href="index.php?app=goods&amp;act=comments&amp;id=<?php echo $this->_var['goods']['goods_id']; ?>"><span>I commenti di feedback</span></a></li>
            <li><a class="normal" href="index.php?app=goods&amp;act=saleslog&amp;id=<?php echo $this->_var['goods']['goods_id']; ?>"><span>Vendite record</span></a></li>
            <li><a class="normal" href="index.php?app=goods&amp;act=qa&amp;id=<?php echo $this->_var['goods']['goods_id']; ?>"><span>Prodotto consultivo</span></a></li>
        </ul>
        
        <div class="option_box">
            <div class="default"><?php echo html_filter($this->_var['goods']['description']); ?></div>
        </div>
        
        <div class="module_currency">
            <h2 class="common_title veins1">
                <div class="ornament1"></div>
                <div class="ornament2"></div>
                <span class="ico1"><span class="ico2">Ultimi commenti</span></span>
            </h2>
            <div class="wrap">
                <div class="wrap_child">
                    <?php echo $this->fetch('comments.html'); ?>
                    <div class="clear"></div>
                </div>
            </div>
        </div>
        <div class="clear"></div>
        
        <div class="module_currency">
            <h2 class="common_title veins1">
                <div class="ornament1"></div>
                <div class="ornament2"></div>
                <span class="ico1"><span class="ico2">Ultimo vendite record</span></span>
            </h2>
            <div class="wrap">
                <div class="wrap_child">
                    <?php echo $this->fetch('saleslog.html'); ?>
                    <div class="clear"></div>
                </div>
            </div>
        </div>
        <div class="clear"></div>
        
        <div class="module_currency">
            <h2 class="common_title veins1">
                <div class="ornament1"></div>
                <div class="ornament2"></div>
                <span class="ico1"><span class="ico2">L'ultima domanda sui oggetti</span></span>
            </h2>
            <div class="wrap">
                <div class="wrap_child">
                    <?php echo $this->fetch('qa.html'); ?>
                    <div class="clear"></div>
                </div>
            </div>
        </div>
        <div class="clear"></div>
    </div>
    
    <div class="clear"></div>
</div>

<?php echo $this->fetch('footer.html'); ?>