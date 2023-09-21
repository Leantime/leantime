@props([
    'includeTitle' => true,
    'ticket' => [],
])

@dispatchEvent('beforeCalendar')

<div class="tw-w-full tw-h-full tw-overflow-x-hidden"
     hx-get="{{BASE_URL}}/widgets/pomodoro/get"
     hx-trigger="ticketUpdate from:body"
     hx-swap="outerHTML"
    >

    <h5 class="subtitle">{{ __('headlines.timer') }}</h5>


    <div class="tw-w-full tw-text-center tw-pt-xl">
        <div class="outer-circle">
            <span id="timer">25:00</span>
            <div class="wrapper">
                <div class="breath">
                    <div class="flare1"></div>
                    <div class="flare2"></div>
                    <div class="flare3"></div>
                    <div class="flare4"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="tw-text-center" style="margin-top: 30px; position: relative; z-index: 10;">
        <button id="btn_start" style="display:none;" class="btn btn-primary"><i class="fa-solid fa-play"></i></button>
        <button id="btn_pause" style="display:none;" class="btn btn-primary"><i class="fa-solid fa-pause"></i></button>
        <button id="btn_reset" class="btn btn-outline"><i class="fa-solid fa-rotate-left"></i></button>


        <hr />

        <button id="btn_pomodoro" class="btn btn-default">pomodoro (25m)</button>
        <button id="btn_shortbreak" class="btn btn-default">short break (5m)</button>
        <button id="btn_longbreak" class="btn btn-default">long break (15m)</button>

        <button id="btn_custom" class="btn btn-default">
            <label for="ipt_custom">custom:</label>
        </button>
        <input type="number" id="ipt_custom" value="45" min="0" max="100000000">
        <select id="custom_units">
            <option value="seconds">seconds</option>
            <option value="minutes" selected>minutes</option>
            <option value="hours">hours</option>
        </select>

    </div>


</div>

