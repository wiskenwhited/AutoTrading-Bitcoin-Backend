@extends('master')
    <p><b>Every Time Event Fired this counter value increase by 10.</b></p>
    <p>Total = <span id="power">0</span></p>
    <script type="text/javascript" src="/socket.io.js"></script>
    <script>
        //var socket = io('http://localhost:3000');
        var socket = io('http://localhost:3000');
        socket.on("trades:App\\Events\\TradesUpdatedEvent", function(message){
            // increase the power everytime we load test route
            console.log(message);
            console.log(message.trades);
//            $('#power').text(parseInt($('#power').text()) + parseInt(message.data.power));
        });
    </script>
