<?php

declare(strict_types=1);

namespace ManasahTech\Data\Attributes;

use Attribute;


#[Attribute(Attribute::TARGET_CLASS)]
final class Table implements MappingAttribute 
{

}