<?php

namespace App\Http\Controllers;

use App\Models\Pasien;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class PasienController extends Controller
{
    /**
     * Display a listing of the pasien.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Pasien::select('*');

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('status', function ($row) {
                    $statusClass = $row->is_active ? 'badge-light-success' : 'badge-light-danger';
                    $statusText = $row->is_active ? 'Aktif' : 'Non-Aktif';
                    return '<span class="badge ' . $statusClass . '">' . $statusText . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('pasien.edit', $row->id) . '" class="btn btn-sm btn-primary">Edit</a> ';
                    $actionBtn .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $row->id . '">Hapus</button>';
                    return $actionBtn;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('pasien.index');
    }

    /**
     * Show the form for creating a new pasien.
     */
    public function create()
    {
        $defaultCode = Pasien::generateCode();
        return view('pasien.create', compact('defaultCode'));
    }

    /**
     * Store a newly created pasien in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:pasien,code',
            'nama' => 'required|string|max:100',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'tanggal_lahir' => 'required|date',
            'alamat' => 'required|string',
            'is_active' => 'boolean',
        ]);

        // Set status default to active if not provided
        if (!isset($validated['is_active'])) {
            $validated['is_active'] = true;
        }

        Pasien::create($validated);

        return redirect()->route('pasien.index')->with('success', 'Pasien berhasil ditambahkan');
    }

    /**
     * Show the pasien details.
     */
    public function show(Pasien $pasien)
    {
        return view('pasien.show', compact('pasien'));
    }

    /**
     * Show the form for editing the specified pasien.
     */
    public function edit(Pasien $pasien)
    {
        return view('pasien.edit', compact('pasien'));
    }

    /**
     * Update the specified pasien in storage.
     */
    public function update(Request $request, Pasien $pasien)
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('pasien')->ignore($pasien->id),
            ],
            'nama' => 'required|string|max:100',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'tanggal_lahir' => 'required|date',
            'alamat' => 'required|string',
            'is_active' => 'boolean',
        ]);

        // Set status default to active if not provided
        if (!isset($validated['is_active'])) {
            $validated['is_active'] = true;
        }

        $pasien->update($validated);

        return redirect()->route('pasien.index')->with('success', 'Pasien berhasil diperbarui');
    }

    /**
     * Remove the specified pasien from storage.
     */
    public function destroy(Pasien $pasien)
    {
        $pasien->delete();
        return redirect()->route('pasien.index')->with('success', 'Pasien berhasil dihapus');
    }
}
