<?php

namespace App\Scopes\Search;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class Search implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $query, Model $model)
    {
        $value = request('q');

        if (!$value || !$model->searchables) {
            return;
        }

        foreach ($model->searchables as $key => $searchable) {
            $key === 0 ?
                $query->where($searchable, 'like', "%$value%") :
                $query->orWhere($searchable, 'like', "%$value%");
        }
    }
}
