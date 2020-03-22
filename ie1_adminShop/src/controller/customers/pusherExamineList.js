/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 应该创建于 2019/10/25
 * 推客审核
 */

layui.define(function (exports) {
    layui.use(['table', 'jquery', 'form', 'admin', 'setter', 'element', 'laydate', 'laypage'], function () {
        var element = layui.element;
        var $ = layui.$;
        var form = layui.form;
        var layDate = layui.laydate;
        var laypage = layui.laypage;
        var arr = {};//全局ajax请求参数
        var open_index;//定义弹出层，方便关闭
        var operation_id;//数据表格操作需要用到单条 id
        var total_count = 0;//订单总数，分页用
        var pageLimit = 10;//查询使用到的每页显示数量，只需要初始化与 limit 相同即可
        var limit = 10;//列表中每页显示数量
        var tabPage = 1;//获取当前分页的页数
        var tab_id = 0;
        form.render();
        getGroupExamineList('');
        getPage();

        //默认列表分页
        function getPage() {
            laypage.render({
                elem: 'page' //注意，这里的 page 是 ID，不用加 # 号
                , count: total_count //数据总数，从服务端得到
                , prev: '<'
                , next: '>'
                , limit: pageLimit
                , limits: [limit, limit * 2, limit * 3]
                , layout: ['prev', 'page', 'next', 'refresh', 'skip', 'limit']
                , jump: function (obj, first) {
                    pageLimit = obj.limit;
                    tabPage = obj.curr;
                    //首次不执行
                    if (!first) {
                        var status = $('#status').val();
                        var time = $('#date-range0').val();
                        var searchName = $('input[name=searchName]').val();
                        var s_info = '';
                        if (status === '') {
                            s_info += 'type=3';
                        } else {
                            s_info += 'type=' + status;
                        }
                        if (time !== '') {
                            //判断时间范围的格式
                            var time_arr = time.split(' to ');
                            if (time_arr.length !== 2) {
                                layer.msg('时间范围格式错误', {icon: 1, time: 2000});
                                return;
                            }
                            s_info += '&time=' + time;
                        }
                        if (searchName !== '') {
                            s_info += '&searchName=' + searchName;
                        }
                        getGroupExamineList(s_info)
                    }
                }
            });
        }

        //获取团长审核列表
        function getGroupExamineList(s_info) {
            total_count = 0;
            $('.groupExamineList').empty().append('<tr>\n' +
                '                        <th>会员ID</th>\n' +
                '                        <th>头像+昵称</th>\n' +
                '                        <th>姓名+手机号</th>\n' +
                '                        <th>团长配送费</th>\n' +
                '                        <th>消费金额</th>\n' +
                '                        <th>推荐人姓名</th>\n' +
                '                        <th>审核人姓名</th>\n' +
                '                        <th>城市</th>\n' +
                '                        <th>小区名称</th>\n' +
                '                        <th>申请时间</th>\n' +
                '                        <th>审核状态</th>\n' +
                '                    </tr>');
            if (s_info === '') {
                s_info = 'type=3';
            }
            s_info += '&limit=' + pageLimit;
            s_info += '&page=' + tabPage;
            arr = {
                method: 'merchantTuanUser',
                type: 'get',
                params: s_info + '&is_self=0'//type 3是待审核和失败 0待审核 1审核通过 2审核失败 不传 type 是全部
            };
            var res = getAjaxReturnKey(arr);
            if (!res || !res.data) {
                return false;
            }
            total_count = res.count;
            var data = res.data;
            var len = data.length;
            for (var i = 0; i < len; i++) {
                $('.groupExamineList').append(groupExamineDiv(data[i]));
            }
        }

        //搜索团长审核列表  点击搜索的时候，设置的 pageLimit tabPage 需要处理
        $(document).off('click', '.examine_search').on('click', '.examine_search', function () {
            tabPage = 1;
            var status = $('#status').val();
            var time = $('#date-range0').val();
            var searchName = $('input[name=searchName]').val();
            var s_info = '';
            if (status === '') {
                s_info += 'type=3';
            } else {
                s_info += 'type=' + status;
            }
            if (time !== '') {
                //判断时间范围的格式
                var time_arr = time.split(' to ');
                if (time_arr.length !== 2) {
                    layer.msg('时间范围格式错误', {icon: 1, time: 2000});
                    return;
                }
                s_info += '&time=' + time;
            }
            if (searchName !== '') {
                s_info += '&searchName=' + searchName;
            }
            getGroupExamineList(s_info);
            getPage();
        });

        //搜索时间清空
        $(document).off('click', '.empty_time').on('click', '.empty_time', function () {
            $('#date-range0').val('');
            $('#date-range1').val('');
        });

        //点击待审核按钮执行操作
        $(document).off('click', '.need_audit').on('click', '.need_audit', function () {
            var that = this;
            var id = $(that).attr('id');
            //打开新窗口，显示通过和不通过按钮
            layer.confirm('该团长是否通过审核？', {
                btn: ['通过', '不通过'] //可以无限个按钮
                , btnAlign: 'c'
                , btn1: function (index) {
                    //按钮 通过 的回调
                    layer.close(index);
                    arr = {
                        method: 'merchantTuanUser/' + id,
                        type: 'put',
                        data: {
                            status: 1,
                        },
                    };
                    var res = getAjaxReturnKey(arr);
                    if (!res) {
                        return false;
                    }
                    layer.msg(res.message, {icon: 1, time: 2000});
                    $(that).parent().parent().parent().remove();
                }
                , btn2: function () {
                    //按钮 不通过 的回调 需要修改审核状态
                    arr = {
                        method: group_method + '/' + id,
                        type: 'put',
                        data: {
                            status: 0,
                        },
                    };
                    var res = getAjaxReturnKey(arr);
                    if (!res) {
                        return false;
                    }
                    layer.msg(res.message, {icon: 1, time: 2000});
                    $(that).parent().removeClass('btn-green').addClass('btn-red').html('审核未通过');
                }
            });
        })
    });
    exports('customers/pusherExamineList', {})
});

//团长审核div
function groupExamineDiv(info) {
    var status = info.status;
    if (status === '0') {
        status = '<span class="btn btn-green"><a href="javascript:void(0)" class="need_audit" id="' + info.id + '">待审核</a></span>';
    } else if (status === '2') {
        status = '<span class="btn btn-red">审核未通过</span>';
    } else {
        status = '<span class="btn btn-red">类型错误</span>';
    }
    var area = info.province + info.city + info.area;
    return '                   <tr>\n' +
        '                            <td>' + info.id + '</td>\n' +
        '                            <td>\n' +
        '                                <img src="' + (info.avatar ? info.avatar : '') + '" alt="" class="face">\n' +
        '                                <div class="name">' + (info.nickname ? info.nickname : '无') + '</div>\n' +
        '                            </td>\n' +
        '                            <td>' + info.realname + '<br>' + (info.phone ? info.phone : '无') + '</td>\n' +
        '                            <td>' + parseFloat(info.tuan_express_fee ? info.tuan_express_fee : 0) + '</td>\n' +
        '                            <td>' + parseFloat(info.money ? info.money : 0) + '</td>\n' +
        '                            <td>未设置</td>\n' +
        '                            <td>' + info.audit_name + '</td>\n' +
        '                            <td>' + area + '</td>\n' +
        '                            <td>' + info.area_name + '</td>\n' +
        '                            <td>' + info.format_create_time + '</td>\n' +
        '                            <td>' + status + '</td>\n' +
        // '                            <td><a href="" class="green">查看详情</a></td>\n' +
        '                        </tr>';
}