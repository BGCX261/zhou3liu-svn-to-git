<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $this->_var['charset']; ?>" />
<title> Informazione di sistema -- Powered by ECMall </title>
<link href="<?php echo $this->res_base . "/" . 'css/ecmall.css'; ?>" rel="stylesheet" type="text/css" />


<style>
#box {width: 420px; position: absolute; left: 50%; margin-left: -210px;}
.dos_prompt {padding: 30px 0 40px 150px;}
.dos_prompt h3 {margin-bottom: 20px;}
.dos_prompt h4 {font-size: 12px; font-weight: normal; margin-bottom: 10px;}
.dos_prompt p {line-height: 20px;}
.dos_prompt p a {color: #0066cb; font-family: "宋体";}
.dos_prompt p a:hover {color: red;}
.right_ico { background: url( <?php echo $this->res_base . "/" . 'images/member/ico_bg.gif'; ?> ) no-repeat 70px -189px; }
.error_ico { background: url( <?php echo $this->res_base . "/" . 'images/member/ico_bg.gif'; ?> ) no-repeat 70px 24px; }
</style>


<script type="text/javascript">
//<!CDATA[
window.onload = function () {
    var box = document.getElementById("box");
    box.style.top = "50%";
    box.style.marginTop = -(box.offsetHeight / 2) + "px";
};

<?php if ($this->_var['redirect']): ?>
window.setTimeout("<?php echo $this->_var['redirect']; ?>", 3000);
<?php endif; ?>
//]]>
</script>
</head>

<body>
<div id="box">
    <div class="module_common">
        <h2><b class="information" title="informationInformazione di sistema"></b></h2>
        <div class="wrap">
            <div class="wrap_child">
                <div class="dos_prompt <?php if ($this->_var['icon'] == "notice"): ?>right_ico<?php else: ?>error_ico<?php endif; ?>">
                    <h3><?php echo $this->_var['message']; ?></h3>
                    <?php if ($this->_var['err_file']): ?>
                    <h4>Error File: <b><?php echo $this->_var['err_file']; ?></b> at <b><?php echo $this->_var['err_line']; ?></b> line.</h4>
                    <?php endif; ?>
                    <?php if ($this->_var['redirect']): ?>
                    <h4>Se non fare una scelta, il sistema automaticamente vai</h4>
                    <?php endif; ?>
                    <p>
                        <?php $_from = $this->_var['links']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['item']):
?>
                        <a href="<?php echo $this->_var['item']['href']; ?>">>> <?php echo $this->_var['item']['text']; ?></a><br />
                        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
