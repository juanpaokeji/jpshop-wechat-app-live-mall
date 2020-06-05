<!doctype html>
<html>
	<head>
		<meta charset="UTF-8" />
		<title><?php echo $config['name'].' '.$config['version']; ?> - <?php echo $config['powered']; ?></title>
		<link rel="stylesheet" href="./templates/css/install.css" />
	</head>
	<body>
		<div class="wrap" >
			<div class="header"background='#5ECA5E'>
				<h1 class="header-title"><?php echo $config['name']; ?></h1>
				<div class="header-install">安装向导</div>
				<!-- <div class="header-version">版本：<?php echo $config['version'];?></div> -->
			</div>
			<div class="step">
				<ul>
					<?php echo $step_html;?>
				</ul>
			</div>
<style>
</style>