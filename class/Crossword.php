<?php

class Crossword {

//     tworzy tablice-grid z pustymi wartosciami lub wartosciami z wyrazami 
//     zwraca tablice w getCrossword()
//     laczy sie z klasa Cookies by pobrac juz utworzona tablice 

    private $width = 31;
    private $height = 31;
    private $crossword = [];
    private $originWordsCount;
    private $omittedWords = [];
    private $firstOmitted;
    private $words = [
        'baton',
        'zabawka',
        'przestrzen',
        'myslnik',
        'paczka',
        'kanasta',
        'obibok',
        'klocek'
    ];
    private $directions = ['V', 'H'];
    private $sequenceWords = [];
    // temporary variables 
//        'letter' => $word1[$i],
//        'posInFirst' => $i,
//        'posInSecond' => $j 
    private $commonLetters = [];

    public function __construct() {

        $this->countOriginWords();

        if (!Cookies::isReset() && Cookies::isGrid()) {
            $this->crossword = Cookies::getGrid();
        } else {
            $this->createEmptyGrid();
//            $aWords = $this->words; // trzeba tu reference uzyc 
            $this->placeFirstWord($this->words);
            $this->algorithmOne($this->words);
            // run again for omitted 
            if (count($this->omittedWords) > 0) {
                $this->firstOmitted = count($this->omittedWords);
                $omittedWords = self::getValuesFromArray($this->omittedWords);
                $this->omittedWords = [];
                $this->algorithmOne($omittedWords, 'add');
            }
//            var_dump($this->getCrosswordSize($this->sequenceWords));
        }
    }

    private function algorithmOne(&$aWords, $phase = '') { // passing table as reference 
        // TO DO add to go through all placed words when searching for fit 
        // ADDED checks all common letter 
        // ADDED places word on first good fit 
        if ($phase === 'add') {
            $loopCount = $this->originWordsCount + count($aWords);
            $counter = $this->originWordsCount + 1;            
        } else {            
            $loopCount = $this->originWordsCount;
            $counter = count($this->sequenceWords) + 1;            
        }
        
        do {
            $word2 = $this->getRandomWord($aWords);
            $length = strlen($word2);

            // CHECK ALL WORDS A NOT ONLY LAST             
            $notPlaced = true;
            // loop all placed words starting from the last one 
            for ($index = count($this->sequenceWords) - 1; $index >= 0; $index--) {

                $word1Array = $this->sequenceWords[$index];
                // for 2 fetched words try if they have common letters and if they can anyhow fit 
                if ($this->findAllCommonLetters($word1Array['word'], $word2)) {
                    // calc new word position - is always taking opposite direction
                    // commonIndex to numer wspolnej litery jeden z tablicy 
                    $commonIndex = -1;
                    do {
                        $commonIndex++;
                        list($x, $y, $direction) = $this->calcNewWordPosition($word1Array, $commonIndex);

                        if ($this->ifCanPlaceWord($x, $y, $length, $direction, $word2, $commonIndex)) {
                            // place word 2        
                            $this->placeWord($x, $y, $length, $direction, $word2);
                            $this->addToSequenceWords($x, $y, $length, $direction, $word2, $counter);
                            $notPlaced = false;
                        }
                    } while ($notPlaced && $commonIndex !== count($this->commonLetters) - 1);
                }
                // if word was placed - do not search for other words on board 
                if ($notPlaced === false) {
                    break;
                }
            }
            // add to omitted words if word was not placed 
            if ($notPlaced === true) {
                $this->omittedWords[$counter] = $word2;
            }

            $counter++;
        } while ($counter <= $loopCount);
    }
    
    private function sliceCrossword($cross) {
        $size = $this->getCrosswordSize($this->sequenceWords);
        $newCrossword = [];
        for ($row = $size['yMin']; $row <= $size['yMax']; $row++) {
            $newRow = array_slice($cross[$row], $size['xMin'], $size['xMax'] - $size['xMin'] + 1);
            $newCrossword[] = $newRow;
        }
        return $newCrossword;
    }

