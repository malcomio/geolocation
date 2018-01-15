<?php

namespace Drupal\Tests\geolocation\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\JavascriptTestBase;
use Zumba\GastonJS\Exception\JavascriptError;
use Drupal\FunctionalJavascriptTests\DrupalSelenium2Driver;

/**
 * Support tests using Google Maps API.
 */
abstract class GeolocationJavascriptTestBase extends JavascriptTestBase {

  /**
   * {@inheritdoc}
   */
  protected $minkDefaultDriverClass = DrupalSelenium2Driver::class;

  /**
   * Filter the missing key Google Maps API error.
   *
   * @param mixed $path
   *   Path to get.
   *
   * @return string
   *   Return what drupal would.
   *
   * @throws \Zumba\GastonJS\Exception\JavascriptError
   */
  protected function drupalGetFilterGoogleKey($path) {
    /* @var $this \Drupal\FunctionalJavascriptTests\JavascriptTestBase */
    try {
      $this->drupalGet($path);
      $this->getSession()->getDriver()->wait(1000, '1==2');
    }
    catch (JavascriptError $e) {
      foreach ($e->javascriptErrors() as $errorItem) {
        if (strpos((string) $errorItem, 'MissingKeyMapError') !== FALSE) {
          continue;
        }
        else {
          throw $e;
        }
      }
    }
    return FALSE;
  }

}
