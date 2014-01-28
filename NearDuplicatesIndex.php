<?php

class NearDuplicatesIndex {
    private $docs_to_ngrams;
    private $p;
    private $n;
    private $pairs_of_randoms;
    private $sketches;

    function __construct()
    {
        $this->docs_to_ngrams = []; # maps filenames to Ngram objects
        $this->p = 24107.0; # a prime larger than the number of 3-grams we'll have
        $this->n = 25; # the number of samples for the sketches
        $this->pairs_of_randoms = [];
        $this->generate_random_pairs_of_numbers();
        $this->sketches = []; # maps filename to sketch
    }

    # A document is an array of terms (e.g., ['a','this','where','pretzel']).
    # Every document must have a unique name.
    public function append($doc, $docname) {
        if(array_key_exists($docname, $this->sketches)) {
            throw new Exception;
        }

        $ngrams = $this->calculate_ngrams($doc, 3);
        $this->sketches[$docname] = $this->calculate_sketch($docname, $ngrams);
    }


    public static function xrange($start, $end, $step = 1) {
        for ($i = $start; $i <= $end; $i += $step) {
            yield $i;
        }
    }

    private function  generate_random_pairs_of_numbers() {
        foreach(self::xrange(0,25) as $i) {
            $a = rand(1, $this->p - 1);
            $b = rand(0, $this->p - 1);
            $this->pairs_of_randoms[] = [$a, $b];
        }

       // print_r($this->pairs_of_randoms);
    }

    private function calculate_ngrams($doc, $length) {
        $num_terms = count($doc);
        $ngrams = [];
        foreach(self::xrange(0, $num_terms) as $t) {
            if($num_terms <= $t+$length-1) {
                break; // n-2 ngrams!
            }

            $ngram_tokens = array_slice($doc,$t,$length);
            $ngram_value = implode("-", $ngram_tokens);
            $ngrams[] = new Ngram($ngram_value);
        }

        return $ngrams;
    }

    /**
     * @param $docname
     * @param Ngram[] $doc_ngrams
     * @return array
     */
    private function calculate_sketch($docname, $doc_ngrams) {
        $p = $this->p;
        foreach(self::xrange(0, $this->n) as $s) {
            $f_min = floatval('1.79769313486e+308'); //sys.float_info.max
            $a_s = $this->pairs_of_randoms[$s][0];
            $b_s = $this->pairs_of_randoms[$s][1];
            foreach($doc_ngrams as $ngram) {
                $fsx = ($a_s*floatval($ngram->getID()) + $b_s) % $p;
                if($fsx < $f_min) {
                    $f_min = $fsx;
                }
            }

            $sketch[$s] = $f_min;
        }

        return $sketch;
    }

    # Public: Estimates the jaccard coefficient
    #
    # m - the number of sketches (in the same index) of the same value
    #
    # Returns the estimated jaccard coefficient
    private function jaccard($m) {
        return ($m/floatval($this->n));
    }

    # Public: calculates the estimated jaccard coefficient between two documents
    #
    # docname1 - first document's name
    # docname2 - second document's name
    #
    # Returns the estimated jaccard coefficient between both documents
    public function get_jaccard($docName1, $docName2) {
        if(!($this->sketches[$docName1] || $this->sketches[$docName2])) {
            throw new Exception;
        }

        # get num of same sketch values
        $k = 0.0;
        foreach(self::xrange(0, $this->n) as $index) {
            if($this->sketches[$docName1][$index] == $this->sketches[$docName2][$index]) {
                $k += 1;
            }
        }
        return $this->jaccard($k);
    }
}