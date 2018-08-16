<?php
namespace Phinance;

use NetDriver\Http\HttpRequest;

interface HttpRequestCreateListenerInterface
{
    /**
     * Callbacks when http request was created.
     *
     * @param HttpRequest $request
     */
    public function onHttpRequestCreated(HttpRequest $request);
}