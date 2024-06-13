<?php

namespace App\Http\Controllers;

use App\Helpers\ApiFormatter;
use App\Models\User; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $sortBy = $request->query('sort_by', 'updated_at');
        $sortOrder = $request->query('sort_order', 'desc');

        $data = User::orderBy($sortBy, $sortOrder)->get();

        return ApiFormatter::sendResponse(200, 'success', $data);
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
                'username' => 'required',
                // 'username' => 'required|unique:users',
                'email' => 'required|email|min:3',
                // 'email' => 'required|unique:users',
                'password' => 'required',
                'role' => 'required|in:admin,staff'
                // 'role' => 'required'
            ]);

            // proses tambah data
            // NamaModel::create(['column' => $request->name_or_key, ])
            $data = User::create([
                'username' => $request->username,
                'email' => $request->email,
                // 'password' => $request->password,
                'password' => hash::make($request->password),
                'role' => $request->role,
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
            $data = User::where('id', $id)->first();

            if (is_null($data)) {
                return ApiFormatter::sendResponse(400, 'bad request', 'Data not found!');
            } else {
                return ApiFormatter::sendResponse(200, 'success', $data);
            } 
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
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
    
                $getUser = User::find($id);
    
                if (!$getUser) {
                    return ResponseFormatter::sendResponse(404, false, 'Data User Not Found');
                } else {
                    $this->validate($request, [
                        'username' => 'required',
                        'email' => 'required',
                        'password' => 'required',
                        'role' => 'required'
                    ]);
    
                    $updateUser = $getUser->update([
                        'username' => $request->username,
                        'email' => $request->email,
                        'password' => Hash::make($request->password),
                        'role' => $request->role,
                    ]);
    
                    if($updateUser) {
                        return ApiFormatter::sendResponse(200, 'Succesfully Update A User Data', $getUser);
                    }
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
            $checkProses = User::where('id', $id)->delete();

            return ApiFormatter::sendResponse(200, 'success', 'Data stuff berhasil dihapus!');
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function trash()
    {
        try {
            // onlyTrashed: mencari data yang deletes_at nya BUKAN null
            $data = User::onlyTrashed()->get();

            return ApiFormatter::sendResponse(200, 'success', $data);
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function restore($id) 
    {
        try {

            $getUser = User::onlyTrashed()->where('id', $id)->restore();

            if (!$getUser) {
                return ApiFormatter::sendResponse(404, 'Data User Not Found');
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, $err->getMessage());
        }
    }

    public function deletePermanent($id) 
    {
        try {
            $checkProses = User::onlyTrashed()->where('id', $id)->forceDelete();

            return ApiFormatter::sendResponse(200, 'success', 'Berhasil menghapus permanen data stuff!');
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function login(Request $request)
    {
        try {
            $this->validate($request, [
                'email' => 'required',
                'password' => 'required',
            ]);

            $user = User::where('email', $request->email)->first(); // Mencari dan mendapatkan data user berdasarkan email yang digunakan untuk login 

            if (!$user) {
                // jika email tidak terdaftar maka akan dikembalikan response error
                return ApiFormatter::sendResponse(400, false, 'Login failed! User Doesnt Exists');
            } else {
                // jika email terdaftar, selanjutnya pencocokan password yang diinput dengan password di database dengan menggunakan Hash::check();
                $isValid = Hash::check($request->password, $user->password);

                if (!$isValid) {
                    // jika password tidak cocok maka akan dikembalikan dengan response error
                    return ApiFormatter::sendResponse(400, false, 'Login failed! User Doesnt Match');
                } else {
                    // jika password sesuai selanjutnya akan membuat token
                    // bin2hex digunakan untuk dapat mengonversi string karakter Ascil menjadi nilai heksadecimal
                    // random bytes menghasilkan byte pseudo acak yang aman secara scriptografis dengan panjang 40 karakter
                    $generateToken = bin2hex(random_bytes(40));
                    // token  inilah nanti yang digunakan pada proses authentication user yang login
                    
                    $user->update([
                        'token' => $generateToken
                        // update kolom token dengan value hasil dari generateToken di row user yang ingin login
                    ]);

                    return ApiFormatter::sendResponse(200, 'Login Successfully', $user);
                }
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, false, $err->getMessage());
        }
    }

    public function logout(Request $request)
    {
        try {
            $this->validate($request, [
            'email' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return ApiFormatter::sendResponse(400, 'Login failed! User Doesnt Exist');
        } else {
            if (!$user->token) {
                return ApiFormatter::sendResponse(400, 'Logout failed! User Doesnt Login Sciene');
            } else {
                $logout = $user->update(['token' => null]);

                if ($logout) {
                    return ApiFormatter::sendResponse(200, 'Logout Successfully');
                }
            } 
        }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(500, false, $err->getMessage());
        }
    }

    public function _construct()
    {
        $this->middleware('auth:api');
    }
}
