<?php
namespace rock\db\common;

use rock\helpers\Instance;
use rock\helpers\InstanceException;


/**
 * ActiveDataProvider implements a data provider based on {@see \rock\db\Query} and  {@see \rock\db\ActiveQuery}.
 *
 * ActiveDataProvider provides data by performing DB queries using {@see \rock\db\common\ActiveDataProvider::$query}.
 *
 * The following is an example of using ActiveDataProvider to provide ActiveRecord instances:
 *
 * ```php
 * $provider = new ActiveDataProvider([
 *     'query' => Post::find(),
 *     'pagination' => [
 *         'limit' => 20,
 *         'sort' => SORT_DESC,
 *         'pageLimit' => 5,
 *         'page' => (int)$_GET['page']
 *     ],
 * ]);
 *
 * $posts = $provider->getModels(); // returns the posts in the current page
 * $provider->getPagination(); // returns ActiveDataPagination
 * ```
 *
 * And the following example shows how to use ActiveDataProvider without ActiveRecord:
 *
 * ```php
 * $query = new Query;
 * $provider = new ActiveDataProvider([
 *     'query' => $query->from('post'),
 *     'pagination' => [
 *         'limit' => 20,
 *         'sort' => SORT_DESC,
 *         'pageLimit' => 5,
 *         'page' => (int)$_GET['page'],
 *     ],
 * ]);
 *
 * $posts = $provider->getModels(); // returns the posts in the current page
 * $provider->getPagination(); // returns ActiveDataPagination
 * ```
 */
class ActiveDataProvider extends BaseDataProvider
{
    /**
     * @var QueryInterface the query that is used to fetch data models and {@see \rock\db\common\BaseDataProvider::$totalCount}
     * if it is not explicitly set.
     */
    public $query;
    /**
     * @var string|callable the column that is used as the key of the data models.
     * This can be either a column name, or a callable that returns the key value of a given data model.
     *
     * If this is not set, the following rules will be used to determine the keys of the data models:
     *
     * - If {@see \rock\db\common\ActiveDataProvider::$query} is an  {@see \rock\db\ActiveQuery} instance, the primary keys of {@see \rock\db\ActiveQuery::$modelClass} will be used.
     * - Otherwise, the keys of the {@see \rock\db\common\BaseDataProvider::$models} array will be used.
     *
     * @see getKeys()
     */
    public $key;
    /**
     * @var ConnectionInterface|array|string the DB connection object or the application component ID of the DB connection.
     * If not set, the default DB connection will be used.
     */
    public $connection;
    /**
     * Calculate sub-attributes (e.g `category.id => [category][id]`).
     * @var bool
     */
    public $subattributes = true;

    /**
     * Initializes the DB connection component.
     * This method will initialize the {@see \rock\db\common\ActiveDataProvider::$connection} property to make sure it refers to a valid DB connection.
     * @throws InstanceException if {@see \rock\db\common\ActiveDataProvider::$connection} is invalid.
     */
    public function init()
    {
        parent::init();
        if (is_string($this->connection)) {
            $this->connection = Instance::ensure($this->connection, \rock\db\Connection::className());
        }
    }

    /**
     * @inheritdoc
     */
    protected function prepareModels()
    {
        if (!$this->query instanceof QueryInterface) {
            throw new DbException('The "query" property must be an instance of a class that implements the QueryInterface e.g. yii\db\Query or its subclasses.');
        }
        $query = clone $this->query;
        if (($pagination = $this->getPagination()) !== false) {
            $pagination->totalCount = $this->getTotalCount();
            $query->limit($pagination->limit)->offset($pagination->offset);
        }

        return $query->all($this->connection, $this->subattributes);
    }

    /**
     * @inheritdoc
     */
    protected function prepareKeys($models)
    {
        $keys = [];
        if ($this->key !== null) {
            foreach ($models as $model) {
                if (is_string($this->key)) {
                    $keys[] = $model[$this->key];
                } else {
                    $keys[] = call_user_func($this->key, $model);
                }
            }

            return $keys;
        } elseif ($this->query instanceof ActiveQueryInterface) {
            /* @var $class ActiveRecordInterface */
            $class = $this->query->modelClass;
            $pks = $class::primaryKey();
            if (count($pks) === 1) {
                $pk = $pks[0];
                foreach ($models as $model) {
                    $keys[] = $model[$pk];
                }
            } else {
                foreach ($models as $model) {
                    $kk = [];
                    foreach ($pks as $pk) {
                        $kk[$pk] = $model[$pk];
                    }
                    $keys[] = $kk;
                }
            }
        }
        return $keys ? : array_keys($models);
    }

    /**
     * @inheritdoc
     */
    protected function prepareTotalCount()
    {
        if (!$this->query instanceof QueryInterface) {
            throw new DbException('The "query" property must be an instance of a class that implements the QueryInterface e.g. yii\db\Query or its subclasses.');
        }
        $query = clone $this->query;
        return (int) $query->limit(-1)->offset(-1)->orderBy([])->count('*', $this->connection);
    }
}