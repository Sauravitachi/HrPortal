<?php

test('the application redirects guest users to login', function () {
    $response = $this->get('/');

    $response->assertRedirect('/login');
});
