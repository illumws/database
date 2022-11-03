<?php
/* ===========================================================================
 * Copyright 2018 Zindex Software
 * Copyright 2022 Alessandro Amos
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ============================================================================ */

namespace Illum\Database\ORM;

use Illum\Database\ORM\Core\{DataMapper, EntityMapper, EntityQuery};
use Illuminate\Support\Collection;
use Illum\Database\Connection;

/**
 * @method static static|null get(array $columns = [])
 * @method static static|null filter($names)
 * @method static Collection all(array $columns = [])
 * @method static int delete(bool $force = false, array $tables = [])
 * @method static int update(array $columns = [])
 * @method static int increment($column, $value = 1)
 * @method static int decrement($column, $value = 1)
 * @method static static|null find($id)
 * @method static Collection findAll(...$ids)
 */
abstract class Entity implements \JsonSerializable
{
    /**
     * @var Connection|null
     */
    private static ?Connection $connection = null;

    /** @var array */
    private $dataMapperArgs;

    /** @var  DataMapper|null */
    private $dataMapper;

    /**
     * Entity constructor.
     * @param EntityManager $entityManager
     * @param EntityMapper $entityMapper
     * @param array $columns
     * @param array $loaders
     * @param bool $isReadOnly
     * @param bool $isNew
     */
    final public function __construct(
        EntityManager $entityManager,
        EntityMapper $entityMapper,
        array $columns = [],
        array $loaders = [],
        bool $isReadOnly = false,
        bool $isNew = false
    ) {
        $this->dataMapperArgs = [$entityManager, $entityMapper, $columns, $loaders, $isReadOnly, $isNew];
    }

    /**
     * @return IDataMapper
     */
    final protected function orm(): IDataMapper
    {
        if ($this->dataMapper === null) {
            $this->dataMapper = new DataMapper(...$this->dataMapperArgs);
            unset($this->dataMapperArgs);
        }

        return $this->dataMapper;
    }

    /**
     * @return void
     */
    public function save(){
        self::make()->save($this);
    }

    /**
     * @param $name
     * @param mixed $value
     * @return mixed
     */
    public function column($name){
        $args = func_get_args();
        if (isset($args[1])){
            return $this->orm()->setColumn($name, $args[1]);
        }
        return $this->orm()->getColumn($name);
    }

    /**
     * @param $name
     * @return bool|mixed
     */
    public function hasColumn($name){
        return $this->orm()->hasColumn($name);
    }

    /**
     * @param Connection $connection
     * @return void
     */
    public static function setConnection(Connection $connection){
        self::$connection = $connection;
    }

    /**
     * @return Connection|null
     */
    public static function getConnection(): ?Connection
    {
        return self::$connection;
    }

    /**
     * @return EntityManager
     */
    protected static function make(): EntityManager
    {
        return new EntityManager(self::getConnection());
    }

    /**
     * @return Entity
     */
    public static function new(): Entity
    {
        return self::make()->create(static::class);
    }

    /**
     * @return Core\EntityQuery
     */
    public static function query(): Core\EntityQuery
    {
        return self::make()->query(static::class);
    }

    /**
     * @param $name
     * @return bool|mixed
     */
    public function __isset($name)
    {
        return $this->hasColumn($name);
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        return call_user_func_array([static::query(), $name], $arguments);
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * @return array|mixed
     */
    public function toArray()
    {
        return $this->orm()->getRawColumns();
    }

    public function __invoke($column)
    {
        return $this->column($column);
    }

    public function __get($column)
    {
        return $this->column($column);
    }

    public function __set($column, $value)
    {
        return $this->column($column, $value);
    }
}
