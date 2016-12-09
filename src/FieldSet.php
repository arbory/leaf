<?php

namespace CubeSystems\Leaf;

use CubeSystems\Leaf\Fields\FieldInterface;
use Illuminate\Support\Collection;

/**
 * Class FieldSet
 * @package CubeSystems\Leaf
 */
class FieldSet extends Collection
{
    /**
     * FieldSet constructor.
     * @param array $items
     */
    public function __construct( $items = [] )
    {
        parent::__construct();

        foreach( $items as $item )
        {
            $this->add( $item );
        }
    }

    /**
     * @param FieldInterface $field
     * @return FieldInterface
     */
    public function add( FieldInterface $field )
    {
        $field->setFieldSet( $this );

        $this->push( $field );

        return $field;
    }

    /**
     * @return FieldInterface[]|array
     */
    public function getFields()
    {
        return $this->all();
    }

    /**
     * @param $name
     * @return FieldInterface
     */
    public function findFieldByName( $name )
    {
        return $this->filter( function ( FieldInterface $field ) use ( $name )
        {
            return $field->getName() === $name;
        } )->first();
    }

    public function triggerUpdate( $model, $input )
    {
        $this->each( function ( FieldInterface $field ) use ( $model, $input )
        {
            $field->beforeModelSave( $model, $input );
        } );
    }

    public function triggerAfterSave( $model, $input )
    {
        $this->each( function ( FieldInterface $field ) use ( $model, $input )
        {
            $field->afterModelSave( $model, $input );
        } );
    }
}
