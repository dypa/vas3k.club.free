<?php

namespace App\Entity;

use App\Enum\PostType;
use App\Repository\PostRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PostRepository::class)]
#[ORM\Index(fields: ['postType'])]
#[ORM\Index(fields: ['clubId'])]
final class Post
{
    #[ORM\Id, ORM\Column, ORM\GeneratedValue]
    public readonly ?int $id;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    public ?\DateTime $createdAt;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    public \DateTime $updatedAt;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    public ?\DateTime $deletedAt;

    #[ORM\Column(nullable: true)]
    public ?\DateTime $viewedAt;

    // TODO enumType не нужен в аттрибуте, но без него не работает
    #[ORM\Column(enumType: PostType::class)]
    public PostType $postType;

    #[ORM\Column]
    public string $clubId;

    #[ORM\Column(nullable: true)]
    public ?string $title;

    #[ORM\Column]
    public bool $like = false;

    #[ORM\Column(nullable: true)]
    public ?int $votes;

    public ?string $type;
}
