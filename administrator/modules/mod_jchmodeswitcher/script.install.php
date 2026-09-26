<?php

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerScript;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die('Restricted Access');

// phpcs:enable PSR1.Files.SideEffects

class Mod_JchmodeswitcherInstallerScript extends InstallerScript
{
    protected $allowDowngrades = true;

    public function postflight(string $type)
    {
        if ($type == 'install' || $type == 'update') {
            $instances = $this->getInstances(true);

            /** @var DatabaseInterface $db */
            $db = Factory::getContainer()->get(DatabaseInterface::class);
            // Helper to fetch a view level id by title with safe fallback
            $getLevelId = static function (DatabaseInterface $db, string $title, int $fallback): int {
                $query = $db->getQuery(true)
                    ->select($db->quoteName('id'))
                    ->from($db->quoteName('#__viewlevels'))
                    ->where($db->quoteName('title') . ' = ' . $db->quote($title))
                    ->setLimit(1);

                $db->setQuery($query);

                try {
                    $id = (int)$db->loadResult();
                    return $id ?: $fallback;
                } catch (\Throwable $e) {
                    return $fallback;
                }
            };

            $publicId = $getLevelId($db, 'Public', 1);
            $registeredId = $getLevelId($db, 'Registered', 2);


            // If there are multiple, keep the newest (highest id) and delete the others (+ menu links)
            if (count($instances) > 1) {
                rsort($instances, SORT_NUMERIC);       // highest first
                $moduleId = array_shift($instances);  // keep newest
                $deleteIds = $instances;               // everything else

                // Delete menu assignments to avoid orphans
                $query = $db->getQuery(true)
                    ->delete($db->quoteName('#__modules_menu'))
                    ->where($db->quoteName('moduleid') . ' IN (' . implode(',', array_map('intval', $deleteIds)) . ')');
                $db->setQuery($query)->execute();

                // Delete the extra module rows
                $query = $db->getQuery(true)
                    ->delete($db->quoteName('#__modules'))
                    ->where($db->quoteName('id') . ' IN (' . implode(',', array_map('intval', $deleteIds)) . ')');
                $db->setQuery($query)->execute();
            } else {
                $moduleId = (int)$instances[0];
            }

            $module = [
                'asset_id' => 0,
                'language' => '*',
                'note' => '',
                'published' => 1,
                'assignment' => 0,
                'showtitle' => 1,
                'content' => '',
                'client_id' => 1,
                'module' => $this->extension,
                'position' => 'status',
                'access' => $registeredId,
                'title' => Text::_(strtoupper($this->extension)),
                'params' => []
            ];

            if (empty($instances)) {
                $module['id'] = 0;
            } else {
                $module['id'] = $moduleId;
            }

            if (empty($instances) || !$this->inModulesMenuTable($moduleId)) {
                $model = Factory::getApplication()
                    ->bootComponent('com_modules')
                    ->getMVCFactory()
                    ->createModel('Module', 'Administrator', ['ignore_request' => true]);
                if (!$model->save($module)) {
                    Factory::getApplication()->enqueueMessage(
                        Text::sprintf('MOD_JCHMODESWITCHER_INSTALL_ERROR', $model->getError())
                    );
                }

                $this->addToModulesMenuTable();
            } else {
                //Change public access to registered
                $query = $db->getQuery(true)
                    ->update($db->quoteName('#__modules'))
                    ->set($db->quoteName('access') . ' = ' . $db->quote($registeredId))
                    ->where($db->quoteName('id') . ' = ' . $db->quote($moduleId))
                    ->where($db->quoteName('access') . ' = ' . $publicId);

                try {
                    $db->setQuery($query)->execute();
                } catch (Exception $e) {
                }
            }
        }

        return true;
    }

    private function inModulesMenuTable(mixed $id): bool
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select($db->quoteName('moduleid'))
            ->from('#__modules_menu')
            ->where($db->quoteName('moduleid') . ' = :id');
        $query->bind(':id', $id);
        $db->setQuery($query, 0, 1);

        return (bool)$db->loadResult();
    }

    private function addToModulesMenuTable(): void
    {
        try {
            $id = $this->getInstances(true)[0];
            $db = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->getQuery(true)
                ->insert('#__modules_menu')
                ->columns([$db->quoteName('moduleid'), $db->quoteName('menuid')])
                ->values((int)$id . ',  0');
            $db->setQuery($query);
            $db->execute();
        } catch (Exception $e) {
        }
    }
}
