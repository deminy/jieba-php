<?php

namespace Jieba;

use Cache\Adapter\Common\AbstractCachePool;
use Jieba\Traits\CachePoolTrait;
use Jieba\Traits\LoggerTrait;

/**
 * Class Posseg
 *
 * @package Jieba
 */
class Posseg
{
    use CachePoolTrait, LoggerTrait;

    /**
     * @var array
     */
    public $prob_start       = [];
    /**
     * @var array
     */
    public $prob_trans       = [];
    /**
     * @var array
     */
    public $prob_emit        = [];
    /**
     * @var array
     */
    public $char_state       = [];
    /**
     * @var array
     */
    public $word_tag         = [];
    /**
     * @var array
     */
    public $pos_tag_readable = [];

    /**
     * @var Jieba
     */
    protected $jieba;

    /**
     * Posseg constructor.
     *
     * @param Jieba $jieba
     * @param AbstractCachePool|null $cachePool
     */
    public function __construct(Jieba $jieba, AbstractCachePool $cachePool = null)
    {
        $this
            ->setJieba($jieba)
            ->setLogger($this->jieba->getLogger())
            ->setCachePool($cachePool ?: CacheFactory::getCachePool())
            ->init();
    }

    /**
     * @return Posseg
     */
    protected function init(): Posseg
    {
        $this->prob_start = CacheFactory::getModel($this->getCachePool(), CacheFactory::MODEL_POS_PROB_START);
        $this->prob_trans = CacheFactory::getModel($this->getCachePool(), CacheFactory::MODEL_POS_PROB_TRANS);
        $this->prob_emit  = CacheFactory::getModel($this->getCachePool(), CacheFactory::MODEL_POS_PROB_EMIT);
        $this->char_state = CacheFactory::getModel($this->getCachePool(), CacheFactory::MODEL_POS_CHAR_STATE);

        // TODO: Here property \Jieba::$dictname was used.
        // TODO: performance improvement with cache
        // @see \Jieba::$dictname
        // @see https://bitbucket.org/deminy/jieba-php/src/005f8d6440fd55189f386ccfe438ec6ac41c53c4/src/Jieba/Posseg.php?at=old&fileviewer=file-view-default#Posseg.php-39
        DictHelper::addWordTags($this->getJieba()->getOptions()->getDict()->getDictFilePath(), $this->word_tag);

        // TODO: Here property \Jieba::$user_dictname was used.
        // TODO: performance improvement with cache
        // @see \Jieba::user_dictname
        // @see https://bitbucket.org/deminy/jieba-php/src/005f8d6440fd55189f386ccfe438ec6ac41c53c4/src/Jieba/Posseg.php?at=old&fileviewer=file-view-default#Posseg.php-52
        foreach (Helper::getUserDictNames() as $userDictName) {
            DictHelper::addWordTags($userDictName, $this->word_tag);
        }

        $this->pos_tag_readable = CacheFactory::get(
            $this->getCachePool(),
            'pos_tag_readable',
            function () {
                return DictHelper::getPosTagReadable(Helper::getDictFilePath('pos_tag_readable.txt'));
            }
        );

        return $this;
    }

    /**
     * @param array $t_state_v
     * @param int   $top_k
     * @return array
     */
    public function getTopStates(array $t_state_v, int $top_k = 4): array
    {
        arsort($t_state_v);

        $top_states = array_slice($t_state_v, 0, $top_k);

        return $top_states;
    }

