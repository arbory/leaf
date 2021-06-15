<?php

namespace Arbory\Base\Nodes;

use Illuminate\Support\Collection;

/**
 * Class ContentTypesRepository.
 */
class ContentTypeRegister
{
    /**
     * @var \Illuminate\Support\Collection
     */
    protected $contentTypes;

    /**
     * ContentTypesRepository constructor.
     */
    public function __construct()
    {
        $contentTypes = collect(config('arbory.content_types', []));
        $contentTypeNames = $contentTypes->map(function ($item) {
            return new ContentTypeDefinition($item);
        });

        $this->contentTypes = $contentTypes->combine($contentTypeNames);
    }

    /**
     * @param ContentTypeDefinition $definition
     * @return void
     */
    public function register(ContentTypeDefinition $definition)
    {
        $this->contentTypes->put($definition->getModel(), $definition);
    }

    /**
     * @param string $class
     * @return ContentTypeDefinition|null
     */
    public function findByModelClass(string $class)
    {
        return $this->contentTypes->get($class);
    }

    /**
     * @param Node $parent
     * @return Collection
     */
    public function getAllowedChildTypes(Node $parent): Collection
    {
        if (! $parent->content || ! method_exists($parent->content, 'getAllowedChildTypes')) {
            return $this->getAllContentTypes();
        }

        $allowed = $parent->content->getAllowedChildTypes($parent, $this->getAllContentTypes());

        if (is_array($allowed)) {
            $allowed = new Collection($allowed);
        }

        return $this->mapToDefinitions($allowed);
    }

    /**
     * @return \Illuminate\Support\Collection|string[]
     */
    public function getAllContentTypes()
    {
        return $this->contentTypes;
    }

    /**
     * @param $type
     * @return bool
     */
    public function isValidContentType($type)
    {
        return $this->contentTypes->has($type);
    }

    /**
     * @param Collection $mappable
     * @return Collection
     */
    protected function mapToDefinitions(Collection $mappable): Collection
    {
        return $mappable->mapWithKeys(function ($item) {
            if ($item instanceof ContentTypeDefinition) {
                return $item;
            }

            return [$item => $this->findByModelClass($item)];
        });
    }
}
