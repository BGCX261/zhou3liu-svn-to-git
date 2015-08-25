<script type="text/javascript" src="<?php echo $this->lib_base . "/" . 'goodsinfo.js'; ?>" charset="utf-8"></script>
<script type="text/javascript">
//<!CDATA[
/* buy */
function buy()
{
    if (goodsspec.getSpec() == null)
    {
        alert(lang.select_specs);
        return;
    }
    var spec_id = goodsspec.getSpec().id;

    var quantity = $("#quantity").val();
    if (quantity == '')
    {
        alert(lang.input_quantity);
        return;
    }
    if (parseInt(quantity) < 1)
    {
        alert(lang.invalid_quantity);
        return;
    }
    add_to_cart(spec_id, quantity);
}

/* add cart */
function add_to_cart(spec_id, quantity)
{
    var url = SITE_URL + '/index.php?app=cart&act=add';
    $.getJSON(url, {'spec_id':spec_id, 'quantity':quantity}, function(data){
        if (data.done)
        {
            $('.bold_num').text(data.retval.cart.kinds);
            $('.bold_mly').html(price_format(data.retval.cart.amount));
            $('.ware_cen').slideDown('slow');
            setTimeout(slideUp_fn, 5000);
        }
        else
        {
            alert(data.msg);
        }
    });
}

var specs = new Array();
<?php $_from = $this->_var['goods']['_specs']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'spec');if (count($_from)):
    foreach ($_from AS $this->_var['spec']):
?>
specs.push(new spec(<?php echo $this->_var['spec']['spec_id']; ?>, '<?php echo $this->_var['spec']['spec_1']; ?>', '<?php echo $this->_var['spec']['spec_2']; ?>', <?php echo $this->_var['spec']['price']; ?>, <?php echo $this->_var['spec']['stock']; ?>));
<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
var specQty = <?php echo $this->_var['goods']['spec_qty']; ?>;
var defSpec = <?php echo $this->_var['goods']['default_spec']; ?>;
var goodsspec = new goodsspec(specs, specQty, defSpec);
//]]>
</script>

<h2 class="ware_title"><?php echo htmlspecialchars($this->_var['goods']['goods_name']); ?></h2>

<div class="ware_info">
    <div class="ware_pic">
        <div class="big_pic">
            <a href="javascript:;"><span class="jqzoom"><img src="<?php echo ($this->_var['goods']['_images']['0']['thumbnail'] == '') ? $this->_var['default_image'] : $this->_var['goods']['_images']['0']['thumbnail']; ?>" width="300" height="300" jqimg="<?php echo $this->_var['goods']['_images']['0']['image_url']; ?>" /></span></a>
        </div>
        
        <div class="bottom_btn">
            <!--<a class="collect" href="javascript:collect_goods(<?php echo $this->_var['goods']['goods_id']; ?>);" title="Aggiungi ai preferiti"></a>-->
            <div class="left_btn"></div>
            <div class="right_btn"></div>
            <div class="ware_box">
                <ul>
                    <?php $_from = $this->_var['goods']['_images']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods_image');$this->_foreach['fe_goods_image'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_goods_image']['total'] > 0):
    foreach ($_from AS $this->_var['goods_image']):
        $this->_foreach['fe_goods_image']['iteration']++;
?>
                    <li <?php if (($this->_foreach['fe_goods_image']['iteration'] <= 1)): ?>class="ware_pic_hover"<?php endif; ?> bigimg="<?php echo $this->_var['goods_image']['image_url']; ?>"><img src="<?php echo $this->_var['goods_image']['thumbnail']; ?>" width="55" height="55" /></li>
                    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                </ul>
            </div>
        </div>
    </div>
    
    <div class="ware_text">
        <div class="rate">
            <span class="letter1">Prezzo: </span><span class="fontColor3" ectype="goods_price"><?php echo price_format($this->_var['goods']['_specs']['0']['price']); ?></span><br />
            <span class="letter1">Marca: </span><?php echo $this->_var['goods']['brand']; ?><br />
            Vendita situazione: <?php echo $this->_var['sales_info']; ?><?php echo $this->_var['comments']; ?><br />
            regione: <?php echo $this->_var['store']['region_name']; ?>
        </div>
        
        <div class="handle">
            <?php if ($this->_var['goods']['spec_qty'] > 0): ?>
            <ul>
                <li class="handle_title"><?php echo $this->_var['goods']['spec_name_1']; ?>: </li>
            </ul>
            <?php endif; ?>
            <?php if ($this->_var['goods']['spec_qty'] > 1): ?>
            <ul>
                <li class="handle_title"><?php echo $this->_var['goods']['spec_name_2']; ?>: </li>
            </ul>
            <?php endif; ?>
            <ul>
                <li class="handle_title">Quantita: </li>
                <li>
                    <input type="text" class="text width1" name="" id="quantity" value="1" />
                    pezzo（Quantità<span class="stock" ectype="goods_stock"><?php echo $this->_var['goods']['_specs']['0']['stock']; ?></span>pezzo）
                </li>
            </ul>
            <?php if ($this->_var['goods']['spec_qty'] > 0): ?>
            <ul>
                <li class="handle_title">Hai scelto: </li>
                <li class="aggregate" ectype="current_spec"></li>
            </ul>
            <?php endif; ?>
        </div>
        
        <ul class="ware_btn">
            <div class="ware_cen" style="display:none">
                <div class="ware_center">
                    <h1>
                        <span class="dialog_title">L' oggetto è stato correttamente aggiunto al carrello</span>
                        <span class="close_link" title="Chiuso" onmouseover="this.className = 'close_hover'" onmouseout="this.className = 'close_link'" onclick="slideUp_fn();"></span>
                    </h1>
                    <div class="ware_cen_btn">
                        <p class="ware_text_p">Ci sono <span class="bold_num">3</span> tipi di prodotti nel carrello, costo <span class="bold_mly">658.00</span> totale</p>
                        <p class="ware_text_btn">
                            <input type="submit" class="btn1" name="" value="Vedi carrello" onclick="location.href='index.php?app=cart'" />
                            <input type="submit" class="btn2" name="" value="Continua a scegliere oggetti" onclick="$('.ware_cen').css({'display':'none'});" />
                        </p>
                    </div>
                </div>
                <div class="ware_cen_bottom"></div>
            </div>

            <!--<li class="btn_c1" title="Compralo subito"><a href="#"></a></li>-->
            <li class="btn_c2" title="Aggiungi al carrello"><a href="javascript:buy();"></a></li>
            <li class="btn_c3" title="Aggiungi ai preferiti"><a href="javascript:collect_goods(<?php echo $this->_var['goods']['goods_id']; ?>);"></a></li>
        </ul>
    </div>
    
    <div class="clear"></div>
</div>