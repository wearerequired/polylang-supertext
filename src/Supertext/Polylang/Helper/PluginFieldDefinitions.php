<?php

namespace Supertext\Polylang\Helper;

/**
 * Class PluginFieldDefinitions
 * @package Supertext\Polylang\Helper
 */
class PluginFieldDefinitions
{
  /**
   * @return array Yoast SEO field definitions
   */
  public static function getYoastSeoFieldDefinitions()
  {
    return array(
      'label' => 'Yoast SEO',
      'type' => 'group',
      'sub_field_definitions' => array(
        '_yoast_wpseo_title' => array(
          'label' => __('SEO-optimized title', 'polylang-supertext'),
          'type' => 'field'
        ),
        '_yoast_wpseo_metadesc' => array(
          'label' => __('SEO-optimized description', 'polylang-supertext'),
          'type' => 'field'
        ),
        '_yoast_wpseo_focuskw' => array(
          'label' => __('Focus keywords', 'polylang-supertext'),
          'type' => 'field'
        ),
        '_yoast_wpseo_opengraph-title' => array(
          'label' => __('Facebook title', 'polylang-supertext'),
          'type' => 'field'
        ),
        '_yoast_wpseo_opengraph-description' => array(
          'label' => __('Facebook description', 'polylang-supertext'),
          'type' => 'field'
        )
      )
    );
  }

  public static function getBePageBuilderFieldDefinitions()
  {
    return array(
      'label' => 'BE page builder',
      'type' => 'group',
      'sub_field_definitions' => array(
        '_be_pb_content' => array(
          'label' => __('BE page builder content', 'polylang-supertext'),
          'type' => 'field'
        )
      )
    );
  }
}