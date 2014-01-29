<?php

function xrange($start, $end, $step = 1) {
    for ($i = $start; $i <= $end; $i += $step) {
        yield $i;
    }
}

function generate_random_pairs_of_numbers($p) {
    $pairs_of_randoms = [];
    foreach(xrange(0,25) as $i) {
        $a = rand(1, $p - 1);
        $b = rand(0, $p - 1);
        $pairs_of_randoms[] = [$a, $b];
    }
    return $pairs_of_randoms;
}

function c_mul($a,$b) {
    return intval($a) * $b & 0xFFFFFFFF;
}

function hashCalc($str) {
    $str = str_split($str);
    $value = ord($str[0]) << 7;
    //echo $value.'<br>';
    foreach($str as $char) {
        $value = (c_mul(1000003, $value) ^ ord($char));
    }

    return $value ^ count($str);
}

function calculate_ngrams($doc, $length) {
    $num_terms = count($doc);
    $ngrams = [];
    foreach(xrange(0, $num_terms) as $t) {
        if($num_terms <= $t+$length-1) {
            break; // n-2 ngrams!
        }

        $ngram_tokens = array_slice($doc,$t,$length);
        $ngram_value = implode("-", $ngram_tokens);
        $ngrams[] = hashCalc($ngram_value);
    }

    return $ngrams;
}

function calculate_sketch($docname, $doc_ngrams, $p, $n, $pairs_of_randoms) {
    $sketch = [];
    foreach(xrange(0, $n) as $s) {
        $f_min = floatval('1.79769313486e+308'); //sys.float_info.max
        $a_s = $pairs_of_randoms[$s][0];
        $b_s = $pairs_of_randoms[$s][1];
        foreach($doc_ngrams as $ngram) {
            $fsx = ($a_s*floatval($ngram) + $b_s) % $p;
            if($fsx < $f_min) {
                $f_min = $fsx;
            }
        }

        $sketch[$s] = $f_min;
    }

    return $sketch;
}

function jaccard($m, $n) {
    return ($m/floatval($n));
}

function get_jaccard($sketches1,$sketches2, $n) {
    # get num of same sketch values
    $k = 0.0;
    foreach(xrange(0, $n) as $index) {
        if($sketches1[$index] == $sketches2[$index]) {
            $k += 1;
        }
    }
    return jaccard($k, $n);
}

function filename($test_docs_dir, $filename) {
    return $test_docs_dir.'/'.$filename;
}