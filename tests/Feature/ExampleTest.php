<?php

test('the application redirects guests to the login page', function () {
    $this->get('/')->assertRedirect();
});
