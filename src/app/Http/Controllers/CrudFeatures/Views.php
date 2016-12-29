<?php

namespace Backpack\CRUD\app\Http\Controllers\CrudFeatures;

trait Views
{
    protected $detailsRowView = 'crud::details_row';
    protected $revisionsView = 'crud::revisions';
    protected $reorderView = 'crud::reorder';
    protected $listView = 'crud::list';

    protected $createView = 'crud::create';
    protected $editView = 'crud::edit';
    protected $showView = 'crud::show';

    /**
     * Sets the details row template
     * @param string $view name of the template file
     * @return string $view name of the template file
     */
    public function setDetailsRowView($view)
    {
        $this->detailsRowView = $view;
        return $this->detailsRowView;
    }

    /**
     * Gets the details row template
     * @return string name of the template file
     */
    public function getDetailsRowView()
    {
        return $this->detailsRowView;
    }

    /**
     * Sets the revision template
     * @param string $view name of the template file
     * @return string $view name of the template file
     */
    public function setRevisionsView($view)
    {
        $this->revisionsView = $view;
        return $this->revisionsView;
    }

    /**
     * Gets the revisions template
     * @return string name of the template file
     */
    public function getRevisionsView()
    {
        return $this->revisionsView;
    }

    /**
     * Sets the reorder template
     * @param string $view name of the template file
     * @return string $view name of the template file
     */
    public function setReorderView($view)
    {
        $this->reorderView = $view;
        return $this->reorderView;
    }

    /**
     * Gets the reorder template
     * @return string name of the template file
     */
    public function getReorderView()
    {
        return $this->reorderView;
    }

    /**
     * Sets the list template
     * @param string $view name of the template file
     * @return string $view name of the template file
     */
    public function setListView($view)
    {
        $this->listView = $view;
        return $this->listView;
    }

    /**
     * Gets the list template
     * @return string name of the template file
     */
    public function getListView()
    {
        return $this->listView;
    }

    /**
     * Sets the list template
     * @param string $view name of the template file
     * @return string $view name of the template file
     */
    public function setCreateView($view)
    {
        $this->createView = $view;
        return $this->createView;
    }

    /**
     * Gets the create template
     * @return string name of the template file
     */
    public function getCreateView()
    {
        return $this->createView;
    }

    /**
     * Sets the edit template
     * @param string $view name of the template file
     * @return string $view name of the template file
     */
    public function setEditView($view)
    {
        $this->editView = $view;
        return $this->editView;
    }

    /**
     * Gets the edit template
     * @return string name of the template file
     */
    public function getEditView()
    {
        return $this->editView;
    }

    /**
     * Sets the show template
     * @param string $view name of the template file
     * @return string $view name of the template file
     */
    public function setShowView($view)
    {
        $this->showView = $view;
        return $this->showView;
    }

    /**
     * Gets the show template
     * @return string name of the template file
     */
    public function getShowView()
    {
        return $this->showView;
    }
}
