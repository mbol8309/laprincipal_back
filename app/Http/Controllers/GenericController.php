<?php
namespace App\Http\Controllers;

use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

class GenericController extends Controller
{
    //use AuthorizesRequests, ValidatesRequests;

    public function __construct()
    {
    }

    /**
     * Dynamic get all elements in model.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getAll(Request $request)
    {
        //return response()->json($model);
        $model = $this->GetModelFromRequest($request);

        $query = $model->query();

        // Obtener los modelos relacionados a cargar
        if ($request->has('with')) {
            $with = $request->with;
            if (!empty($with)) {
                foreach ($with as $modelW) {
                    if (method_exists($model, $modelW)) {
                        $query = $query->with($modelW);
                    } else {
                        throw new Exception("Relation not exist: " . $modelW);
                    }

                }
            }
        }

        // Filters
        if ($request->has('filters')) {
            $filters = $request->filters; // json_decode($request->filters, true);
            $query = $this->applyFilters($model, $filters, $query);
        }

        // Pagination
        $perPage = $request->has('per_page') ? intval($request->per_page) : 2000;
        $page = $request->has('page') ? intval($request->page) : 1;
        $skip = ($page - 1) * $perPage;
        $total = $query->count();
        $data = $query->skip($skip)->take($perPage)->get();

        return response()->json([
            'data' => $data,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
        ]);
    }

    /**
     * Dynamic get element by id.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getById(Request $request)
    {
        //return response()->json($model);
        $model = $this->GetModelFromRequest($request);

        $id = null;
        if ($request->has('id')) {
            $id = $request->id;
        }
        if ($id == null) {$this->ThrowGenericEx("Id not found");}

        $data = null;
        // Obtener los modelos relacionados a cargar
        if ($request->has('with')) {
            $with = $request->with;
            if (!empty($with)) {
                $data = $model->with($with)->findOrFail($id);
            }
        }
        if ($data == null) {
            $data = $model->findOrFail($id);
        }
        if ($data == null) {$this->ThrowGenericEx("Entity not found");}

        return response()->json([
            'data' => $data,
        ]);
    }

    /*PRIVATE FUNCTIONS */
    /**************filter generico con N niveles de relacion**/
    private function applyFilters($model, $filters, $query)
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

    private function GetModelFromRequest($request)
    {
        $model = null;
        if ($request->has('model')) {
            $modelName = $request->input('model');
            $modelClass = '\\App\\Models\\' . $modelName;
            $model = new $modelClass;
        }
        if ($model == null) {$this->ThrowGenericEx("Model not found");}
        return $model;
    }

    private function ThrowGenericEx($msg)
    {
        throw new Exception($msg);
    }

    /*END PRIVATE FUNCTIONS */

}
