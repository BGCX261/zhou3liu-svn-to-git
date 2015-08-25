<div class="table_common">
    <table>
        <tr class="bg2">
            <th>Acquirente</th>
            <th>Prezzo</th>
            <th>Quantita</th>
            <th>Ora di comprare</th>
            <th>Valutazione</th>
        </tr>
        <?php $_from = $this->_var['sales_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'sales');if (count($_from)):
    foreach ($_from AS $this->_var['sales']):
?>
        <tr>
            <td><?php if ($this->_var['sales']['anonymous']): ?>***<?php else: ?><?php echo $this->_var['sales']['buyer_name']; ?><?php endif; ?></td>
            <td><?php echo price_format($this->_var['sales']['price']); ?></td>
            <td><?php echo $this->_var['sales']['quantity']; ?> <span class="fontColor5"><?php if ($this->_var['sales']['specification']): ?>（<?php echo $this->_var['sales']['specification']; ?>）<?php endif; ?></span></td>
            <td><?php echo local_date("Y-m-d",$this->_var['sales']['add_time']); ?></td>
            <td><?php if ($this->_var['sales']['evaluation'] > 0): ?><img src="<?php echo $this->res_base . "/" . 'images/bit.gif'; ?>" /><?php endif; ?>
                <?php if ($this->_var['sales']['evaluation'] > 1): ?><img src="<?php echo $this->res_base . "/" . 'images/bit.gif'; ?>" /><?php endif; ?>
                <?php if ($this->_var['sales']['evaluation'] > 2): ?><img src="<?php echo $this->res_base . "/" . 'images/bit.gif'; ?>" /><?php endif; ?>
                <?php if ($this->_var['sales']['evaluation'] < 3): ?><img src="<?php echo $this->res_base . "/" . 'images/bit2.gif'; ?>" /><?php endif; ?>
                <?php if ($this->_var['sales']['evaluation'] < 2): ?><img src="<?php echo $this->res_base . "/" . 'images/bit2.gif'; ?>" /><?php endif; ?>
                <?php if ($this->_var['sales']['evaluation'] < 1): ?><img src="<?php echo $this->res_base . "/" . 'images/bit2.gif'; ?>" /><?php endif; ?> </td>
        </tr>
        <?php endforeach; else: ?>
        <tr>
            <td colspan="6"><span class="light">No oggetti trovati</span></td>
        </tr>
        <?php endif; unset($_from); ?><?php $this->pop_vars();; ?>
    </table>
</div>