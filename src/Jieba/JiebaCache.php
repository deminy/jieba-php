<?php

namespace Jieba;

/**
 * Class JiebaCache
 *
 * @package Jieba
 */
class JiebaCache
{
    /**
     * @var float
     */
    public $total = 0.0;
    /**
     * @var array
     */
    public $trie = [];
    /**
     * @var array
     */
    public $FREQ = [];
    /**
     * @var float
     */
    public $min_freq = 0.0;
    /**
     * @var array
     */
    public $route = [];
    /**
     * @var string
     */
    public $dictname;
    /**
     * @var array
     */
    public $user_dictname=[];

    /**
     * @param array $options # other options
     *
     * @return void
     */
    public function init(array $options = [])
    {
        $defaults = array(
            'mode'=>'default',
            'dict'=>'normal'
        );

        $options = array_merge($defaults, $options);

        if ($options['mode']=='test') {
            echo "Building Trie...\n";
        }

        if ($options['dict']=='small') {
            $f_name = "dict.small.txt";
            $this->dictname="dict.small.txt";
        } elseif ($options['dict']=='big') {
            $f_name = "dict.big.txt";
            $this->dictname="dict.big.txt";
        } else {
            $f_name = "dict.txt";
            $this->dictname="dict.txt";
        }

        $t1 = microtime(true);
        $this->trie = Jieba::genTrie(dirname(__DIR__)."/dict/".$f_name);
        if ($options['mode']=='test') {
            echo "loading model cost ".(microtime(true) - $t1)." seconds.\n";
            echo "Trie has been built succesfully.\n";
        }
    }

    /**
     * @param string $sentence # input sentence
     * @param array  $DAG      # DAG
     * @return array
     */
    public function calc(string $sentence, array $DAG): array
    {
        $N = mb_strlen($sentence, 'UTF-8');
        $this->route = [];
        $this->route[$N] = array($N => 1.0);
        for ($i=($N-1); $i>=0; $i--) {
            $candidates = [];
            foreach ($DAG[$i] as $x) {
                $w_c = mb_substr($sentence, $i, (($x+1)-$i), 'UTF-8');
                $previous_freq = current($this->route[$x+1]);
                if (isset($this->FREQ[$w_c])) {
                    $current_freq = (float) $previous_freq + $this->FREQ[$w_c];
                } else {
                    $current_freq = (float) $previous_freq + $this->min_freq;
                }
                $candidates[$x] = $current_freq;
            }
            arsort($candidates);
            $max_prob = reset($candidates);
            $max_key = key($candidates);
            $this->route[$i] = array($max_key => $max_prob);
        }

        return $this->route;
    }

