<!DOCTYPE html>
<html>
<head>
	<title></title>
	<script src="jquery-1.12.0.min.js"></script>
    <script src="jquery.ui.widget.js"></script>
    <script src="jquery.iframe-transport.js"></script>
</head>
<body>
<?php require_once 'upload_plugin_one.php';?>    
    <div>上传一张正确图片测试：</div>
    <?php echo upload_plugin_one::get_upload_one_file_html();?>
    <div>上传一张错误图片测试：</div>
    <?php echo upload_plugin_one::get_upload_one_file_html();?>
    <div>上传一个正确视频测试：</div>
    <?php echo upload_plugin_one::get_upload_one_file_html('','video');?>
    <div>上传一个错误视频测试：</div>
    <?php echo upload_plugin_one::get_upload_one_file_html('','video');?>
</body>
</html>
