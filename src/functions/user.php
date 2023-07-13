<?php

declare(strict_types=1);

function user_is_anonymous(): bool
{
    return \Drupal::currentUser()->isAnonymous();
}

function user_is_logged_in(): bool
{
    return \Drupal::currentUser()->isAuthenticated();
}