    /**
     * @param string $f_name  # input f_name
     * @return array
     */
    public function genTrie(string $f_name): array
    {
        // 配置缓存文件
        $cachepath = dirname($f_name).'/cache/';
        if (!file_exists($cachepath)) {
            mkdir($cachepath);
        }
        $triecachefile = 'trie.cache';
        $freqcachefile = 'freq.cache';
        $totalcachefile = 'total.cache';
        $minfreqcachefile = 'minfreq.cache';
        $flag1 = file_exists($cachepath.$triecachefile) && file_exists($cachepath.$freqcachefile);
        $flag2 = file_exists($cachepath.$totalcachefile) && file_exists($cachepath.$minfreqcachefile);
        $flag = $flag1 && $flag2;
        if ($flag) {
            // 读取缓存文件
            $triecache = fopen($cachepath.$triecachefile, 'r');
            $triesize = filesize($cachepath.$triecachefile);
            $this->trie = unserialize(fread($triecache, $triesize));
            $freqcache = fopen($cachepath.$freqcachefile, 'r');
            $freqsize = filesize($cachepath.$freqcachefile);
            $this->FREQ = unserialize(fread($freqcache, $freqsize));
            $totalcache = fopen($cachepath.$totalcachefile, 'r');
            $totalsize = filesize($cachepath.$totalcachefile);
            $this->total = unserialize(fread($totalcache, $totalsize));
            $minfreqcache = fopen($cachepath.$minfreqcachefile, 'r');
            $minfreqsize = filesize($cachepath.$minfreqcachefile);
            $this->min_freq = unserialize(fread($minfreqcache, $minfreqsize));
        } else {
            // 建立树并缓存
            $this->trie = new MultiArray(json_decode(file_get_contents($f_name . '.json'), true));
            $this->trie->cache = new MultiArray(json_decode(file_get_contents($f_name . '.cache.json'), true));

            $content = fopen($f_name, "r");
            while (($line = fgets($content)) !== false) {
                $explode_line = explode(" ", trim($line));
                $word = $explode_line[0];
                $freq = $explode_line[1];
                $tag = $explode_line[2];
                $freq = (float) $freq;
                $this->FREQ[$word] = $freq;
                $this->total += $freq;
            }
            fclose($content);

            foreach ($this->FREQ as $key => $value) {
                $this->FREQ[$key] = log($value / $this->total);
            }
            $this->min_freq = min($this->FREQ);
            // 缓存文件
            $triecache = fopen($cachepath.$triecachefile, 'w');
            $triecontent = serialize($this->trie);
            fwrite($triecache, $triecontent);
            fclose($triecache);
            $freqcache = fopen($cachepath.$freqcachefile, 'w');
            $freqcontent = serialize($this->FREQ);
            fwrite($freqcache, $freqcontent);
            fclose($freqcache);
            $totalcache = fopen($cachepath.$totalcachefile, 'w');
            $totalcontent = serialize($this->total);
            fwrite($totalcache, $totalcontent);
            fclose($totalcache);
            $minfreqcache = fopen($cachepath.$minfreqcachefile, 'w');
            $minfreqcontent = serialize($this->min_freq);
            fwrite($minfreqcache, $minfreqcontent);
            fclose($minfreqcache);
        }

        return $this->trie;
    }

    /**
     * Static method loadUserDict
     *
     * @param string $f_name  # input f_name
     * @return array
     */
    public function loadUserDict(string $f_name): array
    {
        array_push($this->user_dictname, $f_name);
        $content = fopen($f_name, "r");
        while (($line = fgets($content)) !== false) {
            $explode_line = explode(" ", trim($line));
            $word = $explode_line[0];
            $freq = $explode_line[1];
            $tag = $explode_line[2];
            $freq = (float) $freq;
            $this->total += $freq;
            $this->FREQ[$word] = log($freq / $this->total);
            $l = mb_strlen($word, 'UTF-8');
            $word_c = [];
            for ($i=0; $i<$l; $i++) {
                $c = mb_substr($word, $i, 1, 'UTF-8');
                array_push($word_c, $c);
            }
            $word_c_key = implode('.', $word_c);
            $this->trie->set($word_c_key, array("end"=>""));
        }
        fclose($content);

        return $this->trie;
    }

    /**
     * @param string $sentence # input sentence
     * @return array
     */
    public function __cutAll(string $sentence): array
    {
        $words = [];

        $DAG = $this->getDAG($sentence);
        $old_j = -1;

        foreach ($DAG as $k => $L) {
            if (count($L) == 1 && $k > $old_j) {
                $word = mb_substr($sentence, $k, (($L[0]-$k)+1), 'UTF-8');
                array_push($words, $word);
                $old_j = $L[0];
            } else {
                foreach ($L as $j) {
                    if ($j > $k) {
                        $word = mb_substr($sentence, $k, ($j-$k)+1, 'UTF-8');
                        array_push($words, $word);
                        $old_j = $j;
                    }
                }
            }
        }

        return $words;
    }

