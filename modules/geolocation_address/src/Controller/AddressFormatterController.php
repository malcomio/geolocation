<?php

namespace Drupal\geolocation_address\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AddressFormatterController.
 *
 * @package Drupal\geolocation_address\Controller
 */
class AddressFormatterController extends ControllerBase {

  /**
   * Return formatted address data.
   *
   * @param array $a
   *   Stuff.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Current Request.
   *
   * @return array
   *   Formatted address.
   */
  public function formatAddress(array $a, Request $request) {
    // TODO:
    return [];
  }

}
