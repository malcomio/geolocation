<?php

namespace geolocation_address\Formatter;

use Symfony\Component\Yaml\Yaml;
use PredictHQ\AddressFormatter\Exception\TemplatesMissingException;

/**
 * Format an address based on the country template.
 *
 * Takes advantage of the OpenCageData address-formatting templates available at:
 * @link https://github.com/OpenCageData/address-formatting
 *
 * Also based on the Perl address formatter using the same address templates:
 * @link https://metacpan.org/pod/Geo::Address::Formatter
 *
 * Test cases come from the OpenCageData repo.
 */
class Formatter {
  private $components = [];
  private $componentAliases = [];
  private $templates = [];
  private $stateCodes = [];
  private $validReplacementComponents = [
    'state',
  ];

  public function __construct() {
    $this->loadTemplates();
  }

  /**
   * Pass a PredictHQ\AddressFormatter\Address object here
   */
  public function format(Address $address, $options = []) {
    $addressArray = [];

    if (strlen($address->getAttention()) > 0) {
      $addressArray['attention'] = $address->getAttention();
    }
    if (strlen($address->getHouseNumber()) > 0) {
      $addressArray['house_number'] = $address->getHouseNumber();
    }
    if (strlen($address->getHouse()) > 0) {
      $addressArray['house'] = $address->getHouse();
    }
    if (strlen($address->getRoad()) > 0) {
      $addressArray['road'] = $address->getRoad();
    }
    if (strlen($address->getVillage()) > 0) {
      $addressArray['village'] = $address->getVillage();
    }
    if (strlen($address->getSuburb()) > 0) {
      $addressArray['suburb'] = $address->getSuburb();
    }
    if (strlen($address->getCity()) > 0) {
      $addressArray['city'] = $address->getCity();
    }
    if (strlen($address->getCounty()) > 0) {
      $addressArray['county'] = $address->getCounty();
    }
    if (strlen($address->getPostcode()) > 0) {
      $addressArray['postcode'] = $address->getPostcode();
    }
    if (strlen($address->getStateDistrict()) > 0) {
      $addressArray['state_district'] = $address->getStateDistrict();
    }
    if (strlen($address->getState()) > 0) {
      $addressArray['state'] = $address->getState();
    }
    if (strlen($address->getRegion()) > 0) {
      $addressArray['region'] = $address->getRegion();
    }
    if (strlen($address->getIsland()) > 0) {
      $addressArray['island'] = $address->getIsland();
    }
    if (strlen($address->getCountry()) > 0) {
      $addressArray['country'] = $address->getCountry();
    }
    if (strlen($address->getCountryCode()) > 0) {
      $addressArray['country_code'] = $address->getCountryCode();
    }
    if (strlen($address->getContinent()) > 0) {
      $addressArray['continent'] = $address->getContinent();
    }

    return $this->formatArray($addressArray);
  }

  public function formatArray($addressArray, $options = []) {
    // Figure out which template to use.
    $tpl = (isset($this->templates[strtoupper($countryCode)])) ? $this->templates[strtoupper($countryCode)] : $this->templates['default'];
    $tplText = (isset($tpl['address_template'])) ? $tpl['address_template'] : '';

    return $text;
  }

  public function loadTemplates()
  {
    /**
     * Unfortunately it's not possible to include a git submodule with a composer package, so we load
     * the address-formatting templates as a separate package via our composer.json and if the address-formatting
     * templates exist at the expected location for a composer loaded package, we use that by default.
     */
    $composerTemplatesPath = implode(DIRECTORY_SEPARATOR, array(realpath(dirname(__FILE__)), '..', '..', 'address-formatter-templates', 'conf'));

    if (is_dir($composerTemplatesPath)) {
      $templatesPath = $composerTemplatesPath;
    } else {
      //Use the git submodule path
      $templatesPath = implode(DIRECTORY_SEPARATOR, array(realpath(dirname(__FILE__)), '..', 'address-formatter-templates', 'conf'));
    }

    if (is_dir($templatesPath)) {
      $countriesPath = implode(DIRECTORY_SEPARATOR, array($templatesPath, 'countries', 'worldwide.yaml'));
      $componentsPath = implode(DIRECTORY_SEPARATOR, array($templatesPath, 'components.yaml'));
      $stateCodesPath = implode(DIRECTORY_SEPARATOR, array($templatesPath, 'state_codes.yaml'));

      $components = [];
      $componentAliases = [];
      $templates = [];
      $stateCodes = [];

      /**
       * The components file is made up of multiple yaml documents but the symfony yaml parser
       * doesn't support multiple docs in a single file. So we split it into multiple docs.
       */
      $componentYamlParts = explode('---', file_get_contents($componentsPath));

      foreach ($componentYamlParts as $key => $val) {
        $component = Yaml::parse($val);

        if (isset($component['aliases'])) {
          foreach ($component['aliases'] as $k => $v) {
            $componentAliases[$v] = $component['name'];
          }
        }

        $components[$component['name']] = (isset($component['aliases'])) ? $component['aliases'] : [];
      }

      //Load the country templates and state codes
      $templates = Yaml::parse(file_get_contents($countriesPath));
      $stateCodes = Yaml::parse(file_get_contents($stateCodesPath));

      $this->components = $components;
      $this->componentAliases = $componentAliases;
      $this->templates = $templates;
      $this->stateCodes = $stateCodes;
    } else {
      throw new TemplatesMissingException('Address formatting templates path cannot be found.');
    }
  }
}
