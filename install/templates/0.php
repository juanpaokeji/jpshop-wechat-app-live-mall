<?php require './templates/header.php';?>
	<form name="theForm" action="index.php?step=0" method="post" onsubmit="return dataVerify();">
		<div class="section">
		<div class="main server">
			<table width="100%">
				<tr>
					<td class="td1" width="100">Storage设置</td>
					<td class="td1" width="200">&nbsp;</td>
					<td class="td1">&nbsp;</td>
				</tr>
				<tr>
					<td class="tar">Storage名称：</td>
					<td><input type="text" name="storagedomain" id="storagedomain" value="<?php echo $config['uploaddir']?>" class="input"></td>
					<td><span>Storage是SAE为开发者提供的分布式文件存储服务</span></td>
				</tr>
			</table>
		</div>
		</div>
		<div class="btn-box">
			<button type="submit" class="btn">下一步</button>
		</div>
	</form>
	<script>
	function dataVerify(){
		var storage = document.theForm.storagedomain;
		if(storage.value == ''){
			storage.focus();
			alert('Storage名称不能为空!');
			return false;
		}
		return true;
	}
	</script>
<?php require './templates/footer.php';?>