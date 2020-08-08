<?php
class uploadFiles {

	//数组，$_FILES中存放的临时文件信息：
	protected static $postFile;
	//字符串，$_FILES中存放文件的扩展名
	protected static $ext;
	//数组，允许上传的文件扩展名，可自定义：
	protected static $allowExt;
	//整型，允许上传的文件大小上限，可自定义，单位b（最大不可超过php.ini中设置的值）
	protected static $maxSize;
	//整型，标识上传文件的类型：1-图片 2-视频或音频
	protected static $fileType;	
	//整型，允许上传的图片宽度上限，可自定义，单位px（若上传文件为图片）
	protected static $maxWidth;
	//整型，允许上传的图片高度上限，可自定义，单位px（若上传文件为图片）
	protected static $maxHight;
	//字符串，预览文件存放目录，可自定义
	protected static $previewPath = 'upload/preview/';
	//字符串，文件最终存放目录，可自定义
	protected static $finalPath = 'upload/upload/';
	//返回数组
	protected static $result = array('sta'=>TRUE, 'msg'=>'文件上传成功！');

	/**
	 * 上传图片类型参照
	 *
	 * @var Array
	 */
	protected  static $imageApp = array(
		//图片格式
		'gif' => array('image/gif'),
		'jpg' => array('image/pjpeg','image/jpeg'),
		'jpeg' => array('image/pjpeg','image/jpeg'),
		'png' => array('image/png')
	);
	/**
	 * 上传视频、音频类型参照
	 *
	 * @var Array
	 */
	protected  static $videoApp = array(
		//视频格式
		'mp4' => array('video/mp4'),
		'3gp' => array('application/octet-stream'),
		'flv' => array('application/octet-stream'),
		'mkv' => array('application/octet-stream'),
		'rmvb' => array('application/vnd.rn-realmedia-vbr'),
		'wmv' => array('video/x-ms-wmv'),
		'avi' => array('video/avi'),
		//音频格式
		'mp3' => array('audio/mpeg'),
		'wav' => array('audio/wav')
	);

	/**
	 * 构造方法，给各属性赋值
	 *
	 * 
	 */
	public function __construct($postFile,$allowExt=array(),$maxSize=0,$maxWidth=0,$maxHight=0,$previewPath='',$finalPath='') {

		if(is_array($postFile) && count($postFile)>0) {
			self::$postFile = $postFile;
			if(is_array($postFile['name'])) {
				$arr = explode(".", $postFile['name'][0]);
			} else {
				$arr = explode(".", $postFile['name']);
			}			
			self::$ext = $arr[count($arr) - 1];
		} else {
			self::$result['sta'] = FALSE;
			self::$result['msg'] = $this->getErrorMsg(1);
		}

		self::$allowExt = array_keys(self::$fileApp);
		if(is_array($allowExt) && count($allowExt)>0) {
			self::$allowExt = $allowExt;
		}

		if($maxSize > 0) {
			self::$maxSize = $maxSize;
		} else {
			self::$maxSize = 50 * 1024 * 1024;
		}

		if($maxWidth > 0) {
			self::$maxWidth = $maxWidth;
		} else {
			self::$maxWidth = 200;
		}

		if($maxHight > 0) {
			self::$maxHight = $maxHight;
		} else {
			self::$maxHight = 200;
		}

		self::$previewPath = 'upload/preview/';
		if($previewPath != '') {
			self::$previewPath = $this->formatPath($previewPath);
		}
		$this->createDir(self::$previewPath);

		self::$finalPath = 'upload/final/';
		if($finalPath != '') {
			self::$finalPath = $this->formatPath($finalPath);
		}
		$this->createDir(self::$finalPath);
	}

	/**
	 * 获取预览地址
	 *
	 * @return String
	 */
	public function getPreviewSrc() {

		//判断图片文件扩展名和类型是否正确
		$this->checkFileNameExt();
		//判断文件大小
		$this->checkFileSize();
		//如果文件为图片，判断其宽高
		$this->checkImageWH();		

		//把临时文件上传到预览目录并重命名
		$previewName = 'pre_'.mt_rand(1000000,999999).time().'.'.self::$ext;
		$previewSrc = self::$previewPath.$previewName;
		if($this->getFileType() == 1) {
			if(!move_uploaded_file(self::$postFile['tmp_name'],$previewSrc)) {
				self::$result['sta'] = FALSE;
				self::$result['msg'] = $this->getErrorMsg(0);
			} else {
				self::$result['previewSrc'] = $previewSrc;
			}
		}

		return self::$result;
	}	

