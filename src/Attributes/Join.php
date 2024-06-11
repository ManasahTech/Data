<?php

declare(strict_types=1);

namespace ManasahTech\Data\Attributes;

use Attribute;
use BackedEnum;


#[Attribute(Attribute::TARGET_PROPERTY)]
final class Join implements MappingAttribute 
{
}