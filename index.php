<?php
require 'Colo.php';

Colo::route('/', function(){
    echo 'hello world!';
});

Colo::start();
