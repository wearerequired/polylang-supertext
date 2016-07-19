<?php

namespace Supertext\Polylang\Helper;

use Comotive\Util\WordPress;
use Supertext\Polylang\Api\ApiConnection;
use Supertext\Polylang\Api\Multilang;
use Supertext\Polylang\Api\Wrapper;

/**
 * Class Library
 * @package Supertext\Polylang\Helper
 */
class Library
{
  /**
   * @param string $language polylang language code
   * @return string equivalent supertext language code
   */
  public function mapLanguage($language)
  {
    $options = $this->getSettingOption();
    foreach ($options[Constant::SETTING_LANGUAGE_MAP] as $polyKey => $stKey) {
      if ($language == $polyKey) {
        return $stKey;
      }
    }

    return null;
  }

  /**
   * @param int $userId wordpress user
   * @return array user configuration for supertext api calls
   */
  public function getUserCredentials($userId)
  {
    $options = $this->getSettingOption();
    $userMap = isset($options[Constant::SETTING_USER_MAP]) ? $options[Constant::SETTING_USER_MAP] : null;

    if (is_array($userMap)) {
      foreach ($userMap as $config) {
        if ($config['wpUser'] == $userId) {
          return $config;
        }
      }
    }

    // Default user, so it doesn't crash
    return array(
      'wpUser' => $userId,
      'stUser' => Constant::DEFAULT_API_USER,
      'stApi' => ''
    );
  }

  /**
   * @return array full settings array
   */
  public function getSettingOption()
  {
    return get_option(Constant::SETTINGS_OPTION, array());
  }

  /**
   * @param string $subSetting key
   * @param array|mixed $value saved value
   */
  public function saveSetting($subSetting, $value)
  {
    $options = $this->getSettingOption();
    $options[$subSetting] = $value;
    update_option(Constant::SETTINGS_OPTION, $options);
  }

  /**
   * @param $hash
   * @return array full settings array
   */
  public function getReferenceData($hash)
  {
    $references = get_option(Constant::REFERENCE_OPTION, array());
    return $references[$hash];
  }

  /**
   * @param $hash
   * @param $data
   */
  public function saveReferenceData($hash, $data)
  {
    $references = get_option(Constant::REFERENCE_OPTION, array());
    $references[$hash] = $data;
    update_option(Constant::REFERENCE_OPTION, $references);
  }

  /**
   * @param $hash
   */
  public function removeReferenceData($hash)
  {
    $references = get_option(Constant::REFERENCE_OPTION, array());
    unset($references[$hash]);
    update_option(Constant::REFERENCE_OPTION, $references);
  }

  /**
   * @return bool true if workingly configured
   */
  public function isWorking()
  {
    if (!WordPress::isPluginActive('polylang/polylang.php')) {
      return false;
    }

    $options = $this->getSettingOption();

    return
      isset($options[Constant::SETTING_USER_MAP]) &&
      count($options[Constant::SETTING_USER_MAP]) > 0 &&
      isset($options[Constant::SETTING_LANGUAGE_MAP]) &&
      count($options[Constant::SETTING_LANGUAGE_MAP]) == count(Multilang::getLanguages());
  }

  /**
   * Get an API connection as an authenticated user
   * @param int $userId
   * @return ApiConnection the api connection of the user
   */
  public function getApiConnection($userId = 0)
  {
    // Get currently logged in user, if no user given
    if ($userId == 0) {
      $userId = get_current_user_id();
    }

    // Try to find credentials
    $userId = intval($userId);
    $credentials = $this->getUserCredentials($userId);

    // Get the ready to call instance
    return ApiConnection::getInstance(
      Constant::API_URL,
      $credentials['stUser'],
      $credentials['stApi'],
      str_replace('_', '-', get_locale())
    );
  }

  public function getShortcodeTags()
  {
    //Support Visual Composer (add shortcodes)
    if (WordPress::isPluginActive('js_composer/js_composer.php') && method_exists('WPBMap', 'addAllMappedShortcodes')) {
      \WPBMap::addAllMappedShortcodes();
    }

    global $shortcode_tags;
    return $shortcode_tags;
  }

  public function getShortcodeRegex()
  {
    //Support Visual Composer (add shortcodes)
    if (WordPress::isPluginActive('js_composer/js_composer.php') && method_exists('WPBMap', 'addAllMappedShortcodes')) {
      \WPBMap::addAllMappedShortcodes();
    }

    return get_shortcode_regex();
  }
}