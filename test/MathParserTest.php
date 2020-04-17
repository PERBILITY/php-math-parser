<?php

declare(strict_types=1);

namespace MathParser;

use MathParser\Exceptions\InvalidSyntaxException;
use PHPUnit\Framework\TestCase;

/**
 * @author Stefan Ackermann <stefan.ackermann@perbility.de>
 */
class MathParserTest extends TestCase
{
    /**
     * @return mixed[][]
     */
    public static function provideValidData()
    {
        return [
            ['10', 10],
            ['10.0', 10.0],
            ['1 + 1', 2],
            ['1.0 + 1', 2.0],
            ['1 - 2', -1],
            ['0.5 + 0.5', 1.0],
            ['-1--1', 0],
            ['1-1/6', 5 / 6],
            ['(1-7)/6', -1],
            ['-0 + 0', 0],
            ['8 - 6.4', 1.6],
            ['1 / 2', 0.5],
            ['10 * 10', 100],
            ['10 % 2', 0],
            ['10 % -3', 10 % -3],
            ['10 % 3', 10 % 3],
            
            ['10 % 0', null],
            ['1/0', null],
            ['1/0 + 100', null],
            
            ['1^0', 1],
            ['2^8', 256],
            ['2^-8', 1 / 256],
            ['0^0', 1],
            ['2^8 + -56 + (4*60+45) /15 % 177 - 2^7 - 49', 42],
            
            // original tests
            ['10 / 5', 10 / 5],
            ['(2 + 3) * 4', (2 + 3) * 4],
            ['1 + 2 * ((3 + 4) * 5 + 6)', 1 + 2 * ((3 + 4) * 5 + 6)],
            ['9 * (3+8) - 6 - 45', 9 * (3 + 8) - 6 - 45],
            ['1 * 2 + ((3 + 4) * 5 + 6)', 1 * 2 + ((3 + 4) * 5 + 6)],
            ['1.45 + 3', 1.45 + 3],
            ['0.45 + 3.5', 0.45 + 3.5],
            ['(1.65 + 2) * (3.1415 + 4) * (5 + 6.8989)', (1.65 + 2) * (3.1415 + 4) * (5 + 6.8989)],
            ['-2.5 / 0.5', -2.5 / 0.5],
            ['-9 * (-3+8) - 6 - -45', -9 * (-3 + 8) - 6 - -45],
            ['-7.3 * (-3.2+8) - 6 - -45.5', -7.3 * (-3.2 + 8) - 6 - -45.5]
        ];
    }
    
    /**
     * @return mixed[][]
     */
    public static function provideInvalidSyntaxData()
    {
        $data = [
            ['', null],
            ['1 1', null],
            ['-1-+1', null],
            ['1e100+1e100', null],
            ['1/((0', null],
            ['1e5+1e5', null],
            ['1f5+1f5', null],
            
            ['mathe ist doof', null],
            ['.1', null],
            ['10(100)', null],
        ];
        
        return $data;
    }
    
    /**
     * @return mixed[][]
     */
    public static function provideInvalidSyntaxType()
    {
        $data = [
            [0, null],
            [[], null],
        ];
        
        return $data;
    }
    
    /**
     * @return mixed[][]
     */
    public static function provideVariableData()
    {
        $data = [
            [['$0', [1]], 1],
            [['$0 + $0', [1]], 2],
            [['$0 + $1', [1, 41]], 42],
            [['$0 + $2', [0, 41, 13]], 13],
            [['$a + $b', ['a' => 0, 'b' => 41, 13]], 41],
            [['$a + $b + $0', ['a' => 0, 'b' => 41, 0 => 13]], 54],
            [['$a1 + $b2 + $0', ['a1' => 0, 'b2' => 41, 0 => 13]], 54],
            [['$0 + $2', [0, 41, 13]], 13],
            [['$0 + $2', [0, 41, 13]], 13],
            [['$0 + $2', [null, 41, 13]], null],
        ];
        
        return $data;
    }
    
