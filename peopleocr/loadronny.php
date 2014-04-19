<?php
    include_once("./_config/config.php");
    $rep = returnpost($_POST);

    $ronny = file_get_contents("http://campaign-finance.g0v.ronny.tw/api/gettables");
    $ronny = mb_convert_encoding($ronny, "utf8", "gbk");
    echo $ronny;
    json_decode($ronny);


    





?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>title</title>
</head>

<body>
    <?php print_r($ronny); ?>
</body>
</html>