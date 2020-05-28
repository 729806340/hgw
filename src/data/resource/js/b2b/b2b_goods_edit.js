
$(function(){

//�Զ����ع�����
    $('#type_div').perfectScrollbar();
//��ť��ִ����֤���ύ��
    $("#submitBtn").click(function(){
        $("#goods_class_form").submit();
    });

    // �������
    var i = 0;

    var ul1_attr = '<li>' +
        '<label class="w100 center"><input type="goods_calculate" class="w50" name="at_value[key][calculate]" value="0" /></label>' +
        '<label class="w100 center"><input type="goods_price" class="w50" name="at_value[key][price]" value="0" /></label>' +
        '<label class="w100 center"><input type="goods_storage" class="w50" name="at_value[key][storage]" value="0" /></label>' +
        '<label class="w100 center"><input type="goods_cost" class="w50" name="at_value[key][cost]" value="0" /></label>' +
        '<label class="w100 center"><input type="tax_input" class="w50" name="at_value[key][tax_input]" value="0" /></label>' +
        '<label class="w100 center"><input type="tax_output" class="w50" name="at_value[key][tax_output]" value="0" /></label>' +
        '<label class="w100 center"><a onclick="remove_attr($(this));" class="ncap-btn ncap-btn-red" href="JavaScript:void(0);">移除</a></label>' +
        '</li>';

    $("#add_type").click(function(){
        $('#ul_attr > li:last').after(ul1_attr.replace(/key/g, i));
        i++;
    });


    // ͼƬ�ϴ�
    $('#fileupload').each(function(){
//        console.log('fileupload');
        $(this).fileupload({
            dataType: 'json',
            url: 'index.php?act=goods&op=upload_pic',
            done: function (e,data) {
                if(data != 'error'){
                    add_uploadedfile(data.result);
                }
            }
        });
    });


});





function remove_attr(o){
    o.parents('li:first').remove();
}