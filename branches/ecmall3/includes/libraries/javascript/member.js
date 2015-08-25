$(function(){
    /* 全选 */
    $('.checkall').click(function(){
        $('.checkitem').attr('checked', this.checked);
        $('.checkall').attr('checked', this.checked);
    });

    /* 批量操作按钮 */
    $('a[ectype="batchbutton"]').click(function(){
        /* 是否有选择 */
        if($('.checkitem:checked').length == 0){    //没有选择
            return false;
        }
        /* 运行presubmit */
        if($(this).attr('presubmit')){
            if(!eval($(this).attr('presubmit'))){
                return false;
            }
        }
        /* 获取选中的项 */
        var items = '';
        $('.checkitem:checked').each(function(){
            items += this.value + ',';
        });
        items = items.substr(0, (items.length - 1));
        /* 将选中的项通过GET方式提交给指定的URI */
        var uri = $(this).attr('uri');
        window.location = uri + '&' + $(this).attr('name') + '=' + items;
    });

    /* 缩小大图片 */
    $('.makesmall').each(function(){
        makesmall(this, $(this).attr('max_width'), $(this).attr('max_height'));
    });

    $('.su_btn').click(function(){
        if($(this).hasClass('close')){
            $(this).parent().next('.su_block').css('display', '');
            $(this).removeClass('close');
        }
        else{
            $(this).addClass('close');
            $(this).parent().next('.su_block').css('display', 'none');
        }
    });

    $('*[ectype="dialog"]').click(function(){
        var id = $(this).attr('dialog_id');
        var title = $(this).attr('dialog_title') ? $(this).attr('dialog_title') : '';
        var url = $(this).attr('uri');
        var width = $(this).attr('dialog_width');
        ajax_form(id, title, url, width);
    });

    var url = window.location.search;
    var params  = url.substr(1).split('&');
    var app = '';
    //找出排序的列和排序的方式及app控制器
    var sort  = '';
    var order = '';
    for(var j=0; j < params.length; j++)
    {
        var param = params[j];
        var arr   = param.split('=');
        if(arr[0] == 'app')
        {
            app = arr[1];
        }
        if(arr[0] == 'sort')
        {
            sort = arr[1];
        }
        if(arr[0] == 'order')
        {
            order = arr[1];
        }
    }
    $('span[ectype="order_by"]').each(function(){
        if($(this).parent().attr('column') == sort)
        {
            if(order == 'asc')
            {
            $(this).removeClass();
            $(this).addClass("sort_asc");
            }
        else if (order == 'desc')
            {
            $(this).removeClass();
            $(this).addClass("sort_desc");
            }
        }
    });
    $('span[ectype="order_by"]').click(function(){
        var s_name = $(this).parent().attr('column');
        var found   = false;
        for(var i = 0;i < params.length;i++)
        {
            var param = params[i];
            var arr   = param.split('=');
            if('page' == arr[0])
            {
                params[i] = 'page=1';
            }
            else if('sort' == arr[0])
            {
                params[i] = 'sort'+'='+ s_name;
                found = true;
            }
            else if('order' == arr[0])
            {
                params[i] = 'order'+'='+(arr[1] =='asc' ? 'desc' : 'asc');
            }
        }
        if(!found)
        {
                params.push('sort'+'='+ s_name);
                params.push('order=asc');
        }
        if(location.pathname.indexOf('/admin/')>-1)
        {
                location.assign(SITE_URL + '/admin/index.php?' + params.join('&'));
                return;
        }
        location.assign(SITE_URL + '/index.php?' + params.join('&'));
    });

    // 初始化上传
    trigger_uploader();
});


function set_zindex(parents, index){
    $.each(parents,function(i,n){
        if($(n).css('position') == 'relative'){//alert('relative');
            //alert($(n).css('z-index'));
            $(n).css('z-index',index);
        }
    });
}


