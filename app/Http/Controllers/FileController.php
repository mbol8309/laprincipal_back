<?php

namespace App\Http\Controllers;

use App\Classes\Thumbnailer;
use App\Models\File;
use Illuminate\Support\Facades\Schema;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;
use Spatie\PdfToImage\Pdf;

class FileController extends Controller
{
    public static $thumnails_converters = [
        'createImageThumbnail' => [
            'image/jpeg',
            'image/png'
        ],
        'createPdfThumbnail' => [
            'application/pdf'
        ],
        'createTextThumbnail' => [
            'text/plain',
            'application/xml',
            'text/xml'
        ]
    ];

    public function getAll(Request $request)
    {
        $model = $this->GetModelFromRequest($request);

        $id = null;
        if ($request->has('id')) {
            $id = $request->id;
        }

        $data = null;
        $data = $model->findOrFail($id);
        if ($data == null) {
            $this->ThrowGenericEx("Entity not found");
        }
        $query = $data->files();

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
        $data = $query->skip($skip)->take($perPage)->get()->toArray();
        $baseurl = config('app.url');
        // $data = array_map(function ($file) use ($baseurl) {
        //     $file['path'] =  $baseurl . '/' . $file['path'];
        //     $file['thumbnail_path'] =  $baseurl . '/' . $file['thumbnail_path'];
        //     return $file;
        // }, $data);

        return response()->json([
            'data' => $data,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page
        ]);
    }

    public function upload(Request $request)
    {
        // Validate the uploaded file
        $validatedData = $request->validate([
            'file' => 'required|file',
            'function' => 'string'
        ]);

        // $entity = $this->GetModelFromRequest($request);
        // $entityId = $validatedData['id'];

        // Save the file to the storage disk
        $path = $validatedData['file']->store('files', 'public');
        $fuction = isset($validatedData['function']) ? $validatedData['function'] : 'image';
        $file = $validatedData['file'];

        $type = $validatedData['file']->getClientMimeType();

        $thumbnail = null;
        foreach (self::$thumnails_converters as $converter => $formats) {
            if (in_array($type, $formats)) {
                //is an image
                $thumbnail = call_user_func([Thumbnailer::class, $converter], $file);
            }
        }

        // $entityType = 'App\\Models\\' . $validatedData['model'];


        // Create a new File model and associate it with the given entity
        $file = File::create([
            'path' => asset(Storage::url($path)),
            'function' => $fuction,
            'thumbnail_path' => $thumbnail,
            'name' => $validatedData['file']->getClientOriginalName(),
            'type' => $validatedData['file']->getClientMimeType(),
            'size' => $validatedData['file']->getSize(),
        ]);
        // $entity = $entity::findOrFail($entityId);

        // $entity->attachFile($file);
        return $file;
    }



    public function destroy(Request $request, $id)
    {
        $file = File::findOrFail($id);

        // Delete the file from storage if it's no longer attached to any entities
        $file->deleteFile();
        $file->delete();

        return $file;
    }
}
