<?php

require_once __DIR__.'/vendor/autoload.php';

use SensioLabs\AnsiConverter\Theme\SolarizedXTermTheme;

$theme = new SolarizedXTermTheme();

$parameters = parse_ini_file(__DIR__.'/src/config/parameters.ini', true);
$config = $parameters['config'];

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

    <pre id="output"></pre>

    <input type="text" id="command" value="" placeholder="" autofocus/>

    <a href="https://github.com/jlaso/ssh-socket" target="_blank">
        <img style="position: absolute; top: 0; right: 0; border: 0;"
             src="https://camo.githubusercontent.com/365986a132ccd6a44c23a9169022c0b5c890c387/68747470733a2f2f73332e616d617a6f6e6177732e636f6d2f6769746875622f726962626f6e732f666f726b6d655f72696768745f7265645f6161303030302e706e67" alt="Fork me on GitHub"
             data-canonical-src="https://s3.amazonaws.com/github/ribbons/forkme_right_red_aa0000.png">
    </a>

</body>


<script>
    var conn = null;

    function output(data, append)
    {
        var append = append | false;
        if(append){
            $("#output").append(data+"\n");
            $("#output").scrollTop(1000000);
        }else{
            $("#output").html(data);
        }
    }

    function processCommand(command){
        switch (command){
            case 'clear':
                output("");
                break;

            case 'help':
                alert(
                    "* This is an experiment to exploit the ssh2 library \n"+
                    "* Interactive commands are not allowed so far because of streaming way of the output"
                );
                break;

            default:
                conn.send(command);
                break;
        }
        $("#command").val("");
    }

    $(function(){

        conn = new WebSocket('<?php echo sprintf('ws://%s:%d',$_SERVER['SERVER_ADDR'], $config['port']); ?>');

        conn.onopen = function(e) {
            output("Connection established!\n\n");
        };

        conn.onclose = function(e) {
            output("\nConnection lost!\n\n",true);
        }

        conn.onmessage = function(e) {
            output(e.data, true);
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