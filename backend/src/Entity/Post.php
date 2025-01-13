<?php

namespace App\Entity;

use App\Enum\PostType;
use App\Repository\PostRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 *  @final Unable to create a proxy for a final class "App\Entity\Post" in prod env
 */
#[ORM\Entity(repositoryClass: PostRepository::class)]
#[ORM\Index(fields: ['postType'])]
/* final */ class Post
{
    #[ORM\Id, ORM\Column]
    public string $id;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    public ?\DateTime $createdAt;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    public \DateTime $lastModified;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    public ?\DateTime $deletedAt;

    #[ORM\Column(nullable: true)]
    public ?\DateTime $viewedAt;

    #[ORM\Column]
    public PostType $postType;

    #[ORM\Column(nullable: true)]
    public ?string $title;

    #[ORM\Column]
    public bool $like = false;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $html;
}
