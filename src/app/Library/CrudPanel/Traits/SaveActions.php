<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

trait SaveActions
{
    public $availableSaveActions = [];

    /**
     * Get the developer's preference on what save action is the default one
     * for the current operation.
     *
     * @return string
     */
    public function getSaveActionDefaultForCurrentOperation()
    {
        return config('backpack.crud.operations.'.$this->getCurrentOperation().'.defaultSaveAction', 'save_and_back');
    }

    /**
     * Get the save action with full fallback until default.
     *
     * @return string
     */
    public function getFallBackSaveAction()
    {
        $higherAction = $this->getSaveActionByOrder(1);

        if (empty($higherAction)) {
            if ($this->hasOperationSetting('defaultSaveAction')) {
                return $this->getOperationSetting('defaultSaveAction');
            }

            return $this->getSaveActionDefaultForCurrentOperation();
        }

        return key($higherAction);
    }

    /**
     * Gets the save action in the desired order.
     *
     * @param int $order
     * @return array
     */
    public function getSaveActionByOrder($order)
    {
        return array_filter($this->availableSaveActions, function ($arr) use ($order) {
            return $arr['order'] == $order;
        });
    }

    /**
     * Allow the developer to register save actions.
     *
     * @param array $saveAction
     * @return void
     */
    public function registerSaveActions($saveActions)
    {
        if (is_array($saveActions)) {
            if (count($saveActions) != count($saveActions, COUNT_RECURSIVE)) {
                foreach ($saveActions as $saveAction) {
                    $this->registerSaveAction($saveAction);
                }
            } else {
                $this->registerSaveAction($saveActions);
            }
        }
    }

    /**
     * Register save actions in the crud.
     *
     * @param array $saveAction
     * @return void
     */
    public function registerSaveAction($saveAction)
    {
        if (is_array($saveAction)) {
            //check for some mandatory fields
            $saveAction['name'] ?? abort(500, 'Please define save action name.');
            $saveAction['redirect'] = $saveAction['redirect'] ?? function ($crud, $request, $itemId) {
                return $request->has('http_referrer') ? $request->get('http_referrer') : $crud->route;
            };
            $saveAction['permissions'] = $saveAction['permissions'] ?? true;
            $saveAction['button_text'] = $saveAction['button_text'] ?? $saveAction['name'];
            $saveAction['order'] = isset($saveAction['order']) ? $this->reorderSaveActions($saveAction['order']) : count($this->availableSaveActions) + 1;
            $this->availableSaveActions[$saveAction['name']] = [];
            $this->availableSaveActions[$saveAction['name']] = $saveAction;
        }
    }

    /**
     * Replaces setting order or forces some default.
     *
     * @param int $wantedOrder
     * @return int
     */
    public function reorderSaveActions($wantedOrder)
    {
        if (! empty($this->availableSaveActions)) {
            $lastOrder = max(array_column($this->availableSaveActions, 'order'));
            foreach ($this->availableSaveActions as &$sv) {
                if ($wantedOrder == $sv['order']) {
                    if (empty(array_filter($this->availableSaveActions, function ($arr) {
                        $arr['order'] == 1;
                    }))) {
                        $sv['order'] = 1;
                    } else {
                        $sv['order'] = $lastOrder + 1;
                    }
                }
            }
        }

        return $wantedOrder;
    }

    /**
     * Replace the current save actions with the ones provided.
     *
     * @param array $saveActions
     * @return void
     */
    public function replaceSaveActions($saveActions)
    {
        if (is_array($saveActions)) {
            //we reset all save actions
            $this->availableSaveActions = [];

            if (count($saveActions) != count($saveActions, COUNT_RECURSIVE)) {

                //if developer provided more than one save action.
                foreach ($saveActions as $sv) {
                    $this->registerSaveAction($sv);
                }
            } else {
                $this->registerSaveAction($saveActions);
            }
        }
    }

    /**
     * Allow the developer to unset save actions.
     *
     * @param string $saveAction
     * @return void
     */
    public function forgetSaveAction($saveAction)
    {
        foreach ($this->availableSaveActions as $key => $sv) {
            if ($sv['name'] == $saveAction) {
                unset($this->availableSaveActions[$key]);
            }
        }
    }

    /**
     * Checks if a save action exists.
     *
     * @param string $saveAction
     * @return bool
     */
    public function saveActionExists($saveAction)
    {
        return isset($this->availableSaveActions[$saveAction]);
    }

    /**
     * Apply the orders to save actions array.
     *
     * @param string $saveAction
     * @param int $order
     * @return void
     */
    public function applyOrderToSaveAction($saveAction, $order)
    {
        if ($this->saveActionExists($saveAction)) {
            $this->reorderSaveActions($order);

            $this->availableSaveActions[$saveAction]['order'] = $order;
        }
    }

    /**
     * Allows the developer to set save actions order.
     *
     * @param string|array $saveAction
     * @param int|null $order
     * @return void
     */
    public function orderSaveActions($saveAction, $order = null)
    {
        if (is_array($saveAction) && is_null($order)) {
            foreach ($saveAction as $sv => $order) {
                $this->applyOrderToSaveAction($sv, $order);
            }
        } else {
            $this->applyOrderToSaveAction($saveAction, $order);
        }
    }

