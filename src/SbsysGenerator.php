<?php

namespace Drupal\os2web_hearings;

use Drupal\node\NodeInterface;

/**
 * Class generates xml string from array.
 */
class SbsysGenerator {

  /**
   * XmlWriter object.
   *
   * @var \XMLWriter
   */
  protected $xmlWriter;

  /**
   * Version of XML.
   *
   * @var string
   */
  protected $ver;

  /**
   * Chartset of XML.
   *
   * @var string
   */
  protected $charset;

  /**
   * Class constructor.
   *
   * @param string $ver
   *   Xml version string.
   * @param string $charset
   *   Xml charset.
   */
  public function __construct($ver = '1.0', $charset = 'UTF-8') {
    $this->ver = $ver;
    $this->charset = $charset;
  }

  /**
   * Main process method.
   *
   * @param string $root
   *   Root tag name.
   * @param \Drupal\node\NodeInterface $hearing
   *   Hearing node.
   * @param string $renderedText
   *   Node rendered text.
   *
   * @return string|bool
   *   Create File URI or FALSE on error.
   */
  public function generateFile($root, NodeInterface $hearing, $renderedText) {
    $this->xmlWriter = new \XMLWriter();

    $this->xmlWriter->openMemory();
    $this->xmlWriter->startDocument($this->ver, $this->charset);
    $this->xmlWriter->startElement($root);
    $this->xmlWriter->setIndent(TRUE);
    $this->xmlWriter->setIndentString('    ');

    $this->write($this->xmlWriter, $this->generateXmlData($hearing, $renderedText));
    $this->xmlWriter->endElement();
    $this->xmlWriter->endDocument();
    $xml = $this->xmlWriter->outputMemory(TRUE);
    $this->xmlWriter->flush();

    /** @var \Drupal\Core\File\FileSystemInterface $fileSystem */
    $fileSystem = \Drupal::service('file_system');
    $nodeId = $hearing->id();

    return $fileSystem->saveData($xml, "temporary://sbsys_$nodeId.xml");
  }

  /**
   * Write element method.
   *
   * @param \XMLWriter $xml
   *   Xml writer instance.
   * @param array|object $data
   *   Data to append to current writer.
   */
  protected function write(\XMLWriter $xml, $data) {
    foreach ($data as $key => $value) {
      if (is_int($key) && (is_array($value) || is_object($value))) {
        $xml->startElement('item');
        $this->write($xml, $value);
        $xml->endElement();
      }
      else {
        if (is_int($key)) {
          $xml->writeElement("item$key", $value);
        }
        else {
          if (is_array($value) || is_object($value)) {
            $xml->startElement($key);
            $this->write($xml, $value);
            $xml->endElement();
          }
          else {
            if ($key == 'BodyTekst' || $key == 'FormularData') {
              $xml->startElement($key);
              $xml->writeCdata($value);
              $xml->endElement();
            }
            else {
              $xml->writeElement($key, $value);
            }
          }
        }
      }
    }
  }

  /**
   * Generates the SBSYS XML structure of a node.
   *
   * @param \Drupal\node\NodeInterface $hearing
   *   Hearing node.
   * @param string $renderedText
   *   Node rendered text.
   *
   * @return array
   *   Filled SBSYS XML structure.
   */
  protected function generateXmlData(NodeInterface $hearing, $renderedText) {
    $sbsysJournalisering = [
      'PrimaerPartCprNummer' => '',
      'PrimaerPartCvrNummer' => '',
      'KLe' => '',
      'SagSkabelonId' => '',
      'caseid' => $hearing->field_os2web_hearings_sbsys_case->value,
    ];
    $digitalForsendelse = [
      'Slutbruger' => [
        'CprNummer' => '',
        'CvrNummer' => '',
        'Navn' => '',
        'Adresse' => '',
        'Postnr' => '',
        'Postdistrikt' => '',
      ],
      'Kvittering' => [
        'TitelTekst' => $hearing->getTitle(),
        'BodyTekst' => $hearing->body->value,
      ],
      'MaaSendesTilDFF' => 'ja',
    ];

    $xmlData = [
      'OS2FormsId' => $hearing->id(),
      'SBSYSJournalisering' => $sbsysJournalisering,
      'DigitalForsendelse' => $digitalForsendelse,
      'FormularData' => $renderedText,
    ];

    return $xmlData;
  }

}
