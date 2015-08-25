<script type="text/javascript" src="<?php echo $this->lib_base . "/" . 'jquery.plugins/jquery.validate.js'; ?>" charset="utf-8"></script>
<script type="text/javascript">
$(function(){
    $('#message').validate({
        errorPlacement: function(error, element){
            var _message_box = $(element).parent().find('.field_message');
            _message_box.find('.field_notice').hide();
            _message_box.parent().append(error);
        },
        rules : {
            content : {
                required : true,
                byteRange : [0,255,'<?php echo $this->_var['charset']; ?>']
            }
        },
        messages : {
            content : {
                required : 'Il contenuto non può essere vuoto',
                byteRange: 'Inserisci meno di 250 parole.'
            }
        }
    });
})
</script>

<div class="message">
    <?php $_from = $this->_var['qa_info']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'qainfo');if (count($_from)):
    foreach ($_from AS $this->_var['qainfo']):
?>
    <div class="<?php echo $this->cycle(array('values'=>'message_text2,message_text2 bg1')); ?>">
        <dl class="leave_word">
            <dt>Contenuto consultivo: </dt>
            <dd><?php echo nl2br($this->_var['qainfo']['question_content']); ?></dd>
            <p>
                <span class="name"><?php if ($this->_var['qainfo']['user_name']): ?><?php echo $this->_var['qainfo']['user_name']; ?><?php else: ?>Ospite<?php endif; ?> <?php echo local_date("Y-m-d H:i:s",$this->_var['qainfo']['time_post']); ?></span>
            </p>
        </dl>
        <?php if ($this->_var['qainfo']['reply_content']): ?>
        <dl class="revert_to">
            <dt>Risposte di venditore: </dt>
            <dd><?php echo nl2br($this->_var['qainfo']['reply_content']); ?></dd>
            <p>
                <span class="name"><?php echo local_date("Y-m-d H:i:s",$this->_var['qainfo']['reply_time']); ?></span>
            </p>
        </dl>
        <?php endif; ?>
    </div>
    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
</div>

<div class="fill_in">
    <form method="post" id="message" action="index.php?app=goods&amp;act=qa&amp;id=<?php echo $_GET['id']; ?>">
    <p><textarea name="content"></textarea><span class="field_message"><span class="field_notice"></span></span></p>
    <p>
        <span>Email: </span>
        <span><input type="text" class="text" name="email" value="<?php echo $this->_var['email']; ?>" /></span>
        <?php if ($this->_var['captcha']): ?>
        <span>Codice di verifica: </span>
        <span><input type="text" class="text" name="captcha" /></span>
        <span><a href="javascript:change_captcha($('#captcha'));"><img id="captcha" class="captcha" src="index.php?app=captcha&amp;<?php echo $this->_var['random_number']; ?>" /></a></span>
        <?php endif; ?>
        <?php if ($_SESSION['user_info']): ?>
        <span><label><input type="checkbox" name="hide_name" value="hide" /> Invia anonimamente</label></span>
        <?php endif; ?>
        <input type="submit" value="Invia domanda" />
        <input type="hidden" value="<?php echo $_GET['id']; ?>" name="goods_id" />
        <input type="hidden" value="ask" name="type" />
    </p>
    </form>
</div>