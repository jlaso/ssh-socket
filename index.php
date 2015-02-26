<?php

require_once __DIR__.'/vendor/autoload.php';

use SensioLabs\AnsiConverter\Theme\SolarizedXTermTheme;

$theme = new SolarizedXTermTheme();

?>
<html>

<head>
    <style>
        #output,
        #command{
            font-family: "Lucida Console", Monaco, monospace;
            font-size: 1.1em;
            color: greenyellow;
            background-color: #222;
        }
        #output{
            width: 100%;
            height: 80%;
            overflow: scroll;
            margin-bottom: 0;
        }
        #command{
            width: 100%;
            border: none;
            padding: 5px;
        }
        <?php
            echo $theme->asCss();
         ?>
    </style>
    <script src="https://code.jquery.com/jquery-2.1.3.min.js"></script>
</head>

<body>

    <h1>TEST SSH</h1>

    <pre id="output">
    </pre>

    <input type="text" id="command" value="" placeholder="" autofocus/>

</body>


<script>
    var conn = null;

    function processCommand(command){
        switch (command){
            case 'clear':
                $("#output").html("");
                break;

            default:
                conn.send(command);
                break;
        }
        $("#command").val("");
    }

    $(function(){
        console.log( "ready!" );

        conn = new WebSocket('ws://localhost:8080');
        conn.onopen = function(e) {
            console.log("Connection established!");
        };

        conn.onmessage = function(e) {
            //console.log(e.data);
            $("#output").append(e.data+"\n");
            $("#output").scrollTop(1000000);
        };

        $('#command').keydown(function (e) {
            var key = e.charCode || e.keyCode || 0;
            if (key == 13) {
                event.preventDefault();
                var command = $("#command").val();
                processCommand(command);
            }
        });

    });
</script>

</html>