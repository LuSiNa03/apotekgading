<?php

test('the application root redirects unauthenticated users', function () {
    $response = $this->get('/');

    $response->assertRedirect();
});
