<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>404 Page Not Found</title>
<link rel="shortcut icon" href="<?php echo config_item('base_url'); ?>favicon.ico" type="image/x-icon">
<link rel="icon" href="<?php echo config_item('base_url'); ?>favicon.ico" type="image/x-icon">
<style type="text/css">
::selection { background-color: #db4673; color: white; }
::-moz-selection { background-color: #db4673; color: white; }

body {
	background-color: #fcf8f9;
	margin: 40px;
	font: 14px/22px system-ui, -apple-system, sans-serif;
	color: #331520;
}

a {
	color: #db4673;
	background-color: transparent;
	font-weight: 600;
	text-decoration: none;
}

a:hover {
	text-decoration: underline;
}

h1 {
	color: #881337;
	background-color: #fdf2f4;
	border-bottom: 1px solid #fecdd6;
	font-size: 20px;
	font-weight: 700;
	margin: 0;
	padding: 16px 20px;
	border-top-left-radius: 8px;
	border-top-right-radius: 8px;
}

#container {
	max-width: 650px;
	margin: 50px auto;
	background: #ffffff;
	border: 1px solid #fecdd6;
	border-radius: 8px;
	box-shadow: 0 4px 16px rgba(136, 19, 55, 0.08);
	overflow: hidden;
}

p {
	margin: 16px 20px;
	line-height: 1.6;
}
</style>
</head>
<body>
	<div id="container">
		<h1><?php echo $heading; ?></h1>
		<?php echo $message; ?>
	</div>
</body>
</html>

