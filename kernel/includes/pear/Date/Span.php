<?php
require_once 'Date.php';
require_once 'Date/Calc.php';

define('DATE_SPAN_INPUT_FORMAT_NNSV', 1);

$_DATE_SPAN_FORMAT  = '%C';


$_DATE_SPAN_INPUT_FORMAT = DATE_SPAN_INPUT_FORMAT_NNSV;

class Date_Span {

    var $day;
    var $hour;
    var $minute;
    var $second;

    function Date_Span($time = 0, $format = null)
    {
        $this->set($time, $format);
    }

    function set($time = 0, $format = null)
    {
        if ($time instanceof date_span) {
            return $this->copy($time);
        } elseif (($time instanceof date) and ($format instanceof date)) {
            return $this->setFromDateDiff($time, $format);
        } elseif (is_array($time)) {
            return $this->setFromArray($time);
        } elseif (is_string($time)) {
            return $this->setFromString($time, $format);
        } elseif (is_int($time)) {
            return $this->setFromSeconds($time);
        } else {
            return $this->setFromSeconds(0);
        }
    }

    function setFromArray($time)
    {
        if (!is_array($time)) {
            return false;
        }
        $tmp1 = new Date_Span;
        if (!$tmp1->setFromSeconds(@array_pop($time))) {
            return false;
        }
        $tmp2 = new Date_Span;
        if (!$tmp2->setFromMinutes(@array_pop($time))) {
            return false;
        }
        $tmp1->add($tmp2);
        if (!$tmp2->setFromHours(@array_pop($time))) {
            return false;
        }
        $tmp1->add($tmp2);
        if (!$tmp2->setFromDays(@array_pop($time))) {
            return false;
        }
        $tmp1->add($tmp2);
        return $this->copy($tmp1);
    }

    function setFromString($time, $format = null)
    {
        if (is_null($format)) {
            $format = $GLOBALS['_DATE_SPAN_INPUT_FORMAT'];
        }
        // If format is a string, it parses the string format.
        if (is_string($format)) {
            $str = '';
            $vars = array();
            $pm = 'am';
            $day = $hour = $minute = $second = 0;
            for ($i = 0; $i < strlen($format); $i++) {
                $char = $format{$i};
                if ($char == '%') {
                    $nextchar = $format{++$i};
                    switch ($nextchar) {
                        case 'c':
                            $str .= '%d, %d:%d:%d';
                            array_push(
                                $vars, 'day', 'hour', 'minute', 'second');
                            break;
                        case 'C':
                            $str .= '%d, %2d:%2d:%2d';
                            array_push(
                                $vars, 'day', 'hour', 'minute', 'second');
                            break;
                        case 'd':
                            $str .= '%f';
                            array_push($vars, 'day');
                            break;
                        case 'D':
                            $str .= '%d';
                            array_push($vars, 'day');
                            break;
                        case 'e':
                            $str .= '%f';
                            array_push($vars, 'hour');
                            break;
                        case 'f':
                            $str .= '%f';
                            array_push($vars, 'minute');
                            break;
                        case 'g':
                            $str .= '%f';
                            array_push($vars, 'second');
                            break;
                        case 'h':
                            $str .= '%d';
                            array_push($vars, 'hour');
                            break;
                        case 'H':
                            $str .= '%2d';
                            array_push($vars, 'hour');
                            break;
                        case 'm':
                            $str .= '%d';
                            array_push($vars, 'minute');
                            break;
                        case 'M':
                            $str .= '%2d';
                            array_push($vars, 'minute');
                            break;
                        case 'n':
                            $str .= "\n";
                            break;
                        case 'p':
                            $str .= '%2s';
                            array_push($vars, 'pm');
                            break;
                        case 'r':
                            $str .= '%2d:%2d:%2d %2s';
                            array_push(
                                $vars, 'hour', 'minute', 'second', 'pm');
                            break;
                        case 'R':
                            $str .= '%2d:%2d';
                            array_push($vars, 'hour', 'minute');
                            break;
                        case 's':
                            $str .= '%d';
                            array_push($vars, 'second');
                            break;
                        case 'S':
                            $str .= '%2d';
                            array_push($vars, 'second');
                            break;
                        case 't':
                            $str .= "\t";
                            break;
                        case 'T':
                            $str .= '%2d:%2d:%2d';
                            array_push($vars, 'hour', 'minute', 'second');
                            break;
                        case '%':
                            $str .= "%";
                            break;
                        default:
                            $str .= $char . $nextchar;
                    }
                } else {
                    $str .= $char;
                }
            }
            $vals = sscanf($time, $str);
            foreach ($vals as $i => $val) {
                if (is_null($val)) {
                    return false;
                }
                $$vars[$i] = $val;
            }
            if (strcasecmp($pm, 'pm') == 0) {
                $hour += 12;
            } elseif (strcasecmp($pm, 'am') != 0) {
                return false;
            }
            $this->setFromArray(array($day, $hour, $minute, $second));
        // If format is a integer, it uses a predefined format
        // detection method.
        } elseif (is_integer($format)) {
            switch ($format) {
                case DATE_SPAN_INPUT_FORMAT_NNSV:
                    $time = preg_split('/\D+/', $time);
                    switch (count($time)) {
                        case 0:
                            return $this->setFromArray(
                                array(0, 0, 0, 0));
                        case 1:
                            return $this->setFromArray(
                                array(0, $time[0], 0, 0));
                        case 2:
                            return $this->setFromArray(
                                array(0, $time[0], $time[1], 0));
                        case 3:
                            return $this->setFromArray(
                                array(0, $time[0], $time[1], $time[2]));
                        default:
                            return $this->setFromArray($time);
                    }
                    break;
            }
        }
        return false;
    }

