<?php

class Detector {
    private $test_docs_dir;
    private $files = [];

    /**
     * @var NearDuplicatesIndex
     */
    private $index;

    function __construct($test_docs_dir="./../ndd/test")
    {
        $this->test_docs_dir = $test_docs_dir;
        //$this->files = [d for d in os.listdir(test_docs_dir) if os.path.isfile(os.path.join(test_docs_dir, d)) and d[0] != "." ]
        $filesInDocsDir = scandir($test_docs_dir);

        foreach($filesInDocsDir as $file) {
            if(is_dir($this->filename($file)) || !is_readable($this->filename($file))) {
                continue;
            }

            $this->files[] = $file;
        }

        $this->index = new NearDuplicatesIndex();

        # Calculate near-duplicates index
        foreach($this->files as $file) {
            $filename = $this->filename($file);
            $doc = explode(' ', trim(trim(trim(file_get_contents($filename), ",.!|&-_()[]<>{}/\"'"))));
            $this->index->append($doc, $filename);
        }
    }

    private function filename($filename) {
        return $this->test_docs_dir.'/'.$filename;
    }

    # Public: checks for near-duplicates in the set of files based on jaccard
    #         coefficient threshold of 0.5
    #
    # Returns a string containing formatted names and coefficients of
    #   documents whose jaccard coefficient is greater than 0.5
    public function check_for_duplicates() {
        $matches = [];

        foreach($this->files as $indx1 => $f1) {
            $file1 = $this->filename($f1);
            foreach(array_slice($this->files,$indx1+1) as $indx2 => $f2 ) {
                $file2 = $this->filename($f2);
                $jaccard = $this->index->get_jaccard($file1, $file2);
                if($jaccard > 0.5) {
                    $matches[] = $f1.' and '.$f2.' are near-duplicates, with Jaccard value of '.$jaccard."\n";
                }
            }
        }

        return implode("", $matches);
    }
}

//$a = new Detector();
//var_dump(mt_getrandmax());