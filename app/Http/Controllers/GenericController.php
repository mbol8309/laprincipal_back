<?php

namespace App\Http\Controllers;

use App\Models\Libro;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use ReflectionClass;

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
    public function getAll(Request $request, $internCall = false)
    {
        //return response()->json($model);
        $model = $this->GetModelFromRequest($request);
        if ($internCall == false) {
            $res = $this->ExecControllerFunction($this->GetModelNameFromRequest($request), 'getAll', $request);
        }
        $query = $model->query();

        // Obtener los modelos relacionados a cargar
        if ($request->has('with')) {
            $with = $request->with;
            if (is_string($with)) $with = [$with];  //to allow passing string
            if (!empty($with)) {
                foreach ($with as $modelW) {
                    if (method_exists($model, $modelW)) {
                        $query = $query->with($modelW);
                    } else throw new Exception("Relation not exist: " . $modelW);
                }
            }
        }

        // Filters
        if ($request->has('filters')) {
            $filters = $request->filters; // json_decode($request->filters, true);
            $query = $this->applyFilters($model, $filters, $query);
        }

        // Sort
        if ($request->has('sort_by')) {
            $sort_by = $request->input('sort_by');
            $sort_by = explode(' ', $sort_by);
            $orden = strtolower($sort_by[1]) == 'asc' ? 'asc' : 'desc';
            $campo = strtolower($sort_by[0]);
            if (!Schema::hasColumn($model->getTable(), $campo)) {
                throw new Exception("No existe el campo $campo en la tabla " . $model->getTable());
            }
            $query->orderBy($campo, $orden);
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
            'current_page' => $page
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
        if ($id == null) {
            $this->ThrowGenericEx("Id not found");
        }

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
        if ($data == null) {
            $this->ThrowGenericEx("Entity not found");
        }

        return response()->json([
            'data' => $data
        ]);
    }

    /**
     * Dynamic get element by id.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getByIds(Request $request)
    {
        //return response()->json($model);
        $model = $this->GetModelFromRequest($request);

        $ids = [];
        if ($request->has('ids')) {
            $ids = $request->ids;
        }
        if ($ids == null) {
            $this->ThrowGenericEx("Id not found");
        }

        $data = null;
        // Obtener los modelos relacionados a cargar
        if ($request->has('with')) {
            $with = $request->with;
            if (!empty($with)) {
                $data = $model->with($with)->whereIn('id', $ids)->get();
            }
        }
        if ($data == null) {
            $data = $model->whereIn('id', $ids)->get();
        }
        if ($data == null) {
            $this->ThrowGenericEx("Entity not found");
        }

        return response()->json([
            'data' => $data
        ]);
    }

    public function insert(Request $request)
    {
        // Obtener el nombre de la clase del modelo a partir del nombre del controlador
        //$modelClass = 'App\\Models\\' . str_replace('Controller', '', class_basename(get_class($this)));
        $model = $this->GetModelFromRequest($request);

        //obtener data para insertar
        $data = [];
        if ($request->has('data')) {
            if (!empty($request->data)) {
                $data = $request->data;
            }
        }

        // Obtener las reglas de validación del modelo
        $rules = $this->getValidationRules($model);

        if ($rules != null) {
            // Validar los datos del formulario
            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
        }

        if ($data != null) {
            // Asignar los datos al modelo
            $model->fill($data);
            //dump($model);exit;
            $model->save();
        }

        return response()->json([
            'data' => $model,
            'success' => true
        ]);
        //return response()->json(['message' => 'Registro creado con éxito', 'data' => $model], 201);
    }

    public function delete(Request $request)
    {
        try {
            $model = $this->GetModelFromRequest($request);
            $id = $request->has('id') ? $request->id : null;
            if ($id == null) {
                $this->ThrowGenericEx("Id is not valid");
            }
            $data = $model->findOrFail($id);
            $data->delete();
            return response()->json(['message' => 'Resource deleted']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Resource not found'], 404);
        }
    }

    public function updateById(Request $request)
    {
        try {
            $model = $this->GetModelFromRequest($request);
            $id = $request->has('id') ? $request->id : null;
            if ($id == null) {
                $this->ThrowGenericEx("Id is not valid");
            }

            $entity = $model->findOrFail($id);

            $data = null;
            if ($request->has('data')) {
                if (!empty($request->data)) {
                    $data = $request->data;
                }
            }

            $rules = $this->getValidationRules($model);

            if ($rules != null) {
                // Validar los datos del formulario
                $validator = Validator::make($data, $rules);

                if ($validator->fails()) {
                    return response()->json(['errors' => $validator->errors()], 422);
                }
            }

            if ($data != null) {
                $entity->update($data);
                foreach ($data as $key => $value) {
                    // Check if the key corresponds to a many-to-many relation
                    if (is_array($value) && $entity->$key() instanceof BelongsToMany) {
                        $entity->$key()->sync($value);
                    }

                    //hasMany
                    if (is_array($value) &&  $entity->$key() instanceof HasMany) {
                        $relatedModel = $entity->$key()->getRelated();
                        $relatedKey = $relatedModel->getKeyName();

                        // Get the IDs of the related models in the request
                        $relatedIds = $value;

                        // Get the IDs of the related models currently associated with the entity
                        $currentRelatedIds = $entity->$key->pluck($relatedKey)->toArray();

                        // Determine which related models should be disassociated
                        $disassociatedIds = array_diff($currentRelatedIds, $relatedIds);

                        // Determine which related models should be associated
                        $associatedIds = array_diff($relatedIds, $currentRelatedIds);

                        // Disassociate the old related models that are not in the request
                        $foreignKey = $entity->$key()->getForeignKeyName();
                        $localKey = $entity->$key()->getLocalKeyName();
                        if (!empty($disassociatedIds)) {
                            $relatedModel->whereIn($relatedKey, $disassociatedIds)->update([
                                $entity->getForeignKey() => null
                            ]);
                        }

                        // Associate the new related models that are in the request
                        if (!empty($associatedIds)) {
                            $relatedModels = $relatedModel->whereIn($relatedKey, $associatedIds)->get();

                            $relatedModel->whereIn($relatedKey, $associatedIds)->update([
                                $entity->getForeignKey() => $entity->$localKey
                            ]);
                        }
                    }
                }
            }

            return response()->json(['data' => $entity]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Resource not found'], 404);
        }
    }

    /*PRIVATE FUNCTIONS */
    /***************filter generico con N niveles de relacion***/
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

    private function GetModelNameFromRequest($request)
    {
        $modelName = null;
        if ($request->has('model')) {
            $modelName = $request->input('model');
        }
        return $modelName;
    }

    private function GetModelFromRequest($request)
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

    private function ThrowGenericEx($msg)
    {
        throw new Exception($msg);
    }

    private function getValidationRules($modelName)
    {
        $rules = null;

        if (Schema::hasTable('sys_validation_rule')) {
            $rules = [];
            $reflectionClass = new ReflectionClass($modelName);
            // Obtener las reglas de validación de la tabla 'validation_rules'
            $short_snake_case_name = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $reflectionClass->getShortName()));
            $validationRules = DB::table('sys_validation_rule')
                ->where('model_name', '=', $short_snake_case_name)
                ->get();

            /*dump($validationRules);
            exit;*/

            // Agrupar las reglas por campo
            $groupedRules = $validationRules->groupBy('field_name');

            // Construir el array de reglas
            foreach ($groupedRules as $fieldName => $rulesForField) {
                $fieldRules = [];
                foreach ($rulesForField as $rule) {
                    if ($rule->rule_parameters) {
                        $fieldRules[$rule->rule_name] = explode(',', $rule->rule_parameters);
                    } else {
                        $fieldRules[] = $rule->rule_name;
                    }
                }
                $rules[$fieldName] = $fieldRules;
            }
        }

        return $rules;
    }

    private function ExecControllerFunction($model, $functionName, $request)
    {
        $controllerName = ucfirst($model) . 'Controller';
        $fullControllerName = 'App\\Http\\Controllers\\' . $controllerName;
        if (class_exists($fullControllerName)) {
            $obj = new $fullControllerName();
            if (method_exists($obj, $functionName)) {
                $result = call_user_func_array(array($obj, $functionName), ['request' => $request]);
                return 1;
            } else {
                // El método no existe en el controlador
                return -2;
            }
        } else {
            // El controlador no existe
            return -1;
        }
    }


    /*END PRIVATE FUNCTIONS */
}
