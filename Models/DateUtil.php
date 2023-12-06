<?php

class DateUtil {
       public static function transformDay($dayNumber) {
        switch ($dayNumber) {
            case 1:
                $giorno = 'Luned&igrave;';
                break;
            case 2:
                $giorno = 'Marted&igrave;';
                break;
            case 3:
                $giorno = 'Mercoled&igrave;';
                break;
            case 4:
                $giorno = 'Gioved&igrave;';
                break;
            case 5:
                $giorno = 'Venerd&igrave;';
                break;
            case 6:
                $giorno = 'Sabato';
                break;
            case 7:
                $giorno = 'Domenica';
                break;
        }
        return $giorno;
    }
}
