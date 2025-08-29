<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Exception;

class InventoryMovementService
{
    /**
     * Proses transfer inventory
     *
     * @param array $data
     * @return string Nomor dokumen transfer
     * @throws Exception
     */
    public function processTransfer(array $data): string
    {
        return DB::transaction(function () use ($data) {

            // --- 1. Generate nomor transfer
            $date = Carbon::parse($data['grdate']);
            $ptaNumber = generateTransferNumber($date->year, $date->month);

            // --- 2. Insert header ke t_inv01
            DB::table('t_inv01')->insert([
                'docnum'        => $ptaNumber,
                'docyear'       => $date->year,
                'docdate'       => $data['grdate'],
                'postdate'      => $data['grdate'],
                'received_by'   => $data['recipient'],
                'movement_code' => '301',
                'remark'        => $data['remark'] ?? null,
                'createdon'     => now(),
                'createdby'     => $this->currentUserIdentifier(),
            ]);

            // --- 3. Proses detail parts
            foreach ($data['parts'] as $i => $part) {
                $qty = (int) str_replace(',', '', $data['quantity'][$i] ?? 0);

                $latestStock = DB::table('t_inv_stock')
                    ->where('material', $part)
                    ->where('whscode', $data['whscode'])
                    ->first();

                if (!$latestStock || $latestStock->quantity < $qty) {
                    throw new Exception("Stock Tidak Mencukupi untuk part : {$part}");
                }

                // Panggil stored procedure
                DB::select('call spTransferMaterialWithBatchFIFO2(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                    $part,
                    $data['whscode'],
                    $data['whscodeto'],
                    $qty,
                    $ptaNumber,
                    $date->year,
                    '301',
                    $data['partdesc'][$i] ?? '',
                    $data['uoms'][$i] ?? '',
                    '-',
                    null,
                    0,
                    Auth::user()->email,
                ]);
            }

            // --- 4. Insert attachments kalau ada
            if (!empty($data['efile'])) {
                $this->saveAttachments($ptaNumber, $data['efile']);
            }

            return $ptaNumber;
        });
    }

    /**
     * Simpan file attachment
     */
    protected function saveAttachments(string $ptaNumber, array $files): void
    {
        $insertFiles = [];

        foreach ($files as $efile) {
            $filename = uniqid() . '_' . $efile->getClientOriginalName();
            $efile->move(public_path('files/TRANSFER'), $filename);

            $insertFiles[] = [
                'doc_object' => 'TRANSFER',
                'doc_number' => $ptaNumber,
                'efile'      => $filename,
                'pathfile'   => '/files/TRANSFER/' . $filename,
                'createdon'  => now(),
                'createdby'  => $this->currentUserIdentifier(),
            ];
        }

        if (count($insertFiles) > 0) {
            insertOrUpdate($insertFiles, 't_attachments');
        }
    }

    /**
     * Ambil identitas user (username/email)
     */
    protected function currentUserIdentifier(): string
    {
        $user = Auth::user();
        return $user->username ?? $user->email ?? 'system';
    }
}