function js_success(dialog_id)
{
    DialogManager.close(dialog_id);
    var url = window.location.href;
    url =  url.indexOf('#') > 0 ? url.replace(/#/g, '') : url;
    window.location.replace(url);
}

function js_fail(str)
{
    $('#warning').html('<label class="error">' + str + '</label>');
    $('#warning').show();
}

function check_number(v)
{

    if(isNaN(v))
    {
        alert(lang.only_number);
        return false;
    }
    if(v.indexOf('-') > -1)
    {
        alert(lang.only_number);
        return false;
    }
    return true;
}
function check_required(v)
{
    if(v == '')
    {
        alert(lang.not_empty);
        return false;
    }
    return true;
}

function check_pint(v)
{
    var regu = /^[0-9]{1,}$/;
    if(!regu.test(v))
    {
        alert(lang.only_int);
        return false;
    }
    return true;
}

function check_max(v)
{
    var regu = /^[0-9]{1,}$/;
    if(!regu.test(v))
    {
        alert(lang.only_int);
        return false;
    }
    var max = 255;
    if(parseInt(v) > parseInt(max))
    {

        alert(lang.small+max);
        return false;
    }
    return true;
}

function order_action_result(action, order_id, rzt)
{
    if (rzt.done === false)
    {
        //操作失败
        alert(rzt.msg);

        return;
    }
    else
    {
        //操作成功
        //关闭窗口
        DialogManager.close(action);

        //更新视图
        for (k in rzt.retval)
        {
            switch (k)
            {
                case 'actions':
                    $('#order' + order_id + '_action').children().hide();
                    for (_j in rzt.retval[k])
                    {
                        $('#order' + order_id + '_action_' + rzt.retval[k][_j]).show();
                    }
                    break;
                default:
                    var _tmp = $('#order' + order_id + '_' + k);
                    _tmp.html(rzt.retval[k]);
                    break;
            }
        }
        $.get('index.php?app=sendmail');

        alert(rzt.msg);
    }
}

/* 把图片插入编辑器 */
function insert_editor(file_name, path)
{
    tinyMCE.execCommand('mceInsertContent', false,
        '<img src="'+ SITE_URL +'/' + path + '" alt="'+ file_name + '">');
}

function trigger_uploader(){
    // 打开商品图片上传器
    $('#open_uploader').unbind('click');
    $('#open_uploader').click(function(){
        if($('#uploader').css('display') == 'none'){
            $('#uploader').show();
            $(this).find('.hide').attr('class','show');
        }else{
            $('#uploader').hide();
            $(this).find('.show').attr('class','hide');
        }
    });

    // 打开编辑器图片上传器
    $('#open_editor_uploader').unbind('click');
    $('#open_editor_uploader').click(function(){
        if($('#editor_uploader').css('display') == 'none'){
            $('#editor_uploader').show();
        }else{
            $('#editor_uploader').hide();
        }
    });
    // 打开商品远程地址上传
    $('#open_remote').unbind('click');
    $('#open_remote').click(function(){
        if($('#remote').css('display') == 'none'){
            $('#remote').show();
        }else{
            $('#remote').hide();
        }
    });
    // 打开编辑器远程地址上传
    $('#open_editor_remote').unbind('click');
    $('#open_editor_remote').click(function(){
        if($('#editor_remote').css('display') == 'none'){
            $('#editor_remote').show();
        }else{
            $('#editor_remote').hide();
        }
    });

    /* 悬停解释 */
    $('*[ecm_title]').hover(function(){
        $('*[ectype="explain_layer"]').remove();
        $(this).parent().parent().append('<div class="titles" ectype="explain_layer" style="display:none; z-index:999">' + $(this).attr('ecm_title') + '<div class="line"></div></div>');
        $('*[ectype="explain_layer"]').fadeIn();
    },
    function(){
        $('*[ectype="explain_layer"]').fadeOut();
    }
    );

    /* 图片控制 */
    var handle_pic, handler, drop, cover, insert;

    $('*[ectype="handle_pic"]').find('img:first').hover(function(){
        $('*[ectype="explain_layer"]').remove();
        handle_pic = $(this).parents('*[ectype="handle_pic"]');
        handler = handle_pic.find('*[ectype="handler"]');
        var parents = handler.parents();
        handler.show();
        handler.hover(function(){
            $(this).show();
            set_zindex(parents, "999");
        },
        function(){
            $(this).hide();
            set_zindex(parents, "0");
        });
        set_zindex(parents, '999');

        cover = handler.find('*[ectype="set_cover"]');
        cover.unbind('click');
        cover.click(function(){
            set_cover(handle_pic.attr("file_id"));
        });

        drop = handler.find('*[ectype="drop_image"]');
        drop.unbind('click');
        drop.click(function(){
            drop_image(handle_pic.attr("file_id"));
        });

        insert = handler.find('*[ectype="insert_editor"]');
        insert.unbind('click');
        insert.click(function(){
            insert_editor(handle_pic.attr("file_name"),handle_pic.attr("file_path"));
        });
    },
    function(){
        handler.hide();
        var parents = handler.parents();
        set_zindex(parents, '0');
    });
}
