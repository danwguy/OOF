<?php

    class DateTimeUtil {


        const DATETIME_FORMAT_MYSQL = "Y-m-d H:i:s"; //  2099-12-31 23:59:59
        const DATE_FORMAT_MYSQL = "Y-m-d"; //  2099-12-31
        const DATETIME_FORMAT_DISPLAY_LONG = "n/j/Y g:i:s a"; //  12/31/1999 11:59:59 am, 1/1/99 1:01:01 am
        const DATETIME_FORMAT_DISPLAY_LONG_24_HOUR = "n/j/Y G:i:s"; //  12/31/1999 13:59:59 am, 1/1/99 13:01:01 am
        const DATETIME_FORMAT_DISPLAY_SHORT = "n/j/y g:i a"; //  12/31/99 11:59 am, 1/1/99 1:01 am
        const DATETIME_FORMAT_DISPLAY_SHORT_24_HOUR = "n/j/y G:i"; //  12/31/99 13:59 am, 1/1/99 13:01 am
        const DATETIME_FORMAT_TABLE_LONG = "m/d/Y h:i:s a"; //  12/31/1999 11:59:59 am, 01/01/1999 01:01:01 am
        const DATETIME_FORMAT_TABLE_SHORT = "m/d/y h:i a"; //  12/31/99 11:59 am, 01/01/99 01:01 am
        const DATETIME_FORMAT_TABLE_READABLE_LONG = "h:i a (m/d/y)";
        const DATETIME_FORMAT_LOGGING             = "Y-m-d H:i:s T"; //   2012-12-31 23:59:59 PST
        const DATE_FORMAT_DISPLAY = "m/d/y"; //  12/31/99
        const DATE_FORMAT_DISPLAY_LONG = "m/d/Y"; //  12/31/2012
        const DATE_FORMAT_CREDIT_CARD_EXPIRATION = "m/Y"; // 02/2012
        const TIME_FORMAT_DISPLAY_LONG = "g:i:s a"; //  11:59:59 am
        const TIME_FORMAT_DISPLAY_SHORT = "g:i a"; //  11:59 am

        const INTERVAL_FORMAT_DAYS = "%r%a"; //  58
        const INTERVAL_FORMAT_DISPLAY_DAYS_LONG = "%a days"; //  58 days
        const INTERVAL_FORMAT_DISPLAY_DAYS_SHORT = "%ad"; //  58d
        const INTERVAL_FORMAT_DISPLAY_TIME_LONG = "%h:%I%S"; //  11:59:59
        const INTERVAL_FORMAT_DISPLAY_TIME_SHORT = "%h%I"; //  11:59
        const INTERVAL_FORMAT_DISPLAY_FULL_LONG = "%a days %H<sub>h</sub>%I<sub>m</sub>%S<sub>s</sub>"; //  58 days 11d59m59s
        const INTERVAL_FORMAT_DISPLAY_FULL_SHORT = "%a<sub>d</sub>%H<sub>h</sub>%I<sub>m</sub>%S<sub>s</sub>"; //  58d11h59m59s

        const MAX_DATE = "9999-12-31";
        const MIN_DATE = "0000-01-01";

        public static $days = array(
            'short'   => array(
                'Sun',
                'Mon',
                'Tue',
                'Wed',
                'Thu',
                'Fri',
                'Sat'
            ),
            'long'    => array(
                'Sunday',
                'Monday',
                'Tuesday',
                'Wednesday',
                'Thursday',
                'Friday',
                'Saturday'
            ),
            'default' => array(
                'su',
                'mo',
                'tu',
                'we',
                'th',
                'fr',
                'sa'
            )
        );

        public static function add(DateTime $datetime, DateInterval $dateinterval) {
            $new_datetime = clone $datetime;

            return $new_datetime->add($dateinterval);
        }

        public static function sub(DateTime $datetime, DateInterval $dateinterval) {
            $new_datetime = clone $datetime;

            return $new_datetime->sub($dateinterval);
        }

        public static function format_date_string($date_string = null, $format = DateTimeUtil::DATETIME_FORMAT_MYSQL) {
            return DateTimeUtil::format(new DateTime($date_string), $format);
        }

        public static function format_date_interval(
            DateInterval $date_interval,
            $precision = 2,
            $long_labels = false,
            $signed = true) {
            $interval_string = $date_interval->format("%Y%M%D%H%I%S");
            if($long_labels) {
                $interval_keys = array("year", "month", "day", "hour", "minute", "second");
            } else {
                $interval_keys = array("y", "m", "d", "h", "m", "s");
            }
            $interval_parts = array();
            foreach($interval_keys as $num => $key) {
                $interval_part = substr($interval_string, $num * 2, 2);
                if(intval($interval_part) || $interval_parts) {
                    $interval_parts[$num . $key] = intval($interval_part);
                }
            }
            $interval_part_keys = array_keys($interval_parts);
            $formatted_string   = "";
            for($i = 0; $i < $precision; $i++) {
                if($interval_parts) {
                    $displayable_part  = array_shift($interval_parts);
                    $displayable_label =
                        ($long_labels ? " " : "") . substr(array_shift($interval_part_keys), 1) . ($long_labels
                                                                                                   && $displayable_part
                                                                                                      != 1 ? "s"
                            : "") . " ";
                    $formatted_string .= $displayable_part . $displayable_label;
                }
            }
            if($signed) {
                $formatted_string = $date_interval->format("%r") . $formatted_string;
            }

            return $formatted_string;
        }

        public static function get_age(DateTime $start_time, DateTime $end_time = null) {
            if(!$start_time) {
                return null;
            }
            if(!$end_time) {
                $end_time = new DateTime();
            }

            return $start_time->diff($end_time);
        }

        public static function correct_date($month = null, $year = null) {
            $date          = array();
            $date['month'] = ($month) ? $month : date('m');
            $date['year']  = ($year) ? $year : date('Y');

            while($date['month'] > 12) {
                $date['month'] -= 12;
                $date['year']++;
            }

            while($date['month'] <= 0) {
                $date['month'] += 12;
                $date['year']--;
            }

            if(strlen($date['month']) < 2) {
                $date['month'] = '0' . $date['month'];
            }
        }

        public static function get_days($type = null) {
            if(!$type || !isset(self::$days[$type])) {
                return self::$days['default'];
            }

            return self::$days[$type];
        }

        public static function get_business_days(DateTime $older_start_date, DateTime $newer_end_date = null) {
            $older_start_date = clone $older_start_date;

            if(!$newer_end_date) {
                $newer_end_date = new DateTime();
            }
            $newer_end_date = clone $newer_end_date;

            if(strtotime($older_start_date->format(DateTimeUtil::DATE_FORMAT_DISPLAY_LONG)) < strtotime(
                    $newer_end_date->format(DateTimeUtil::DATE_FORMAT_DISPLAY_LONG))
            ) {
                $count = 0;
                while(strtotime($older_start_date->format(DateTimeUtil::DATE_FORMAT_DISPLAY_LONG)) < strtotime(
                        $newer_end_date->format(DateTimeUtil::DATE_FORMAT_DISPLAY_LONG))) {
                    $older_start_date = DateTimeUtil::add($older_start_date, new DateInterval("P1D"));
                    if($older_start_date->format("N") < 6
                    ) { // We only count if the older date hasn't hit a weekend date 6-7
                        $count++;
                    }
                }

                return $count;
            } else {
                return null;
            }


        }

        public static function compare_dates(DateTime $datetime_a, DateTime $datetime_b) {

            $a = clone $datetime_a;
            $b = clone $datetime_b;
            $a->setTimezone(new DateTimeZone("UTC"));
            $b->setTimezone(new DateTimeZone("UTC"));
            $a->setTime(0, 0, 0);
            $b->setTime(0, 0, 0);

            return -1 * $a->diff($b)->format(DateTimeUtil::INTERVAL_FORMAT_DAYS);
        }

        public static function now() {
            return new DateTime();
        }

        public static function new_datetime($datetime_string) {
            if(is_a($datetime_string, "DateTime")) {
                return $datetime_string;
            } else {
                return new DateTime($datetime_string);
            }
        }

        public static function format($datetime, $format = DateTimeUtil::DATETIME_FORMAT_MYSQL) {
            if(is_a($datetime, "DateTime")) {
                /** @var DateTime $datetime */
                return $datetime->format($format);
            }

            return null;
        }

        public static function timespan($start, $end = null) {
            if(!$end) {
                $end = new DateTime();
            }
            if($start && !is_a($start, 'DateTime')) {
                $start = date('Y-m-d H:i:s', $start);
                $start = new DateTime($start);
            }
            if($end && !is_a($end, 'DateTime')) {
                $end = date('Y-m-d H:i:s', $end);
                $end = new DateTime($end);
            }
            $diff              = $end->diff($start);
            $return            = array();
            $return['years']   = ($diff->y > 1) ? $diff->y . " Years, " : $diff->y . " Year, ";
            $return['months']  = ($diff->m > 1) ? $diff->m . " Months, " : $diff->m . " Month, ";
            $return['days']    = ($diff->d > 1) ? $diff->d . " Days, " : $diff->d . " Day, ";
            $return['hours']   = ($diff->h > 1) ? $diff->h . " Hours, " : $diff->h . " Day, ";
            $return['minutes'] = ($diff->i > 1) ? $diff->i . " Minutes, " : $diff->i . " Minute, ";
            $return['seconds'] = ($diff->s > 1) ? $diff->s . " Seconds" : $diff->s . " Second";

            return " " . implode("", $return) . " ";
        }
    }