    function setFromSeconds($seconds)
    {
        if ($seconds < 0) {
            return false;
        }
        $sec  = intval($seconds);
        $min  = floor($sec / 60);
        $hour = floor($min / 60);
        $day  = intval(floor($hour / 24));
        $this->second = $sec % 60;
        $this->minute = $min % 60;
        $this->hour   = $hour % 24;
        $this->day    = $day;
        return true;
    }

    function setFromMinutes($minutes)
    {
        return $this->setFromSeconds(round($minutes * 60));
    }

    function setFromHours($hours)
    {
        return $this->setFromSeconds(round($hours * 3600));
    }

    function setFromDays($days)
    {
        return $this->setFromSeconds(round($days * 86400));
    }

    function setFromDateDiff($date1, $date2)
    {
        if ((!$date1 instanceof date) or (!$date2 instanceof date)) {
            return false;
        }
        $date1->toUTC();
        $date2->toUTC();
        if ($date1->after($date2)) {
            list($date1, $date2) = array($date2, $date1);
        }
        $days = Date_Calc::dateDiff(
            $date1->getDay(), $date1->getMonth(), $date1->getYear(),
            $date2->getDay(), $date2->getMonth(), $date2->getYear()
        );
        $hours = $date2->getHour() - $date1->getHour();
        $mins  = $date2->getMinute() - $date1->getMinute();
        $secs  = $date2->getSecond() - $date1->getSecond();
        $this->setFromSeconds(
            $days * 86400 + $hours * 3600 + $mins * 60 + $secs
        );
        return true;
    }

		function copy($time)
    {
        if ($time instanceof date_span) {
            $this->second = $time->second;
            $this->minute = $time->minute;
            $this->hour   = $time->hour;
            $this->day    = $time->day;
            return true;
        } else {
            return false;
        }
    }

