<?php

namespace App\Http\Controllers;

use App\Models\Pabrik;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class PabrikController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Pabrik::select('*');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('status_label', function ($row) {
                    $statusClass = $row->status == '1' ? 'badge-light-success' : 'badge-light-danger';
                    return '<span class="badge ' . $statusClass . '">' . $row->status_label . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('pabrik.edit', $row->id) . '" class="btn btn-sm btn-primary">Edit</a> ';
                    $actionBtn .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $row->id . '">Hapus</button>';
                    return $actionBtn;
                })
                ->rawColumns(['status_label', 'action'])
                ->make(true);
        }

        return view('pabrik.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pabrik.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode' => 'nullable|string|max:16|min:4|unique:pabrik,kode',
            'nama' => 'required|string|max:100|min:3',
            'alamat' => 'required|string|min:5',
            'status' => 'required|in:0,1',
        ]);

        // Handle kode generation if not provided
        if (empty($validated['kode'])) {
            // Generate kode: PABYYMMDDSTRPAD4
            $date = date('ymd');
            $last = Pabrik::whereDate('created_at', now())->count() + 1;
            $kode = 'PAB' . $date . str_pad($last, 4, '0', STR_PAD_LEFT);

            // Check if generated kode already exists, if so, increment until finding unique one
            $counter = $last;
            while (Pabrik::where('kode', $kode)->exists()) {
                $counter++;
                $kode = 'PAB' . $date . str_pad($counter, 4, '0', STR_PAD_LEFT);
            }
            $validated['kode'] = $kode;
        } else {
            // Ensure kode always starts with PAB
            if (!str_starts_with($validated['kode'], 'PAB')) {
                $validated['kode'] = 'PAB' . $validated['kode'];
            }
        }

        Pabrik::create($validated);

        return redirect()->route('pabrik.index')->with('success', 'Pabrik berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(Pabrik $pabrik)
    {
        return view('pabrik.show', compact('pabrik'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pabrik $pabrik)
    {
        return view('pabrik.edit', compact('pabrik'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Pabrik $pabrik)
    {
        $validated = $request->validate([
            'kode' => [
                'required',
                'string',
                'max:16',
                'min:4',
                Rule::unique('pabrik')->ignore($pabrik->id),
            ],
            'nama' => 'required|string|max:100|min:3',
            'alamat' => 'required|string|min:5',
            'status' => 'required|in:0,1',
        ]);

        // Ensure kode always starts with PAB
        if (!str_starts_with($validated['kode'], 'PAB')) {
            $validated['kode'] = 'PAB' . $validated['kode'];
        }

        $pabrik->update($validated);

        return redirect()->route('pabrik.index')->with('success', 'Pabrik berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pabrik $pabrik)
    {
        $pabrik->delete();
        return redirect()->route('pabrik.index')->with('success', 'Pabrik berhasil dihapus');
    }
}
