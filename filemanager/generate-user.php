<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create User</title>
    <link rel="stylesheet" href="style/login.css">
    <style>
        body{
            margin: 0;
            padding: 0;
            position: relative;
        }
        .box{
            width: 600px;
        }
        .all{
            margin: 0;
            padding: 0;
        }
        .label{
            padding: 2px 0;
        }
        .input{
            padding: 2px 0;
            position: relative;
        }
        
        .input textarea,
        .input [type="text"],
        .input [type="password"],
        .input input[type="button"],
        .input input[type="submit"],
        .input input[type="reset"],
        .input button{
            width: 100%;
        }
        .input input[type="text"],
        .input input[type="password"],
        .input textarea{
            background-color: #FFFFFF;
        }
        .input input[type="text"]:focus-visible,
        .input input[type="password"]:focus-visible,
        .input textarea:focus-visible,
        .input input[type="button"]:focus-visible,
        .input input[type="submit"]:focus-visible,
        .input input[type="reset"]:focus-visible,
        .input button:focus-visible{
            outline: none;
        }
        textarea{
            resize: vertical;
        }
    </style>
    <script>
        var userList = [];
        function inAray(needle, haystack)
        {
            for(var i = 0; i<haystack.length; i++)
            {
                if(haystack[i] == needle)
                {
                    return true;
                }
            }
            return false;
        }
        function getLastUser()
        {
            var cur = document.querySelector('#output').value || "";
            cur = cur.replace(/\r?\n/g, "\r\n").trim();
            var arr = cur.split("\r\n");
            for(var i = 0; i<arr.length; i++)
            {
                var x = arr[i].split(":");
                var y = x[0].trim();
                if(y != "")
                {
                    arr[i] = y;
                }
            }
            var combined = arr.concat(userList); 
            return combined;
            
        }
        function generatePassword()
        {
            var username = document.querySelector('#username').value || '';
            var password = document.querySelector('#password').value || '';
            var userListEx = getLastUser();
            if(username != '' && password != '' && !inAray(username, userListEx))
            {
                addToList(username, password);
                userList.push(username);
            }
            return false;
        }
        function addToList(username, password)
        {
            ajax.post('ajax-create-user.php', {username: username, password:password}, function(response) {
                var cur = document.querySelector('#output').value || "";
                if(cur != "")
                {
                    cur += "\r\n";
                }
                cur += response;
                document.querySelector('#output').value = cur;
            });
        }
        var ajax = {};
        ajax.x = function () {
            if (typeof XMLHttpRequest !== 'undefined') {
                return new XMLHttpRequest();
            }
            var versions = [
                "MSXML2.XmlHttp.6.0",
                "MSXML2.XmlHttp.5.0",
                "MSXML2.XmlHttp.4.0",
                "MSXML2.XmlHttp.3.0",
                "MSXML2.XmlHttp.2.0",
                "Microsoft.XmlHttp"
            ];

            var xhr;
            for (var i = 0; i < versions.length; i++) {
                try {
                    xhr = new ActiveXObject(versions[i]);
                    break;
                } catch (e) {
                }
            }
            return xhr;
        };

        ajax.send = function (url, callback, method, data, async) {
            if (async === undefined) {
                async = true;
            }
            var x = ajax.x();
            x.open(method, url, async);
            x.onreadystatechange = function () {
                if (x.readyState == 4) {
                    callback(x.responseText)
                }
            };
            if (method == 'POST') {
                x.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            }
            x.send(data)
        };

        ajax.get = function (url, data, callback, async) {
            var query = [];
            for (var key in data) {
                query.push(encodeURIComponent(key) + '=' + encodeURIComponent(data[key]));
            }
            ajax.send(url + (query.length ? '?' + query.join('&') : ''), callback, 'GET', null, async)
        };

        ajax.post = function (url, data, callback, async) {
            var query = [];
            for (var key in data) {
                query.push(encodeURIComponent(key) + '=' + encodeURIComponent(data[key]));
            }
            ajax.send(url, callback, 'POST', query.join('&'), async)
        };
    </script>
</head>
<body>
    <div class="all">
        <div class="box">
            <div class="box-inner">
                <div class="box-title">Password Generator</div>
                    <div class="form box-form">
                        <form action="" onsubmit="return generatePassword()">
                            <div class="input-group">
                                <div class="label">Username</div>
                                <div class="input">
                                    <input type="text" class="input-text-login" name="username" id="username" autocomplete="none">
                                </div>
                            </div>
                            <div class="input-group">
                                <div class="label">Password</div>
                                <div class="input field">
                                    <input type="password" class="input-text-login" name="password" id="password" autocomplete="none">
                                </div>
                            </div>
                            <div class="input-group">
                                <div class="input field">
                                    <input type="submit" class="login-button" name="generate" id="generate" value="Add To List">
                                </div>
                            </div>
                            <div class="input-group">
                                <div class="input field">
                                    <textarea class="input-text-login" name="output" id="output" cols="30" rows="10"></textarea>
                                </div>
                            </div>
                            <div class="input-group">
                                <div class="label">Put text in text area to file /filemanager/.htpasswd</div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>