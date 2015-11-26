<?php
namespace Home\Controller;

use Think\Controller;

class IndexController extends Controller
{
    public function index()
    {
        $elements = range(1,10,1);
        $result = array();
        self::combination($elements, 10, 3, $result, 3);
        sort($result);
        var_dump($result);
    }

    /**
     * Generating combination
     * @param $elements
     * @param $n
     * @param $m
     * @param $tempIndex
     * @param $M
     */
    private function combination($elements, $n, $m, &$result, $M, &$tempIndex = array())
    {
        for ($i = $n; $i >= $m; $i--) {
            $tempIndex[$m - 1] = $i - 1;
            if ($m > 1)
                self::combination($elements, $i - 1, $m - 1, $result, $M, $tempIndex);
            else {
                $tmp = '';
                for ($j = 0; $j <= $M - 1; $j++)
                    $tmp = $tmp . ' ' . $elements[$tempIndex[$j]] . ' ';
                $result[] = $tmp;
            }
        }
    }


}