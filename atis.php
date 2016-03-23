<?php
Echo '[' . $_GET["arpt"] . '][information]' . $_GET["info"] . ' ' . substr($_GET["metar"], 7, 4) . '[UTC][DEPRWY]' . $_GET["dep"] . '[LDGRWY]' . $_GET["arr"] . '[EXPECT][' . $_GET["apptype"] . "][APP][RWY]" . $_GET["arr"] . '[wind]' . substr($_GET["metar"], 13,3) . '[degrees][at]' . substr($_GET["metar"], 16,2) . '[MPS]';
//Echo 'Hello, world! You entered: Arrival: ' . $_GET["arr"] . ' Departure: ' . $_GET["dep"] . ' Approach Type: ' . $_GET["apptype"] . ' Information: ' . $_GET["info"] . ' METAR: ' . $_GET["metar"] . ' Airport: ' . $_GET["arpt"] . ' Thank you for using atis.gq!';
?>