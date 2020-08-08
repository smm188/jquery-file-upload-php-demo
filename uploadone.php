<!DOCTYPE html>
<html>
<head>
	<title>jquery.fileupload.js使用测试</title>
	<script src="jquery-1.12.0.min.js"></script>
    <script src="jquery.ui.widget.js"></script>
    <script src="jquery.iframe-transport.js"></script>
    <script src="jquery.fileupload.js"></script>
    <script src="jquery.xdr-transport.js"></script>
    <style type="text/css">
		.bar {
			margin-top:10px;
			height:10px;
			max-width: 300px;
			background: green;
		}
    </style>
</head>
<body>
<!-- 
	jquery.fileupload.js 下载地址：http://plugins.jquery.com/blueimp-file-upload/
	jquery.fileupload.js api地址：https://github.com/blueimp/jQuery-File-Upload/wiki/API
	jquery.fileupload.js options地址：https://github.com/blueimp/jQuery-File-Upload/wiki/Options
-->
	<div style="margin-top:20px;">
		<span>请上传：</span>
		<span style='color:blue;'>(支持.jpg .jpeg .gif .png格式)</span>
	</div>
	<div style="margin-top:10px;">
		<form action="" name="" enctype="multipart/form-data">
			<input type="file" name="files" class="upinput" param1="xxx"/>
			<input type="hidden" name="param2" value="yyy" class="param2">
		</form>	
	</div>
	<!-- 上传进度条及状态： -->
	<div class="progress">
		<div class="bar" style="width: 0%;"></div>
		<div class="upstatus" style="margin-top:10px;"></div>
	</div>
	<!-- 预览框： -->	
	<div class="preview"></div>
</body>
</html>

<script type="text/javascript">
$(".upinput").fileupload({
	url:"upfileone.php",//文件上传地址，当然也可以直接写在input的data-url属性内
	dataType: "json", //如果不指定json类型，则传来的json字符串就需要解析jQuery.parseJSON(data.result);
	formData:function(form){//如果需要额外添加参数可以在这里添加
		return [{name:"param1",value:$(".upinput").attr("param1")},
				{name:"param2",value:$(".param2").val()}];
	},
	done:function(e,data){
	    //done方法就是上传完毕的回调函数，其他回调函数可以自行查看api
	    //注意data要和jquery的ajax的data参数区分，这个对象包含了整个请求信息
	    //返回的数据在data.result中，这里dataType中设置的返回的数据类型为json
	    console.log(data.result);
	    if(data.result.sta) {
	    	// 上传成功：
	    	$(".upstatus").html(data.result.msg);
	    	$(".preview").html("<img src="+data.result.previewSrc+">");
	    } else {
	    	// 上传失败：
	    	$(".progress .bar").css("width", "0%");
	    	$(".upstatus").html("<span style='color:red;'>"+data.result.msg+"</span>");
	    }
	    
	},
	progress: function (e, data) {//上传进度
        var progress = parseInt(data.loaded / data.total * 100, 10);
        $(".progress .bar").css("width", progress + "%");
        $(".upstatus").html("正在上传...");
  	}
});
</script>