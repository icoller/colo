<?php
require 'src/Colo.php';

Colo::route('/', function(){
    echo 'hello world!';
});

Colo::start();