<script>

    /** Represents a timer that can count down. */
    function CountdownTimer(seconds, tickRate) {
        this.seconds = seconds || (25*60);
        this.tickRate = tickRate || 500; // Milliseconds
        this.tickFunctions = [];
        this.isRunning = false;
        this.remaining = this.seconds;

        /** CountdownTimer starts ticking down and executes all tick
         functions once per tick. */
        this.start = function() {
            if (this.isRunning) {
                return;
            }

            this.isRunning = true;

            // Set variables related to when this timer started
            var startTime = Date.now(),
                thisTimer = this;

            // Tick until complete or interrupted
            (function tick() {
                secondsSinceStart = ((Date.now() - startTime) / 1000) | 0;
                var secondsRemaining = thisTimer.remaining - secondsSinceStart;

                // Check if timer has been paused by user
                if (thisTimer.isRunning === false) {
                    thisTimer.remaining = secondsRemaining;
                } else {
                    if (secondsRemaining > 0) {
                        // Execute another tick in tickRate milliseconds
                        setTimeout(tick, thisTimer.tickRate);
                    } else {
                        // Stop this timer
                        thisTimer.remaining = 0;
                        thisTimer.isRunning = false;

                        // Alert user that time is up
                        playAlarm();
                    }

                    var timeRemaining = parseSeconds(secondsRemaining);

                    // Execute each tickFunction in the list with thisTimer
                    // as an argument
                    thisTimer.tickFunctions.forEach(
                        function(tickFunction) {
                            tickFunction.call(this,
                                timeRemaining.minutes,
                                timeRemaining.seconds);
                        },
                        thisTimer);
                }
            }());
        };

        /** Pause the timer. */
        this.pause = function() {
            this.isRunning = false;
        };

        /** Pause the timer and reset to its original time. */
        this.reset = function(seconds) {
            this.isRunning = false;
            this.seconds = seconds
            this.remaining = seconds
        };

        /** Add a function to the timer's tickFunctions. */
        this.onTick = function(tickFunction) {
            if (typeof tickFunction === 'function') {
                this.tickFunctions.push(tickFunction);
            }
        };
    }

    /** Return minutes and seconds from seconds. */
    function parseSeconds(seconds) {
        return {
            'minutes': (seconds / 60) | 0,
            'seconds': (seconds % 60) | 0
        }
    }

    /** Play the selected alarm at selected volume. */
    function playAlarm() {
        var alarmValue = document.getElementById('alarm_select').value;
        if (alarmValue != 'none') {
            var alarmAudio = document.getElementById(alarmValue);
            var alarmVolume = document.getElementById('alarm_volume').value;
            alarmAudio.volume = alarmVolume / 100;
            alarmAudio.play();
        }
    }

    /** Change the color of the favicon. */
    function changeFavicon(color) {
        document.head = document.head || document.getElementsByTagName('head')[0];
        var color = color || 'green';

        var newFavicon = document.createElement('link'),
            oldFavicon = document.getElementById('dynamic-favicon');
        newFavicon.id = 'dynamic-favicon'
        newFavicon.type = 'image/ico';
        newFavicon.rel = 'icon';
        newFavicon.href = 'images/' + color + '_tomato.ico';

        if (oldFavicon) {
            document.head.removeChild(oldFavicon);
        }
        document.head.appendChild(newFavicon);
    }

    /** Window onload functions. */
    jQuery(document).ready(function() {

        var timerDisplay = document.getElementById('timer'),
            customTimeInput = document.getElementById('ipt_custom'),
            timer = new CountdownTimer(),
            timeObj = parseSeconds(25*60);

        /** Set the time on the main clock display and
         set the time remaining section in the title. */
        function setTimeOnAllDisplays(minutes, seconds) {
            if (minutes >= 60) {
                // Add an hours section to all displays
                hours = Math.floor(minutes / 60);
                minutes = minutes % 60;
                clockHours = hours + ':';
                document.title = '(' + hours + 'h' + minutes + 'm) Leantime Timer';
            } else {
                clockHours = '';
                document.title = '(' + minutes + 'm) Pomodoro';
            }

            clockMinutes = minutes < 10 ? '0' + minutes : minutes;
            clockMinutes += ':';
            clockSeconds = seconds < 10 ? '0' + seconds : seconds;

            timerDisplay.textContent = clockHours + clockMinutes + clockSeconds;
        }

        /** Revert the favicon to red, delete the old timer
         object, and start a new one. */
        function resetMainTimer(seconds) {

            timer.pause();
            timer = new CountdownTimer(seconds);
            timer.onTick(setTimeOnAllDisplays);
        }

        // Set default page timer displays
        setTimeOnAllDisplays(timeObj.minutes, timeObj.seconds);

        timer.onTick(setTimeOnAllDisplays);

        // Add listeners for start, pause, etc. buttons
        document.getElementById('btn_start').addEventListener(
            'click', function () {
                timer.start();

                jQuery("#btn_start").hide();
                jQuery("#btn_pause").show();
            });

        document.getElementById('btn_pause').addEventListener(
            'click', function () {
                timer.pause();
                jQuery("#btn_start").show();
                jQuery("#btn_pause").hide();

            });

        document.getElementById('btn_reset').addEventListener(
            'click', function () {
                resetMainTimer(timer.seconds);

            });

        document.getElementById('btn_pomodoro').addEventListener(
            'click', function () {
                resetMainTimer(25*60);
                timer.start();
            });

        document.getElementById('btn_shortbreak').addEventListener(
            'click', function () {
                resetMainTimer(5*60);
                timer.start();
            });

        document.getElementById('btn_longbreak').addEventListener(
            'click', function () {
                resetMainTimer(15*60);
                timer.start();
            });

        document.getElementById('btn_custom').addEventListener(
            'click', function () {
                customUnits = document.getElementById('custom_units').value
                if (customUnits === 'minutes') {
                    resetMainTimer(customTimeInput.value*60);
                } else if (customUnits === 'hours') {
                    resetMainTimer(customTimeInput.value*3600);
                } else {
                    resetMainTimer(customTimeInput.value);
                }
                timer.start();
            });

        jQuery("#btn_start").show();

        // Bind keyboard shortcut for starting/pausing timer
        /*
        Mousetrap.bind('space', function(e) {
            // Remove default behavior of buttons (page scrolling)
            if (e.preventDefault()) {
                e.preventDefault();
            } else {
                e.returnValue = false; //IE
            }

            // Pause or start the timer
            if(timer.isRunning) {
                timer.pause();
            } else {
                timer.start();
            }
        });*/
    });


