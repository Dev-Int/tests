<?php

declare(strict_types=1);

namespace Admin\Tests\DataBuilder;

interface DataBuilderInterface
{
    public function build(): object;
}
