<?php echo $this->fetch('member.header.html'); ?>
<div class="content">
    <div class="totline"></div><div class="botline"></div>
    <?php echo $this->fetch('member.menu.html'); ?>
    <div id="right">
        <?php echo $this->fetch('member.submenu.html'); ?>
        <div class="wrap">
            <div class="eject_btn" title="发送短消息"><b class="ico2" onclick="go('index.php?app=message&act=send');">发送短消息</b></div>
            <div class="public table">
                <table class="table_head_line">
                    <?php if ($this->_var['messages']): ?>
                    <tr class="line_bold">
                        <th class="width1"><input type="checkbox" id="all" class="checkall"/></th>
                        <th class="align1" colspan="4">
                            <label for="all"><span class="all">全选</span></label>
                            <a href="javascript:;" class="delete" uri="index.php?app=message&act=drop" name="msg_id" presubmit="confirm('您确定要删除它吗？')" ectype="batchbutton">删除</a>
                        </th>
                    </tr>

                    <tr class="line tr_color">
                        <th></th>
                        <th class="align1"><?php echo $this->_var['lang_user_name']; ?></th>
                        <th>标题</th>
                        <th>最后更新</th>
                        <th class="width4">操作</th>
                    </tr>
                    <?php endif; ?>
                    <?php $_from = $this->_var['messages']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'message');$this->_foreach['v'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['v']['total'] > 0):
    foreach ($_from AS $this->_var['message']):
        $this->_foreach['v']['iteration']++;
?>
                    <tr <?php if (($this->_foreach['v']['iteration'] == $this->_foreach['v']['total'])): ?>class="line_bold"<?php else: ?>class="line"<?php endif; ?>>
                        <td class="align2"><input type="checkbox" class="checkitem" value="<?php echo $this->_var['message']['msg_id']; ?>"/></td>
                        <td class="link1"><?php echo $this->_var['message']['user_name']; ?></td>
                        <td <?php if ($this->_var['message']['new'] == 1): ?>class="link2 font_bold"<?php else: ?>class="link2"<?php endif; ?>><a href="index.php?app=message&amp;act=view&amp;msg_id=<?php echo $this->_var['message']['msg_id']; ?>"><?php echo htmlspecialchars($this->_var['message']['title']); ?></a></td>
                        <td class="align2 color1"><?php echo local_date("Y-m-d H:i",$this->_var['message']['last_update']); ?></td>
                        <td>
                            <a href="javascript:drop_confirm('您确定要删除它吗？', 'index.php?app=message&amp;act=drop&msg_id=<?php echo $this->_var['message']['msg_id']; ?>');" class="delete">删除</a>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr>
                        <td colspan="5" class="member_no_records padding6"><?php echo $this->_var['lang'][$_GET['act']]; ?>没有短信息</td>
                    </tr>

                    <?php endif; unset($_from); ?><?php $this->pop_vars();; ?>
                    <?php if ($this->_var['messages']): ?>
                    <tr>
                        <th class="width1"><input id="all2" type="checkbox" class="checkall" /></th>
                        <th class="align1"><label for="all2"><span class="all">全选</span></label><a href="###" class="delete" uri="index.php?app=message&act=drop" name="msg_id" presubmit="confirm('您确定要删除它吗？')" ectype="batchbutton">删除</a></th>
                        <td colspan="3" class="page word_spacing5">
                           <p class="position2">
                                <?php echo $this->fetch('member.page.bottom.html'); ?>
                            </p>
                         </td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
            <div class="wrap_bottom"></div>
        </div>
        <div class="clear"></div>
        <div class="adorn_right1"></div>
        <div class="adorn_right2"></div>
        <div class="adorn_right3"></div>
        <div class="adorn_right4"></div>
    </div>
    <div class="clear"></div>
</div>
<div class="clear"></div>
<iframe id='iframe_post' name="iframe_post" frameborder="0" width="0" height="0">
</iframe>
<?php echo $this->fetch('footer.html'); ?>
