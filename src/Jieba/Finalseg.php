<?php

namespace Jieba;

/**
 * Class Finalseg
 *
 * @package Jieba
 */
class Finalseg
{
    public static $prob_start = [];
    public static $prob_trans = [];
    public static $prob_emit  = [];

    /**
     * Static method init
     *
     * @param array $options # other options
     *
     * @return void
     */
    public static function init(array $options = [])
    {
        $defaults = array(
            'mode'=>'default',
        );

        $options = array_merge($defaults, $options);

        self::$prob_start = self::loadModel(dirname(__DIR__) . '/model/prob_start.json');
        self::$prob_trans = self::loadModel(dirname(__DIR__) . '/model/prob_trans.json');
        self::$prob_emit  = self::loadModel(dirname(__DIR__) . '/model/prob_emit.json');
    }

    /**
     * Static method loadModel
     *
     * @param string $f_name # input f_name
     * @param array $options # other options
     *
     * @return mixed
     */
    public static function loadModel(string $f_name, array $options = [])
    {

        $defaults = array(
            'mode'=>'default',
        );

        $options = array_merge($defaults, $options);

        return json_decode(file_get_contents($f_name), true);
    }

    /**
     * Static method viterbi
     *
     * @param string $sentence # input sentence
     * @param array  $options  # other options
     *
     * @return array
     */
    public static function viterbi(string $sentence, array $options = []): array
    {

        $defaults = array(
            'mode'=>'default',
        );

        $options = array_merge($defaults, $options);

        $obs = $sentence;
        $states = array('B', 'M', 'E', 'S');
        $V    = [];
        $V[0] = [];
        $path = [];

        foreach ($states as $key => $state) {
            $y = $state;
            $c = mb_substr($obs, 0, 1, 'UTF-8');
            $prob_emit = 0.0;
            if (isset(self::$prob_emit[$y][$c])) {
                $prob_emit = self::$prob_emit[$y][$c];
            } else {
                $prob_emit = MIN_FLOAT;
            }
            $V[0][$y] = self::$prob_start[$y] + $prob_emit;
            $path[$y] = $y;
        }

        for ($t=1; $t<mb_strlen($obs, 'UTF-8'); $t++) {
            $c = mb_substr($obs, $t, 1, 'UTF-8');
            $V[$t] = [];
            $newpath = [];
            foreach ($states as $key => $state) {
                $y = $state;
                $temp_prob_array = [];
                foreach ($states as $key => $state0) {
                    $y0 = $state0;
                    $prob_trans = 0.0;
                    if (isset(self::$prob_trans[$y0][$y])) {
                        $prob_trans = self::$prob_trans[$y0][$y];
                    } else {
                        $prob_trans = MIN_FLOAT;
                    }
                    $prob_emit = 0.0;
                    if (isset(self::$prob_emit[$y][$c])) {
                        $prob_emit = self::$prob_emit[$y][$c];
                    } else {
                        $prob_emit = MIN_FLOAT;
                    }
                    $temp_prob_array[$y0] = $V[$t-1][$y0] + $prob_trans + $prob_emit;
                }
                arsort($temp_prob_array);
                $max_prob = reset($temp_prob_array);
                $max_key = key($temp_prob_array);
                $V[$t][$y] = $max_prob;
                if (is_array($path[$max_key])) {
                    $newpath[$y] = [];
                    foreach ($path[$max_key] as $key => $path_value) {
                        array_push($newpath[$y], $path_value);
                    }
                    array_push($newpath[$y], $y);
                } else {
                    $newpath[$y] = array($path[$max_key], $y);
                }
            }
            $path = $newpath;
        }

        $es_states = array('E','S');
        $temp_prob_array = [];
        $len = mb_strlen($obs, 'UTF-8');
        foreach ($es_states as $key => $state) {
            $y = $state;
            $temp_prob_array[$y] = $V[$len-1][$y];
        }
        arsort($temp_prob_array);
        $prob = reset($temp_prob_array);
        $state = key($temp_prob_array);

        return array("prob"=>$prob, "pos_list"=>$path[$state]);
    }

    /**
     * Static method __cut
     *
     * @param string $sentence # input sentence
     * @param array  $options  # other options
     *
     * @return array
     */
    public static function __cut(string $sentence, array $options = []): array
    {

        $defaults = array(
            'mode'=>'default',
        );

        $options = array_merge($defaults, $options);

        $words = [];

        $viterbi_array = self::viterbi($sentence);
        $prob = $viterbi_array['prob'];
        $pos_list = $viterbi_array['pos_list'];

        $begin = 0;
        $next = 0;
        $len = mb_strlen($sentence, 'UTF-8');

        for ($i=0; $i<$len; $i++) {
            $char = mb_substr($sentence, $i, 1, 'UTF-8');
            $pos = $pos_list[$i];
            if ($pos=='B') {
                $begin = $i;
            } elseif ($pos=='E') {
                array_push($words, mb_substr($sentence, $begin, (($i+1)-$begin), 'UTF-8'));
                $next = $i+1;
            } elseif ($pos=='S') {
                array_push($words, $char);
                $next = $i+1;
            }
        }

        if ($next<$len) {
            array_push($words, mb_substr($sentence, $next, null, 'UTF-8'));
        }

        return $words;
    }


    /**
     * Static method cut
     *
     * @param string  $sentence # input sentence
     * @param array   $options  # other options
     *
     * @return array $seg_list
     */
    public static function cut(string $sentence, array $options = []): array
    {

        $defaults = array(
            'mode'=>'default',
        );

        $options = array_merge($defaults, $options);

        $seg_list = [];

        $re_han_pattern = '([\x{4E00}-\x{9FA5}]+)';
        $re_skip_pattern = '([a-zA-Z0-9+#\r\n]+)';
        preg_match_all(
            '/('.$re_han_pattern.'|'.$re_skip_pattern.')/u',
            $sentence,
            $matches,
            PREG_PATTERN_ORDER
        );
        $blocks = $matches[0];

        foreach ($blocks as $blk) {
            if (preg_match('/'.$re_han_pattern.'/u', $blk)) {
                $words = self::__cut($blk);

                foreach ($words as $word) {
                    array_push($seg_list, $word);
                }
            } else {
                array_push($seg_list, $blk);
            }
        }

        return $seg_list;
    }
}
