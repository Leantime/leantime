<?php

/**
 * League.Csv (https://csv.thephpleague.com)
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace League\Csv;

use ArrayIterator;
use CallbackFilterIterator;
use Iterator;
use LimitIterator;
use function array_reduce;

/**
 * Criteria to filter a {@link Reader} object.
 */
class Statement
{
    /** @var array<callable> Callables to filter the iterator. */
    protected array $where = [];
    /** @var array<callable> Callables to sort the iterator. */
    protected array $order_by = [];
    /** iterator Offset. */
    protected int $offset = 0;
    /** iterator maximum length. */
    protected int $limit = -1;

    /**
     * @throws Exception
     */
    public static function create(callable $where = null, int $offset = 0, int $limit = -1): self
    {
        $stmt = new self();
        if (null !== $where) {
            $stmt = $stmt->where($where);
        }

        return $stmt->offset($offset)->limit($limit);
    }

    /**
     * Set the Iterator filter method.
     */
    public function where(callable $where): self
    {
        $clone = clone $this;
        $clone->where[] = $where;

        return $clone;
    }

    /**
     * Set an Iterator sorting callable function.
     */
    public function orderBy(callable $order_by): self
    {
        $clone = clone $this;
        $clone->order_by[] = $order_by;

        return $clone;
    }

    /**
     * Set LimitIterator Offset.
     *
     * @throws Exception if the offset is lesser than 0
     */
    public function offset(int $offset): self
    {
        if (0 > $offset) {
            throw InvalidArgument::dueToInvalidRecordOffset($offset, __METHOD__);
        }

        if ($offset === $this->offset) {
            return $this;
        }

        $clone = clone $this;
        $clone->offset = $offset;

        return $clone;
    }

    /**
     * Set LimitIterator Count.
     *
     * @throws Exception if the limit is lesser than -1
     */
    public function limit(int $limit): self
    {
        if (-1 > $limit) {
            throw InvalidArgument::dueToInvalidLimit($limit, __METHOD__);
        }

        if ($limit === $this->limit) {
            return $this;
        }

        $clone = clone $this;
        $clone->limit = $limit;

        return $clone;
    }

    /**
     * Execute the prepared Statement on the {@link Reader} object.
     *
     * @param array<string> $header an optional header to use instead of the CSV document header
     */
    public function process(TabularDataReader $tabular_data, array $header = []): TabularDataReader
    {
        if ([] === $header) {
            $header = $tabular_data->getHeader();
        }

        $iterator = $tabular_data->getRecords($header);
        $iterator = array_reduce($this->where, [$this, 'filter'], $iterator);
        $iterator = $this->buildOrderBy($iterator);

        return new ResultSet(new LimitIterator($iterator, $this->offset, $this->limit), $header);
    }

    /**
     * Filters elements of an Iterator using a callback function.
     */
    protected function filter(Iterator $iterator, callable $callable): CallbackFilterIterator
    {
        return new CallbackFilterIterator($iterator, $callable);
    }

    /**
     * Sort the Iterator.
     */
    protected function buildOrderBy(Iterator $iterator): Iterator
    {
        if ([] === $this->order_by) {
            return $iterator;
        }

        $compare = function (array $record_a, array $record_b): int {
            foreach ($this->order_by as $callable) {
                if (0 !== ($cmp = $callable($record_a, $record_b))) {
                    return $cmp;
                }
            }

            return $cmp ?? 0;
        };

        $it = new ArrayIterator();
        foreach ($iterator as $offset => $value) {
            $it[$offset] = $value;
        }
        $it->uasort($compare);

        return $it;
    }
}
