<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\test\old\DataStore;

class CsvBaseTest extends AbstractTest
{
    protected $filename;

    protected $delimiter;

    protected $entity = 'testCsvBase';

    protected function setUp(): void
    {
        parent::setUp();
        $this->filename = $this->config[$this->entity]['filename'];

        // If file does not exist creates it
        if (!is_file($this->filename)) {
            $fp = fopen($this->filename, 'w');
            fclose($fp);
        }
        $this->delimiter = $this->config[$this->entity]['delimiter'];
        $this->object = $this->container->get($this->entity);
    }

    protected function tearDown(): void
    {
        unlink($this->filename);
    }

    protected function _initObject($data = null)
    {
        if (is_null($data) || !count($data)) {
            $data = $this->_itemsArrayDelault;
        }
        if (is_null($this->filename)) {
            $this->filename = tempnam(sys_get_temp_dir(), 'csv');
        }
        $fp = fopen($this->filename, 'w');
        foreach ($data as $index => $item) {
            if (!$index) {
                // at first we write the column headings
                fputcsv($fp, array_keys($item), $this->delimiter);
            }
            fputcsv($fp, $item, $this->delimiter);
        }
        fclose($fp);
        // Set real column heading because at first created file was empty
        $this->object->getHeaders();
    }

    public function testWriteAndReadNullValueAndEmptyString()
    {
        $this->_initObject();
        $itemData = [
            'id' => 1000,
            'anotherId' => null,
            'fFloat' => 1000.01,
            'fString' => '',
        ];
        $this->object->create($itemData, true);
        $row = $this->object->read(1000);
        $this->assertEquals($itemData, $row);
    }

    public function testWriteAndRead_FalseValue()
    {
        $this->_initObject();
        $itemData = [
            'id' => 1000,
            'anotherId' => false,
            'fFloat' => 1000.01,
            'fString' => 'FalseValue',
        ];
        $this->object->create($itemData, true);
        $row = $this->object->read(1000);
        $this->assertEquals($row['anotherId'], false);
    }

    public function testReadNotExistedRow()
    {
        $this->_initObject();
        $row = $this->object->read(10000);
        $this->assertEquals($row, null);
    }

    public function testWriteAndRead_TrueValue()
    {
        $this->_initObject();
        $itemData = [
            'id' => 1000,
            'anotherId' => true,
            'fFloat' => 1000.01,
            'fString' => 'TrueValue',
        ];
        $this->object->create($itemData, true);
        $row = $this->object->read(1000);
        $this->assertEquals($row['anotherId'], true);
    }

    public function testWriteToEmptyFile()
    {
        $itemData[] = [
            'id' => 1000,
            'anotherId' => true,
            'fFloat' => 1000.01,
            'fString' => 'TrueValue',
        ];
        $this->_initObject($itemData);
        $this->object->delete(1000);
        $itemData = array_shift($itemData);
        $this->object->create($itemData);
        $row = $this->object->read(1000);
        $this->assertEquals($row, $itemData);
    }

    public function test_getAllExpectArray()
    {
        $this->_initObject();
        clearstatcache();
        $content = $this->object->getAll();
        $this->assertTrue(isset($content[0]['id']));
    }

    public function testIterator()
    {
        $this->_initObject();
        $iterator = $this->object->getIterator();
        $item = $iterator->current();
        $this->assertEquals(1, $item['id']);

        $item = $iterator->current();
        $this->assertEquals(1, $item['id']);

        $iterator->next();
        $item = $iterator->current();
        $this->assertEquals(2, $item['id']);

        $iterator->next();
        $iterator->next();
        $item = $iterator->current();
        $this->assertEquals(4, $item['id']);
        $this->assertTrue($iterator->valid());

        $iterator->next();
        $this->assertFalse($iterator->valid());
        $this->assertEquals(4, $item['id']);

        $item = $iterator->current();
        $this->assertNull($item);
    }
}
