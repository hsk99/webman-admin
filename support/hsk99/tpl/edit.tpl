<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="/static/component/pear/css/pear.css" />
    <script src="/static/component/layui/layui.js"></script>
    <script src="/static/component/pear/pear.js"></script>
</head>

<body>
    <form class="layui-form" action="">
        <div class="mainBox">
            <div class="main-container">
                {{$html_form}}
            </div>
        </div>
        <div class="bottom">
            <div class="button-container">
                <button type="submit" class="layui-btn layui-btn-normal layui-btn-sm" lay-submit="" lay-filter="save">
                    <i class="layui-icon layui-icon-ok"></i>
                    提交
                </button>
                <button type="reset" class="layui-btn layui-btn-primary layui-btn-sm">
                    <i class="layui-icon layui-icon-refresh"></i>
                    重置
                </button>
            </div>
        </div>
    </form>
    <script>
        let UPLOAD_IMAGE_PATH = "/{:request()->app}/index/upload";
        layui.use(['form', 'jquery', 'layedit', 'uploads', 'laydate'], function () {
            let form = layui.form;
            let $ = layui.jquery;
            let layedit = layui.layedit;
            let laydate = layui.laydate;
            layedit.set({
                uploadImage: {
                    url: UPLOAD_IMAGE_PATH
                }
            });
            //建立编辑器
            {{$html_js}}
            form.on('submit(save)', function (data) {
                {{$html_js_data}}
                let loading = layer.load();
                $.ajax({
                    data: data.field,
                    dataType: 'json',
                    type: 'post',
                    success: function (res) {
                        layer.close(loading);
                        //判断有没有权限
                        if (res && res.code == 999) {
                            layer.msg(res.msg, {
                                icon: 5,
                                time: 2000,
                            })
                            return false;
                        } else if (res.code == 200) {
                            layer.msg(res.msg, { icon: 1, time: 1000 }, function () {
                                parent.layer.close(parent.layer.getFrameIndex(window.name));//关闭当前页
                                parent.layui.table.reload("dataTable");
                            });
                        } else {
                            layer.msg(res.msg, { icon: 2, time: 1000 });
                        }
                    }
                })
                return false;
            });
        })
    </script>
</body>

</html>