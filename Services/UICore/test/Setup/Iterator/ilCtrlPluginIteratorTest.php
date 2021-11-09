<?php declare(strict_types = 1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;

/**
 * Class ilCtrlPluginIteratorTest
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
final class ilCtrlPluginIteratorTest extends TestCase
{
    public function testPluginIteratorWithValidDirectory() : void
    {
        $data_dir = realpath(__DIR__ . '/../../Data');
        $iterator = new ilCtrlPluginIterator($data_dir . '/Setup/Plugins/Valid');

        $this->assertTrue($iterator->valid());
        $this->assertEquals(
            $data_dir . '/Setup/Plugins/Valid/Services/UIComponent/UserInterfaceHook/ValidTestPlugin',
            $iterator->current()
        );

        $this->assertEquals(
            'test_ui_plugin',
            $iterator->key()
        );

        $iterator->next();

        $this->assertFalse($iterator->valid());
        $this->assertNull($iterator->current());
        $this->assertNull($iterator->key());
    }

    public function testPluginIteratorWithInvalidPluginFileInDirectory() : void
    {
        $plugin_dir = realpath(__DIR__ . '/../../Data/Setup/Plugins/Invalid');
        $iterator = new ilCtrlPluginIterator($plugin_dir);

        $this->assertFalse($iterator->valid());
        $this->assertNull($iterator->current());
        $this->assertNull($iterator->key());
    }

    public function testPluginIteratorWithEmptyDirectory() : void
    {
        $iterator = new ilCtrlPluginIterator(__DIR__ . '/../../Data/Setup/EmptyDirectory');

        $this->assertFalse($iterator->valid());
        $this->assertNull($iterator->current());
        $this->assertNull($iterator->key());
    }

    public function testPluginIteratorWithInvalidDirectory() : void
    {
        $not_existing_dir = __DIR__ . '/not_existing_dir';

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage("RecursiveDirectoryIterator::__construct($not_existing_dir): failed to open dir: No such file or directory");
        $iterator = new ilCtrlPluginIterator($not_existing_dir);
    }
}