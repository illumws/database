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
use Illum\Database\SQL\Delete;
use Illum\Database\SQL\Select;
use Illum\Database\SQL\Update;
use Illum\Database\SQL\Where;
use Illum\Database\SQL\WhereStatement;
use Illuminate\Container\Container;
use Illuminate\Support\Collection;
use Illum\Database\Connection;

/**
 * @mixin IDataMapper
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
     * @return array|mixed
     */
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

    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->orm(), $name], $arguments);
    }

    /**
     * @param $name
     * @return bool|mixed
     */
    public function __isset($name)
    {
        return $this->orm()->hasColumn($name);
    }

    public function __invoke($column)
    {
        $args = func_get_args();
        if (isset($args[1])){
            return $this->orm()->setColumn($column, $args[1]);
        }
        return $this->orm()->getColumn($column);
    }

    public function __get($column)
    {
        return $this->orm()->getColumn($column);
    }

    public function __set($column, $value)
    {
        return $this->orm()->setColumn($column, $value);
    }
}
