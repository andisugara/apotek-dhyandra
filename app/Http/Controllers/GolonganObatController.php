<?php

namespace App\Http\Controllers;

use App\Models\GolonganObat;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class GolonganObatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = GolonganObat::select('*');

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('status', function ($row) {
                    $statusClass = $row->is_active ? 'badge-light-success' : 'badge-light-danger';
                    $statusText = $row->is_active ? 'Aktif' : 'Non-Aktif';
                    return '<span class="badge ' . $statusClass . '">' . $statusText . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('golongan_obat.edit', $row->id) . '" class="btn btn-sm btn-primary">Edit</a> ';
                    $actionBtn .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $row->id . '">Hapus</button>';
                    return $actionBtn;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('golongan_obat.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('golongan_obat.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100|unique:golongan_obat,nama',
            'keterangan' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // Set status default to active if not provided
        if (!isset($validated['is_active'])) {
            $validated['is_active'] = true;
        }

        GolonganObat::create($validated);

        return redirect()->route('golongan_obat.index')->with('success', 'Golongan obat berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(GolonganObat $golongan_obat)
    {
        return view('golongan_obat.show', compact('golongan_obat'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(GolonganObat $golongan_obat)
    {
        return view('golongan_obat.edit', compact('golongan_obat'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, GolonganObat $golongan_obat)
    {
        $validated = $request->validate([
            'nama' => [
                'required',
                'string',
                'max:100',
                Rule::unique('golongan_obat')->ignore($golongan_obat->id),
            ],
            'keterangan' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // Set status default to active if not provided
        if (!isset($validated['is_active'])) {
            $validated['is_active'] = false;
        }

        $golongan_obat->update($validated);

        return redirect()->route('golongan_obat.index')->with('success', 'Golongan obat berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GolonganObat $golongan_obat)
    {
        $golongan_obat->delete();
        return redirect()->route('golongan_obat.index')->with('success', 'Golongan obat berhasil dihapus');
    }
}