    function format($format = null)
    {
        if (is_null($format)) {
            $format = $GLOBALS['_DATE_SPAN_FORMAT'];
        }
        $output = '';
        for ($i = 0; $i < strlen($format); $i++) {
            $char = $format{$i};
            if ($char == '%') {
                $nextchar = $format{++$i};
                switch ($nextchar) {
                    case 'C':
                        $output .= sprintf(
                            '%d, %02d:%02d:%02d',
                            $this->day,
                            $this->hour,
                            $this->minute,
                            $this->second
                        );
                        break;
                    case 'd':
                        $output .= $this->toDays();
                        break;
                    case 'D':
                        $output .= $this->day;
                        break;
                    case 'e':
                        $output .= $this->toHours();
                        break;
                    case 'E':
                        $output .= floor($this->toHours());
                        break;
                    case 'f':
                        $output .= $this->toMinutes();
                        break;
                    case 'F':
                        $output .= floor($this->toMinutes());
                        break;
                    case 'g':
                        $output .= $this->toSeconds();
                        break;
                    case 'h':
                        $output .= $this->hour;
                        break;
                    case 'H':
                        $output .= sprintf('%02d', $this->hour);
                        break;
                    case 'i':
                        $hour =
                            ($this->hour + 1) > 12 ?
                            $this->hour - 12 :
                            $this->hour;
                        $output .= ($hour == 0) ? 12 : $hour;
                        break;
                    case 'I':
                        $hour =
                            ($this->hour + 1) > 12 ?
                            $this->hour - 12 :
                            $this->hour;
                        $output .= sprintf('%02d', $hour==0 ? 12 : $hour);
                        break;
                    case 'm':
                        $output .= $this->minute;
                        break;
                    case 'M':
                        $output .= sprintf('%02d',$this->minute);
                        break;
                    case 'n':
                        $output .= "\n";
                        break;
                    case 'p':
                        $output .= $this->hour >= 12 ? 'pm' : 'am';
                        break;
                    case 'P':
                        $output .= $this->hour >= 12 ? 'PM' : 'AM';
                        break;
                    case 'r':
                        $hour =
                            ($this->hour + 1) > 12 ?
                            $this->hour - 12 :
                            $this->hour;
                        $output .= sprintf(
                            '%02d:%02d:%02d %s',
                            $hour==0 ?  12 : $hour,
                            $this->minute,
                            $this->second,
                            $this->hour >= 12 ? 'pm' : 'am'
                        );
                        break;
                    case 'R':
                        $output .= sprintf(
                            '%02d:%02d', $this->hour, $this->minute
                        );
                        break;
                    case 's':
                        $output .= $this->second;
                        break;
                    case 'S':
                        $output .= sprintf('%02d', $this->second);
                        break;
                    case 't':
                        $output .= "\t";
                        break;
                    case 'T':
                        $output .= sprintf(
                            '%02d:%02d:%02d',
                            $this->hour, $this->minute, $this->second
                        );
                        break;
                    case '%':
                        $output .= "%";
                        break;
                    default:
                        $output .= $char . $nextchar;
                }
            } else {
                $output .= $char;
            }
        }
        return $output;
    }

    function toSeconds()
    {
        return $this->day * 86400 + $this->hour * 3600 +
            $this->minute * 60 + $this->second;
    }

    function toMinutes()
    {
        return $this->day * 1440 + $this->hour * 60 + $this->minute +
            $this->second / 60;
    }

    function toHours()
    {
        return $this->day * 24 + $this->hour + $this->minute / 60 +
            $this->second / 3600;
    }

    function toDays()
    {
        return $this->day + $this->hour / 24 + $this->minute / 1440 +
            $this->second / 86400;
    }

    function add($time)
    {
        return $this->setFromSeconds(
            $this->toSeconds() + $time->toSeconds()
        );
    }

    function subtract($time)
    {
        $sub = $this->toSeconds() - $time->toSeconds();
        if ($sub < 0) {
            $this->setFromSeconds(0);
        } else {
            $this->setFromSeconds($sub);
        }
    }

    function equal($time)
    {
        return $this->toSeconds() == $time->toSeconds();
    }

    function greaterEqual($time)
    {
        return $this->toSeconds() >= $time->toSeconds();
    }

    function lowerEqual($time)
    {
        return $this->toSeconds() <= $time->toSeconds();
    }

    function greater($time)
    {
        return $this->toSeconds() > $time->toSeconds();
    }

    function lower($time)
    {
        return $this->toSeconds() < $time->toSeconds();
    }

    function compare($time1, $time2)
    {
        if ($time1->equal($time2)) {
            return 0;
        } elseif ($time1->lower($time2)) {
            return -1;
        } else {
            return 1;
        }
    }

    function isEmpty()
    {
        return !$this->day && !$this->hour && !$this->minute && !$this->second;
    }

    function setDefaultInputFormat($format)
    {
        $old = $GLOBALS['_DATE_SPAN_INPUT_FORMAT'];
        $GLOBALS['_DATE_SPAN_INPUT_FORMAT'] = $format;
        return $old;
    }

    function getDefaultInputFormat()
    {
        return $GLOBALS['_DATE_SPAN_INPUT_FORMAT'];
    }

    function setDefaultFormat($format)
    {
        $old = $GLOBALS['_DATE_SPAN_FORMAT'];
        $GLOBALS['_DATE_SPAN_FORMAT'] = $format;
        return $old;
    }

    function getDefaultFormat()
    {
        return $GLOBALS['_DATE_SPAN_FORMAT'];
    }

    function __clone() {
        $c = get_class($this);
        $s = new $c;
        $s->day    = $this->day;
        $s->hour   = $this->hour;
        $s->minute = $this->minute;
        $s->second = $this->second;
        return $s;
    }
}

?>
