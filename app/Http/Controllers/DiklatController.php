<?php

namespace App\Http\Controllers;
use App\Models\JenisDiklat;
use App\Models\Diklat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Storage;

class DiklatController extends Controller
{

    public function index()
    {
        $user = Auth::user();
        if ($user->level == "admin") {
            // Fetch the user's own work experiences
            $diklat = Diklat::where('is_deleted', '0')->get();
        } else {
            $diklat = Diklat::where('id_users', $user->id)
            ->where('is_deleted', '0')
            ->get();
        }    
        return view('diklat.index', [
            'diklat' => $diklat,
            'users' => User::all(),
            'jenisdiklat' => JenisDiklat::all()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_users' => 'required', 
            'id_jenis_diklat' => 'required', 
            'nama_diklat' => 'required', 
            'penyelenggara' => 'required', 
            'tanggal_diklat' => 'required', 
            'jp' => 'required', 
            'file_sertifikat' => 'required|mimes:pdf,doc,docx,png,jpg,jpeg', 
        ]);

        $diklat = new Diklat();
    
        $file = $request->file('file_sertifikat');
        $fileName = $file->getClientOriginalName();
        $file->storeAs('File Sertifikat', $fileName, 'public'); // Simpan file di dalam folder public/Pengalaman Kerja
    
        $diklat->id_users = $request->id_users;
        $diklat->id_jenis_diklat = $request->id_jenis_diklat;
        $diklat->nama_diklat = $request->nama_diklat;
        $diklat->penyelenggara = $request->penyelenggara;
        $diklat->tanggal_diklat = $request->tanggal_diklat;
        $diklat->jp = $request->jp;
        $diklat->file_sertifikat = $fileName; // Simpan nama file ke dalam kolom 'file_sertifikat'
    
        $diklat->save();
    
        return redirect()->back()->with('success', 'Data telah tersimpan.');
    }
    /**
     * Display the specified resource.
     */
    public function show(Diklat $diklat)
    {
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Diklat $id_diklat)
    {
        $diklat = Diklat::find($id_diklat);
        if (!$diklat) return redirect()->route('diklat.index')->with('error_message', 'User dengan id'.$id_diklat.' tidak ditemukan');
        return view('diklat.edit', [
            'diklat' => $diklat,
            'users' => User::where('is_deleted', '0')->get(),
        ]);
    }

    public function update(Request $request, $id_diklat)
    {
        $request->validate([
            'id_users' => 'required',
            'id_jenis_diklat' => 'required',
            'nama_diklat' => 'required',
            'penyelenggara' => 'required',
            'tanggal_diklat' => 'required',
            'jp' => 'required',
            'file_sertifikat' => 'nullable|mimes:pdf,doc,docx,png,jpg,jpeg',
        ]);
    
        $diklat = Diklat::find($id_diklat);
    
        if (!$diklat) {
            return redirect()->route('diklat.index')->with('error_message', 'Data tidak ditemukan');
        }
    
        $diklat->id_users = $request->id_users;
        $diklat->id_jenis_diklat = $request->id_jenis_diklat;
        $diklat->nama_diklat = $request->nama_diklat;
        $diklat->penyelenggara = $request->penyelenggara;
        $diklat->tanggal_diklat = $request->tanggal_diklat;
        $diklat->jp = $request->jp;
    
        if ($request->hasFile('file_sertifikat')) {
            // Menghapus file file_sertifikat sebelumnya
            if ($diklat->file_sertifikat) {
                Storage::disk('public')->delete('Diklat/' . $diklat->file_sertifikat);
            }
    
            // Upload file file_sertifikat baru
            $file_sertifikat = $request->file('file_sertifikat');
            $namafile_sertifikat = time() . '.' . $file_sertifikat->getClientOriginalExtension();
            Storage::disk('public')->put('Diklat/' . $namafile_sertifikat, file_get_contents($file_sertifikat));
            $diklat->file_sertifikat = $namafile_sertifikat;
        }
    
        $diklat->save();
    
        return redirect()->route('diklat.index')->with('success_message', 'Data telah tersimpan');
    }
    
    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id_diklat)
    {
        $diklat =  Diklat::find($id_diklat);
        if ($diklat) {
            $diklat->update([
                'is_deleted' => '1',
            ]);
        }
        return redirect()->route('diklat.index')->with('success_message', 'Data telah terhapus');
    }
    }