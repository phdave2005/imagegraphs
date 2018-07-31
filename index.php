<?php
$status = '1';

$chartType = $jsonStd = $jsonStdHighQ = $jsonTransparency = $jsonTransparencyHighQ = $jsonFirstDerivativeEstimate = $graphType = $legendString = $originalFilesize = $xUnit = $yUnit = '';
$resMax = $stdMax = 0;
$showAlpha = $showFD = $multiQuality = '0';

$fileIdentifier = 'ig'.md5(rand(0, 100000)).'_';

if (isset($_FILES['upload'])) {


	include './functions/graphingfunctions.php';
	
	if ($_FILES['upload']['size'] < 2048000) {
	
		if ($_FILES['upload']['type'] === 'image/jpeg' ||  $_FILES['upload']['type'] === 'image/png') {
			
			list($oldWidth, $oldHeight) = getimagesize($_FILES['upload']['tmp_name']);

			if ($_FILES['upload']['type'] === 'image/jpeg') {
				$ext = 'jpg';
				$create_function = 'imagecreatefromjpeg';
                $image_function  = 'imagejpeg';
				$quality_start = 0;
                $quality_cap   = 101;
			} else {
				$ext = 'png';
				$create_function = 'imagecreatefrompng';
                $image_function  = 'imagepng';
				$quality_start = 0;
				$quality_cap = 10;
			}
			
			$filename = './images/'.$fileIdentifier.'.'.$ext;
	
			$status = '2';
	
			$chartType = $_POST['charttype'];
			$graphType = $_POST['graphtype'];
			$originalFilesize = '<p id="StatGraphFilesize" style="padding-left:5%;font-weight:bold"><sup>*</sup> Uploaded Filesize = '.$_FILES['upload']['size'].' Bytes</p>';
			
			move_uploaded_file($_FILES['upload']['tmp_name'], $filename);
			
			if ($chartType[0] === 'F') {
				if ($chartType === 'Filesize_Vs_Quality') {
					$identifier = 'FvQ';
					$xUnit = ($ext === 'png') ? ' (Compression Factor)' : ' (%)';
				} else if ($chartType === 'Filesize_Vs_Rotation_Angle') {
					$identifier = 'FvRA';
					$xUnit = ' (&deg;)';
				} else if ($chartType === 'Filesize_Vs_Cropping') {
					$identifier = 'FvC';
					$xUnit = ' (% Width of Original)';
				} else {
					if ($chartType === 'Filesize_Vs_Resizing') {
						$identifier = 'FvR';
						$xUnit = ' (% Area of Original)';
					}
				}
				$yUnit = ' (Bytes)';
				list($jsonStd, $jsonStdHighQ, $jsonTransparency, $jsonTransparencyHighQ, $stdMax) = returnFilesizeJSON($identifier, $ext, $create_function, $image_function, $oldWidth, $oldHeight, $filename);
			} else if ($chartType[0] === 'T') {
				if ($chartType === 'Time_Vs_Quality') {
					$identifier = 'TvQ';
					$xUnit = ($ext === 'png') ? ' (Compression Factor)' : ' (%)';
				} else if ($chartType === 'Time_Vs_Rotation_Angle') {
					$identifier = 'TvRA';
					$xUnit = ' (&deg;)';
				} else if ($chartType === 'Time_Vs_Cropping') {
					$identifier = 'TvC';
					$xUnit = ' (% Width of Original)';
				} else {
					if ($chartType === 'Time_Vs_Resizing') {
						$identifier = 'TvR';
						$xUnit = ' (% Area of Original)';
					}
				}
				$yUnit = ' (Nanoseconds)';
				list($jsonStd, $jsonStdHighQ, $jsonTransparency, $jsonTransparencyHighQ, $stdMax) = returnTimeJSON($identifier, $ext, $create_function, $image_function, $oldWidth, $oldHeight, $filename);
			} else {
				;
			}
			
			
			
			if (strpos($_POST['addons'], "fde") !== false) {
				//now loop through the $json Array and calculate derivative
				$showFD = '1';
				$jsonFDE = returnFDE($jsonStd);
				$jsonFirstDerivativeEstimate = json_encode($jsonFDE);
			}
			if (strpos($_POST['addons'], "mq") !== false) $multiQuality = '1';
	
		} else {
			
			$status = '4';
			
		}
	
	} else {
		
		$status = '3';
		
	}
}
?>
<!DOCTYPE html><html style="overflow-x:hidden" xmlns="http://www.w3.org/1999/xhtml">
<head><meta http-equiv="Content-Type" content="text/html;charset=utf-8">
<title>Test Boost</title>
<meta name="description" content="Image Graphs">
<meta name="keywords" content="Image, Analysis">
<meta name="author" content="Dr. David Partyka">
<head>
<link href="css/tipTip.css" rel="stylesheet" type="text/css" media="all">
<link rel="stylesheet" type="text/css" href="css/jquery.jqplot.css">
<link rel="stylesheet" type="text/css" href="css/default.css">
</head>
<body style="overflow-y:scroll">
<div id="header"><span id="reset" style="color:white;cursor:pointer">Reset</span></div>
<div id="StatGraphContainer" style="height:500px"></div>
<?php echo $originalFilesize ?>
<div id="FirstDerivativeGraphContainer" style="height:500px"></div>
<form id="upload-form" method="post" action="#" enctype="multipart/form-data">
    <input id="upload" name="upload" type="file" onchange="uploadFile(event)">
	<input id="charttype" name="charttype" type="hidden" value="Filesize_Vs_Quality">
	<input id="graphtype" name="graphtype" type="hidden" value="line">
	<!-- <input id="fvc_amt" name="fvc_amt" type="hidden" value="50">
	<input id="fvra_amt" name="fvra_amt" type="hidden" value="5">
	<input id="fvr_amt" name="fvr_amt" type="hidden" value="50">
	<input id="tvc_amt" name="tvc_amt" type="hidden" value="50">
	<input id="tvr_amt" name="tvra_amt" type="hidden" value="50">
	<input id="tvra_amt" name="tvra_amt" type="hidden" value="5">
	<input id="api_get_resize_amt" name="api_get_resize_amt" type="hidden" value="500">
	<input id="api_get_quality_amt" name="api_get_quality_amt" type="hidden" value="250">
	-->
	<input id="amt" name="amt" type="hidden" value="50">
	<input id="addons" name="addons" type="hidden">
