<?php
namespace extas\components\dashboards;

use extas\components\Item;
use extas\components\Replace;
use extas\interfaces\dashboards\IDashboardView;
use extas\interfaces\IReplace;

/**
 * Class DashboardView
 *
 * @package extas\components\dashboards
 * @author jeyroik@gmail.com
 */
class DashboardView extends Item implements IDashboardView
{
    /**
     * @var IReplace
     */
    protected $replace = null;

    /**
     * DashboardView constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $this->replace = new Replace();

        if (!$this->getBasePath()) {
            $this->setBasePath(getenv('EXTAS__WF__VIEW_BASE_PATH') ?: APP__ROOT . '/src/views');
        }

        $this->setViewPath($this->getViewPath() . '.php');
    }

    /**
     * @param array $data
     *
     * @return string
     */
    public function render($data = []): string
    {
        return $this->replace->apply($data)->to($this->getBasePath() . '/' . $this->getViewPath());
    }

    /**
     * @return string
     */
    public function getViewPath(): string
    {
        return $this->config[static::FIELD__VIEW_PATH] ?? '';
    }

    /**
     * @return string
     */
    public function getBasePath(): string
    {
        return $this->config[static::FIELD__BASE_PATH] ?? '';
    }

    /**
     * @param string $viewPath
     *
     * @return IDashboardView
     */
    public function setViewPath(string $viewPath): IDashboardView
    {
        $this->config[static::FIELD__VIEW_PATH] = $viewPath;

        return $this;
    }

    /**
     * @param string $basePath
     *
     * @return IDashboardView
     */
    public function setBasePath(string $basePath): IDashboardView
    {
        $this->config[static::FIELD__BASE_PATH] = $basePath;

        return $this;
    }

    /**
     * @return string
     */
    protected function getSubjectForExtension(): string
    {
        return static::SUBJECT;
    }
}
