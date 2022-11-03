<?php

use Opis\Database\Connection;
use Opis\Database\ORM\Entity;

require __DIR__ . "/vendor/autoload.php";

$connection = new Connection(
    'mysql:host=localhost;dbname=r3_t0001',
    'root',
    ''
);
class Sale extends Entity implements \Opis\Database\ORM\IMappableEntity {

    public static function mapEntity(\Opis\Database\ORM\IEntityMapper $mapper)
    {
        $mapper->primaryKey('fcx13_idvnd')->table('fcx13_head');
    }
}

Entity::setConnection($connection);

$sales = Sale::get();

echo "<pre>";
print_r($sales);
echo "</pre>";