    /**
     * @param string $sentence # input sentence
     * @return array
     */
    public function viterbi(string $sentence): array
    {
        $obs = $sentence;
        $states = $this->char_state;
        $V = [];
        $V[0] = [];
        $mem_path = [];
        $mem_path[0] = [];
        $all_states = array_keys($this->prob_trans);

        $c = mb_substr($obs, 0, 1);

        if (isset($states[$c]) && !empty($states[$c])) {
            $c_states = $states[$c];
        } else {
            $c_states = $all_states;
        }

        foreach ($c_states as $key => $state) {
            $y = $state;
            $c = mb_substr($obs, 0, 1);
            $prob_emit = 0.0;
            if (isset($this->prob_emit[$y][$c])) {
                $prob_emit = $this->prob_emit[$y][$c];
            } else {
                $prob_emit = Constant::MIN_FLOAT;
            }
            $V[0][$y] = $this->prob_start[$y] + $prob_emit;
            $mem_path[0][$y] = '';
        }

        for ($t=1; $t<mb_strlen($obs); $t++) {
            $c = mb_substr($obs, $t, 1);
            $V[$t] = [];
            $mem_path[$t] = [];

            $prev_states = array_keys($this->getTopStates($V[$t-1]));

            $prev_mem_path = array_keys($mem_path[$t-1]);

            $prev_states = [];

            foreach ($prev_mem_path as $mem_path_state) {
                if (count($this->prob_trans[$mem_path_state])>0) {
                    array_push($prev_states, $mem_path_state);
                }
            }

            $prev_states_expect_next = [];

            foreach ($prev_states as $prev_state) {
                $prev_states_expect_next
                    = array_unique(
                        array_merge(
                            $prev_states_expect_next,
                            array_keys($this->prob_trans[$prev_state])
                        )
                    );
            }

            if (isset($states[$c])) {
                $obs_states = $states[$c];
            } else {
                $obs_states = $all_states;
            }

            $obs_states = array_intersect($obs_states, $prev_states_expect_next);

            if (count($obs_states)==0) {
                $obs_states = $all_states;
            }


            foreach ($obs_states as $y) {
                $temp_prob_array = [];
                foreach ($prev_states as $y0) {
                    $prob_trans = 0.0;
                    if (isset($this->prob_trans[$y0][$y])) {
                        $prob_trans = $this->prob_trans[$y0][$y];
                    } else {
                        $prob_trans = Constant::MIN_FLOAT;
                    }
                    $prob_emit = 0.0;
                    if (isset($this->prob_emit[$y][$c])) {
                        $prob_emit = $this->prob_emit[$y][$c];
                    } else {
                        $prob_emit = Constant::MIN_FLOAT;
                    }
                    $temp_prob_array[$y0] = $V[$t-1][$y0] + $prob_trans + $prob_emit;
                }
                arsort($temp_prob_array);
                $max_prob = reset($temp_prob_array);
                $max_key = key($temp_prob_array);
                $V[$t][$y] = $max_prob;
                $mem_path[$t][$y] = $max_key;
            }
        }

        $last = [];
        $mem_path_end_keys = array_keys(end($mem_path));

        foreach ($mem_path_end_keys as $y) {
            $end_array = end($V);
            $last[$y] = $end_array[$y];
        }

        arsort($last);
        $return_prob = reset($last);
        $return_prob_key = key($last);

        $obs_length = mb_strlen($obs);

        $route = [];
        for ($t=0; $t<$obs_length; $t++) {
            array_push($route, 'None');
        }

        $i = $obs_length-1;

        while ($i >= 0) {
            $route[$i] = $return_prob_key;
            $return_prob_key = $mem_path[$i][$return_prob_key];
            $i-=1;
        }

        return array("prob"=>$return_prob, "pos_list"=>$route);
    }

    /**
     * @param string $sentence # input sentence
     * @return array
     */
    public function __cut(string $sentence): array
    {
        $words = [];

        $viterbi_array = $this->viterbi($sentence);

        $pos_list = $viterbi_array['pos_list'];

        $begin = 0;
        $next = 0;
        $len = mb_strlen($sentence);

        for ($i=0; $i<$len; $i++) {
            $char = mb_substr($sentence, $i, 1);
            eval('$pos_array = array'.$pos_list[$i].';');
            $pos = $pos_array[0];

            switch ($pos) {
                case Constant::B:
                    $begin = $i;
                    break;
                case Constant::E:
                    eval('$this_pos_array = array'.$pos_list[$i].';');
                    $this_pos = $this_pos_array[1];
                    $this_word_pair = array(
                        "word"=>mb_substr($sentence, $begin, (($i+1)-$begin)),
                        "tag"=>$this_pos
                    );
                    array_push($words, $this_word_pair);
                    $next = $i+1;
                    break;
                case Constant::S:
                    eval('$this_pos_array = array'.$pos_list[$i].';');
                    $this_pos = $this_pos_array[1];
                    $this_word_pair = array(
                        "word"=>$char,
                        "tag"=>$this_pos
                    );
                    array_push($words, $this_word_pair);
                    $next = $i+1;
                    break;
                default:
                    break;
            }
        }

        if ($next<$len) {
            eval('$this_pos_array = array'.$pos_list[$next].';');
            $this_pos = $this_pos_array[1];
            $this_word_pair = array(
                "word"=>mb_substr($sentence, $next, null),
                "tag"=>$this_pos
            );
            array_push($words, $this_word_pair);
        }

        return $words;
    }

    /**
     * @param string $sentence # input sentence
     * @return array
     */
    public function __cutDetail(string $sentence): array
    {
        $words = [];

        $re_han_pattern = '([\x{4E00}-\x{9FA5}]+)';
        $re_skip_pattern = '([a-zA-Z0-9+#\r\n]+)';
        $re_punctuation_pattern = '([\x{ff5e}\x{ff01}\x{ff08}\x{ff09}\x{300e}'.
                                    '\x{300c}\x{300d}\x{300f}\x{3001}\x{ff1a}\x{ff1b}'.
                                    '\x{ff0c}\x{ff1f}\x{3002}]+)';
        $re_eng_pattern = '[a-zA-Z+#]+';
        $re_num_pattern = '[0-9]+';

        preg_match_all(
            '/('.$re_han_pattern.'|'.$re_skip_pattern.'|'.$re_punctuation_pattern.')/u',
            $sentence,
            $matches,
            PREG_PATTERN_ORDER
        );
        $blocks = $matches[0];

        foreach ($blocks as $blk) {
            if (preg_match('/'.$re_han_pattern.'/u', $blk)) {
                $blk_words = $this->__cut($blk);
                foreach ($blk_words as $blk_word) {
                    array_push($words, $blk_word);
                }
            } elseif (preg_match('/'.$re_skip_pattern.'/u', $blk)) {
                if (preg_match('/'.$re_num_pattern.'/u', $blk)) {
                    array_push($words, array("word"=>$blk, "tag"=>"m"));
                } elseif (preg_match('/'.$re_eng_pattern.'/u', $blk)) {
                    array_push($words, array("word"=>$blk, "tag"=>"eng"));
                }
            } elseif (preg_match('/'.$re_punctuation_pattern.'/u', $blk)) {
                array_push($words, array("word"=>$blk, "tag"=>"w"));
            }
        }

        return $words;
    }

