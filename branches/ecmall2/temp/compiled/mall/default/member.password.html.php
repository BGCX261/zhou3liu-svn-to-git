<?php echo $this->fetch('member.header.html'); ?>
<style type="text/css">
.bgwhite {background: #FFFFFF;}
</style>
<script type="text/javascript">
$(function(){
    $('#password_form').validate({
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
            new_password : {
                required   : true,
                minlength  : 6,
                maxlength  : 20
            },
            confirm_password : {
                required   : true,
                equalTo    : '#new_password'
            }
        },
        messages : {
            orig_password : {
                required : '原始密码不能为空'
            },
            new_password  : {
                required   : '密码不能为空',
                minlength  : '密码长度应在6-20个字符之间'
            },
            confirm_password : {
                required   : '密码不能为空',
                equalTo    : '两次输入的密码不相符'
            }
        }
    });
});
</script>
<style>
.borline td {padding:10px 0px;}
.ware_list th {text-align:left;}
</style>
<div class="content">
    <?php echo $this->fetch('member.menu.html'); ?>
    <div id="right">
        <?php echo $this->fetch('member.submenu.html'); ?>
        <div class="eject_con bgwhite">
            <div class="add">
                <form method="post" id="password_form">
                        <ul>
                            <li><h3>原始密码:</h3>
                                <p>
                                    <input type="password" name="orig_password" />
                                    <label class="field_notice">原始密码</label></p>
                            </li>
                            <li>
                                <h3>新密码:</h3>
                                <p>
                                    <input type="password" name="new_password" id="new_password"/>
                                    <label class="field_notice">新密码</label></p>
                            </li>
                            <li>
                                <h3>确认密码:</h3>
                                <p>
                                    <input type="password" name="confirm_password" />
                                    <label class="field_notice">确认密码</label></p>
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
