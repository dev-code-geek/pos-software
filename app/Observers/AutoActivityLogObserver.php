<?php

namespace App\Observers;

use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Model;

class AutoActivityLogObserver
{
    protected function moduleName(Model $model): string
    {
        return \Illuminate\Support\Str::snake(class_basename($model));
    }

    public function created(Model $model)
    {
        ActivityLogger::logModelChange('created', $model, $this->moduleName($model));
    }

    public function updated(Model $model)
    {
        ActivityLogger::logModelChange('updated', $model, $this->moduleName($model), $model->getOriginal());
    }

    public function deleted(Model $model)
    {
        ActivityLogger::logModelChange('deleted', $model, $this->moduleName($model));
    }
}