<?php
session_start();
$_SESSION['valido'] = true;
http_response_code(200);