    private function getCrosswordSize($cross) {
        $xMax = 0; $xMin = $this->width - 1;
        $yMax = 0; $yMin = $this->height - 1;
        foreach ($this->sequenceWords as $value) {
            if ($value['direction'] === 'V') {
                $yLast = $value['y'] + $value['length'] - 1;
            } else {
                $yLast = $value['y'];
            }
            if ($value['direction'] === 'H') {
                $xLast = $value['x'] + $value['length'] - 1;
            } else {
                $xLast = $value['x'];
            }                        
            
            if ($value['x'] < $xMin) {
                $xMin = $value['x'];
            }
            if ($xLast > $xMax) {
                $xMax = $xLast;
            }
            if ($value['y'] < $yMin) {
                $yMin = $value['y'];
            }
            if ($yLast > $yMax) {
                $yMax = $yLast;
            }
        }
        
        return [
            'xMin' => $xMin,
            'xMax' => $xMax,
            'yMin' => $yMin,
            'yMax' => $yMax
        ];
    }
    
    private function countOriginWords() {
        $this->originWordsCount = count($this->words);
    }

    private function ifCanPlaceWord($x, $y, $length, $direction, $word2, $commonIndex) {
        // czy nie pokrywa zle innego ... bo moze pokrywac wiele ale dobrze 
        if ($direction === 'H') {
            for ($i = -1; $i <= 1; $i++) {
                // column before word 
                if (!$this->isGoodFieldAround($x - 1, $y + $i)) {
                    return false;
                }
                // column after word 
                if (!$this->isGoodFieldAround($x + $length, $y + $i)) {
                    return false;
                }
            }
            for ($i = 0; $i < $length; $i++) {
                $letter = $word2[$i];
                // w srodku 
                if (!$this->isGoodFieldOn($x + $i, $y, $letter)) {
                    return false;
                }
                // u gory 
                if (!$this->isGoodFieldAround($x + $i, $y - 1) && !($i === $this->commonLetters[$commonIndex]['posInSecond'])) {
                    return false;
                }
                // na dole 
                if (!$this->isGoodFieldAround($x + $i, $y + 1) && !($i === $this->commonLetters[$commonIndex]['posInSecond'])) {
                    return false;
                }
            }
        }
        if ($direction === 'V') {
            for ($i = -1; $i <= 1; $i++) {
                // row before word 
                if (!$this->isGoodFieldAround($x + $i, $y - 1)) {
                    return false;
                }
                // row after word 
                if (!$this->isGoodFieldAround($x + $i, $y + $length)) {
                    return false;
                }
            }
            for ($i = 0; $i < $length; $i++) {
                $letter = $word2[$i];
                if (!$this->isGoodFieldOn($x, $y + $i, $letter)) {
                    return false;
                }
                if (!$this->isGoodFieldAround($x - 1, $y + $i) && !($i === $this->commonLetters[$commonIndex]['posInSecond'])) {
                    return false;
                }
                if (!$this->isGoodFieldAround($x + 1, $y + $i) && !($i === $this->commonLetters[$commonIndex]['posInSecond'])) {
                    return false;
                }
            }
        }
        return true;
    }

    private function isGoodFieldOn($x, $y, $letter) {
        if ($this->crossword[$y][$x] === ' ' || $this->crossword[$y][$x] === $letter) {
            return true;
        }
        return false;
    }

    private function isGoodFieldAround($x, $y) {
        if ($this->crossword[$y][$x] === ' ') {
            return true;
        }
        return false;
    }

// TO DO napisac funkcje ktora wyszukuje wszystkie wspolne wyrazy dla 2 

    private function findAllCommonLetters($word1, $word2) {
        // sets the new x y of the new word ... a nie jakies pomiedzy      
        $this->commonLetters = [];
        for ($i = 0; $i < strlen($word1); $i++) {
            for ($j = 0; $j < strlen($word2); $j++) {
                if ($word1[$i] === $word2[$j]) {
                    $this->commonLetters[] = [
                        'letter' => $word1[$i],
                        'posInFirst' => $i,
                        'posInSecond' => $j
                    ];
                }
            }
        }
        if (count($this->commonLetters) > 0) {
            return true;
        }
        return false;
    }

