<?php

namespace Backpack\CRUD\app\Http\Controllers\CrudFeatures;

//save_and_back save_and_edit save_and_new
trait SaveActions
{
    public function getSaveAction()
    {
        $saveAction = session('save_action', config('backback.crud.default_save_action', 'save_and_back'));
        $saveOptions = [];
        $saveCurrent = [
            'value' => $saveAction,
            'label' => $this->getSaveActionButtonName($saveAction)
        ];

        switch ($saveAction) {
            case 'save_and_edit':
                $saveOptions['save_and_back'] = $this->getSaveActionButtonName('save_and_back');
                $saveOptions['save_and_new'] = $this->getSaveActionButtonName('save_and_new');
                break;
            case 'save_and_new':
                $saveOptions['save_and_back'] = $this->getSaveActionButtonName('save_and_back');
                $saveOptions['save_and_edit'] = $this->getSaveActionButtonName('save_and_edit');
                break;
            case 'save_and_black':
            default:
                $saveOptions['save_and_edit'] = $this->getSaveActionButtonName('save_and_edit');
                $saveOptions['save_and_new'] = $this->getSaveActionButtonName('save_and_new');
                break;
        }

        return [
            'active' => $saveCurrent,
            'options' => $saveOptions
        ];
    }

    public function setSaveAction( $forceSaveAction = null )
    {
        if( $forceSaveAction ){
            $saveAction = $forceSaveAction;
        } else {
            $saveAction = \Request::input('save_action', config('backback.crud.default_save_action', 'save_and_back'));
        }

        if( session('save_action', 'save_and_back') !== $saveAction ){
            \Alert::info(trans('backpack::crud.save_action_changed_notification'))->flash();
        }

        session(['save_action' => $saveAction]);
    }

    public function performSaveAction( $itemId = null )
    {
        $saveAction = \Request::input('save_action', config('backback.crud.default_save_action', 'save_and_back'));
        $itemId = $itemId ? $itemId : \Request::input('id');

        switch ($saveAction) {
            case 'save_and_new':
                $redirectUrl = $this->crud->route.'/create';
                break;
            case 'save_and_edit':
                $redirectUrl = $this->crud->route.'/'.$itemId.'/edit';
                break;
            case 'save_and_back':
            default:
                $redirectUrl = $this->crud->route;
                break;
        }

        return \Redirect::to($redirectUrl);
    }

    private function getSaveActionButtonName( $actionValue = 'save_and_black' )
    {
        switch ($actionValue) {
            case 'save_and_edit':
                return trans('backpack::crud.save_action_save_and_edit');
                break;
            case 'save_and_new':
                return trans('backpack::crud.save_action_save_and_new');
                break;
            case 'save_and_back':
            default:
                return trans('backpack::crud.save_action_save_and_back');
                break;
        }
    }
}
