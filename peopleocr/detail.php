<?php
    include_once("./_config/config.php");

    if(empty($_GET['id'])){
        header("location:index.php");
    }else{
        $file = mysql_fetch_assoc(myquery(" select * from ".__table_prefix."filestatus where filSno = '".$_GET['id']."' "));
        $fields = array();
        $detail = array();
        $res = myquery(" select * from ".__table_prefix."filefields where filSno = '".$file['filSno']."' ");
        while($row = mysql_fetch_assoc($res)){
            $fields[$row['fidRow']][$row['fidCol']] = $row;
            $detail['row'] = ($row['fidRow'] > $detail['row'])?$row['fidRow']:$detail['row'];
            $detail['col'] = ($row['fidCol'] > $detail['col'])?$row['fidCol']:$detail['col'];
        }

        //整理 handsontable data
        $handsontable_data = array();
        for($x=1;$x<=$detail['row'];$x++){
            for($y=1;$y<=$detail['col'];$y++){
                $handsontable_data[$x-1][$y-1] = $fields[$x][$y]['fidValue'];
                $handsontable_info[$x-1][$y-1] = json_encode($fields[$x][$y]);
            }
        }
    }
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<?php include_once("./incl/setting.php");?>
<title>title</title>

<link rel="stylesheet" href="./js/handsontable/jquery.handsontable.full.css" />
<script type="text/javascript" src="./js/handsontable/jquery.handsontable.full.js"></script>
<script type="text/javascript" src="./js/json2.min.js"></script>

</head>

<body>

<div class="detail_table">
    <div id="excel"></div>
</div>
<div class="detail_preview">
    <div class="preview_box">
        <img src="<?=$file['filImage'];?>" class="img-rounded">
        <canvas id="img_canvas" width="400" height="300" ></canvas>
    </div>
</div>
<div class="detail_ocr">
    <canvas id="canvas" width="400" height="300" style="position: relative; left: 400"></canvas>
</div>




<script type="text/javascript">
var img_data;
var img_info = <?=$file['filCanvas']?>;
var handsontable_data = <?=json_encode($handsontable_data);?>;
var handsontable_info = <?=json_encode($handsontable_info);?>;
var win_w = 0;
var win_h = 0;
var prv_w = 400;
var prv_h = 300;

$(function(){
    win_w = $(window).width();
    win_h = $(window).height();

    img_data = new Image;
    img_data.src = '<?=$file['filImage'];?>';
    
    //設定 handsontable
    var handsontable_config = {
        data: handsontable_data,
        minRows: <?=$file['filRows'] - 1;?>,
        minCols: <?=$file['filCols'] - 1;?>,        
        contextMenu: true
    };
    //定義 handsontable 事件
    var hooks = Handsontable.PluginHooks.hooks;
    for (var hook in hooks) {
        if (hooks.hasOwnProperty(hook)) {
            handsontable_config[hook] = (function (hook) {
                var checked = '';
                if (hook === 'afterLoadData' || hook === 'afterChange' || hook === 'beforeChange' || hook === 'afterSelection' || hook === 'afterSelectionEnd') {
                    checked = 'checked';
                }
                return function () {
                    log_events(hook, arguments);
                }
            })(hook);
        }
    }
    //載入 handsontable
    $('#excel').handsontable(handsontable_config);
});
//handsontable 事件
function log_events(event, data) {
    if(event == "afterSelection"){
        show_field_img(data[0],data[1]);
    }
    if(event == "afterChange"){
        var senddata = {};
        senddata.data = data[0];
        //console.info(data); data 應該是多筆陣列
        $.ajax({
            url:'ajax.php',
            type:'POST', //POST GET
            dataType:'json', //html json jsonp script text
            data:{'act':'save_field', 'id':<?=$_GET['id'];?>, 'data':senddata},
            success:function(res){ //ajaxStart : beforeSend ajaxSend success ajaxSuccess error ajaxError complete ajaxComplete ; ajaxStop 
                //console.info(res);
            }
        });
    }
}
//圖檔處理
function show_field_img(x,y){
    var tar = $('#excel table tr:eq('+x+') td:eq('+y+')');
    //有的沒的
    var top = tar.offset().top;
    var left = tar.offset().left;
    var width = tar.width();
    var height = tar.height();
    var lefttop = img_info.cross_points[y][x];
    var rightdown = img_info.cross_points[y + 1][x + 1];
    //原始圖片大小
    var source_width = parseInt(rightdown[0] - lefttop[0]);
    var source_height = parseInt(rightdown[1] - lefttop[1]);
    //縮圖大小
    var target_width = (source_width > source_height) ? prv_w : Math.floor(prv_w * source_width / source_height);
    var target_height = (source_height > source_width) ? prv_w : Math.floor(prv_w * source_height / source_width);
    //縮圖位置
    var img_context = $('#img_canvas')[0].getContext('2d');
    img_context.clearRect(0, 0, prv_w, prv_h);
    img_context.beginPath();
    img_context.strokeStyle = 'red';
    img_context.rect(
            Math.floor(lefttop[0] * prv_w / img_info.width),
            Math.floor(lefttop[1] * prv_h / img_info.height),
            Math.floor((rightdown[0] - lefttop[0]) * prv_w / img_info.width),
            Math.floor((rightdown[1] - lefttop[1]) * prv_h / img_info.height)
            );
    img_context.stroke();
    //原始圖片
    $('#canvas')[0].getContext('2d').clearRect(0, 0, prv_w, prv_h);
    $('#canvas')[0].getContext('2d').drawImage(
        img_data,
        lefttop[0], // source_x
        lefttop[1], // source_y
        source_width, // source_width
        source_height, // source_height
        0, // target_x
        0, // target_y
        target_width,
        target_height
    );
    //設定位置
    $(".detail_ocr").css('top' , (top+height+30)+'px');
    $(".detail_ocr").css('left' , (left+width+30)+'px');
    $(".detail_preview").css('top' , (top+height+30+target_height)+'px');
    $(".detail_preview").css('left' , (left+width+30)+'px');
}
</script>
</body>
</html>