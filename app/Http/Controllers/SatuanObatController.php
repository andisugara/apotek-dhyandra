<?php

namespace App\Http\Controllers;

use App\Models\SatuanObat;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class SatuanObatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = SatuanObat::select('*');

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('status', function ($row) {
                    $statusClass = $row->is_active ? 'badge-light-success' : 'badge-light-danger';
                    $statusText = $row->is_active ? 'Aktif' : 'Non-Aktif';
                    return '<span class="badge ' . $statusClass . '">' . $statusText . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('satuan_obat.edit', $row->id) . '" class="btn btn-sm btn-primary">Edit</a> ';
                    $actionBtn .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $row->id . '">Hapus</button>';
                    return $actionBtn;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('satuan_obat.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('satuan_obat.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:50|unique:satuan_obat,nama',
            'is_active' => 'boolean',
        ]);

        // Set status default to active if not provided
        if (!isset($validated['is_active'])) {
            $validated['is_active'] = true;
        }

        SatuanObat::create($validated);

        return redirect()->route('satuan_obat.index')->with('success', 'Satuan obat berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(SatuanObat $satuan_obat)
    {
        return view('satuan_obat.show', compact('satuan_obat'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SatuanObat $satuan_obat)
    {
        return view('satuan_obat.edit', compact('satuan_obat'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SatuanObat $satuan_obat)
    {
        $validated = $request->validate([
            'nama' => [
                'required',
                'string',
                'max:50',
                Rule::unique('satuan_obat')->ignore($satuan_obat->id),
            ],
            'is_active' => 'boolean',
        ]);

        // Set status default to active if not provided
        if (!isset($validated['is_active'])) {
            $validated['is_active'] = false;
        }

        $satuan_obat->update($validated);

        return redirect()->route('satuan_obat.index')->with('success', 'Satuan obat berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SatuanObat $satuan_obat)
    {
        $satuan_obat->delete();
        return redirect()->route('satuan_obat.index')->with('success', 'Satuan obat berhasil dihapus');
    }
}