	/**
	 * 格式化目录
	 *
	 * @return String
	 */
	protected function formatPath($path) {

		$path = str_replace('\\','/',$path);
		$path = rtrim($path,'/').'/';

		return $path;
	}

	/**
	 * 创建文件目录
	 *
	 * @return Array
	 */
	protected function createDir($path) {

		$arr = explode('/',$path);
		$dirAll = '';
		if(count($arr) > 0) {
			foreach($arr as $key=>$value) {
				$tmp = trim($value);
				if($tmp != '') {
					$dirAll .= $tmp.'/';
					if(!file_exists($dirAll)) {
						if(mkdir($dirAll,0777,true)) {
							self::$result['sta'] = FALSE;
							self::$result['msg'] = $this->getErrorMsg(7);
						}
					}
				}
			}
		}

		if(!self::$result['sta']) {
			return self::$result;
		}

	}

	/**
	 * 判断图片文件扩展名和类型是否正确
	 *
	 * @return Array
	 */
	protected function checkFileNameExt() {

		if (self::$ext != "") {
			//判断文件扩展名
			if (!in_array(self::$ext, self::$allowExt)) {
				self::$result['sta'] = FALSE;
				self::$result['msg'] = $this->getErrorMsg(2);
			}

			//判断文件类型
			if (isset(self::$fileApp[self::$ext])) {
				if (!in_array(self::$postFile['type'], self::$fileApp[self::$ext])) {
					self::$result['sta'] = FALSE;
					self::$result['msg'] = $this->getErrorMsg(3);
				}
			} else {
				self::$result['sta'] = FALSE;
				self::$result['msg'] = $this->getErrorMsg(6);
			}			
		}

		if(!self::$result['sta']) {
			return self::$result;
		}
		
	}

	/**
	 * 判断文件大小
	 *
	 * @return Array
	 */
	protected function checkFileSize() {

		if (self::$postFile['size'] > self::$maxSize) {
			self::$result['sta'] = FALSE;
			self::$result['msg'] = $this->getErrorMsg(4);
		}
		
		if(!self::$result['sta']) {
			return self::$result;
		}
	}

	/**
	 * 如果文件为图片，判断其宽高
	 *
	 * @return Array
	 */
	protected function checkImageWH() {
		
		if($this->getFileType() == 1) {
			$imageInfo = getimagesize(self::$postFile['tmp_name']);
			if($imageInfo[0]>self::$maxWidth || $imageInfo[1]>self::$maxHight) {
				self::$result['sta'] = FALSE;
				self::$result['msg'] = $this->getErrorMsg(5);
			}
		}

		if(!self::$result['sta']) {
			return self::$result;
		}
		
	}

	/**
	 * 获取文件类型：1-图片；2-视频；3-音频
	 *
	 * @return Int
	 */
	protected function getFileType() {

		$type = 0;
		switch (self::$ext) {
			case 'gif':
			case 'jpg':
			case 'jpeg':
			case 'png':
				$type = 1;
				break;

			case 'mp4':
			case '3gp':
			case 'flv':
			case 'mkv':
			case 'rmvb':
			case 'wmv':
			case 'avi':
				$type = 2;
				break;

			case 'mp3':
			case 'wav':
				$type = 3;
				break;
		}
		return $type;
	}

	/**
	 * 获取错误信息
	 *
	 * @param Int $type 错误信息类型
	 * @return String
	 */
	protected function getErrorMsg($type) {
		$errorMsg = '';
		switch ($type) {
			case 1:
				$errorMsg = '文件未上传！';
				break;
			case 2:
				$errorMsg = '文件扩展名不符合要求！';
				break;
			case 3:
				$errorMsg = '文件类型不符合规范！';
				break;
			case 4:
				$errorMsg = '文件大小超出限制！';
				break;
			case 5:
				$errorMsg = '图片的宽高不符合要求！';
				break;
			case 6:
				$errorMsg = '系统不支持此类型文件的上传！';
				break;
			case 7:
				$errorMsg = '系统错误！';
			
			default:
				$errorMsg = '文件上传失败！';
				break;
		}
		$errorMsg .= '请仔细检查上传文件后，再重新上传！';
		return $errorMsg;
	}
}