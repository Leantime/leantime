{{--
    Mount point for an HTMX-backed component (Type 2).

    Renders the standard lazy-load wrapper + loading placeholder and fetches the component's content
    via htmx. Works two ways:

      Contract-driven (drift-proof) — pass an HxComponent class; route + refresh events are read
      from the class so emit/listen sides share one enum:
        <x-global::hx :for="\Leantime\Domain\Tickets\Hxcontrollers\Subtasks::class" :id="$ticketId" />

      Attribute-driven (escape hatch for one-offs / plugins) — pass the endpoint + events explicitly:
        <x-global::hx endpoint="comments/reactions/get" :id="$commentId"
                      :listen="[\Leantime\Domain\Tickets\Htmx\HtmxTicketEvents::UPDATE]" />

    Props:
      for          FQCN of an HxComponent (contract-driven mode).
      id           Entity id, appended to the route and used to scope listen events.
      action       Override the mounted action (defaults to the component's $mountAction).
      endpoint     Route segment after /hx/ (attribute-driven mode), e.g. "comments/reactions/get".
      trigger      Initial load trigger. Default "revealed" (loads when scrolled into view).
      listen       Event(s) that should re-fetch the component (attribute-driven mode).
      target/swap  Standard htmx overrides. swap defaults to the component's $swap, else innerHTML.
      vals         Array serialized into hx-vals.
      loader       loadingText skeleton type. loaderCount  number of skeleton rows.
--}}
@props([
    'for' => null,
    'id' => null,
    'wrapperId' => null,
    'action' => null,
    'endpoint' => null,
    'trigger' => 'revealed',
    'listen' => [],
    'target' => null,
    'swap' => null,
    'vals' => null,
    'indicator' => '.htmx-indicator',
    'loader' => 'text',
    'loaderCount' => 1,
])

@php
    $listenEvents = [];
    $resolvedSwap = $swap;

    if ($for && is_string($for) && is_subclass_of($for, \Leantime\Core\Controller\HxComponent::class)) {
        $resolvedAction = \Illuminate\Support\Str::kebab($action ?? $for::$mountAction);
        $path = trim($for::route(), '/').'/'.$resolvedAction.($id !== null ? '/'.$id : '');
        $resolvedSwap = $resolvedSwap ?? $for::$swap;

        foreach ($for::listensTo() as $event) {
            $listenEvents[] = $id !== null ? $event->scoped($id) : $event->event();
        }
    } else {
        $path = trim((string) $endpoint, '/');

        foreach ((array) $listen as $event) {
            $listenEvents[] = $event instanceof \Leantime\Core\Events\Htmx\HtmxEvent ? $event->event() : (string) $event;
        }
    }

    $resolvedSwap = $resolvedSwap ?? 'innerHTML';

    $triggerParts = array_merge([$trigger], array_map(fn ($event) => $event.' from:body', $listenEvents));
    $triggerAttr = implode(', ', array_filter($triggerParts));

    $url = rtrim(BASE_URL, '/').'/hx/'.$path;
@endphp

<div
    @if($wrapperId) id="{{ $wrapperId }}" @endif
    hx-get="{{ $url }}"
    hx-trigger="{{ $triggerAttr }}"
    @if($target) hx-target="{{ $target }}" @endif
    hx-swap="{{ $resolvedSwap }}"
    @if($vals !== null) hx-vals='{!! json_encode($vals) !!}' @endif
    hx-indicator="{{ $indicator }}"
    {{ $attributes }}
>
    <x-global::loadingText :type="$loader" :count="$loaderCount" includeHeadline="false" />
</div>
