<?php
require dirname(dirname(__FILE__)).'/lib.inc/vendor/autoload.php';

$file = dirname(dirname(__FILE__)).'/tutorial.md';
$mddata = file_get_contents($file);
$md = new Parsedown();
$html = $md->text($mddata);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tutorial Universal REST Simulator</title>
    <style>
    body {
        margin: 0;
        padding: 0;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        font-size: 1em;
        color: #111111;
        line-height: 1.45;
        position: relative;
        background-color: #F8F8F8;
    }

    .all {
        position: relative;
        padding: 20px 0;
    }

    .wrapper {
        width: 100%;
        max-width: 1000px;
        margin: auto;
        padding: 32px;
        box-sizing: border-box;
        border: 1px solid #DDDDDD;
        border-radius: 10px;
        background-color: #FFFFFF;
    }

    @media screen and (max-width: 1024px) {
        body {
            background-color: #FFFFFF;
        }

        .wrapper {
            border: none;
            border-radius: 0;
            width: 100%;
            max-width: 100%;
        }
    }

    code {
        white-space: pre;
        background-color: #EEEEEE;
        padding: 2px 0;
    }

    pre {
        display: block;
        border: 1px solid #DDDDDD;
        padding: 20px;
        overflow-x: auto;
    }

    pre,
    pre code {
        background-color: #F8F8F8;
    }

    a {
        color: #555555;
        text-decoration: none;
    }

    a:hover {
        text-decoration: underline;
    }

    h1 {
        text-align: center;
        font-weight: normal;
        font-size: 32px;
        color: #0c7927;
    }

    h2,
    h3,
    h4 {
        border-bottom: 1px solid #DDDDDD;
        color: #444444;
        padding: 4px 0;
        font-weight: normal;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    table thead th,
    table thead td {
        background-color: #EEEEEE;
        padding: 5px 10px;
    }

    table tbody td {
        padding: 5px 10px;
    }

    table tbody tr:nth-child(odd) td {
        background-color: #FAFAFA;
    }

    table tbody tr:nth-child(even) td {
        background-color: #F5F5F5;
    }

    * {
        scrollbar-width: thin;
        scrollbar-color: #c0c0c0 #e5e5e5;
    }

    *::-webkit-scrollbar {
        width: 10px;
    }

    *::-webkit-scrollbar-track {
        background: #e5e5e5;
    }

    *::-webkit-scrollbar-thumb {
        background-color: #c0c0c0;
        border-radius: 10px;
        border: 3px solid #ffffff;
    }
    </style>
</head>

<body>
    <div class="all">
        <div class="wrapper">
            <?php
    echo $html;
    ?>
        </div>
    </div>
    <script>
    var hdr;
    window.onload = function() {
        hdr = document.getElementsByTagName('h2');
        for (var i = 0; i < hdr.length; i++) {
            var txt = hdr[i].textContent;
            txt = txt.toLowerCase()
                .split(' ').join('-')
                .split('/').join('')
                .split('+').join('')
                .split('$').join('')
                .split('(').join('')
                .split(')').join('')
                .split('.').join('')
                .trim();
            hdr[i].setAttribute('id', txt);
        }
    }
    </script>
</body>

</html>