<?php
namespace extas\components\dashboards;

use extas\interfaces\IHasName;

/**
 * Class DashboardList
 *
 * @package extas\components\dashboards
 * @author jeyroik@gmail.com
 */
class DashboardList extends DashboardView
{
    const FIELD__ITEMS = 'items';
    const FIELD__TITLE = 'title';
    const FIELD__NAME = 'name';
    const FIELD__SELECTED = 'selected';

    /**
     * @param array $data
     *
     * @return string
     */
    public function render($data = []): string
    {
        $itemTemplate = new DashboardView([DashboardView::FIELD__VIEW_PATH => 'layouts/select.item']);
        $itemsView = '';
        $current = $this->getSelected();
        $items = $this->getItems();

        foreach ($items as $item) {
            $item['selected'] = $item->getName() == $current
                ? 'selected'
                : '';
            $itemsView .= $itemTemplate->render(['item' => $item]);
        }

        $listTemplate = new DashboardView([DashboardView::FIELD__VIEW_PATH => 'layouts/select.list']);
        return $listTemplate->render([
            'list' => [
                'title' => $this->getListTitle(),
                'name' => $this->getListName()
            ],
            'items' => $itemsView
        ]);
    }

    /**
     * @return string
     */
    public function getSelected(): string
    {
        return $this->config[static::FIELD__SELECTED] ?? '';
    }

    /**
     * @return string
     */
    public function getListTitle(): string
    {
        return $this->config[static::FIELD__TITLE] ?? '';
    }

    /**
     * @return string
     */
    public function getListName(): string
    {
        return $this->config[static::FIELD__NAME] ?? '';
    }

    /**
     * @return IHasName[]
     */
    public function getItems(): array
    {
        return $this->config[static::FIELD__ITEMS] ?? [];
    }
}
