<?php

namespace Backpack\CRUD\PanelTraits;

trait Buttons
{
    // ------------
    // BUTTONS
    // ------------

    // TODO: $this->crud->setButtons(); // default includes edit and delete, with their name, icon, permission, link and class (btn-default)
    // TODO: $this->crud->addButton();
    // TODO: $this->crud->removeButton();
    // TODO: $this->crud->replaceButton();



	// ------------
    // TONE FUNCTIONS - UNDOCUMENTED, UNTESTED, SOME MAY BE USED
    // ------------
    // TODO: check them

    public function addButton($button)
    {
        array_unshift($this->buttons, $button);
    }

    public function buttons()
    {
        return $this->buttons;
    }

    public function addCustomButton($button)
    {
        array_unshift($this->custom_buttons, $button);
    }

    public function customButtons()
    {
        return $this->custom_buttons;
    }

    public function showButtons()
    {
        return ! empty($this->buttons) && ! (count($this->buttons) == 1 && array_key_exists('add', $this->buttons));
    }

    public function initButtons()
    {
        $this->buttons = [
            'add'    => ['route' => "{$this->route}/create", 'label' => trans('crud::crud.buttons.add'), 'class' => '', 'hide' => [], 'icon' => 'fa-plus-circle', 'extra' => []],
            'view'   => ['route' => "{$this->route}/%d", 'label' => trans('crud::crud.buttons.view'), 'class' => '', 'hide' => [], 'icon' => 'fa-eye', 'extra' => []],
            'edit'   => ['route' => "{$this->route}/%d/edit", 'label' => trans('crud::crud.buttons.edit'), 'class' => '', 'hide' => [], 'icon' => 'fa-edit', 'extra' => []],
            'delete' => ['route' => "{$this->route}/%d", 'label' => trans('crud::crud.buttons.delete'), 'class' => '', 'hide' => [], 'icon' => 'fa-trash', 'extra' => ['data-confirm' => trans('crud::crud.confirm.delete'), 'data-type' => 'delete']],
        ];
    }

    public function removeButtons($buttons)
    {
        foreach ($buttons as $button) {
            unset($this->buttons[$button]);
        }

        return $this->buttons;
    }

}
