<?php

namespace Leantime\Domain\Queue\Workers;

enum Workers: string
{
    case EMAILS = 'email';
    case HTTPREQUESTS = 'httprequests';

    case DEFAULT = 'default';

    // Personal notification webhooks, drained by Notifications\Services\WebhookQueue.
    case WEBHOOKS = 'webhooks';

    // AI maintenance jobs (indexing, summaries, memory extraction). Core has no runner for it:
    // the AI plugin drains the channel with its own scheduled runner, like WEBHOOKS above.
    case AI = 'ai';
}