    /**
     * @return mixed[][]
     */
    public static function provideVariableDataForStrictNullHandling()
    {
        // fallback = 42;
        $data = [
            [['$0', [null]], null],
            
            [['$0 + $0', [null]], null],
            [['$0 + $1', [null, 41]], null],
            [['$0 + $1', [41, null]], null],
            [['$a1 + $b2 + $0', ['a1' => null, 'b2' => 41, 0 => 13]], null],
            
            [['$0 * $0', [null]], null],
            [['$0 * $1', [null, 41]], null],
            [['$0 * $1', [41, null]], null],
            [['$a1 * $b2 * $0', ['a1' => null, 'b2' => 41, 0 => 13]], null],
            
            [['$0 / $0', [null]], null],
            [['$0 / $1', [null, 41]], null],
            [['$0 / $1', [41, null]], null],
            [['$a1 / $b2 / $0', ['a1' => null, 'b2' => 41, 0 => 13]], null],
            
            [['$0 % $0', [null]], null],
            [['$0 % $1', [null, 41]], null],
            [['$0 % $1', [41, null]], null],
            [['$a1 % $b2 % $0', ['a1' => null, 'b2' => 41, 0 => 13]], null],
            
            [['$0 - $0', [null]], null],
            [['$0 - $1', [null, 41]], null],
            [['$0 - $1', [41, null]], null],
            [['$a1 - $b2 - $0', ['a1' => null, 'b2' => 41, 0 => 13]], null],
            
            [['$0 ^ $0', [null]], null],
            [['$0 ^ $1', [null, 41]], null],
            [['$0 ^ $1', [41, null]], null],
            [['$a1 ^ $b2 ^ $0', ['a1' => null, 'b2' => 4, 0 => 3]], null],
        ];
        
        return $data;
    }
    
    
    /**
     * @return mixed[][]
     */
    public static function provideVariableDataForFallbackNullHandling()
    {
        // fallback = 42;
        $data = [
            [['$0', [null]], null],
            
            [['$0 + $0', [null]], 84],
            [['$0 + $1', [null, 41]], 83],
            [['$0 + $1', [41, null]], 83],
            [['$a1 + $b2 + $0', ['a1' => null, 'b2' => 41, 0 => 13]], 42 + 54],
            
            [['$0 * $0', [null]], 42 * 42],
            [['$0 * $1', [null, 41]], 42 * 41],
            [['$0 * $1', [41, null]], 41 * 42],
            [['$a1 * $b2 * $0', ['a1' => null, 'b2' => 41, 0 => 13]], 42 * 41 * 13],
            
            [['$0 / $0', [null]], 1],
            [['$0 / $1', [null, 41]], 42 / 41],
            [['$0 / $1', [41, null]], 41 / 42],
            [['$a1 / $b2 / $0', ['a1' => null, 'b2' => 41, 0 => 13]], 42 / 41 / 13],
            
            [['$0 % $0', [null]], 0],
            [['$0 % $1', [null, 41]], 1],
            [['$0 % $1', [41, null]], 41],
            [['$a1 % $b2 % $0', ['a1' => null, 'b2' => 41, 0 => 13]], 42 % 41 % 13],
            
            [['$0 - $0', [null]], 0],
            [['$0 - $1', [null, 41]], 1],
            [['$0 - $1', [41, null]], -1],
            [['$a1 - $b2 - $0', ['a1' => null, 'b2' => 41, 0 => 13]], 42 - 41 - 13],
            
            [['$0 ^ $0', [null]], 42 ** 42],
            [['$0 ^ $1', [null, 41]], 42 ** 41],
            [['$0 ^ $1', [41, null]], 41 ** 42],
            [['$a1 ^ $b2 ^ $0', ['a1' => null, 'b2' => 4, 0 => 3]], 42 ** 4 ** 3],
        ];
        
        return $data;
    }
    
