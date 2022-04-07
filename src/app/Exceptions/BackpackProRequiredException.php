<?php

namespace Backpack\CRUD\app\Exceptions;

class BackpackProRequiredException extends \Exception
{
    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function render($request)
    {
        $this->message = $this->message.' is a Backpack PRO feature. Please purchase and install <a href="https://backpackforlaravel.com/pricing">Backpack\PRO</a>.';
        return response(view('errors.500', ['exception' => $this]), 500);
    }
}
