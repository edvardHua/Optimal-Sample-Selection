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
        self::conditionOne(array(1, 2, 3, 4, 5), 5, 4, 3);
//        $this->display();
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
    public function conditionOne($nArray, $n, $k, $j)
    {
        $A = array(); // k elements combination
        $B = array(); // j elements combination
        self::combination($nArray, $n, $k, $A, $k);
        self::combination($nArray, $n, $j, $B, $j);
        sort($A);
        sort($B);
//        var_dump($A);
//        var_dump($B);

        $jInAArray = array();
        $hitMap = array(); // each $A hit map, count is the ratio
        foreach ($A as $key => $value) {
            $itemArray = explode(' ', $value); // use blank to split string to array
            $itemSize = count($itemArray);
            $C = array();

            self::combination($itemArray, $itemSize, $j, $C, $j);
            $jInAArray[$key] = $C;

//            var_dump($C);

            $countC = count($C);
            for ($m = 0; $m < $countC; $m++) {
                $index = array_search($C[$m], $B);
                if ($index !== false) { // search success, must use !== to prevent 0 case
                    $hitMap[$key][$B[$index]] = 1;
                }
            }
        }

        var_dump($hitMap);


        $result = array();
        do{
            $index = self::maxHit($hitMap);
            var_dump($index);

            array_push($result, $A[$index]);
            unset($A[$index]);
            unset($jInAArray[$index]);
            $excludeItem = array_keys($hitMap[$index]); // transform to do diff opt
            var_dump($excludeItem);


            $B = array_diff($B, $excludeItem);

            var_dump($B);

            unset($hitMap);
            foreach($jInAArray as $key => $value){
                $countC = count($value);
                for ($m = 0; $m < $countC; $m++) {
                    $index = array_search($value[$m], $B);
                    if ($index !== false) { // search success, must use !== to prevent 0 case
                        $hitMap[$key][$B[$index]] = 1;
                    }
                }
            }
            var_dump($hitMap);
        }while(!empty($B));

        var_dump($result);
    }

    /**
     * s < j = k
     */
    public function conditionTwo(){

    }

    /**
     * s < j < k
     */
    public function conditionThree(){

    }

    /**
     * max count in array
     */
    private static function maxHit($variable)
    {
        $index = 0;
        $compare = count($variable[0]);
        foreach ($variable as $key => $value) {
            $tmp = count($value);
            if ($tmp > $compare)
                $index = $key;
        }
        return $index;
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
                for ($j = 0; $j <= $M - 1; $j++) {
                    $str .= ' ' . $candidate[$index[$j]];
                }
                $str = substr($str, 1); // exclude first blank
                $result[] = $str;
            }
        }
    }

}