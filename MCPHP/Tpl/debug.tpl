<div class="mcclear"></div>
<div id="MCPHPtraceInfo">
	<div class="traceInfo">
		<div class="tracehead"><span class="hd left">MCPHP TRACE:</span><span class="close" onmouseover="this.style.background='#cccccc';this.style.color='#000'" onmouseout="this.style.background='transparent';this.style.color=''"  onclick="window.document.body.removeChild(document.getElementById('MCPHPtraceInfo'))">×</span><span class="right"><?php echo $mcphpVersion;?></span></div>
			<div class="tracecontent">
            <p class="title">[ 基本信息 ]</p>
			<p><span>运行用时:</span><?php echo $runTime;?> 秒</p>
			<p><span>内存使用:</span><?php echo $useMems;?></p>
			<p><span>文件加载:</span><?php echo $includedFileNum;?> 次</p>
			<p><span>Sql语句:</span><?php echo $sqlNum;?> 次</p>
			<p><span>缓存写入:</span><?php echo $GLOBALS['_cacheWrite'];?> 次</p>
			<p><span>缓存读取:</span><?php echo $GLOBALS['_cacheRead'];?> 次</p>
			<p><span>请求时间:</span><?php echo $reqTime;?></p>
			<p><span>当前页面:</span><?php echo $currentFile;?></p>
			<p><span>客 户 端:</span><?php echo $browser;?></p>
			<p class="title">[ 错误信息 ]</p>
			<div class="infoList">
				<?php echo $errorInfo;?>
			</div>
			<p class="title">[ 文件加载详情 ]</p>
			<div class="classList">
				<?php echo $includedFile;?>
			</div>
			<p class="title">[ SQL运行详情 ]</p>
			<div class="infoList">
				<?php echo $sqls;?>
			</div>	
		</div>
	</div>
</div>
<style>
	#MCPHPtraceInfo{ font-size:12px;font-family:Verdana,"Microsoft YaHei", Geneva, sans-serif;}
	.mcclear{clear:both}
	.orange{ color:#FF9900;}
	.red{ color:#FF0000;}.left{float:left;}.right{float:right;}
	.traceInfo{ width:70%; text-align:left;margin:100px auto;box-shadow:0px 0px 15px #cbcbcb; -moz-box-shadow:0px 0px 15px #cbcbcb; -ms-box-shadow:0px 0px 15px #cbcbcb; -o-box-shadow:0px 0px 15px #cbcbcb; -webkit-box-shadow:0px 0px 15px #cbcbcb; word-break:break-all; background: #fefefe; }
	.traceInfo .tracehead{ line-height:180%;  background: #e5e5e5; padding: 0 10px; border-bottom: 1px solid #c0c0c0; height: 25px; }
	.traceInfo .tracehead .hd{color: #F60; font-size:18px;}
	.traceInfo .tracehead span{ color:#777; font-size:12px; font-weight:normal;}
	.traceInfo .tracehead span.close{ border:1px solid #c0c0c0; cursor:pointer; height:12px; padding:2px 1px; width:16px; text-align:center; line-height:10px; overflow:hidden; margin-top:4px; margin-left:20px; float:right;}
	.traceInfo .tracecontent { padding: 10px; color: #666; }
	.traceInfo .tracecontent .classList,.traceInfo .tracecontent .infoList { border: 1px dashed #ccc; padding: 5px; margin: 5px 1em 5px 1em; }
	.traceInfo .tracecontent p { line-height: 150%; }
	.traceInfo .tracecontent p.title{ line-height: 180%;font-weight: bold;font-size:14px;color:#FF3300 }
	.traceInfo .tracecontent p span { margin-left:1em;font-weight: bold; text-align: left; display: inline-block; word-spacing:2px; margin-right: 5px; }
	.traceInfo ul ,.traceInfo ol{ margin-left:1em; color:#ccc;}
	.traceInfo ul strong.red{ color:#444;}
</style>