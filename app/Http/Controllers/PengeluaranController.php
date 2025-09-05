<?php

namespace App\Http\Controllers;

use App\Models\Akun;
use App\Models\Pengeluaran;
use App\Models\TransaksiAkun;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
                    $actionBtn = '<a href="' . route('pengeluaran.show', $row->id) . '" class="btn btn-sm btn-info">Detail</a> ';
                    $actionBtn .= '<a href="' . route('pengeluaran.edit', $row->id) . '" class="btn btn-sm btn-primary">Edit</a> ';

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
        $akuns = Akun::where('status', '1')->get();
        return view('pengeluaran.create', compact('akuns'));
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
            'akun_id' => 'required|exists:akun,id',
        ]);

        DB::beginTransaction();
        try {
            // Create expense record
            $pengeluaran = new Pengeluaran($validated);
            $pengeluaran->user_id = Auth::id();
            $pengeluaran->save();

            // Get kas akun (adjust as needed based on your system)
            $kasAkunId = 1; // Default cash account, adjust based on your system setup

            // 1. Jurnal kas - Uang keluar dari kas (kredit)
            TransaksiAkun::create([
                'akun_id' => $kasAkunId,
                'tanggal' => $pengeluaran->tanggal,
                'kode_referensi' => 'PGL-' . $pengeluaran->id,
                'tipe_referensi' => 'PENGELUARAN',
                'referensi_id' => $pengeluaran->id,
                'deskripsi' => 'Pengeluaran kas untuk ' . $pengeluaran->nama,
                'debit' => 0,
                'kredit' => $pengeluaran->jumlah,
                'user_id' => Auth::id()
            ]);

            // 2. Jurnal beban/pengeluaran - Beban pengeluaran (debit)
            TransaksiAkun::create([
                'akun_id' => $validated['akun_id'],
                'tanggal' => $pengeluaran->tanggal,
                'kode_referensi' => 'PGL-' . $pengeluaran->id,
                'tipe_referensi' => 'PENGELUARAN',
                'referensi_id' => $pengeluaran->id,
                'deskripsi' => 'Beban pengeluaran untuk ' . $pengeluaran->nama,
                'debit' => $pengeluaran->jumlah,
                'kredit' => 0,
                'user_id' => Auth::id()
            ]);

            DB::commit();

            return redirect()->route('pengeluaran.index')
                ->with('success', 'Pengeluaran berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified expense.
     *
     * @param  \App\Models\Pengeluaran  $pengeluaran
     * @return \Illuminate\Http\Response
     */
    public function edit(Pengeluaran $pengeluaran)
    {
        $akuns = Akun::where('status', '1')->get();

        // Get current akun_id from TransaksiAkun
        $transaksiAkun = TransaksiAkun::where('referensi_id', $pengeluaran->id)
            ->where('tipe_referensi', 'PENGELUARAN')
            ->where('debit', '>', 0)
            ->first();

        $akunId = $transaksiAkun ? $transaksiAkun->akun_id : null;

        return view('pengeluaran.edit', compact('pengeluaran', 'akuns', 'akunId'));
    }

    /**
     * Display the specified expense.
     *
     * @param  \App\Models\Pengeluaran  $pengeluaran
     * @return \Illuminate\Http\Response
     */
    public function show(Pengeluaran $pengeluaran)
    {
        $pengeluaran->load(['user', 'transaksiAkun.akun']);
        return view('pengeluaran.show', compact('pengeluaran'));
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
            'akun_id' => 'required|exists:akun,id',
        ]);

        DB::beginTransaction();
        try {
            // Get old amount for comparison
            $oldAmount = $pengeluaran->jumlah;
            $newAmount = $validated['jumlah'];

            // Update expense record
            $pengeluaran->update($validated);

            // Get kas akun (adjust as needed based on your system)
            $kasAkunId = 1; // Default cash account, adjust based on your system setup

            // Find existing accounting entries
            $transaksiDebit = TransaksiAkun::where('referensi_id', $pengeluaran->id)
                ->where('tipe_referensi', 'PENGELUARAN')
                ->where('debit', '>', 0)
                ->first();

            $transaksiKredit = TransaksiAkun::where('referensi_id', $pengeluaran->id)
                ->where('tipe_referensi', 'PENGELUARAN')
                ->where('kredit', '>', 0)
                ->first();

            // Update or create accounting entries
            if ($transaksiDebit) {
                $transaksiDebit->update([
                    'akun_id' => $validated['akun_id'],
                    'tanggal' => $pengeluaran->tanggal,
                    'deskripsi' => 'Beban pengeluaran untuk ' . $pengeluaran->nama,
                    'debit' => $newAmount,
                ]);
            } else {
                TransaksiAkun::create([
                    'akun_id' => $validated['akun_id'],
                    'tanggal' => $pengeluaran->tanggal,
                    'kode_referensi' => 'PGL-' . $pengeluaran->id,
                    'tipe_referensi' => 'PENGELUARAN',
                    'referensi_id' => $pengeluaran->id,
                    'deskripsi' => 'Beban pengeluaran untuk ' . $pengeluaran->nama,
                    'debit' => $newAmount,
                    'kredit' => 0,
                    'user_id' => Auth::id()
                ]);
            }

            if ($transaksiKredit) {
                $transaksiKredit->update([
                    'akun_id' => $kasAkunId,
                    'tanggal' => $pengeluaran->tanggal,
                    'deskripsi' => 'Pengeluaran kas untuk ' . $pengeluaran->nama,
                    'kredit' => $newAmount,
                ]);
            } else {
                TransaksiAkun::create([
                    'akun_id' => $kasAkunId,
                    'tanggal' => $pengeluaran->tanggal,
                    'kode_referensi' => 'PGL-' . $pengeluaran->id,
                    'tipe_referensi' => 'PENGELUARAN',
                    'referensi_id' => $pengeluaran->id,
                    'deskripsi' => 'Pengeluaran kas untuk ' . $pengeluaran->nama,
                    'debit' => 0,
                    'kredit' => $newAmount,
                    'user_id' => Auth::id()
                ]);
            }

            DB::commit();

            return redirect()->route('pengeluaran.index')
                ->with('success', 'Pengeluaran berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
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

        DB::beginTransaction();
        try {
            // Delete related accounting entries
            TransaksiAkun::where('referensi_id', $pengeluaran->id)
                ->where('tipe_referensi', 'PENGELUARAN')
                ->delete();

            // Delete expense
            $pengeluaran->delete();

            DB::commit();
            return response()->json(['success' => 'Pengeluaran berhasil dihapus.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }
}
