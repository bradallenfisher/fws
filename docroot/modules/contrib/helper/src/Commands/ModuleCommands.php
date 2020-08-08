<?php

namespace Drupal\helper\Commands;

use Consolidation\AnnotatedCommand\CommandData;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drush\Commands\DrushCommands;
use Drush\Exceptions\UserAbortException;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Drush commands for working with module schemas.
 */
class ModuleCommands extends DrushCommands {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * SchemaCommands constructor.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler.
   */
  public function __construct(ModuleHandlerInterface $moduleHandler) {
    $this->moduleHandler = $moduleHandler;
  }

  /**
   * Gets the schema version for a module.
   *
   * @param string $module
   *   The module name, for example "system".
   *
   * @command module:schema-version:get
   * @aliases msvg
   * @validate-module
   * @usage drush module:schema-version:get system
   *   Displays the current schema version for the system module.
   */
  public function getSchemaVersion($module) {
    return (string) drupal_get_installed_schema_version($module);
  }

  /**
   * Sets the schema version for a module.
   *
   * @param string $module
   *   The module name, for example "system".
   * @param int|string $version
   *   The version to set or the string
   *   "current[+-]number" to set a relative value to the current version.
   *
   * @command module:schema-version:set
   * @aliases msvs
   * @validate-module
   * @usage drush module:schema-version:set system 8701
   *   Sets the current schema version for the system module to 8701.
   * @usage drush module:schema-version:set system current-3
   *   Sets the current schema version for the system module to three minus the
   *   current schema.
   * @usage drush module:schema-version:set system current+1
   *   Sets the current schema version for the system module to one plus the
   *   current schema.
   *
   * @throws \InvalidArgumentException
   * @throws \Drush\Exceptions\UserAbortException
   * @throws \RuntimeException
   */
  public function setSchemaVersion($module, $version) {
    module_load_install($module);

    $current_version = (int) drupal_get_installed_schema_version($module);
    $last_removed = $this->moduleHandler->invoke($module, 'update_last_removed');
    $lowest = \Drupal::CORE_MINIMUM_SCHEMA_VERSION;

    $args = [
      '!module' => $module,
      '!version' => &$version,
      '!current' => $current_version,
      '!removed' => $last_removed,
      '!lowest' => $lowest,
    ];

    if (preg_match('/(current([\+\-]))?(\d+)/', $version, $matches)) {
      switch ($matches[2]) {
        case '+':
          $version = $current_version + (int) $matches[3];
          break;

        case '-':
          $version = $current_version - (int) $matches[3];
          break;

        case '':
          $version = (int) $matches[3];
          break;
      }
    }
    else {
      throw new \InvalidArgumentException(dt('The schema version !version is not valid.', $args));
    }

    if ($version < $lowest) {
      throw new \InvalidArgumentException(dt('The schema version !version cannot be lower than !lowest.', $args));
    }
    elseif ($version === $current_version) {
      return dt('The !module module schema is already at !current.', $args);
    }
    elseif (!empty($last_removed) && $version < $last_removed) {
      $this->io()->caution(dt('The schema version !version is lower than the !module_update_last_removed() value of !removed. This will prevent module updates from running.', $args));
    }

    if (!$this->io()->confirm(dt('Do you want to set the schema for !module module from !current to !version?', $args))) {
      throw new UserAbortException();
    }

    if (!$this->getConfig()->simulate()) {
      drupal_set_installed_schema_version($module, $version);
      if (drupal_get_installed_schema_version($module, TRUE) !== $version) {
        throw new \RuntimeException(dt('Unable to update schema for !module module from !current to !version.', $args));
      }
    }

    return dt('Updated schema for !module module from !current to !version.', $args);
  }

