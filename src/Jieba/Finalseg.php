<?php

namespace Jieba;

use Jieba\Constants\JiebaConstant;
use Jieba\Data\MultiByteString;
use Jieba\Data\TopArrayElement;
use Jieba\Data\Viterbi;
use Jieba\Data\Word;
use Jieba\Helper\DictHelper;
use Jieba\Helper\ModelSingleton;
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
     * Cut given sentence to an array of individual Chinese and non-Chinese characters.
     * @param string $sentence
     * @return array
     * @todo make code easier to understand.
     */
    public function cut(string $sentence): array
    {
        return (new MultiByteString($sentence))->cut(
            function (string $block) {
                return DictHelper::cutSentence(
                    $block,
                    Word::class,
                    function (string $sentence) {
                        // here \Jieba\Data\Viterbi::$positions is an array of single characters (BMES characters).
                        return $this->viterbi($sentence);
                    }
                );
            }
        );
    }

    /**
     * @param string $sentence
     * @return Viterbi
     */
    protected function viterbi(string $sentence): Viterbi
    {
        $probEmit  = ModelSingleton::singleton()->getProbEmit();
        $probStart = ModelSingleton::singleton()->getProbStart();
        $probTrans = ModelSingleton::singleton()->getProbTrans();

        $string = new MultiByteString($sentence);
        $V      = [[]];
        $path   = [];

        foreach (JiebaConstant::BMES as $state) {
            $c            = $string->get(0);
            $prob_emit    = ($probEmit[$state][$c] ?? JiebaConstant::MIN_FLOAT);
            $V[0][$state] = $probStart[$state] + $prob_emit;
            $path[$state] = [$state];
        }

        for ($t = 1; $t < $string->strlen(); $t++) {
            $c       = $string->get($t);
            $V[$t]   = [];
            $newPath = [];
            foreach (JiebaConstant::BMES as $state) {
                $temp_prob_array = [];
                foreach (JiebaConstant::BMES as $state0) {
                    $prob_trans               = ($probTrans[$state0][$state] ?? JiebaConstant::MIN_FLOAT);
                    $prob_emit                = ($probEmit[$state][$c] ?? JiebaConstant::MIN_FLOAT);
                    $temp_prob_array[$state0] = $V[$t-1][$state0] + $prob_trans + $prob_emit;
                }
                $top             = new TopArrayElement($temp_prob_array);
                $maxKey          = $top->getKey();
                $V[$t][$state]   = $top->getValue(); // maximum probability
                $newPath[$state] = array_merge($path[$maxKey], [$state]);
            }
            $path = $newPath;
        }

        $lastIndex = $string->strlen() - 1;
        if ($V[$lastIndex][JiebaConstant::E] >= $V[$lastIndex][JiebaConstant::S]) {
            $state = JiebaConstant::E;
        } else {
            $state = JiebaConstant::S;
        }

        return new Viterbi($V[$lastIndex][$state], $path[$state]);
    }
}
