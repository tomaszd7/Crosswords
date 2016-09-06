<?php

class Crossword {

//     tworzy tablice-grid z pustymi wartosciami lub wartosciami z wyrazami 
//     zwraca tablice w getCrossword()
//     laczy sie z klasa Cookies by pobrac juz utworzona tablice 

    private $width = 15;
    private $height = 15;
    private $crossword = [];
    private $words = [
//        'baton',
//        'puszak',
//        'klocak'
        'myslnik',
        'paczka',
        'baton',
        'puszek',
        'klocek'
    ];
    private $directions = ['V', 'H'];
    private $sequenceWords = [];
    // temporary variables 
    private $letter;

    public function __construct() {

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
        $word2 = $this->getRandomWord();
        $length = strlen($word2);

        // get first word
        $word1Array = $this->sequenceWords[0];
        // find common letter 
        if ($this->findFirstCommonLetter($word1Array['word'], $word2)) {
            // calc new word position 
            list($x, $y, $direction) = $this->calcNewWordPosition($word1Array);

            // check if can place 
            // place word 2        
            $this->placeWord($x, $y, $length, $direction, $word2);
            $this->addToSequenceWords($x, $y, $length, $direction, $word2);
        } else {
            $this->addToSequenceWords(0, 0, $length, 'NONE', $word2);
        }
    }

    private function findFirstCommonLetter($word1, $word2) {
        // sets the new x y of the new word ... a nie jakies pomiedzy

        for ($i = 0; $i < strlen($word1); $i++) {
            $pos = strpos($word2, $word1[$i]);
            if ($pos) {
                $this->letter = [
                    'letter' => $word1[$i],
                    'posInFirst' => $i,
                    'posInSecond' => $pos
                ];
                return true;
            }
        }
        return false;
    }

    private function calcNewWordPosition($word1Array) {
        // takes second word from $this->letter 
        // return x y and direction of new word 
        if ($word1Array['direction'] === 'V') {
            $direction = 'H';
            $y = $word1Array['y'] + $this->letter['posInFirst'];
            $x = $word1Array['x'] - $this->letter['posInSecond'];
        } else {
            $direction = 'V';
            $y = $word1Array['y'] - $this->letter['posInSecond'];
            $x = $word1Array['x'] + $this->letter['posInFirst'];
        }
        return [$x, $y, $direction];
    }

    private function placeFirstWord() {
        $word = $this->getRandomWord();
        $length = strlen($word);
        $direction = $this->getRandDirection();
        list($x, $y) = $this->getFirstCoordinates($length, $direction);
        $this->placeWord($x, $y, $length, $direction, $word);
        $this->addToSequenceWords($x, $y, $length, $direction, $word);
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

    private function addToSequenceWords($x, $y, $length, $direction, $word) {
        $this->sequenceWords[] = [
            'x' => $x,
            'y' => $y,
            'length' => $length,
            'direction' => $direction,
            'word' => $word
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

    public function getCrossword() {
        return $this->crossword;
    }

    public function getSequenceWords() {
//        echo '<pre>';
        return $this->sequenceWords;
    }

}
