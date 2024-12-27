<?php

/**
 * A Custom Controller.
 * 
 */
namespace Drupal\mylearnings\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Cache\CacheBackendInterface;

class MyController extends ControllerBase {
    /**
     * a custom function to render a page.
     */
    public function content() {
        $data = rand(1,10);

        $cid = 'custom_string';

        $tags = ['custom_learn'];

        return [
            '#markup' => $data,
            '#cache' => [
                'tags' => ['node:1'],
            ]
        ];
    }
}