    /**
     * Get save actions, with pre-selected action from stored session variable or config fallback.
     *
     * @return array
     */
    public function getSaveAction()
    {
        $saveAction = session($this->getCurrentOperation().'.saveAction', $this->getFallBackSaveAction());

        //run save actions permission callback
        foreach ($this->availableSaveActions as $actionName => $action) {
            $permission = $action['permissions'];
            if (is_callable($permission)) {
                $this->availableSaveActions[$actionName]['permissions'] = $permission($this);
            }
        }

        //get only passed permissions
        $saveOptions = array_filter($this->availableSaveActions, function ($saveOption) {
            return $saveOption['permissions'] == true;
        }, ARRAY_FILTER_USE_BOTH);

        //sort by order
        uasort($saveOptions, function ($a, $b) {
            return $a['order'] <=> $b['order'];
        });

        //get the current action
        if (isset($saveOptions[$saveAction])) {
            $currentAction = $saveOptions[$saveAction];
        } else {
            $currentAction = array_first($saveOptions);
        }

        $saveCurrent = [
            'value' => $currentAction['name'],
            'label' => $currentAction['button_text'],
        ];

        //we get the dropdown options
        $dropdownOptions = [];
        foreach ($saveOptions as $key => $option) {
            if ($option['name'] != $saveCurrent['value']) {
                $dropdownOptions[$option['name']] = $option['button_text'];
            }
        }

        return [
            'active'  => $saveCurrent,
            'options' => $dropdownOptions,
        ];
    }

    /**
     * Change the session variable that remembers what to do after the "Save" action.
     *
     * @param string|null $forceSaveAction
     *
     * @return void
     */
    public function setSaveAction($forceSaveAction = null)
    {
        $saveAction = $forceSaveAction ?:
            \Request::input('save_action', $this->getFallBackSaveAction());

        $showBubble = $this->getOperationSetting('showSaveActionChange') ?? config('backpack.crud.operations.'.$this->getCurrentOperation().'.showSaveActionChange') ?? true;

        if (
            $showBubble &&
            session($this->getCurrentOperation().'.saveAction', 'save_and_back') !== $saveAction
        ) {
            \Alert::info(trans('backpack::crud.save_action_changed_notification'))->flash();
        }

        session([$this->getCurrentOperation().'.saveAction' => $saveAction]);
    }

    /**
     * Redirect to the correct URL, depending on which save action has been selected.
     *
     * @param string $itemId
     *
     * @return \Illuminate\Http\Response
     */
    public function performSaveAction($itemId = null)
    {
        $request = \Request::instance();
        $saveAction = $request->input('save_action', $this->getFallBackSaveAction());
        $itemId = $itemId ?: $request->input('id');

        if (isset($this->availableSaveActions[$saveAction])) {
            if (is_callable($this->availableSaveActions[$saveAction]['redirect'])) {
                $redirectUrl = $this->availableSaveActions[$saveAction]['redirect']($this, $request, $itemId);
            }
        }

        // if the request is AJAX, return a JSON response
        if ($this->request->ajax()) {
            return [
                'success'      => true,
                'data'         => $this->entry,
                'redirect_url' => $redirectUrl,
            ];
        }

        return \Redirect::to($redirectUrl);
    }

    /**
     * Returns the default Backpack save actions for cruds.
     *
     * @return array
     */
    public function setupBackpackDefaultSaveActions()
    {
        $defaultSaveActions = [
            [
                'name' => 'save_and_back',
                'permissions' => function ($crud) {
                    return $crud->hasAccess('list');
                },
                'redirect' => function ($crud, $request, $itemId = null) {
                    return $request->has('http_referrer') ? $request->get('http_referrer') : $crud->route;
                },
                'button_text' => trans('backpack::crud.save_action_save_and_back'),
                'order' => 2,
            ],
            [
                'name' => 'save_and_edit',
                'permissions' => function ($crud) {
                    return $crud->hasAccess('update');
                },
                'redirect' => function ($crud, $request, $itemId = null) {
                    $itemId = $itemId ?: $request->input('id');
                    $redirectUrl = $this->route.'/'.$itemId.'/edit';
                    if ($request->has('locale')) {
                        $redirectUrl .= '?locale='.$request->input('locale');
                    }
                    if ($request->has('current_tab')) {
                        $redirectUrl = $redirectUrl.'#'.$request->get('current_tab');
                    }

                    return $redirectUrl;
                },
                'button_text' => trans('backpack::crud.save_action_save_and_edit'),
                'order' => 3,
            ],
            [
                'name' => 'save_and_new',
                'permissions' => function ($crud) {
                    return $crud->hasAccess('create');
                },
                'redirect' => function ($crud, $request, $itemId = null) {
                    return $this->route.'/create';
                },
                'button_text' => trans('backpack::crud.save_action_save_and_new'),
                'order' => 4,
            ],
        ];

        foreach ($defaultSaveActions as $sv) {
            $this->registerSaveAction($sv);
        }
    }

    /**
     * Get first save action name in the list.
     *
     * @return string
     */
    public function getFirstSaveActionName()
    {
        return array_key_first($this->availableSaveActions);
    }

    /**
     * Get first save action config.
     *
     * @return void
     */
    public function getFirstSaveAction()
    {
        return array_first($this->availableSaveActions);
    }
}
