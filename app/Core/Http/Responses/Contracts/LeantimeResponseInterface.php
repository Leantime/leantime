<?php

namespace Leantime\Core\Http\Responses\Contracts;

use Illuminate\Contracts\Support\Responsable;

/**
 * Marker contract for Leantime's first-class HTTP response types.
 *
 * Response types live in app/Core/Http/Responses and are returned directly from
 * controllers; Laravel's router converts them to a Symfony Response via toResponse().
 * Extending Laravel's Responsable keeps the behaviour fully framework-native while
 * giving Leantime a single place to discover/centralise every response type it offers
 * (e.g. ImageResponse today; a JsonRpcResponse, etc. can follow the same pattern).
 *
 * @see \Illuminate\Contracts\Support\Responsable
 */
interface LeantimeResponseInterface extends Responsable {}