</form>
<div id="AlertDiv">
	<div id="AlertIconHolder"></div>
	<div><p id="Alertp"></p>
		<button type="button" onclick="HideDiv('Alert');rubberBand(this)">OK</button>
	</div>
</div>
<div id="PageCover"></div>
<div id="radiobutton-div">
	<h2>Select Your Options Below</h2>
	<div id="tab-holder">
		<div id="filesize_tab" style="margin-left:0.5%">Filesize Functions</div><div id="time_tab">Time Functions</div><div id="api_tab">API</div>
	</div>
	<br>
	<div id="content-div" style="border-top:1px solid #CDCDCD;clear:both">
		<h3>Chart Type</h3>
		<ul id="filesize_ul">
			<li id="fvc_range_li">
				<input id="Filesize_Vs_Cropping" type="radio" name="filesizechartchoices" class="chartchoices">Filesize vs Cropping (jpg/png)
				<br>
				<i id="fvc_range_ileft">Fastest Processing</i><input id="fvc_range" name="fvc_range" class="nonapirange" type="range" min="3" default="50" max="100" disabled><i id="fvc_range_iright">Best Resolution</i>
			</li>
			<li id="fvr_range_li">
				<input id="Filesize_Vs_Resizing" type="radio" name="filesizechartchoices" class="chartchoices">Filesize vs Resizing (jpg/png)
				<br>
				<i id="fvr_range_ileft">Fastest Processing</i><input id="fvr_range" name="fvr_range" class="nonapirange" type="range" min="3" default="50" max="100" disabled><i id="fvr_range_iright">Best Resolution</i>
			</li>
			<li id="fvra_range_li">
				<input id="Filesize_Vs_Rotation_Angle" type="radio" name="filesizechartchoices" class="chartchoices">Filesize vs Rotation Angle (jpg/png)
				<br>
				<i id="fvra_range_ileft">Fastest Processing</i><input id="fvra_range" name="fvra_range" class="nonapirange" type="range" min="3" default="50" max="90" disabled><i id="fvra_range_iright">Best Resolution</i>
			</li>
			<li>
				<input id="Filesize_Vs_Quality" type="radio" name="filesizechartchoices" class="chartchoices" checked>Filesize vs Quality (jpg/png)
			</li>
		</ul>
		<ul id="time_ul">
			<li id="tvc_range_li">
				<input id="Time_Vs_Cropping" type="radio" name="timechartchoices" class="chartchoices">Time vs Cropping (jpg/png)
				<br>
				<i id="tvc_range_ileft">Fastest Processing</i><input id="tvc_range" name="tvc_range" class="nonapirange" type="range" min="3" default="50" max="100" disabled><i id="tvc_range_iright">Best Resolution</i>
			</li>
			<li id="tvr_range_li">
				<input id="Time_Vs_Resizing" type="radio" name="timechartchoices" class="chartchoices">Time vs Resizing (jpg/png)
				<br>
				<i id="tvr_range_ileft">Fastest Processing</i><input id="tvr_range" name="tvr_range" class="nonapirange" type="range" min="3" default="50" max="100" disabled><i id="tvr_range_iright">Best Resolution</i>
			</li>
			<li id="tvra_range_li">
				<input id="Time_Vs_Rotation_Angle" type="radio" name="timechartchoices" class="chartchoices">Time vs Rotation Angle (jpg/png)
				<br>
				<i id="tvra_range_ileft">Fastest Processing</i><input id="tvra_range" name="tvra_range" class="nonapirange" type="range" min="3" default="50" max="90" disabled><i id="tvra_range_iright">Best Resolution</i>
			</li>
			<li>
				<input id="Time_Vs_Quality" type="radio" name="timechartchoices" class="chartchoices" checked>Time vs Quality (jpg/png)
			</li>
		</ul>
		<h3>Graph Type</h3>
		<ul id="graphtype_ul">
			<li><input id="line" type="radio" name="graphchoices" class="graphchoices" checked>Line</li>
			<li><input id="bar" type="radio" name="graphchoices" class="graphchoices">Bar</li>
		</ul>
		<h3>Also Include:</h3>
		<ul id="also_ul">
			<li><input id="ab" type="checkbox" class="checks"><label class="labels">Plus Alpha Blending (image copy, png only)</label></li>
			<li id="mq_li"><input id="mq" type="checkbox" class="checks" disabled><label class="labels">Plus Low/High Quality Comparison</label></li>
			<li><input id="fde" type="checkbox" class="checks"><label class="labels">Plus First Derivative Plot (estimate)</label></li>
		</ul>
		<ul id="api_ul">
			<li>
				<input id="api_get_quality" type="radio" name="apichoices" class="apichoices" checked>Get Highest Quality Less Than Specified Filesize Pct.
				<br>
				<i id="api_get_quality_range_ileft" style="opacity:1">1% Original Filesize</i><input id="api_get_quality_range" class="apirange"  type="range" min="1" default="250" max="500"><i id="api_get_quality_range_iright"  style="opacity:1">500% Original Filesize</i>
			</li>
			<li>
				<input id="api_get_resize" type="radio" name="apichoices" class="apichoices">Enlarge Until Specified Filesize Pct.
				<br>
				<i id="api_get_resize_range_ileft">1% Original Filesize</i><input id="api_get_resize_range" class="apirange" type="range" min="1" default="500" max="1000" disabled><i id="api_get_resize_range_iright">1000% Original Filesize</i>
			</li>
		</ul>
	</div>
	<div id="button_holder">
		<button id="submit" type="button" onclick="clickUpload()">Submit</button>
	</div>
