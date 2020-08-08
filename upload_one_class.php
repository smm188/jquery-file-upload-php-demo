<?php
/**
 * 处理单个上传文件类
 * 主要实现功能：1.判断文件类型和扩展名 2.判断文件大小 3.判断图片宽高 4.获取预览地址 5.移动文件（通常用于把预览文件移动到最终目录）
 *
 */
class upload_one_class {
	//数组，$_FILES中存放的临时文件信息：
	protected static $post_file;
	//字符串，$_FILES中存放文件的扩展名
	protected static $ext;
	//数组，允许上传的文件扩展名，可自定义：
	protected static $allow_ext;
	//整型，允许上传的文件大小上限，可自定义，单位b（最大不可超过php.ini中设置的值）
	protected static $max_size;
	//整型，标识上传文件的类型：1-图片 2-视频或音频
	protected static $file_type;	
	//整型，允许上传的图片宽度上限，可自定义，单位px（若上传文件为图片）
	protected static $max_width;
	//整型，允许上传的图片高度上限，可自定义，单位px（若上传文件为图片）
	protected static $max_hight;
	//字符串，预览文件存放目录，可自定义
	protected static $preview_path = '';
	//字符串，文件最终存放目录，可自定义
	protected static $final_path = '';
	//返回数组
	protected static $result = array('sta'=>TRUE, 'msg'=>'文件上传成功！');

