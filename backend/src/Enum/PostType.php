<?php

namespace App\Enum;

enum PostType: string
{
    case POST = 'post';
    case INTRO = 'intro';
    case LINK = 'link';
    case QUESTION = 'question';
    case IDEA = 'idea';
    case PROJECT = 'project';
    case EVENT = 'event';
    case REFERRAL = 'referral';
    case BATTLE = 'battle';
    case WEEKLY_DIGEST = 'weekly_digest';
    case GUIDE = 'guide';
    case THREAD = 'thread';
    case DOCS = 'docs';

    public function getI18n(): string
    {
        return match ($this) {
            self::POST => 'Текст',
            self::INTRO => '#intro',
            self::LINK => 'Ссылка',
            self::QUESTION => 'Вопрос',
            self::IDEA => 'Идея',
            self::PROJECT => 'Проект',
            self::EVENT => 'Событие',
            self::REFERRAL => 'Рефералка',
            self::BATTLE => 'Батл',
            self::WEEKLY_DIGEST => 'Журнал Клуба',
            self::GUIDE => 'Путеводитель',
            self::THREAD => 'Тред',
            self::DOCS => '',
        };
    }
}
