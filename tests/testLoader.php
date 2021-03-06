<?php

namespace Driade\Fixtures\Test;

use Driade\Fixtures\Loader;
use Illuminate\Database\Capsule\Manager as Capsule;

class testLoader extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {

        touch(__DIR__ . '/database.sqlite');

        $this->capsule = new Capsule;

        $this->capsule->addConnection([
            'driver'    => 'sqlite',
            'database'  => __DIR__ . '/database.sqlite',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
        ]);

        $this->capsule->bootEloquent();
        $this->capsule->setAsGlobal();
        $this->schema = $this->capsule->schema();

    }

    private function loadSeed($number)
    {
        include __DIR__ . '/tables/' . $number . ".php";
    }

    public function testHasMany()
    {
        $this->loadSeed(1);

        $user = Loader::load(__DIR__ . '/fixtures/hasMany.php');

        $this->assertInstanceOf('Driade\Fixtures\Test\Models\User', $user);

        $this->assertEquals(3, $user->orders()->count());

        foreach ($user->orders as $index => $order) {

            $this->assertInstanceOf('Driade\Fixtures\Test\Models\Order', $order);
            $this->assertSame($index + 1, $order->id);

            $this->assertNotEquals(0, $order->products->count());

            foreach ($order->products as $index2 => $product) {
                $this->assertInstanceOf('Driade\Fixtures\Test\Models\OrderProduct', $product);
                $this->assertSame($index * 2 + $index2 + 1, $product->id);
            };
        }
    }

    public function testBelongs()
    {
        $this->loadSeed(1);

        $order = Loader::load(__DIR__ . '/fixtures/belongs.php');

        $this->assertInstanceOf('Driade\Fixtures\Test\Models\Order', $order);
        $this->assertEquals(1, $order->id);
        $this->assertInstanceOf('Driade\Fixtures\Test\Models\User', $order->user);

        $this->assertEquals(1, $order->user->id);
    }

    public function testBelongsDirectInput()
    {
        $this->loadSeed(1);

        $order = Loader::load([
            'Driade\Fixtures\Test\Models\Order',
            'total' => 2,
            'user'  => [
                'Driade\Fixtures\Test\Models\User',
            ],
        ]);

        $this->assertInstanceOf('Driade\Fixtures\Test\Models\Order', $order);
        $this->assertEquals(1, $order->id);
        $this->assertInstanceOf('Driade\Fixtures\Test\Models\User', $order->user);

        $this->assertEquals(1, $order->user->id);
    }

    public function testComplex()
    {
        $this->loadSeed(1);

        $order = Loader::load(__DIR__ . '/fixtures/complex.php');

        $this->assertInstanceOf('Driade\Fixtures\Test\Models\Order', $order);
        $this->assertEquals(1, $order->id);
        $this->assertInstanceOf('Driade\Fixtures\Test\Models\User', $order->user);

        $this->assertEquals(1, $order->user->id);

        $this->assertInstanceOf('Driade\Fixtures\Test\Models\Courier', $order->courier);
        $this->assertEquals(1, $order->courier->id);
    }

    public function testHasOne()
    {
        $this->loadSeed(2);

        $owner = Loader::load(__DIR__ . '/fixtures/hasOne.php');

        $this->assertInstanceOf('Driade\Fixtures\Test\Models\Owner', $owner);
        $this->assertInstanceOf('Driade\Fixtures\Test\Models\Dog', $owner->dog);
    }

    public function testHasOneInverse()
    {
        $this->loadSeed(2);

        $dog = Loader::load(__DIR__ . '/fixtures/hasOne2.php');

        $this->assertInstanceOf('Driade\Fixtures\Test\Models\Dog', $dog);
        $this->assertInstanceOf('Driade\Fixtures\Test\Models\Owner', $dog->owner);
    }

    public function testBelongsToMany()
    {
        $this->loadSeed(3);

        $author = Loader::load(__DIR__ . '/fixtures/belongsToMany.php');

        $this->assertInstanceOf('Driade\Fixtures\Test\Models\Author', $author);

        $this->assertEquals(2, $author->books->count());

        foreach ($author->books as $index => $book) {
            $this->assertInstanceOf('Driade\Fixtures\Test\Models\Book', $book);
            $this->assertEquals($index + 1, $book->id);
        }
    }

    public function testPolymorphic()
    {
        $this->loadSeed(4);

        $photo = Loader::load(__DIR__ . '/fixtures/polymorphic.php');

        $this->assertInstanceOf('Driade\Fixtures\Test\Models\Photo', $photo);

        $this->assertInstanceOf('Driade\Fixtures\Test\Models\Staff', $photo->imageable);
    }

    public function testPolymorphic2()
    {
        $this->loadSeed(4);

        $staff = Loader::load(__DIR__ . '/fixtures/polymorphic2.php');

        $this->assertInstanceOf('Driade\Fixtures\Test\Models\Staff', $staff);

        $this->assertInstanceOf('Driade\Fixtures\Test\Models\Photo', $staff->photos->first());
    }

    public function testClassConstant()
    {
        $this->loadSeed(1);

        $user = Loader::load(__DIR__ . '/fixtures/classConstant.php');

        $this->assertInstanceOf('Driade\Fixtures\Test\Models\User', $user);
        $this->assertInstanceOf('Driade\Fixtures\Test\Models\Order', $user->orders->first());
    }

    public function testMultipleRoots()
    {
        $this->loadSeed(1);

        $users = Loader::load(__DIR__ . '/fixtures/multipleRoots.php');

        $this->assertTrue(is_array($users));
        $this->assertEquals(3, count($users));
    }

    public function testResolverMiddle()
    {
        $this->loadSeed(1);

        $user = Loader::load(__DIR__ . '/fixtures/resolveMiddle.php');

        $this->assertInstanceOf('Driade\Fixtures\Test\Models\User', $user);
        $this->assertInstanceOf('Driade\Fixtures\Test\Models\Order', $user->orders->first());
        $this->assertInstanceOf('Driade\Fixtures\Test\Models\OrderProduct', $user->orders->first()->products->first());
        $this->assertInstanceOf('Driade\Fixtures\Test\Models\Courier', $user->orders->first()->courier);
    }
}
