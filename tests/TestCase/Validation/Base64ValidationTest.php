<?php

namespace Josegonzalez\Upload\Test\TestCase\Validation;

use Cake\TestSuite\TestCase;
use Josegonzalez\Upload\Validation\Base64Validation;

class Base64ValidationTest extends TestCase
{

    public function teardown()
    {
        parent::tearDown();
    }

    public function testIsMimeTypeOK()
    {
        $png = 'iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAYAAACNiR0NAAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAA7DAAAOwwHHb6hkAAAAB3RJTUUH4AMUECwX5I9GIwAAACFJREFUOMtj/P//PwM1ARMDlcGogaMGjho4auCogUPFQABpCwMlgqgSYAAAAABJRU5ErkJggg==';
        $this->assertTrue(Base64Validation::isMimeType($png, 'image/png'));
    }

    public function testIsMimeTypeInvalid()
    {
        $phpCode = 'PD9waHAgZWNobyAnQ2FrZVBocCc7ID8+';
        $this->assertFalse(Base64Validation::isMimeType($phpCode, 'image/png'));
    }
}
