<?php

namespace Supertext\Polylang\TextAccessors;

use Supertext\Polylang\Api\Multilang;

/**
 * Class PostMediaTextAccessor
 * @package Supertext\Polylang\TextAccessors
 */
class PostMediaTextAccessor implements ITextAccessor
{
  /**
   * Gets the content accessors name
   * @return string
   */
  public function getName()
  {
    return __('Media', 'polylang-supertext');
  }

  /**
   * @param $postId
   * @return array
   */
  public function getTranslatableFields($postId)
  {
    $translatableFields = array();

    $translatableFields[] = array(
      'title' => __('Image captions', 'polylang-supertext'),
      'name' => 'post_image',
      'checkedPerDefault' => true
    );

    return $translatableFields;
  }

  /**
   * @param $post
   * @return array
   */
  public function getRawTexts($post)
  {
    return get_children(
      array(
        'post_parent' => $post->ID,
        'post_type' => 'attachment',
        'post_mime_type' => 'image')
    );
  }

  /**
   * @param $post
   * @param $selectedTranslatableFields
   * @return array
   */
  public function getTexts($post, $selectedTranslatableFields)
  {
    $texts = array();

    if ($selectedTranslatableFields['post_image']) {
      $attachments = get_children(
        array(
          'post_parent' => $post->ID,
          'post_type' => 'attachment',
          'post_mime_type' => 'image',
          'orderby' => 'menu_order ASC, ID',
          'order' => 'DESC')
      );

      foreach ($attachments as $attachment) {
        $texts[$attachment->ID] = array(
          'post_title' => $attachment->post_title,
          'post_content' => $attachment->post_content,
          'post_excerpt' => $attachment->post_excerpt,
          'image_alt' => get_post_meta($attachment->ID, '_wp_attachment_image_alt', true)
        );
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

      $targetAttachmentId = Multilang::getPostInLanguage($id, Multilang::getPostLanguage($post->ID));
      $targetAttachement = get_post($targetAttachmentId);

      foreach($text as $key => $value){
        if ($key === 'image_alt') {
          update_post_meta(
            $targetAttachement->ID,
            '_wp_attachment_image_alt',
            addslashes(html_entity_decode($value, ENT_COMPAT | ENT_HTML401, 'UTF-8'))
          );
          continue;
        }

        $targetAttachement->{$key} = html_entity_decode($value, ENT_COMPAT | ENT_HTML401, 'UTF-8');
      }

      wp_update_post($targetAttachement);
    }
  }
}