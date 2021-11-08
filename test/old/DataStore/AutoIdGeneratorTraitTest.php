<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\test\old\DataStore;

use PHPUnit\Framework\TestCase;
use rollun\datastore\DataStore\Interfaces\DataStoreInterface;
use rollun\datastore\DataStore\Memory;
use rollun\datastore\DataStore\Traits\AutoIdGeneratorTrait;
use rollun\utils\IdGenerator;

class AutoIdGeneratorTraitTest extends TestCase
{
    /**
     * @var DataStoreInterface with AutoIdGeneratorTrait
     */
    protected $object;

    protected $idLength = 3;

    protected $idCharSet = "QWERTYUIOPASDFGHJKLZXCVBNM0123456789";

    public function setUp()
    {
        $idLength = $this->idLength;
        $idCharSet = $this->idCharSet;
        $this->object = new class($idLength, $idCharSet) extends Memory
        {
            use AutoIdGeneratorTrait;

            /**
             *  constructor.
             * @param int $idLength
             * @param string $idCharSet
             */
            public function __construct($idLength = 10, $idCharSet = "QWERTYUIOPASDFGHJKLZXCVBNM0123456789")
            {
                $this->idGenerator = new IdGenerator($idLength, $idCharSet);
                parent::__construct();
            }

            /**
             * @param $itemData
             * @param bool $rewriteIfExist
             * @return array|mixed
             * @throws \rollun\datastore\DataStore\DataStoreException
             */
            public function create($itemData, $rewriteIfExist = false)
            {
                $itemData = $this->prepareItem($itemData);

                return parent::create($itemData, $rewriteIfExist);
            }
        };
    }

    public function testCreateWithoutId()
    {
        $item = $this->object->create(
            [
                "name" => "test",
            ]
        );
        $this->assertNotEmpty($item[$this->object->getIdentifier()]);
    }

    public function testCreateWithId()
    {
        $id = "MY_ID";
        $item = $this->object->create(
            [
                $this->object->getIdentifier() => $id,
                "name" => "test",
            ]
        );
        $this->assertEquals($id, $item[$this->object->getIdentifier()]);
    }

    /**
     * @expectedException \rollun\datastore\DataStore\DataStoreException
     * @expectedExceptionMessage Can't generate id.
     */
    public function testCantGenerateIdDataStoreException()
    {
        $len = pow(strlen($this->idCharSet), $this->idLength) + 10;
        for ($i = 0; $i < $len; $i++) {
            $this->object->create(
                [
                    "num" => $i,
                ]
            );
        }
    }
}
