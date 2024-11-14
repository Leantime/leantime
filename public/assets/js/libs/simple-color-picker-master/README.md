# simpleColorPicker

A simple color picker jQuery plugin that appears as the user focuses the input.

Check out the latest version at http://github.com/rachel-carvalho/simple-color-picker.

## Usage
Just attach the simpleColorPicker to an input text and when it gains focus the color palette appears aligned to its bottom right corner.

### Samples

See them working live at http://rachel-carvalho.github.com/simple-color-picker.

#### Default options

    $(document).ready(function() {
        $('input#color').simpleColorPicker();
    });

#### More colors per line

    $(document).ready(function() {
        $('input#color2').simpleColorPicker({ colorsPerLine: 16 });
    });

#### Different colors

    $(document).ready(function() {
        var colors = ['#000000', '#444444', '#666666', '#999999', '#cccccc', '#eeeeee', '#f3f3f3', '#ffffff'];
        $('input#color3').simpleColorPicker({ colors: colors });
    });

#### Effects

    $(document).ready(function() {
        $('input#color4').simpleColorPicker({ showEffect: 'fade', hideEffect: 'slide' });
    });

#### Non-input elements

    $(document).ready(function() {
        $('button#color5').simpleColorPicker({ onChangeColor: function(color) { $('label#color-result').text(color); } });
    });
