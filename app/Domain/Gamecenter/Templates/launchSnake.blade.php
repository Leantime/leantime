<style>
    /*
        JavaScript Snake
        By Patrick Gillespie
        http://patorjk.com/games/snake
    */

    #game-area {
        margin: 10px;
        padding: 0px;
    }

    #mode-wrapper {
        color: #ffffff;
        font-family: Verdana, arial, helvetica, sans-serif;
        font-size: 14px;
    }
    #game-area:focus {
        outline: none;
    }
    a.snake-link, a.snake-link:link, a.snake-link:visited {
        color: var(--accent1);
    }
    a.snake-link:hover {
        color: #FfFf54;
    }
    .snake-pause-screen {
        font-family: Verdana, arial, helvetica, sans-serif;
        font-size: 14px;
        position: absolute;
        width: 300px;
        height: 80px;
        text-align: center;
        top: 50%;
        left: 50%;
        margin-top: -40px;
        margin-left: -150px;
        display: none;
        background-color: black;
        color: white;
    }
    .snake-panel-component {
        position: absolute;
        font-family: Verdana, arial, helvetica, sans-serif;
        font-size: 14px;
        color: #ffffff;
        text-align: center;
        padding: 8px;
        margin: 0px;
    }
    .snake-snakebody-block {
        margin: 0px;
        padding: 0px;
        background-color: var(--accent1);
        position: absolute;
        border: 0px solid #000080;
        background-repeat: no-repeat;
    }
    .snake-snakebody-alive {
        background-color: var(--accent1);
    }
    .snake-snakebody-dead {
        background-color: var(--accent2);
    }
    .snake-food-block {
        margin: 0px;
        padding: 0px;
        background-color: var(--accent2);
        border: 0px solid #000080;
        position: absolute;
    }
    .snake-playing-field {
        margin: 0px;
        padding: 0px;
        position: absolute;
        background-color: var(--secondary-background);
        border: 1px solid var(--main-border-color);
    }
    .snake-game-container {
        margin: 0px;
        padding: 0px;
        border-width: 0px;
        border-style: none;
        zoom: 1;
        position: relative;
    }
    .snake-welcome-dialog {
        padding: 8px;
        margin: 0px;
        background-color: #000000;
        color: #ffffff;
        font-family: Verdana, arial, helvetica, sans-serif;
        font-size: 14px;
        position: absolute;
        top: 50%;
        left: 50%;
        width: 300px;
        /*height: 150px;*/
        margin-top: -100px;
        margin-left: -158px;
        text-align: center;
        display: block;
    }
    .snake-try-again-dialog, .snake-win-dialog {
        padding: 8px;
        margin: 0px;
        background-color: #000000;
        color: #ffffff;
        font-family: Verdana, arial, helvetica, sans-serif;
        font-size: 14px;
        position: absolute;
        top: 50%;
        left: 50%;
        width: 300px;
        height: 100px;
        margin-top: -75px;
        margin-left: -158px;
        text-align: center;
        display: none;
    }
</style>

<select id="selectMode">
    <option value="100">Easy</option>
    <option value="75" selected="">Medium</option>
    <option value="50">Hard</option>
    <option value="25">Impossible</option>
    <option value="110">Rush</option>
</select>

<div id="game-area" tabindex="0" ></div>


<script>
    var mySnakeBoard = new SNAKE.Board({
        boardContainer: "game-area",
        fullScreen: false,
        premoveOnPause: false
    });
</script>
