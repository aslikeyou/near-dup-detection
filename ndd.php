<?php

function __autoload($class_name) {
    include $class_name . '.php';
}


$detector = new Detector('./../ndd/test');
    echo "Checking for duplicates using NDD...\n";
    $duplicates = $detector->check_for_duplicates();
    if($duplicates) {
        echo "Duplicates found (Jaccard coefficient > 0.5):\n";
        print_r($duplicates);
    }