</div>
<script src="./js/jquery-3.1.0.min.js"></script>
<script type="text/javascript" src="js/jquery.jqplot.js"></script>
<script type="text/javascript" src="js/jqplot.barRenderer.js"></script>
<script type="text/javascript" src="js/jqplot.categoryAxisRenderer.js"></script>
<script type="text/javascript" src="js/jqplot.pointLabels.js"></script>
<script type="text/javascript" src="js/jqplot.pieRenderer.js"></script>
<script type="text/javascript">
	
	var thisStatus = '<?php echo $status ?>';

	function barGraph() {
		var jsonStd = JSON.parse('<?php echo $jsonStd ?>'),
			xArray = [], thisArray = [], i, xUnit, yUnit,
			chartType = '<?php echo $chartType ?>',
			splitCT = chartType.split("_Vs_"),
			yAxis = splitCT[0],
			xAxis = splitCT[1],
			showFD = ('<?php echo $showFD ?>' === '1') ? true : false,
			stdMax = Number('<?php echo $stdMax ?>'),
			xUnit = '<?php echo $xUnit ?>',
			yUnit = '<?php echo $yUnit ?>';
		
		for(i = 0; i < stdMax; i++) {
			thisArray.push(jsonStd[i]);
			xArray.push(i);
		}
		
		$("#StatGraphContainer").jqplot([thisArray], {
			animate: true,
			title: {
				fontFamily:'Helvetica',
				fontSize: '16pt',
				text: chartType.replace(/_/g, " ")
			},
			axes:{
				xaxis:{
					renderer: $.jqplot.CategoryAxisRenderer, ticks: xArray
				}
			},
			seriesColors:['#00FF00'],
			series:[{renderer:$.jqplot.BarRenderer}],
			seriesDefaults: {
								rendererOptions: {
									barWidth: (0.7 * screen.availWidth) / (stdMax - 1)
								}
							}
			}
		);
		
		if (showFD) lineGraphFDE(stdMax);
	}
	
	function lineGraph() {
		
		var json, allDataArray = [], allDataCount, domain = [], seriesArray = [], tempArray = [], i, xUnit, 
			chartType = '<?php echo $chartType ?>',
			splitCT = chartType.split("_Vs_"),
			yAxis = splitCT[0],
			xAxis = splitCT[1],
			isAlpha = ('<?php echo $showAlpha ?>'),
			showAlpha = (isAlpha === '1') ? true : false,
			showFD = ('<?php echo $showFD ?>' === '1') ? true : false,
			showMQ = ('<?php echo $multiQuality ?>' === '1') ? true : false,
			stdMax = Number('<?php echo $stdMax ?>'),
			legendString = '<?php echo $legendString ?>',
			legendArray = legendString.split(","),
			xUnit = '<?php echo $xUnit ?>',
			yUnit = '<?php echo $yUnit ?>';

		json = JSON.parse('<?php echo $jsonStd ?>');
		for(i = 0; i < stdMax; i++) {
			tempArray.push([i, json[i]]);
			domain.push(i);
		}
		allDataArray.push(tempArray);//feed function array of arrays
		
		if (showMQ) {
			json = JSON.parse('<?php echo $jsonStdHighQ ?>');
			tempArray = [];
			for(i = 0; i < stdMax; i++) {
				tempArray.push([i, json[i]]);
				domain.push(i);
			}
			allDataArray.push(tempArray);
		}
		
		if (showAlpha) {
			//transparency
			tempArray = [];
			json = JSON.parse('<?php echo $jsonTransparency ?>');
			for(i = 0; i < stdMax; i++) {
				tempArray.push([i, json[i]]);
			}
			allDataArray.push(tempArray);
			
			if (showMQ) {
				json = JSON.parse('<?php echo $jsonTransparencyHighQ ?>');
				tempArray = [];
				for(i = 0; i < stdMax; i++) {
					tempArray.push([i, json[i]]);
					domain.push(i);
				}
				allDataArray.push(tempArray);
			}
		}
		
		allDataCount = allDataArray.length;
		
		for(i = 0; i < allDataCount; i++) {
			seriesArray.push({
						lineWidth:2,
						markerOptions: {style:"circle", size:6}
					});
		}
		
		$("#StatGraphContainer").jqplot ('chart1', allDataArray, {
			animate: true,
			title: {
				fontFamily:'Helvetica',
				fontSize: '16pt',
				text: chartType.replace(/_/g, " ")
			},
			seriesDefaults: {
				rendererOptions: {
					smooth: true
				}
			},
			legend: {
				show: true,
				labels: legendArray,
				location:'nw'
			},
			axes: {
				xaxis: {
					label: xAxis + xUnit,
					labelRenderer: $.jqplot.CanvasAxisLabelRenderer,
					labelOptions:{
						fontFamily:'Helvetica',
						fontSize: '12pt'
					},
					ticks: domain,
					tickOptions: {
						formatString: "%d"
					}
				},
				yaxis: {
					labelRenderer: $.jqplot.CanvasAxisLabelRenderer,
					labelOptions:{
						fontFamily:'Helvetica',
						fontSize: '12pt'
					},
					label: yAxis + yUnit,
					min: 0
				}
			},
			series: seriesArray
		});
			
		if (showFD) lineGraphFDE(stdMax);
	}
	
	function lineGraphFDE(stdMax) {
		
		var jsonFDE = JSON.parse('<?php echo $jsonFirstDerivativeEstimate ?>'),
			i = 1, FDEArray = [], fdeMax = stdMax - 1, domain = [0, 1], seriesArray;
			
		//domain.push(0.5);
		//FDEArray.push([0.5, jsonFDE[0]]);
		for(i = 1; i < fdeMax; i++) {
			FDEArray.push([i, jsonFDE[i]]);
			domain.push(i);
		}
		domain.push(stdMax);//fdeMax + 1
			
		seriesArray = [{color: "red", lineWidth:2, markerOptions: {style:"circle", size:6}}];
		
		$("#FirstDerivativeGraphContainer").jqplot ('chart2', [FDEArray], {
			animate: true,
			title: {
				fontFamily: 'Helvetica',
				fontSize: '16pt',
				text: 'First Derivative Estimate'
			},
			axesDefaults: {
				labelRenderer: $.jqplot.CanvasAxisLabelRenderer
			},
			seriesDefaults: {
				rendererOptions: {
					smooth: true
				}
			},
			legend: {
				show: true,
				labels: ['First Derivative Estimate'],
				location: 'nw'
			},
			axes: {
				xaxis: {
					label: 'Change in X',
					labelRenderer: $.jqplot.CanvasAxisLabelRenderer,
					labelOptions:{
						fontFamily:'Helvetica',
						fontSize: '12pt'
					},
					ticks: domain,
					tickOptions: {
						formatString: "%d"
					}
				},
				yaxis: {
					labelRenderer: $.jqplot.CanvasAxisLabelRenderer,
					labelOptions:{
						fontFamily:'Helvetica',
						fontSize: '12pt'
					},
					label: 'Change in Y',
				},
				series: seriesArray
			}
		});
	}
	
	function clickUpload() {
		document.getElementById("upload").click();
	}

	function uploadFile(event) {
		var e = document.getElementById("upload"),
		filesize = event.target.files[0].size;
        if (!e.value || filesize === 0) {
			//AlertConFunction('Alert', 'The file is empty', scr, 'r');
			alert('The file is empty');
		} else {
            if (filesize > 2048000) {
				//AlertConFunction('Alert', 'Please limit files to less than 2048000 bytes in size', scr, 'r');
				alert('Please limit files to less than 2048000 bytes in size');
            } else if (!e.value.match(/^.*\.(jpg|jpeg|png)$/)) {
				//AlertConFunction('Alert', 'Only files of type .jpg, .jpeg, and .png are allowed', scr, 'r');
				alert('Only files of type .jpg, .jpeg, and .png are allowed');
            } else {
                document.getElementById("upload-form").submit();
			}
		}
	}

	$(document).ready(function () {
		
		document.getElementById("radiobutton-div").style.display = 'block';
		
		if (thisStatus !== '1') {
			document.getElementById("reset").style.display = 'inline';
			if (thisStatus === '3') {
				alert('File exceeded maximum file size');
			} else if (thisStatus === '4') {
				alert('The file you uploaded is neither jpg nor png');
			} else {
				
				$.ajax("./functions/delete.php", {
                                type: "GET",
                                data : {fileID: '<?php echo $fileIdentifier ?>'},
                                done: function(data) {
                                                            
										}
				});
				
				var graphType = '<?php echo $graphType ?>';
				document.getElementById("radiobutton-div").style.display = 'none';
				
				if (graphType === 'bar') {
					barGraph();
				} else {
					lineGraph();
				}
			}
		}
		
		if (thisStatus !== '2') {
			document.getElementById("PageCover").style.display = 'block';
		} else {
			document.getElementById("PageCover").style.display = 'none';
		}
		
		$(".graphchoices").click(function () {
			document.getElementById("graphtype").value = $(this).attr("id");
		});
		
		$(".apichoices").click(function () {
			var t 	   = $(this),
				thisID = t.attr("id");
			
			document.getElementById("charttype").value = thisID;
				
			if (thisID === 'api_get_quality') {
				$("#api_get_quality_range, #api_get_quality_range_ileft, #api_get_quality_range_iright").css({"opacity" : "1"}).filter("#api_get_quality_range").removeAttr("disabled");
				$("#api_get_resize_range, #api_get_resize_range_ileft, #api_get_resize_range_iright").css({"opacity" : "0.5"}).filter("#api_get_resize_range").attr("disabled", true);
			} else {
				$("#api_get_resize_range, #api_get_resize_range_ileft, #api_get_resize_range_iright").css({"opacity" : "1"}).filter("#api_get_resize_range").removeAttr("disabled");
				$("#api_get_quality_range, #api_get_quality_range_ileft, #api_get_quality_range_iright").css({"opacity" : "0.5"}).filter("#api_get_quality_range").attr("disabled", true);
			}
		});
		$(".chartchoices").click(function () {
			var t 	   = $(this),
				thisID = t.attr("id");
			
			document.getElementById("charttype").value = thisID;
				
			if (thisID === 'Filesize_Vs_Rotation_Angle') {
				$("#fvra_range, #fvra_range_ileft, #fvra_range_iright").css({"opacity" : "1"}).filter("#fvra_range").removeAttr("disabled");
				$("#fvc_range, #fvc_range_ileft, #fvc_range_iright, #fvr_range, #fvr_range_ileft, #fvr_range_iright").css({"opacity" : "0.5"}).filter("#fvc_range, #fvr_range").attr("disabled", true);
				$("#mq_li").css({"opacity" : "1"}).find("#mq").removeAttr("disabled");
			} else if (thisID === 'Filesize_Vs_Cropping') {
				$("#fvc_range, #fvc_range_ileft, #fvc_range_iright").css({"opacity" : "1"}).filter("#fvc_range").removeAttr("disabled");
				$("#fvr_range, #fvr_range_ileft, #fvr_range_iright, #fvra_range, #fvra_range_ileft, #fvra_range_iright").css({"opacity" : "0.5"}).filter("#fvr_range, #fvra_range").attr("disabled", true);
				$("#mq_li").css({"opacity" : "1"}).find("#mq").removeAttr("disabled");
			} else if (thisID === 'Filesize_Vs_Resizing') {
				$("#fvr_range, #fvr_range_ileft, #fvr_range_iright").css({"opacity" : "1"}).filter("#fvr_range").removeAttr("disabled");
				$("#fvc_range, #fvc_range_ileft, #fvc_range_iright, #fvra_range, #fvra_range_ileft, #fvra_range_iright").css({"opacity" : "0.5"}).filter("#fvc_range, #fvra_range").attr("disabled", true);
				$("#mq_li").css({"opacity" : "1"}).find("#mq").removeAttr("disabled");
			} else if (thisID === 'Filesize_Vs_Quality') {
				$("#fvc_range, #fvc_range_ileft, #fvc_range_iright, #fvr_range, #fvr_range_ileft, #fvr_range_iright, #fvra_range, #fvra_range_ileft, #fvra_range_iright").css({"opacity" : "0.5"}).filter("#fvc_range, #fvr_range, #fvra_range").attr("disabled", true);
				$("#mq_li").css({"opacity" : "0.5"}).find("#mq").attr("disabled", true);
			} else if (thisID === 'Time_Vs_Rotation_Angle') {
				$("#tvra_range, #tvra_range_ileft, #tvra_range_iright").css({"opacity" : "1"}).filter("#tvra_range").removeAttr("disabled");
				$("#tvc_range, #tvc_range_ileft, #tvc_range_iright, #tvr_range, #tvr_range_ileft, #tvr_range_iright").css({"opacity" : "0.5"}).filter("#tvc_range, #tvr_range").attr("disabled", true);
				$("#mq_li").css({"opacity" : "1"}).find("#mq").removeAttr("disabled");
			} else if (thisID === 'Time_Vs_Cropping') {
				$("#tvc_range, #tvc_range_ileft, #tvc_range_iright").css({"opacity" : "1"}).filter("#tvc_range").removeAttr("disabled");
				$("#tvr_range, #tvr_range_ileft, #tvr_range_iright, #tvra_range, #tvra_range_ileft, #tvra_range_iright").css({"opacity" : "0.5"}).filter("#tvr_range, #tvra_range").attr("disabled", true);
				$("#mq_li").css({"opacity" : "1"}).find("#mq").removeAttr("disabled");
			} else if (thisID === 'Time_Vs_Resizing') {
				$("#tvr_range, #tvr_range_ileft, #tvr_range_iright").css({"opacity" : "1"}).filter("#tvr_range").removeAttr("disabled");
				$("#tvc_range, #tvc_range_ileft, #tvc_range_iright, #tvra_range, #tvra_range_ileft, #tvra_range_iright").css({"opacity" : "0.5"}).filter("#tvc_range, #tvra_range").attr("disabled", true);
				$("#mq_li").css({"opacity" : "1"}).find("#mq").removeAttr("disabled");
			} else {//if (thisID === 'Time_Vs_Quality') {
				$("#tvc_range, #tvc_range_ileft, #tvc_range_iright, #tvr_range, #tvr_range_ileft, #tvr_range_iright, #tvra_range, #tvra_range_ileft, #tvra_range_iright").css({"opacity" : "0.5"}).filter("#tvc_range, #tvr_range, #tvra_range").attr("disabled", true);
				$("#mq_li").css({"opacity" : "0.5"}).find("#mq").attr("disabled", true);
			}
		});
		$(".checks").click(function () {
			var t = $(this),
				thisID = $(this).attr("id"),
				d = document.getElementById("addons"),
				currentVal = d.value.trim();
					
			if (t.is(":checked")) {
				d.value = currentVal + (currentVal ? "," : "") + thisID;
			} else {
				if (currentVal.indexOf("," + thisID) > -1) {
					currentVal = currentVal.replace("," + thisID, "");
				} else if (currentVal.indexOf(thisID + ",") > -1) {
					currentVal = currentVal.replace(thisID + ",", "");
				} else {
					if (currentVal.indexOf(thisID) > -1) currentVal = currentVal.replace(thisID, "");
				}
				d.value = currentVal;
			}
		});
		
		$("input[type='range']").change(function () {
			var t = $(this),
				thisID = t.attr("id"),
				thisVal = Number(t.val());
				
			//document.getElementById(thisID.replace("range", "amt")).value = thisVal;
			
			document.getElementById("amt").value = thisVal;
			
			if (thisVal === Number(t.attr("min"))) {
				$("#" + thisID + "_ileft").css({"color" : "green"});
				$("#" + thisID + "_iright").css({"color" : "black"});
			} else {
				$("#" + thisID + "_ileft").css({"color" : "black"});
				if (thisVal === Number(t.attr("max"))) {
					$("#" + thisID + "_iright").css({"color" : "green"});
				} else {
					$("#" + thisID + "_iright").css({"color" : "black"});
				}
			}
		});
		
		$("#reset").click(function () {
			document.getElementById("PageCover").style.display = 'block';
			document.getElementById("radiobutton-div").style.display = 'block';
			document.getElementById("StatGraphContainer").style.display = 'none';
		});
		
		$("#tab-holder > div").click(function () {
			var t = $(this),
				thisID = t.attr("id"),
				e = $("#" + thisID.replace("tab", "ul")),
				el = $("#tab-holder > div");
			
			el.css({"background-color" : "#fff", "color" : "#000"}).removeClass("active");
			t.css({"background-color" : "#CDCDCD", "color" : "#fff"}).addClass("active");
			
			el = $("#content-div");
			if (thisID !== 'api_tab') {
				document.getElementById((thisID === 'time_tab' ? "Time_Vs_Quality" : "Filesize_Vs_Quality")).click();
				el.find("#graphtype_ul, #also_ul, h3").fadeIn(1);
				document.getElementById("amt").value = (thisID === 'filesize_tab') ? 50 : 500;
			} else {
				document.getElementById("api_get_quality").click();
				el.find("#graphtype_ul, #also_ul, h3").fadeOut(1);
				document.getElementById("amt").value = 50;
			}
			el.find("#api_ul, #filesize_ul, #time_ul").fadeOut(1, function () {
				setTimeout(function () {e.fadeIn(700);}, 50);
			});
		});
		
	});
