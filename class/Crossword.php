<?php

class Crossword {

//     tworzy tablice-grid z pustymi wartosciami lub wartosciami z wyrazami 
//     zwraca tablice w getCrossword()
//     laczy sie z klasa Cookies by pobrac juz utworzona tablice 

    private $width = 15;
    private $height = 10;
    private $crossword = [];
    private $words = [
        'myslnik',
        'paczka',
        'baton',
        'puszek',
        'klocek'
    ];
    private $directions = ['V', 'H'];
    
    private $wordsOnGrid = [];
    

    public function __construct() {

        if (!Cookies::isReset() && Cookies::isGrid()) {
            $this->crossword = Cookies::getGrid();
        } else {
            $this->createEmptyGrid();
            $this->placeWords();
        }
    }
    
//    private function getRandomWord() {
//        $index = $this->getRandomIndex();
//        $word = $this->words[$index];
//        $this->removeElement($index);
//        return $word;
//    }
//
//    
//    private function getRandomIndex() {
//        return array_rand($this->words);        
//    }
//    
//    private function removeElement($index) {
//        unset($this->words[$index]);
//    }
    
    
    private function createEmptyGrid() {
        for ($i = 0; $i < $this->height; $i++) {
            $row = [];
            for ($j = 0; $j < $this->width; $j++) {
                $row[] = ' ';
            }
            $this->crossword[] = $row;
        }
    }

    private function placeWords() {
        foreach ($this->words as $word) {
            $wordLength = strlen($word);
            $word = strtoupper($word);
            list($x, $y, $direction) = $this->getValidPlace($wordLength);
//            echo $word . "\t\t LEN: " . $wordLength . " ROW: " . $y . " X: " . $x . "<br/>";
            if ($direction === 'V') {
                for ($i = 0; $i < $wordLength; $i++) {
                    $this->crossword[$y][$x + $i] = $word[$i];
                }                
            } else {
                for ($j = 0; $j < $wordLength; $j++) {
                    $this->crossword[$y + $j][$x] = $word[$j];
                }                                
            }
            $this->addToWordsOnGrid($x, $y, $wordLength, $direction, $word);
        }
    }
    
    private function addToWordsOnGrid($x, $y, $length, $direction, $word) {
        $this->wordsOnGrid[] = [
            'x' => $x,
            'y' => $y,
            'length' => $length,
            'direction' => $direction,
            'word' => $word
        ];
    }

    private function getValidPlace($wordLength) {
        do {
            $x = $this->getStartingX($wordLength);
            $y = $this->getStartingY($wordLength);
            $randDir = $this->getRandDirection();
        } while (!$this->isValidPlace($x, $y, $wordLength, $randDir));
        return [$x, $y, $randDir];
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

    private function getStartingX($wordLength) {
        return rand(0, $this->width - 1 - $wordLength);
    }

    private function getStartingY($wordLength) {
        return rand(0, $this->height - 1 - $wordLength);
    }

    public function getCrossword() {
        return $this->crossword;
    }
    
    public function getWordsOnGrid() {
        echo '<pre>';
        print_r($this->wordsOnGrid);
    }

}
