<?php

namespace {BC}\UseCases\{Entity}\{Action}{Entity};

use Shared\Entities\VO\ResourceUuid;

interface {Action}{Entity}Request
{
    public function uuid(): ResourceUuid;
    public function param(): scalar|VO|ReadModel;
}
