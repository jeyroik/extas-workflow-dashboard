<?php

use extas\interfaces as I;
use extas\components as C;

return [
    I\plugins\IPluginRepository::class => C\plugins\PluginRepository::class,
    I\extensions\IExtensionRepository::class => C\extensions\ExtensionRepository::class,
    I\stages\IStageRepository::class => C\stages\StageRepository::class,
    I\packages\IPackageClassRepository::class => C\packages\PackageClassRepository::class,
    I\repositories\IRepository::class => C\repositories\Repository::class,
    I\repositories\drivers\IDriverRepository::class => C\repositories\drivers\DriverRepository::class
];
