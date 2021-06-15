<?php

namespace Arbory\Base\Nodes;

use Arbory\Base\Pages\PageInterface;
use Baum\NestedSet\Node as BaumNode;
use GoldSpecDigital\LaravelEloquentUUID\Database\Eloquent\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Arbory\Base\Repositories\NodesRepository;
use Arbory\Base\Support\Activation\HasActivationDates;

/**
 * Class Node.
 *
 * @property Model|null $content
 * @property string $name
 * @property string $id
 * @property string $content_type
 * @property string $slug
 * @property bool $active
 */
class Node extends Model
{
    use Uuid;
    use HasActivationDates;
    use BaumNode;

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var string
     */
    protected $keyType = 'string';

    /**
     * @var string
     */
    protected $leftColumnName = 'lft';

    /**
     * @var string
     */
    protected $rightColumnName = 'rgt';

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'slug',
        'content_type',
        'content_id',
        'item_position',
        'locale',
        'meta_title',
        'meta_author',
        'meta_keywords',
        'meta_description',
        'activate_at',
        'expire_at',
    ];

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function save(array $options = [])
    {
        (new NodesRepository)->setLastUpdateTimestamp(time());

        return parent::save($options);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo|PageInterface
     */
    public function content()
    {
        return $this->morphTo();
    }

    /**
     * @return \Illuminate\Support\Collection|Node[]
     */
    public function parents()
    {
        return $this->ancestors()->get();
    }

    /**
     * Use ancestors() instead.
     *
     * @deprecated
     *
     * @return Builder
     */
    public function parentsQuery()
    {
        return $this->newQuery()
            ->where($this->getLeftColumnName(), '<', (int) $this->getLeft())
            ->where($this->getRightColumnName(), '>', (int) $this->getRight())
            ->orderBy($this->getDepthColumnName(), 'asc');
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getContentType()
    {
        return $this->content_type;
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @return string
     */
    public function getUri()
    {
        $uri = [];

        foreach ($this->parents() as $parent) {
            $uri[] = $parent->getSlug();
        }

        $uri[] = $this->getSlug();

        return implode('/', $uri);
    }

    /**
     * @param       $name
     * @param array $parameters
     * @param bool $absolute
     * @return string|null
     */
    public function getUrl($name, array $parameters = [], $absolute = true)
    {
        $routes = app('routes');
        $routeName = 'node.'.$this->getKey().'.'.$name;
        $route = $routes->getByName($routeName);

        return $route ? route($routeName, $parameters, $absolute) : null;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @return bool
     */
    public function getActiveAttribute()
    {
        if (! $this->isPublic()) {
            return false;
        }

        if ($this->parents()->isNotEmpty()) {
            return $this->parent()->first()->isActive();
        }

        return true;
    }

    /**
     * @return bool
     */
    public function isPublic()
    {
        return $this->hasActivated() && ! $this->hasExpired();
    }

    /**
     * Return parent id (legacy support).
     *
     * @return mixed
     */
    public function getParentId()
    {
        return $this->getAttribute($this->getParentColumnName());
    }
}