</script>
</body>
</html>

==============================
<?php

function returnFilesizeJSON($identifier, $ext, $create_function, $image_function, $oldWidth, $oldHeight, $filename) {
	global $legendString, $fileIdentifier;
		
	$isPNG = ($ext === 'png') ? true : false;
	$lowQ = $isPNG ? 9 : 0;
	
	$jsonStd = $jsonStdHighQ = $jsonTransparency = $jsonTransparencyHighQ = '';//initialize
	
	$qualityArray = array($lowQ);
	
	if (strpos($_POST['addons'], "mq") !== false) {
		$highQ = $isPNG ? 0 : 100;
		$qualityArray[] = $highQ;
		$outerLoopMax = 2;
	} else {
		$outerLoopMax = 1;
	}
	
	$crop = $resize = $rotate = false;
	$qualityFactor = (int)$_POST['amt'];
	if ($identifier === 'FvC') {
		$thisMax = 101;
		$crop = true;
	} else if ($identifier === 'FvQ') {
		$thisMax = $isPNG ? 10 : 101;
		$qualityFactor = $thisMax - 1;
	} else if ($identifier === 'FvR') {
		$thisMax = 101;
		$resize = true;
	} else {
		if ($identifier === 'FvRA') {
			$thisMax = 91;
			$rotate = true;
		}
	}
	$minusOne = $thisMax - 1;
	$qualityIncrement = ($minusOne / $qualityFactor);
		
	if ($identifier === 'FvQ') {
		$image = $create_function($filename);
		for($i = 0; $i < $thisMax; $i+=$qualityIncrement) {
			$thisFilename = './images/'.$fileIdentifier.$i.'.'.$ext;
			$image_function($image, $thisFilename, $i);//output to file 
			$tempArray[$i] = filesize($thisFilename);
		}
		$legendString = 'Without Resampling';
		$jsonStd = json_encode($tempArray);
	} else {
		if ($crop || $resize) {
			$innerLoopStart = 1;
			$tempArray[0] = 0;
		} else {
			$innerLoopStart = 0;
		}
		$image = $create_function($filename);
		for($i = 0; $i < $outerLoopMax; $i++) {
			$tempArray = array();
			for($j = $innerLoopStart; $j < $thisMax; $j+=$qualityIncrement) {
				if ($rotate) {
					$degrees = $j;
					$image_t = imagerotate($image, $degrees, 0);
				} else {
					$image_t = $image;
				}
				if ($resize) {
					$thisHeight = sqrt($j * 0.01) * $oldHeight;
					$thisWidth = sqrt($j * 0.01) * $oldWidth;
					$image_x = imagecreatetruecolor($thisWidth, $thisHeight);
					imagecopyresampled($image_x, $image_t, 0, 0, 0, 0, $thisWidth, $thisHeight, $oldWidth, $oldHeight);
				} else if ($crop) {
					$thisWidth = (($j / 100) * $oldWidth);
					$image_x = imagecreatetruecolor($thisWidth, $oldHeight);
					imagecopyresampled($image_x, $image_t, 0, 0, 0, 0, $thisWidth, $oldHeight, $oldWidth, $oldHeight);
				} else {
					$thisHeight = $oldHeight;
					$thisWidth = $oldWidth;
					$image_x = $image_t;
				}
				$thisFilename = './images/'.$fileIdentifier.$i.$j.'.'.$ext;
				$image_function($image_x, $thisFilename, $qualityArray[$i]);//output to file 
				$tempArray[$j] = filesize($thisFilename);
			}
			if ($i === 0) {
				$legendString = 'With Resampling: Low Quality';
				$jsonStd = json_encode($tempArray);
			} else {
				$legendString .= ',With Resampling: High Quality';
				$jsonStdHighQ = json_encode($tempArray);
			}
		}
	}
		
	$tempArray = array();
	if ($isPNG && strpos($_POST['addons'], "ab") !== false) {
		global $showAlpha;
		$showAlpha = '1';
		if ($identifier === 'FvQ') {
			$image = $create_function($filename);
			imagealphablending($image, false);
			imagesavealpha($image, true);
			for($i = 0; $i < $thisMax; $i+=$qualityIncrement) {			
				$thisFilename = './images/'.$fileIdentifier.$i.'ab.'.$ext;
				$image_function($image, $thisFilename, $i);//output to file 
				$tempArray[$i] = filesize($thisFilename);
			}
		} else {
			if ($crop || $resize) {
				$loopStart = 1;
				$tempArray[0] = 0;
			} else {
				$loopStart = 0;
			}
			$image = $create_function($filename);
			for($i = 0; $i < $outerLoopMax; $i++) {
				$tempArray = array();
				for($j = $innerLoopStart; $j < $thisMax; $j+=$qualityIncrement) {
					if ($rotate) {
						$degrees = $j;
						$image_t = imagerotate($image, $degrees, 0);
					} else {
						$image_t = $image;
					}
					if ($resize) {
						$thisHeight = sqrt($j * 0.01) * $oldHeight;//(($i / 100) * $oldHeight);
						$thisWidth = sqrt($j * 0.01) * $oldWidth;//(($i / 100) * $oldWidth);
						$image_x = imagecreatetruecolor($thisWidth, $thisHeight);
						imagecopyresampled($image_x, $image_t, 0, 0, 0, 0, $thisWidth, $thisHeight, $oldWidth, $oldHeight);
					} else if ($crop) {
						$thisWidth = (($j / 100) * $oldWidth);
						$image_x = imagecreatetruecolor($thisWidth, $oldHeight);
						imagecopyresampled($image_x, $image_t, 0, 0, 0, 0, $thisWidth, $oldHeight, $oldWidth, $oldHeight);
					} else {
						$thisHeight = $oldHeight;
						$thisWidth = $oldWidth;
						$image_x = $image_t;
					}
					imagealphablending($image_x, false);
					imagesavealpha($image_x, true);
					$thisFilename = './images/'.$fileIdentifier.$i.$j.'ab.'.$ext;
					$image_function($image_x, $thisFilename, $qualityArray[$i]);//output to file		
					$tempArray[$j] = filesize($thisFilename);
				}
				if ($i === 0) {
					$legendString .= ',With Alpha Blending: Low Quality';
					$jsonTransparency = json_encode($tempArray);
				} else {
					$legendString .= ',With Alpha Blending: High Quality';
					$jsonTransparencyHighQ = json_encode($tempArray);
				}
			}
		}
	}
			
	return array($jsonStd, $jsonStdHighQ, $jsonTransparency, $jsonTransparencyHighQ, $thisMax);
}

