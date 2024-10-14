@props([
    'milestone' => ''
])

<div hx-trigger="load"
     hx-get="{{ BASE_URL }}/hx/tickets/milestones/showCard?milestoneId=<?=$milestone->id ?>">
    
    <x-global::content.card>
        <x-global::elements.loadingText type="card" />
    </x-global::content.card>
</div>