    /**
     * @return mixed[][]
     */
    public static function provideVariableDataForLooseNullHandling()
    {
        // fallback = 42;
        $data = [
            [['$0', [null]], null],
            
            [['$0 + $0', [null]], 0],
            [['$0 + $1', [null, 41]], 41],
            [['$0 + $1', [41, null]], 41],
            [['$a1 + $b2 + $0', ['a1' => null, 'b2' => 41, 0 => 13]], 54],
            
            [['$0 * $0', [null]], 0],
            [['$0 * $1', [null, 41]], 0],
            [['$0 * $1', [41, null]], 0],
            [['$a1 * $b2 * $0', ['a1' => null, 'b2' => 41, 0 => 13]], 0],
            
            [['$0 / $0', [null]], null],
            [['$0 / $1', [null, 41]], 0],
            [['$0 / $1', [41, null]], null],
            [['$a1 / $b2 / $0', ['a1' => null, 'b2' => 41, 0 => 13]], 0],
            
            [['$0 % $0', [null]], null],
            [['$0 % $1', [null, 41]], 0],
            [['$0 % $1', [41, null]], null],
            [['$a1 % $b2 % $0', ['a1' => null, 'b2' => 41, 0 => 13]], 0 % 41 % 13],
            
            [['$0 - $0', [null]], 0],
            [['$0 - $1', [null, 41]], -41],
            [['$0 - $1', [41, null]], 41],
            [['$a1 - $b2 - $0', ['a1' => null, 'b2' => 41, 0 => 13]], -41 - 13],
            
            // 64 bit to the rescue !
            [['$0 ^ $0', [null]], 1],
            [['$0 ^ $1', [null, 41]], 0 ** 41],
            [['$0 ^ $1', [41, null]], 41 ** 0],
            [['$a1 ^ $b2 ^ $0', ['a1' => null, 'b2' => 4, 0 => 3]], 0 ** 4 ** 3],
        ];
        
        return $data;
    }
    
    /**
     * @return mixed[][]
     */
    public static function provideVariableDataForSkipHandling()
    {
        $data = [
            [['$0', [null]], null],
            
            [['$0 + $0', [null]], null],
            [['$0 + $1', [null, 41]], 41],
            [['$0 * $1', [41, null]], 41],
            [['$a1 + $b2 + $0', ['a1' => null, 'b2' => 41, 0 => 13]], 54],
            
            [['$0 * $0', [null]], null],
            [['$0 * $1', [null, 41]], 41],
            [['$0 * $1', [41, null]], 41],
            [['$a1 * $b2 * $0', ['a1' => null, 'b2' => 41, 0 => 13]], 41 * 13],
            
            [['$0 / $0', [null]], null],
            [['$0 / $1', [null, 41]], 41],
            [['$0 / $1', [41, null]], 41],
            [['$a1 / $b2 / $0', ['a1' => null, 'b2' => 41, 0 => 13]], 41 / 13],
            
            [['$0 % $0', [null]], null],
            [['$0 % $1', [null, 41]], 41],
            [['$0 % $1', [41, null]], 41],
            [['$a1 % $b2 % $0', ['a1' => null, 'b2' => 41, 0 => 13]], 41 % 13],
            
            [['$0 - $0', [null]], null],
            [['$0 - $1', [null, 41]], 41],
            [['$0 - $1', [41, null]], 41],
            [['$a1 - $b2 - $0', ['a1' => null, 'b2' => 41, 0 => 13]], 41 - 13],
            
            [['$0 ^ $0', [null]], null],
            [['$0 ^ $1', [null, 41]], 41],
            [['$0 ^ $1', [41, null]], 41],
            [['$a1 ^ $b2 ^ $0', ['a1' => null, 'b2' => 4, 0 => 3]], 4 ** 3],
        ];
        
        return $data;
    }
    
