<?php
namespace extas\interfaces\dashboards;

use extas\interfaces\IItem;

/**
 * Interface IDashboardView
 *
 * @package extas\interfaces\dashboards
 * @author jeyroik@gmail.com
 */
interface IDashboardView extends IItem
{
    const SUBJECT = 'extas.workflow.dashboard.view';

    const FIELD__VIEW_PATH = 'view_path';
    const FIELD__BASE_PATH = 'base_path';

    /**
     * @return string
     */
    public function getViewPath(): string;

    /**
     * @param string $viewPath
     *
     * @return IDashboardView
     */
    public function setViewPath(string $viewPath): IDashboardView;

    /**
     * @return string
     */
    public function getBasePath(): string;

    /**
     * @param string $basePath
     *
     * @return IDashboardView
     */
    public function setBasePath(string $basePath): IDashboardView;

    /**
     * @param array $data
     *
     * @return string
     */
    public function render($data = []): string;
}
