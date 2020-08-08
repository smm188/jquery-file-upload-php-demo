<?php
/**
 * 前台上传文件预览辅助插件类
 *
 */
class upload_plugin_one {
	/**
	 * 输出上传一个文件的前台代码
	 *
	 * @param String $accept_upload_ajax_url: 响应上传ajax的url地址
	 * @param String $file_type: image/video
	 * @param String $old_src: 已有的文件地址
	 * @return String $str
	 */
	public static function get_upload_one_file_html($accept_upload_ajax_url='',$file_type='',$old_src='') {	    
		//检查参数
		$accept_upload_ajax_url = $accept_upload_ajax_url=='' ? 'accept_all_type_one.php' : $accept_upload_ajax_url;
		$file_type = $file_type=='' ? 'image' : $file_type;
		//设置一个唯一标识
		$sign = time().mt_rand(100000,999999);
		//设置返回代码存放变量
		$str = "";
		$str .= '<script src="jquery.fileupload.js"></script>';
        $str .= '<script src="jquery.xdr-transport.js"></script>';
		//输入框
		$str .= "<input id='upload_file_$sign' type='file' name='upload_file_$sign' />";
		//上传进度显示
		$str .= "	<div id='upload_status_$sign' style='margin-top:10px;'></div>";
		//上传进度条
		$str .= "	<div id='upload_bar_$sign' style='margin-top:10px;margin-bottom:10px;height:10px;max-width:200px;background:green;width:0%;'></div>";
		//预览框
		$str .= "<div id='upload_preview_$sign'>";		
		//若数据库里有文件地址，则直接显示
	if(!empty($old_src)) {
	    if($file_type == 'image') {
	        $str .= "<img src='$old_src'/>";
	    }
	    if($file_type == 'video') {
	        $str .= "<embed src='$old_src' allowscriptaccess='always' allowfullscreen='true' wmode='opaque' width='200' height='200'></embed>";
	    }
	}
		$str .= "</div>";

		//下面是js代码:
		$str .= "<script type='text/javascript'>";
		$str .= "	$('#upload_file_$sign').fileupload({";
		$str .= "		url:'$accept_upload_ajax_url',";
		$str .= "		dataType:'json',";
		$str .= "		done:function(e,data){";
		//上传成功：
		$str .= "			if(data.result.sta){";
		$str .= "				$('#upload_preview_$sign').html(";
	if($file_type == 'image') {//图片预览
		$str .= "					'<img src=\"'+data.result.preview_src+'\"/>'";
	} elseif($file_type == 'video') {//视频或音频预览
		$str .= "					'<embed src=\"'+data.result.preview_src+'\" allowscriptaccess=\"always\" allowfullscreen=\"true\" wmode=\"opaque\" width=\"200\" height=\"200\"></embed>'";
	}
		$str .= "				);";
		$str .= "			} else {";
		//上传失败
		$str .= "				$('#upload_bar_$sign').css('width','0%');";
		$str .= "				$('#upload_status_$sign').html('<span style=\"color:red;\">'+data.result.msg+'</span>');";
		$str .= "			}";
		$str .= "		},";
		//上传进度
		$str .= "		progress: function (e, data) {";
        $str .= "			var progress = parseInt(data.loaded / data.total * 100, 10);";
        $str .= "			$('#upload_bar_$sign').css('width', progress + '%');";
        $str .= "				$('#upload_status_$sign').html('上传进度：'+progress+'%');";
  		$str .= "		}";
		$str .= "	});";
		$str .= "</script>";

		return $str;
	}
	
}