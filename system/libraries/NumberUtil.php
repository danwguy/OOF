<?php


    class NumberUtil {


        const ROUND_DOWN  = 'down';
        const ROUND_UP    = 'up';
        const ROUND_CLOSE = 'close';

        private static $ordinals = array(0  => 'th', 1 => 'st', 2 => 'nd', 3 => 'rd', 4 => 'th', 5 => 'th', 6 => 'th',
                                         7  => 'th', 8 => 'th', 9 => 'th', 10 => 'th', 11 => 'th', 12 => 'th',
                                         13 => 'th', 14 => 'th', 15 => 'th', 16 => 'th', 17 => 'th', 18 => 'th',
                                         19 => 'th', 20 => 'th', 21 => 'st', 22 => 'nd', 23 => 'rd', 24 => 'th',
                                         25 => 'th', 26 => 'th', 27 => 'th', 28 => 'th', 29 => 'th', 30 => 'th',
                                         31 => 'st', 32 => 'nd', 33 => 'rd', 34 => 'th', 35 => 'th', 36 => 'th',
                                         37 => 'th', 38 => 'th', 39 => 'th', 40 => 'th', 41 => 'st', 42 => 'nd',
                                         43 => 'rd', 44 => 'th', 45 => 'th', 46 => 'th', 47 => 'th', 48 => 'th',
                                         49 => 'th', 50 => 'th', 51 => 'st', 52 => 'nd', 53 => 'rd', 54 => 'th',
                                         55 => 'th', 56 => 'th', 57 => 'th', 58 => 'th', 59 => 'th', 60 => 'th',
                                         61 => 'st', 62 => 'nd', 63 => 'rd', 64 => 'th', 65 => 'th', 66 => 'th',
                                         67 => 'th', 68 => 'th', 69 => 'th', 70 => 'th', 71 => 'st', 72 => 'nd',
                                         73 => 'rd', 74 => 'th', 75 => 'th', 76 => 'th', 77 => 'th', 78 => 'th',
                                         79 => 'th', 80 => 'th', 81 => 'st', 82 => 'nd', 83 => 'rd', 84 => 'th',
                                         85 => 'th', 86 => 'th', 87 => 'th', 88 => 'th', 89 => 'th', 90 => 'th',
                                         91 => 'st', 92 => 'nd', 93 => 'rd', 94 => 'th', 95 => 'th', 96 => 'th',
                                         97 => 'th', 98 => 'th', 99 => 'th');

        public static function to_money($amount, $currency = "\$", $currency_mark_before_number = true) {
            $negative = '';
            if(is_numeric($amount)) {
                if($amount < 0) {
                    $negative = "-";
                    $amount   = abs($amount);
                }
                $amount = number_format($amount, 2);
            }
            if(preg_match("/[A-Za-z]+/", $currency)) {
                $space = " ";
            } else {
                $space = "";
            }

            return ($currency_mark_before_number)
                ? $negative . $currency . $space . $amount
                : $negative . $amount . $space . $currency;
        }

        public static function is_valid_number($string) {
            return preg_match("/^[+-]?((.\d+)|((\d+)|(\d{1,3}(,\d{3})+))(\.\d+)?)$/", $string);
        }

        public static function to_ordinal($number_string, $superscript_ordinal = false) {
            if(NumberUtil::is_valid_number($number_string)) {
                $truncated_number = preg_replace("/\.\d*/", "", $number_string);

                return $number_string . ($superscript_ordinal ? "<sup>" : "") . NumberUtil::$ordinals[substr(
                    $truncated_number,
                    -2)] . ($superscript_ordinal ? "</sup>" : "");
            }

            return $number_string;
        }

        public static function round($number, $to_nearest, $rounding_mode = self::ROUND_CLOSE) {
            if($to_nearest == 0) {
                return $number;
            }
            switch($rounding_mode) {
                case self::ROUND_DOWN:
                    return $number - $number % $to_nearest;
                    break;
                case self::ROUND_UP;
                    return $number - $number % $to_nearest + $number;
                    break;
                case self::ROUND_CLOSE:
                    return $to_nearest * floor($number / $to_nearest + 0.5);
                    break;
            }
        }

    }