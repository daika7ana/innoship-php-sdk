<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\Enums;

enum OrderOfflineStatus: string
{
    case New = 'New';
    case Success = 'Success';
    case Error = 'Error';
    case InProgress = 'InProgress';
    case Consolidate = 'Consolidate';
}
