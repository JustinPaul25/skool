<?php

it('loads the home page', function () {
    $response = $this->get('/');

    $response->assertOk();
});
