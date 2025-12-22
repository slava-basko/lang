<?php

namespace Basko\Lang\Stream;

interface StreamInterface
{
    public function reset();

    public function getPosition();

    public function isEof();

    public function peek();

    public function consume();

    public function expect($compareTo);
}