</script>

<style>
    @keyframes pulse {
        0% {
            transform: scale(0.7);
        }

        2% {
            transform: scale(0.7);
        }

        21% {
            transform: scale(1);
        }

        57% {
            transform: scale(1);
        }

        100% {
            transform: scale(0.7);
        }

    }

    @keyframes rotate {
        0% {
            transform: rotate(0deg);
        }
        100% {
            transform: rotate(360deg);
        }
    }

    .outer-circle {
        width: 250px;
        height: 250px;
        margin:auto;
        border-radius: 125px;
        box-shadow: 0 0 5px 15px rgba(var(--accent1), 0.2);
        background-image: radial-gradient(#cccccc, #cccccc00 50%, #cccccc66 90%);
        position:relative;
    }

    .wrapper {
        animation: pulse 20s linear infinite;
        position: relative;
    }

    .breath {
        width: 250px;
        height: 250px;
        border-radius: 125px;
        position: relative;
        background-color: #fff;
        /*transform:rotate(360deg);*/
        animation: rotate 10s linear infinite;
    }

    .breath::before {
         content: '';
         position: absolute;
         top: 0;
         right: 0;
         bottom: 0;
         left: 0;
         z-index: -1;
            margin: -10px;
            border-radius: 140px;
         background: linear-gradient(135deg, var(--accent1), var(--accent2));
         box-shadow: 0 0 10px 5px rgba(0,0,0, 0.2);
     }

    .breath::after {
         content: "";
         display: block;
         position: relative;
         width: 250px;
         height: 250px;
         border-radius: 125px;
         background-color:rgba(255,255,255,0.7);
     }

    .flare1 {
        width: 300px;
        height: 300px;
        content: "";
        display: block;
        border-radius: 250px;
        background-image: radial-gradient(#81B1A8ff, #81B1A800 60%);

        position: absolute;
        left: -60px;
        top: -60px;
        z-index: -1;
    }

    .flare2 {
        width: 300px;
        height: 300px;
        content: "";
        display: block;
        border-radius: 250px;
        background-image: radial-gradient(#81B1A8ff, #81B1A800 60%);

        position: absolute;
        right: -60px;
        bottom: -60px;
        z-index: -1;
    }

    .flare3 {
        width: 300px;
        height: 300px;
        content: "";
        display: block;
        border-radius: 250px;
        background-image: radial-gradient(#1b75bbff, #1b75bb00 60%);

        position: absolute;
        right: 0;
        bottom: -60px;
        z-index: -1;
    }

    .flare4 {
        width: 300px;
        height: 300px;
        content: "";
        display: block;
        border-radius: 250px;
        background-image: radial-gradient(#1b75bbff, #1b75bb00 60%);
        position: absolute;
        left: 0px;
        top: -60px;
        z-index: -1;
    }

    .flare5 {
        width: 190px;
        height: 190px;
        content: "";
        display: block;
        border-radius: 50px;
        background-image: radial-gradient(#124F7D63, #124F7D00 60%);
        position: absolute;
        left: 17px;
        top: 140px;
        z-index: -1;
    }

    #timer {
        position:absolute;
        top:85px;
        width:100%;
        font-size:55px;
        z-index:5;
        left:0;
        text-shadow: 0px 0px 5px #fff;
    }



</style>
