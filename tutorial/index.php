<?php
require dirname(dirname(__FILE__)).'/lib.inc/vendor/autoload.php';

$file = dirname(dirname(__FILE__)).'/tutorial.md';
$mddata = file_get_contents($file);
$md = new Parsedown();
$html = $md->text($mddata);
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tutorial Universal REST Simulator</title>
    <style>
        body{
            margin: 0;
            padding: 0;
        }
        .all{
            width: 100%;
            max-width: 1000px;
            margin: auto;
            padding: 20px;
            box-sizing: border-box;
        }
        code{ white-space: pre ; }
        pre{
            display: block;
            border: 1px solid #DDDDDD;
            background-color: #F5F5F5;
            padding: 20px;
            overflow-x: auto;
        }
        a{
            color: #555555;
            text-decoration: none;
        }
        a:hover{
            text-decoration: underline;
        }
    </style>

</head>
<body>
    <div class="all">
    <?php
    echo $html;
    ?>
    </div>
    <script>
        var hdr;
        window.onload = function(){
            hdr = document.getElementsByTagName('h2');
            for(var i = 0; i<hdr.length; i++)
            {
                var txt = hdr[i].textContent;
                txt = txt.toLowerCase();
                txt = txt.split(' ').join('-');
                hdr[i].setAttribute('id', txt);
            }
        }       
    </script>
</body>
</html>