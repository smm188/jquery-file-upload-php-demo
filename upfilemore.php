<?php
$fileArr = $_FILES['files'];

//设置预览目录
$previewPath = "upload/preview/";
creatDir($previewPath);

//ajax返回数组
$data = array('sta'=>TRUE,'msg'=>'图片上传成功！');

//检查是否为图片
$ext = getExt($fileArr['name'][0]);
$arrExt = array('jpg','jpeg','gif','png');
if(!in_array($ext,$arrExt)) {
	$data['sta'] = FALSE;
	$data['msg'] = 'Error:文件《'.$fileArr['name'][0].'》不是图片或采用了不合适的扩展名！';
} else {
	//文件上传到预览目录	
	if($fileArr['error'][0] == 0) {				
		$previewName = 'pre_'.md5(mt_rand(1000,9999)).time().'.'.$ext;
		$previewSrc = $previewPath.$previewName;
		// $fileName = $fileArr['name'][0];
		if(!move_uploaded_file($fileArr['tmp_name'][0],$previewSrc)) {
			$data['sta'] = FALSE;
			$data['msg'] = $fileArr['name'][0].'图片上传失败！';
		} else {
			$data['msg'] = $fileArr['name'][0].'图片上传成功！';
			$data['previewSrc'] = $previewSrc;
		}			
	}
}
 
echo json_encode($data);

//获取文件扩展名
function getExt($filename) {
	$ext = pathinfo($filename, PATHINFO_EXTENSION);
	return $ext;
}

//创建目录并赋权限
function creatDir($path) {
	$arr = explode('/',$path);
	$dirAll = '';
	$result = FALSE;
	if(count($arr) > 0) {
		foreach($arr as $key=>$value) {
			$tmp = trim($value);
			if($tmp != '') {
				$dirAll .= $tmp.'/';
				if(!file_exists($dirAll)) {
					mkdir($dirAll,0777,true);					
				}
			}
		}
	}
}
