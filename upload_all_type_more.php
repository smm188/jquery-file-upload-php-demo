<!DOCTYPE html>
<html>
<head>
	<title></title>
	<script src="jquery-1.12.0.min.js"></script>
    <script src="jquery.ui.widget.js"></script>
    <script src="jquery.iframe-transport.js"></script>
</head>
<body>
<?php require_once 'upload_plugin_more.php';?>    
    <div>上传图片测试：</div>
    <?php echo upload_plugin_more::get_upload_more_file_html();?>
</body>
</html>
