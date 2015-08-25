<?php echo $this->fetch('header.html'); ?>

<script type="text/javascript">
$(function(){
    $('#login_form').validate({
        errorPlacement: function(error, element){
            $(element).parent('td').append(error); 
        },
        success       : function(label){
            label.addClass('validate_right').text('OK!');
        },
        onkeyup : false,
        rules : {
            user_name : {
                required : true
            },
            password : {
                required : true
            },
            captcha : {
                required : true,
                remote   : {
                    url : 'index.php?app=captcha&act=check_captcha',
                    type: 'get',
                    data:{
                        captcha : function(){
                            return $('#captcha1').val();
                        }
                    }
                }
            }
        },
        messages : {
            user_name : {
                required : '您必须提供一个用户名'
            },
            password  : {
                required : '您必须提供一个密码'
            },
            captcha : {
                required : '请输入右侧图片中的文字',
                remote   : '验证码错误'
            }
        }
    });
});
</script>

<div class="content">
    <div class="module_common">
        <h2><b class="login" title="LOGINUtente login"></b></h2>
        <div class="wrap">
            <div class="wrap_child">
                <div class="login_con">
                    <div class="login_left">
                        <form method="post" id="login_form">
                        <table>
                            <tr>
                                <td>ID utente: </td>
                                <td><input type="text" name="user_name" class="text width5" /></td>
                            </tr>
                            <tr>
                                <td>Password: </td>
                                <td><input type="password" name="password" class="text width5" /></td>
                            </tr>
                            <?php if ($this->_var['captcha']): ?>
                            <tr>
                                <td>Codice di verifica:</td>
                                <td>
                                    <input type="text" name="captcha" class="text" id="captcha1" />
                                    <span><a href="javascript:change_captcha($('#captcha'));" class="renewedly"><img id="captcha" src="index.php?app=captcha&amp;<?php echo $this->_var['random_number']; ?>" /></a></span>
                                </td>
                            </tr>
                            <?php endif; ?>
                            <tr class="distance">
                                <td></td>
                                <td>
                                  <input type="submit" name="Submit" value="" class="enter" />                                  
                                  <a href="index.php?app=find_password" class="clew">Hai dimenticato la password？</a>
                                </td>
                            </tr>
                        </table>
                        <input type="hidden" name="ret_url" value="<?php echo $this->_var['ret_url']; ?>" />
                        </form>
                    </div>

                    <div class="login_right">
                        <h4>Attenzione:<br />Se non sei un membro, si prega di registrarsi</h4>
                        <p>Dopo registrazione, puoi</p>
                        <ol>
                            <li><strong>1.</strong> Salvare i dati personali.</li>
                            <li><strong>2.</strong> Raccoltare i prodotti che ti piace.</li>
                           <!-- <li><strong>3.</strong> Godere il sistema di punti dei membri</li>-->
                            <li><strong>3.</strong> Iscriverti alle nostre informazioni sul prodotto</li>
                        </ol>
                        <a href="index.php?app=member&amp;act=register&amp;ret_url=<?php echo $this->_var['ret_url']; ?>" class="login_btn" title="Registrati"></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php echo $this->fetch('footer.html'); ?>