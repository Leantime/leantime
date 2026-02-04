<div>
    <emoji-picker></emoji-picker>
    <textarea id="emoji-input" rows="3" class="form-control mt-2"></textarea>
</div>

<script type="module">
    // import '@nolanlawson/emoji-picker-element';
    
    document.addEventListener('DOMContentLoaded', function () {
        const emojiPicker = document.querySelector('emoji-picker');
        const inputField = document.querySelector('#emoji-input');

        emojiPicker.addEventListener('emoji-click', (event) => {
            const emoji = event.detail.unicode;
            inputField.value += emoji;
        });
    });
</script>
