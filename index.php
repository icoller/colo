<?php
require 'system/Colo.php';

Colo::route('/', function(){
    echo 'hello world!';
});

Colo::start();
