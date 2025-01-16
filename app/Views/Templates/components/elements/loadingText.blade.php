@props([
    'count' => 1,
    'includeHeadline' => false,
    'type' => 'text',
])

@if ($includeHeadline == 'true')
    <div class="loading-text">
        <p style="width:40%">Loading...</p>
        <br />
    </div>
    <br />
@endIf

@if ($type == 'card')
    @for ($i = 0; $i < $count; $i++)
        <div class="loading-text w-full">
            <div class="row mb-l">
                <div class="col-md-6">
                    <p style="width:30%">Loading...</p>
                    <p style="width:60%">Loading...</p>
                    <p style="width:20%">Loading...</p>
                </div>
                <div class="col-md-6 text-right">
                    <p style="width:5%" class="float-right">Loading...</p>
                    <div class="clearall"></div>
                    <div class="clearall"></div><br />
                    <p style="width:20%" class="float-right ml-sm">Loading...</p>&nbsp;<p style="width:25%"
                        class="float-right ml-sm">Loading...</p>&nbsp;<p style="width:10%" class="float-right ml-sm">
                        Loading...</p>
                </div>
            </div>
        </div>
    @endfor
@endif

@if ($type == 'ticket-column-card')
    @for ($i = 0; $i < $count; $i++)
        <div class="loading-text ticketBox">
            <div class="row mb-l">
                <div class="col-md-12">
                    <p style="width:30%">Loading...</p>
                    <p style="width:60%">Loading...</p>
                    <p style="width:20%">Loading...</p>
                </div>
            </div>
        </div>
    @endfor
@endif

@if ($type == 'goal-card')
    <div class="row max-height: 10%" style="border-bottom:1px solid var(--main-border-color); margin-bottom:20px">
        <div class="sortableTicketList disabled col-md-12" style="padding-top:15px;">
            <div class="row">
                <div class="col-md-12">
                    <div class="row loading-text">
                        @for ($i = 0; $i < $count; $i++)
                            <div class="col-md-4">
                                <x-global::content.card>
                                    <div class="loading-text">
                                        <div class="row">
                                            <div class="col-md-12">
                                                {{-- Header with menu --}}
                                                <div class="d-flex justify-content-between mb-3">
                                                    <p style="width:70%">Loading...</p>
                                                    <p style="width:30px; height:10px;">Loading...</p>
                                                </div>

                                                {{-- Goal title --}}
                                                <p style="width:85%">Loading...</p>
                                                <br />

                                                {{-- Metric description --}}
                                                <p style="width:60%">Loading...</p>
                                                <br />

                                                {{-- Progress bar --}}
                                                <p style="width:100%; height:10px;">Loading...</p>
                                                <br />

                                                {{-- Metric values --}}
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <p style="width:90%">Loading...</p>
                                                    </div>
                                                    <div class="col-md-4 center">
                                                        <p style="width:90%">Loading...</p>
                                                    </div>
                                                    <div class="col-md-4 text-right">
                                                        <p style="width:90%; float:right">Loading...</p>
                                                    </div>
                                                </div>
                                                <br />

                                                {{-- Bottom actions --}}
                                                <div class="d-flex justify-content-between">
                                                    <div>
                                                        <p style="width:100px; display:inline-block">Loading...</p>
                                                        <p style="width:100px; display:inline-block; margin-left:10px">
                                                            Loading...</p>
                                                    </div>
                                                    <p style="width:30px">Loading...</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </x-global::content.card>
                            </div>
                        @endfor
                    </div>
                    <br />
                </div>
            </div>
        </div>
    </div>
@endif

@if ($type == 'text')
    @for ($i = 0; $i < $count; $i++)
        <div class="loading-text">
            <p style="width:90%">Loading...</p>
            <p style="width:90%">Loading...</p>
            <br />
            <p style="width:60%">Loading...</p>
            <p style="width:65%">Loading...</p>
            <p style="width:55%">Loading...</p>
            <p style="width:50%">Loading...</p>
            <p style="width:20%">Loading...</p>
            <br />
            <p style="width:90%">Loading...</p>
            <p style="width:90%">Loading...</p>
        </div>
    @endfor
@endif

@if ($type == 'longtext')
    @for ($i = 0; $i < $count; $i++)
        <div class="loading-text">
            <p style="width:90%">Loading...</p>
            <p style="width:90%">Loading...</p>
            <br />
            <p style="width:60%">Loading...</p>
            <p style="width:65%">Loading...</p>
            <p style="width:55%">Loading...</p>
            <p style="width:50%">Loading...</p>
            <p style="width:20%">Loading...</p>
            <br />
            <p style="width:90%">Loading...</p>
            <p style="width:90%">Loading...</p>
            <p style="width:90%">Loading...</p>
            <p style="width:90%">Loading...</p>
            <br />
            <p style="width:60%">Loading...</p>
            <p style="width:65%">Loading...</p>
            <p style="width:55%">Loading...</p>
            <p style="width:50%">Loading...</p>
            <p style="width:20%">Loading...</p>
            <br />
            <p style="width:90%">Loading...</p>
            <p style="width:90%">Loading...</p>
            <p style="width:90%">Loading...</p>
            <p style="width:90%">Loading...</p>
            <br />
            <p style="width:60%">Loading...</p>
            <p style="width:65%">Loading...</p>
            <p style="width:55%">Loading...</p>
            <p style="width:50%">Loading...</p>
            <p style="width:20%">Loading...</p>
            <br />
            <p style="width:90%">Loading...</p>
            <p style="width:90%">Loading...</p>
        </div>
    @endfor
@endif

@if ($type == 'line')
    @for ($i = 0; $i < $count; $i++)
        <div class="loading-text">
            <p style="width:40%">Loading...</p>
            <br />
        </div>
    @endfor
@endif

@if ($type == 'project')
    @for ($i = 0; $i < $count; $i++)
        <div class="loading-text">
            <p style="margin-left:10px; margin-right:10px; width:30px; height:30px; float:left;">Loading...</p>
            <p style="width:200px; margin-left:50px;"></p>
            <br />
        </div>
    @endfor
@endif

@if ($type == 'plugincard')
    <div class="row">
        @for ($i = 0; $i < $count; $i++)
            <div class="col-md-4">
                <div class="loading-text">
                    <div class="row mb-l">
                        <div class="col-md-12">
                            <p style="width:100%; height:80px;">Loading...</p>
                        </div>
                    </div>
                    <div class="row mb-l">
                        <div class="col-md-6">
                            <p style="width:60%">Loading...</p>
                            <p style="width:20%">Loading...</p>
                        </div>
                        <div class="col-md-6 text-right">
                            <p style="width:5%" class="float-right">Loading...</p>
                            <div class="clearall"></div>
                            <div class="clearall"></div><br />
                            <p style="width:20%" class="float-right ml-sm">Loading...</p>&nbsp;<p style="width:25%"
                                class="float-right ml-sm">Loading...</p>&nbsp;<p style="width:10%"
                                class="float-right ml-sm">Loading...</p>
                        </div>
                    </div>
                </div>
            </div>
        @endfor
    </div>
@endif

