<?php

class Crossword {

//     tworzy tablice-grid z pustymi wartosciami lub wartosciami z wyrazami 
//     zwraca tablice w getCrossword()
//     laczy sie z klasa Cookies by pobrac juz utworzona tablice 

    private $width = 23;
    private $height = 23;
    private $crossword = [];
    private $originWordsCount;
    private $omittedWords = [];
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
    private $commonLetter;

//        'letter' => $word1[$i],
//        'posInFirst' => $i,
//        'posInSecond' => $pos --- to jest wzgledna numeracja a nie bezwzgledna!!!
    private $commonLetters = [];

    public function __construct() {

        $this->countOriginWords();

        if (!Cookies::isReset() && Cookies::isGrid()) {
            $this->crossword = Cookies::getGrid();
        } else {
            $this->createEmptyGrid();
//            $this->placeWords();
            $this->placeFirstWord();
            $this->algorithmOne();
        }
    }

    private function algorithmOne() {
        $counter = 2;
        do {
            $word2 = $this->getRandomWord(); 
            $length = strlen($word2);

            // get previous - last word
            $word1Array = end($this->sequenceWords);
            // find common letter 

            // add searching for all letters in last word and not only first 
            
            if ($this->findAllCommonLetters($word1Array['word'], $word2)) {
                // calc new word position - is always taking opposite direction
                $commonIndex = 0;
//                if ($counter> 2) {
//                    var_dump($this->commonLetters, '<br>');
//                }
                list($x, $y, $direction) = $this->calcNewWordPosition($word1Array, $commonIndex);

//                var_dump($x, $y, $length, $direction, $word2, '<br/>');
//                var_dump($this->commonLetter, '<br/>');
                if ($this->ifCanPlaceWord($x, $y, $length, $direction, $word2, $commonIndex)) {
                    // place word 2        
                    $this->placeWord($x, $y, $length, $direction, $word2);
                    $this->addToSequenceWords($x, $y, $length, $direction, $word2, $counter);
                } else {
                    $this->omittedWords[] =  ' No. '. $counter . ' '.$word2;                    
                }
            } 
            
            else {
                $this->omittedWords[] =  ' No. '. $counter. ' NO COMMON ' .$word2;  
//                var_dump($word1Array , '<br/>');
//                var_dump($this->commonLetter, '<br/>');
//                $this->addToSequenceWords(0, 0, $length, 'NONE', $word2);
            }
            $counter++;
        } while ($counter < 9);
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
//                    var_dump($x - 1, $y + $i);
                    return false;
                }
                // column after word 
                if (!$this->isGoodFieldAround($x + $length, $y + $i)) {
//                    var_dump($x + $length, $y + $i);
                    return false;
                }                
            }
            for ($i = 0; $i < $length; $i++) {
                $letter = $word2[$i];
//                var_dump($word2[$i]);
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
//                    var_dump($x + $i, $y - 1);
                    return false;
                }
                // row after word 
                if (!$this->isGoodFieldAround($x + $i, $y + $length)) {
//                    var_dump($x + $i, $y + $length);
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

    private function findFirstCommonLetter($word1, $word2) {
        // sets the new x y of the new word ... a nie jakies pomiedzy
        
        for ($i = 0; $i < strlen($word1); $i++) {
            $pos = strpos($word2, $word1[$i]);
            if ($pos !== false) { // czyli zero tez bierze za false!!! a ma szukac tylko false
                $this->commonLetter = [
                    'letter' => $word1[$i],
                    'posInFirst' => $i,
                    'posInSecond' => $pos
                ];
                return true;
            }
        }
        return false;
    }
// TO DO napisac funkcje ktora wyszukuje wszystkie wspolne wyrazy dla 2 
    
    private function findAllCommonLetters($word1, $word2) {
        // sets the new x y of the new word ... a nie jakies pomiedzy      
        // trzeba dodac przesuwajacy sie wskaznik
        // trzeba czyscic !! - 
        // TO DO na razie w drugim wyrazie nie szuka po wszystkim
        $this->commonLetters = [];
        $pointer = 0;
        for ($i = 0; $i < strlen($word1); $i++) {
            $pos = strpos(substr($word2, $pointer), $word1[$i]);
            if ($pos !== false) { // czyli zero tez bierze za false!!! a ma szukac tylko false
                $this->commonLetters[] = [
                    'letter' => $word1[$i],
                    'posInFirst' => $i,
                    'posInSecond' => $pos + $pointer
                ];
                $pointer = $i;
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

    private function placeFirstWord() {
        $word = $this->getRandomWord();
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

    private function getRandomWord() {
        $index = $this->getRandomIndex();
        $word = strtoupper($this->words[$index]);
        $this->removeElement($index);
        return $word;
    }

    private function getRandomIndex() {
        return array_rand($this->words);
    }

    private function removeElement($index) {
        unset($this->words[$index]);
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

    private function isValidPlace($x, $y, $length, $randDir) {
        if ($randDir === 'V') {
            for ($i = 0; $i < $length; $i++) {
                if ($this->crossword[$y][$x + $i] !== ' ') {
                    return false;
                }
            }
            return true;
        } else {
            for ($j = 0; $j < $length; $j++) {
                if ($this->crossword[$y + $j][$x] !== ' ') {
                    return false;
                }
            }
            return true;
        }
    }

    private function getRandDirection() {
        return $this->directions[array_rand($this->directions)];
    }

    public function getHeaderFields() {
        $wordsPlaced = count($this->sequenceWords);
        return [
            'allWords' => $this->originWordsCount,
            'placedWords' => $wordsPlaced,
            'omittedWords' => join(',', $this->omittedWords)
        ];
    }

    public function getCrossword() {
        return $this->crossword;
    }

    public function getSequenceWords() {
//        echo '<pre>';
        return $this->sequenceWords;
    }

}
