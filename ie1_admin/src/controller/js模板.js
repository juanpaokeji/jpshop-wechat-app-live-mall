/**

 @Name：layuiAdmin 设置
 @Author：贤心
 @Site：http://www.layui.com/admin/
 @License: LPPL
 应用列表
 */
 
layui.define(['table','jquery','form','jquery','layer'],function(exports){
	var form = layui.form;
	var $ = layui.$;
	var table = layui.table;
	var form = layui.form;
	var layer = layui.layer;
	console.log(layer)
	var url = './start/json/ceshi/ApplicationGroup.json';
	var tableIns;
	
	layui.use(['form'],function(){
		
		/*添加员工事件*/
		$('#add').click(function(){
			layer.open({
				
			})
		})
	})
	
	
	/*数据表格*/
  layui.use(['table'],function(){
		/*数据表格渲染*/
		var tableIns = table.render({
			elem:'#views_set_system_permissions_table',
			height:630,
			url:url,
			method:'get',
			cols:[[
				{field: 'pid', title: '类目名称', width: '10%',align:'center'},
				{field: 'status', title: '状态', width: '15%',templet: '#statusTpl',align:'center'},
				{field:'opreationes',title:'操作',toolbar:'#opreationes',align:'center',width:'15%',fixed:'right'}
			]],
			response: {
				statusName: 'status',
				statusCode: "200",
				dataName: 'data'
			},
			headers:{
				'Access-Token': layui.data('layuiAdmin').access_token
			}
		})
		
		/*表格重载*/
		form.on('submit(find)',function(){
			tableIns.reload({
				where:{
					id:$('input[name=title]').val()
				}
			})
		})
		
		/*工具条点击*/
		table.on('tool(路径)',function(obj){
			var data = obj.data;
			var layEvent = obj.event;
			var tr = obj.tr;
			console.log(data)
			
			if(layEvent === 'edit'){//修改
				layer_form = layer.open({
					type: 1, 
					title:'添加套餐',
					content: $('#formm'), //这里content是一个普通的String
					shade:0,
					area:['700px','568px'],
					btn:'提交',
					yes:function(index,layero){
						$.ajax({
							url:url,
							type:'put',
							async:false,
							data:{
								
							},
							success:function(data){
								console.log(data)
								layer.close(index)
								tableIns.reload()
							},
							error:function(err){
								console.log(err)
							},
							berforSend:function(){
							}
						})
					}
				});
			}else if(layEvent === 'del'){
				layer.confirm('确定删除该数据?', function(index){
					obj.del();
					layer.close(index);
					$.ajax({
						url:url,
						type:'delete',
						data:{
							id:data.id
						},
						async:false,
						success:function(data){
							console.log(data)
							tableIns.reload()
						},
						error:function(err){
							console.log(data)
						}
					})
				});
			}
		})
		
	})
  
  
  //对外暴露的接口
  exports('', {});
});