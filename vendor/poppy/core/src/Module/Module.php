<?php

namespace Poppy\Core\Module;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use JsonSerializable;
use Poppy\Framework\Classes\Traits\HasAttributesTrait;

/**
 * Class Module.
 */
class Module implements Arrayable, ArrayAccess, JsonSerializable
{
    use HasAttributesTrait;

    /**
     * Module constructor.
     * @param $slug
     */
    public function __construct($slug)
    {
        $this->attributes = [
            'directory' => poppy_path($slug),
            'namespace' => poppy_class($slug),
            'slug'      => $slug,
            'enabled'   => app('poppy')->isEnabled($slug),
        ];
    }

    /**
     * @return string
     */
    public function directory(): string
    {
        return $this->get('directory');
    }

    /**
     * @return string
     */
    public function namespace(): string
    {
        return $this->get('namespace');
    }

    /**
     * @return string
     */
    public function slug(): string
    {
        return $this->get('slug');
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return (bool) $this->offsetGet('enabled');
    }

    /**
     * 为了兼容存在
     * @return Collection
     * @deprecated
     */
    public function pages(): Collection
    {
        return collect($this->get('pages', []))->map(function ($definition, $identification) {
            $definition['initialization']['identification'] = $identification;
            unset($definition['initialization']['tabs']);

            return $definition['initialization'];
        })->groupBy('target');
    }

    /**
     * @return bool
     */
    public function validate(): bool
    {
        return $this->offsetExists('name')
            && $this->offsetExists('identification')
            && $this->offsetExists('description')
            && $this->offsetExists('authors');
    }
}
