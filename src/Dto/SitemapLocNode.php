<?php

namespace App\Dto;

use App\Enum\PostType;
use DateTime;

final class SitemapLocNode
{
    /** @deprecated */
    public string $location;
    public DateTime $lastmod;
    public PostType $type;
    public string $clubId;
}
