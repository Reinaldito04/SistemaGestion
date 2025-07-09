<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use App\Traits\ControllerTrait;

class FileController extends Controller
{
    use ControllerTrait;
    protected $model;

    public function __construct()
    {
        $this->model = new File;
    }

    public function show($id)
    {
          $query = $this->model->query();
            try {
                $data = $this->retrieveById($query, $id);
               $image = base64_decode($data->file_base64);
    
            return response($image)->header('Content-Type', $data->mime_type);
            } catch (\InvalidArgumentException $e) {
                return response()->json(['message' => $e->getMessage()], 400);
            } catch (\Exception $e) {
                return response()->json(['message' => $e->getMessage()], 404);
            }
    }

       

      public function index(Request $request)
    {
       $searchableColumns = ['id', 'name', 'file_extension', 'created_at', 'updated_at'];
       $query = $this->model->select($searchableColumns);

        

        $query = $this->find($request, $query, $searchableColumns);
        $results = $this->paginate($request, $query, $searchableColumns);
        return response()->json($results);
    }


}
