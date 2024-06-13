<?php

namespace App\Http\Controllers;

use App\Helpers\ApiFormatter;
use App\Models\Stuff;
use Illuminate\Http\Request;

class StuffController extends Controller
{
    public function index(Request $request)
    {
        try {
            $sortBy = $request->query('sort_by', 'updated_at');
    
            $data = Stuff::with('stuffStock', 'inboundStuffs', 'lendings')
                         ->orderBy($sortBy, 'desc')->get();
    
            return ApiFormatter::sendResponse(200, 'Berhasil mendapatkan data', $data);
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'Terjadi kesalahan', $err->getMessage());
        }
    }
    

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            // validasi
            $this->validate($request, [
                'name' => 'required',
                'category' => 'required',
            ]);

            // proses tambah data
            // NamaModel::create(['column' => $request->name_or_key, ])
            $data = Stuff::create([
                'name' => $request->name,
                'category' => $request->category,
            ]);
            
            return ApiFormatter::sendResponse(200, 'success', $data);
        } catch (\Exception $err){
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $data = Stuff::where('id', $id)->with('stuffStock', 'inboundStuffs', 'lendings')->first();

            if (is_null($data)) {
                return ApiFormatter::sendResponse(400, 'bad request', 'Data not found!');
            } else {
                return ApiFormatter::sendResponse(200, 'success', $data);
            } 
        } catch (\Exception $err) {
            return ApiFormatter::senResponse(400, 'bad request', $err->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $this->validate($request, [
                'name' => 'required',
                'category' => 'required',
            ]);

            $checkProses = Stuff::where('id', $id)->update([
                'name' => $request->name,
                'category' => $request->category
            ]);

            if ($checkProses) {
                $data = Stuff::find($id);
                return ApiFormatter::sendResponse(200, 'success', $data);
            } else {
                return ApiFormatter::sendResponse(400, 'bad request', 'Gagal mengubah data!');
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $getStuff = Stuff::where('id' ,$id)->delete();
            
            return ApiFormatter::sendResponse(200, 'success', 'Data stuff berhasil di hapus!');
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request' , $err->getMessage());
        }
    }

    public function trash()
    {
        try {
            // onlyTrashed: mencari data yang deletes_at nya BUKAN null
            $data = Stuff::onlyTrashed()->get();

            return ApiFormatter::sendResponse(200, 'success', $data);
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function restore($id)
    {
        try {
            $checkProses = Stuff::onlyTrashed()->where('id', $id)->restore();

            if ($checkProses) {
                $data = Stuff::find($id);
                return ApiFormatter::sendResponse(200, 'success', $data);
            } else {
                return ApiFormatter::sendResponse(400, 'bad request', 'Gagal mengembalikan data!');
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function deletePermanent($id) 
    {
        try {
            $checkProses = Stuff::onlyTrashed()->where('id', $id)->forceDelete();

            if ($checkProses) {
                return ApiFormatter::sendResponse(200, 'success', 'Berhasil menghapus permanen data stuff!');
            } else {
                return ApiFormatter::sendResponse(400, 'bad request', 'Gagal menghapus permanen data stuff!');
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }


    public function _construct()
    {
        $this->middleware('auth:api');
    }
}
