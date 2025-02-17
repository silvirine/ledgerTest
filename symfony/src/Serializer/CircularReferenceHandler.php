<?php

namespace App\Serializer;

class CircularReferenceHandler
{
    /**
     * This method is called when a circular reference is detected.
     *
     * @param object $object The object that caused the circular reference.
     * @return mixed A scalar value or an array representation of the object.
     */
    public function __invoke($object)
    {
        // Return the unique identifier of the object (assuming a getId() method exists)
        return method_exists($object, 'getId') ? $object->getId() : null;
    }
}
