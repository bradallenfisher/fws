<?php

namespace Drupal\helper\Commands;

use Drupal\Core\Installer\Exception\InstallerException;
use Drupal\helper\InstallProfile;
use Drush\Commands\DrushCommands;
use Drush\Exceptions\UserAbortException;

/**
 * Drush commands for working with install profiles.
 */
class InstallProfileCommands extends DrushCommands {

  /**
   * The install profile helper.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $installProfile;

  /**
   * SchemaCommands constructor.
   *
   * @param \Drupal\helper\InstallProfile $installProfile
   *   The install profile helper.
   */
  public function __construct(InstallProfile $installProfile) {
    $this->installProfile = $installProfile;
  }

  /**
   * Switches install profiles.
   *
   * @param string $profile
   *   The install profile.
   * @param int $schema_version
   *   The schema version to set for the install profile.
   *
   * @command install-profile:switch
   * @alias ips
   * @usage drush install-profile:switch minimal
   *   Switches from the current install profile to the minimal install profile.
   *
   * @throws \Drush\Exceptions\UserAbortException
   *   If the user aborted the confirmation.
   */
  public function switch($profile, $schema_version = NULL) {
    $args = [
      '!current' => \Drupal::installProfile(),
      '!new' => $profile,
    ];

    try {
      $this->installProfile->validateProfile($profile);
    }
    catch (InstallerException $exception) {
      $this->io()->error($exception->getMessage());
    }

    if (!$this->io()->confirm(dt('Do you want to switch from the !current install profile to the !new install profile?', $args))) {
      throw new UserAbortException();
    }

    if (!$this->getConfig()->simulate()) {
      $this->installProfile->switch($profile, $schema_version);
    }


    $this->io()->success(dt('Updated install profile from !current to !new.', $args));
  }

}
