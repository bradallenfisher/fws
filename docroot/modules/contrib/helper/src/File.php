<?php

namespace Drupal\helper;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\file\FileInterface;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface;

/**
 * Provides helpers for working with files.
 */
class File {

  /**
   * The file storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $fileStorage;

  /**
   * The mime type guesser.
   *
   * @var \Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface
   */
  protected $mimeTypeGuesser;

  /**
   * File constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface $mime_type_guesser
   *   The mime type guesser.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, MimeTypeGuesserInterface $mime_type_guesser) {
    $this->fileStorage = $entity_type_manager->getStorage('file');
    $this->mimeTypeGuesser = $mime_type_guesser;
  }

  /**
   * Creates or reuses a file object based on a URI.
   *
   * @param string $uri
   *   A string containing the URI.
   * @param bool $reuse_existing
   *   If TRUE will try to reuse an existing file with the same URI.
   *   If FALSE will always create a new file.
   *
   * @return \Drupal\file\FileInterface
   *   A file object.
   */
  public function createOrReuseFromUri($uri, $reuse_existing = TRUE) {
    if ($reuse_existing) {
      // Check if this file already exists, and if so, return that.
      $files = $this->fileStorage->loadByProperties(['uri' => $uri]);
      if ($valid_files = array_filter($files, [$this, 'filterValidFiles'])) {
        return reset($valid_files);
      }
    }

    // If an existing file could not be found, create a new file.
    return $this->fileStorage->create([
      'uri' => $uri,
      'uid' => \Drupal::currentUser()->id(),
    ]);
  }

  /**
   * Filter callback; Permanent files or temporary files owned by current user.
   *
   * @param \Drupal\file\FileInterface $file
   *   The file object.
   *
   * @return bool
   *   TRUE if the file is valid, or FALSE otherwise.
   */
  public static function filterValidFiles(FileInterface $file) {
    return $file->isPermanent() || $file->getOwnerId() == \Drupal::currentUser()->id();
  }

  /**
   * Converts a file URL into a data URI.
   *
   * @param string $uri
   *   The file URI.
   * @param bool $base_64_encode
   *   TRUE to return the data URI as base-64 encoded content.
   * @param string|null $mimetype
   *   The optional mime type to provide for the data URI. If not provided
   *   the mime type guesser service will be used.
   *
   * @return string
   *   The image data URI for use in a src attribute.
   *
   * @throws \Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException
   *   If the file cannot be read.
   */
  public function getDataUri($uri, $base_64_encode = TRUE, $mimetype = NULL) {
    if (!isset($mimetype)) {
      $mimetype = $this->mimeTypeGuesser->guess($uri);
    }
    $contents = file_get_contents($uri);
    if ($contents === FALSE) {
      throw new AccessDeniedException($uri);
    }
    if ($base_64_encode) {
      $contents = base64_encode($contents);
    }
    return 'data:' . $mimetype . ($base_64_encode ? ';base64' : '') . ',' . $contents;
  }

}
