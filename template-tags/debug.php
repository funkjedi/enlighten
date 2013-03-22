<?php

function __d() {
    print '<div id="editor">';
    $args = func_get_args();
    foreach($args as $index => $arg) {
        var_dump($arg);
        echo "\n";
    }
    print '</div>';
?>
    <style type="text/css" media="screen">
        #editor {
            z-index: 100000;
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            display: none;
        }
    </style>
    <script src="http://d1n0x3qji82z53.cloudfront.net/src-min-noconflict/ace.js" type="text/javascript" charset="utf-8"></script>
    <script>
        var editor = ace.edit("editor");
        editor.setTheme("ace/theme/clouds");
        editor.getSession().setMode("ace/mode/php");
        document.getElementById("editor").style.display = 'block';
    </script>
<?php
    exit;
}


function enlighten_log($message) {
    print "<!-- $message -->";
}
