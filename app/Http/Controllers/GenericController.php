<?php
namespace App\Http\Controllers;

use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

class GenericController extends Controller
{
    //use AuthorizesRequests, ValidatesRequests;
    protected $model;

    public function __construct($model = null)
    {
        $this->model = $model;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getAll(Request $request)
    {
        //return response()->json($model);
        $model = $this->model;
        if ($model == null) {
            $modelName = $request->input('model');
            $modelClass = '\\App\\Models\\' . $modelName;
            $model = new $modelClass;
        }
        if ($model == null) {
            throw new Exception("Model not found");
        }

        $query = $model->query();

        // Obtener los modelos relacionados a cargar
        if ($request->has('with')) {
            $with = $request->with; //$request->get('with', []);

            if (!empty($with)) {

                foreach ($with as $modelW) {
                    if (method_exists($model, $modelW)) {
                        $query = $query->with($modelW);
                        dump($query);exit;
                    } else {
                        throw new Exception("Relation not exist: " . $modelW);
                    }

                }
            }
        }

        // Filters
        if ($request->has('filters')) {
            $filters = $request->filters; // json_decode($request->filters, true);
            $query = $this->applyFilters($model, $filters);
        }

        // Pagination
        $perPage = $request->has('per_page') ? intval($request->per_page) : 10;
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

    /**************filter generico con N niveles de relacion**/
    protected function applyFilters($model, $filters)
    {
        $query = $model->query();

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