function returnTimeJSON($identifier, $ext, $create_function, $image_function, $oldWidth, $oldHeight, $filename) {
	global $legendString, $fileIdentifier;
		
	$isPNG = ($ext === 'png') ? true : false;
	$lowQ = $isPNG ? 9 : 0;
	
	$jsonStd = $jsonStdHighQ = $jsonTransparency = $jsonTransparencyHighQ = '';//initialize
	
	$qualityArray = array($lowQ);
	
	if (strpos($_POST['addons'], "mq") !== false) {
		$highQ = $isPNG ? 0 : 100;
		$qualityArray[] = $highQ;
		$outerLoopMax = 2;
	} else {
		$outerLoopMax = 1;
	}
	
	$crop = $resize = $rotate = false;
	$qualityFactor = (int)$_POST['amt'];
	if ($identifier === 'TvC') {
		$thisMax = 101;
		$crop = true;
		$endCount = $isPNG ? 5 : 9;//passed hq EmploymentHistory/testPNG no extras, upper limit (10 failed for jpeg, 6 failed for png)
	} else if ($identifier === 'TvQ') {
		$thisMax = $isPNG ? 10 : 101;
		$qualityFactor = $thisMax - 1;
		$endCount = 10;
	} else if ($identifier === 'TvR') {
		$thisMax = 101;
		$resize = true;
		$endCount = $isPNG ? 1 : 6;//passed hq EmploymentHistory/testPNG no extras, upper limit (7 failed for jpeg,  2 failed for png)
	} else {
		if ($identifier === 'TvRA') {
			$thisMax = 91;
			$rotate = true;
			$endCount = $isPNG ? 1 : 12;//passed hq EmploymentHistory no extras, upper limit (13 failed for jpeg, 2 failed for png)
		}
	}
	$labelAppend = '- Average of '.$endCount.' trials per data point';
	$minusOne = $thisMax - 1;
	$qualityIncrement = ($minusOne / $qualityFactor);
		
	if ($identifier === 'TvQ') {
		$image = $create_function($filename);
		for($i = 0; $i < $thisMax; $i+=$qualityIncrement) {
			$timeSum = 0;
			for($j = 0; $j < $endCount; $j++) {
				$thisFilename = './images/'.$fileIdentifier.$i.$j.'.'.$ext;
				$time1 = microtime(true);
				$image_function($image, $thisFilename, $i);//output to file 
				$time2 = microtime(true);
				$timeSum += $time2 - $time1;
			}
			$tempArray[$i] = $timeSum / $endCount;
		}
		$legendString = 'Without Resampling'.$labelAppend;
		$jsonStd = json_encode($tempArray);
	} else {
		if ($crop || $resize) {
			$innerLoopStart = 1;
			$tempArray[0] = 0;
		} else {
			$innerLoopStart = 0;
		}
		$image = $create_function($filename);
		for($i = 0; $i < $outerLoopMax; $i++) {
			$tempArray = array();
			for($j = $innerLoopStart; $j < $thisMax; $j+=$qualityIncrement) {
				$timeSum = 0;
				for($k = 0; $k < $endCount; $k++) {
					$time1 = microtime(true);
					if ($rotate) {
						$degrees = $j;
						$image_t = imagerotate($image, $degrees, 0);
					} else {
						$image_t = $image;
					}
					if ($resize) {
						$thisHeight = sqrt($j * 0.01) * $oldHeight;
						$thisWidth = sqrt($j * 0.01) * $oldWidth;
						$image_x = imagecreatetruecolor($thisWidth, $thisHeight);
						imagecopyresampled($image_x, $image_t, 0, 0, 0, 0, $thisWidth, $thisHeight, $oldWidth, $oldHeight);
					} else if ($crop) {
						$thisWidth = (($j / 100) * $oldWidth);
						$image_x = imagecreatetruecolor($thisWidth, $oldHeight);
						imagecopyresampled($image_x, $image_t, 0, 0, 0, 0, $thisWidth, $oldHeight, $oldWidth, $oldHeight);
					} else {
						$thisHeight = $oldHeight;
						$thisWidth = $oldWidth;
						$image_x = $image_t;
					}
					$thisFilename = './images/'.$fileIdentifier.$i.$j.$k.'.'.$ext;
					$image_function($image_x, $thisFilename, $qualityArray[$i]);//output to file 
					$time2 = microtime(true);
					$timeSum += $time2 - $time1;
				}
				$tempArray[$j] = (1000 * $timeSum) / $endCount;
			}
			if ($i === 0) {
				$legendString = 'With Resampling: Low Quality'.$labelAppend;
				$jsonStd = json_encode($tempArray);
			} else {
				$legendString .= ',With Resampling: High Quality'.$labelAppend;
				$jsonStdHighQ = json_encode($tempArray);
			}
		}
	}
			
	return array($jsonStd, $jsonStdHighQ, $jsonTransparency, $jsonTransparencyHighQ, $thisMax);
}

function returnFDE($json) {
	$decodedStd = json_decode($json, 1);
	$dsCount = count($decodedStd);
	$thisCount = $dsCount - 1;//FDE can't be calculated for endpoints
	$dsValues = array_values($decodedStd);
				
	$mapArray = array();
	$i = 0;
	foreach($decodedStd as $key=>$value) {
		$mapArray[$i] = $key;
		$i++;
	}
				
	$jsonFDE[0.5] = ($dsValues[1] - $dsValues[0]) / ($mapArray[1] - $mapArray[0]);
				
	for($i = 1; $i < $thisCount; $i++) {
		$prev = $i - 1;
		$next = $i + 1;
		$jsonFDE[$mapArray[$i]] = ($dsValues[$next] - $dsValues[$prev]) / ($mapArray[$next] - $mapArray[$prev]);//($dsKeys[$next] - $dsKeys[$prev]); 
	}
		
	return $jsonFDE;
}

?>

====================
$filePrepend = $_GET['fileID'];
	
	foreach(glob('../images/'.$filePrepend.'*') as $file) {
		unlink($file);
	}


