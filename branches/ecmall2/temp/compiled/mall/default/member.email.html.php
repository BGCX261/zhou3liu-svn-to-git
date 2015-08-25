<?php echo $this->fetch('member.header.html'); ?>
<script type="text/javascript">
$(function(){
    $('#email_form').validate({
        errorPlacement: function(error, element){
            $(element).next('.field_notice').hide();
            $(element).after(error);
        },
        success       : function(label){
            label.addClass('validate_right').text('OK!');
        },
        rules : {
            orig_password : {
                required : true
            },
           email : {
                required   : true,
                email      : true
            }
        },
        messages : {
            orig_password : {
                required : '原始密码不能为空'
            },
            email : {
                required   : '您必须提供您的电子邮件',
                email    : '这不是一个有效的电子邮件地址'
            }
        }
    });
});
</script>
<style>
.borline td {padding:10px 0px;}
.ware_list th {text-align:left;}
.bgwhite {background: #FFFFFF;}
</style>
<div class="content">
    <?php echo $this->fetch('member.menu.html'); ?>
    <div id="right">
        <?php echo $this->fetch('member.submenu.html'); ?>
        <div class="eject_con bgwhite">
            <div class="add">
                <form method="post" id="email_form">
                    <ul>
                        <li><h3>原始密码:</h3>
                        <p>
                            <input style="width:149px" type="password" name="orig_password" />
                            <label class="field_notice">原始密码</label>                 </p>
                        </li>
                        <li><h3>电子信箱:</h3>
                        <p>
                            <input type="text" name="email" />
                            <label class="field_notice">您必须提供您的电子邮件</label>                 </p>
                        </li>
                    </ul>
                    <div class="submit">
                                    <input class="btn" type="submit" value="Inoltri" />
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="clear"></div>
</div>
<?php echo $this->fetch('footer.html'); ?>
