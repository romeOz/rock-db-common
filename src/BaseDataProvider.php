<?php

namespace rock\db\common;

use rock\base\ObjectInterface;
use rock\base\ObjectTrait;
use rock\helpers\Instance;


/**
 * BaseDataProvider provides a base class that implements the {@see \rock\common\db\DataProviderInterface}.
 *
 * @property integer $count The number of data models in the current page. This property is read-only.
 * @property array $keys The list of key values corresponding to {@see \rock\common\db\BaseDataProvider::$models}. Each data model in {@see \rock\common\db\BaseDataProvider::$models} is
 * uniquely identified by the corresponding key value in this array.
 * @property array $models The list of data models in the current page.
 * @property PaginationProvider|boolean $pagination The pagination object. If this is false, it means the pagination
 * is disabled. Note that the type of this property differs in getter and setter. See {@see \rock\common\db\BaseDataProvider::getPagination()} and
 * {@see \rock\common\db\BaseDataProvider::setPagination()} for details.
 * @property integer $totalCount Total number of possible data models.
 *
 */
abstract class BaseDataProvider implements ObjectInterface, DataProviderInterface
{
    use ObjectTrait;

    private $_sort;
    private $_pagination;
    private $_keys;
    private $_models;
    private $_totalCount;


    /**
     * Prepares the data models that will be made available in the current page.
     * @return array the available data models
     */
    abstract protected function prepareModels();

    /**
     * Prepares the keys associated with the currently available data models.
     * @param array $models the available data models
     * @return array the keys
     */
    abstract protected function prepareKeys($models);

    /**
     * Returns a value indicating the total number of data models in this data provider.
     * @return integer total number of data models in this data provider.
     */
    abstract protected function prepareTotalCount();

    /**
     * Prepares the data models and keys.
     *
     * This method will prepare the data models and keys that can be retrieved via
     * {@see \rock\db\common\BaseDataProvider::getModels()} and {@see \rock\db\common\BaseDataProvider::getKeys()}.
     *
     * This method will be implicitly called by {@see \rock\db\common\BaseDataProvider::getModels()} and {@see \rock\db\common\BaseDataProvider::getKeys()} if it has not been called before.
     *
     * @param boolean $forcePrepare whether to force data preparation even if it has been done before.
     */
    public function prepare($forcePrepare = false)
    {
        if ($forcePrepare || $this->_models === null) {
            $this->_models = $this->prepareModels();
        }
        if ($forcePrepare || $this->_keys === null) {
            $this->_keys = $this->prepareKeys($this->_models);
        }
    }

    /**
     * Returns the data models in the current page.
     * @return array the list of data models in the current page.
     */
    public function getModels()
    {
        $this->prepare();

        return $this->_models;
    }

    /**
     * Sets the data models in the current page.
     * @param array $models the models in the current page
     */
    public function setModels($models)
    {
        $this->_models = $models;
    }

    /**
     * Returns the key values associated with the data models.
     * @return array the list of key values corresponding to {@see \rock\common\db\BaseDataProvider::$models}. Each data model in {@see \rock\common\db\BaseDataProvider::$models}
     * is uniquely identified by the corresponding key value in this array.
     */
    public function getKeys()
    {
        $this->prepare();

        return $this->_keys;
    }

    /**
     * Sets the key values associated with the data models.
     * @param array $keys the list of key values corresponding to {@see \rock\common\db\BaseDataProvider::$models}.
     */
    public function setKeys($keys)
    {
        $this->_keys = $keys;
    }

    /**
     * Returns the number of data models in the current page.
     * @return integer the number of data models in the current page.
     */
    public function getCount()
    {
        return count($this->getModels());
    }

    /**
     * Returns the total number of data models.
     * When {@see \rock\db\common\PaginationProvider} is false, this returns the same value as {@see \rock\db\common\BaseDataProvider::count()}.
     * Otherwise, it will call {@see \rock\db\common\BaseDataProvider::prepareTotalCount()} to get the count.
     * @return integer total number of possible data models.
     */
    public function getTotalCount()
    {
        if ($this->getPagination() === false) {
            return $this->getCount();
        } elseif ($this->_totalCount === null) {
            $this->_totalCount = $this->prepareTotalCount();
        }

        return $this->_totalCount;
    }

    /**
     * Sets the total number of data models.
     * @param integer $value the total number of data models.
     */
    public function setTotalCount($value)
    {
        $this->_totalCount = $value;
    }

    /**
     * Returns the pagination object used by this data provider.
     * Note that you should call {@see \rock\db\common\BaseDataProvider::prepare()} or {@see \rock\db\common\BaseDataProvider::getModels()} first to get correct values
     * of {@see \rock\db\common\PaginationProvider::$totalCount} and {@see \rock\db\common\PaginationProvider::$pageCount}.
     * @return PaginationProvider|boolean the pagination object. If this is false, it means the pagination is disabled.
     */
    public function getPagination()
    {
        if ($this->_pagination === null) {
            $this->setPagination([]);
        }

        return $this->_pagination;
    }

    /**
     * Sets the pagination for this data provider.
     * @param array|PaginationProvider|boolean $value the pagination to be used by this data provider.
     * This can be one of the following:
     *
     * - a configuration array for creating the pagination object. The "class" element defaults
     *   to 'rock\db\common\PaginationProvider'
     * - an instance of {@see \rock\db\common\PaginationProvider} or its subclass
     * - false, if pagination needs to be disabled.
     *
     * @throws DbException
     */
    public function setPagination($value)
    {
        if (is_array($value)) {
            $config = ['class' => PaginationProvider::className()];

            $this->_pagination = Instance::ensure(array_merge($config, $value));
        } elseif ($value instanceof PaginationProvider || $value === false) {
            $this->_pagination = $value;
        } else {
            throw new DbException('Only Pagination instance, configuration array or false is allowed.');
        }
    }

    /**
     * @return Sort|boolean the sorting object. If this is false, it means the sorting is disabled.
     */
    public function getSort()
    {
        if ($this->_sort === null) {
            $this->setSort([]);
        }

        return $this->_sort;
    }

    /**
     * Sets the sort definition for this data provider.
     * @param array|Sort|boolean $value the sort definition to be used by this data provider.
     * This can be one of the following:
     *
     * - a configuration array for creating the sort definition object. The "class" element defaults
     *   to 'rock\db\common\Sort'
     * - an instance of {@see \rock\db\common\Sort} or its subclass
     * - false, if sorting needs to be disabled.
     *
     * @throws DbException
     */
    public function setSort($value)
    {
        if (is_array($value)) {
            $config = ['class' => Sort::className()];
            $this->_sort = Instance::ensure(array_merge($config, $value));
        } elseif ($value instanceof Sort || $value === false) {
            $this->_sort = $value;
        } else {
            throw new DbException('Only Sort instance, configuration array or false is allowed.');
        }
    }

    /**
     * Refreshes the data provider.
     * After calling this method, if {@see \rock\db\common\BaseDataProvider::getModels()}, {@see \rock\db\common\BaseDataProvider::getKeys()} or {@see \rock\db\common\BaseDataProvider::getTotalCount()} is called again,
     * they will re-execute the query and return the latest data available.
     */
    public function refresh()
    {
        $this->_totalCount = null;
        $this->_models = null;
        $this->_keys = null;
    }
}