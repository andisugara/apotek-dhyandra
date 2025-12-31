<?php

namespace App\Http\Controllers;

use App\Models\ObatSatuan;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Symfony\Component\HttpFoundation\StreamedResponse;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LaporanStokObatController extends Controller
{
    /**
     * Tampilkan laporan stok obat per satuan.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = ObatSatuan::with(['obat.golongan', 'obat.kategori', 'satuan', 'stok']);

            return datatables()->of($query)
                ->addIndexColumn()
                ->editColumn('kode_obat', function ($row) {
                    return $row->obat->kode_obat ?? '-';
                })
                ->editColumn('nama_obat', function ($row) {
                    return $row->obat->nama_obat ?? '-';
                })
                ->editColumn('pabrik', function ($row) {
                    return $row->obat->pabrik->nama ?? '-';
                })
                ->editColumn('golongan', function ($row) {
                    return $row->obat->golongan->nama ?? '-';
                })
                ->editColumn('kategori', function ($row) {
                    return $row->obat->kategori->nama ?? '-';
                })
                ->editColumn('satuan', function ($row) {
                    return $row->satuan->nama ?? '-';
                })
                ->editColumn('stok', function ($row) {
                    // Jumlahkan qty dari semua stok yang terkait dengan ObatSatuan ini
                    return $row->stok->sum('qty') ?? 0;
                })
                ->editColumn('status', function ($row) {
                    if (isset($row->obat->is_active)) {
                        $label = $row->obat->is_active == 1 ? 'Aktif' : 'Non Aktif';
                        $class = $row->obat->is_active == 1 ? 'bg-success' : 'bg-danger';
                        return '<span class="badge ' . $class . '">' . $label . '</span>';
                    }
                    return '-';
                })
                ->rawColumns(['status'])
                ->make(true);
        }

        // Untuk tampilan awal (bukan ajax)
        $data = [];
        return view('laporan.stok_obat', compact('data'));
    }

    /**
     * Export Excel semua data stok obat (dikelompokkan per obat dengan merge field obat)
     */
    public function exportExcel()
    {
        $data = ObatSatuan::with(['obat.golongan', 'obat.kategori', 'satuan', 'stok'])
            ->orderBy('obat_id')
            ->get();
        
        // Kelompokkan data berdasarkan nama obat
        $groupedData = $data->groupBy(function ($item) {
            return $item->obat->id;
        });
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Header
        $headers = ['No', 'Kode Obat', 'Nama Obat', 'Golongan', 'Kategori', 'Satuan', 'Pabrik', 'Stok', 'Harga Jual', 'Status'];
        $sheet->fromArray([$headers], null, 'A1');
        
        // Style header
        $headerStyle = [
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'D3D3D3']],
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center']
        ];
        $sheet->getStyle('A1:J1')->applyFromArray($headerStyle);
        
        // Data
        $row = 2;
        $no = 1;
        
        foreach ($groupedData as $obatId => $items) {
            $startRow = $row;
            $itemCount = count($items);
            $firstItem = $items->first();
            
            foreach ($items as $item) {
                $sheet->setCellValue('A' . $row, $no);
                $sheet->setCellValue('B' . $row, $firstItem->obat->kode_obat ?? '-');
                $sheet->setCellValue('C' . $row, $firstItem->obat->nama_obat ?? '-');
                $sheet->setCellValue('D' . $row, $firstItem->obat->golongan->nama ?? '-');
                $sheet->setCellValue('E' . $row, $firstItem->obat->kategori->nama ?? '-');
                $sheet->setCellValue('F' . $row, $item->satuan->nama ?? '-');
                $sheet->setCellValue('G' . $row, $firstItem->obat->pabrik->nama ?? '-');
                $sheet->setCellValue('H' . $row, $item->stok->sum('qty') ?? 0);
                $sheet->setCellValue('I' . $row, $firstItem->obat->harga_jual ?? 0);
                $sheet->setCellValue('J' . $row, $firstItem->obat->is_active == 1 ? 'Aktif' : 'Non Aktif');
                
                $row++;
            }
            
            // Merge cell untuk field obat jika ada lebih dari 1 satuan
            if ($itemCount > 1) {
                // Merge: No, Kode Obat, Nama Obat, Golongan, Kategori, Pabrik, Status
                $columns = ['A', 'B', 'C', 'D', 'E', 'G', 'J'];
                foreach ($columns as $col) {
                    $sheet->mergeCells($col . $startRow . ':' . $col . ($startRow + $itemCount - 1));
                    $sheet->getStyle($col . $startRow)->getAlignment()->setVertical('center');
                }
            }
            
            $no++;
        }
        
        // Auto-size columns
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Set column width minimum
        foreach (range('A', 'J') as $col) {
            if ($sheet->getColumnDimension($col)->getWidth() < 15) {
                $sheet->getColumnDimension($col)->setWidth(15);
            }
        }
        
        $writer = new Xlsx($spreadsheet);
        $filename = 'laporan-stok-obat-' . date('Y-m-d-His') . '.xlsx';
        
        return response()->stream(
            function () use ($writer) {
                $writer->save('php://output');
            },
            200,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ]
        );
    }

    /**
     * Export PDF semua data stok obat (dikelompokkan per obat dengan merge field obat)
     */
    public function exportPdf()
    {
        $data = ObatSatuan::with(['obat.golongan', 'obat.kategori', 'satuan', 'stok'])
            ->orderBy('obat_id')
            ->get();
        
        // Kelompokkan data berdasarkan obat_id
        $groupedData = $data->groupBy(function ($item) {
            return $item->obat->id;
        });
        
        $html = '<html><head>';
        $html .= '<meta charset="UTF-8">';
        $html .= '<style>';
        $html .= 'body { font-family: Arial, sans-serif; }';
        $html .= 'h2 { text-align: center; margin-bottom: 20px; }';
        $html .= 'table { width: 100%; border-collapse: collapse; font-size: 11px; }';
        $html .= 'th, td { border: 1px solid #000; padding: 8px; text-align: left; }';
        $html .= 'th { background-color: #d3d3d3; font-weight: bold; text-align: center; }';
        $html .= 'tr:nth-child(even) { background-color: #f9f9f9; }';
        $html .= '</style>';
        $html .= '</head><body>';
        
        $html .= '<h2>Laporan Stok Obat</h2>';
        $html .= '<table>';
        $html .= '<thead><tr>';
        $html .= '<th>No</th><th>Kode Obat</th><th>Nama Obat</th><th>Golongan</th><th>Kategori</th>';
        $html .= '<th>Satuan</th><th>Pabrik</th><th>Stok</th><th>Harga Jual</th><th>Status</th>';
        $html .= '</tr></thead><tbody>';
        
        $no = 1;
        foreach ($groupedData as $obatId => $items) {
            $itemCount = count($items);
            $firstItem = $items->first();
            $isFirstRow = true;
            
            foreach ($items as $item) {
                $html .= '<tr>';
                
                // Kolom yang di-merge: No, Kode, Nama, Golongan, Kategori, Pabrik, Status
                if ($isFirstRow) {
                    $html .= '<td rowspan="' . $itemCount . '">' . $no . '</td>';
                    $html .= '<td rowspan="' . $itemCount . '">' . ($firstItem->obat->kode_obat ?? '-') . '</td>';
                    $html .= '<td rowspan="' . $itemCount . '">' . ($firstItem->obat->nama_obat ?? '-') . '</td>';
                    $html .= '<td rowspan="' . $itemCount . '">' . ($firstItem->obat->golongan->nama ?? '-') . '</td>';
                    $html .= '<td rowspan="' . $itemCount . '">' . ($firstItem->obat->kategori->nama ?? '-') . '</td>';
                    $isFirstRow = false;
                }
                
                // Kolom per satuan: Satuan, Stok, Harga Jual, Status
                $html .= '<td>' . ($item->satuan->nama ?? '-') . '</td>';
                $html .= '<td rowspan="' . $itemCount . '">' . ($firstItem->obat->pabrik->nama ?? '-') . '</td>';
                $html .= '<td>' . ($item->stok->sum('qty') ?? 0) . '</td>';
                $html .= '<td rowspan="' . $itemCount . '">' . ($firstItem->obat->harga_jual ?? 0) . '</td>';
                $html .= '<td rowspan="' . $itemCount . '">' . ($firstItem->obat->is_active == 1 ? 'Aktif' : 'Non Aktif') . '</td>';
                
                $html .= '</tr>';
            }
            
            $no++;
        }
        
        $html .= '</tbody></table>';
        $html .= '</body></html>';
        
        $pdf = \PDF::loadHTML($html);
        $pdf->setPaper('A4', 'landscape');
        return $pdf->download('laporan-stok-obat-' . date('Y-m-d-His') . '.pdf');
    }
}