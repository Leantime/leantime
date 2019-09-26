
<form action='' method='POST'>
    
    <label for="users">Users:</label>
    <select name="users[]" multiple="multiple">
        <optgroup>
            <?php foreach($this->get('availableUsers') as $user): ?>
            <option value="<?php echo $user['id'] ?>">
                <?php echo $user['username'] ?>
            </option>
            <?php endforeach; ?>
        </optgroup>
    </select>
    
</form>