	/**
	 * 上传图片类型参照
	 *
	 * @var Array
	 */
	protected  static $image_app = array(
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
	protected  static $video_app = array(
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
	 */
	public function __construct($allow_ext=array(),$max_size=0,$max_width=0,$max_hight=0,$preview_path='',$final_path='') {
		//取得上传文件信息数组
		if(count($_FILES) > 0) {
			foreach($_FILES as $file) {
				self::$post_file = $file;
			}
		}
		//取得文件扩展名
		if(!empty(self::$post_file)) {
			$arr = explode(".", self::$post_file['name']);					
			self::$ext = $arr[count($arr) - 1];
		} else {
			self::$result['sta'] = FALSE;
			self::$result['msg'] = $this->get_error_msg(1);
		}
		//取得文件类型：1-图片 2-视频或音频
		if(in_array(self::$ext,self::$image_app)) {
			self::$file_type = 1;
		} 
		if(in_array(self::$ext,self::$video_app)) {
			self::$file_type = 2;
		}
		//如果上传的文件为图片，则设置宽高最大值
		if(self::$file_type == 1) {
			//设置允许的上传图片最大宽度值
			if($max_width > 0) {
				self::$max_width = $max_width;
			} else {
				self::$max_width = 200;
			}
			//设置允许的上传图片最大高度值
			if($max_hight > 0) {
				self::$max_hight = $max_hight;
			} else {
				self::$max_hight = 200;
			}
		}
		//设置允许的上传文件类型
		self::$allow_ext = array_merge(array_keys(self::$image_app),array_keys(self::$video_app));
		if(is_array($allow_ext) && count($allow_ext)>0) {
			self::$allow_ext = $allow_ext;
		}
		//设置允许的上传文件最大值
		if($max_size > 0) {
			self::$max_size = $max_size;
		} else {
			self::$max_size = 50 * 1024 * 1024;
		}
		//设置预览目录
		self::$preview_path = 'upload/preview/';
		if($preview_path != '') {
			self::$preview_path = $this->format_path($preview_path);
		}
		$this->create_dir(self::$preview_path);
		//设置最终存放目录
		self::$final_path = 'upload/final/';
		if($final_path != '') {
			self::$final_path = $this->format_path($final_path);
		}
		$this->create_dir(self::$final_path);
	}

	/**
	 * 获取预览地址
	 *
	 * @return Array
	 */
	public function get_preview_src() {
		//判断文件扩展名和类型是否正确
		$this->check_file_ext();
		//判断文件大小
		if(self::$result['sta']) {
		    $this->check_file_size();
		}
		//如果文件为图片，判断其宽高
		if(self::$result['sta'] && self::$file_type == 1) {
		    $this->check_image_wh();
		}		

		//把临时文件上传到预览目录并重命名
		if(self::$result['sta']) {
		    $preview_name = 'pre_'.time().mt_rand(100000,999999).'.'.self::$ext;
		    $preview_src = self::$preview_path.$preview_name;
		    if(!move_uploaded_file(self::$post_file['tmp_name'],$preview_src)) {
		        self::$result['sta'] = FALSE;
		        self::$result['msg'] = $this->get_error_msg(0);
		    } else {
		        self::$result['preview_src'] = $preview_src;
		    }
		}
		
		return self::$result;
	}
	
	/**
	 * 把预览文件移动到最终存放地址
	 *
	 * @param String $preview_src
	 * @return Array
	 */
	public static function move_preview($preview_src) {
	    //检查预览文件
	    if(!empty($preview_src)) {
	        if(!is_file($preview_src)) {
	            self::$result['sta'] = FALSE;
	            self::$result['msg'] = $this->get_error_msg(8);
	        }
	    } else {
	        self::$result['sta'] = FALSE;
	        self::$result['msg'] = $this->get_error_msg(1);
	    }	    
	    if(self::$result['sta']) {
	        //生成最终文件名
	        $final_name = 'final_'.md5(time().mt_rand(100000,999999)).'.'.self::$ext;
	        //最终文件全路径
	        $final_src = self::$final_path.$final_name;
	        //如果文件已存在，则删除
	        if(is_file($final_src)) {
	            unlink($final_src);
	        }
	        //移动文件
	        $res = rename($preview_src,$final_src);
	        if($res) {
	            self::$result['final_src'] = $final_src;
	        } else {
	            self::$result['sta'] = FALSE;
	            self::$result['msg'] = $this->get_error_msg(0);
	        }
	    }
	    return self::$result;
	}
	
	/**
	 * 判断图片文件扩展名和类型是否正确
	 *
	 */
	protected function check_file_ext() {
	
	    if (!empty(self::$ext)) {
	        //判断文件扩展名
	        if (!in_array(self::$ext, self::$allow_ext)) {
	            self::$result['sta'] = FALSE;
	            self::$result['msg'] = $this->get_error_msg(2);
	        }
	
	        //判断文件类型
	        if(self::$file_type == 1) {//如果文件为图片
	            if (!in_array(self::$post_file['type'], self::$image_app[self::$ext])) {
	                self::$result['sta'] = FALSE;
	                self::$result['msg'] = $this->get_error_msg(3);
	            }
	        }
	        if(self::$file_type == 2) {//如果文件为视频或音频
	            if (!in_array(self::$post_file['type'], self::$video_app[self::$ext])) {
	                self::$result['sta'] = FALSE;
	                self::$result['msg'] = $this->get_error_msg(3);
	            }
	        }
	        	
	    }
	
	}
	
	/**
	 * 判断文件大小
	 *
	 */
	protected function check_file_size() {
	
	    if (self::$post_file['size'] > self::$max_size) {
	        self::$result['sta'] = FALSE;
	        self::$result['msg'] = $this->get_error_msg(4);
	    }
	}
	
	/**
	 * 如果文件为图片，判断其宽高
	 *
	 */
	protected function check_image_wh() {
	     
	    if($this->get_file_type() == 1) {
	        $imageInfo = getimagesize(self::$post_file['tmp_name']);
	        if($imageInfo[0]>self::$max_width || $imageInfo[1]>self::$max_hight) {
	            self::$result['sta'] = FALSE;
	            self::$result['msg'] = $this->get_error_msg(5);
	        }
	    }
	}

	/**
	 * 格式化目录
	 *
	 * @return String
	 */
	protected function format_path($path) {

		$path = str_replace('\\','/',$path);
		$path = rtrim($path,'/').'/';

		return $path;
	}

	/**
	 * 创建文件目录
	 *
	 */
	protected function create_dir($path) {

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
							self::$result['msg'] = $this->get_error_msg(7);
						}
					}
				}
			}
		}
	}

	/**
	 * 获取错误信息
	 *
	 * @param Int $type 错误信息类型
	 * @return String
	 */
	protected function get_error_msg($type) {
		$error_msg = '';
		switch ($type) {
			case 1:
				$error_msg = '文件未上传！';
				break;
			case 2:
				$error_msg = '文件扩展名不符合要求！';
				break;
			case 3:
				$error_msg = '文件类型不符合规范！';
				break;
			case 4:
				$error_msg = '文件大小超出限制！';
				break;
			case 5:
				$error_msg = '图片的宽高不符合要求！';
				break;
			case 6:
				$error_msg = '系统不支持此类型文件的上传！';
				break;
			case 7:
				$error_msg = '系统错误！';
			case 8:
			    $error_msg = '文件异常！';
			
			default:
				$error_msg = '文件上传失败！';
				break;
		}
		$error_msg .= '请仔细检查上传文件后，再重新上传！';
		return $error_msg;
	}
}