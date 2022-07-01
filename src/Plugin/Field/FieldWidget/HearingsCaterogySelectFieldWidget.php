<?php

namespace Drupal\os2web_hearings\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\OptionsSelectWidget;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\field\Entity\FieldConfig;

/**
 * Plugin implementation of the 'hearings_caterogy_select_field_widget' widget.
 *
 * @FieldWidget(
 *   id = "hearings_caterogy_select_field_widget",
 *   module = "os2web_hearings",
 *   label = @Translation("Høringer kategorier valgliste"),
 *   field_types = {
 *     "entity_reference",
 *     "list_integer",
 *     "list_float",
 *     "list_string"
 *   },
 *   multiple_values = TRUE
 * )
 */
class HearingsCaterogySelectFieldWidget extends OptionsSelectWidget {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'disabled_elements_level' => 0,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = [];
    $elements['disabled_elements_level'] = [
      '#type' => 'select',
      '#title' => $this->t('Depth level for disabled elements'),
      '#options' => range(0, 10),
      '#default_value' => $this->getSetting('disabled_elements_level'),
      '#required' => TRUE,
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();

    $summary[] = t('Depth level for disabled elements: @disabled_elements_level', ['@disabled_elements_level' => $this->getSetting('disabled_elements_level')]);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    $storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
    $disabled_elements_level = $this->getSetting('disabled_elements_level');
    $options_attributes = [];
    foreach ($element['#options'] as $tid => $label) {
      if (!is_int($tid)) {
        continue;
      }
      if ($disabled_elements_level < count($storage->loadAllParents($tid))) {
        continue;
      }

      $options_attributes[$tid] = ['disabled' => TRUE];
    }
    $element['#options_attributes'] = $options_attributes;
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    // This format is supposed to be used only for configurable fields;
    if (! ($field_definition instanceof FieldConfig)) {
      return FALSE;
    }

    // This formatter is only available for node entity of
    // os2web_hearings_hearing_case bundle with available
    // field_os2web_hearings_category as entity reference type.
    return $field_definition->id() == 'node.os2web_hearings_hearing_case.field_os2web_hearings_category' && $field_definition->getType() == 'entity_reference';
  }


}
