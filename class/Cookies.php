<?php

class Cookies {

    // zwraca tablice assoc z klucz-cookkie -> wartosc .. 
    // lub pusta tablice jesli nic 

    public static function isGrid() {
        if (isset($_COOKIE['crossword'])) {
            return true;
        } else {
            return false;
        }
    }
    
    public static function isReset() {
        if (isset($_POST['reset'])) {
            return true;
        } else {
            return false;
        }
    }

    public static function getGrid() {
        return ['crossword' => json_decode($_COOKIE['crossword'], true)];
    }

    public static function setGrid($crossword) {
        setcookie('crossword', json_encode($crossword));
    }

}
