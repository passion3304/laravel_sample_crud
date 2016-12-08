<?php

namespace Backpack\CRUD\PanelTraits;

trait Tabs
{
    public $tabs = [];
    public $tabsHorizontal = true;

    private function initTabs()
    {
        if (is_array($this->tabs)) {
            $this->tabs = collect([]);
            $this->tabsHorizontal = config('backpack.crud.tabs_horizontal', true);
        }
    }

    public function enableVerticalTabs()
    {
        $this->tabsHorizontal = false;

        return $this->tabsHorizontal;
    }

    public function disableVerticalTabs()
    {
        $this->tabsHorizontal = true;

        return $this->tabsHorizontal;
    }

    public function enableHorizontalTabs()
    {
        $this->tabsHorizontal = true;

        return $this->tabsHorizontal;
    }

    public function disableHorizontalTabs()
    {
        $this->tabsHorizontal = false;

        return $this->tabsHorizontal;
    }

    public function clearTabs()
    {
        $this->tabs = collect([]);

        return $this->tabs;
    }

    public function getTabs()
    {
        $this->initTabs();

        return $this->tabs;
    }

    public function addTab($label)
    {
        $this->initTabs();

        $newTab = (object) [
            'label' => $label,
            'name'  =>  snake_case($label),
            'fields' => collect([]),
            'horizontal' => $this->tabsHorizontal,
        ];

        $this->tabs->push($newTab);

        return $newTab;
    }

    public function removeTab($label)
    {
        $this->initTabs();

        $this->tabs = $this->tabs->reject(function ($tab) use ($label) {
            return $tab->label == $label;
        });

        return $this->tabs;
    }

    public function getCreateTabs()
    {
        return $this->tabs->filter(function (&$tab) {

            $tab->fields = $tab->fields->reject(function ($field) {
                return $field['method'] == 'update';
            });

            return $tab->fields->count();
        });
    }

    public function getUpdateTabs()
    {
        return $this->tabs->filter(function (&$tab) {

            $tab->fields = $tab->fields->reject(function ($field) {
                return $field['method'] == 'create';
            });

            return $tab->fields->count();
        });
    }
}
