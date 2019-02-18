<?php

namespace Arbory\Base\Admin;

use Arbory\Base\Admin\Layout\AbstractLayout;
use Arbory\Base\Admin\Layout\LayoutInterface;
use Arbory\Base\Admin\Widgets\Breadcrumbs;
use Arbory\Base\Html\Html;
use Closure;
use Arbory\Base\Admin\Layout\Row;
use Arbory\Base\Html\Elements\Content;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Collection;

/**
 * Class Layout
 * @package Arbory\Base\Admin
 */
class Layout extends AbstractLayout implements Renderable,LayoutInterface
{
    /**
     * @var Collection|Row[]
     */
    protected $rows;

    protected $bodyClass;

    /**
     * @var Breadcrumbs
     */
    protected $breadcrumbs;

    /**
     * @var LayoutInterface[]
     */
    protected $layouts = [];

    /**
     * @var Pipeline
     */
    protected $pipeline;

    /**
     * Layout constructor.
     * @param Closure|null $callback
     */
    public function __construct( Closure $callback = null )
    {
        $this->pipeline = new Pipeline(app());
        $this->rows = new Collection();

        if( $callback instanceof Closure )
        {
            $callback( $this );
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->render();
    }

    public function layout( LayoutInterface $layout )
    {
        $this->breadcrumbs = $layout->breadcrumbs($this->breadcrumbs);

        return $this->row( $layout );
    }

    /**
     * @param mixed $content
     * @return Layout
     */
    public function body( $content )
    {
        return $this->row( $content );
    }

    /**
     * @param $breadcrumbs
     *
     * @return $this
     */
    public function breadcrumbs($breadcrumbs)
    {
        $this->breadcrumbs = $breadcrumbs;

        return $this;
    }

    /**
     * @param $content
     * @return $this
     */
    public function row( $content )
    {
        $this->rows->push( $this->createRow( $content ) );

        return $this;
    }

    /**
     * @param $content
     * @return Row
     */
    protected function createRow( $content )
    {
        if( $content instanceof Closure )
        {
            $row = new Row();
            $content( $row );

            return $row;
        }

        return new Row( $content );
    }

    /**
     * @param $class
     * @return Layout
     */
    public function bodyClass( $class )
    {
        $this->bodyClass = $class;

        return $this;
    }

    public function use($layout)
    {
        $this->layouts[] = $layout;
    }

    public function build()
    {

    }

    /**
     * @return string
     */
    public function render()
    {
        $variables = [
            'content' => parent::render(),
            'bodyClass' => $this->bodyClass,
        ];

        return view( 'arbory::controllers.resource.layout', $variables )->render();
    }

    /**
     * @return Breadcrumbs
     */
    public function getBreadcrumbs(): ?Breadcrumbs
    {
        return $this->breadcrumbs;
    }

    public function setContent($content): LayoutInterface
    {
        $this->content = $content;

        return $this;
    }

    public function getContent()
    {
        return $this->content;
    }

}
