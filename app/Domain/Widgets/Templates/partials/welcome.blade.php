@props([
    'includeTitle' => true,
    'randomImage' => '',
    'totalTickets' => 0,
    'projectCount' => 0
])

<div class=""
     hx-get="{{BASE_URL}}/widgets/welcome/get"
     hx-trigger="ticketUpdate from:body"
     hx-swap="outerHTML"
>

    <div class='pull-right' style='max-width:200px; padding:20px'>
        <div  style='width:100%' class='svgContainer'>
            {!! file_get_contents(ROOT . "/dist/images/svg/" . $randomImage) !!}
        </div>
    </div>

    <h1 class="articleHeadline tw-pb-m">
        Welcome <strong>{{ $currentUser['firstname'] }}</strong>
    </h1>

    <p>You have <strong>{{ $totalTickets }} To-Dos</strong> across <strong> {{ $projectCount  }} projects</strong> assigned to you.</p>

    @dispatchEvent('afterWelcomeMessage')

    <div class="clear"></div>

</div>

@dispatchEvent('afterWelcomeMessageBox')
