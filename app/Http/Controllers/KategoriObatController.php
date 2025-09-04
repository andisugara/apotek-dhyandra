<?php

namespace App\Http\Controllers;

use App\Models\KategoriObat;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class KategoriObatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = KategoriObat::select('*');

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('status', function ($row) {
                    $statusClass = $row->is_active ? 'badge-light-success' : 'badge-light-danger';
                    $statusText = $row->is_active ? 'Aktif' : 'Non-Aktif';
                    return '<span class="badge ' . $statusClass . '">' . $statusText . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('kategori_obat.edit', $row->id) . '" class="btn btn-sm btn-primary">Edit</a> ';
                    $actionBtn .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $row->id . '">Hapus</button>';
                    return $actionBtn;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('kategori_obat.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('kategori_obat.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100|unique:kategori_obat,nama',
            'keterangan' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // Set status default to active if not provided
        if (!isset($validated['is_active'])) {
            $validated['is_active'] = true;
        }

        KategoriObat::create($validated);

        return redirect()->route('kategori_obat.index')->with('success', 'Kategori obat berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(KategoriObat $kategori_obat)
    {
        return view('kategori_obat.show', compact('kategori_obat'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(KategoriObat $kategori_obat)
    {
        return view('kategori_obat.edit', compact('kategori_obat'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, KategoriObat $kategori_obat)
    {
        $validated = $request->validate([
            'nama' => [
                'required',
                'string',
                'max:100',
                Rule::unique('kategori_obat')->ignore($kategori_obat->id),
            ],
            'keterangan' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // Set status default to active if not provided
        if (!isset($validated['is_active'])) {
            $validated['is_active'] = false;
        }

        $kategori_obat->update($validated);

        return redirect()->route('kategori_obat.index')->with('success', 'Kategori obat berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(KategoriObat $kategori_obat)
    {
        $kategori_obat->delete();
        return redirect()->route('kategori_obat.index')->with('success', 'Kategori obat berhasil dihapus');
    }
}