    /**
     * @param string $sentence # input sentence
     * @return array
     */
    public function __cutDAG(string $sentence): array
    {
        $words = [];

        $N = mb_strlen($sentence);
        $this->getJieba()->calc($sentence, $this->getJieba()->getDAG($sentence));

        $x = 0;
        $buf = '';

        while ($x < $N) {
            $current_route_keys = array_keys($this->getJieba()->getRouteByKey($x));
            $y = $current_route_keys[0]+1;
            $l_word = mb_substr($sentence, $x, ($y-$x));

            if (($y-$x)==1) {
                $buf = $buf.$l_word;
            } else {
                if (mb_strlen($buf)>0) {
                    if (mb_strlen($buf)==1) {
                        if (isset($this->word_tag[$buf])) {
                            $buf_tag = $this->word_tag[$buf];
                        } else {
                            $buf_tag = "x";
                        }
                        array_push(
                            $words,
                            array("word"=>$buf, "tag"=>$buf_tag)
                        );
                        $buf = '';
                    } else {
                        $regognized = $this->__cutDetail($buf);
                        foreach ($regognized as $key => $word) {
                            array_push($words, $word);
                        }
                        $buf = '';
                    }
                }

                if (isset($this->word_tag[$l_word])) {
                    $buf_tag = $this->word_tag[$l_word];
                } else {
                    $buf_tag = "x";
                }
                array_push(
                    $words,
                    array("word"=>$l_word, "tag"=>$buf_tag)
                );
            }
            $x = $y;
        }

        if (mb_strlen($buf)>0) {
            if (mb_strlen($buf)==1) {
                if (isset($this->word_tag[$buf])) {
                    $buf_tag = $this->word_tag[$buf];
                } else {
                    $buf_tag = "x";
                }
                array_push(
                    $words,
                    array("word"=>$buf, "tag"=>$buf_tag)
                );
            } else {
                $regognized = $this->__cutDetail($buf);
                foreach ($regognized as $key => $word) {
                    array_push($words, $word);
                }
            }
        }

        return $words;
    }

    /**
     * @param string  $sentence # input sentence
     * @return array
     */
    public function cut(string $sentence): array
    {
        $seg_list = [];

        $re_han_pattern = '([\x{4E00}-\x{9FA5}]+)';
        $re_skip_pattern = '([a-zA-Z0-9+#\r\n]+)';
        $re_punctuation_pattern = '([\x{ff5e}\x{ff01}\x{ff08}\x{ff09}\x{300e}'.
                                    '\x{300c}\x{300d}\x{300f}\x{3001}\x{ff1a}\x{ff1b}'.
                                    '\x{ff0c}\x{ff1f}\x{3002}]+)';
        $re_eng_pattern = '[a-zA-Z+#]+';
        $re_num_pattern = '[0-9]+';

        preg_match_all(
            '/('.$re_han_pattern.'|'.$re_skip_pattern.'|'.$re_punctuation_pattern.')/u',
            $sentence,
            $matches,
            PREG_PATTERN_ORDER
        );
        $blocks = $matches[0];

        foreach ($blocks as $blk) {
            if (preg_match('/'.$re_han_pattern.'/u', $blk)) {
                $words = Posseg::__cutDAG($blk);

                foreach ($words as $word) {
                    array_push($seg_list, $word);
                }
            } elseif (preg_match('/'.$re_skip_pattern.'/u', $blk)) {
                if (preg_match('/'.$re_num_pattern.'/u', $blk)) {
                    array_push($seg_list, array("word"=>$blk, "tag"=>"m"));
                } elseif (preg_match('/'.$re_eng_pattern.'/u', $blk)) {
                    array_push($seg_list, array("word"=>$blk, "tag"=>"eng"));
                }
            } elseif (preg_match('/'.$re_punctuation_pattern.'/u', $blk)) {
                array_push($seg_list, array("word"=>$blk, "tag"=>"w"));
            }
        }

        return $seg_list;
    }

    /**
     * @param array $seg_list
     * @return array
     */
    public function posTagReadable(array $seg_list): array
    {
        foreach ($seg_list as $seg) {
            $seg['tag_readable'] = $this->pos_tag_readable[$seg['tag']];
        }

        return $seg_list;
    }

    /**
     * @return Jieba
     */
    public function getJieba(): Jieba
    {
        return $this->jieba;
    }

    /**
     * @param Jieba $jieba
     * @return Posseg
     */
    public function setJieba(Jieba $jieba): Posseg
    {
        $this->jieba = $jieba;

        return $this;
    }
}
