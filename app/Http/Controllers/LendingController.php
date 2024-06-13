<?php

namespace App\Http\Controllers;

use App\Models\InboundStuff;
use App\Helpers\ApiFormatter;
use App\Models\Stuff;
use App\Models\StuffStock;
use App\Models\Lending;
use App\Models\Restoration;
use Illuminate\Http\Request;

class LendingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $sortBy = $request->query('sort_by', 'updated_at');

            $getLending = Lending::with('stuff', 'user', 'restoration')
                                ->orderBy($sortBy, 'desc')->get();

            return ApiFormatter::sendResponse(200, 'Successfully Get All Lending Data', $getLending);
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, $err->getMessage());
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
        try{
            $this->validate($request, [
                'stuff_id' => 'required',
                'date_time' => 'required',
                'name' => 'required',
                'user_id' => 'required',
                'notes' => 'required',
                'total_stuff' => 'required',
            ]);

            $createLending = Lending::create([
                'stuff_id' => $request->stuff_id,
                'date_time' => $request->date_time,
                'name' => $request->name,
                'user_id' => $request->user_id,
                'notes' => $request->notes,
                'total_stuff' => $request->total_stuff,
            ]);

            $getStuffStock = StuffStock::where('stuff_id', $request->stuff_id)->first();


                return ApiFormatter::sendResponse(200, 'Successfully Create A Lending Data', $createLending);
            } catch (\Exception $err) {
                return ApiFormatter::sendResponse(400, $err->getMessage());}
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
            $data = Lending::where('id', $id)->with('user', 'restoration', 'restoration.user', 'stuff', 'stuff.stuffStock')->first();

            return ApiFormatter::sendResponse(200, 'success', $data);
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, $err->getMessage());
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
        $getLending = Lending::find($id);

        if ($getLending) {
            $this->validate($request, [
                'stuff_id' => 'required',
                'date_time' => 'required',
                'name' => 'required',
                'user_id' => 'required',
                'notes' => 'required',
                'total_stuff' => 'required',
            ]);

            $getStuffStock = StuffStock::where('stuff_id', $request->stuff_id)->first(); // get stock berdasarkan request stuff_id

            $getCurrentStock = StuffStock::where('stuff_id', $getLending['stuff_id'])->first(); // get stock berdasarkan id lending

            if ($request->stuff_id == $getCurrentStock['stuff_id']) {
                $updateStock = $getCurrentStock->update([
                    'total_available' => $getCurrentStock['total_available'] + $getLending['total_stuff'] - $request->total_stuff,
                ]);
            } else {
                $updateStock = $getCurrentStock->update([
                    'total_available' => $getCurrentStock['total_available'] + $getLending['total_stuff'],
                ]); 
                
                $updateStock = $getStuffStock->update([
                    'total_available' => $getStuffStock['total_available'] - $request['total_stuff'],
                ]); 
            }

            $updateLending = $getLending->update([
                'stuff_id' => $request->stuff_id,
                'date_time' => $request->date_time,
                'name' => $request->name,
                'user_id' => $request->user_id,
                'notes' => $request->notes,
                'total_stuff' => $request->total_stuff,
            ]);

            $getUpdateLending = Lending::where('id', $id)->with('stuff', 'user', 'restoration')->first();

            return ApiFormatter::sendResponse(200, 'Successfully Update A Lending Data', $getUpdateLending);
        } else {
            return ApiFormatter::sendResponse(404, 'Lending Not Found');
        }
    } catch (\Exception $err) {
        return ApiFormatter::sendResponse(400, $err->getMessage());
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
            $lending = Lending::find($id);
        
            if ($lending->restorations) {
                return response()->json(['error' => 'Peminjaman sudah dikembalikan, tidak bisa dibatalkan'], 400);
            }
        
            $lending->delete();
        
            $stuffStock = StuffStock::where('stuff_id', $lending->stuff_id)->first();
        
            if ($stuffStock) {
                $stuffStock->total_available += $lending->total_stuff;
                $stuffStock->save();
            } 
    
            return ApiFormatter::sendResponse(200, 'success', 'Data Lending berhasil dihapus ');
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, false, $err->getMessage());
        }    
    }


    public function trash()
    {
        try {

            $data = Lending::onlyTrashed()->get();
          
            return ApiFormatter::sendResponse(200, 'success', $data);
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, false, $err->getMessage());
        }
    }

    public function restore($id)
    {
        try {

            $getLending = Lending::onlyTrashed()->where('id', $id);

            if (!$getLending) {
                return ResponseFormatter::sendResponse(404, false, 'Restored Data Lending Doesnt Exists');
            } else {
                $restoreLending = $getLending->restore();

                if ($restoreLending) {
                    $getRestore = Lending::find($id);
                    $addStock = StuffStock::where('stuff_id', $getRestore['stuff_id'])->first();
                    $updateStock = $addStock->update([
                        'total_available' => $addStock['total_available'] - $getRestore['total_stuff'],
                    ]);

                    return ApiFormatter::sendResponse(200, true, 'Successfully Restore A Deleted Lending Data', $getRestore);
                }
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, false, $err->getMessage());
        }
    }

    public function deletePermanent($id)
    {
        try {

            $getLending = Lending::onlyTrashed()->where('id', $id);

            if (!$getLending) {
                return ApiFormatter::sendResponse(404, false, 'Data Lending for Permanent Delete Doesnt Exists');
            } else {
                $forceStuff = $getLending->forceDelete();

                if ($forceStuff) {
                    return ApiFormatter::sendResponse(200, true, 'Successfully Permanent Delete A Lending Data');
                }
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, false, $err->getMessage());
        }
    }

    public function _construct()
    {
        $this->middleware('auth:api');
    }
    
}
