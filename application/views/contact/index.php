<div class='contact-container'>
    <?php
    echo $html->heading("We'd love to hear from ya", '1');
    ?>
    <div class='contact-form'>
        <label for='name' class='centered'>
            Name:
            <input type='text' name='name' id='name' class='rounded-input' placeholder="What's your name?" />
        </label>
        <label for='email' class='centered'>
            Email:
            <input type='text' name='mail' id='email' class='rounded-input' placeholder="youremail@yourdomain.com" />
        </label>
        <label for='message'>
            Message:
            <textarea name='message' class='contact-textarea rounded-text' id='message' placeholder="What would you like to say?"></textarea>
        </label>
        <button class='send-message game-list-button'>Send</button>
    </div>
</div>