    private function calcNewWordPosition($word1Array, $commonIndex) {
        // takes second word from $this->commonLetter 
        // return x y and direction of new word 
        if ($word1Array['direction'] === 'V') {
            $direction = 'H';
            $y = $word1Array['y'] + $this->commonLetters[$commonIndex]['posInFirst'];
            $x = $word1Array['x'] - $this->commonLetters[$commonIndex]['posInSecond'];
        } else {
            $direction = 'V';
            $y = $word1Array['y'] - $this->commonLetters[$commonIndex]['posInSecond'];
            $x = $word1Array['x'] + $this->commonLetters[$commonIndex]['posInFirst'];
        }
        return [$x, $y, $direction];
    }

    private function placeFirstWord(&$aWords) {
        $word = $this->getRandomWord($aWords);
        $length = strlen($word);
        $direction = $this->getRandDirection();
        list($x, $y) = $this->getFirstCoordinates($length, $direction);
        $this->placeWord($x, $y, $length, $direction, $word);
        $this->addToSequenceWords($x, $y, $length, $direction, $word, 1);
    }

    private function placeWord($x, $y, $length, $direction, $word) {
        if ($direction === 'H') {
            for ($i = 0; $i < $length; $i++) {
                $this->crossword[$y][$x + $i] = $word[$i];
            }
        } else {
            for ($j = 0; $j < $length; $j++) {
                $this->crossword[$y + $j][$x] = $word[$j];
            }
        }
    }

    private function getFirstCoordinates($length, $direction) {
        list($x, $y) = $this->getMiddleCoordinates();
        $offset = floor($length / 2);
        if ($direction === 'V') {
            $y = $y - $offset;
        } else {
            $x = $x - $offset;
        }
        return [$x, $y];
    }

    private function getMiddleCoordinates() {
        return [floor($this->width / 2), floor($this->height / 2)];
    }

    private function getRandomWord(&$aWords) {
        $index = $this->getRandomIndex($aWords);
        $word = strtoupper($aWords[$index]);
        $this->removeElement($index, $aWords);
        return $word;
    }

    private function getRandomIndex(&$aWords) {
        return array_rand($aWords);
    }

    private function removeElement($index, &$aWords) {
//        var_dump($aWords[$index], count($aWords));
        unset($aWords[$index]);
    }

    private function createEmptyGrid() {
        for ($i = 0; $i < $this->height; $i++) {
            $row = [];
            for ($j = 0; $j < $this->width; $j++) {
                $row[] = ' ';
            }
            $this->crossword[] = $row;
        }
    }

    private function addToSequenceWords($x, $y, $length, $direction, $word, $counter) {
        $this->sequenceWords[] = [
            'x' => $x,
            'y' => $y,
            'length' => $length,
            'direction' => $direction,
            'word' => $word,
            'No.' => $counter
        ];
    }

    private function getRandDirection() {
        return $this->directions[array_rand($this->directions)];
    }

    public function getHeaderFields() {
        $wordsPlaced = count($this->sequenceWords);
        return [
            'allWords' => $this->originWordsCount,
            'placedWords' => $wordsPlaced,
            'firstOmitted' => $this->firstOmitted,
            'omittedWords' => self::getStringFromArray($this->omittedWords)
        ];
    }

    private static function getValuesFromArray($arr) {
        return array_values($arr);
    }

    private static function getStringFromArray($arr) {
        $result = '';
        foreach ($arr as $key => $value) {
            $result .= $key.'. '.$value;            
        }
        return $result;
    }

    public function getCrossword() {
        $newCrossword = $this->sliceCrossword($this->crossword);
        return $newCrossword;
    }

    public function getSequenceWords() {

        return $this->sequenceWords;
    }

}
