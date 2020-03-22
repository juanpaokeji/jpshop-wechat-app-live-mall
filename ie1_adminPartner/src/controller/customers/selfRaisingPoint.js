/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2018/3/2
 * 团购
 */

layui.define(function (exports) {
    layui.use(['table', 'jquery', 'form', 'admin', 'setter', 'element', 'laydate', 'laypage'], function () {
        var $ = layui.$;
        var form = layui.form;
        var laypage = layui.laypage;
        var arr = {};//全局ajax请求参数
        var total_count = 0;//订单总数，分页用
        var pageLimit = 10;//查询使用到的每页显示数量，只需要初始化与 limit 相同即可
        var limit = 10;//列表中每页显示数量
        var tabPage = 1;//获取当前分页的页数
        form.render();

        //搜索时间清空
        $(document).off('click', '.empty_time').on('click', '.empty_time', function () {
            $('#date-range0').val('');
        });

        //加载团长列表页面，并获取团长列表
        getGroupList('');
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
                        getGroupList('');
                    }
                }
            });
        }

        //团长列表开始
        function getGroupList(s_info) {
            $('.groupList').empty().append('<tr>\n' +
                '                        <th>头像</th>\n' +
                '                        <th style="width: 138px">昵称</th>\n' +
                '                        <th>团长信息</th>\n' +
                '                        <th>所属城市</th>\n' +
                '                        <th>自提点信息</th>\n' +
                '                        <th>团长配送费</th>\n' +
                '                        <th>总订单数</th>\n' +
                '                        <th>旗下团员</th>\n' +
                '                        <th>佣金</th>\n' +
                '                        <th>状态</th>\n' +
                '                    </tr>');
            arr = {
                method: 'partnerLeader',
                type: 'get',
                data: {
                    limit: pageLimit,
                    page: tabPage,
                    type: '1',
                    is_self: s_info
                }
            };
            var res = getAjaxReturn(arr);
            if (res && res.data) {
                var data = res.data;
                var len = data.length;
                total_count = res.count;
                for (var i = 0; i < len; i++) {
                    $('.groupList').append(groupListDiv(data[i]));
                }
            }
        }

        //搜索团长列表
        $(document).off('click', '.list_search').on('click', '.list_search', function () {
            var time = $('#date-range0').val();
            var listSearchName = $('input[name=listSearchName]').val();
            var search_city = $('input[name=search_city]').val();
            var s_info = '';
            if (time !== '') {
                //判断时间范围的格式
                var time_arr = time.split(' to ');
                if (time_arr.length !== 2) {
                    layer.msg('时间范围格式错误', {icon: 1, time: 2000});
                    return;
                }
                s_info += '&audit_time=' + time;
            }
            if (listSearchName !== '') {
                s_info += '&searchName=' + listSearchName;
            }
            if (search_city !== '') {
                s_info += '&city=' + search_city;
            }
            getGroupList(s_info);
        });

    });
    exports('customers/selfRaisingPoint', {})
});

//团长列表div
function groupListDiv(info) {
    var state = info.state;
    var state_a = '';
    //0 正常 1 冻结 2 关闭
    if (state === '0') {
        state_a = '正常';
    } else if (state === '1') {
        state_a = '冻结';
    } else if (state === '2') {
        state_a = '关闭';
    } else {
        state_a = '数据错误'
    }
    var sum_money = info.sum_money ? info.sum_money : 0;//总消费佣金
    var on_money = info.on_money ? info.on_money : 0;//待结算佣金
    var user_balance = info.user_balance ? info.user_balance : 0;//待提现金额
    return '               <tr>\n' +
        '                        <td><img src="' + (info.avatar ? info.avatar : '') + '" alt="" class="face"></td>\n' +
        '                        <td>' + (info.nickname ? info.nickname : '无') + '</td>\n' +
        '                        <td class="tLeft">' + info.realname + '<br>' + (info.phone ? info.phone : '无') + '</td>\n' +
        '                        <td>' + info.province + '<br/>' + info.city + '<br/>' + info.area + '</td>\n' +
        '                        <td class="tLeft">小区：' + info.area_name + '<br>自提点：' + info.addr + '</td>\n' +
        '                        <td>' + parseInt(info.tuan_express_fee) + '</td>\n' +
        '                        <td><i class="blue">' + info.self_number + '</i></td>\n' +
        '                        <td><i class="blue"><a data="' + info.uid + '" href="javascript:void(0)" class="green count">' + (info.count ? info.count : 0) + '</a></i></td>\n' +
        '                        <td class="tLeft">\n' +
        '                            <p>总消费佣金：' + sum_money + '</p>\n' +
        '                            <p>待结算佣金：' + on_money + '</p>\n' +
        '                            <p>待提现金额：' + user_balance + '</p>\n' +
        '                        </td>\n' +
        '                        <td><a data="' + info.id + '" state="' + info.state + '" href="javascript:void(0)" class="btn btn-green now_state">' + state_a + '</a></td>\n' +
        '                    </tr>';
}
