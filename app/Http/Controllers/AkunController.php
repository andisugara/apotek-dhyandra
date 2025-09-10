<?php

namespace App\Http\Controllers;

use App\Models\Akun;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class AkunController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Akun::select('*');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('status_label', function ($row) {
                    $statusClass = $row->status == '1' ? 'badge-light-success' : 'badge-light-danger';
                    return '<span class="badge ' . $statusClass . '">' . $row->status_label . '</span>';
                })
                ->addColumn('is_default_label', function ($row) {
                    if ($row->is_default) {
                        return '<span class="badge badge-light-primary">Default</span>';
                    }
                    return '';
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('akun.edit', $row->id) . '" class="btn btn-sm btn-primary">Edit</a> ';
                    $actionBtn .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $row->id . '">Hapus</button>';
                    return $actionBtn;
                })
                ->rawColumns(['status_label', 'is_default_label', 'action'])
                ->make(true);
        }

        return view('akun.index');
    }

    public function create()
    {
        return view('akun.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode' => 'nullable|string|max:16|min:4|unique:akun,kode',
            'nama' => 'required|string|max:100|min:3',
            'status' => 'required|in:0,1',
        ]);

        // Handle kode generation if not provided
        if (empty($validated['kode'])) {
            // Generate kode: AKUNyymmddXXXX
            $date = date('ymd');
            $last = Akun::whereDate('created_at', now())->count() + 1;
            $kode = 'AKN' . $date . str_pad($last, 4, '0', STR_PAD_LEFT);

            // Check if generated kode already exists, if so, increment until finding unique one
            $counter = $last;
            while (Akun::where('kode', $kode)->exists()) {
                $counter++;
                $kode = 'AKN' . $date . str_pad($counter, 4, '0', STR_PAD_LEFT);
            }
            $validated['kode'] = $kode;
        } else {
            // Ensure kode always starts with AKN
            if (!str_starts_with($validated['kode'], 'AKN')) {
                $validated['kode'] = 'AKN' . $validated['kode'];
            }
        }

        // Handle is_default logic
        $validated['is_default'] = isset($request->is_default) ? true : false;

        // If this account is set as default, remove default status from other accounts
        if ($validated['is_default']) {
            Akun::where('is_default', true)->update(['is_default' => false]);
        }

        Akun::create($validated);
        return redirect()->route('akun.index')->with('success', 'Akun berhasil ditambahkan');
    }

    public function show(Akun $akun)
    {
        return view('akun.show', compact('akun'));
    }

    public function edit(Akun $akun)
    {
        return view('akun.edit', compact('akun'));
    }

    public function update(Request $request, Akun $akun)
    {
        $validated = $request->validate([
            'kode' => [
                'required',
                'string',
                'max:16',
                'min:4',
                Rule::unique('akun')->ignore($akun->id),
            ],
            'nama' => 'required|string|max:100|min:3',
            'status' => 'required|in:0,1',
        ]);

        // Ensure kode always starts with AKN
        if (!str_starts_with($validated['kode'], 'AKN')) {
            $validated['kode'] = 'AKN' . $validated['kode'];
        }

        // Handle is_default logic
        $validated['is_default'] = isset($request->is_default) ? true : false;

        // If this account is set as default, remove default status from other accounts
        if ($validated['is_default'] && !$akun->is_default) {
            Akun::where('is_default', true)->update(['is_default' => false]);
        }

        // If this account was the default and is no longer default, we don't need to do anything special
        // as no other account is being set as default

        $akun->update($validated);
        return redirect()->route('akun.index')->with('success', 'Akun berhasil diperbarui');
    }

    public function destroy(Akun $akun)
    {
        $akun->delete();
        return redirect()->route('akun.index')->with('success', 'Akun berhasil dihapus');
    }
}
