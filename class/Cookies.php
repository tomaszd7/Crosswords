<?php

class Cookies {
    public static $words = [];

    // zwraca tablice assoc z klucz-cookkie -> wartosc .. 
    // lub pusta tablice jesli nic 

    public static function isCookies() {
        if (isset($_COOKIE['words'])) {
            self::$words = json_decode($_COOKIE['words'], true);
            return true;
        }
        return false;
    }
    
    public static function isUserWords()  {
        if (isset($_GET['0']) && count($_GET[0])>0) {
            self::$words = [];
            for ($i = 0; $i < count($_GET); $i++) {
                if (strlen(trim($_GET[$i])) >0) {
//                    var_dump($_GET[$i], strlen(trim($_GET[$i])));
                    self::$words[] = trim($_GET[$i]);
                }
            }
            setcookie('words', json_encode(self::$words));
            header('Location: index.php');
            return true;
        }
        return false;;
    }
    
    
    public static function isReset() {
        if (isset($_POST['reset'])) {
            return true;
        } else {
            return false;
        }
    }

//    public static function isGrid() {
//        if (isset($_COOKIE['crossword'])) {
//            return true;
//        } else {
//            return false;
//        }
//    }
//    public static function getGrid() {
//        return ['crossword' => json_decode($_COOKIE['crossword'], true)];
//    }
//
//    public static function setGrid($crossword) {
//        setcookie('crossword', json_encode($crossword));
//    }    
    
}
