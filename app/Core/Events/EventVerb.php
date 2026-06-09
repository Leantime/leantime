<?php

namespace Leantime\Core\Events;

/**
 * The central verb vocabulary for class-based domain events.
 *
 * Event classes are named {Entity}{Verb} (TicketCreated, MilestoneDeleted) and the verb
 * MUST be a case of this enum — one vocabulary across all domains, mirroring the client
 * (HTMX) convention lt:{domain}:{entity}.{verb} and the permission vocabulary
 * {domain}.{action}. Synonyms are deliberately rejected: it is always Updated, never
 * Changed/Edited/Saved/Modified. Add a case here only when no existing verb fits.
 *
 * All verbs are past tense: events report state changes that already happened.
 */
enum EventVerb: string
{
    case Created = 'created';
    case Updated = 'updated';
    case Deleted = 'deleted';
    case Added = 'added';
    case Removed = 'removed';
    case Moved = 'moved';
    case Completed = 'completed';
    case Started = 'started';
    case Succeeded = 'succeeded';
    case Failed = 'failed';
    case Archived = 'archived';
    case Restored = 'restored';
    case Duplicated = 'duplicated';
    case Uploaded = 'uploaded';
    case Sent = 'sent';
    case Notified = 'notified';
    case Registered = 'registered';
    case Initialized = 'initialized';
}
