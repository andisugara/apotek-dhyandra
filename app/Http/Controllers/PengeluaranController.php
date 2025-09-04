<?php

namespace App\Http\Controllers;

use App\Models\Pengeluaran;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class PengeluaranController extends Controller
{
    /**
     * Display a listing of the expenses.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $pengeluaran = Pengeluaran::with('user')->select('pengeluaran.*');

            // Filter by month and year if provided
            if ($request->has('month') && $request->has('year')) {
                $month = $request->month;
                $year = $request->year;
                $pengeluaran->whereMonth('tanggal', $month)
                    ->whereYear('tanggal', $year);
            }

            return DataTables::of($pengeluaran)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('pengeluaran.edit', $row->id) . '" class="btn btn-sm btn-primary">Edit</a> ';

                    // Only allow deletion if expense is within 1 month and user is superadmin
                    $user = User::find(Auth::id());
                    if ($row->canBeDeleted() && $user && $user->hasRole('Superadmin')) {
                        $actionBtn .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $row->id . '">Hapus</button>';
                    }

                    return $actionBtn;
                })
                ->addColumn('formatted_jumlah', function ($row) {
                    return 'Rp ' . $row->formatted_jumlah;
                })
                ->addColumn('user_name', function ($row) {
                    return $row->user ? $row->user->name : 'Unknown';
                })
                ->addColumn('formatted_tanggal', function ($row) {
                    return $row->tanggal->format('d/m/Y');
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        // Get current month and year for default filter
        $currentMonth = date('m');
        $currentYear = date('Y');

        return view('pengeluaran.index', compact('currentMonth', 'currentYear'));
    }

    /**
     * Show the form for creating a new expense.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('pengeluaran.create');
    }

    /**
     * Store a newly created expense in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'tanggal' => 'required|date',
            'jumlah' => 'required|numeric|min:0',
        ]);

        $pengeluaran = new Pengeluaran($validated);
        $pengeluaran->user_id = Auth::id();
        $pengeluaran->save();

        return redirect()->route('pengeluaran.index')
            ->with('success', 'Pengeluaran berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified expense.
     *
     * @param  \App\Models\Pengeluaran  $pengeluaran
     * @return \Illuminate\Http\Response
     */
    public function edit(Pengeluaran $pengeluaran)
    {
        return view('pengeluaran.edit', compact('pengeluaran'));
    }

    /**
     * Update the specified expense in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Pengeluaran  $pengeluaran
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Pengeluaran $pengeluaran)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'tanggal' => 'required|date',
            'jumlah' => 'required|numeric|min:0',
        ]);

        $pengeluaran->update($validated);

        return redirect()->route('pengeluaran.index')
            ->with('success', 'Pengeluaran berhasil diperbarui.');
    }

    /**
     * Remove the specified expense from storage.
     *
     * @param  \App\Models\Pengeluaran  $pengeluaran
     * @return \Illuminate\Http\Response
     */
    public function destroy(Pengeluaran $pengeluaran)
    {
        // Verify if user is authorized to delete (only superadmin)
        $user = User::find(Auth::id());
        if (!$user || !$user->hasRole('Superadmin')) {
            throw ValidationException::withMessages([
                'delete' => ['Only superadmin can delete expenses.'],
            ]);
        }

        // Check if the expense can be deleted (within 1 month)
        if (!$pengeluaran->canBeDeleted()) {
            throw ValidationException::withMessages([
                'delete' => ['Only expenses created within the last month can be deleted.'],
            ]);
        }

        $pengeluaran->delete();

        return response()->json(['success' => 'Pengeluaran berhasil dihapus.']);
    }
}
