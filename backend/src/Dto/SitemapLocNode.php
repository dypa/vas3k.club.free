<?php

namespace App\Dto;

use App\Enum\PostType;

final class SitemapLocNode
{
    public \DateTime $lastmod;
    public PostType $type;
    public string $clubId;
}
