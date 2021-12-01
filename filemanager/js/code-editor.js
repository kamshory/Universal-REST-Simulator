var modified = true;
var editor = null;
function format() {
    var totalLines = editor.lineCount();
    editor.autoFormatRange({ line: 0, ch: 0 }, { line: totalLines });
}
var modeInput = null;
window.onload = function () {
    document.addEventListener('keydown', function (e) {
        if (e.ctrlKey && (e.which == 83)) {
            e.preventDefault();
            saveFile();
            return false;
        }
    });
    modeInput = document.getElementById('filename');
    CodeMirror.modeURL = "cm/mode/%N/%N.js";
    editor = CodeMirror.fromTextArea(document.getElementById("code"),
        {
            lineNumbers: true,
            lineWrapping: true,
            matchBrackets: true,
            indentUnit: 4,
            indentWithTabs: true
        });
    change();

    window.addEventListener('resize', function (e) {
        var w = window.innerWidth - 0;
        var h = window.innerHeight - 70;
        editor.setSize(w, h);
    });
    document.getElementById('open').addEventListener('click', function (e) {
        var c1 = document.getElementById('code').value;
        var c2 = editor.getValue();
        e.preventDefault();
        if (c1 != c2) {
            if (confirm('This file has been changed but you have not saved. Are you going to open a new file without saving this file?')) {
                openFile();
            }
        }
        else {
            openFile();
        }
    });
    var w = window.innerWidth - 0;
    var h = window.innerHeight - 60;
    editor.setSize(w, h);

    CodeMirror.on(modeInput, "keypress", function (e) {
        if (e.keyCode == 13) {
            openFile();
        }
    });
    modeInput.addEventListener('change', function () {
        change();
    });
    document.getElementById('save').addEventListener('click', function () {
        saveFile();
    });
    $(document).on('click', '.alert-button button', function (e2) {
        $(this).closest('.alert').fadeOut('fast');
    });
}
function openFile() {
    var filepath = modeInput.value;
    ajax.get('code-editor.php', { 'option': 'ajax-load', 'filepath': filepath }, function (answer) {
        editor.setValue(answer);
        document.getElementById('code').value = answer;
        change();
    });
}
function onSaveFile() {
    saveFile();
    return false;
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
function customAlert(title, text, btnText) {
    var obj = $('.alert');
    obj.find('.alert-title').text(title);
    obj.find('.alert-content').text(text);
    obj.find('.alert-button button').text(btnText);
    obj.css({ 'display': 'block' });
    setTimeout(function (e2) {
        obj.fadeOut('fast');
    }, 2000);
}
function saveFile() {
    var filepath = document.getElementById('filename').value;
    var filecontent = editor.getValue();
    ajax.post('tool-edit-file.php?option=savefile', { filepath: filepath, filecontent: filecontent }, function (answer) {
        if (answer == 'READONLY') {
            customAlert('Failed', 'The operation was disabled on read-only mode.', 'Close');
        }
        else if (answer == 'READONLYFILE') {
            customAlert('Failed', 'Saving was aborted because this file is read-only. You should to change permission of this file first.', 'Close');
        }
        else if (answer == 'ISDIR') {
            customAlert('Failed', 'Saving was aborted because this file name is similiar to a directory name. You should to change file name first.', 'Close');
        }
        else if (answer == 'FORBIDDENEXT') {
            customAlert('Failed', 'Saving was aborted because this file name extension is forbidden. Please use another file name extension to save it.', 'Close');
        }
        else if (answer == 'NOTMODIFIED') {
            customAlert('Notice', 'Content is not modified.', 'Close');
        }
        else if (answer == 'SAVED') {
            customAlert('Saved', 'File saved.', 'Close');
            document.getElementById('code').value = filecontent;
        }
    });
}
function getfileextension(filename) {
    return (/[.]/.exec(filename)) ? /[^.]+$/.exec(filename) : '';
}

function change() {
    var modeInput = document.getElementById('filename');
    var val = modeInput.value, m, mode, spec;
    var ext = getfileextension(val);
    document.getElementById('filename').setAttribute('class', 'fileicon-' + ext + ' filepath');
    if (m = /.+\.([^.]+)$/.exec(val)) {
        var info = CodeMirror.findModeByExtension(m[1]);
        if (info) {
            mode = info.mode;
            spec = info.mime;
        }
    }
    else if (/\//.test(val)) {
        var info = CodeMirror.findModeByMIME(val);
        if (info) {
            mode = info.mode;
            spec = val;
        }
    }
    else {
        mode = spec = val;
    }
    if (mode) {
        editor.setOption("mode", spec);
        CodeMirror.autoLoadMode(editor, mode);
    }
}