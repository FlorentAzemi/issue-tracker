<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function loadRelationships($model, $request)
    {
        if ($request->has('with')) {
            $withArray = $request->input('with');

            if (!empty($withArray) && is_string($withArray)) {
                $withArray = explode(',', $withArray);
            }

            if (!empty($withArray) && is_array($withArray)) {
                try {
                    $model->load($withArray);
                } catch (\Exception $e) {
                    //
                }
            }
        }

        return $model;
    }
}