    /**
     * @param string $sentence # input sentence
     * @return array
     */
    public function getDAG(string $sentence): array
    {
        $N = mb_strlen($sentence, 'UTF-8');
        $i = 0;
        $j = 0;
        $DAG = [];
        $word_c = [];

        while ($i < $N) {
            $c = mb_substr($sentence, $j, 1, 'UTF-8');
            if (count($word_c)==0) {
                $next_word_key = $c;
            } else {
                $next_word_key = implode('.', $word_c).'.'.$c;
            }

            if ($this->trie->exists($next_word_key)) {
                array_push($word_c, $c);
                $next_word_key_value = $this->trie->get($next_word_key);
                if ($next_word_key_value == array("end"=>"")
                 || isset($next_word_key_value["end"])
                 || isset($next_word_key_value[0]["end"])
                ) {
                    if (!isset($DAG[$i])) {
                        $DAG[$i] = [];
                    }
                    array_push($DAG[$i], $j);
                }
                $j += 1;
                if ($j >= $N) {
                    $word_c = [];
                    $i += 1;
                    $j = $i;
                }
            } else {
                $word_c = [];
                $i += 1;
                $j = $i;
            }
        }

        for ($i=0; $i<$N; $i++) {
            if (!isset($DAG[$i])) {
                $DAG[$i] = array($i);
            }
        }

        return $DAG;
    }

    /**
     * @param string $sentence # input sentence
     * @return array
     */
    public function __cutDAG(string $sentence): array
    {
        $words = [];

        $N = mb_strlen($sentence, 'UTF-8');
        $DAG = $this->getDAG($sentence);

        $this->calc($sentence, $DAG);

        $x = 0;
        $buf = '';

        while ($x < $N) {
            $current_route_keys = array_keys($this->route[$x]);
            $y = $current_route_keys[0]+1;
            $l_word = mb_substr($sentence, $x, ($y-$x), 'UTF-8');

            if (($y-$x)==1) {
                $buf = $buf.$l_word;
            } else {
                if (mb_strlen($buf, 'UTF-8')>0) {
                    if (mb_strlen($buf, 'UTF-8')==1) {
                        array_push($words, $buf);
                        $buf = '';
                    } else {
                        $regognized = Finalseg::singleton()->cut($buf);
                        foreach ($regognized as $key => $word) {
                            array_push($words, $word);
                        }
                        $buf = '';
                    }
                }
                array_push($words, $l_word);
            }
            $x = $y;
        }

        if (mb_strlen($buf, 'UTF-8')>0) {
            if (mb_strlen($buf, 'UTF-8')==1) {
                array_push($words, $buf);
            } else {
                $regognized = Finalseg::singleton()->cut($buf);
                foreach ($regognized as $key => $word) {
                    array_push($words, $word);
                }
            }
        }

        return $words;
    }

    /**
     * Static method cut
     *
     * @param string  $sentence # input sentence
     * @param boolean $cut_all  # cut_all or not
     * @return array
     */
    public function cut(string $sentence, bool $cut_all = false): array
    {
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
                if ($cut_all) {
                    $words = Jieba::__cutAll($blk);
                } else {
                    $words = Jieba::__cutDAG($blk);
                }

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
     * @param string  $sentence # input sentence
     * @return array
     */
    public function cutForSearch(string $sentence): array
    {
        $seg_list = [];

        $cut_seg_list = Jieba::cut($sentence);

        foreach ($cut_seg_list as $w) {
            $len = mb_strlen($w, 'UTF-8');

            if ($len>2) {
                for ($i=0; $i<($len-1); $i++) {
                    $gram2 = mb_substr($w, $i, 2, 'UTF-8');

                    if (isset($this->FREQ[$gram2])) {
                        array_push($seg_list, $gram2);
                    }
                }
            }

            if (mb_strlen($w, 'UTF-8')>3) {
                for ($i=0; $i<($len-2); $i++) {
                    $gram3 = mb_substr($w, $i, 3, 'UTF-8');

                    if (isset($this->FREQ[$gram3])) {
                        array_push($seg_list, $gram3);
                    }
                }
            }

            array_push($seg_list, $w);
        }

        return $seg_list;
    }
}
