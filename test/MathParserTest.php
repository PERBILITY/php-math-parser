<?php
namespace MathParser;

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
            // should fail..? https://floating-point-gui.de/
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
            ['$was', null],
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
            [['$0', ['1']], 1],
            [['$0 + $0', [1]], 2],
            [['$0 + $1', [1, 41]], 42],
            [['$0 + $2', [0, 41, 13]], 13],
        ];
        
        return $data;
    }
    
    /**
     * @return mixed[][]
     */
    public static function provideInvalidVariableData()
    {
        $data = [
            [['$0', ['test']], null],
        ];
        
        return $data;
    }
    
    /**
     * @dataProvider provideValidData
     */
    public function testValid($input, $expected)
    {
        $mathParser = new Math();
        $this->assertSame($expected, $mathParser->evaluate($input));
    }
    
    /**
     * @dataProvider provideInvalidSyntaxData
     */
    public function testInvalidSyntax($input, $expected)
    {
        $this->expectException(\RuntimeException::class);
        $mathParser = new Math();
        $mathParser->evaluate($input);
    }
    
    /**
     * @dataProvider provideInvalidSyntaxType
     */
    public function testInvalidSyntaxType($input, $expected)
    {
        $this->expectException(\RuntimeException::class);
        $mathParser = new Math();
        $mathParser->evaluate($input);
    }
    
    /**
     * @dataProvider provideVariableData
     */
    public function testVariables($input, $expected)
    {
        $mathParser = new Math();
        $mathParser->setVariables($input[1]);
        $this->assertSame($expected, $mathParser->evaluate($input[0]));
    }
    
    /**
     * @dataProvider provideInvalidVariableData
     */
    public function testInvalidVariable($input, $expected)
    {
        $this->expectException(\InvalidArgumentException::class);
        $mathParser = new Math();
        $mathParser->setVariables($input[1]);
        $mathParser->evaluate($input[0]);
    }
    
    /**
     * @dataProvider provideInvalidVariableData
     */
    public function testInvalidVariableWithRegister($input, $expected)
    {
        $this->expectException(\InvalidArgumentException::class);
        $mathParser = new Math();
        $mathParser->registerVariable(0, $input[1][0]);
        $mathParser->evaluate($input[0]);
    }
}