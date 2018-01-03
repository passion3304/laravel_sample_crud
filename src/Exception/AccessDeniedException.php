<?php

namespace Backpack\CRUD\Exception;

class AccessDeniedException extends \UnexpectedValueException
{
    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function render($request)
    {
        return response(view('errors.403'), 403);
    }
}