  /**
   * Sets the schema version for module.
   *
   * @hook interact module:schema-version:set
   */
  public function interactSchemaVersion(InputInterface $input) {
    if ($input->getArgument('module') && $input->getArgument('version') === NULL) {
      $module = $input->getArgument('module');
      $this->validateModuleInstalled($module);
      $versions = $this->getAvailableSchemaVersions($module);
      if (empty($versions)) {
        throw new \RuntimeException(dt('No possible schema versions for !module module.', ['!module' => $module]));
      }
      $current_version = $this->getSchemaVersion($module);
      $this->io()->note(dt('The current schema version for !module module is !version.', ['!module' => $module, '!version' => $current_version]));
      $selected = $this->io()->choice(dt('Choose a schema version to set'), $versions);
      $input->setArgument('version', $versions[$selected]);
    }
  }

  /**
   * Resets a post-update hook for a module.
   *
   * @param string $module
   *   The module name, for example "system".
   * @param string $hook
   *   The post-update hook name.
   *
   * @command module:post-update:reset
   * @usage drush module:post-update:reset system extra_fields
   *   Resets the status of the system_post_update_extra_fields function so
   *   that it will run again on the next database update.
   * @aliases mpur
   * @validate-module
   *
   * @throws \InvalidArgumentException
   * @throws \Drush\Exceptions\UserAbortException
   * @throws \RuntimeException
   */
  public function resetPostUpdate($module, $hook) {
    $key_value = \Drupal::keyValue('post_update');
    $update_list = $key_value->get('existing_updates');

    $post_update_hook = $module . '_post_update_' . $hook;
    if (!in_array($post_update_hook, $update_list)) {
      throw new \InvalidArgumentException(dt("There is no @hook function that has run.", ['@hook' => $post_update_hook]));
    }

    if (!$this->io()->confirm(dt('Do you want to reset the post-update hook for @hook?', ['@hook' => $post_update_hook]))) {
      throw new UserAbortException();
    }

    if (!$this->getConfig()->simulate()) {
      $update_list = array_diff($update_list, [$post_update_hook]);
      $key_value->set('existing_updates', $update_list);
    }

    dt('Reset status of @hook so it will run on the next update.', ['@hook' => $post_update_hook]);
  }

  /**
   * Interaction hook for the module:post-update:reset command.
   *
   * @hook interact module:post-update:reset
   */
  public function interactResetPostUpdate(InputInterface $input) {
    if ($input->getArgument('hook') === NULL) {
      $module = $input->getArgument('module');
      if ($module) {
        $this->validateModuleInstalled($module);
      }
      $key_value = \Drupal::keyValue('post_update');
      $update_list = $key_value->get('existing_updates');
      $post_update_options = [];
      foreach ($update_list as $post_update_hook) {
        if (!$module || $module === $this->getModuleFromPostUpdateHookName($post_update_hook)) {
          $post_update_options[] = $post_update_hook;
        }
      }
      if (empty($post_update_options)) {
        if ($module) {
          throw new \RuntimeException(dt('No !module_post_update functions have run yet.', ['!module' => $module]));
        }
        else {
          throw new \RuntimeException(dt('No hook_post_update functions have run yet.'));
        }
      }
      $selected = $this->io()->choice(dt('Choose a post_update hook to reset'), $post_update_options);
      if (!$module) {
        $module = $this->getModuleFromPostUpdateHookName($post_update_options[$selected]);
        $input->setArgument('module', $module);
      }
      $input->setArgument('hook', str_replace($module . '_post_update_', '', $post_update_options[$selected]));
    }
  }

  /**
   * Extract the module name from a post-update hook.
   *
   * @param string $hook
   *   The post-update hook name.
   *
   * @return string
   *   The module name.
   */
  protected function getModuleFromPostUpdateHookName($hook) {
    return preg_replace('/\_post\_update\_.*$/', '', $hook);
  }

