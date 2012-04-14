<?php
#
# xPyrus - Framework for Community and knowledge exchange
# Copyright (C) 2003-2008 UniHelp e.V., Germany
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU Affero General Public License as
# published by the Free Software Foundation, only version 3 of the
# License.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU Affero General Public License for more details.
#
# You should have received a copy of the GNU Affero General Public License
# along with this program. If not, see http://www.gnu.org/licenses/agpl.txt
#

// $Id: captcha_computation.php 5807 2008-04-12 21:23:22Z trehn $
// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/utils/captcha_computation.php $

class CaptchaComputation {
    protected $numbers;
    protected $operation;
    protected $expectedResult;
    
    const OPERATIONS = '+ - *';
    
    public function __construct() {
    }
    
    private static function makeSmall(&$number) {
        if ($number < 10) {
            return $number;
        }
        $number = ($number % 9) + 1; 
        return $number;
    }
    
    public function generate() {
        $this->numbers = array();
        $this->numbers[0] = rand(2,59);
        $this->numbers[1] = rand(2,41);
        if ($this->numbers[0] < $this->numbers[1]) {
            $tmp = $this->numbers[0];
            $this->numbers[0] = $this->numbers[1];
            $this->numbers[1] = $tmp;
        }
        
        $ops = explode(' ', self::OPERATIONS);
        $this->operation = $ops[array_rand($ops)];
        $this->expectedResult = 0;
        switch ($this->operation) {
            case '+': $this->expectedResult = $this->numbers[0] + $this->numbers[1]; break;
            case '-': $this->expectedResult = $this->numbers[0] - $this->numbers[1]; break;
            case '*': $this->expectedResult = self::makeSmall($this->numbers[0]) * self::makeSmall($this->numbers[1]); break;
        }
    }
   
    private static function verbalize($op) {
        switch ($op) {
            case '+': return CAPTCHA_PLUS;
            case '-': return CAPTCHA_MINUS;
            case '*': return CAPTCHA_TIMES;
        }
    }

    public function render() {
        return CAPTCHA_CALCULATE . $this->numbers[0] . ' ' . self::verbalize($this->operation) . ' ' . $this->numbers[1];
    }
    
    public function getExpectedResult() {
        return $this->expectedResult;
    }
    
    public function cleanup() {
        
    }
}

?>
