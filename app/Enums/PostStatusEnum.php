<?php

namespace App\Enums;

enum PostStatusEnum: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';
    case DELETED = 'deleted';
    case PENDING = 'pending';
    case REJECTED = 'rejected';
    case REVIEWED = 'reviewed';
}
