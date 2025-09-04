<?php

namespace App\Http\Controllers;

use App\Models\LokasiObat;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class LokasiObatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = LokasiObat::select('*');

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('status', function ($row) {
                    $statusClass = $row->is_active ? 'badge-light-success' : 'badge-light-danger';
                    $statusText = $row->is_active ? 'Aktif' : 'Non-Aktif';
                    return '<span class="badge ' . $statusClass . '">' . $statusText . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('lokasi_obat.edit', $row->id) . '" class="btn btn-sm btn-primary">Edit</a> ';
                    $actionBtn .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $row->id . '">Hapus</button>';
                    return $actionBtn;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('lokasi_obat.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('lokasi_obat.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100|unique:lokasi_obat,nama',
            'is_active' => 'boolean',
        ]);

        // Set status default to active if not provided
        if (!isset($validated['is_active'])) {
            $validated['is_active'] = true;
        }

        LokasiObat::create($validated);

        return redirect()->route('lokasi_obat.index')->with('success', 'Lokasi obat berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(LokasiObat $lokasi_obat)
    {
        return view('lokasi_obat.show', compact('lokasi_obat'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LokasiObat $lokasi_obat)
    {
        return view('lokasi_obat.edit', compact('lokasi_obat'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LokasiObat $lokasi_obat)
    {
        $validated = $request->validate([
            'nama' => [
                'required',
                'string',
                'max:100',
                Rule::unique('lokasi_obat')->ignore($lokasi_obat->id),
            ],
            'is_active' => 'boolean',
        ]);

        // Set status default to active if not provided
        if (!isset($validated['is_active'])) {
            $validated['is_active'] = false;
        }

        $lokasi_obat->update($validated);

        return redirect()->route('lokasi_obat.index')->with('success', 'Lokasi obat berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LokasiObat $lokasi_obat)
    {
        $lokasi_obat->delete();
        return redirect()->route('lokasi_obat.index')->with('success', 'Lokasi obat berhasil dihapus');
    }
}
