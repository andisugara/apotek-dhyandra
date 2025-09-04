<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Supplier::select('*');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('status_label', function ($row) {
                    $statusClass = $row->status == '1' ? 'badge-light-success' : 'badge-light-danger';
                    return '<span class="badge ' . $statusClass . '">' . $row->status_label . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('supplier.edit', $row->id) . '" class="btn btn-sm btn-primary">Edit</a> ';
                    $actionBtn .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $row->id . '">Hapus</button>';
                    return $actionBtn;
                })
                ->rawColumns(['status_label', 'action'])
                ->make(true);
        }

        return view('supplier.index');
    }

    public function create()
    {
        return view('supplier.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode' => 'nullable|string|max:15|min:4|unique:suppliers,kode',
            'nama' => 'required|string|max:255|min:3',
            'alamat' => 'required|string|min:5',
            'kota' => 'required|string|max:100|min:3',
            'telepone' => 'required|string|max:20|min:10|regex:/^[0-9]+$/',
            'lead_time' => 'required|integer|min:0',
            'status' => 'required|in:0,1',
        ]);

        // Handle kode supplier
        if (empty($validated['kode'])) {
            // Generate kode supplier jika tidak diisi
            $date = date('ymd');
            $last = Supplier::whereDate('created_at', now())->count() + 1;
            $kode = 'SUP' . $date . str_pad($last, 4, '0', STR_PAD_LEFT);

            // Check if generated kode already exists, if so, increment until finding unique one
            $counter = $last;
            while (Supplier::where('kode', $kode)->exists()) {
                $counter++;
                $kode = 'SUP' . $date . str_pad($counter, 4, '0', STR_PAD_LEFT);
            }
            $validated['kode'] = $kode;
        } else {
            // Ensure kode always starts with SUP
            if (!str_starts_with($validated['kode'], 'SUP')) {
                $validated['kode'] = 'SUP' . $validated['kode'];
            }
        }

        Supplier::create($validated);
        return redirect()->route('supplier.index')->with('success', 'Supplier berhasil ditambahkan');
    }

    public function edit($id)
    {
        $supplier = Supplier::findOrFail($id);
        return view('supplier.edit', compact('supplier'));
    }

    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);
        $validated = $request->validate([
            'kode' => 'required|string|max:15|min:4|unique:suppliers,kode,' . $id,
            'nama' => 'required|string|max:255|min:3',
            'alamat' => 'required|string|min:5',
            'kota' => 'required|string|max:100|min:3',
            'telepone' => 'required|string|max:20|min:10|regex:/^[0-9]+$/',
            'lead_time' => 'required|integer|min:0',
            'status' => 'required|in:0,1',
        ]);

        // Ensure kode always starts with SUP
        if (!str_starts_with($validated['kode'], 'SUP')) {
            $validated['kode'] = 'SUP' . $validated['kode'];
        }
        $supplier->update($validated);
        return redirect()->route('supplier.index')->with('success', 'Supplier berhasil diupdate');
    }

    public function destroy($id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->delete();
        return redirect()->route('supplier.index')->with('success', 'Supplier berhasil dihapus');
    }
}
