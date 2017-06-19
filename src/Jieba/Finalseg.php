<?php

namespace Jieba;

use Jieba\Traits\SingletonTrait;

/**
 * Class Finalseg
 *
 * @package Jieba
 */
class Finalseg
{
    use SingletonTrait;

    /**
     * @var array
     */
    protected $probStart = [];
    /**
     * @var array
     */
    protected $probTrans = [];
    /**
     * @var array
     */
    protected $probEmit  = [];

    /**
     * Finalseg constructor.
     * @todo use cache for performance improvement.
     */
    protected function __construct()
    {
        $this
            ->setProbStart(Helper::loadModel('prob_start.json'))
            ->setProbTrans(Helper::loadModel('prob_trans.json'))
            ->setProbEmit(Helper::loadModel('prob_emit.json'));
    }

    /**
     * @param string  $sentence # input sentence
     * @return array
     */
    public function cut(string $sentence): array
    {
        $re_han_pattern  = '([\x{4E00}-\x{9FA5}]+)';
        $re_skip_pattern = '([a-zA-Z0-9+#\r\n]+)';
        preg_match_all(
            '/('.$re_han_pattern.'|'.$re_skip_pattern.')/u',
            $sentence,
            $matches,
            PREG_PATTERN_ORDER
        );
        $blocks = $matches[0];

        $seg_list = [];
        foreach ($blocks as $blk) {
            if (preg_match('/'.$re_han_pattern.'/u', $blk)) {
                $words = $this->__cut($blk);

                foreach ($words as $word) {
                    array_push($seg_list, $word);
                }
            } else {
                array_push($seg_list, $blk);
            }
        }

        return $seg_list;
    }

    /**
     * @return array
     */
    public function getProbStart(): array
    {
        return $this->probStart;
    }

    /**
     * @param array $probStart
     * @return $this
     */
    public function setProbStart(array $probStart): Finalseg
    {
        $this->probStart = $probStart;

        return $this;
    }

    /**
     * @return array
     */
    public function getProbTrans(): array
    {
        return $this->probTrans;
    }

    /**
     * @param array $probTrans
     * @return $this
     */
    public function setProbTrans(array $probTrans): Finalseg
    {
        $this->probTrans = $probTrans;

        return $this;
    }

    /**
     * @return array
     */
    public function getProbEmit(): array
    {
        return $this->probEmit;
    }

    /**
     * @param array $probEmit
     * @return $this
     */
    public function setProbEmit(array $probEmit): Finalseg
    {
        $this->probEmit = $probEmit;

        return $this;
    }

    /**
     * @param string $sentence # input sentence
     * @return array
     */
    protected function __cut(string $sentence): array
    {
        $words = [];

        $viterbi_array = $this->viterbi($sentence);
        $pos_list = $viterbi_array['pos_list'];

        $begin = 0;
        $next  = 0;
        $len   = mb_strlen($sentence, 'UTF-8');

        for ($i=0; $i<$len; $i++) {
            $char = mb_substr($sentence, $i, 1, 'UTF-8');
            switch ($pos_list[$i]) {
                case 'B':
                    $begin = $i;
                    break;
                case 'E':
                    array_push($words, mb_substr($sentence, $begin, (($i+1)-$begin), 'UTF-8'));
                    $next = $i+1;
                    break;
                case 'S':
                    array_push($words, $char);
                    $next = $i+1;
                    break;
                default:
                    break;
            }
        }

        if ($next < $len) {
            array_push($words, mb_substr($sentence, $next, null, 'UTF-8'));
        }

        return $words;
    }

    /**
     * @param string $sentence # input sentence
     * @return array
     */
    protected function viterbi(string $sentence): array
    {
        $obs = $sentence;
        $states = array('B', 'M', 'E', 'S');
        $V    = [];
        $V[0] = [];
        $path = [];

        foreach ($states as $key => $state) {
            $y = $state;
            $c = mb_substr($obs, 0, 1, 'UTF-8');
            $prob_emit = 0.0;
            if (isset($this->probEmit[$y][$c])) {
                $prob_emit = $this->probEmit[$y][$c];
            } else {
                $prob_emit = Constant::MIN_FLOAT;
            }
            $V[0][$y] = $this->probStart[$y] + $prob_emit;
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
                    if (isset($this->probTrans[$y0][$y])) {
                        $prob_trans = $this->probTrans[$y0][$y];
                    } else {
                        $prob_trans = Constant::MIN_FLOAT;
                    }
                    if (isset($this->probEmit[$y][$c])) {
                        $prob_emit = $this->probEmit[$y][$c];
                    } else {
                        $prob_emit = Constant::MIN_FLOAT;
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
}