    /**
     * @return mixed[][]
     */
    public static function provideVariableDataWithUsedVars()
    {
        $data = [
            [['$0', [1]], ['0']],
            [['$0 + $0', [1]], ['0']],
            [['$0 + $1', [1, 41]], ['0', '1']],
            [['$0 + $2', [0, 41, 13]], ['0', '2']],
            [['$a + $b', ['a' => 0, 'b' => 41, 13]], ['a', 'b']],
            [['$a + $b + $0', ['a' => 0, 'b' => 41, 0 => 13]], ['a', 'b', '0']],
            [['$a1 + $b2 + $0', ['a1' => 0, 'b2' => 41, 0 => 13]], ['a1', 'b2', '0']],
            [['$0 + $2', [0, 41, 13]], ['0', '2']],
            [['10', []], []],
        ];
        
        return $data;
    }
    
    /**
     * @return mixed[][]
     */
    public static function provideInvalidVariableData()
    {
        $data = [
            [['$0', ['1']], 1],
            [['$0', ['test']], null],
        ];
        
        return $data;
    }
    
    /**
     * @dataProvider provideValidData
     */
    public function testValid($input, $expected)
    {
        $this->assertSame($expected, Math::evaluate($input));
    }
    
    /**
     * @dataProvider provideInvalidSyntaxData
     */
    public function testInvalidSyntax($input, $expected)
    {
        $this->expectException(InvalidSyntaxException::class);
        Math::evaluate($input);
    }
    
    /**
     * @dataProvider provideInvalidSyntaxType
     */
    public function testInvalidSyntaxType($input, $expected)
    {
        $this->expectException(\TypeError::class);
        Math::evaluate($input);
    }
    
    /**
     * @dataProvider provideVariableData
     */
    public function testVariables($input, $expected)
    {
        $this->assertSame($expected, Math::evaluate($input[0], $input[1]));
    }
    
    /**
     * @dataProvider provideVariableData
     */
    public function testVariablesMultipleTimes($input, $expected)
    {
        $this->assertSame($expected, Math::evaluate($input[0], $input[1]));
        $this->assertSame($expected, Math::evaluate($input[0], $input[1]));
    }
    
    /**
     * @dataProvider provideInvalidVariableData
     */
    public function testInvalidVariable($input, $expected)
    {
        $this->expectException(\InvalidArgumentException::class);
        Math::evaluate($input[0], $input[1]);
    }
    
    /**
     * @dataProvider provideInvalidVariableData
     */
    public function testInvalidVariableWithRegister($input, $expected)
    {
        $this->expectException(\InvalidArgumentException::class);
        Math::evaluate($input[0], [0 => $input[1][0]]);
    }
    
    /**
     * @dataProvider provideVariableDataWithUsedVars
     */
    public function testDistinctVariables($input, $expected)
    {
        $stack = Math::parse($input[0]);
        $vars = Math::getDistinctVariables($stack);
        $this->assertSame($expected, $vars);
    }
    
    /**
     * @dataProvider provideVariableDataForStrictNullHandling
     */
    public function testStrictNullHandling($input, $expected)
    {
        $value = Math::evaluate($input[0], $input[1]);
        $this->assertSame($expected, $value);
    }
    
    /**
     * @dataProvider provideVariableDataForLooseNullHandling
     */
    public function testLooseNullHandling($input, $expected)
    {
        $value = Math::evaluate($input[0], $input[1], ['null_handling' => 'loose']);
        $this->assertSame($expected, $value);
    }
    
    /**
     * @dataProvider provideVariableDataForSkipHandling
     */
    public function testSkipNullHandling($input, $expected)
    {
        $value = Math::evaluate($input[0], $input[1], ['null_handling' => 'skip']);
        $this->assertSame($expected, $value);
    }
    
    /**
     * @dataProvider provideVariableDataForFallbackNullHandling
     */
    public function testFallbackNullHandling($input, $expected)
    {
        $value = Math::evaluate($input[0], $input[1], ['null_handling' => 'fallback', 'fallback' => 42]);
        $this->assertSame($expected, $value);
    }
}