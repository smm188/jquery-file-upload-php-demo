<?php
require_once 'uploadFiles.php';
$file = $_FILES['files'];
// var_dump($file);
//formData传过来的参数order_id和customer_id
$order_id = $_POST['order_id'];
$customer_id = $_POST['customer_id'];

$uploadClass = new uploadFiles($file);

$fileInfo = array();
if($file['error'] == 0) {	
	if(isset($order_id) && isset($customer_id)) {
		//数据库操作
		//...
		//文件上传到预览目录
		// if(move_uploaded_file($file['tmp_name'],"upload/{$file['name']}")) {
		// 	$fileInfo['name'] = $file['name'];
		// 	$fileInfo['sta'] = true;
		// }
		$fileInfo = $uploadClass->getPreviewSrc();
	} 
} 

echo json_encode($fileInfo);
