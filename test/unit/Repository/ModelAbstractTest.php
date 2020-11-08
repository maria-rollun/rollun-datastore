<?php


namespace rollun\test\unit\Repository;


use PHPUnit\Framework\TestCase;
use rollun\repository\Interfaces\ModelCastingInterface;
use rollun\repository\ModelAbstract;

class ModelAbstractTest extends TestCase
{
    public function testSetAttributeByMethod()
    {
        $model = new class extends ModelAbstract {};

        $model->setAttribute('field', 'test');

        $this->assertEquals('test', $model->getAttributes()['field']);
    }

    public function testSetAttributeByProperty()
    {
        $model = new class extends ModelAbstract {};

        $model->field = 'test';

        $this->assertEquals('test', $model->getAttributes()['field']);
    }

    public function testGetAttributeByMethod()
    {
        $model = new class extends ModelAbstract {};
        $model->field = 'test';

        $field = $model->getAttribute('field');

        $this->assertEquals('test', $field);
    }

    public function getGetAttributeProperty()
    {
        $model = new class extends ModelAbstract {};
        $model->field = 'test';

        $field = $model->field;

        $this->assertEquals('test', $field);
    }

    public function testFillAttributes()
    {
        $data = [
            'field' => 'test',
            'name' => 'hello',
        ];
        $model = new class($data) extends ModelAbstract {};

        $this->assertEquals($data['field'], $model->getAttributes()['field']);
        $this->assertEquals($data['name'], $model->getAttributes()['name']);
    }

    public function testHidden()
    {
        $data = [
            'field' => 'test',
            'name' => 'hello',
            'hidden' => true,
        ];
        $model = new class($data) extends ModelAbstract {
            public function hidden(): array
            {
                return ['hidden'];
            }
        };

        $array = $model->toArray();

        $this->assertFalse(isset($array['hidden']));
    }

    public function testSetMutatedAttribute()
    {
        $data = [
            'field' => 'test',
            'name' => 'hello',
        ];
        $model = new class($data) extends ModelAbstract {
            public function setFieldAttribute($value)
            {
                return 'mutated-' . $value;
            }
        };

        $expected = 'mutated-' . $data['field'];
        $this->assertEquals($expected, $model->field);
    }

    public function testGetMutatedAttribute()
    {
        $data = [
            'field' => 'test',
            'name' => 'hello',
        ];
        $model = new class($data) extends ModelAbstract {
            public function getFieldAttribute($value)
            {
                return 'mutated-' . $value;
            }
        };

        $expected = 'mutated-' . $data['field'];
        $this->assertEquals($expected, $model->field);
    }

    public function testIsChanged()
    {
        $data = [
            'field' => 'test',
            'name' => 'hello',
        ];
        $model = new class($data, true) extends ModelAbstract {
            public function setFieldAttribute($value)
            {
                return 'mutated-' . $value;
            }
        };

        $this->assertFalse($model->isChanged());

        $model->field = 'changed';

        $this->assertTrue($model->isChanged());
    }

    public function testGetChangedAttributes()
    {
        $data = [
            'field' => 'test',
            'name' => 'hello',
        ];
        $model = new class($data, true) extends ModelAbstract {};

        $this->assertEmpty($model->getChanges());

        $model->field = 'changed';

        $this->assertSame(['field' => 'changed'], $model->getChanges());
    }

    /*public function testGetMutatedAttributes()
    {
        $data = [
            'field' => 'test',
            'name' => 'hello',
        ];
        $model = new class($data) extends ModelAbstract {
            public function getFieldAttribute($value)
            {
                return 'mutated-' . $value;
            }
        };
        $attributes = $model->getAttributes();
        $expected = 'mutated-' . $data['field'];
        $this->assertEquals($expected, $attributes['field']);
    }*/

    public function testChangedNumeric()
    {
        $data = [
            'field' => '1.0',
        ];
        $model = new class($data, true) extends ModelAbstract {};

        $model->field = '1.00';

        $this->assertEmpty($model->getChanges());
    }

    public function testChangesNew()
    {
        $data = [
            'field' => 'test',
        ];
        $model = new class($data) extends ModelAbstract {
            public function castJsonToArray()
            {

            }
        };

        $this->assertEquals($data, $model->getChanges());

        $model->field = 'hello';

        $this->assertEquals(['field' => 'hello'], $model->getChanges());
    }

    public function testCasing()
    {
        $data = [
            'field1' => '1234',
            'field2' => '12.34',
            'field3' => ['key' => 'value'],
            'field4' => ['key' => 'value'],
            'field5' => (object) ['key' => 'value'],
            'field6' => ['key' => 'value'],
            'field7' => ['value1', 'value2'],
        ];

        $model = new class($data) extends ModelAbstract {
            protected $casting = [
                'field1' => ModelCastingInterface::CAST_INT,
                'field2' => ModelCastingInterface::CAST_FLOAT,
                'field3' => ModelCastingInterface::CAST_JSON,
                'field4' => ModelCastingInterface::CAST_SERIALIZE,
                'field5' => ModelCastingInterface::CAST_ARRAY,
                'field6' => ModelCastingInterface::CAST_OBJECT,
            ];
            public function __construct($attributes = [], $exists = false)
            {
                $custom = new class() implements ModelCastingInterface{
                    public function get($value)
                    {
                        return explode('/', $value);
                    }

                    public function set($value)
                    {
                        return str_replace(['1', '2'], ['-one', '-two'], implode('/', $value));
                    }
                };
                $this->casting['field7'] = get_class($custom);
                parent::__construct($attributes, $exists);
            }
        };

        $this->assertIsInt($model->field1);
        $this->assertIsFloat($model->field2);
        $this->assertIsObject($model->field3);
        $this->assertIsArray($model->field4);
        $this->assertIsArray($model->field5);
        $this->assertIsObject($model->field6);
        $this->assertEquals(['value-one', 'value-two'], $model->field7);
    }
}