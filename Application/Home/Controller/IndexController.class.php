<?php
namespace Home\Controller;

use Think\Controller;

class IndexController extends Controller
{
    public function index()
    {
//        $elements = range(1,10,1);
//        $result = array();
//        self::combination($elements, 10, 3, $result, 3);
//        sort($result);
//        var_dump($result);
//        self::conditionOne(array(1,2,3,4,5),5,4,3);
        $this->display();
    }

    public function optimal()
    {
        $data = I('post.');
        $n = str_replace(" ", "", $data['n']);
        // n = 1,2,3,4,5,6,7
        // k = 5  j = 4 s = 2
        $nArray = explode(",", $n);
    }

    /**
     * s=j<k
     */
    public function conditionOne($nArray,$n, $k, $j)
    {
        $A = array(); // k elements combination
        $B = array(); // j elements combination
        self::combination($nArray,$n,$k,$A,$k);
        self::combination($nArray,$n,$j,$B,$j);

        $countA = count($A);
//        var_dump($A);

        $hitMap = array(); // each $A hit map, count is the ratio
        for($i = 0; $i < $countA; $i ++){
            $itemArray = explode(' ',$A[$i]); // use blank to split string to array
            $itemSize = count($itemArray);
            $C = array();
            self::combination($itemArray,$itemSize,$j,$C,$j);
            $countC = count($C);
            for($m = 0; $m < $countC; $m ++){
                $index = array_search($C[$m],$B);
                if($index != false){ // search success
                    $hitMap[$i][$B[$index]] = 1;
                }
            }
        }
//        var_dump($hitMap);
    }

    /**
     * Generating combination
     * @param $candidate The candidate array
     * @param $n size of candidate array
     * @param $m how many you want to pick
     * @param $result collections of the combination
     * @param $M equal to $m
     * @param $index  index of array with each result
     */
    private static function combination($candidate, $n, $m, &$result, $M, &$index = array())
    {
        for ($i = $n; $i >= $m; $i--) {
            $index[$m - 1] = $i - 1;
            if ($m > 1)
                self::combination($candidate, $i - 1, $m - 1, $result, $M, $index);
            else {
                $str = '';
                for ($j = 0; $j <= $M - 1; $j++){
                    $str .= ' '.$candidate[$index[$j]];
                }
                $str = substr($str,1); // exclude first blank
                $result[] = $str;
            }
        }
    }

}