  /**
   * Deletes the schema version for a module.
   *
   * This is useful for removing leftover schemas of deleted modules.
   *
   * @param string $module
   *   The module name, for example "system".
   *
   * @command module:schema-version:delete
   * @aliases msvd
   * @validate-module
   * @usage drush module:schema-version:delete system
   *   Deletes the system module schema version.
   */
  public function deleteSchemaVersion($module) {
    module_load_install($module);

    $args = [
      '!module' => $module,
    ];

    if ($this->moduleHandler->moduleExists($module)) {
      $this->io()->caution(dt('You should uninstall the !module module instead of deleting its schema.', $args));
    }

    if (!$this->io()->confirm(dt('Do you want to delete the schema for !module module?', $args))) {
      throw new UserAbortException();
    }

    if (!$this->getConfig()->simulate()) {
      \Drupal::keyValue('system.schema')->delete($module);
      if (drupal_get_installed_schema_version($module, TRUE) !== SCHEMA_UNINSTALLED) {
        throw new \RuntimeException(dt('Unable to delete schema for !module module.', $args));
      }
    }

    return dt('Deleted schema for !module module.', $args);
  }

  /**
   * Cleans up the schema versions for deleted modules.
   *
   * @command module:schema-version:cleanup
   * @aliases msvc
   * @usage drush module:schema-version:cleanup
   *   Cleans up any missing module schemas.
   */
  public function cleanupSchemaVersion() {
    $list = $this->moduleHandler->getModuleList();
    /** @var array $schemas */
    $schemas = drupal_get_installed_schema_version(NULL, FALSE, TRUE);
    $missing = array_keys(array_diff_key($schemas, $list));
    if (empty($missing)) {
      return dt('No deleted module schemas to cleanup.');
    }

    $this->io()->note(dt('Found the following deleted modules with schemas to clean up:'));
    $this->io()->listing($missing);

    foreach ($missing as $module) {
      $this->io()->success($this->deleteSchemaVersion($module));
    }
  }

  /**
   * Validation hook to check that a module name is valid.
   *
   * If the argument to be validated is not named $module, pass the
   * argument name as the value of the validate-module-name annotation.
   *
   * @param \Consolidation\AnnotatedCommand\CommandData $commandData
   *   The command data.
   *
   * @hook validate @validate-module
   */
  public function validateModule(CommandData $commandData) {
    $arg_name = $commandData->annotationData()->get('validate-module-name', NULL) ?: 'module';
    $module = $commandData->input()->getArgument($arg_name);
    $this->validateModuleInstalled($module);
  }

  /**
   * Validates that a module is valid and installed.
   *
   * @param string $module
   *   The module name.
   *
   * @throws \InvalidArgumentException
   *   If the module is not installed.
   */
  protected function validateModuleInstalled($module) {
    if (\Drupal::keyValue('system.schema')->get($module) === NULL) {
      throw new \InvalidArgumentException(dt('The !module module is not installed.', ['!module' => $module]));
    }
  }

  /**
   * Returns a list of possible schema versions for a module.
   *
   * @param string $module
   *   The module name.
   *
   * @return int[]
   *   An array of schema versions.
   */
  protected function getAvailableSchemaVersions($module) {
    module_load_install($module);

    $versions = drupal_get_schema_versions($module);
    if (!$versions) {
      $versions = [];
    }

    // Add the minimum schema version available, which is either the value from
    // hook_update_last_removed(), or the n000 where n is the major Drupal
    // version number, since that is the schema version modules receive by
    // default when they are installed.
    $minimum_version = $this->moduleHandler->invoke($module, 'update_last_removed') ?? drush_drupal_major_version() * 1000;
    array_unshift($versions, $minimum_version);

    // Add the current version minus one as an option.
    $current_version = drupal_get_installed_schema_version($module);
    if ($current_version > 0 && ($current_version - 1) > $minimum_version) {
      $versions[] = $current_version - 1;
    }

    // Filter out the current version as well as any versions below the last
    // removed version.
    $versions = array_filter($versions, function ($version) use ($current_version, $minimum_version) {
      return $version != $current_version && $version >= $minimum_version;
    });

    // Unique and sort the options.
    $versions = array_unique($versions);
    sort($versions);

    return $versions;
  }

}
