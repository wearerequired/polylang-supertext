<?php

namespace Supertext\Polylang\Helper;

/**
 * Class PluginCustomFieldsContentAccessor
 * @package Supertext\Polylang\Helper
 */
abstract class AbstractPluginCustomFieldsContentAccessor implements IContentAccessor, ISettingsAware
{
  /**
   * @var string plugin id
   */
  private $pluginId;
  /**
   * @var TextProcessor text processor
   */
  private $textProcessor;
  /**
   * @var Library library
   */
  private $library;

  /**
   * @param $textProcessor
   * @param $library
   */
  public function __construct($textProcessor, $library)
  {
    $this->pluginId = $this->getPluginId();
    $this->textProcessor = $textProcessor;
    $this->library = $library;
  }

  /**
   * @param $postId
   * @return array
   */
  public function getTranslatableFields($postId)
  {
    $postCustomFields = get_post_meta($postId);
    $savedFieldDefinitions = $this->library->getSettingOption(Constant::SETTING_PLUGIN_CUSTOM_FIELDS);

    if(!isset($savedFieldDefinitions[$this->pluginId])){
      return array();
    }

    $translatableFields = array();
    $meta_keys = array_keys($postCustomFields);

    foreach ($savedFieldDefinitions[$this->pluginId] as $savedFieldDefinition) {
      if (count(preg_grep('/^' . $savedFieldDefinition['meta_key_regex'] . '$/', $meta_keys)) > 0) {
        $translatableFields[] = array(
          'title' => $savedFieldDefinition['label'],
          'name' => $savedFieldDefinition['meta_key_regex'],
          'checkedPerDefault' => true
        );
      }
    }

    return $translatableFields;
  }

  /**
   * @param $post
   * @return array
   */
  public function getRawTexts($post)
  {
    return get_post_meta($post->ID);
  }

  /**
   * @param $post
   * @param $selectedTranslatableFields
   * @return array
   */
  public function getTexts($post, $selectedTranslatableFields)
  {
    $texts = array();

    $postCustomFields = get_post_meta($post->ID);

    foreach($postCustomFields as $meta_key => $value){
      foreach($selectedTranslatableFields as $meta_key_regex => $selected){
        if (!preg_match('/^' . $meta_key_regex . '$/', $meta_key)) {
          continue;
        }

        $texts[$meta_key] = $this->textProcessor->replaceShortcodes($value[0]);
      }
    }

    return $texts;
  }

  /**
   * @param $post
   * @param $texts
   */
  public function setTexts($post, $texts)
  {
    foreach ($texts as $id => $text) {
      $decodedContent = html_entity_decode($text, ENT_COMPAT | ENT_HTML401, 'UTF-8');
      $decodedContent = $this->textProcessor->replaceShortcodeNodes($decodedContent);
      update_post_meta($post->ID, $id, $decodedContent);
    }
  }

  /**
   * @return array
   */
  public function getSettingsViewBundle()
  {
    $savedFieldDefinitions = $this->library->getSettingOption(Constant::SETTING_PLUGIN_CUSTOM_FIELDS);
    $savedFieldDefinitionIds = array();

    if(isset($savedFieldDefinitions[$this->pluginId])){
      foreach($savedFieldDefinitions[$this->pluginId] as $savedFieldDefinition){
        $savedFieldDefinitionIds[] = $savedFieldDefinition['id'];
      }
    }

    return array(
      'view' => new View('backend/settings-plugin-custom-fields'),
      'context' => array(
        'pluginId' => $this->pluginId,
        'pluginName' => $this->getName(),
        'fieldDefinitions' => $this->getFieldDefinitions(),
        'savedFieldDefinitionIds' => $savedFieldDefinitionIds
      )
    );
  }

  /**
   * @param $postData
   */
  public function saveSettings($postData)
  {
    $checkedFieldIds = explode(',', $postData['pluginCustomFields'][$this->pluginId]['checkedFields']);
    $fieldDefinitionsToSave = array();

    $fieldDefinitions = $this->getFieldDefinitions();

    while (($field = array_shift($fieldDefinitions))) {
      if (!empty($field['sub_field_definitions'])) {
        $fieldDefinitions = array_merge($fieldDefinitions, $field['sub_field_definitions']);
        continue;
      }

      if (in_array($field['id'], $checkedFieldIds) && isset($field['meta_key_regex'])) {
        $fieldToSave = $field;
        unset($fieldToSave['sub_field_definitions']);
        $fieldDefinitionsToSave[] = $fieldToSave;
      }
    }

    $savedFieldDefinitions = $this->library->getSettingOption(Constant::SETTING_PLUGIN_CUSTOM_FIELDS);
    $savedFieldDefinitions[$this->pluginId] = $fieldDefinitionsToSave;

    $this->library->saveSettingOption(Constant::SETTING_PLUGIN_CUSTOM_FIELDS, $savedFieldDefinitions);
  }

  /**
   * Abstract function
   * Gets the field definitions
   * @return mixed
   */
  protected abstract function getFieldDefinitions();

  /**
   * @return mixed
   */
  private function getPluginId()
  {
    return lcfirst(str_replace('ContentAccessor', '', (new \ReflectionClass($this))->getShortName()));
  }
}