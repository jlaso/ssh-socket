<html>

<head>
    <style>
        textarea#output{
            font-family: "Lucida Console", Monaco, monospace;
        }
    </style>
    <script src="https://code.jquery.com/jquery-2.1.3.min.js"></script>
</head>
<body>

<div>

    <div>
        <h1 style="display: inline-block;">TEST SSH</h1>
        <input type="text" id="command" value="" placeholder="command"/>
        <input type="button" id="go" value="go"/>
    </div>
    
    <textarea name="" id="output" cols="180" rows="60"></textarea>
</div>


</body>


<script>
    var conn = null;

    $(function(){
        console.log( "ready!" );

        conn = new WebSocket('ws://localhost:8080');
        conn.onopen = function(e) {
            console.log("Connection established!");
        };

        conn.onmessage = function(e) {
            console.log(e.data);
            $("#output").append(e.data+"\n");
        };

        $("#go").click(function(e){
            e.preventDefault();
            console.log('sending');
            var command = $("#command").val();
            conn.send(command);
            $("#command").val("");
        })
    });
</script>

</html>