<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
    protected $generic;

    public function __construct()
    {
        $this->generic = new GenericController();
    }

    protected function BaseGetAll($request){
        $this->generic->getAll($request,true);
    }

    protected function GetModelFromRequest($request)
    {
        $model = null;
        if ($request->has('model')) {
            $modelName = $this->GetModelNameFromRequest($request);
            if ($modelName != null) {
                $modelName = str_replace(' ', '', ucwords(str_replace('_', ' ', $modelName))); // convert snake_case to PascalCase
                $modelClass = '\\App\\Models\\' . $modelName;
                if (class_exists($modelClass)) {
                    $model = new $modelClass;
                }
            }
        }
        if ($model == null) {
            $this->ThrowGenericEx("Model not found ($modelName)");
        }
        return $model;
    }

    public function GetModelNameFromRequest($request)
    {
        $modelName = null;
        if ($request->has('model')) {
            $modelName = $request->input('model');
        }
        return $modelName;
    }

    /*PRIVATE FUNCTIONS */
    /***************filter generico con N niveles de relacion***/
    protected function applyFilters($model, $filters, $query)
    {
        //$query = $model->query();

        foreach ($filters as $field => $value) {
            if (strpos($field, '.') !== false) {
                // Es una propiedad de una entidad relacionada
                $relationName = explode('.', $field)[0];
                $relatedFieldName = explode('.', $field)[1];

                // Obtenemos la relación
                $relation = $model->$relationName();

                if (!$relation) {
                    throw new Exception("Relation not found: $relationName");
                }

                // Obtenemos el modelo de la entidad relacionada
                $relatedModel = $relation->getRelated();

                // Obtenemos los filtros de la entidad relacionada
                $relatedFilters = [$relatedFieldName => $value];

                // Aplicamos los filtros a la entidad relacionada
                $query->whereHas($relationName, function ($q) use ($relatedModel, $relatedFilters) {
                    $this->applyFilters($relatedModel, $relatedFilters, $q);
                });
            } else {
                // Es un campo del modelo principal
                // Aplicamos el filtro al campo correspondiente
                if ($value === null) {
                    $query->whereNull($field);
                } elseif (is_array($value)) {
                    $query->whereIn($field, $value);
                } elseif (preg_match('/^(>=|<=|<>|!=|>|<)/', $value, $matches)) {
                    $operator = $matches[1];
                    $value = substr($value, strlen($operator));
                    $query->where($field, $operator, $value);
                } elseif (is_numeric($value)) {
                    $query->where($field, '=', $value);
                } elseif (strtotime($value) != false && Carbon::parse($value) !== false) {
                    $query->whereDate($field, '=', $value);
                } else {
                    $query->where($field, 'LIKE', '%' . $value . '%');
                }
            }
        }

        return $query;
    }

}
