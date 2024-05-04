<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\events;

enum Priority: int
{
    case API = 20;
    case Client = 10;
}
