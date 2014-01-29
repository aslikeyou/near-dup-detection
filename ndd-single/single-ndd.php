<?php

require './helper.php';

$files = [];

$test_docs_dir="./../test";

$files[] = 'file01.txt';
$files[] = 'file75.txt';

$p = 24107.0; # a prime larger than the number of 3-grams we'll have
$n = 25; # the number of samples for the sketches

$pairs_of_randoms = generate_random_pairs_of_numbers($p);

$sketches = [];

foreach($files as $filename) {
    $doc = explode(' ', trim(trim(trim(file_get_contents(filename($test_docs_dir,$filename)), ",.!|&-_()[]<>{}/\"'"))));
    $ngrams = calculate_ngrams($doc, 3);
    $sketches[$filename] =  calculate_sketch($filename, $ngrams, $p, $n, $pairs_of_randoms);
}

echo get_jaccard($sketches[$files[0]], $sketches[$files[1]], $n);
echo "\n";




